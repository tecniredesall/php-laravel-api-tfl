<?php


namespace App\SQS\Actions\Response;

use App\SQS\Actions\Action;
use Illuminate\Support\Facades\Log;

class CudRequestAction extends Action
{
    const REMOVE = 3;

    public function invoke($json, $message, $isWriting, $who, $default_location)
    {
        try {
            if ($json['entity_name'] == "Sellers" or $json['entity_name'] == "Users") {
                $this->processSellersAndUsers($json);
            }

            $cudrequest_id = $json['cudrequest_id'];
            $date_completed = !is_null($json['date_was_completed']) ? str_replace("/", "-", $json['date_was_completed']) : null;
            $error_code = !is_null($json['error_code']) ? $json['error_code'] : null;
            $was_completed = $json['was_completed'];

            \App\Cudrequest::where('cudrequest_id', $cudrequest_id)
                ->update(['was_completed' => $was_completed, 'date_was_completed' => $date_completed, 'error_code' => $error_code]);

            return $this->client->deleteMessage($message);
        }catch (\Exception $e){
            Log::error($e->getMessage());
        }
    }

    /**
     * @param $json
     */
    private function processSellersAndUsers($json): void
    {
        if ($json['cudtype_id'] == self::REMOVE) {
            if ($json['entity_name'] == "Sellers")
                $action = 'removeSeller';
            else
                $action = 'removeUser';
        } else {
            if ($json['entity_name'] == "Sellers")
                $action = 'createOrUpdateEmailSeller';
            else
                $action = 'createOrUpdateEmailUser';
        }

        $actions = [
            'id' => json_decode($json['request'])->id,
            'email' => json_decode($json['request'])->email,
            'status' => json_decode($json['request'])->status
        ];

        $array = [
            'group' => env('INSTANCE_ID'),
            'type' => 'REQUEST_CENTRAL',
            'action' => $action,
            'destination' => '-',
            'message' => json_encode($actions, JSON_FORCE_OBJECT)
        ];
        \App\SQS::send($array, 'remote', null, null);
    }
}