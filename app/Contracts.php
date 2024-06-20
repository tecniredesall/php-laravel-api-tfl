<?php

namespace App;
use Illuminate\Support\Facades\DB;
class Contracts extends Api
{
    public $timestamps = false;

    protected $table = 'apicontracts';

    protected $fillable = [];

    protected $hidden = [];

    public static function mostrar( $id = null, $request = array() )
    {
        $member = 'resource:io.grainchain.Member#';
        $commoditie = 'resource:io.grainchain.Commodity#';
        $seller = $member;
        $buyer = $member;
        $settle_at = $member;
        $contract = [];
        $query = self::select('*')->whereRaw('json like '."'".'%"settle_at":'.'"'.$member.\App\Company_info::pluck('email')[0].'"%'."' and json like "."'".'%"status":'.'"Open"%'."'");
        $cRows = $query->get()->count();
        $skip = isset( $request->page ) && $request->page != 1 ? ( $request->page * env( 'PER_PAGE' ) ) - env( 'PER_PAGE' ) : 0;
        $apiContracts = $query->skip( $skip )->take( env( 'PER_PAGE' ) )->get()->toArray();
        foreach ($apiContracts as $key => $con) {
            $json =  json_decode($con['json']);
            // $json->commodity = 'E3595975010A9A9E8E53C515';
            $cont = new  \stdClass();
            $cont->date = $con['date']; // store date
            $cont->id = $json->id;  // Contract
            $cont->settle_at = explode('#', $json->settle_at)[1];   // Elevator
            $cont->start_date = $json->start_date;
            $cont->end_date = $json->end_date;
            //$cont->commodityId = \App\Commodities::where('id', explode('#', $json->commodity)[1])->pluck('id')[0];
            $cont->commodityId = strval($json->commodity);
            $cont->commodity = \App\CommoditiesGeneral::where('commodity_general_id', strval($json->commodity))->pluck('name')[0];
            // $icon = \App\Metadata_commodities::where('commodity_id', explode('#', $json->commodity)[1] )->first();
            // $cont->icon_commodity =  $icon !== null ? \App\Metadata_commodities::where('commodity_id', explode('#', $json->commodity)[1])->pluck('icon_name')[0] : "others";
            $cont->icon_commodity = "others";
            $cont->seller = explode('#', $json->seller)[1];
            $cont->buyer = explode('#', $json->buyer)[1];
            $cont->weight = \App\NumberFormat::simpleFormatGeneral($json->weight);
            if( $cont->seller == $cont->settle_at ) $cont->type = 'Shipping'; else $cont->type = 'Receiving';
            $contract[] = $cont;
        }

        return \App\Api::getPaginator( $contract, $cRows, $request );
    }

    public static function relatedFeature( $id )
    {
        $contract = [];
        $apiContracts = self::select('*')->where('id', $id)->first();
        $json =  json_decode($apiContracts['json']);
        $contract['id'] = $id;
        $contract['date'] = $apiContracts->date;
        if( isset($json->quality_specs) ) {
            foreach ($json->quality_specs as $charact) {
                $cont = new  \stdClass();
                $cont->idFeature = $charact->id;

                $characticQ = \DB::table('characteristics_cmodity_silosys as ccs')
                    ->join('commodities_features as cf', 'cf.commodities_features_id', 'ccs.commodity_feature_id')
                    ->where('ccs.cmodity_contract_id', $id)->where('ccs.cmodity_characteristic_id', $charact->id)
                    ->select('cf.name as cf_name');
                $charactic = $characticQ->get()->toArray();

                $cont->charactic = !empty($charactic) ? $characticQ->pluck('cf_name')[0] : '';
                $features[] = $cont;
                $contract['features'] = $features;
            }
        }
        return $contract;
    }

