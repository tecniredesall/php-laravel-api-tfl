<?php

namespace App\Http\Controllers\API\MOBILE;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class ComoditiesStock extends BaseController {
    
    private $dIni;
    private $dOut;

    private $fLocation = 0;
    private $toString = 1;

    private $permission = 4;

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
        try{
            $this->fLocation = isset( $request->fLocation ) ? $request->fLocation : 0;
            $string = isset( $request->q ) ? $request->q : '';
            $query = \App\Tanks::with([ 'commodities' ])
                ->where( 'branch_id', '<>', 0 )
                ->where( 'status', '>', 0 )
                ->where( 'status', '<', 4 )
                ->whereHas( 'commodities', function( $qs ) use ( $string ){
                    if( $string != '' )
                        $qs->whereRaw( "LOWER(commodities.name) LIKE ?", [ '%' . strtolower( $string ) . '%' ] );
                })
                ->groupBy( 'commodity' )
                ->select( 'commodity', \DB::raw( 'SUM(stock_lb) AS stockLb, SUM(stock_lbd) AS stockLbd' ) );
            $cRows = $query->get()->count();
            $skip = isset($request->page) && $request->page != 1 ? ($request->page * env('PER_PAGE')) - env('PER_PAGE') : 0;
            $query = $query->skip($skip)->take(env('PER_PAGE'))->get();
            if( $cRows > 0 ){
                foreach( $query as $k => $v ){
                    if( $v->commodity != 0 ){
                        $row = array();
                        $row[ 'id' ] = $v->commodities[ 'id' ];
                        $row[ 'name' ] = $v->commodities[ 'name' ];
                        $row[ 'icon_c' ] = !is_null($v->commodities[ 'metas' ])? $v->commodities[ 'metas' ][ 'icon_name' ] : 'others';
                        $row[ 'stock_lb' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLb );
                        $row[ 'stock_lbd' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLbd );
                        $row[ 'stock_mt_lb' ] = \App\NumberFormat::tonsFormatGeneral( $v->stockLb );
                        $row[ 'stock_mt_lbd' ] = \App\NumberFormat::tonsFormatGeneral( $v->stockLbd );
                        $array[] = $row;
                    }
                }
            }
            return $this->sendSuccess( '', array( 'data' => \App\Api::getPaginator( $array, $cRows, $request ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    /**
     * Store a newly created resource in storage
.     *
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
    public function show( $id, Request $request  ){
        $array = array();
        $cid = isset( $id ) ? $id : 0;
        if( $cid == 0 )
            return $this->sendError( 'Request Invalid', array(), 400 );
        try{
            $string = isset( $request->q ) ? $request->q : '';
            $sql = \App\Locations::where( 'status', 1 )
                ->whereRaw( "LOWER(name) LIKE ?", [ '%' . strtolower( $string ) . '%' ] )
                ->orderBy( 'name', 'ASC' )
                ->select( 'id', 'name' )
                ->get();
            foreach( $sql as $key => $val ){
                $rows = array();
                $query = \App\Tanks::with( array( 'commodities' ) )
                ->where( 'branch_id', $val->id )
                ->where( 'commodity', $cid )
                ->where( 'status', '>', 0 )
                ->where( 'status', '<', 4 )
                ->select( 'id', 'name', 'commodity', \DB::raw( 'SUM(stock_lb) AS stockLb, SUM(stock_lbd) AS stockLbd' ) )
                ->groupBy( 'commodity' );
                $cRows = $query->get()->count();
                $skip = isset($request->page) && $request->page != 1 ? ($request->page * env('PER_PAGE')) - env('PER_PAGE') : 0;
                $query = $query->skip($skip)->take(env('PER_PAGE'))->get();
                if( $query->count() > 0 ){
                    $rows[ $val->name ] = array();
                    foreach( $query as $k => $v ){
                        if( $v->commodity != 0 ){
                            $row = array();
                            $row[ 'id' ] = $val->id;
                            $row[ 'stock_lb' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLb );
                            $row[ 'stock_lbd' ] = \App\NumberFormat::simpleFormatGeneral( $v->stockLbd );
                            $row[ 'stock_mt_lb' ] = \App\NumberFormat::tonsFormatGeneral( $v->stockLb );
                            $row[ 'stock_mt_lbd' ] = \App\NumberFormat::tonsFormatGeneral( $v->stockLbd );
                            $rows[ $val->name ] = $row;
                        }
                    }
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
     * Display the specified route.
     *
     * @param  int  $cid
     * @param  int  $lid
     * @param  int  $tid
     * @param  int  $request
     * @return \Illuminate\Http\Response
     */
    public function tanks( $cid, $lid, $tid=null, Request $request ){
        if( !is_null( $tid ) )
            $this->callbackElements( $request );
        $array = array();
        if( ( !is_null( $cid ) && $cid != 0 ) && ( !is_null( $lid ) && $lid != 0 ) ){
            try{
                $string = isset( $request->q ) ? $request->q : '';
                if( is_null( $tid ) ){
                    $subQuery = \App\Tanks::with( array( 'location', 'commodities' ) )
                        ->where( 'branch_id', $lid )
                        ->whereRaw( "LOWER(name) LIKE ?", [ '%' . strtolower( $string ) . '%' ] )
                        ->where( 'commodity', $cid )
                        ->where( 'status', '>', 0 )
                        ->where( 'status', '<', 4 )
                        ->select( 'id', 'name', 'branch_id', 'commodity', 'stock', 'stock_lb', 'stock_lbd', 'capacity' );
                } else
                    $subQuery = \App\Tanks::with( array( 'location', 'commodities' ) )->where( 'id', $tid )
                        ->select( 'id', 'name', 'branch_id', 'commodity', 'stock', 'stock_lb', 'stock_lbd', 'capacity', 'source_id', 'status' );

                $cRows = $subQuery->get()->count();
                $skip = isset($request->page) && $request->page != 1 ? ($request->page * env('PER_PAGE')) - env('PER_PAGE') : 0;
                $subQuery = $subQuery->skip($skip)->take(env('PER_PAGE'))->get();
                foreach( $subQuery as $a => $b ){
                    $rows = array();
                    $rows[ 'id' ] = $b->id;
                    $rows[ 'name' ] = $b->name;
                    $rows[ 'cname' ] = !is_null($b->commodities[ 'name' ]) ? $b->commodities[ 'name' ] : 'N/A';
                    $rows[ 'icon_c' ] = !is_null($b->commodities[ 'metas' ]) ? $b->commodities[ 'metas' ][ 'icon_name' ] : 'others';
                    $rows[ 'stock_lb' ] = \App\NumberFormat::simpleFormatGeneral( $b->stock_lb );
                    $rows[ 'stock_lbd' ] = \App\NumberFormat::simpleFormatGeneral( $b->stock_lbd );
                    $rows[ 'stock_mt_lb' ] = \App\NumberFormat::tonsFormatGeneral( $b->stock_lb );
                    $rows[ 'stock_mt_lbd' ] = \App\NumberFormat::tonsFormatGeneral( $b->stock_lbd );
                    if( !is_null( $tid ) ){
                        $rows[ 'bushles' ] = \App\NumberFormat::simpleFormatGeneral( $b->stock );
                        $rows[ 'capacity' ] = \App\NumberFormat::simpleFormatGeneral( $b->capacity );
                        $rows[ 'locations' ] = $b->location[ 'name' ];
                        $rows[ 'percent' ] = ($b->capacity == 0 ) ? (string)\App\NumberFormat::totalFormat(0) : (string)\App\NumberFormat::formatCapacityPercent( $b->stock, $b->capacity );
                        $general = \App\Company_info::selectRaw('decimals_in_general')->pluck('decimals_in_general')[0];
                        $t1 = \App\TransactionsIn::whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                            ->where( 'branch_id', $lid )->where( 'commodity', $cid )->where( 'tank', $tid )->where( 'status', 1 )
                            ->orderBy( 'id', 'DESC' )->get(array(  'id', 'drivername', \DB::raw('FORMAT(net,' . $general .') as net'), 'source_id', 'status') );

                        $t2 = \App\TransactionsOut::whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )->where( 'branch_id', $lid )
                            ->where( 'commodity', $cid )->where( 'tank', $tid )->where( 'status', 1 )
                            ->orderBy( 'id', 'DESC' )->get(array(  'id', 'drivername', \DB::raw('FORMAT(net,' . $general .') as net'), 'source_id', 'status') );

                        if( !is_null( $t1 ) && !is_null( $t2 ) ){
                            $rows[ 'tickets' ][ 'receive' ] = is_null( $t1 ) ? [] : $t1;
                            $rows[ 'tickets' ][ 'shipping' ] = is_null( $t2 ) ? [] : $t2;
                        }
                    }

                    if( is_null( $tid ) )
                        $array[] = $rows;
                    else 
                        $array = $rows;
                }
                if( !is_null( $tid ) )
                    return $this->sendSuccess( '', array( 'data' => $array ) );
                else
                    return $this->sendSuccess( '', array( 'data' => \App\Api::getPaginator( $array, $cRows, $request ) ) );
            } catch( Exception $e ){
                Log::error($e->getMessage());
                return $this->sendError( 'Internal Server Error', array(), 500 );
            }
        } else
            return $this->sendError( 'Request Invalid', array(), 400 );
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
