<?php

namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helpers\ReportsHTMLtoPDF;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Exports\BatchReportExport;

class Batch extends Api
{
    protected $table = 'batch';

    protected $fillable = [];

    protected $hidden = [];

    //@params $request->contractno, $request->filter, $request->page  (POST)
    public static function batchContract( $request )
    {
        try{
            if ( $request->filter == 1 ){
                $table = 'transactions_in';
                $customerName = 'sellers';
            }  else if( $request->filter == 2 ) {
                $table = 'transactions_out';
                $customerName = 'buyers';
            }

            $bt_batch = \App\Batch::where('cmodity_contract_id', $request->contractno)->where('ticket_type_id', $request->filter)
                ->join('commodities_general as cg','cg.commodity_general_id','commodity_id')
                ->join($customerName,$customerName.'.id','member_id')
                ->join('users','users.id','user_id')
                ->selectRaw("batch.batch_id, batch.no_batch, batch.ticket_type_id, batch.cmodity_contract_id, batch.start_date, batch.end_date, batch.status, 
            batch.storage_date, $customerName.name as customerName, cg.name as commodity_name, users.name as user_name")
                ->groupBy('batch.no_batch');

            $cRows = $bt_batch->get()->count();
            $skip = isset( $request->page ) && $request->page != 1 ? ( $request->page * env( 'PER_PAGE' ) ) - env( 'PER_PAGE' ) : 0;
            $query = $bt_batch->skip( $skip )->take( env( 'PER_PAGE' ) )->get()->toArray();



            foreach ($query as $key => $item) {
                $ticketsbyBatch = \App\Batch_tickets::where('batch_id', $item['batch_id'])->get()->count();
                $query[$key]['tickets'] = $ticketsbyBatch;

                $batchId = \App\Batch::where('cmodity_contract_id', $item['cmodity_contract_id'])
                    ->join('batch_tickets as bt','batch.batch_id','bt.batch_id')
                    ->where('bt.batch_id', $item['batch_id'])->get()->toArray();

                $totalNet = 0;
                foreach ($batchId as $k => $branch) {

                    $total_net = \DB::table($table)
                        ->where('source_id',  $branch['ticket_id'])
                        ->where('branch_id', $branch['branch_id'])
                        ->selectRaw('net as total_net')
                        ->first();

                    $totalNet = $totalNet + $total_net->total_net;

                }
                $general = \App\Company_info::selectRaw('decimals_in_general')->pluck('decimals_in_general')[0];
                $totalNet = number_format($totalNet, $general);

                $query[$key]['total_net'] = $totalNet;
            }

            return \App\Api::getPaginator( $query, $cRows, $request );

        } catch( \Exception $e ){
            return $e->getMessage();
        }
    }

    //@params $request->cmodity_contract_id, $request->filter, $request->page, $batch_id( ee1b7de8-1b91-11ea-8071-0a2edc50a93a ) (GET)
    public static function batchTicket( $batch_id, $request )
    {
        try{
            $skip = isset( $request->page ) && $request->page != 1 ? ( $request->page * env( 'PER_PAGE' ) ) - env( 'PER_PAGE' ) : 0;
            $batchTicket = \App\Batch_tickets::where('batch_id', $batch_id);

            if( $request->filter == 1 ){
                $table = 'transactions_in';
                $contract = 'contractid';
            } else if( $request->filter == 2 ){
                $table = 'transactions_out';
                $contract = 'contractno';
            }

            $batchTicketQ = $batchTicket->join($table, 'ticket_id', 'source_id')
                ->where($table.'.status', 2)
                ->where($table.'.'.$contract, $request->cmodity_contract_id)
                ->select($table.'.*',$table.'.source_id as sourceIn')
                ->skip( $skip )->take( env( 'PER_PAGE' ) );

            $batchTicket = $batchTicketQ->get()->toArray();

            foreach( $batchTicket as $key => $value ){
                $ticket_id = $value['sourceIn'];
                $feturesTicket = \App\Batch::selectRaw('batch.cmodity_contract_id, c.cmodity_characteristic_id, t.value, batch.ticket_type_id')
                    ->join('batch_tickets as bt','batch.batch_id','bt.batch_id')
                    ->join('characteristics_cmodity_silosys as c','batch.cmodity_contract_id','c.cmodity_contract_id')
                    ->leftJoin($table.'_commodities_features as t', function($join)
                    {
                        $join->on('bt.ticket_id','=','t.transaction_id');
                        $join->on('c.commodity_feature_id','=','t.commodities_features_id');
                    })
                    ->where('t.source_id', '=', $ticket_id)
                    ->where('batch.ticket_type_id', '=', $request->filter )
                    ->get();

                $batchTicket[$key]['features'] = $feturesTicket;
            }

            $cRows = $batchTicketQ->get()->count();
            return \App\Api::getPaginator( $batchTicket, $cRows, $request );

        } catch( \Exception $e ){
            return $e->getMessage();
        }
    }

    //@params $request->uuid_batch_id, $request->type_file (POST)
    public static function generateFiles( $request )
    {
        try {
            $uuid_batch_id = $request->uuid_batch_id;
            $lang = $request->lang;
            $batchTickets = [];
            $batch = \App\Batch::selectRaw('no_batch, ticket_type_id, cmodity_contract_id, start_date, end_date, cg.name, batch.status')
                ->join('commodities_general as cg', 'batch.commodity_id', 'cg.commodity_general_id')
                ->where('batch_id', $uuid_batch_id)->first();

            if( !is_null($batch) ){
                if ($batch->ticket_type_id == 1) {
                    $table = 'transactions_in';
                    $orgticket = 'orgticket,';
                } else if ($batch->ticket_type_id == 2) {
                    $table = 'transactions_out';
                    $orgticket = '';
                }

                $apiContracts = \App\Contracts::where('id', $batch->cmodity_contract_id)->first();
                $json = json_decode($apiContracts['json']);
                if (!empty($batch)) {
                    $batch_tickets = \App\Batch_tickets::selectRaw('ticket_id, branch_id')
                        ->where('batch_id', $uuid_batch_id)->get()->toArray();

                    $batch['no_batch'] = $batch->no_batch;
                    $batch['seller'] = explode('#', $json->seller)[1];
                    $batch['buyer'] = explode('#', $json->buyer)[1];
                    $batch['elevator'] = explode('#', $json->settle_at)[1];   // Elevator
                    $batch['decimals_in_tickets'] = \App\Company_info::pluck('decimals_in_tickets')[0];
                    $batch['metric_system'] = \App\Company_info::pluck('metric_system_id')[0] == 1 ? 'lb' : 'kg';
                    $batch['lang'] = $lang;
                    $estado = $batch['status'];
                    if($estado == 1) $status = 'Draft'; elseif ($estado == 2) $status = 'Sent';  elseif ($estado == 3) $status = 'Approved'; elseif ($estado == 4) $status = 'Cmodity';
                    $batch['status'] = $status;

                    $batchTickets['contract'] = $batch;

                    if (!empty($batch_tickets)) {
                        foreach ($batch_tickets as $key => $bt) {
                            $obj = new \stdClass();
                            $obj->ticket_id = $bt['ticket_id'];

                            $charact = \DB::table($table . '_commodities_features as tcf')
                                ->selectRaw('ccs.cmodity_characteristic_id, tcf.value')
                                ->join('characteristics_cmodity_silosys as ccs', 'ccs.commodity_feature_id', 'tcf.commodities_features_id')
                                ->where([['tcf.source_id', $bt['ticket_id']], ['tcf.branch_id', $bt['branch_id']], ['ccs.cmodity_contract_id', $batch->cmodity_contract_id]])
                                ->orderBy('ccs.cmodity_characteristic_id', 'ASC')->get()->toArray();

                            $totalNet = \DB::table($table)
                                ->where([['source_id', $bt['ticket_id']], ['branch_id', $bt['branch_id']]])->selectRaw($orgticket . 'date_start, net')->first();

                            if (!is_null($totalNet)) {
                                $obj->orgticket = isset( $totalNet->orgticket ) ? $totalNet->orgticket : 'N/A';
                                $obj->date_start = $totalNet->date_start;
                                $obj->net = $totalNet->net;
                            }

                            $obj->characteristics = $charact;
                            $batchTickets['tickets'][$key] = $obj;

                        }
                    }

                    $fileName = 'preApproveTickets';
                    $file = view('partials.excel.preApproveTickets',$batchTickets);
                    $type = isset( $request->type_file ) ? strtolower( $request->type_file ) : 'pdf';
                    if( isset( $request->type_file ) and ( $type == 'csv' ) )
                    {
                        return Excel::download(new BatchReportExport($batchTickets), 'users.csv');
                    } else if (isset( $request->type_file ) and ( $type == 'xlsx' ))
                    {
                        Storage::disk('s3')->put(env("INSTANCE_ID") . '/reports/xlsx/' . $fileName . '.xlsx', $file);
                        $reportXLS = new ReportsHTMLtoPDF(view('partials.excel.preApproveTickets', $batchTickets)->render(), $fileName, "", "s3", $fileName, $type);
                        $xlsFile = $reportXLS->output();
                        $data = [
                            'xls' => $xlsFile['data']['file'],
                            'seller_email' => $batch['seller']
                        ];
                    } else {
                        $reportPDF = new ReportsHTMLtoPDF(view('partials.pdf.preApproveTickets', $batchTickets)->render(), $fileName, "", "s3", $fileName, 'pdf');
                        $pdfFile = $reportPDF->savePDF()->output();
                        $data = [
                            'pdf' => $pdfFile['data']['file'],
                            'seller_email' => $batch['seller'],
                            's3' => $pdfFile['data']['s3']
                        ];
                    }

                    return $data;
                } else {
                    return ['error' => 'No se encontro informacion'];
                }
            }

        }catch( \Exception $e){
            return $e->getMessage();
        }
    }

    //@params $request->uuid_batch_id, $request->seller_email_cc(optional)  (POST)
    public static function sendmailPrepprove( $request )
    {
        $url =  \App\Batch::generateFiles( $request );

        $data = [
            'email' => $url['seller_email'],
            'companyName' => \App\Company_info::pluck('name')[0] . ' ' . \App\Company_info::pluck('address')[0]
        ];

        Mail::send('mail.reports.approve', $data, function ($message) use ($url) {
            $message->from(env('MAIL_FROM_ADDRESS'), 'SiloSys Report');
            $message->subject('Report');
            $message->to($url['seller_email']);
            $message->attachData(Storage::disk('s3')->get($url['s3']), '', ['as' => 'preApproveTickets.pdf', 'mime' => 'application/pdf']);
        });

        return true;
    }


    public static function featuresValue( $batch_id, $request ){
        try{
            $batchTicket = \App\Batch_tickets::where('batch_id', $batch_id)->get()->toArray();
            if( $request->filter == 1 ) $table = 'transactions_in'; else if( $request->filter == 2 ) $table = 'transactions_out';
            $features = [];
            foreach ($batchTicket as $key => $item) {
                $obj = new \stdClass();
                $obj->ticket_id = $item['ticket_id'];

                $feturesTicket = \App\Batch::selectRaw('c.cmodity_characteristic_id, t.value')
                    ->join('batch_tickets as bt','batch.batch_id','bt.batch_id')
                    ->join('characteristics_cmodity_silosys as c','batch.cmodity_contract_id','c.cmodity_contract_id')
                    ->leftJoin($table.'_commodities_features as t', function($join)
                    {
                        $join->on('bt.ticket_id','=','t.transaction_id');
                        $join->on('c.commodity_feature_id','=','t.commodities_features_id');
                    })
                    ->where('bt.ticket_id', '=', $item['ticket_id'])
                    ->where('batch.ticket_type_id', '=', $request->filter )
                    ->where(DB::raw('COALESCE(value,0)'), 0)
                    ->orderBy('bt.ticket_id', 'DESC')->get()->toArray();

                $obj->characteristics = $feturesTicket;
                $features['data'][$key] = $obj;

            }

            return $features;
        }catch( \Exception $e ){
            return $e->getMessage();
        }
    }

    // @params $request->ticket_type_id, $request->cmodity_contract_id, $request->commodity_id, $request->member_id,$request->user_id, $request->status, $request['tickets'] : [{"ticket_id": 20009,"branch_id": 1},{"ticket_id": 20008,"branch_id": 1},{"ticket_id": 20007,"branch_id": 1}]  (POST)
    public static function ticketsBatch( $request ){
        try{
            $batch = \App\Batch::select('*')->where( 'cmodity_contract_id' , $request->cmodity_contract_id)->orderBy('no_batch', 'desc')->first();
            if( $request->ticket_type_id == 1 ){
                $table = 'transactions_in';
                $contract = 'contractid';
            } else if( $request->ticket_type_id == 2 ){
                $table = 'transactions_out';
                $contract = 'contractno';
            }
            $bt = []; $success = false;
            $storeBatch =
                [
                    'batch_id' => (string) Str::uuid(),
                    'no_batch' => empty($batch) ? 1 : $batch['no_batch']+1,
                    'ticket_type_id' => $request->ticket_type_id,
                    'cmodity_contract_id' => $request->cmodity_contract_id,
                    'commodity_id' => $request->commodity_id,
                    'member_id' => $request->member_id,
                    'start_date' => date('Y-m-d'),
                    'end_date' => date('Y-m-d'),
                    'status' => $request->status,
                    'user_id' => $request->user_id
                ];

            DB::table('batch')->insert($storeBatch);
            $bt = $storeBatch;

            foreach ( $request['tickets'] as $ticket )
            {
                $batchTickets =
                    [
                        'batch_ticket_id' => (string) Str::uuid(),
                        'batch_id' => $storeBatch['batch_id'],
                        'ticket_id' => $ticket['ticket_id'],
                        'branch_id' => $ticket['branch_id']
                    ];

                $batchT = DB::table('batch_tickets')->where( [ ['ticket_id', $ticket['ticket_id']],['branch_id', $ticket['branch_id']] ])->first();
                if(!is_null($batchT)) {
                    $cmLoads = \App\Cmodity_loads::where('batch_ticket_id', $batchT->batch_ticket_id)->first();
                    if( $cmLoads['had_error'] === 1 ){
                        DB::table('batch_tickets')->where( [ ['ticket_id', $ticket['ticket_id']],['branch_id', $ticket['branch_id']] ])->delete();
                        \App\Cmodity_loads::where('batch_ticket_id', $batchT->batch_ticket_id )->delete();
                    }
                }

                if( is_null( DB::table('batch_tickets')->where( [ ['ticket_id', $ticket['ticket_id']],['branch_id', $ticket['branch_id']] ])->first() ) ){
                    DB::table($table)->where( [ ['source_id', $ticket['ticket_id']],['branch_id', $ticket['branch_id']] ])->update([$contract => $request->cmodity_contract_id]);
                    DB::table('batch_tickets')->insert($batchTickets);
                    $success = true;
                    $bt['tickets'][] = $batchTickets;
                }else{
                    $bt['tickets'][] = $ticket['ticket_id'];
                }
            }

            if( $success ){
                return response()->json(array(
                    'success'=> $success,
                    'message' => '',
                    'data' => $bt
                ));
            }else{
                return response()->json(array(
                    'success'=> $success,
                    'message' => 'The ticket already exists in another batch'
                ));
            }

        }catch( \Exception $e ) {
            return $e->getMessage();
        }
    }

    public static function attachTicketsBatch( $request ){
        try{
            if( $request->ticket_type_id == 1 ){
                $table = 'transactions_in';
                $contract = 'contractid';
            } else if( $request->ticket_type_id == 2 ){
                $table = 'transactions_out';
                $contract = 'contractno';
            }
            $bt = [];   $success = false;
            foreach ( $request['tickets'] as $ticket )
            {
                $batchTickets =
                    [
                        'batch_ticket_id' => (string) Str::uuid(),
                        'batch_id' => $request->batch_id,
                        'ticket_id' => $ticket['ticket_id'],
                        'branch_id' => $ticket['branch_id']
                    ];
                $cmodity_contract_id = DB::table('batch')->where( 'batch_id', $request->batch_id)->pluck('cmodity_contract_id')[0];
                if( is_null( DB::table('batch_tickets')->where( [ ['ticket_id', $ticket['ticket_id']],['branch_id', $ticket['branch_id']] ])->first() ) ){
                    DB::table($table)->where( [ ['source_id', $ticket['ticket_id']],['branch_id', $ticket['branch_id']] ])->update([$contract => $cmodity_contract_id]);
                    DB::table('batch_tickets')->insert($batchTickets);
                    $bt['tickets'][] = $batchTickets;
                    $success = true;
                }else{
                    $bt['tickets'][] = $ticket['ticket_id'];
                }
            }

            if( $success ){
                return response()->json(array(
                    'success'=> $success,
                    'message' => '',
                    'data' => $bt
                ));
            }else{
                return response()->json(array(
                    'success'=> $success,
                    'message' => 'The ticket already exists in another batch'
                ));
            }
        }catch( \Exception $e ) {
            return $e->getMessage();
        }
    }

    public static function deleteTicketsBatch( $request ){
        \DB::beginTransaction();
        try{
            $delete = \App\Batch_tickets::where([ ['batch_id', $request['batch_id']], ['ticket_id', $request['ticket_id'] ] ]);
            $bt = $delete->first();
            if( !empty( $bt ) ){
                $batchT = \App\Batch_tickets::where('batch_id', $request['batch_id'])->count();

                if( $batchT <= 1 ){
                    \App\Batch::where('batch_id', $request['batch_id'] )->delete();
                }

                \App\Batch_tickets::where('batch_ticket_id', $bt->batch_ticket_id )->delete();

                $data = ['ticket_id' => $request['ticket_id'], 'batch_id' => $request['batch_id'] ];
            }else{
                $data = ['ticket_id' => 'Ticket not found'];
            }
            \DB::commit();
            return response()->json(['success' => true, 'message' => '', 'data'=> $data]);
        }catch( \Exception $e){
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    //  @params $request->batch_id  (GET)
    public static function deleteBatch( $batch_id ){
        \DB::beginTransaction();
        try{
            $batch = \App\Batch::where('batch_id', $batch_id )->first();
            if( !is_null( $batch )){
                $bt = \App\Batch_tickets::where('batch_id', $batch->batch_id)->get()->toArray();

                foreach ( $bt as $ticket ){
                    \App\Batch_tickets::where('ticket_id', $ticket['ticket_id'])->delete();
                }
                \App\Batch::where('batch_id', $batch_id )->delete();
            }
            \DB::commit();
            return response()->json(['success'=> true, 'message' => '', 'data' => $batch_id]);
        }catch( \Exception $e){
            \DB::rollBack();
            return response()->json(['success'=> false,'message' => $e->getMessage() ]);
        }
    }

    // @params $request->batch_id, $request->filter, $request->page
    public static function batchDetail( $batch_id, $request ){
        try{

            if($request->filter == 1) $table = 'transactions_in'; else if($request->filter == 2) $table = 'transactions_out';
            $query = \App\Batch::where('batch.batch_id', $batch_id)->join('batch_tickets as bt', 'bt.batch_id', 'batch.batch_id');
            $skip = isset( $request->page ) && $request->page != 1 ? ( $request->page * env( 'PER_PAGE' ) ) - env( 'PER_PAGE' ) : 0;
            $cRows = $query->get()->count();
            $query =  $query->skip( $skip )->take( env( 'PER_PAGE' ) )->get()->toArray();

            foreach ($query as $key => $item) {
                $ticket_id = $item['ticket_id'];
                $branch_id = $item['branch_id'];
                $ticketData = DB::table($table)->where('source_id', $ticket_id)->where('branch_id', $item['branch_id'])->first();
                $query[$key] = $item;
                $query[$key]['start_date'] = !is_null($ticketData) ? $ticketData->date_start : '';
                $query[$key]['end_date'] = !is_null($ticketData) ? $ticketData->date_end : '';
                $query[$key]['net'] = !is_null($ticketData) ? $ticketData->net : '';
                $query[$key]['orgticket'] = $request->filter == 1 ? $ticketData->orgticket : 'N/A';
                $query[$key]['location_name'] = DB::table('locations')->where('id', $item['branch_id'])->pluck('name')[0];
                if( $request->filter == 1 ) $contract = !empty($ticketData) ? $ticketData->contractid : ''; elseif ( $request->filter == 2 ) $contract = !empty($ticketData) ? $ticketData->contractno : '';
                $query[$key]['link'] = !is_null( $contract ) ? true : false;

                $char = \App\Contracts::getFeatures($item['cmodity_contract_id']);

                foreach ($char as $k => $i) {
                    $obj = new self;
                    $obj->name = $i->id;

                    $feturesTicket = DB::table('characteristics_cmodity_silosys as ch')
                        ->selectRaw('ch.commodity_feature_id, ch.cmodity_characteristic_id, t.value')
                        ->leftJoin($table.'_commodities_features as t', 't.commodities_features_id','ch.commodity_feature_id')
                        ->where('t.source_id', '=', $ticket_id)->where('t.branch_id', '=', $branch_id)->where('ch.cmodity_characteristic_id', $i->id )
                        ->where('ch.cmodity_contract_id', '=', $item['cmodity_contract_id'] )
                        ->first();

                    $obj->commodities_feature_id = isset( $feturesTicket->commodity_feature_id ) && !is_null( $feturesTicket->commodity_feature_id ) ? $feturesTicket->commodity_feature_id : '';
                    $obj->value = isset( $feturesTicket->value ) && !is_null( $feturesTicket->value ) ? $feturesTicket->value : '';
                    $query[$key]['features'][] = $obj;

                }
            }

            return \App\Api::getPaginator( $query, $cRows, $request );

        }catch( \Exception $e){
            return $e->getMessage();
        }
    }

    // @params $request->batch_id, $request->branch_id, $request->status
    public static function changeStatusBatch( $request ){
        //  \DB::beginTransaction();
        try{
            $member = 'resource:io.grainchain.Member#';
            $uuid_batch_id = new \stdClass();
            $uuid_batch_id->uuid_batch_id = $request['batch_id'];
            $uuid_batch_id->lang = $request['lang'];

            $status = intval($request['status']);
            $batch = \App\Batch::where('batch_id', $request['batch_id']);
            $b = $batch->first();
            $table = 'transactions_in'; $type = 'receive';
            if($b['ticket_type_id'] == 1){
                $table = 'transactions_in';
                $type = 'receive';
                $contractId = 'contractid';
                $license = 'trailerlicense';
                $fieldSelect = ', orgticket, farm';
            } else if($b['ticket_type_id'] == 2){
                $table = 'transactions_out';
                $type = 'shipping';
                $contractId = 'contractno';
                $license = 'trucklicense';
                $fieldSelect = '';
            }

            if( $status === 4 ){
                if (!empty($b)) {
                    $tickets = \App\Batch_tickets::selectRaw('ticket_id, batch_ticket_id, branch_id')->where('batch_tickets.batch_id', $request['batch_id'])->get()->toArray();
                    $contract = \App\Contracts::where( 'id', ($b['cmodity_contract_id']) )->first();
                    $jsonContract = json_decode($contract['json']);
                    $batch->update(['status' => $status]);
                    foreach ($tickets as $ticket) {
                        $fields = \DB::table($table.' as t')
                            ->selectRaw('date_end, net, netdrywt, testwt, moisture, branch_id,'. $contractId.', drivername, truckname, trucklicense,'. $license.$fieldSelect)
                            ->where('t.source_id', $ticket['ticket_id'])->where('t.branch_id', $ticket['branch_id'])->first();

                        $json = [
                            "id" => $ticket['ticket_id'],
                            "receiving_date" => date("Y-m-d\TH:i:s.000\Z", strtotime($fields->date_end)),
                            "seller" => ( $b['ticket_type_id'] == 1 ) ? $jsonContract->seller : $jsonContract->buyer,
                            "elevator" => $member.\App\Company_info::select('email')->pluck('email')[0],
                            "commodity" => $b['commodity_id'],
                            "weight" => $fields->net,
                            "silo_dry_weight" => $fields->netdrywt,
                            "test_weight" => $fields->testwt,
                            "test_serial" => 'N/A',
                            "moisture" => $fields->moisture,
                            "certificate_url" => env('APP_URL') . "/api/mobile/ticket/download/". $type . "/" . $ticket['ticket_id']. "/" . $fields->branch_id,
                            "gc_contract" => $b['ticket_type_id'] == 1 ? $fields->contractid : $fields->contractno,
                            "original_ticket" => ( $b['ticket_type_id'] == 1 ) ? $fields->orgticket : '',
                            "farm_name" =>  $b['ticket_type_id'] == 1 ? \App\Farms::select('id', 'name')->where('id', $fields->farm)->pluck('name')[0] : '',
                            "location_name" => \App\Locations::select('id','name')->where('id', $fields->branch_id)->pluck('name')[0],
                            "driver_name" => $fields->drivername,
                            "truck_plate" => $fields->truckname,
                            "trailer_plate" => $b['ticket_type_id'] == 1 ? $fields->trailerlicense : $fields->trucklicense
                        ];

                        $feturesTicket = DB::select(DB::raw('select ch.cmodity_characteristic_id as name, tcf.value 
                            from (select cmodity_characteristic_id, commodity_feature_id,cmodity_contract_id from characteristics_cmodity_silosys where cmodity_contract_id = ' . "'".$b["cmodity_contract_id"]."'"  . ') as ch 
                            left join (select value, commodities_features_id, source_id,  branch_id from transactions_in_commodities_features t where t.source_id = ' . "'".$ticket['ticket_id']."'"  . ' and t.branch_id = ' . "'".$fields->branch_id."'"  . ') as tcf
                            on tcf.commodities_features_id = ch.commodity_feature_id'));

                        $json['quality_specs'] = $feturesTicket;

                        $data = [
                            'cmodity_load_id' => (string)Str::uuid(),
                            'instance_id' => env('INSTANCE_ID'),
                            'branch_id' => $fields->branch_id,
                            'batch_id' => $request['batch_id'],
                            'batch_ticket_id' => $ticket['batch_ticket_id'],
                            'response' => json_encode($json),
                            'metric_system_id' =>  \App\Company_info::select('*')->pluck('metric_system_id')[0],
                            'storage_date' => now(),
                            'user_id' => $b['user_id']
                        ];

                        if( is_null(\App\Cmodity_loads::where('batch_id', $request['batch_id'])->where('batch_ticket_id', $ticket['batch_ticket_id'])->first() ) ){
                            DB::table('cmodity_loads')->insert($data);
                        }
                    }
                    self::sendmailPrepprove($uuid_batch_id);
                }

            } else if( $status === 2 ) {
                $batch->update(['status' => $status]);
                self::sendmailPrepprove($uuid_batch_id);
            } else {
                if( !empty( $b )){
                    $batch->update(['status' => $status]);
                }
            }

            // \DB::commit();
            return response()->json(['success'=> true,'message' => '' ]);
        }catch( \Exception $e){
            \DB::rollBack();
            return response()->json(['success'=> false,'message' => '' ]);
        }
    }

    public static function log( $batch_id, $old_status, $new_status, $user_id )
    {
        $obj = new \App\Batch_status_log();
        $obj->log_id = (string)Str::uuid();
        $obj->batch_id = $batch_id;
        $obj->old_status = $old_status;
        $obj->new_status = $new_status;
        $obj->user_id = $user_id;
        $obj->push();
    }
}