<?php

namespace App\Http\Controllers\API\MOBILE;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class Silos extends BaseController {
    
    // private $permission = 7;

    /**
     * Enable this module.
     *
     * @return \Illuminate\Http\Response
     */
    // public function __construct(){
    //     $this->middleware( 'candoit:' . $this->permission );
    // }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( Request $request ){
        $string = isset( $request->q ) ? $request->q : '';
        try{
            $array = array();
            $consulta = \App\Locations::where( 'status', 1 )
                ->whereRaw( "LOWER(name) LIKE ?", [ '%' . strtolower( $string ) . '%' ] )
                ->select( 'id', 'name' );
            $query = $consulta->get();
            foreach( $query as $key => $val ){
                $rows = array();
                $rows[ 'id' ] = $val->id;
                $rows[ 'name' ] = $val->name;
                $rows[ 'items' ] = array();
                $sql = \App\Tanks::with( array( 'commodities' ) )
                    ->where( 'branch_id', $val->id )
                    ->where( 'commodity', '<>', 0 )
                    ->select( 'commodity', 'stock_lb', 'stock_lbd' );
                $cRows = $sql->get()->count();
                $skip = isset($request->page) && $request->page != 1 ? ($request->page * env('PER_PAGE')) - env('PER_PAGE') : 0;
                $sql = $sql->skip($skip)->take(env('PER_PAGE'))->get();
                foreach( $sql as $k => $v ){
                    if( $v->commodities[ 'id' ] !== null) {
                        $row = array();
                        $row['id'] = $v->commodities['id'];
                        $row['name'] = $v->commodities['name'];
                        $row['icon_c'] = !is_null($v->commodities[ 'metas' ])? $v->commodities[ 'metas' ][ 'icon_name' ] : 'others';
                        $row['stock_lb'] = \App\NumberFormat::simpleFormatGeneral($v->stock_lb);
                        $row['stock_lbd'] = \App\NumberFormat::simpleFormatGeneral($v->stock_lbd);
                        $row['stock_mt_lb'] = \App\NumberFormat::tonsFormatGeneral($v->stock_lb);
                        $row['stock_mt_lbd'] = \App\NumberFormat::tonsFormatGeneral($v->stock_lbd);
                        $rows['items'][] = $row;
                    }
                }
                $array[] = $rows;
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
    public function show( $id, Request $request ){
        $string = isset( $request->q ) ? $request->q : '';
        $cRows = 0;
        try{
            $array = array();
            $loc = \App\Locations::find( $id );
            $array[ 'id' ] = $loc->id;
            $array[ 'name' ] = $loc->name;
            $array[ 'items' ] = array();
            $total = 0;
            $consulta = \App\Tanks::with( array( 'commodities' ) )
                ->where( 'branch_id', $id )
                ->whereRaw( "LOWER(name) LIKE ?", [ '%' . strtolower( $string ) . '%' ] )
                ->where( 'commodity', '<>', 0 )
                ->select( 'id', 'name', 'commodity', 'stock_lb', 'stock_lbd' );
            $cRows = $consulta->get()->count();
            $skip = isset( $request->page ) && $request->page != 1 ? ( $request->page * env( 'PER_PAGE' ) ) - env( 'PER_PAGE' ) : 0;
            $query = $consulta->skip( $skip )->take( env( 'PER_PAGE' ) )->get();
            foreach( $query as $k => $v ){
                if( $v->id !== null and $v->commodities[ 'name' ] !== null ) {
                    $row = array();
                    $row['id'] = $v->id;
                    $row['name'] = $v->name;
                    $row['cid'] = $v->commodities['id'];
                    $row['cname'] = $v->commodities['name'];
                    $row['icon_c'] = !is_null($v->commodities[ 'metas' ])? $v->commodities[ 'metas' ][ 'icon_name' ] : 'others';;
                    $row['stock_lb'] = \App\NumberFormat::simpleFormatGeneral($v->stock_lb);
                    $row['stock_lbd'] = \App\NumberFormat::simpleFormatGeneral($v->stock_lbd);
                    $row['stock_mt_lb'] = \App\NumberFormat::tonsFormatGeneral($v->stock_lb);
                    $row['stock_mt_lbd'] = \App\NumberFormat::tonsFormatGeneral($v->stock_lbd);
                    $array['items'][] = $row;
                }
            }
            $array[ 'total' ] = \App\NumberFormat::totalFormat( \App\Tanks::where( 'branch_id', $id )->selectRaw( 'SUM(capacity) AS total' )->get()[ 0 ]->total );
            return $this->sendSuccess( '', array( 'data' => \App\Api::getPaginator( $array, $cRows, $request ) ) );
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
