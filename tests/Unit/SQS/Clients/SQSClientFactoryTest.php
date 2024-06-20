<?php

namespace Tests\Unit\SQS\Clients;

use App\SQS\Clients\CasSqsClient;
use App\SQS\Clients\HarvxSqsClient;
use PHPUnit\Framework\TestCase;
use App\SQS\Clients\SQSClientFactory;

class SQSClientFactoryTest extends TestCase
{
    /**
     * Crear una instancia de CasSqsClient.
     *
     * @return void
     */
    public function testCreateCasSqsClient()
    {
        putenv('AWS_QUEUE_URL_CENTRAL_SENDING=aws');
        putenv('AWS_QUEUE_URL_CENTRAL=aws');
        putenv('AWS_REGION=aws');
        putenv('AWS_ACCESS_KEY_ID=aws');
        putenv('AWS_SECRET_ACCESS_KEY=aws');

        $sut = SQSClientFactory::create(null);
        $this->assertInstanceOf(CasSqsClient::class, $sut);
    }

    /**
     * Crear una instancia de DefaultSqsClient.
     *
     * @return void
     */
//    public function testHarvxSqsClient()
//    {
//        putenv('AWS_REGION=aws');
//        DB::shouldReceive('table')
//            ->withAnyArgs()
//            ->andReturnSelf()
//            ->shouldReceive('pluck')
//            ->withAnyArgs()
//            ->andReturn([['queue_acces_key' => 'dummy', 'queue_secret_key' => 'dummy']]);
//        $sut = SQSClientFactory::create(true);
//        $this->assertInstanceOf(HarvxSqsClient::class, $sut);
//    }
}
