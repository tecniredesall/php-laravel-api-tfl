<?php


namespace App\SQS\Clients;

class SQSClientFactory
{
    private $location;

    public function __construct($location = null)
    {
        $this->location = $location;
    }

    public function create($clientType)
    {
        $client = null;
        if ($clientType == 'desktop') {
            $client = new DesktopSqsClient($this->location);
        } elseif ($clientType == 'harvx') {
            $client = new HarvxSqsClient();
        } elseif ($clientType == 'cas') {
            $client = new CasSqsClient();
        }

        return $client;
    }
}