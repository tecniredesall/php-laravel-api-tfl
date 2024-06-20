<?php


namespace App\SQS\Actions\Desktop;


use App\Http\Controllers\API\MOBILE\OpenTickets;
use App\SQS;

class FileUrlHelper
{
    private $openTicket;

    public function __construct(OpenTickets $openTicket)
    {
        $this->openTicket = isset($openTicket) ? $openTicket : new OpenTickets();
    }

    public function sendSqsWithFileUrl($type, $json, $responseEvent)
    {
        $sourceId = $json['ticketId'];
        $branchId = $json['locationID'];
        $json["ticketURL"] = $this->openTicket->getFileUri($type, $sourceId, $branchId);
        $array = $this->CreateSQS($json, $branchId, $responseEvent);
        SQS::send($array, 'local', $branchId , null);
    }

    /**
     * @param $json
     * @param $branchId
     * @return array
     */
    private function CreateSQS($json, $branchId, $responseEvent): array
    {
        return [
            'group' => 'provenance',
            'type' => 'REQUEST',
            'action' => $responseEvent,
            'destination' => strval($branchId),
            'message' => json_encode($json, JSON_FORCE_OBJECT)
        ];
    }
}