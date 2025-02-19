<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendResponse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:sqs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Run SQS's";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        dispatch( new \App\Jobs\ReceiveAndSendMessages() );
    }
}
