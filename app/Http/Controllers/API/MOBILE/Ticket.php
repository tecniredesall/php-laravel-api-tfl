<?php

namespace App\Http\Controllers\API\MOBILE;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;

class Ticket extends BaseController {
    
    /**
     * Display a listing of the tickets.
     *
     * @return json
     */
    public function search( Request $request ){

        if( $request->where == 'receive' ) {
            $table = 'transactions_in';
            $fields = "transactions_in.date_start, transactions_in.user, commodities.id, commodities.name as commodity_name, tanks.id, tanks.name as tank_name, sellers.id, sellers.name as seller_name,
            farms.id, farms.name as farm_name, transactions_in.truckname, transactions_in.origin, transactions_in.drivername, transactions_in.orgticket, transactions_in.trailerlicense,
            transactions_in.orgweight, transactions_in.moisture, transactions_in.testwt, transactions_in.dryshper, transactions_in.drychrat, transactions_in.status";
        }elseif( $request->where == 'shipping' ) {
            $table = 'transactions_out';
            $fields = "transactions_out.date_start, transactions_out.user, commodities.id, commodities.name as commodity_name, tanks.id, tanks.name as tank_name, buyers.id, buyers.name as buyer_name, transactions_out.truckname,
             transactions_out.drivername, transactions_out.moisture, transactions_out.testwt,
              transactions_out.dryshper, transactions_out.drychrat, transactions_out.status";
        }else {
            $table = 'cashsales';
            $fields = "commodities.id, commodities.name as commodity_name, tanks.id, tanks.name as tank_name, cashsales.*";
        }

        $q = \DB::table( $table )->select(\DB::raw($fields));
        if( $table !== 'cashsales' )
            $q = $q->join('commodities', $table.'.commodity', 'commodities.id');
        else
            $q = $q->join('commodities', $table.'.commodity_id', 'commodities.id');

        if( $table == 'transactions_in' )
            $q = $q->join('sellers', $table.'.seller', 'sellers.id')->join('farms', $table.'.farm', 'farms.id');
        if( $table == 'transactions_out' )
            $q = $q->join('buyers', $table.'.buyer', 'buyers.id');
        if( $table !== 'cashsales' )
            $q = $q->join('tanks', $table.'.tank', 'tanks.id');
        else
            $q = $q->join('tanks', $table.'.tank_id', 'tanks.id');

        $q = $q->where([
            [$table.'.source_id', $request->source_id],
            [$table.'.branch_id', $request->branch_id]
        ]);

        if( $q->get()->count() > 0 ) {
            return $q->get();
        }else {
            return 'Ticket N. ' . $request->source_id . ' not found';
        }

    }

    /**
     * Reverting ticket.
     *
     * @return mixin
     */
    public function revert( Request $request ){
        $id = $request->id;
        $branch_id = $request->branch_id;
        try{
            if( $request->where == 'receive' ) {
                //$obj = \App\TransactionsIn::findOrFail($id);
                $obj = \App\TransactionsIn::select('id', 'branch_id', 'source_id')->where([
                    ['branch_id', $branch_id],
                    ['source_id', $id]
                ])->get();
                //update status to 12 (In process)
                \App\TransactionsIn::where([
                    ['branch_id', $branch_id],
                    ['source_id', $id]])->update(['status' => 12]);
            }elseif( $request->where == 'shipping' ) {
                //$obj = \App\TransactionsOut::findOrFail($id);
                $obj = \App\TransactionsOut::select('id', 'branch_id', 'source_id')->where([
                    ['branch_id', $branch_id],
                    ['source_id', $id]
                ])->get();
                //update status to 12 (In process)
                \App\TransactionsOut::where([
                    ['branch_id', $branch_id],
                    ['source_id', $id]])->update(['status' => 12]);
            }else {
                //$obj = \App\Cashsales::findOrFail($id);
                $obj = \App\Cashsales::select('id', 'branch_id', 'source_id')->where([
                    ['branch_id', $branch_id],
                    ['source_id', $id]
                ])->get();
                //update status to 12 (In process)
                \App\Cashsales::where([
                    ['branch_id', $branch_id],
                    ['source_id', $id]])->update(['status' => 12]);
            }
            //$source_id = $obj->source_id;
            $actions = [
                'id' => $request->id,
                'user_id' => $request->user()->id,
            ];

            $array = [
                'group' => $obj[0]->branch_id,
                'type' => 'REQUEST',
                'action' => ( $request->where == 'receive' ) ? 'revertTicketReceive' : ( ( $request->where == 'shipping' ) ? 'revertTicketShipping' : 'revertTicketCash' ),
                'destination' => $obj[0]->branch_id,
                'message' => json_encode( $actions, JSON_FORCE_OBJECT )
            ];

            return \App\SQS::send( $array,  'local', null, null );
        } catch( Exception $e ){
            return 'Ticket N. ' . $request->id. ' not found';
        }
    }
}
