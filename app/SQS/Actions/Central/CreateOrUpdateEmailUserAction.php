<?php


namespace App\SQS\Actions\Central;


use App\Api;
use App\Helpers\LanguageHelper;
use App\SQS\Actions\Action;
use App\SQS\Clients\SqsBaseClient;
use Exception;
use Illuminate\Support\Facades\Log;

class CreateOrUpdateEmailUserAction extends Action
{
    const SEND_EMAIL = 1;

    public function __construct(SqsBaseClient $client)
    {
        parent::__construct($client);
    }

    public function invoke($json, $message, $isWriting, $who, $default_location)
    {
        try {
            $langHelper = new LanguageHelper();
            Api::sendResetPass(
                $json["id"],
                "Users",
                self::SEND_EMAIL,
                $json["email"],
                isset($json["lang"]) ? $langHelper->Normalize($json["lang"]) : "en",
                "NEW_USER_WEB"
            );

            $this->client->deleteMessage($message);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

    }
}