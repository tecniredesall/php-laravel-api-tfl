<?php namespace App\Http\Controllers\API\WEB;

use App\FileReports;
use App\Helpers\ReportsHTMLtoPDF;
use App\Http\Requests\API\WEB\ReportRequest;
use App\Mail\ReportPDF;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Report;
use App\Commodities;
use App\Sellers;
use App\Buyers;
use App\Farms;
use App\Locations;
use App\Company_info;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;


class Reports extends BaseController
{
    public function index($user_id)
    {
        try {
            $user = \App\Users::where('id', $user_id)->first();
            $report = [];
            $commodities = [];
            $buyers = [];
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

    public function farms(Request $request)
    {
        if ($request['owner'] !== NULL) {
            $owner_array = [];
            for ($j = 0; $j < count($request['owner']); $j++) {
                array_push($owner_array, $request['owner'][$j]);
            }
            $owner = implode(', ', array_values($owner_array));
            $result = Farms::select('id', 'name')
                ->whereIn('seller', array(DB::raw($owner)))->get()->toArray();
            //echo '<pre>'; var_dump(  $result ); exit(); echo '</pre>';
            return response()->json($result);
        } else {
            return response()->json(false);
        }
    }

    public function seller_farms(Request $request)
    {
       try {
            $result = \App\Sellers::with( 'farms' )->whereIn( 'id', $request['owner'] )->get()->toArray();

            return response()->json($result);
        } catch (Exception $e) {

        }

    }

    public function reports( ReportRequest $request, $report_id, $lang, $format )
    {
        ini_set('max_execution_time', 3000); //3000 seconds = 50 minutes
        try {
            $lang = ($lang !== 'null') ? $lang : 'es';
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

    public function getPDF($id, Request $request)
    {

        if (!$request->hasValidSignature()) {
            return $this->sendError('File not found or expired download time', array(), 404);
        }

        try {
            $file_report = FileReports::find($id);
            $file_name = $request->input('name', 'report.pdf');

            if ($request->isMethod('post')) {
                return Storage::disk('s3')->download($file_report->route, $file_name);

            }
            $file = Storage::disk('s3')->get($file_report->route);
            $headers = [
                'Content-Type' => 'application/pdf',
            ];
            return \Response::make($file, 200, $headers);
        } catch (\Exception $exception) {
            Log::error($e->getMessage());
            return $this->sendError('Internal Server Error', array($exception->getMessage()), 500);

        }

    }

    public function getXLSX($id){
        try {
            $file_report = FileReports::find($id);
                return Storage::disk('s3')->download($file_report->route);
        } catch (\Exception $exception) {
            Log::error($e->getMessage());
            return $this->sendError('Internal Server Error', array($exception->getMessage()), 500);

        }

    }

}
