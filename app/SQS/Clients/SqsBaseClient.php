<?php


namespace App\SQS\Clients;


use Illuminate\Support\Facades\Log;

abstract class SqsBaseClient
{
    public $sqsClient;

    public function receiveMessage($awsParams){
        if(!empty($awsParams)){
            return $this
                ->sqsClient
                ->receiveMessage($awsParams);
        }
    }

    public function deleteMessage($obj){
        try {
            $this->sqsClient->deleteMessage([
                'QueueUrl' => $this->getReadQueue(),
                'ReceiptHandle' => $obj['ReceiptHandle'],
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['status' => false, 'title' => 'Err', 'msg' => $e->getMessage()], 400);
        }
    }

    public abstract function createMessage($content);

    public abstract function getReadQueue();

    public abstract function getWriteQueue();

    protected  abstract function connection();
}