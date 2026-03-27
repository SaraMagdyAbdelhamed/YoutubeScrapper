<?php

use App\Jobs\ScrapeCategoryJob;
use Illuminate\Support\Facades\Queue;

it('requires categories field to be present', function () {
    $response = $this->post(route('scrape'), []);

    $response->assertSessionHasErrors('categories');
});

it('requires categories field to be a string', function () {
    $response = $this->post(route('scrape'), ['categories' => ['not', 'a', 'string']]);

    $response->assertSessionHasErrors('categories');
});

it('dispatches a job for each category and redirects with success message', function () {
    Queue::fake();

    $categoriesInput = "React\nVue\n\nAngular"; // Test newline separation and empty string filtering

    $response = $this->post(route('scrape'), [
        'categories' => $categoriesInput,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
    $response->assertSessionHas('success', 'تم بدء جلب البيانات! يرجى الانتظار حتى تنتهي المهام في الخلفية.');

    Queue::assertPushed(ScrapeCategoryJob::class, 3);
    
    // Validate that the specific names were pushed
    Queue::assertPushed(ScrapeCategoryJob::class, function (ScrapeCategoryJob $job) {
        return $job->categoryName === 'React';
    });
    Queue::assertPushed(ScrapeCategoryJob::class, function (ScrapeCategoryJob $job) {
        return $job->categoryName === 'Vue';
    });
    Queue::assertPushed(ScrapeCategoryJob::class, function (ScrapeCategoryJob $job) {
        return $job->categoryName === 'Angular';
    });
});

it('executes the scraper synchronously to verify precise database counts', function () {
    // Simulate the AI returning exactly 15 courses (between 10 and 20)
    $simulatedCourses = [];
    for ($i = 1; $i <= 15; $i++) {
        $simulatedCourses[] = "Course {$i}";
    }

    // 1. Mock the External AI Service
    $aiMock = \Mockery::mock(\App\Services\AiCourseGeneratorService::class);
    $aiMock->shouldReceive('generateCourses')
        ->once()
        ->with('Advanced Programming')
        ->andReturn($simulatedCourses);
    app()->instance(\App\Services\AiCourseGeneratorService::class, $aiMock);

    // 2. Mock the YouTube Service (Expecting EXACTLY 2 playlists returned per course)
    $ytMock = \Mockery::mock(\App\Services\YouTubeScraperService::class);
    
    foreach ($simulatedCourses as $courseTitle) {
        $ytMock->shouldReceive('searchPlaylists')
            ->once()
            ->with($courseTitle)
            ->andReturn([
                [
                    'playlist_id' => 'PL_' . md5($courseTitle . '_1'),
                    'title' => $courseTitle . ' Playlist 1',
                    'description' => 'Testing description',
                    'thumbnail' => 'https://example.com/thumb1.jpg',
                    'channel_name' => 'Tech Channel',
                    'lessons_count' => 10,
                    'view_count' => 1000,
                    'duration_seconds' => 3600,
                ],
                [
                    'playlist_id' => 'PL_' . md5($courseTitle . '_2'),
                    'title' => $courseTitle . ' Playlist 2',
                    'description' => 'Testing description',
                    'thumbnail' => 'https://example.com/thumb2.jpg',
                    'channel_name' => 'Edu Channel',
                    'lessons_count' => 20,
                    'view_count' => 5000,
                    'duration_seconds' => 7200,
                ]
            ]);
    }
    app()->instance(\App\Services\YouTubeScraperService::class, $ytMock);

    // 3. Perform the Post Request - Because QUEUE_CONNECTION=sync, the job processes instantly
    $response = $this->post(route('scrape'), [
        'categories' => "Advanced Programming",
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    // 4. Assert rigorous database counts
    $this->assertDatabaseCount('categories', 1);
    
    // Assert 15 courses were created successfully
    $this->assertDatabaseCount('courses', 15);
    
    // Assert exactly 2 playlists per course were saved (15 * 2 = 30)
    $this->assertDatabaseCount('playlists', 30);

    // Verify Pivot Tables
    $this->assertDatabaseCount('category_course', 15);
    $this->assertDatabaseCount('course_playlist', 30);
});
