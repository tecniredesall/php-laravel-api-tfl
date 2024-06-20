<?php


namespace App\SQS\Clients;


use App\Company_info;
use App\Locations;
use Aws\Exception\AwsException;
use Aws\Sqs\SqsClient;
use ErrorException;
use Illuminate\Support\Facades\Log;


class CasSqsClient extends SqsBaseClient
{
    private $AwsQueueUrlCentralSending;
    private $AwsQueueUrlCentral;
    private $AwsRegion;
    private $AwsAccessKeyId;
    private $AwsSecretAccessKey;

    public function __construct()
    {
        //Renombrar clase a CasSQSClient
        $this->AwsQueueUrlCentralSending = env('AWS_QUEUE_URL_CENTRAL_SENDING'); //In develop environment -> 	test-anyone-to-cas.fifo
        if (!isset($this->AwsQueueUrlCentralSending)) throw new ErrorException("Missing env variable 'AWS_QUEUE_URL_CENTRAL_SENDING'");

        $this->AwsQueueUrlCentral = env('AWS_QUEUE_URL_CENTRAL'); //In develop environment -> 	test-cas-to-mexico.fifo
        if (!isset($this->AwsQueueUrlCentral)) throw new ErrorException("Missing env variable 'AWS_QUEUE_URL_CENTRAL'");

        $this->AwsRegion = env('AWS_REGION');
        if (!isset($this->AwsRegion)) throw new ErrorException("Missing env variable 'AWS_REGION'");

        $this->AwsAccessKeyId = env('AWS_ACCESS_KEY_ID');
        if (!isset($this->AwsAccessKeyId)) throw new ErrorException("Missing env variable 'AWS_ACCESS_KEY_ID'");

        $this->AwsSecretAccessKey = env('AWS_SECRET_ACCESS_KEY');
        if (!isset($this->AwsSecretAccessKey)) throw new ErrorException("Missing env variable 'AWS_SECRET_ACCESS_KEY'");

        $this->sqsClient = self::connection();
    }

    public function getReadQueue()
    {
        return $this->AwsQueueUrlCentral;
    }

    public function getWriteQueue()
    {
        return $this->AwsQueueUrlCentralSending;
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

    protected function connection()
    {
        try {
            $credentials = [
                'version' => 'latest',
                'region' => $this->AwsRegion,
                'credentials' => [
                    'key' => $this->AwsAccessKeyId,
                    'secret' => $this->AwsSecretAccessKey,
                ]
            ];

            return new SqsClient($credentials);
        } catch (\Exception $e) {
            return null;
        }
    }
}