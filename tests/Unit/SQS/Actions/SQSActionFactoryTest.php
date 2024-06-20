<?php

namespace Tests\Unit\SQS\Actions;

use App\SQS\Actions\Desktop\EmailSellerAction;
use App\SQS\Actions\Desktop\EmailUserAction;
use App\SQS\Actions\Harvx\HarvxNotificationAction;
use App\SQS\Actions\Harvx\HarvxRequestAction;
use App\SQS\Actions\Harvx\HarvxResponseAction;
use App\SQS\Actions\Response\CudRequestAction;
use App\SQS\Actions\Response\DefaultAction;
use App\SQS\Actions\Response\ResetTakAction;
use App\SQS\Actions\Response\RevertTicketAction;
use App\SQS\Clients\SqsBaseClient;
use PHPUnit\Framework\TestCase;
use App\SQS\Actions\SQSActionFactory;
use Illuminate\Support\Facades\Log;

/*
 * Pruebas para validar que se crea la clase que procesa la acción de manera correcta para los mensajes AWS SQS.
 * */
class SQSActionFactoryTest extends TestCase
{
    const CLIENTEXCEPTION = "Debe especificar el cliente AWS SQS";

    const OBJECTPROPEXCEPTION = "El objeto no tiene una estructura válida";

    public function testEmptySqsClient()
    {
        $this->expectException(\TypeError::class);
        SQSActionFactory::create(null, null);
    }

    public function testEmptyVal()
    {
        $client = $this->createMock(SqsBaseClient::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(self::OBJECTPROPEXCEPTION);
        SQSActionFactory::create($client, null);
    }

    public function testTypeAndActionNotSpecified()
    {
        $this->expectException(\Exception::class);
        $client = $this->createMock(SqsBaseClient::class);
        $val = [
            "MessageAttributes" => [
            ]];

        SQSActionFactory::create($client, $val);
    }

    public function testTypeNotSpecified()
    {
        $client = $this->createMock(SqsBaseClient::class);
        $val = [
            "MessageAttributes" => [
                "TYPE" => ["StringValue" => "RequestSiloSysHarvexTicket"]
            ]];

        $action = SQSActionFactory::create($client, $val);
        $this->assertInstanceOf(HarvxRequestAction::class, $action);
    }

    public function testDesktopActionNotSpecified()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(self::OBJECTPROPEXCEPTION);
        $client = $this->createMock(SqsBaseClient::class);
        $val = array(
                "MessageAttributes" => array(
                    "TYPE" => array("StringValue" => "REQUEST_NET")
                )

        );

        SQSActionFactory::create($client, $val);
    }

    public function testCreateCudRequestAction()
    {
        $client = $this->createMock(SqsBaseClient::class);
        $val = array(
                "MessageAttributes" => array(
                    "TYPE" => array("StringValue" => "RESPONSE"),
                    "ACTION" => array("StringValue" => "CudRequest")
                )
        );

        $action = SQSActionFactory::create($client, $val);
        $this->assertInstanceOf(CudRequestAction::class, $action);
    }

    public function testCreateDefaultAction()
    {
        $client = $this->createMock(SqsBaseClient::class);
        $val = [
            "MessageAttributes" => [
                "TYPE" => ["StringValue" => "RESPONSE"]
            ]];

        $action = SQSActionFactory::create($client, $val);
        $this->assertInstanceOf(DefaultAction::class, $action);
    }

    public function testCreateResetTankAction()
    {
        $client = $this->createMock(SqsBaseClient::class);
        $val = [
            "MessageAttributes" => [
                "TYPE" => ["StringValue" => "RESPONSE"],
                "ACTION" => ["StringValue" => "resetTank"]
            ]];

        $action = SQSActionFactory::create($client, $val);
        $this->assertInstanceOf(ResetTakAction::class, $action);
    }

    public function testCreateRevertTicketAction()
    {
        $client = $this->createMock(SqsBaseClient::class);
        $val = [
            "MessageAttributes" => [
                "TYPE" => ["StringValue" => "RESPONSE"],
                "ACTION" => ["StringValue" => "revertTicketReceive"]
            ]];

        $action = SQSActionFactory::create($client, $val);
        $this->assertInstanceOf(RevertTicketAction::class, $action);
    }

    public function testCreateHarvexResponseAction()
    {
        $client = $this->createMock(SqsBaseClient::class);
        $val = [
            "MessageAttributes" => [
                "TYPE" => ["StringValue" => "ResponseSiloSysHarvexTicket"]
            ]];

        $action = SQSActionFactory::create($client, $val);
        $this->assertInstanceOf(HarvxResponseAction::class, $action);
    }

    public function testCreateHarvexRequestAction()
    {
        $client = $this->createMock(SqsBaseClient::class);
        $val = [
            "MessageAttributes" => [
                "TYPE" => ["StringValue" => "RequestSiloSysHarvexTicket"]
            ]];

        $action = SQSActionFactory::create($client, $val);
        $this->assertInstanceOf(HarvxRequestAction::class, $action);
    }

    public function testCreateHarvexNotificationAction()
    {
        $client = $this->createMock(SqsBaseClient::class);
        $val = [
            "MessageAttributes" => [
                "ACTION" =>  ["StringValue" => "HarvXNotification"],
            ]];

        $action = SQSActionFactory::create($client, $val);
        $this->assertInstanceOf(HarvxNotificationAction::class, $action);
    }


    public function testCreateEmailSellerAction()
    {
        $client = $this->createMock(SqsBaseClient::class);
        $val = [
            "MessageAttributes" => [
                "TYPE" => ["StringValue" => "REQUEST_NET"],
                "ACTION" =>  ["StringValue" => "createOrUpdateEmailSeller"],
            ]];

        $action = SQSActionFactory::create($client, $val);
        $this->assertInstanceOf(EmailSellerAction::class, $action);
    }

    public function testCreateEmailUserAction()
    {
        $client = $this->createMock(SqsBaseClient::class);
        $val = [
            "MessageAttributes" => [
                "TYPE" => ["StringValue" => "REQUEST_NET"],
                "ACTION" =>  ["StringValue" => "createOrUpdateEmailUser"],
            ]];

        $action = SQSActionFactory::create($client, $val);
        $this->assertInstanceOf(EmailUserAction::class, $action);
    }

    public function testCreateCentralAction()
    {
        $client = $this->createMock(SqsBaseClient::class);
        $val = [
            "MessageAttributes" => [
                "TYPE" => ["StringValue" => "RESPONSE_CENTRAL"]
            ]];

        $action = SQSActionFactory::create($client, $val);
        $this->assertInstanceOf(\App\SQS\Actions\Central\DefaultAction::class, $action);
    }
}
