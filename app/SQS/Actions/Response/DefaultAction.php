<?php


namespace App\SQS\Actions\Response;


use App\SQS\Actions\Action;
use Illuminate\Support\Facades\Log;

class DefaultAction extends Action
{
    public function invoke($json, $message, $isWriting, $who, $default_location)
    {
        $rows = array();
        $rows['msgId'] = $message['MessageId'];
        $rows['idHash'] = $message['ReceiptHandle'];
        $rows['senderId'] = $message['Attributes']['SenderId'];
        $rows['groupId'] = env('INSTANCE_ID');
        $rows['type'] = $message['MessageAttributes']['TYPE']['StringValue'];
        $rows['body'] = $json;
        if ($message['MessageAttributes']['STATUS']['StringValue'] == 'ERROR')
            $rows['code_error'] = $message['MessageAttributes']['ERROR_ID']['StringValue'];
        $array[] = $rows;
        try {
            $user = \App\Users::find($json['user_id']);
            broadcast(new \App\Events\SendEvents($json['user_id']));
            $user->notify(new \App\Notifications\Send());
            $devices = \App\Metadata_users::where('user_id', $json['user_id'])->get();
            foreach ($devices as $key => $val){
                if ($val->target_arn != '')
                    \App\SNS::send(self::getMessage($val['MessageAttributes']['ACTION']['StringValue']), $val->target_arn);
            }

            $this->client->deleteMessage($message);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * getMessage
     *
     * @return string
     * @var action
     */
    private function getMessage($action)
    {
        switch ($action) {
            case 'revertTicketReceive':
            case 'revertTicketShipping':
            case 'revertTicketCash':
                return 'Ticket has been reverted successfully';
                break;
            case 'resetTank':
                return 'Tank has been reseted successfully';
                break;
            default:
                break;
        }
    }
}