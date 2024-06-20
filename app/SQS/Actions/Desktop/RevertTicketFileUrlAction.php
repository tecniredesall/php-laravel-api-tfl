<?php


namespace App\SQS\Actions\Desktop;

use App\Http\Controllers\API\MOBILE\OpenTickets;
use App\SQS;
use App\SQS\Actions\Action;
use Illuminate\Support\Facades\Log;

class RevertTicketFileUrlAction extends Action
{
    private const EVENT = 'revertTicketFileUrl';

    private const TYPE = 'receive';

    public function invoke($json, $message, $isWriting, $who, $default_location)
    {
        try {
            $fileHelper = new FileUrlHelper(new OpenTickets());
            $fileHelper->sendSqsWithFileUrl(self::TYPE, $json, self::EVENT);
            $this->client->deleteMessage($message);
            return $json["ticketURL"];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $e->getMessage();
        }
    }
}