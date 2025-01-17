<?php

namespace App\Console;

use App\Console\Commands\CreateGithubRelease;
use App\Console\Commands\CreateGithubReleaseTicket;
use App\Console\Commands\Environment\Update as EnvironmentUpdate;
use App\Console\Commands\Environment\UpdatePrepare as EnvironmentUpdatePrepare;
use App\Console\Commands\Mapping\Commit as MappingCommit;
use App\Console\Commands\Mapping\Merge as MappingMerge;
use App\Console\Commands\Mapping\Save as MappingSave;
use App\Console\Commands\Mapping\Restore as MappingRestore;
use App\Console\Commands\Release\GetCurrentRelease;
use App\Console\Commands\Release\GetReleaseBody;
use App\Console\Commands\Release\ReportRelease;
use App\Console\Commands\Release\Save as ReleaseSave;
use App\Console\Commands\StartSupervisor;
use App\Console\Commands\Test;
use App\Logic\Scheduler\DeleteExpiredDungeonRoutes;
use App\Logic\Scheduler\FindOutdatedThumbnails;
use App\Logic\Scheduler\SynchronizeMapping;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CreateGithubRelease::class,
        CreateGithubReleaseTicket::class,
        StartSupervisor::class,

        // Release
        GetCurrentRelease::class,
        GetReleaseBody::class,
        ReportRelease::class,
        ReleaseSave::class,

        // Environment
        EnvironmentUpdatePrepare::class,
        EnvironmentUpdate::class,

        // Mapping
        MappingCommit::class,
        MappingMerge::class,
        MappingSave::class,
        MappingRestore::class,

        // Test
        Test::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        Log::channel('scheduler')->debug('Starting scheduler');
        $schedule->call(new FindOutdatedThumbnails)->everyFiveMinutes();
        $schedule->call(new DeleteExpiredDungeonRoutes)->hourly();
        if (env('APP_TYPE') === 'mapping') {
            $schedule->call(new SynchronizeMapping)->everyFiveMinutes();
        }
        Log::channel('scheduler')->debug('Finished scheduler');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
