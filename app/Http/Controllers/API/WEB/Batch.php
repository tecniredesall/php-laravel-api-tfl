<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class Batch extends BaseController {
    private $permission = 8;

    /**
     * Enable this module.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->middleware( 'candoit:' . $this->permission );
    }

    public function index( Request $request){
        try{
            return $this->sendSuccess( '', [ 'data' => \App\Batch::batchContract( $request ) ] );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function batchTicket( $batch_id, Request $request ){
        try{
            return $this->sendSuccess( '', [ 'data' => \App\Batch::batchTicket( $batch_id, $request) ] );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function generateFiles( Request $request){
        try{
            if($request->type_file === 'csv')
                return \App\Batch::generateFiles( $request );
            return $this->sendSuccess( '', array( 'data' => \App\Batch::generateFiles( $request ) ) );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function sendmailPrepprove( Request $request){
        try{
            return $this->sendSuccess( '', array( 'data' => \App\Batch::sendmailPrepprove( $request ) ) );

        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function featuresValue( $batch_id, Request $request){
        try{
            return $this->sendSuccess( '', \App\Batch::featuresValue( $batch_id, $request ) );

        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function ticketsBatch( Request $request ){
        try{
            return \App\Batch::ticketsBatch( $request );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function attachTicketsBatch( Request $request ){
        try{
            return \App\Batch::attachTicketsBatch( $request );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function deleteTicketsBatch( Request $request ){
        try{
            return \App\Batch::deleteTicketsBatch( $request );

        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function batchDetail( $batch_id, Request $request ){
        try{
            return $this->sendSuccess( '', array( 'data' => \App\Batch::batchDetail( $batch_id, $request )) );

        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function changeStatusBatch( Request $request ){
        try{
            return \App\Batch::changeStatusBatch( $request );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function deleteBatch( $batch_id){
        try{
            return \App\Batch::deleteBatch( $batch_id );

        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }




}
