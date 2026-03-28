<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Laravel\Ai\Exceptions\ProviderOverloadedException;

class ScrapeCategoryJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 600;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 5;

    public function __construct(public string $categoryName)
    {
        //
    }

    public function handle(\App\Services\ScrapingOrchestrator $orchestrator): void
    {
        try {
            $orchestrator->processCategory($this->categoryName);
        } catch (ProviderOverloadedException $e) {
            // Gemini is temporarily overloaded (503). Release the job back
            // to the queue with an exponential backoff delay so we can retry
            // later without wasting an attempt or notifying the user of a fake failure.
            $delaySeconds = 60 * $this->attempts(); // 60s, 120s, 180s...

            \Illuminate\Support\Facades\Log::warning(
                "Gemini overloaded for category '{$this->categoryName}'. " .
                "Retrying in {$delaySeconds}s (attempt {$this->attempts()}/{$this->tries})."
            );

            $this->release($delaySeconds);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error("ScrapeCategoryJob failed for category: {$this->categoryName}. Error: " . $exception->getMessage());
        
        \App\Events\CategoryScrapeFailed::dispatch(
            $this->categoryName, 
            $exception->getMessage()
        );
    }
}

