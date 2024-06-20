<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class OpenTickets extends BaseController {
    private $dIni;
    private $dOut;
    private $filter = 0;

    private $permission = 3;

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
        $this->filter = isset( $request->filter ) ? $request->filter : 0;
        try{
            foreach( \App\Locations::where( 'status', 1 )->orderBy( 'name', 'ASC' )->select( 'id', 'name' )->get() as $key => $val ) {
                $commodities=array();
                foreach (\App\Commodities::with(array('metas'))->select('id', 'name')->get() as $k=>$v)
                    $commodities[$v->id]=array('id'=>$v->id, 'name'=>$v->name, 'total'=>0, 'icon_c'=>!is_null($v['meta']) ? $v->metas['icon_name'] : 'others');
                $rows=array();
                $rows['id']=$val->id;
                $rows['name']=$val->name;
                $rows['timeAverage']=\App\Api::getTime($val->id, $this->filter);
                if ($this->filter == 0)
                    $query=\App\TransactionsIn::with(array('sellers'))
                        ->where('branch_id', $val->id)
                        ->whereBetween('date_start', [$this->dIni, $this->dOut])
                        ->whereIn('status', [1, 10, 11])
                        ->selectRaw('id, branch_id, source_id, moisture, testwt, date_start, netdrywt, status, seller')
                        ->get();
                else
                    $query=\App\TransactionsOut::with(array('buyers'))
                        ->where('branch_id', $val->id)
                        ->whereBetween('date_start', [$this->dIni, $this->dOut])
                        ->whereIn('status', [1, 10, 11])
                        ->selectRaw('id, branch_id, source_id, moisture, testwt, date_start, netdrywt, status, buyer')
                        ->get();
                $rows['totalItems']= !empty($query->toArray()) ? $query->count() : 0;
                // $rows[ 'commodityArray' ] = \App\Api::getCommoditiesArray( $val->id, $this->filter, $query, $commodities );
                if ($rows['totalItems'] > 0) {
                    foreach ($query->toArray() as $k=>$v) {
                        $row=array();
                        $row['id']=$v['id'];
                        $row['source_id']=$v['source_id'];
                        $row['branch_id']=$v['branch_id'];
                        if ($this->filter == 0)
                            $row['name']= !is_null($v['sellers']) ? $v['sellers']['name'] : 'N/A';
                        else
                            $row['name']=!is_null($v['buyers']) ? $v['buyers']['name'] : 'N/A';
                        $row['moisture']=\App\NumberFormat::hardFormat($v['moisture']);
                        $row['testwt']=\App\NumberFormat::hardFormat($v['testwt']);
                        $row['date_start']=date('h:i A', strtotime($v['date_start']));
                        $row['netdrywt']=\App\NumberFormat::simpleFormatTickets($v['netdrywt']);
                        $row['metrics']=\App\NumberFormat::tonsFormatTickets($v['netdrywt']);
                        $row['status']=$v['status'];
                        $rows[$val->name][]=$row;
                    }
                    $array[]=$rows;
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
        $this->filter = isset( $request->filter ) ? $request->filter : 0;
        $this->callbackElements( $request );
        $array = array();
        $commodities = array();
        foreach( \App\Commodities::with( array( 'metas' ) )->select( 'id', 'name' )->get() as $k => $v )
            $commodities[ $v->id ] = array( 'id' => $v->id, 'name' => $v->name, 'total' => 0, 'icon_c' => $v->metas[ 'icon_name' ] );
        try{
            $loc = \App\Locations::findOrFail( $id );
            $rows = array();
            $rows[ 'id' ] = $loc->id;
            $rows[ 'name' ] = $loc->name;
            $rows[ 'timeAverage' ] = \App\Api::getTime( $id, $this->filter );
            $rows[ 'items' ] = array();
            if( $this->filter == 0 )
                $query = \App\TransactionsIn::with(array( 'sellers' ))
                    ->where( 'branch_id', $loc->id )
                    ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                    ->whereIn( 'status', [1,10,11] )
                    ->selectRaw( 'id, branch_id, source_id, moisture, testwt, date_start, netdrywt, status, seller' )
                    ->get();
            else
                $query = \App\TransactionsOut::with(array( 'buyers' ))
                    ->where( 'branch_id', $loc->id )
                    ->whereBetween( 'date_start', [ $this->dIni, $this->dOut ] )
                    ->whereIn( 'status', [1,10,11] )
                    ->selectRaw( 'id, branch_id, source_id, moisture, testwt, date_start, netdrywt, status, buyer' )
                    ->get();
            $rows[ 'totalItems' ] = $query->count();
            if( $query->count() > 0 ){
                foreach( $query as $k => $v ){
                    $row = array();
                    $row[ 'id' ] = $v->id;
                    $row[ 'branch_id' ] = $v->branch_id;
                    $row[ 'source_id' ] = $v->source_id;
                    if( $this->filter == 0 )
                        $row[ 'name' ] = $v->sellers[ 'name' ];
                    else
                        $row[ 'name' ] = $v->buyers[ 'name' ];
                    $row[ 'moisture' ] = \App\NumberFormat::hardFormat( $v->moisture );
                    $row[ 'testwt' ] = \App\NumberFormat::hardFormat( $v->testwt );
                    $row[ 'date_start' ] = date( 'h:i A', strtotime( $v->date_start ) );
                    $row[ 'netdrywt' ] = \App\NumberFormat::simpleFormatTickets( $v->netdrywt );
                    $row[ 'metrics' ] = \App\NumberFormat::tonsFormatTickets( $v->netdrywt );
                    $row[ 'status' ] = $v->status;
                    $rows[ 'items' ][] = $row;
                }
            }
            return $this->sendSuccess( '', array( 'data' => $rows ) );
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
    public function destroy($id){
        return $this->sendError( 'Bad Request', array(), 400 );
    }

    public function tickets( $where, Request $request ){
        $this->callbackElements( $request );
        try {
            if ($where == 'receive'){
                $query = \App\TransactionsIn::whereBetween('date_start', [$this->dIni, $this->dOut])
                    ->whereIn('status', [1, 10, 11])
                    ->selectRaw('id, commodity, branch_id, source_id, date_start, date_end')
                    ->orderBy('source_id', 'DESC')
                    ->get();
            }elseif( $where == 'shipping' ) {
                $query = \App\TransactionsOut::whereBetween('date_start', [$this->dIni, $this->dOut])
                    ->whereIn('status', [1, 10, 11])
                    ->selectRaw('id, commodity, branch_id, source_id, date_start, date_end')
                    ->orderBy('source_id', 'DESC')
                    ->get();
            }else{
                return $this->sendError( 'Bad Request', array(), 400 );
            }

            $array = array();
            foreach( $query as $key => $val ){
                $rows = array();
                $rows[ 'id' ] = $val->id;
                $rows[ 'source_id' ] = $val->source_id;
                $location = \App\Locations::where('id', $val->branch_id )->first();

                if ($location) {
                    $rows[ 'location_id' ] =  $location->id;
                    $rows[ 'location' ] =  $location->name;
                } else {
                    $rows[ 'location_id' ] =  '';
                    $rows[ 'location' ] = 'N/A';
                }

                $commodity = \App\Commodities::where('id', $val->commodity )->first();
                $rows[ 'commodity' ] = isset($commodity->name) ? $commodity->name : 'N/A';
                $start = new \Carbon\Carbon( $val->date_start );
                $end = \Carbon\Carbon::now();
                if( $where == 'receive' ) {
                    $rows['timeAverageReceive'] = \App\Api::getFormatDates($end->diffInRealSeconds($start));
                }
                if( $where == 'shipping' ) {
                    $rows['timeAverageShipping'] = \App\Api::getFormatDates($end->diffInRealSeconds($start));
                }
                $array[] = $rows;
            }
            return $this->sendSuccess( '', array( 'data' => $array ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function detail($lid, $tkid, Request $request)
    {
        $this->callbackElements($request);
        $this->filter = intval(isset($request->filter) ? $request->filter : 0);
        $array = array();
        $split = array();
        $father = '';

        if ($this->filter === 0) {
            $father = \DB::select(\DB::raw('select * from transactions_in where id_related =' . $tkid));
        }

        if (!empty($father)){
            $split = $this->splitTicket($lid, $tkid, $this->filter);
            return $this->sendSuccess('', array('data' => null, 'split' => $split));
        } else {
            if ((!is_null($lid) && $lid != 0) && (!is_null($tkid) && $tkid != 0)) {
                try {
                    if ($this->filter == 0) {
                        $ticket = \DB::table('transactions_in')->where('transactions_in.source_id', $tkid)->where('transactions_in.branch_id', $lid)
                            ->leftJoin('commodities', 'commodities.id', 'transactions_in.commodity')
                            ->leftJoin('sellers', 'sellers.id', 'transactions_in.seller')
                            ->leftJoin('farms', 'farms.id', 'transactions_in.farm')
                            ->leftJoin('tanks', 'tanks.id', 'transactions_in.tank')
                            ->select('transactions_in.user as user', 'sellers.name as customerName', 'sellers.address', 'farms.name as farm_name', 'commodities.name as commodity_name', 'transactions_in.*', 'transactions_in.id as trans_id', 'tanks.name as tank_name')
                            ->first();

                    } elseif ($this->filter == 1) {
                        $ticket = \DB::table('transactions_out')->where('transactions_out.source_id', $tkid)
                            ->where('transactions_out.branch_id', $lid)
                            ->leftJoin('commodities', 'commodities.id', 'transactions_out.commodity')
                            ->leftJoin('buyers', 'buyers.id', 'transactions_out.buyer')
                            ->leftJoin('tanks', 'tanks.id', 'transactions_out.tank')
                            ->select('transactions_out.user as user', 'buyers.name as customerName', 'buyers.address', 'commodities.name as commodity_name', 'transactions_out.*', 'transactions_out.contractno as contractid', 'transactions_out.id as trans_id', 'tanks.name as tank_name')
                            ->first();

                    } else {
                        $ticket = \DB::table('cashsales')->where('cashsales.source_id', $tkid)
                            ->where('cashsales.branch_id', $lid)
                            ->leftJoin('commodities', 'commodities.id', 'cashsales.commodity_id')
                            ->leftJoin('tanks', 'tanks.id', 'cashsales.tank_id')
                            ->select('cashsales.buyer as customerName', 'commodities.name as commodity_name', 'cashsales.*', 'cashsales.id as trans_id', 'tanks.name as tank_name')
                            ->first();
                    }

                    if ($ticket !== null) {
                        $array['id'] = isset($ticket->trans_id) ? $ticket->trans_id : '';
                        $array['ticket'] = isset($ticket->source_id) ? strval($ticket->source_id) : "0";
                        $array['source_id'] = isset($ticket->source_id) ? strval($ticket->source_id) : "0";
                        $array['contractid'] = isset($ticket->contractid) ? strval($ticket->contractid) : "0";
                        $array['truckname'] = isset($ticket->truckname) ? $ticket->truckname : '';
                        $array['drivername'] = isset($ticket->drivername) ? $ticket->drivername : '';
                        if ($this->filter == 0)
                            $array['trailerlicense'] = isset($ticket->trailerlicense) ? $ticket->trailerlicense : '';
                        elseif ($this->filter == 1)
                            $array['trailerlicense'] = isset($ticket->trucklicense) ? $ticket->trucklicense : '';
                        else {
                            $array['trailerlicense'] = '';
                        }

                        $array['customerName'] = isset($ticket->customerName) ? $ticket->customerName : '';
                        $array['location_name'] = \DB::table('locations')->where('id', $ticket->branch_id)->pluck('name')[0];
                        if ($this->filter == 0) {
                            $array['lot'] = isset($ticket->farm_name) ? $ticket->farm_name : '';
                            $array['tank_name'] = isset($ticket->tank_name) ? $ticket->tank_name : '';
                            $array['origin'] = isset($ticket->origin) ? $ticket->origin : '';
                            $array['purchaseorder'] = '';
                            $array['orgticket'] = isset($ticket->orgticket) ? $ticket->orgticket : '';
                            $array['orgweight'] = \App\NumberFormat::simpleFormatTickets($ticket->orgweight);
                        } elseif ($this->filter == 1) {
                            $array['lot'] = '';
                            $array['tank_name'] = isset($ticket->tank_name) ? $ticket->tank_name : '';
                            $array['origin'] = '';
                            $array['purchaseorder'] = isset($ticket->purchaseorder) ? $ticket->purchaseorder : '';
                            $array['orgticket'] = '';
                            $array['orgweight'] = '';
                        } else {
                            $array['lot'] = '';
                            $array['tank_name'] = isset($ticket->tank_name) ? $ticket->tank_name : '';
                            $array['origin'] = '';
                            $array['purchaseorder'] = '';
                            $array['orgticket'] = '';
                            $array['orgweight'] = '';
                        }

                        if ($this->filter == 0 || $this->filter == 1) {
                            $user = \DB::table('users')->where('id', $ticket->user)->selectRaw("CONCAT( name, ' ', lastname ) As name_complete")->first();
                            $nameUser = "";
                            if ($user) {
                                $nameUser = $user->name_complete;
                            }

                            $array['usercreator'] = $nameUser;
                            $array['address'] = isset($ticket->address) ? $ticket->address : '';
                            $array['date_start'] = \Carbon\Carbon::parse($ticket->date_start)->format('Y-m-d H:i:s T');
                            //$array['time_in'] = \Carbon\Carbon::parse($ticket->date_start)->format('H:i:s T');
                            $array['date_end'] = ($ticket->date_end !== "0000-00-00 00:00:00") ? \Carbon\Carbon::parse($ticket->date_end)->format('Y-m-d H:i:s T') : "0000-00-00";
                        } else {
                            $array['usercreator'] = '';
                            $array['address'] = '';
                            $array['date_start'] = \Carbon\Carbon::parse($ticket->selled_at)->format('Y-m-d H:i:s T');
                        }

                        $array['product'] = isset($ticket->commodity_name) ? $ticket->commodity_name : '';
                        $array['moisture'] = \App\NumberFormat::hardFormat($ticket->moisture);
                        $array['testwt'] = \App\NumberFormat::hardFormat($ticket->testwt);
                        $array['drychrat'] = \App\NumberFormat::simpleFormatTickets($ticket->drychrat);
                        $array['dryshper'] = \App\NumberFormat::simpleFormatTickets($ticket->dryshper);
                        $array['netdrywt'] = \App\NumberFormat::simpleFormatTickets($ticket->netdrywt);
                        $array['gross'] = \App\NumberFormat::simpleFormatTickets($ticket->weight);
                        $array['tare'] = \App\NumberFormat::simpleFormatTickets($ticket->tare);
                        $array['net'] = \App\NumberFormat::simpleFormatTickets($ticket->net);
                        $discount = $ticket->net - $ticket->netdrywt;
                        $array['discount'] = \App\NumberFormat::simpleFormatTickets($discount);
                        $array['status'] = isset($ticket->status) ? $ticket->status : '';

                        if ($this->filter == 0)
                            $table = 'transactions_in_commodities_features';
                        else if ($this->filter == 1)
                            $table = 'transactions_out_commodities_features';

                        if ($this->filter == 0 or $this->filter == 1)
                            $array['features'] = \DB::table($table.' as tcf')
                                ->selectRaw('cf.name as title, tcf.value')
                                ->join('commodities_features as cf', 'cf.commodities_features_id', 'tcf.commodities_features_id')
                                ->where([
                                    ['tcf.source_id', $tkid],
                                    ['tcf.branch_id', $lid]
                                ])->get()->toArray();

                        return $this->sendSuccess('', array('data' => $array, 'split' => $split));
                    } else {
                        return $this->sendError('Request Invalid', array(), 400);
                    }
                } catch (Exception $e) {
                    Log::error($e->getMessage());
                    return $this->sendError('Internal Server Error', array($e->getMessage()), 500);
                }
            } else {
                return $this->sendError('Request Invalid', array(), 400);
            }
        }
    }

    protected function splitTicket($lid, $tkid, $filter)
    {
        try{
            $searchId = 0;
            $sp = \DB::select("call parentReference($searchId,$tkid)");
            foreach ($sp as $key => $value){
                $object = $sp[$key];
                if ($filter == 0)
                    $table = 'transactions_in_commodities_features';
                else if ($filter == 1)
                    $table = 'transactions_out_commodities_features';

                if ($filter == 0 or $filter == 1)
                    $object->features = \DB::table($table.' as tcf')
                        ->selectRaw('cf.name as title, tcf.value')
                        ->join('commodities_features as cf', 'cf.commodities_features_id', 'tcf.commodities_features_id')
                        ->where([
                            ['tcf.source_id', $value->ticket],
                            ['tcf.branch_id',  $value->location_id]
                        ])->get()->toArray();

                $array[] = $object;
            }
            return $array;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError('Internal Server Error', array($e->getMessage()), 500);
        }
    }



}
