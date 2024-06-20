<?php


namespace App\SQS\Actions;

use App\SQS\Clients\SqsBaseClient;

abstract class Action
{
    public $client;

    public function __construct(SqsBaseClient $client)
    {
        $this->client = $client;
    }

    public abstract function invoke($json, $message, $isWriting, $who, $default_location);
}