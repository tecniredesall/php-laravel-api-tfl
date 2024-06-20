<?php

namespace App\Http\Controllers\API\MOBILE;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class Buyers extends BaseController {

    private $permission = 6;

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
    public function index( Request $request ){
        try{
            return $this->sendSuccess( '', array( 'data' => \App\Buyers::mostrar( null, true, $request ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store( Request $request ){
       return $this->sendError( 'Bad Request', array(), 400 );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show( $id, Request $request ){
        try{
            $array = array();
            $cRows = 0;
            $isExistDate = ( isset( $request->user()->metadata[ 'temp_ini' ] ) && isset( $request->user()->metadata[ 'temp_out' ] ) ) ? 1 : 0;
            if( $isExistDate  ){
                $dIni = $request->user()->metadata[ 'temp_ini' ];
                $dOut = $request->user()->metadata[ 'temp_out' ];
            } else {
                $dIni = date( 'Y-m-d', strtotime( '01/01' ) );
                $dOut = date( 'Y-m-d', strtotime( '12/31' ) );
            }

            $string = isset( $request->q ) ? $request->q : '';
            $toString = isset( $request->toString ) ? $request->toString : 0;
            $sql = \App\TransactionsOut::with(array( 'commodities' ))
                ->whereHas( 'commodities', function( $qs ) use ( $string ){
                    if( $string != '' )
                        $qs->whereRaw( "LOWER(commodities.name) LIKE ?", [ '%' . strtolower( $string ) . '%' ] );
                })
                ->whereBetween( 'date_start', [ $dIni, $dOut ] )
                ->where( 'status', 2 )
                ->groupBy( 'commodity' )
                ->distinct()
                ->select( 'commodity' )
                ->get();
            foreach( $sql as $key => $val ){
                $rows = array();
                $query = \App\TransactionsOut::with(array( 'commodities' ))
                    ->whereBetween( 'date_start', [ $dIni, $dOut ] )
                    ->where( 'status', 2 )
                    ->where( 'commodity', $val->commodities[ 'id' ] )
                    ->where( 'buyer', $id )
                    ->groupBy( 'commodity' )
                    ->selectRaw( 'id, branch_id, COUNT(id) AS contador, SUM( netdrywt ) AS tNetdrywt' )
                    ->get();
                if( $query->count() > 0 ){
                    $rows[ 'id' ] = $val->commodities[ 'id' ];
                    $rows[ 'name' ] = $val->commodities[ 'name' ];
                    $rows[ 'icon_c' ] = $val->commodities[ 'metas' ][ 'icon_name' ];
                    foreach( $query as $k => $v ){
                        $row = array();
                        $row[ 'id' ] = $v->id;
                        $row[ 'name' ] = \DB::table( 'locations' )->where( 'id', $v->branch_id )->pluck( 'name' )[ 0 ];
                        $row[ 'loads' ] = \App\NumberFormat::defaultFormat( $v->contador );
                        if( $toString == 1 ){
                            $row[ 'lbs' ] = \App\NumberFormat::simpleFormatGeneral( $v->tNetdrywt ) . ' (' .  \App\NumberFormat::tonsFormatGeneral( $v->tNetdrywt ) . ')';
                        } elseif( $toString == 0 ){
                            $row[ 'lbs' ] = \App\NumberFormat::simpleFormatGeneral( $v->tNetdrywt );
                            $row[ 'lbs_mt' ] = \App\NumberFormat::tonsFormatGeneral( $v->tNetdrywt );
                        }
                        $rows[ 'items' ][] = $row;
                    }
                    $array[] = $rows;
                }
            }
            return $this->sendSuccess( '', array( 'data' => \App\Api::getPaginator( $array, 0, $request ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update( $id, Request $request ){
        return $this->sendError( 'Bad Request', array(), 400 );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id ){
        return $this->sendError( 'Bad Request', array(), 400 );
    }
}