    public static function contractFeature( $request )
    {
        \DB::beginTransaction();
        try{
            foreach ($request['features'] as $features ){
                if( $features['commodity_feature_id'] !== null ) {
                    $char = \App\Characteristics_cmodity_silosys::where("cmodity_contract_id", $request->id)->where("cmodity_characteristic_id", $features['cmodity_characteristic_id'])->first();
                    if( $char !== null ) {
                        $update = \App\Characteristics_cmodity_silosys::where('cmodity_contract_id', $request->id)
                            ->where('cmodity_characteristic_id', $features['cmodity_characteristic_id'])
                            ->update(['commodity_feature_id' => $features['commodity_feature_id']]);
                    }else {
                        $obj = new \App\Characteristics_cmodity_silosys();
                        $obj->cmodity_contract_id = $request->id;
                        $obj->cmodity_characteristic_id = $features['cmodity_characteristic_id']; //$features->cmodity_characteristic_id;
                        $obj->commodity_feature_id = $features['commodity_feature_id']; // $features->commodity_feature_id;
                        $obj->user_id = $request->user_id;
                        $obj->push();
                    }
                }
            }

            \DB::commit();
            return self::mostrar( null, false, $request );
        } catch( Exception $e ){
            \DB::rollBack();
            return false;
        }
    }

