<?php


namespace App\SQS\Actions\Desktop;


use App\Api;
use App\SQS\Actions\Action;
use Illuminate\Support\Facades\Log;

class InvitationEmailAction extends Action
{
    const RESET_PASSWORD = "RESET_PASSWORD";

    const NEW_USER_WEB = "NEW_USER_WEB";

    const NEW_USER_APP = "NEW_USER_APP";

    const SEND_EMAIL = 1;


    public function invoke($json, $message, $isWriting, $who, $default_location)
    {
        $eventType = $this->getEvent($json);
        try {
            Api::sendResetPass(
                $json["id"],
                $json["model"],
                self::SEND_EMAIL,
                $json["email"],
                $json["lang"],
                $eventType
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        } finally {
            $this->client->deleteMessage($message);
        }
    }

    /**
     * @param $json
     * @return String
     */
    private function getEvent($json): string
    {
        // event = Create/Update
        $eventType = strcasecmp($json["event"], "update") == 0 ? self::RESET_PASSWORD : self::NEW_USER_WEB;
        if ($eventType != self::RESET_PASSWORD) {
            $eventType = strcasecmp($json["app"], "web") == 0 ? $eventType : self::NEW_USER_APP;
        }

        return $eventType;
    }
}