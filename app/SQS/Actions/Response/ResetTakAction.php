<?php


namespace App\SQS\Actions\Response;

use App\SQS\Actions\Action;
use App\SQS\Clients\SqsBaseClient;
use Illuminate\Support\Facades\Log;

class ResetTakAction extends Action
{
    public function invoke($json, $message, $isWriting, $who, $default_location)
    {
        try {
            return $this->client->deleteMessage($message);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
        }
    }
}