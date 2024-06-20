<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\RestorePasswords'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule( Schedule $schedule ){
        // $schedule
        //     ->command( 'run:sqs-local' )
        //     ->everyMinute()
        //     ->appendOutputTo( storage_path( 'logs/sqs-local.log' ) );

        // $schedule
        //     ->command( 'run:sqs-remote' )
        //     ->everyMinute()
        //     ->appendOutputTo( storage_path( 'logs/sqs-remote.log' ) );
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}