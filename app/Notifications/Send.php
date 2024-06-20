<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class Send extends Notification implements ShouldQueue {

    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(){}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via( $notifiable ){
        // return [ 'mail', 'database' ];
        // return [ 'database','broadcast' ];
        return [ 'database' ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail( $notifiable ){
        return ( new MailMessage )->line( 'The introduction to the notification.' )->action( 'Notification Action', url( '/' ) )->line( 'Thank you for using our application!' );
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack( $notifiable ){
        return ( new SlackMessage )->from( 'Laravel' )->success()->image( 'https://laravel.com/favicon.png' )->to( '#other' )->content( 'One of your invoices has been paid!' );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray( $notifiable ){
        return [
            //
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase( $notifiable ){
        return [
            'user' => $notifiable,
            'repliedTime' => \Carbon\Carbon::now()
        ];
    }
}