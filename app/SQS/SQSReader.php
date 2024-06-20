<?php


namespace App\SQS;

use App\SQS\Clients\SQSClientFactory;
use App\SQS\Actions\SQSActionFactory;
use Aws\Exception\AwsException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SQSReader
{
    private $sqsClientFactory;

    public function __construct(SQSClientFactory $sqsClientFactory)
    {
        $this->sqsClientFactory = $sqsClientFactory;
    }

    /**
     * receive
     *
     * @param $isWriting Boolean Indica si la cola es de escritura.
     * @param $type String Indica la procedencia del queue local/remote.
     * @param $default_location
     * @throws Exception
     */
    public function read($type)
    {
        try {
            Log::debug($type);
            $client = $this->sqsClientFactory->create($type);
            if (is_null($client)) {
                Log::info("Missing configuration for {$type}");
                return;
            }

            $queue = $client->getReadQueue();
            Log::info("Reading message from SQS {$type} : {$queue}");
            $result = $client->receiveMessage(
                array(
                    'AttributeNames' => ['All'],
                    'VisibilityTimeout' => 5,
                    'MaxNumberOfMessages' => 10,
                    'MessageAttributeNames' => ['*'],
                    'QueueUrl' => $queue,
                    'WaitTimeSeconds' => 10
                )
            );

            $messages = $result->get('Messages');
            if (count((array)$messages) > 0) {
                foreach ($messages as $key => $message) {
                    $json = json_decode($message['Body'], JSON_OBJECT_AS_ARRAY);
                    $debug = var_export($message, true);
                    Log::info("Processing message {$debug}");
                    $action = SQSActionFactory::create($client, $message);
                    Log::debug("Executing " . get_class($action));
                    $action->invoke($json, $message, $isWriting = null, $type, $default_location = null);
                }
            } else {
                Log::debug("No messages found");
            }
        } catch (AwsException $e) {
            Log::error($e->getMessage());
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}