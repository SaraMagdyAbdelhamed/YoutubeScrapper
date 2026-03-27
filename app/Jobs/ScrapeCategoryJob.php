<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
    public $tries = 3;

    public function __construct(public string $categoryName)
    {
        //
    }

    public function handle(\App\Services\ScrapingOrchestrator $orchestrator): void
    {
        $orchestrator->processCategory($this->categoryName);
    }
}
