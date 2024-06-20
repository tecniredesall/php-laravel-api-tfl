<?php


namespace App\SQS\Actions\Desktop;


use App\Helpers\LanguageHelper;
use App\SQS\Actions\Action;
use App\Users;
use Illuminate\Support\Facades\Log;

class EmailUserAction extends Action
{
    public function invoke($json, $message, $isWriting, $who, $default_location)
    {
        try {
            var_dump($json);
            $langHelper = new LanguageHelper();
            $users = Users::where('id', $json['id'])->firstOrFail();
            $actions = [
                'id' => $users->id,
                'email' => $users->email,
                'status' => $users->status,
                'lang' => isset($json["lang"]) ? $langHelper->Normalize($json["lang"]) : "en",
            ];
            $array = [
                'group' => env('INSTANCE_ID'),
                'type' => 'REQUEST_CENTRAL',
                'action' => 'createOrUpdateEmailUser',
                'destination' => '-',
                'message' => json_encode($actions, JSON_FORCE_OBJECT)
            ];
            \App\SQS::send($array, 'remote', null, null);
            $this->client->deleteMessage($message);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $e->getMessage();
        }
    }
}