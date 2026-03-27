<?php

use App\Jobs\ScrapeCategoryJob;
use App\Services\ScrapingOrchestrator;

it('calls the orchestrator to process the given category', function () {
    // 1. Arrange: Create a mock for our ScrapingOrchestrator
    $orchestratorMock = \Mockery::mock(ScrapingOrchestrator::class);
    
    // 2. Expectation: The processCategory method MUST be called exactly once with 'VueJS'
    $orchestratorMock->shouldReceive('processCategory')
        ->once()
        ->with('VueJS')
        ->andReturnNull();

    // 3. Act: Instantiate the Job class purely in isolation
    $job = new ScrapeCategoryJob('VueJS');
    
    // Pass the mock orchestrator directly into the job's handle method
    $job->handle($orchestratorMock);

    // 4. Assert: Mockery automatically asserts that the mock method was called correctly
});
