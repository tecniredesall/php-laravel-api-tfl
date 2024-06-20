<?php

namespace App\Http\Controllers\API\MOBILE;

use App\Buyers;
use App\Commodities;
use App\Farms;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\API\WEB\ReportRequest;
use App\Locations;
use App\Report;
use App\Sellers;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class Reports extends BaseController
{

    public function index()
    {
        try{
            $user_id = auth()->id();
            $user = \App\Users::where('id', $user_id)->first();
            if ( Schema::hasColumn('users', 'user_type') ) {
                if(isset($user->user_type) && $user->user_type == 2 ){
                    $commodities = DB::table('commodities_relations')
                        ->join('commodities as c','c.id', 'commodity_id')
                        ->select('id', 'c.name')
                        ->where('commodities_relations.status', 1)
                        ->where('commodities_relations.user_id', $user_id)
                        ->where('c.status', '<>', 5)
                        ->get()->toArray();

                    $buyers = DB::table('buyers_relations')
                        ->join('buyers as b','b.id', 'buyer_id')
                        ->select('id', 'b.name')
                        ->where('buyers_relations.status', 1)
                        ->where('buyers_relations.user_id', $user_id)
                        ->where('b.status', '<>', 5)
                        ->get()->toArray();

                    $report = DB::table('reports_relations')
                        ->join('reports as r','r.id', 'report_id')
                        ->where('reports_relations.status', 1)
                        ->where('reports_relations.user_id', $user_id)
                        ->get()->toArray();
                }else{
                    if(isset($user->user_type) && $user->user_type == 1 ){
                        $report = Report::select('*')->get()->toArray();
                        $commodities = Commodities::select('*')->where('status', '<>', 5)->get()->toArray();
                        $buyers = Buyers::select('*')->where('status', '<>', 5)->get()->toArray();
                    }
                }
            }else {
                $report = Report::select('*')->get()->toArray();
                $commodities = Commodities::select('*')->where('status', '<>', 5)->get()->toArray();
                $buyers = Buyers::select('*')->where('status', '<>', 5)->get()->toArray();
            }

            $sellers = Sellers::select('*')->where('status', '<>', 5)->get()->toArray();
            $silos = Locations::select('*')->where('status', '<>', 5)->whereNotNull('name')->get()->toArray();

            if( Schema::hasColumn('users', 'user_type') ){
                if( \App\Users::where('id', $user_id)->where('user_type', 2)->first() ){
                    $user_id = $user_id;
                }else{
                    $user_id = 0;
                }
            }

            $data = [
                'commodities' => $commodities,
                'reports' => $report,
                'sellers' => $sellers,
                'buyers' => $buyers,
                'silos' => $silos,
                'user_id' => $user_id,
            ];
            return $this->sendSuccess('', array('data' => $data));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function store(ReportRequest $request)
    {
        ini_set('max_execution_time', 3000); //3000 seconds = 50 minutes
        try {
            $data = $request->initReport()->getResponse();
            return response()->json($data['data'], $data['code']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), array(), 500);
        }
    }

    public function show($id, Request $request)
    {
        if ($id === 'farms') {
            $owners = $request->input('owner', '');
            $farms = [];
            if (!is_array($owners))
                $owners = explode(',', $owners);
            if (count($owners))
                $farms = Farms::select('id', 'name', 'seller')
                    ->whereIn('seller', $owners)->get();

            return response()->json(['data' => $farms]);

        }
    }

    public function seller_farms(Request $request)
    {
            $owners = $request->input('owner', '');
            if (!is_array($owners))
                $owners = explode(',', $owners);
                if (count($owners))
                    $result = \App\Sellers::with( 'farms' )->whereIn( 'id', $owners)->get()->toArray();
            return response()->json(['data' => $result]);
    }

    public function reports( ReportRequest $request, $report_id, $lang, $format )
    {
        ini_set('max_execution_time', 3000); //3000 seconds = 50 minutes
        try {
            if ($format === 'xlsx'){
                $data=$request->initReport($report_id, $lang)->getXlsResponse($format);
            }else {
                $data = $request->initReport($report_id, $lang)->getResponse($format, $report_id, $lang);
            }
            return response()->json($data['data'], $data['code']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), array(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }
}
