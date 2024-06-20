<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class ComoditiesStock extends BaseController
{
    private $permission = 4;
    
    private $dIni;
    private $dOut;
    private $fLocation = 0;
    private $toString = 1;

    public function __construct(){
        $this->middleware( 'candoit:' . $this->permission );
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
        try{
            //$this->fLocation = isset( $request->fLocation ) ? $request->fLocation : 0;
            $this->fLocation = isset( $request->fLocation ) && ( $request->fLocation == 1 ) ? $request->fLocation : 0;
            $this->toString = isset( $request->toString ) ? $request->toString : 1;
            // $consulta = is_null( $id ) ? self::where( 'status', '<>', 5 )->whereRaw( "LOWER(name) LIKE ?", [ '%' . strtolower( $request->q ) . '%' ] ) : self::where( 'id', $id );
            // $cRows = is_null( $id ) ? $consulta->get()->count() : 1;
            // $array = array();
            // $skip = isset( $request->page ) && $request->page != 1 ? ( $request->page * env( 'PER_PAGE' ) ) - env( 'PER_PAGE' ) : 0;
            // $query = is_null( $id ) ? $consulta->skip( $skip )->take( env( 'PER_PAGE' ) )->get() : $consulta->get();

            if( $this->fLocation == 1 ){
                foreach( \App\Locations::where( 'status', 1 )->orderBy( 'name', 'ASC' )->select( 'id', 'name' )->get() as $key => $val ){
                    $rows = array();
                    $query = \App\Tanks::with( array( 'commodities' ) )
                        ->where( 'branch_id', $val->id )
                        ->where( 'status', '>', 0 )
                        ->where( 'status', '<', 4 )
                        ->select( 'commodity', \DB::raw( 'SUM(stock_lb) AS stockLb, SUM(stock_lbd) AS stockLbd' ) )
                        ->groupBy( 'commodity' )
                        ->get();
                    if( $query->count() > 0 ){
                        $rows[ $val->name ] = array();
                        foreach( $query as $k => $v ){
                            if( $v->commodity != 0 && $v->commodities != null){
                                $row = array();
                                $row[ 'id' ] = $v->commodities[ 'id' ];
                                $row[ 'name' ] = $v->commodities[ 'name' ];
                                $row[ 'icon_name' ] = !is_null($v->commodities[ 'metas' ])? $v->commodities[ 'metas' ][ 'icon_name' ] : 'others';
                                if( $this->toString == 1 ){
                                    $row[ 'stock_lb' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLb ) . ' (' . \App\NumberFormat::tonsFormatGeneral( $v->stockLb ) . ')';
                                    $row[ 'stock_lbd' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLbd ) . ' (' . \App\NumberFormat::tonsFormatGeneral( $v->stockLbd ) . ')';
                                } elseif( $this->toString == 0 ){
                                    $row[ 'stock_lb' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLb );
                                    $row[ 'stock_lbd' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLbd );
                                    $row[ 'stock_mt_lb' ] = \App\NumberFormat::tonsFormatGeneral( $v->stockLb  );
                                    $row[ 'stock_mt_lbd' ] = \App\NumberFormat::tonsFormatGeneral( $v->stockLbd  );
                                }
                                $row[ 'ins' ] = \App\NumberFormat::simpleFormatGeneral( \App\TransactionsIn::where( 'commodity', $v->commodity )->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )->where( 'branch_id', $val->id )->where( 'status', 2 )->count() );
                                $row[ 'outs' ] = \App\NumberFormat::simpleFormatGeneral( \App\TransactionsOut::where( 'commodity', $v->commodity )->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )->where( 'branch_id', $val->id )->where( 'status', 2 )->count() );
                                $rows[ $val->name ][] = $row;
                            }
                        }
                        $array[] = $rows;
                    }
                }
            } elseif( $this->fLocation == 0 ){
                $query = \App\Tanks::with( array( 'commodities' ) )
                    ->where( 'branch_id', '<>', 0 )
                    ->where( 'status', '>', 0 )
                    ->where( 'status', '<', 4 )
                    ->select( 'commodity', \DB::raw( 'SUM(stock_lb) AS stockLb, SUM(stock_lbd) AS stockLbd' ) )
                    ->groupBy( 'commodity' )
                    ->get();
                if( $query->count() > 0 ){
                    foreach( $query as $k => $v ){
                        if( $v->commodity != 0 && $v->commodities != null){
                            $row = array();
                            $row[ 'id' ] = $v->commodities[ 'id' ];
                            $row[ 'name' ] = $v->commodities[ 'name' ];
                            $row[ 'icon_name' ] = !is_null($v->commodities[ 'metas' ])? $v->commodities[ 'metas' ][ 'icon_name' ] : 'others';;
                            if( $this->toString == 1 ){
                                $row[ 'stock_lb' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLb ) . ' (' . \App\NumberFormat::tonsFormatGeneral( $v->stockLb ) . ')';
                                $row[ 'stock_lbd' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLbd) . ' (' . \App\NumberFormat::tonsFormatGeneral( $v->stockLbd ) . ')';
                            } elseif( $this->toString == 0 ){
                                $row[ 'stock_lb' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLb);
                                $row[ 'stock_lbd' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLbd);
                                $row[ 'stock_mt_lb' ] = \App\NumberFormat::tonsFormatGeneral( $v->stockLb );
                                $row[ 'stock_mt_lbd' ] = \App\NumberFormat::tonsFormatGeneral( $v->stockLbd );
                            }
                            $row[ 'ins' ] = \App\NumberFormat::simpleFormatGeneral( \App\TransactionsIn::where( 'commodity', $v->commodity )->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )->where( 'status', 2 )->count() );
                            $row[ 'outs' ] = \App\NumberFormat::simpleFormatGeneral( \App\TransactionsOut::where( 'commodity', $v->commodity )->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )->where( 'status', 2 )->count() );
                            $array[] = $row;
                        }
                    }
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
        $this->toString = isset( $request->toString ) ? $request->toString : 1;
        try{
            $array[ 'id' ] = $id;
            $array[ 'name' ] = \App\Commodities::find( $id )->name;
            $array[ 'total' ][ 'stock_lb' ] = 0;
            $array[ 'total' ][ 'stock_lbd' ] = 0;
            $array[ 'total' ][ 'stock_mt_lb' ] = 0;
            $array[ 'total' ][ 'stock_mt_lbd' ] = 0;
            foreach( \App\Locations::where( 'status', 1 )->orderBy( 'name', 'ASC' )->select( 'id', 'name' )->get() as $key => $val ){
                $rows = array();
                $query = \App\Tanks::with( array( 'commodities' ) )
                    ->where( 'branch_id', $val->id )
                    ->where( 'commodity', $id )
                    ->where( 'status', '>', 0 )
                    ->where( 'status', '<', 4 )
                    ->select( 'commodity', \DB::raw( 'SUM(stock_lb) AS stockLb, SUM(stock_lbd) AS stockLbd' ) )
                    ->groupBy( 'commodity' )
                    ->get();
                if( $query->count() > 0 ){
                    $rows[ $val->name ] = array();
                    foreach( $query as $k => $v ){
                        if( $v->commodity != 0 ){
                            $row = array();
                            if( $this->toString == 1 ){
                                $row[ 'stock_lb' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLb ) . ' (' . \App\NumberFormat::tonsFormatGeneral( $v->stockLb ) . ')';
                                $row[ 'stock_lbd' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLbd ) . ' (' . \App\NumberFormat::tonsFormatGeneral( $v->stockLbd ) . ')';
                            } elseif( $this->toString == 0 ){
                                $row[ 'stock_lb' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLb );
                                $row[ 'stock_lbd' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLbd );
                                $row[ 'stock_mt_lb' ] = \App\NumberFormat::tonsFormatGeneral( $v->stockLb );
                                $row[ 'stock_mt_lbd' ] = \App\NumberFormat::tonsFormatGeneral( $v->stockLbd );
                            }
                            $rows[ $val->name ] = $row;
                            
                            $array[ 'total' ][ 'stock_lb' ] = $array[ 'total' ][ 'stock_lb' ] + $v->stockLb;
                            $array[ 'total' ][ 'stock_lbd' ] = $array[ 'total' ][ 'stock_lbd' ] + $v->stockLbd;
                        }
                    }
                    $array[ 'locations' ][] = $rows;
                }
            }

            $array[ 'total' ][ 'stock_mt_lb' ] = \App\NumberFormat::tonsFormatGeneral( $array[ 'total' ][ 'stock_lb' ] );
            $array[ 'total' ][ 'stock_mt_lbd' ] = \App\NumberFormat::tonsFormatGeneral( $array[ 'total' ][ 'stock_lbd' ] );

            $array[ 'total' ][ 'stock_lb' ] = \App\NumberFormat::simpleFormatGeneral( $array[ 'total' ][ 'stock_lb' ] );
            $array[ 'total' ][ 'stock_lbd' ] = \App\NumberFormat::simpleFormatGeneral( $array[ 'total' ][ 'stock_lbd' ] );

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

    public function location( $id, Request $request ){
        $array = array();
        $this->callbackElements( $request );
        $locations = \App\Locations::where( 'id', $id )->get();
        if( $locations->count() > 0 ){
            $location = $locations[ 0 ];
            $array[ 'id' ] = $location->id;
            $array[ 'name' ] = $location->name;
            $array[ 'timeAverageReceive' ] = \App\Api::getTime( $id, 0 );
            $array[ 'timeAverageShipping' ] = \App\Api::getTime( $id, 1 );
            $array[ 'items' ] = [];
            $query = \App\Tanks::with( array( 'commodities' ) )
                ->where( 'branch_id', $id )
                ->where( 'status', '>', 0 )
                ->where( 'status', '<', 4 )
                ->select( 'capacity', 'commodity', \DB::raw( 'SUM(capacity) AS tc, SUM(stock) AS stocks, SUM(stock_lb) AS stockLb, SUM(stock_lbd) AS stockLbd' ) )
                ->groupBy( 'commodity' )
                ->get();
            $totalCapacity = 0;
            $totalStock = 0;
            if( $query->count() > 0 ){
                foreach( $query as $key => $val ){
                    $rows = [];
                    $totalCapacity = $totalCapacity + $val->capacity;
                    $totalStock = $totalStock + $val->stocks;
                    $rows[ 'id' ] = !is_null($val->commodities) ? $val->commodities[ 'id' ] : '';
                    $rows[ 'name' ] = !is_null($val->commodities) ? $val->commodities[ 'name' ] : 'N/A';
                    $rows[ 'icon_name' ] = $this->getIcon($val->commodities);
                    $rows[ 'stock' ] = \App\NumberFormat::simpleFormatGeneral($val->stocks);
                    $array[ 'items' ][] = $rows;
                    
                }
            }
            $query1 = \App\Tanks::where( 'branch_id', $id )
                ->where( 'status', '>', 0 )
                ->where( 'status', '<', 4 )
                ->select( 'commodity', 'id', 'branch_id' )
                ->get();
            $totalReceive = 0;
            $totalShipping = 0;
            foreach( $query1 as $key => $val ){
                $totalReceive = $totalReceive  + \App\TransactionsIn::whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )->where( 'branch_id', $val->branch_id )->where( 'commodity', $val->commodity )->where( 'tank', $val->id )->where( 'status', 1 )->orderBy( 'id', 'DESC' )->get()->count();
                $totalShipping = $totalShipping + \App\TransactionsOut::whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )->where( 'branch_id', $val->branch_id )->where( 'commodity', $val->commodity )->where( 'tank', $val->id )->where( 'status', 1 )->orderBy( 'id', 'DESC' )->get()->count();
            }
            $array[ 'ticketsReceive' ] = $totalReceive;
            $array[ 'ticketsShipping' ] = $totalShipping;
            $array[ 'allStock' ] = $totalStock;
            $array[ 'allCapacity' ] = $totalCapacity;
            $average = 0;
            if( $totalCapacity != 0 )
                $average = ( $totalStock * 100 ) / $totalCapacity;
            $array[ 'average' ] = \App\NumberFormat::formatPercent( $average );
            return $this->sendSuccess( '', array( 'data' => $array ) );
        } else {
            return $this->sendSuccess( '', array( 'data' => [] ) );
        }
    }

    public function getIcon( $commodities )
    {
        if(!is_null($commodities) and !is_null($commodities[ 'metas' ]) )
        {
            return $commodities[ 'metas' ][ 'icon_name' ];
        }
        else{
            return 'others';
        }

    }
}