    //@params $request->date_start, $request->date_end, $request->contractno, $request->seller(buyer), $request->filter, $request->page (POST)
    public static function ticketsContract( $request ){
        $array_comm = str_replace('[', "", \App\Commodities::where('commodity_general_id', strval($request->commodityId ))->pluck('id'));
        $array_comm = str_replace(']', "", $array_comm);
        if(empty($array_comm))
            $array_comm = strval(0);
        $request->date_start = $request->date_start. ' 00:00:00';
        $request->date_end = $request->date_end. ' 23:59:59';
        if ($request->filter == 1)
        {
            $errorTickets = \App\Cmodity_loads::selectRaw(DB::raw("'Error' as Error") . ", t.orgticket, t.source_id, t.branch_id, (select name from locations where id = t.branch_id) as location_name, t.contractid as contractno, FORMAT(t.net, (SELECT decimals_in_tickets FROM company_info)) AS weight, t.date_end, s.id as customerName_id, s.name as customerName")
                ->join('batch_tickets as bt', 'cmodity_loads.batch_ticket_id', 'bt.batch_ticket_id')
                ->join('transactions_in as t', 'bt.ticket_id', 't.source_id')
                ->leftJoin('sellers as s', 's.id', 't.seller')
                ->leftJoin('commodities as c', 'c.id', 't.commodity')
                ->where([ ['t.status', 2],['was_processed',1], ['had_error',1] ])
                ->where('t.contractid', $request->contractno)
                ->whereRaw('LOWER(s.email) = '. strtolower("'$request->seller'"))
                ->whereBetween('t.date_end', [$request->date_start, $request->date_end])
                ->whereIn('c.id', array(DB::raw( $array_comm ) ) )
                ->distinct('t.source_id');

            $q = \App\TransactionsIn::selectRaw(DB::raw("'Si' as Seleccionado" ).", transactions_in.source_id, transactions_in.branch_id, (select name from locations where id = transactions_in.branch_id) as location_name, transactions_in.contractid as contractno, transactions_in.orgticket, FORMAT(transactions_in.net, (SELECT decimals_in_tickets FROM company_info)) AS weight, transactions_in.date_end, s.id as customerName_id, s.name as customerName")
                ->leftJoin('sellers as s','s.id','transactions_in.seller')
                ->leftJoin('commodities as c','c.id','transactions_in.commodity')
                ->where('transactions_in.status', 2)->where('transactions_in.contractid', $request->contractno)
                ->whereRaw('LOWER(s.email) = '. strtolower("'$request->seller'"))
                ->whereBetween('transactions_in.date_end', [$request->date_start, $request->date_end] )
                ->whereIn('c.id', array(DB::raw( $array_comm ) ) )
                ->whereNotExists(function($query)
                {
                    $query->select('ticket_id')
                        ->from('batch_tickets')
                        ->whereRaw('transactions_in.source_id = batch_tickets.ticket_id');
                })->distinct('transactions_in.source_id');

            $query = \App\TransactionsIn::selectRaw(DB::raw("'No' as Seleccionado" ).", transactions_in.source_id, transactions_in.branch_id, (select name from locations where id = transactions_in.branch_id) as location_name, transactions_in.contractid as contractno, transactions_in.orgticket, FORMAT(transactions_in.net, (SELECT decimals_in_tickets FROM company_info)) AS weight, transactions_in.date_end, s.id as customerName_id, s.name as customerName")
                ->leftJoin('sellers as s','s.id','transactions_in.seller')
                ->leftJoin('commodities as c','c.id','transactions_in.commodity')
                ->where( 'transactions_in.status', 2)
                ->whereRaw(DB::raw( '(transactions_in.contractid IS NULL or transactions_in.contractid = "")') )
                ->whereRaw('LOWER(s.email) = '. strtolower("'$request->seller'"))
                ->whereBetween('transactions_in.date_end', [$request->date_start, $request->date_end] )
                ->whereIn('transactions_in.commodity', array(DB::raw( $array_comm ) ) )
                ->whereNotExists(function($query)
                {
                    $query->select('ticket_id')
                        ->from('batch_tickets')
                        ->whereRaw('transactions_in.source_id = batch_tickets.ticket_id');
                })
                ->unionAll($errorTickets)->unionAll($q)->distinct('transactions_in.source_id');

        }
        else if($request->filter == 2) {
            $errorTickets = \App\Cmodity_loads::selectRaw(DB::raw("'Error' as Error") . ',' . DB::raw("'N/A' as orgticket") . ", t.source_id, t.branch_id, (select name from locations where id = t.branch_id) as location_name, t.contractno, FORMAT(t.net, (SELECT decimals_in_tickets FROM company_info)) AS weight, t.date_end, b.id as customerName_id, b.name as customerName")
                ->join('batch_tickets as bt', 'cmodity_loads.batch_ticket_id', 'bt.batch_ticket_id')
                ->join('transactions_out as t', 'bt.ticket_id', 't.source_id')
                ->leftJoin('buyers as b', 'b.id', 't.buyer')
                ->leftJoin('commodities as c', 'c.id', 't.commodity')
                ->where([['t.status', 2], ['was_processed', 1], ['had_error', 1]])
                ->where('t.contractno', $request->contractno)
                ->whereRaw('LOWER(b.email) = '. strtolower("'$request->buyer'"))
                ->whereBetween('t.date_end', [$request->date_start, $request->date_end])
                ->whereIn('c.id', array(DB::raw( $array_comm ) ) )
                ->distinct('t.source_id');

            $q = \App\TransactionsOut::selectRaw(DB::raw("'Si' as Seleccionado") . ',' . DB::raw("'N/A' as orgticket") . ", transactions_out.source_id, transactions_out.branch_id, (select name from locations where id = transactions_out.branch_id) as location_name, transactions_out.contractno, FORMAT(transactions_out.net, (SELECT decimals_in_tickets FROM company_info)) AS weight, transactions_out.date_end, b.id as customerName_id, b.name as customerName")
                ->leftJoin('buyers as b', 'b.id', 'transactions_out.buyer')
                ->leftJoin('commodities as c', 'c.id', 'transactions_out.commodity')
                ->where('transactions_out.status', 2)->where('transactions_out.contractno', $request->contractno)
                ->whereRaw('LOWER(b.email) = '. strtolower("'$request->buyer'"))
                ->whereBetween('transactions_out.date_end', [$request->date_start, $request->date_end])
                ->whereIn('c.id', array(DB::raw( $array_comm ) ) )
                ->whereNotExists(function ($query) {
                    $query->select('ticket_id')
                        ->from('batch_tickets')
                        ->whereRaw('transactions_out.source_id = batch_tickets.ticket_id');
                })->distinct('transactions_out.source_id');

            $query = \App\TransactionsOut::selectRaw(DB::raw("'No' as Seleccionado") . ',' . DB::raw("'N/A' as orgticket") . ", transactions_out.source_id, transactions_out.branch_id, (select name from locations where id = transactions_out.branch_id) as location_name, transactions_out.contractno, FORMAT(transactions_out.net, (SELECT decimals_in_tickets FROM company_info)) AS weight, transactions_out.date_end, b.id as customerName_id, b.name as customerName")
                ->leftJoin('buyers as b', 'b.id', 'transactions_out.buyer')
                ->leftJoin('commodities as c', 'c.id', 'transactions_out.commodity')
                ->where('transactions_out.status', 2)
                ->whereRaw(DB::raw( '(transactions_out.contractno IS NULL or transactions_out.contractno = "")') )
                ->whereRaw('LOWER(b.email) = '. strtolower("'$request->buyer'"))
                ->whereBetween('transactions_out.date_end', [$request->date_start, $request->date_end])
                ->whereIn('transactions_out.commodity', array(DB::raw( $array_comm ) ) )
                ->whereNotExists(function ($query) {
                    $query->select('ticket_id')
                        ->from('batch_tickets')
                        ->whereRaw('transactions_out.source_id = batch_tickets.ticket_id');
                })
                ->unionAll($errorTickets)->unionAll($q)->distinct('transactions_out.source_id');

        }

        if($request->filter == 1) $table = 'transactions_in'; else if($request->filter == 2) $table = 'transactions_out';

        $skip = isset( $request->page ) && $request->page != 1 ? ( $request->page * env( 'PER_PAGE' ) ) - env( 'PER_PAGE' ) : 0;
        $cRows = $query->get()->count();
        $query =  $query->skip( $skip )->take( env( 'PER_PAGE' ) )->get()->toArray();
        $char = self::getFeatures($request->contractno);
        foreach ($query as $key => $item) {
            $ticket_id = $item['source_id'];
            $branch_id = $item['branch_id'];
            $query[$key] = $item;

            foreach ($char as $k => $i) {
                $obj = new self;
                $obj->name = $i->id;

                $feturesTicket = DB::table('characteristics_cmodity_silosys as ch')
                    ->selectRaw('ch.commodity_feature_id, ch.cmodity_characteristic_id, t.value')
                    ->leftJoin($table.'_commodities_features as t', function($join) use($ticket_id, $branch_id){
                        $join->on('t.commodities_features_id','ch.commodity_feature_id')
                            ->where('t.source_id', '=', $ticket_id)
                            ->where('t.branch_id', '=', $branch_id);
                    })
                    ->where('ch.cmodity_characteristic_id', $i->id )
                    ->where('ch.cmodity_contract_id', '=', $request->contractno )
                    ->first();

                $obj->commodities_feature_id = isset( $feturesTicket->commodity_feature_id ) && !is_null( $feturesTicket->commodity_feature_id ) ? $feturesTicket->commodity_feature_id : '';
                $obj->value = isset( $feturesTicket->value ) && !is_null( $feturesTicket->value ) ? $feturesTicket->value : '';
                $query[$key]['features'][] = $obj;

            }
        }

        return \App\Api::getPaginator( $query, $cRows, $request );

    }


