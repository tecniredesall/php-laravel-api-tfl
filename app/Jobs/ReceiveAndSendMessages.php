<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ReceiveAndSendMessages implements ShouldQueue {
    
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 900;

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    //public function retryUntil(){
    //    return now()->addSeconds( 5 );
    //}

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(){}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){
        dispatch( new \App\Jobs\lSqs() );
        dispatch( new \App\Jobs\rSqs() );
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    // public function failed( Exception $exception ){}
}
