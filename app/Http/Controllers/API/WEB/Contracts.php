<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

class Contracts extends BaseController {
    private $permission = 8;

    /**
     * Enable this module.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->middleware( 'candoit:' . $this->permission );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( Request $request  ){
        try{
            return $this->sendSuccess( '', array( 'data' => \App\Contracts::mostrar( null, $request ) ) );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function related( $id ){
        try{
            return $this->sendSuccess( '', array( 'data' => \App\Contracts::relatedFeature( $id ) ) );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function contractFeature( Request $request ){
        try{
            return $this->sendSuccess( '', array( 'data' => \App\Contracts::contractFeature( $request ) ) );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function ticketsContract( Request $request){
        try{
            return $this->sendSuccess( '', [ 'data' => \App\Contracts::ticketsContract( $request ) ] );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function linkTickets( Request $request)
    {
        try {
            return $this->sendSuccess('', ['data' => \App\Contracts::linkTickets($request)]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }


    public function preapproveTickets( Request $request){
        try{
            return $this->sendSuccess( '', array( 'data' => \App\Contracts::preapproveTickets( $request ) ) );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function storeValuesFeatures( Request $request){

        try{
            return $this->sendSuccess( '', [ 'data' => \App\Contracts::storeValuesFeatures( $request ) ] );

        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

}
