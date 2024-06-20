<?php


namespace App\SQS\Actions\Central;

class SQSCentralActionFactory
{
    public function create($sqsClient, $action){
        switch ($action){
            case 'createOrUpdateEmailUser':
                return new CreateOrUpdateEmailUserAction($sqsClient);
            default:
                return new DefaultAction($sqsClient);
        }
    }
}