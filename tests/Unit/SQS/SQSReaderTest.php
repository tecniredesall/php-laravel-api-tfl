<?php

namespace Tests\Unit\SQS;

use App\SQS\Clients\CasSqsClient;
use PHPUnit\Framework\TestCase;
use App\SQS\SQSReader;
use App\SQS\Clients\SQSClientFactory;
use Aws\Result;

class SQSReaderTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateAction()
    {
        $sqsClient = $this->createMock(CasSqsClient::class);
        $receiveResult = $this->createMock(Result::class);
        $receiveResult
            ->method("get")
            ->with("Messages")
            ->willReturn(array(
                array(
                    "MessageAttributes" => array(
                        "TYPE" => array("StringValue" => "RESPONSE"),
                        "ACTION" => array("StringValue" => "revertTicketReceive")
                    ),
                    "Body" => '{ "id": 1 }')
                )
            );

        $sqsClient
            ->method("receiveMessage")
            ->withAnyParameters()
            ->willReturn($receiveResult);

        $sqsClient->method("getQueueURL")->willReturn("");

        $factoryMock = $this
            ->createMock(SQSClientFactory::class);

        $factoryMock->method("create")
            ->with(null)
            ->willReturn($sqsClient);


        $sut = new SQSReader($factoryMock);

        //$result = $sut->read(false, "who", null);
        $this->assertNotEmpty($sut);
    }
}
