<?php


namespace SQS\Actions\Desktop;

use App\SQS\Actions\Desktop\InvitationEmailAction;
use App\SQS\Clients\CasSqsClient;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\TestCase;

class InvitationEmailActionTest extends TestCase
{
    public function testInvokeDefault()
    {
        $apiMock = \Mockery::mock('alias:\App\Api');
        $resultMock = $this->createMock(JsonResponse::class);
        $resultMock->method("getStatusCode")->withAnyParameters()->willReturn(200);
        $apiMock->shouldReceive("sendResetPass")->withAnyArgs()->andReturn($resultMock);

        $sqsMock = $this->createMock(CasSqsClient::class);
        $sut = new InvitationEmailAction($sqsMock);
        $json = array();
        $json["id"] =  1;
        $json["email"] =  "test@grainchain.io";
        $json["model"] =  "Sellers";
        $json["event"] =  "Update";
        $json["app"] =  "WEB";
        $json["lang"] =  "es";
        $sut->invoke($json, [], null, null, null);
        $this->assertInstanceOf(InvitationEmailAction::class, $sut);
    }
}