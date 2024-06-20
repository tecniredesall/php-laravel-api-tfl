<?php

namespace App\Http\Controllers\API\MOBILE;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class SbStocks extends BaseController
{
    private $dIni;
    private $dOut;
    private $filter = 0;
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
    public function clients( Request $request ){
        $array = array();
        $this->callbackElements( $request );
        $this->filter = isset( $request->filter ) ? $request->filter : 0;
        $this->toString = isset( $request->toString ) ? $request->toString : 0;
        $string = isset( $request->q ) ? $request->q : '';
        $cRows = 0;
        try{
            if( $this->filter == 0 )
                $consulta = \App\TransactionsIn::with(array( 'sellers' ))
                    ->whereHas( 'sellers', function( $qs ) use ( $string ){
                        if( $string != '' )
                            $qs->whereRaw( "LOWER(sellers.name) LIKE ?", [ '%' . strtolower( $string ) . '%' ] );
                    })
                    ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                    ->where( 'status', 2 )
                    ->groupBy( 'seller' )
                    ->distinct();
            else
                $consulta = \App\TransactionsOut::with(array( 'buyers' ))
                    ->whereHas( 'buyers', function( $qs ) use ( $string ){
                        if( $string != '' )
                            $qs->whereRaw( "LOWER(buyers.name) LIKE ?", [ '%' . strtolower( $string ) . '%' ] );
                        $qs->where( 'buyers.status', '<>', 5);
                    })
                    ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                    ->where( 'transactions_out.status', 2 )
                    ->groupBy( 'buyer' )
                    ->distinct();
            $cRows = $consulta->get()->count();
            $skip = isset( $request->page ) && $request->page != 1 ? ( $request->page * env( 'PER_PAGE' ) ) - env( 'PER_PAGE' ) : 0;
            $sql = $consulta->skip( $skip )->take( env( 'PER_PAGE' ) )->select( ( $this->filter == 0 ) ? 'seller': 'buyer' )->get();
            foreach( $sql as $key => $val ){
                $rows = array();
                if( $this->filter == 0 )
                    $query = \App\TransactionsIn::with(array( 'commodities' ))
                        ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                        ->where( 'status', 2 )
                        ->where( 'seller', $val->sellers[ 'id' ] )
                        ->groupBy( 'commodity' )
                        ->selectRaw( 'commodity, SUM( netdrywt ) AS tNetdrywt' )
                        ->get();
                else
                    $query = \App\TransactionsOut::with(array( 'commodities' ))
                        ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                        ->where( 'status', 2 )
                        ->where( 'buyer', $val->buyers[ 'id' ] )
                        ->groupBy( 'commodity' )
                        ->selectRaw( 'commodity, SUM( netdrywt ) AS tNetdrywt' )
                        ->get();
                if( $query->count() > 0 ){
                    $rows[ 'id' ] = ( $this->filter == 0 ) ? $val->sellers[ 'id' ] : $val->buyers[ 'id' ];
                    $rows[ 'name' ] = ( $this->filter == 0 ) ? $val->sellers[ 'name' ] : $val->buyers[ 'name' ];
                    $rows[ 'items' ] = array();
                    $rows[ 'total' ] = 0;
                    $c = 0;
                    foreach( $query as $k => $v ){
                        // if( $v->commodity != 0 && ( !is_null( $v->buyers[ 'id' ] ) || !is_null( $v->sellers[ 'id' ] ) ) ){
                        $row = array();
                        $row[ 'id' ] = $v->commodities[ 'id' ];
                        $row[ 'name' ] = $v->commodities[ 'name' ]?$v->commodities[ 'name' ]:'';
                        $row[ 'icon' ] = !is_null($v->commodities[ 'metas' ])?$v->commodities[ 'metas' ][ 'icon_name' ]:'';
                        $row[ ( $this->filter == 0 ) ? 'sid' : 'bid' ] = ( $this->filter == 0 ) ? $val->sellers[ 'id' ] : $val->buyers[ 'id' ];
                        if( $this->toString == 1 ){
                            $row[ 'lbs' ] = \App\NumberFormat::simpleFormatGeneral( $v->tNetdrywt ) . ' (' .  \App\NumberFormat::tonsFormatGeneral( $v->tNetdrywt ) . ')';
                        } elseif( $this->toString == 0 ){
                            $row[ 'lbs' ] = \App\NumberFormat::simpleFormatGeneral( $v->tNetdrywt );
                            $row[ 'lbs_mt' ] = \App\NumberFormat::tonsFormatGeneral( $v->tNetdrywt );
                        }
                        // }
                        $rows[ 'items' ][] = $row;
                    }
                    $rows[ 'total' ] = "";
                    $rows[ 'icon' ] = "";
                    $array[] = $rows;
                }
            }
            return $this->sendSuccess( '', array( 'data' => \App\Api::getPaginator( $array, $cRows, $request ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
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
        $this->toString = isset( $request->toString ) ? $request->toString : 0;
        $string = isset( $request->q ) ? $request->q : '';
        try{
            if( $this->filter == 0 )
                $consulta = \App\TransactionsIn::with(array( 'commodities' ))
                    ->whereHas( 'commodities', function( $qs ) use ( $string ){
                        if( $string != '' )
                            $qs->whereRaw( "LOWER(commodities.name) LIKE ?", [ '%' . strtolower( $string ) . '%' ] );
                    })
                    ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                    ->where( 'status', 2 )
                    ->groupBy( 'commodity' )
                    ->distinct();
            else
                $consulta = \App\TransactionsOut::with(array( 'commodities' ))
                    ->whereHas( 'commodities', function( $qs ) use ( $string ){
                        if( $string != '' )
                            $qs->whereRaw( "LOWER(commodities.name) LIKE ?", [ '%' . strtolower( $string ) . '%' ] );
                    })
                    ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                    ->where( 'status', 2 )
                    ->groupBy( 'commodity' )
                    ->distinct();
            $cRows = $consulta->get()->count();
            $skip = isset( $request->page ) && $request->page != 1 ? ( $request->page * env( 'PER_PAGE' ) ) - env( 'PER_PAGE' ) : 0;
            $sql = $consulta->skip( $skip )->take( env( 'PER_PAGE' ) )->select( 'commodity' )->get();
            foreach( $sql as $key => $val ){
                $rows = array();
                if( $this->filter == 0 )
                    $query = \App\TransactionsIn::with(array( 'sellers' ))
                        ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                        ->where( 'status', 2 )
                        ->where( 'commodity', $val->commodities[ 'id' ] )
                        ->groupBy( 'commodity' )
                        ->groupBy( 'seller' )
                        ->selectRaw( 'id, seller, commodity, SUM( netdrywt ) AS tNetdrywt' );
                else
                    $query = \App\TransactionsOut::with(array( 'buyers' ))
                        ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                        ->where( 'status', 2 )
                        ->where( 'commodity', $val->commodities[ 'id' ] )
                        ->groupBy( 'commodity' )
                        ->groupBy( 'buyer' )
                        ->selectRaw( 'id, buyer, commodity, SUM( netdrywt ) AS tNetdrywt' );
                $total = $query->get()->count();
                $qury = $query->get();
                if( $total > 0 ){
                    $rows[ 'id' ] = $val->commodities[ 'id' ];
                    $rows[ 'name' ] = $val->commodities[ 'name' ];
                    $rows[ 'icon' ] = !is_null($val->commodities[ 'metas' ])? $val->commodities[ 'metas' ][ 'icon_name' ] : 'others';
                    $rows[ 'items' ] = array();
                    $rows[ 'total' ] = 0;
                    $c = 0;
                    foreach( $qury as $k => $v ){
                        $row = array();
                        // if( $v->commodity != 0 && ( !is_null( $v->buyers[ 'id' ] ) || !is_null( $v->sellers[ 'id' ] ) ) ){
                        $row[ 'id' ] = $v->id;
                        if( $this->filter == 0 ){
                            $row[ 'sid' ] = !is_null($v->sellers) ? $v->sellers[ 'id' ] : '';
                            $row[ 'name' ] = !is_null($v->sellers) ? $v->sellers['name'] : 'N/A';
                        } else {
                            $row[ 'bid' ] = !is_null($v->buyers) ? $v->buyers[ 'id' ] : '';
                            $row[ 'name' ] = !is_null($v->buyers) ? $v->buyers[ 'name' ] : '';
                        }
                        if( $this->toString == 1 ){
                            $row[ 'lbs' ] = \App\NumberFormat::simpleFormatGeneral( $v->tNetdrywt ) . ' (' .  \App\NumberFormat::tonsFormatGeneral( $v->tNetdrywt ) . ')';
                        } elseif( $this->toString == 0 ){
                            $row[ 'lbs' ] = \App\NumberFormat::simpleFormatGeneral( $v->tNetdrywt );
                            $row[ 'lbs_mt' ] = \App\NumberFormat::tonsFormatGeneral( $v->tNetdrywt );
                        }
                        //}
                        $c = $c + $v->tNetdrywt;
                        $rows[ 'items' ][] = $row;
                    }
                    $rows[ 'total' ] = \App\NumberFormat::totalFormat( $c );
                    $array[] = $rows;
                }
            }
            return $this->sendSuccess( '', array( 'data' => \App\Api::getPaginator( $array, $cRows, $request ) ) );
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
    public function show( $id ){
        return $this->sendError( 'Bad Request', array(), 400 );
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
