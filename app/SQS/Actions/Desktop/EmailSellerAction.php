<?php


namespace App\SQS\Actions\Desktop;


use App\Sellers;
use App\SQS;
use App\SQS\Actions\Action;
use Exception;
use Illuminate\Support\Facades\Log;

class EmailSellerAction extends Action
{
    public function invoke($json, $message, $isWriting, $who, $default_location)
    {
        try {
            $seller = Sellers::where('id', $json['id'])->firstOrFail();
            $actions = [
                'id' => $seller->id,
                'email' => $seller->email,
                'status' => $seller->status
            ];
            $array = [
                'group' => env('INSTANCE_ID'),
                'type' => 'REQUEST_CENTRAL',
                'action' => 'createOrUpdateEmailSeller',
                'destination' => '-',
                'message' => json_encode($actions, JSON_FORCE_OBJECT)
            ];

            SQS::send($array, 'remote', null);
            $this->client->deleteMessage($message);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $e->getMessage();
        }
    }
}