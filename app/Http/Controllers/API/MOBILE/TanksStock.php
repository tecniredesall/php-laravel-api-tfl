<?php

namespace App\Http\Controllers\API\MOBILE;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class TanksStock extends BaseController {

    private $permission = 5;

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
        $array = array();
        try{
            $consulta = \App\Locations::where( 'status', 1 )->orderBy( 'name', 'ASC' )->get();
            foreach( $consulta as $key => $val ){
                $rows = array();
                $rows[ 'id' ] = $val->id;
                $rows[ 'label' ] = $val->name;
                foreach( \DB::table( 'tankstock' )->where( 'locationname', $val->name )->get() as $k => $v ){
                    $row = array();
                    $row[ 'id' ] = $v->tid;
                    $row[ 'name' ] = $v->cname;
                    $row[ 'weight' ] = \App\NumberFormat::numberFormatGeneral( $v->stock_lb, 0, ',' );
                    $row[ 'dryWeight' ] = \App\NumberFormat::numberFormatGeneral( $v->stock_lbd, 0, ',' );
                    $row[ 'averageMoisture' ] =  \App\NumberFormat::simpleFormatGeneral( \DB::table( 'transactions_in' )->where( 'tank', $v->tid )->where( 'status', 2 )->selectRaw( 'AVG(moisture) AS averageMoisture' )->pluck( 'averageMoisture' )[0] );
                    $row[ 'stock'] = \App\NumberFormat::numberFormatGeneral( $v->stock, 0, ',' );
                    $row[ 'capacity'] = \App\NumberFormat::numberFormatGeneral( $v->capacity, 0, ',' );
                    $row[ 'capicities' ] = \App\NumberFormat::numberFormatGeneral( $v->stock, 0, ',' ) . ' de ' . \App\NumberFormat::numberFormatGeneral( $v->capacity, 0, ',' ) . ' Bus';
                    $row[ 'barlabel' ] = ( $v->warn_min_on == 1 ) ? 'warning' : ( $v->warn_max_on == 1 ) ? 'danger' : 'success';
                    $row[ 'barcolor' ] = ( $v->warn_min_on == 1 ) ? '#EC971F' : ( $v->warn_max_on == 1 ) ? '#C9302C' : '#449D44';
                    $row[ 'percent' ] =  \App\NumberFormat::formatCapacityPercent( $v->stock, $v->capacity );
                    $rows[ $val->name ][][ $v->tname ] = $row;
                }
                $array[] = $rows;
            }
            return $this->sendSuccess( '', array( 'data' => \App\Api::getPaginator( $array, 0, $request ) ) );
            // return $this->sendSuccess( '', array( 'data' => $array ) );/
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show( $id ){
        $array = array();
        try{
            $obj = \App\Locations::where( 'status', 1 )->find( $id );
            foreach( \DB::table( 'tankstock' )->where( 'locationname', $obj->name )->get() as $k => $v ){
                $row = array();
                $row[ 'id' ] = $v->tid;
                $row[ 'name' ] = $v->cname;
                $row[ 'weight' ] = \App\NumberFormat::numberFormatGeneral( $v->stock_lb, 0, ',' );
                $row[ 'dryWeight' ] = \App\NumberFormat::numberFormatGeneral( $v->stock_lbd, 0, ',' );
                $row[ 'averageMoisture' ] = \App\NumberFormat::simpleFormatGeneral( \DB::table( 'transactions_in' )->where( 'tank', $v->tid )->where( 'status', 2 )->selectRaw( 'AVG(moisture) AS averageMoisture' )->pluck( 'averageMoisture' )[0] );
                $row[ 'stock'] = \App\NumberFormat::numberFormatGeneral( $v->stock, 0, ',' );
                $row[ 'capacity'] = \App\NumberFormat::numberFormatGeneral( $v->capacity, 0, ',' );
                $row[ 'capicities' ] = \App\NumberFormat::numberFormatGeneral( $v->stock, 0, ',' ) . ' de ' . \App\NumberFormat::numberFormatGeneral( $v->capacity, 0, ',' ) . ' Bus';
                $row[ 'barlabel' ] = ( $v->warn_min_on == 1 ) ? 'warning' : ( $v->warn_max_on == 1 ) ? 'danger' : 'success';
                $row[ 'barcolor' ] = ( $v->warn_min_on == 1 ) ? '#EC971F' : ( $v->warn_max_on == 1 ) ? '#C9302C' : '#449D44';
                $row[ 'percent' ] = \App\NumberFormat::formatCapacityPercent( $v->stock, $v->capacity );
                $array[][ $v->tname ] = $row;
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
    public function update( $id, Request $request ){
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id ){
        //
    }
}
