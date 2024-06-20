<?php


namespace App\SQS\Actions\Desktop;


use App\SQS\Actions\Action;
use App\SQS\Clients\SqsBaseClient;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestEmailAction extends Action
{
    protected $fromAddress;

    protected $fromName;

    protected $subject;

    public function __construct(SqsBaseClient $client)
    {
        parent::__construct($client);
        $this->fromAddress = env('MAIL_FROM_ADDRESS');
        $this->fromName = env('MAIL_FROM_NAME');
    }

    public function invoke($json, $message, $isWriting, $who, $default_location)
    {
        try {
            App::setLocale($json["lang"]);
            $subject = __("messages.email.test_email_subject");
            $body = __("messages.email.test_email_body");

            Mail::send('emails.testemail', array("text" => $body), function ($message) use ($json, $subject) {
                $message->from($this->fromAddress, $this->fromName);
                $message->subject($subject);
                $message->to($json["email"]);
            });

            $this->client->deleteMessage($message);
        }
       catch (Exception $e)
       {
           Log::error($e);
       }
    }
}