<?php


namespace App\SQS\Clients;

use App\Company_info;
use App\Locations;
use Aws\Sqs\SqsClient;
use ErrorException;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\DB;

class DesktopSqsClient extends SqsBaseClient
{
    private $awsRegion;
    private $readQueue, $writeQueue;

    public function __construct($location)
    {
        $this->awsRegion = env('AWS_REGION');
        if(!isset($this->awsRegion)) throw new ErrorException("Missing env variable 'AWS_REGION'");

        $this->sqsClient = self::connection();

        $this->readQueue = Company_info::pluck('sqs_url')[0];

        if(!is_null($location)){
            $this->writeQueue = Locations::find($location)->sqs_url;
        }
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
            'MessageGroupId' => $content['group'],
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
            'VisibilityTimeout' => 5,
            'QueueUrl' => $this->getWriteQueue(),
        ];
    }

    /*
     * Crea una nueva instancia de client SQS
    */
    protected function connection()
    {
        try {
            $credentials = [
                'version' => 'latest',
                'region' => $this->awsRegion,
                'credentials' => [
                    'key' =>  Company_info::pluck('sqs_key')[0],
                    'secret' => Company_info::pluck('sqs_secret')[0],
                ]
            ];

            return new SqsClient($credentials);
        } catch (\Exception $e) {
            return null;
        }
    }
}