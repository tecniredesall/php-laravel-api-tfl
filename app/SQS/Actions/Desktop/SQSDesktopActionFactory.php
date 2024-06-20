<?php


namespace App\SQS\Actions\Desktop;
use Exception;

class SQSDesktopActionFactory
{
    public function create($sqsClient, $action){
        switch ($action){
            case 'createOrUpdateEmailSeller':
                return new EmailSellerAction($sqsClient);
            case 'createOrUpdateEmailUser':
                return new EmailUserAction($sqsClient);
            case 'sendInvitationEmail':
                return new InvitationEmailAction($sqsClient);
            case 'sendTestEmail':
                return new TestEmailAction($sqsClient);
            case 'receiveTicketFileUrl':
                return new ReceiveTicketActionFileUrl($sqsClient);
            case 'revertTicketFileUrl':
                return new RevertTicketFileUrlAction($sqsClient);
            case 'shippingTicketFileUrl':
                return new ShippingTicketFileUrlAction($sqsClient);
            default:
                throw new Exception(printf("La acción '%s' no está implementada en 'REQUEST_NET'", $action));
        }
    }
}