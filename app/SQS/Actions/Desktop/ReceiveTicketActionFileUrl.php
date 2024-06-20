<?php


namespace App\SQS\Actions\Desktop;

use App\Http\Controllers\API\MOBILE\OpenTickets;
use App\SQS;
use App\SQS\Actions\Action;
use App\TransactionsIn;
use Exception;
use Illuminate\Support\Facades\Log;

class ReceiveTicketActionFileUrl extends Action
{
    const RECEIVE_TICKET = 'receiveTicket';

    public function invoke($json, $message, $isWriting, $who, $default_location)
    {
        try {
            $type = 'receive';
            $sourceId = $json[self::RECEIVE_TICKET]['ticketId'];
            $branchId = $json[self::RECEIVE_TICKET]['locationID'];

            $months_name_short = [
                'Jan' => 'Ene',
                'Feb' => 'Feb',
                'Mar' => 'Mar',
                'Apr' => 'Abr',
                'May' => 'May',
                'Jun' => 'Jun',
                'Jul' => 'Jul',
                'Aug' => 'Ago',
                'Sep' => 'Sep',
                'Oct' => 'Oct',
                'Nov' => 'Nov',
                'Dec' => 'Dic'
            ];

            //rebuild PATH
            $folder = $type;
            $t = TransactionsIn::with('branchs')
            ->where('source_id', $sourceId)
            ->where('branch_id', $branchId)
            ->firstOrFail();

            $date = new \Carbon\Carbon($t->date_end);

            $actuallyMonth = (env('APP_LANG') == 'ES') ? $months_name_short[ucwords(strtolower($date->format('M')))] : ucwords(strtolower($date->format('M')));
            $path = '/' . $t->branchs['pdfpath'] . '/' . $date->format('Y') . '/' . $date->format('m') . '-' . $actuallyMonth . '/' . $date->format('d') . '/' . $folder;
            $nameFile = str_pad($t->source_id, 8, '0', STR_PAD_LEFT) . '.pdf';
            $file = $path . '/' . $nameFile;
            $separator = substr( $file, 0, 1) === "/" ? '' : '/';

            $fileS3 = env('INSTANCE_ID') . $separator  . $file;
           
            $json[self::RECEIVE_TICKET]["ticketURL"] = $fileS3;
            $array = $this->CreateMessageSQS($json, $branchId);
            SQS::send($array, 'local', $branchId, null);
            $this->client->deleteMessage($message);
            return $json[self::RECEIVE_TICKET]["ticketURL"];
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * @param $json
     * @param $branchId
     * @return array
     */
    public function CreateMessageSQS($json, $branchId): array
    {
        return [
            'group' => 'provenance',
            'type' => 'REQUEST',
            'action' => 'receiveTicketFileUrl',
            'destination' => strval($branchId),
            'message' => json_encode($json, JSON_FORCE_OBJECT)
        ];
    }
}