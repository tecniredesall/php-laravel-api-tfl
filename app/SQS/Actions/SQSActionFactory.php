<?php


namespace App\SQS\Actions;

use App\SQS\Actions\Central\SQSCentralActionFactory;
use \App\SQS\Actions\Desktop\SQSDesktopActionFactory;
use \App\SQS\Actions\Response\SQSResponseActionFactory;
use \App\SQS\Actions\Harvx\HarvxRequestAction;
use \App\SQS\Actions\Harvx\HarvxResponseAction;
use \App\SQS\Actions\Harvx\HarvxNotificationAction;
use App\SQS\Clients\SqsBaseClient;
use Exception;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

class SQSActionFactory
{
    public static function create(SqsBaseClient $sqsClient, $message)
    {

        if (!is_object($sqsClient)) {
            throw new InvalidArgumentException("Debe especificar el cliente AWS SQS");
        }

        if (!is_array($message)) {
            throw new InvalidArgumentException("El objeto no tiene una estructura válida");
        }

        $type = "";
        if (isset($message['MessageAttributes']['TYPE']['StringValue'])) {
            $type = $message['MessageAttributes']['TYPE']['StringValue'];
        }

        $action = self::getAction($message);
        //Log::info(printf("Processing queue message: TYPE=%s | ACTION= %s\n", $type, $action));
        switch ($type) {
            case 'RESPONSE':
                $factory = new SQSResponseActionFactory();
                return $factory->create($sqsClient, $action);
            case 'REQUEST_NET':
                if ($action == "") {
                    throw new InvalidArgumentException("El objeto no tiene una estructura válida");
                }

                $factory = new SQSDesktopActionFactory();
                return $factory->create($sqsClient, $action);
            case 'RESPONSE_CENTRAL':
                $factory = new SQSCentralActionFactory();
                return $factory->create($sqsClient, $action);
            case 'RequestSiloSysHarvexTicket':
                return new HarvxRequestAction($sqsClient);
            case 'ResponseSiloSysHarvexTicket':
                return new HarvxResponseAction($sqsClient);
            default:
                if ($action == "HarvXNotification") {
                    return new HarvxNotificationAction($sqsClient);
                }

                throw new Exception(printf("El tipo de mensaje '%s' no está soportado", $type));
        }
    }

    private static function getAction($val)
    {
        $action = "";
        if (isset($val['MessageAttributes']['ACTION']['StringValue'])) {
            $action = $val['MessageAttributes']['ACTION']['StringValue'];
        }

        return $action;
    }
}