    //@params $request->source_id, $request->branch_id, $request->contractno, $request->filter(POST)
    public static function linkTickets( $request ){
        try{
            if( $request->filter == 1 ){
                $table = 'transactions_in';
                $contract = 'contractid';
            } else if( $request->filter == 2 ){
                $table = 'transactions_out';
                $contract = 'contractno';
            }

            $data = [];
            $data['filter'] = $request->filter;
            $data['contractno'] = $request->contractno;

            foreach ( $request['tickets'] as $key => $item) {
                foreach ( $item as $k => $value ){
                    $data['tickets'][$k] = json_encode($value['source_id']);

                    $update = DB::table($table)->where('source_id', $value['source_id'])->where('branch_id', $value['branch_id'])->first();

                    if( !is_null($update) ) {
                        DB::table( $table )
                            ->where('source_id', $value['source_id'])
                            ->where('branch_id', $value['branch_id'])
                            ->update([$contract => $request->contractno]);
                    }
                }
                $default_location = $value['branch_id'] !== null ? $value['branch_id'] : \App\Company_info::pluck('default_location')[0];

               \App\SQS::send([
                    'destination' => $default_location,
                    'action' => 'linked',
                    'type' => 'REQUEST',
                    'group' => 'tanks',
                    'message' => json_encode($data),
                ], 'local', $default_location, null);

            }

        }catch( \Exception $e){
            return $e->getMessage();
        }
    }


