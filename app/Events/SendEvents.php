<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SendEvents implements ShouldBroadcast {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reqId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct( $uId ){
        $this->reqId = $uId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(){
        return new PrivateChannel( 'user.' . $this->reqId );
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(){
        return 'sendinfo';
    }
}
