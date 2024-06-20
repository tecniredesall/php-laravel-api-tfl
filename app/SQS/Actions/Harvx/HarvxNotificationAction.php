<?php


namespace App\SQS\Actions\Harvx;


use App\SQS\Actions\Action;
use Illuminate\Support\Facades\Log;

class HarvxNotificationAction extends Action
{
    public function invoke($json, $message, $isWriting, $who, $default_location)
    {
        try {
            $location_id = $json["locationId"];
            $array = [
                'group' => env('INSTANCE_ID'),
                'type' => 'REQUEST',
                'action' => $message['MessageAttributes']['ACTION']['StringValue'],
                'destination' => $json["locationId"],
                'message' => json_encode($json)
            ];

            \App\SQS::send($array, 'local', $location_id, null);
            $this->client->deleteMessage($message);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $e->getMessage();
        }

    }
}