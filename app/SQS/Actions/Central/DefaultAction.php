<?php


namespace App\SQS\Actions\Central;


use App\Api;
use App\SQS\Actions\Action;
use App\SQS\Clients\SqsBaseClient;
use Illuminate\Support\Facades\Log;

class DefaultAction extends Action
{
    public function __construct(SqsBaseClient $client)
    {
        parent::__construct($client);
    }

    public function invoke($json, $message, $isWriting, $who, $default_location)
    {
        try {
            $this->client->deleteMessage($message);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}