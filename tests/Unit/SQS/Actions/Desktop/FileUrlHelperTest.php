<?php


namespace SQS\Actions\Desktop;

use App\Http\Controllers\API\MOBILE\OpenTickets;
use App\SQS\Actions\Desktop\FileUrlHelper;
use PHPUnit\Framework\TestCase;

class FileUrlHelperTest extends TestCase
{
    public function testGetUrlValidData(){
        $event = 'shippingTicketFileUrl';
        $type = 'shipping';
        $json = array();
        $json["ticketId"] =  1;
        $json["locationID"] =  1;

        $openTickets = \Mockery::mock(OpenTickets::class);
        $openTickets->shouldReceive('getFileUri')
            ->withArgs([$type, 1, 1])
            ->andReturn("https//silosys.grainchain.io");
        $sqsMock = \Mockery::mock('alias:\App\SQS');
        $sqsMock->shouldReceive('send')
            ->once()
            ->withAnyArgs();

        $sut = new FileUrlHelper($openTickets);
        $sut->sendSqsWithFileUrl($type, $json, $event);

        $this->expectNotToPerformAssertions();
    }

}