    public static function storeValuesFeatures( $request ){
        try{
            if( $request->filter == 1 ) $table = 'transactions_in'; else if( $request->filter == 2 ) $table = 'transactions_out';

            foreach ($request['features'] as $features ){

                $data = [
                    'transaction_id' => isset($features['transaction_id']) && !is_null($features['transaction_id']) ? $features['transaction_id'] : '',
                    'commodities_features_id' => isset($features['commodities_features_id']) && !is_null($features['commodities_features_id']) ? $features['commodities_features_id'] : '',
                    'value' => isset($features['value']) && !is_null($features['value']) ? $features['value'] : '',
                    'source_id' => isset($features['source_id']) && !is_null($features['source_id']) ? $features['source_id'] : '',
                    'branch_id' => isset($features['branch_id']) && !is_null($features['branch_id']) ? $features['branch_id'] : \App\Company_info::pluck('default_location')[0],
                    'mstatus' => isset($features['mstatus']) && !is_null($features['mstatus']) ? $features['mstatus'] : 1
                ];

                if( $features['transaction_id'] !== '' && $features['commodities_features_id'] !== '' ) {
                    $comm = DB::table($table . '_commodities_features')->where('source_id', $features['source_id'])->where('branch_id', $features['branch_id'])->where('commodities_features_id', $features['commodities_features_id'])->first();
                    if( !is_null($comm) ){
                        $action = DB::table($table . '_commodities_features')
                            ->where('source_id', $features['source_id'])
                            ->where('branch_id', $features['branch_id'])
                            ->where('commodities_features_id', $features['commodities_features_id']);

                        if( $features['value'] === null ){
                            $action->delete();
                            self::sqs_features($data, $act='deleteCommodityFeature', $table);
                        }else{
                            $action->update(['value' => $features['value']]);
                            self::sqs_features($data, $act='createOrUpdateCommodityFeature', $table);
                        }
                    }else {
                        DB::table($table . '_commodities_features')->insert($data);
                        self::sqs_features($data, $act='createOrUpdateCommodityFeature', $table);
                    }
                }
            }

            return true;
        } catch( \Exception $e ){
            \DB::rollBack();
            return $e->getMessage();
        }
    }

    protected static function sqs_features($data, $action, $table){
        $default_location = $data['branch_id'] !== null ? $data['branch_id'] : \App\Company_info::pluck('default_location')[0];

        \App\SQS::send([
            'destination' => $default_location,
            'action' => $action,
            'type' => 'REQUEST',
            'group' => 'features',
            'transaction_type' => $table,
            'message' => json_encode($data)
        ], 'local', $default_location, null);
    }

    protected static function getFeatures( $contract ){
        $query = self::select('json')->where('id', $contract)->first();
        $json = json_decode($query['json']);
        $data = [];
        if( isset($json->quality_specs)) {
            foreach ($json->quality_specs as $k => $char) {
                if ($char->certification_type === "SiloSys") {
                    $characteristics = $char;
                    $data[] = $characteristics;

                }
            }
        }
        return $data;
    }

}