<?php


namespace App\SQS;


use App\SQS\Clients\SQSClientFactory;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;

class SQSSender
{
    /**
     * send
     *
     * @param $content
     * @param $who
     * @param $default_location
     * @return \Illuminate\Http\JsonResponse
     */
    public function enviar($content, $who, $default_location)
    {
        try {
            $factory = new SQSClientFactory($default_location);
            $client = $factory->create($who); 
            
            try {
                
                $params = $client->createMessage($content);
                Log::info($client->getWriteQueue());

                
                if (isset($content['transaction_type'])) {
                    $params['MessageAttributes']['TRANSACTIONTYPE'] = ["DataType" => "String", "StringValue" => $content['transaction_type']];
                }
                $result = $client->sqsClient->sendMessage($params);
                return response()->json(['status' => true, 'title' => 'Ok', 'msg' => 'Send message has been successfully.', 'data' => $result], 200);
            } catch (\Exception $a) {
                Log::error($a);
                return response()->json(['status' => false, 'title' => 'Err', 'msg' => $a->getMessage()], 500);
            }
        } catch (AwsException $e) {
            Log::error($e);
            return response()->json(['status' => false, 'title' => 'Err', 'msg' => $e->getMessage()], 400);
        }
    }

    
    

    
}