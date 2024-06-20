<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class SbStocks extends BaseController
{
    private $dIni;
    private $dOut;
    private $filter = 0;
    private $fLocation = 0;
    private $toString = 1;

    public function __construct(){
        
    }

    private function callbackElements( $request ){
        $q = \App\Metadata_users::where( 'user_id', $request->user()->id )->get();
        if( $q->count() > 0 ){
            if( $q[0]->temp_ini != '' && $q[0]->temp_out ){
                $this->dIni = $q[ 0 ]->temp_ini;
                $this->dOut = $q[ 0 ]->temp_out;
            } else {
                $this->dIni = date( 'Y-m-d', strtotime( '01/01' ) );
                $this->dOut = date( 'Y-m-d', strtotime( '12/31' ) );
            }
        } else {
            $this->dIni = date( 'Y-m-d', strtotime( '01/01' ) );
            $this->dOut = date( 'Y-m-d', strtotime( '12/31' ) );
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( Request $request ){
        $array = array();
        $this->callbackElements( $request );
        $this->filter = isset( $request->filter ) ? $request->filter : 0;
        $this->fLocation = 1;
        $this->toString = isset( $request->toString ) ? $request->toString : 1;
        try{
            // if( $this->fLocation == 1 ){
                if( $this->filter == 0 )
                    $sql = \App\TransactionsIn::with(array( 'sellers', 'commodities' ))
                    ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                    ->where( 'status', 2 )
                    ->groupBy( 'commodity' )
                    ->distinct()
                    ->get();
                else
                    $sql = \App\TransactionsOut::with(array( 'buyers', 'commodities' ))
                    ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                    ->where( 'status', 2 )
                    ->groupBy( 'commodity' )
                    ->distinct()
                    ->get();
                foreach( $sql as $key => $val ){
                    $rows = array();
                    if( $this->filter == 0 )
                        $query = \App\TransactionsIn::with(array( 'sellers', 'commodities' ))
                            ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                            ->where( 'status', 2 )
                            ->where( 'commodity', $val->commodities[ 'id' ] )
                            ->groupBy( 'commodity' )
                            ->groupBy( 'seller' )
                            ->selectRaw( '*, COUNT(*) AS contador, SUM( netdrywt ) AS tNetdrywt' )
                            ->get();
                    else
                        $query = \App\TransactionsOut::with(array( 'buyers', 'commodities' ))
                            ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                            ->where( 'status', 2 )
                            ->where( 'commodity', $val->commodities[ 'id' ] )
                            ->groupBy( 'commodity' )
                            ->groupBy( 'buyer' )
                            ->selectRaw( '*, COUNT(*) AS contador, SUM( netdrywt ) AS tNetdrywt' )
                            ->get();
                    if( $query->count() > 0 ){
                        $rows[ $val->commodities[ 'name' ] ] = array();
                        foreach( $query as $k => $v ){
                            $row = array();
                            $row[ 'id' ] = $v->id;
                            if( $this->filter == 0 ){
                                $row[ 'sid' ] = !is_null($v->sellers) ? $v->sellers[ 'id' ] : '';
                                $row[ 'name' ] = !is_null($v->sellers) ? $v->sellers['name'] : 'N/A';
                            } else {
                                $row[ 'bid' ] = $v->buyers[ 'id' ];
                                $row[ 'name' ] = $v->buyers[ 'name' ];
                            }
                            $row[ 'loads' ] = \App\NumberFormat::defaultFormat( $v->contador );
                            if( $this->toString == 1 ){
                                $row[ 'lbs' ] = \App\NumberFormat::simpleFormatGeneral( $v->tNetdrywt ) . ' (' .  \App\NumberFormat::tonsFormatGeneral( $v->tNetdrywt ) . ')';
                            } elseif( $this->toString == 0 ){
                                $row[ 'lbs' ] = \App\NumberFormat::simpleFormatGeneral( $v->tNetdrywt );
                                $row[ 'lbs_mt' ] = \App\NumberFormat::tonsFormatGeneral( $v->tNetdrywt );
                            }
                            $row[ 'commodity' ] = $v->commodities[ 'name' ];
                            $rows[ $val->commodities[ 'name' ] ][] = $row;

                        }
                        $array[] = $rows;
                    }
                }
            return $this->sendSuccess( '', array( 'data' => $array ) );
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
        $array = array();
        $this->callbackElements( $request );
        $this->filter = isset( $request->filter ) ? $request->filter : 0;
        $this->fLocation = 1;
        $this->toString = isset( $request->toString ) ? $request->toString : 1;
        if( $id == 0 || is_null( $id ) || $id == '' )
            return $this->sendError( 'Bad Request', array(), 400 );
        try{
            if( $this->filter == 0 )
                $sql = \App\TransactionsIn::with(array( 'sellers', 'commodities' ))
                ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                ->where( 'status', 2 )
                // ->where( 'seller', $id )
                ->groupBy( 'commodity' )
                ->distinct()
                ->get();
            else
                $sql = \App\TransactionsOut::with(array( 'buyers', 'commodities' ))
                ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                ->where( 'status', 2 )
                // ->where( 'buyer', $id )
                ->groupBy( 'commodity' )
                ->distinct()
                ->get();
            foreach( $sql as $key => $val ){
                $rows = array();
                if( $this->filter == 0 )
                    $query = \App\TransactionsIn::with(array( 'commodities' ))
                        ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                        ->where( 'status', 2 )
                        ->where( 'commodity', $val->commodities[ 'id' ] )
                        ->where( 'seller', $id )
                        ->groupBy( 'commodity' )
                        // ->groupBy( 'seller' )
                        ->selectRaw( '*, COUNT(*) AS contador, SUM( netdrywt ) AS tNetdrywt' )
                        ->get();
                else
                    $query = \App\TransactionsOut::with(array( 'commodities' ))
                        ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                        ->where( 'status', 2 )
                        ->where( 'commodity', $val->commodities[ 'id' ] )
                        ->where( 'buyer', $id )
                        ->groupBy( 'commodity' )
                        // ->groupBy( 'buyer' )
                        ->selectRaw( '*, COUNT(*) AS contador, SUM( netdrywt ) AS tNetdrywt' )
                        ->get();
                if( $query->count() > 0 ){
                    $rows[ $val->commodities[ 'name' ] ] = array();
                    foreach( $query as $k => $v ){
                        $row = array();
                        $row[ 'id' ] = $v->id;
                        $row[ 'name' ] = \DB::table( 'locations' )->where( 'id', $v->branch_id )->pluck( 'name' )[ 0 ];
                        $row[ 'loads' ] = \App\NumberFormat::defaultFormat( $v->contador );
                        if( $this->toString == 1 ){
                            $row[ 'lbs' ] = \App\NumberFormat::simpleFormatGeneral( $v->tNetdrywt ) . ' (' .  \App\NumberFormat::tonsFormatGeneral( $v->tNetdrywt ) . ')';
                        } elseif( $this->toString == 0 ){
                            $row[ 'lbs' ] = \App\NumberFormat::simpleFormatGeneral( $v->tNetdrywt );
                            $row[ 'lbs_mt' ] = \App\NumberFormat::tonsFormatGeneral( $v->tNetdrywt );
                        }
                        $row[ 'commodity' ] = $v->commodities[ 'name' ];
                        $rows[ $val->commodities[ 'name' ] ][] = $row;
                    }
                    $array[] = $rows;
                }
            }
            return $this->sendSuccess( '', array( 'data' => $array ) );
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
    public function update( Request $request, $id ){
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
