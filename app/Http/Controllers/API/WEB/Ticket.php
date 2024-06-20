<?php

namespace App\Http\Controllers\API\WEB;

use App\Cashsales;
use App\SQS;
use App\TransactionsIn;
use App\TransactionsOut;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;

class Ticket extends BaseController
{

    /**
     * Display a listing of the tickets that will be reverserd.
     *
     * @return json
     */
    public function search(Request $request)
    {
        $general = \App\Company_info::selectRaw('metric_system_id, decimals_in_tickets, decimals_in_general, decimals_for_money')->pluck('decimals_in_tickets')[0];
        if ($request->where == 'receive') {
            $table = 'transactions_in';
            $fields = "CONCAT(transactions_in.date_start, ' UTC') AS date_start, CONCAT(transactions_in.date_end, ' UTC') AS date_end, transactions_in.user, commodities.id, commodities.name as commodity_name, tanks.id, tanks.name as tank_name, sellers.id, sellers.name as seller_name,
            farms.id, farms.name as farm_name, transactions_in.truckname, transactions_in.origin, transactions_in.drivername, transactions_in.orgticket, transactions_in.trailerlicense,
            transactions_in.orgweight, FORMAT(transactions_in.moisture, 3) as moisture, FORMAT(transactions_in.testwt, 3) as testwt, 
            FORMAT(transactions_in.dryshper," . $general . ") as dryshper, FORMAT(transactions_in.drychrat," . $general . ") as drychrat, transactions_in.status";
        } elseif ($request->where == 'shipping') {
            $table = 'transactions_out';
            $fields = "CONCAT(transactions_out.date_start, ' UTC') AS date_start, CONCAT(transactions_out.date_end, ' UTC') AS date_end, transactions_out.user, commodities.id, commodities.name as commodity_name, tanks.id, tanks.name as tank_name, buyers.id, buyers.name as buyer_name, transactions_out.truckname,
             transactions_out.drivername, FORMAT(transactions_out.moisture, 3) as moisture, FORMAT(transactions_out.testwt, 3) as testwt,
              FORMAT(transactions_out.dryshper," . $general . ") as dryshper, FORMAT(transactions_out.drychrat," . $general . ") as drychrat, transactions_out.status";
        } else {
            $table = 'cashsales';
            $fields = "CONCAT(cashsales.selled_at, ' UTC') AS selled_at, commodities.id, commodities.name as commodity_name, tanks.id, tanks.name as tank_name, FORMAT(cashsales.price," . $general . ") as price,
            FORMAT(cashsales.total," . $general . ") as total, FORMAT(cashsales.moisture, 3) as moisture, FORMAT(cashsales.testwt, 3) as testwt,
            FORMAT(cashsales.dryshper," . $general . ") as dryshper, FORMAT(cashsales.drychrat," . $general . ") as drychrat,FORMAT(cashsales.netdrywt," . $general . ") as netdrywt,
            FORMAT(cashsales.tare,0) as tare, FORMAT(cashsales.weight,0) as weight, FORMAT(cashsales.net,0) as net, cashsales.status";
        }

        $q = \DB::table($table)->select(\DB::raw($fields));
        if ($table !== 'cashsales')
            $q = $q->join('commodities', $table . '.commodity', 'commodities.id');
        else
            $q = $q->join('commodities', $table . '.commodity_id', 'commodities.id');

        if ($table == 'transactions_in')
            $q = $q->join('sellers', $table . '.seller', 'sellers.id')->join('farms', $table . '.farm', 'farms.id');
        if ($table == 'transactions_out')
            $q = $q->join('buyers', $table . '.buyer', 'buyers.id');
        if ($table !== 'cashsales')
            $q = $q->join('tanks', $table . '.tank', 'tanks.id');
        else
            $q = $q->join('tanks', $table . '.tank_id', 'tanks.id');

        $q = $q->where([
            [$table . '.source_id', $request->source_id],
            [$table . '.branch_id', $request->branch_id]
        ]);

        if ($q->count() > 0) {
            return $q->get();
        } else {
            return 'Ticket N. ' . $request->source_id . ' not found';
        }
    }

    /**
     * Reverting ticket.
     *
     * @return mixin|string
     */
    public function revert(Request $request)
    {
        $id = $request->id;
        $branch_id = $request->branch_id;
        try {
            if ($request->where == 'receive') {
                $ticket = TransactionsIn::select('id', 'branch_id', 'source_id')->where([
                    ['branch_id', $branch_id],
                    ['source_id', $id]
                ])->get();
                //update status to 12 (In process)
                TransactionsIn::where([
                    ['branch_id', $branch_id],
                    ['source_id', $id]])->update(['status' => 12]);
            } elseif ($request->where == 'shipping') {
                $ticket = TransactionsOut::select('id', 'branch_id', 'source_id')->where([
                    ['branch_id', $branch_id],
                    ['source_id', $id]
                ])->get();
                //update status to 12 (In process)
                TransactionsOut::where([
                    ['branch_id', $branch_id],
                    ['source_id', $id]])->update(['status' => 12]);
            } else {
                $ticket = Cashsales::select('id', 'branch_id', 'source_id')->where([
                    ['branch_id', $branch_id],
                    ['source_id', $id]
                ])->get();
                //update status to 12 (In process)
                Cashsales::where([
                    ['branch_id', $branch_id],
                    ['source_id', $id]])->update(['status' => 12]);
            }

            $actions = [
                'id' => $request->id,
                'user_id' => $request->user()->id,
            ];

            if (empty($ticket)) {
                return "Ticket N. {$request->id} not found";
            }

            $array = [
                'group' => $ticket[0]->branch_id,
                'type' => 'REQUEST',
                'action' => ($request->where == 'receive') ? 'revertTicketReceive' : (($request->where == 'shipping') ? 'revertTicketShipping' : 'revertTicketCash'),
                'destination' => $ticket[0]->branch_id,
                'message' => json_encode($actions, JSON_FORCE_OBJECT)
            ];
            $location = $obj[0]->branch_id;

            return \App\SQS::send( $array, 'local', $location, null );
        } catch( Exception $e ){
            return 'Ticket N. ' . $request->id . 'not found';
        }
    }
}