<?php


namespace App\SQS\Clients;

use App\Company_info;
use App\Locations;
use Aws\Sqs\SqsClient;
use ErrorException;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HarvxSqsClient extends SqsBaseClient
{
    private $awsRegion;
    private $readQueue, $writeQueue;

    public function __construct()
    {
        $this->awsRegion = env('AWS_REGION');
        if (!isset($this->awsRegion)) throw new ErrorException("Missing env variable 'AWS_REGION'");

        $this->sqsClient = self::connection();
        $queue = DB::table('queueconfiguration')->pluck('queue_url')[0];;
        $this->readQueue = $queue;
        $this->writeQueue = $queue;
    }

    public function getReadQueue()
    {
        return $this->readQueue;
    }

    public function getWriteQueue()
    {
        return $this->writeQueue;
    }

    public function createMessage($content)
    {
        return [
            'DelaySeconds' => 0,
            'MessageAttributes' => [
                "TYPE" => [
                    "DataType" => "String",
                    "StringValue" => $content['type']
                ],
                "ACTION" => [
                    "DataType" => "String",
                    "StringValue" => $content['action']
                ],
                "DESTINATION" => [
                    "DataType" => "String",
                    "StringValue" => $content['destination']
                ]
            ],
            'MessageBody' => $content['message'],
            'QueueUrl' => $this->getWriteQueue(),
        ];
    }

    /*
     * Crea una nueva instancia de client SQS
    */
    protected function connection()
    {
        try {
            $sqsConfig = DB::table('queueconfiguration')->first();
            if (!is_null($sqsConfig) && isset($sqsConfig->queue_acces_key)) {
                $credentials = [
                    'version' => 'latest',
                    'region' => $this->awsRegion,
                    'credentials' => [
                        'key' => $sqsConfig->queue_acces_key,
                        'secret' => $sqsConfig->queue_secret_key,
                    ]
                ];

                return new SqsClient($credentials);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}