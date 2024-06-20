<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class Seasons extends BaseController {

    // private $permission = 6;

    /**
     * Enable this module.
     *
     * @return \Illuminate\Http\Response
     */
    // public function __construct(){
    //     $this->middleware( 'candoit:' . $this->permission );
    // }

    public function change( Request $request ){
        $year = $request->selTemp;
        if( $year > date( 'Y' ) )
            return $this->sendError( 'Invalid year for the season', array(), 400 );
        try{
            $q = \App\Metadata_users::where( 'user_id', $request->user()->id )->get();
            if( $q->count() > 0 )
                $obj = \App\Metadata_users::find( $q[ 0 ]->id );
            else
                $obj = new \App\Metadata_users;
            $obj->user_id = $request->user()->id;
            $obj->temp_ini = $year . '-01-01';
            $obj->temp_out = $year . '-12-31';
            if( $q->count() > 0 )
                $obj->push();
            else
                $obj->save();
            return $this->sendSuccess( 'Season has been changed successfully', array() );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }
}
