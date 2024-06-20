<?php


namespace App\SQS\Actions\Response;

class SQSResponseActionFactory
{
    public function create($sqsClient, $action){
        switch ($action){
            case 'revertTicketReceive':
                return new RevertTicketAction($sqsClient);
            case 'CudRequest':
                return new CudRequestAction($sqsClient);
            case 'resetTank':
                return new ResetTakAction($sqsClient);
            default:
                return new DefaultAction($sqsClient);
        }
    }
}