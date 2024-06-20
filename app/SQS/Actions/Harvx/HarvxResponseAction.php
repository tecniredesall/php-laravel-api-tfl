<?php


namespace App\SQS\Actions\Harvx;


use App\SQS\Actions\Action;
use App\SQS\Clients\SqsBaseClient;
use Illuminate\Support\Facades\Log;

class HarvxResponseAction extends Action
{
    public function invoke($json, $message, $isWriting, $who, $default_location)
    {
        try {

            $commodityGeneralId = \App\Commodities::where('commodity_general_id', $json["commodityGeneralId"])->first();
            if ($commodityGeneralId !== null) {
                \App\SQS::send(["status" => false, "title" => "Error", "code" => 406, "errorCode" => 433], 'local', null, true);
            }

            $json["instanceId"] = env('INSTANCE_ID');
            $ticket_array = [];
            $location_id = $json["locationId"];

            foreach ($json["tickets"] as $ticket) {
                $type = $ticket["ticketType"];
                $central_id = $ticket["ticketId"];
                $transactionsIn = \App\TransactionsIn::where('source_id', $central_id)->where('status', 9)->first();
                if ($transactionsIn !== null) {
                    \App\SQS::send(["status" => false, "title" => "Error", "code" => 406, "errorCode" => 439], 'local', null, true);
                }

                $ticket["ticketURL"] = $central_id > 0 ? env('APP_URL') . "/api/mobile/ticket/download/" . $type . "/" . $central_id . "/" . $location_id : "";
                $ticket_array[] = $ticket;
            }
            $json["tickets"] = $ticket_array;
            $objSQS = new \stdClass();
            $objSQS->event = 'receiving-ticket.new';
            $objSQS->payload = $json;

            $array = [
                'type' => 'REQUEST',
                'action' => 'RequestSiloSysHarvexTicket',
                'destination' => $json["locationId"],
                'message' => json_encode($objSQS)
            ];

            \App\SQS::send($array, 'local', null, true);
            $this->client->deleteMessage($message);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            report($e);
        }
    }
}