<?php


namespace SQS\Actions\Desktop;

use App\SQS\Actions\Desktop\ReceiveTicketActionFileUrl;
use App\SQS\Clients\DefaultSqsClient;
use Illuminate\Contracts\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

class ReceiveTicketActionTest extends TestCase
{
    const PDF_PATH = "/SiloSysFiles/ReleaseCandidate";

    public function testReceiveTicketAction()
    {
        $sqs = \Mockery::mock('alias:\App\SQS');
        $sqs->shouldReceive("send")
            ->withAnyArgs();

        $transactionInModel = \Mockery::mock('alias:\App\TransactionsIn');
        $transactionInModel->date_end = date('Y/m/d');
        $transactionInModel->branchs['pdfpath'] = self::PDF_PATH;
        $transactionInModel->source_id = 16253;
        $transactionInModel
            ->shouldReceive("with")
            ->withAnyArgs()
            ->andReturnSelf()
            ->shouldReceive("where")
            ->withAnyArgs()
            ->andReturnSelf()
            ->shouldReceive("where")
            ->withAnyArgs()
            ->andReturnSelf()
            ->shouldReceive("firstOrFail")
            ->andReturn($transactionInModel);

        $fileSystemMock = \Mockery::mock(Filesystem::class);
        $fileSystemMock->shouldReceive("url")
            ->withAnyArgs()
            ->andReturn(env("INSTANCE_ID") . self::PDF_PATH);

        $storageMock = \Mockery::mock('alias:\Illuminate\Support\Facades\Storage');
        $storageMock
            ->shouldReceive("disk")
            ->withAnyArgs()
            ->andReturn($fileSystemMock);

        $sqsMock = $this->createMock(CasSqsClient::class);
        $sut = new ReceiveTicketActionFileUrl($sqsMock);
        $json["receiveTicket"]["ticketId"] = 16253;
        $json["receiveTicket"]["locationID"] = 1;
        $uri = $sut->invoke($json, null, null, null, null);
        $this->assertContains(self::PDF_PATH, $uri);
    }
}