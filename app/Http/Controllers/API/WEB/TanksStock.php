<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class TanksStock extends BaseController {

    private $dIni;
    private $dOut;
    private $permission = 5;

    /**
     * Enable this module.
     *
     * @return \Illuminate\Http\Response
     */
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
            foreach( \App\Locations::where( 'status', 1 )->orderBy( 'name', 'ASC' )->select( 'id', 'name' )->get() as $key => $val ){
                $rows = array();
                $rows[ 'id' ] = $val->id;
                $rows[ 'name' ] = $val->name;
                $rows[ 'timeAverageReceive' ] = \App\Api::getTime( $val->id, 0 );
                $totalCapacity = 0; $totalStock = 0;
                $rows[ 'tanks' ] = array();

                $query = \DB::select("SELECT *, 
                    FORMAT((SELECT AVG(moisture) FROM transactions_in  WHERE date_start BETWEEN '". $this->dIni . "' AND '" . $this->dOut . "' AND tank = ts.tid AND branch_id = ts.location_tank ), (select decimals_in_general from company_info)) AS avg_moisture,
                    ( SELECT COUNT(id) FROM transactions_in WHERE date_start BETWEEN '". $this->dIni . "' AND '" . $this->dOut . "' AND tank = ts.tid AND branch_id = ts.location_tank AND commodity = ts.cid ORDER BY id DESC ) AS tReceive,
                    ( SELECT COUNT(id) FROM transactions_out WHERE date_start BETWEEN '". $this->dIni . "' AND '" . $this->dOut . "' AND tank = ts.tid AND branch_id = ts.location_tank AND commodity = ts.cid ORDER BY id DESC ) AS tShipping
                    FROM tankstock as ts
                        WHERE ts.status <> 5
                        AND ts.location_tank = ". $val->id );

                foreach( $query as $k => $v ){

                    $totalCapacity = $totalCapacity + $v->tcapacity;
                    $totalStock = $totalStock + $v->tstock;
                    $capacity =   \App\NumberFormat::capacityPercent( $v->tstock, $v->tcapacity );

                    if($capacity < $v->warn_min ){
                        $barlabel = 'warning';
                    }else if($capacity > $v->warn_max ){
                        $barlabel = 'danger';
                    }else{
                        $barlabel = 'success';
                    }

                    $query[$k]->barlabel = $barlabel;
                    $query[$k]->percent = \App\NumberFormat::formatPercent( $capacity );
                }

                $rows[ 'average' ] = $totalStock !== 0 ? \App\NumberFormat::formatCapacityPercent( $totalStock, $totalCapacity) . '%' : '0%';
                $rows[ 'tanks' ] = $query;
                $array[] = $rows;
            }
            return $this->sendSuccess( '', array( 'data' => $array, 'pending' => \App\Cudrequest::cudrequest( ["Tanks"] )  ) );
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
                $row[ 'capicityString' ] = \App\NumberFormat::numberFormatGeneral( $v->stock, 0, ',' ) . ' de ' . \App\NumberFormat::numberFormatGeneral( $v->capacity, 0, ',' ) . ' Bus';
                $row[ 'barlabel' ] = ( $v->warn_min_on == 1 ) ? 'success' : ( ( $v->warn_max_on == 1 ) ? 'danger' : 'success');
                $row[ 'barcolor' ] = ( $v->warn_min_on == 1 ) ? '#EC971F' : ( ( $v->warn_max_on == 1 ) ? '#C9302C' : '#449D44');
                $row[ 'percent' ] = ($v->capacity == 0 ) ? 0 : \App\NumberFormat::formatCapacityPercent( $v->stock, $v->capacity );
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

    public function byTank( $id, Request $request ){
        $array = array();
        $this->callbackElements( $request );
        try{
            foreach( \App\Tanks::with(array( 'commodities', 'stocks' ))->where( 'status', '<>', 5 )->where( 'id', $id )->get() as $k => $v ){
                $row = array();
                $row = $v->toArray();
                $row[ 'weight' ] = \App\NumberFormat::numberFormatGeneral( $v->stock_lb, 0, ',' );
                $row[ 'dryWeight' ] = \App\NumberFormat::numberFormatGeneral( $v->stock_lbd, 0, ',' );
                $row[ 'averageMoisture' ] = \App\NumberFormat::simpleFormatGeneral( \DB::table( 'transactions_in' )->where( 'tank', $v->id )->where( 'status', 2 )->selectRaw( 'AVG(moisture) AS averageMoisture' )->pluck( 'averageMoisture' )[0] );
                $row[ 'stock'] = \App\NumberFormat::numberFormatGeneral( $v->stock, 0, ',' );
                $row[ 'capacity'] = \App\NumberFormat::numberFormatGeneral( $v->capacity, 0, ',' );
                $row[ 'capicityString' ] = \App\NumberFormat::numberFormatGeneral( $v->stock, 0, ',' ) . ' de ' . \App\NumberFormat::numberFormatGeneral( $v->capacity, 0, ',' ) . ' Bus';
                $row[ 'barlabel' ] = ( $v->stocks['warn_min_on'] == 1 ) ? 'success' : ( ($v->stocks['warn_max_on'] == 1) ? 'danger' : 'success' );
                $row[ 'barcolor' ] = ( $v->stocks[ 'warn_min_on' ] == 1 ) ? '#EC971F' : ( ($v->stocks[ 'warn_max_on' ] == 1)  ? '#C9302C' : '#449D44');
                $row[ 'percent' ] = ($v->capacity == 0 ) ? 0 : \App\NumberFormat::formatCapacityPercent( $v->stock, $v->capacity );
                $t1 = \App\TransactionsIn::whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )->where( 'branch_id', $v->branch_id )->where( 'commodity', $v->commodity )->where( 'tank', $v->id )->where( 'status', 1 )->orderBy( 'id', 'DESC' )->get(array( 'id', 'drivername', 'net' ));
                $t2 = \App\TransactionsOut::whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )->where( 'branch_id', $v->branch_id )->where( 'commodity', $v->commodity )->where( 'tank', $v->id )->where( 'status', 1 )->orderBy( 'id', 'DESC' )->get(array( 'id', 'drivername', 'net' ));
                $row[ 'tickets' ][ 'receive' ] = ( $t1->count() > 0 ) ? $t1->toArray() : [];
                $row[ 'tickets' ][ 'shipping' ] = ( $t2->count() > 0 ) ? $t2->toArray() : [];
                $array[] = $row;
            }
            return $this->sendSuccess( '', array( 'data' => $array ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }
}