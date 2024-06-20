<?php

namespace App\Http\Controllers\API\MOBILE;

use App\Cashsales;
use App\Locations;
use App\Tanks;
use App\TransactionsIn;
use App\TransactionsOut;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use phpDocumentor\Reflection\Types\Array_;
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcher;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * @OA\Info(title="API WEB", version="1.0")
 *
 * @OA\Server(url="http://127.0.0.1:9000")
 * @OA\SecurityScheme(
 *     @OA\Flow(
 *         flow="clientCredentials",
 *         tokenUrl="oauth/token",
 *         scopes={}
 *     ),
 *     securityScheme="bearerAuth",
 *     in="header",
 *     type="http",
 *     description="Oauth2 security",
 *     name="oauth2",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 * )
 * @OA\Tag(
 *     name="Silosys",
 *     description="Everything about Silosys web",
 *     @OA\ExternalDocumentation(
 *         description="Find out more",
 *         url="https://silosys-web-develop.grainchain.io"
 *     )
 * )
 */
class OpenTickets extends BaseController
{
    private $dIni;
    private $dOut;
    private $filter = 0;

    private $permission = 3;

    public function __construct()
    {
        $this->middleware('candoit:' . $this->permission, ['except' => ['getTicket', 'getFileUri']]);
    }

    private function callbackElements($request)
    {
        $q = \App\Metadata_users::where('user_id', $request->user()->id)->get();
        if ($q->count() > 0) {
            if ($q[0]->temp_ini != '' && $q[0]->temp_out) {
                $this->dIni = $q[0]->temp_ini;
                $this->dOut = $q[0]->temp_out;
            } else {
                $this->dIni = date('Y-m-d', strtotime('01/01'));
                $this->dOut = date('Y-m-d', strtotime('12/31'));
            }
        } else {
            $this->dIni = date('Y-m-d', strtotime('01/01'));
            $this->dOut = date('Y-m-d', strtotime('12/31'));
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $array = array();
        $string = isset($request->q) ? $request->q : '';
        $all = isset($request->todos) ? $request->todos : 'none';
        $this->callbackElements($request);
        $this->filter = $request->input('filter', 0);
        $date_field = 'date_start';
        try {
            $consulta = Locations::where('status', 1)->whereRaw("LOWER(name) LIKE ?", ['%' . strtolower($string) . '%'])->orderBy('name', 'ASC')->select('id', 'name');
            $cRows = $consulta->get()->count();
            $skip = isset($request->page) && $request->page != 1 ? ($request->page * env('PER_PAGE')) - env('PER_PAGE') : 0;
            $sql = $consulta->skip($skip)->take(env('PER_PAGE'))->get();
            foreach ($sql as $key => $val) {
                $commodities = array();
                foreach (\App\Commodities::with(array('metas'))->select('id', 'name')->get() as $k => $v) {
                    if(!is_null($v->id)) {
                        $commodities[$v->id]=
                            array(
                                'id'=>$v->id,
                                'name'=>!is_null($v->name) ? $v->name : 'N/A',
                                'total'=>0,
                                'icon_c'=>(isset($v->metas) and !is_null($v->metas) ) ? $v->metas['icon_name'] : 'others');
                    }
                }
                if ($this->filter == 0) {
                    $query = TransactionsIn::where('branch_id', $val->id);
                    if ($all == 1)
                        $query->whereIn('status', [1, 10, 11]);
                    elseif ($all == 2)
                        $query->whereIn('status', [2, 12]);
                } elseif ($this->filter == 1) {
                    $query = TransactionsOut::where('branch_id', $val->id);
                    if ($all == 1)
                        $query->whereIn('status', [1, 10, 11]);
                    elseif ($all == 2)
                        $query->whereIn('status', [2, 12]);
                } elseif ($this->filter == 2) {
                    $query = Cashsales::where('branch_id', $val->id);
                    $date_field = 'selled_at';
                    if ($all == 1)
                        $query->whereIn('status', [1, 10, 11]);
                    elseif ($all == 2)
                        $query->whwhereInere('status', [2, 12]);
                }
                $query->whereBetween($date_field, [$this->dIni, $this->dOut])
                    ->whereIn('status', [1, 2, 9, 10, 11, 12]);

                $total = $query->get()->count();
                if ($total > 0) {
                    $rows = array();
                    $rows['id'] = $val->id;
                    $rows['name'] = !is_null($val->name) ? $val->name : 'N/A';
                    $rows['timeAverage'] = \App\Api::getTime($val->id, $this->filter);
                    $rows['commodityArray'] = !empty(\App\Api::getCommoditiesArray($val->id, $this->filter, $query, $commodities)) ? \App\Api::getCommoditiesArray($val->id, $this->filter, $query, $commodities)  : [];
                    $rows['totalItems'] = $total;
                    $array[] = $rows;
                }
            }
            return $this->sendSuccess('', array('data' => \App\Api::getPaginator($array, $cRows, $request)));
        }catch (Exception $e) {
            return response()->json(['status' => false, 'title' => 'Error', 'msg' => $e->getMessage()], 500);
        }
    }

    public function tickets_count(Request $request)
    {
        $this->callbackElements($request);
        try {
            $array = array();
            $total_rec = \App\TransactionsIn::whereBetween('date_start', [$this->dIni, $this->dOut])
                ->join('locations', 'transactions_in.branch_id', 'locations.id')
                ->selectRaw('transactions_in.status, count(*) as total, locations.status as status_loc')
                ->whereIn('transactions_in.status', [1, 2, 9, 10, 11, 12])
                ->where('locations.status', 1)
                ->groupBy('transactions_in.status')->get()->toArray();

            $rec = array();
            $rec['id'] = 0;
            $rec['name'] = 'Receiving';
            $rec['totalOpen'] = 0;
            $rec['totalClosed'] = 0;
            $rec['totalVoid'] = 0;
            if (empty($total_rec) == false) {
                foreach ($total_rec as $valor) {
                    if ($valor['status'] == 1 || $valor['status'] == 10 || $valor['status'] == 11) {
                        $rec['totalOpen'] += $valor['total'];
                    } elseif ($valor['status'] == 2 || $valor['status'] == 12) {
                        $rec['totalClosed'] += $valor['total'];
                    } elseif ($valor['status'] == 9) {
                        $rec['totalVoid'] += $valor['total'];
                    }
                }
            }

            $total_ship = \App\TransactionsOut::whereBetween('date_start', [$this->dIni, $this->dOut])
                ->join('locations', 'transactions_out.branch_id', 'locations.id')
                ->selectRaw('transactions_out.status, count(*) as total, locations.status as status_loc')
                ->whereIn('transactions_out.status', [1, 2, 9, 10, 11, 12])
                ->where('locations.status', 1)
                ->groupBy('transactions_out.status')->get()->toArray();

            $ship = array();
            $ship['id'] = 1;
            $ship['name'] = 'Shipping';
            $ship['totalOpen'] = 0;
            $ship['totalClosed'] = 0;
            $ship['totalVoid'] = 0;
            if (empty($total_ship) == false) {
                foreach ($total_ship as $valor) {
                    if ($valor['status'] == 1 || $valor['status'] == 10 || $valor['status'] == 11) {
                        $ship['totalOpen'] += $valor['total'];
                    } elseif ($valor['status'] == 2 || $valor['status'] == 12) {
                        $ship['totalClosed'] += $valor['total'];
                    } elseif ($valor['status'] == 9) {
                        $ship['totalVoid'] += $valor['total'];
                    }
                }
            }

            $total_cash = \App\Cashsales::whereBetween('selled_at', [$this->dIni, $this->dOut])
                ->join('locations', 'cashsales.branch_id', 'locations.id')
                ->selectRaw('cashsales.status, count(*) as total, locations.status as status_loc')
                ->whereIn('cashsales.status', [1, 2, 9, 10, 11, 12])
                ->where('locations.status', 1)
                ->groupBy('cashsales.status')->get()->toArray();

            $cash = array();
            $cash['id'] = 2;
            $cash['name'] = 'Cashsales';
            $cash['totalOpen'] = 0;
            $cash['totalClosed'] = 0;
            $cash['totalVoid'] = 0;
            if (empty($total_cash) == false) {
                foreach ($total_cash as $valor) {
                    if ($valor['status'] == 1 || $valor['status'] == 10 || $valor['status'] == 11) {
                        $cash['totalOpen'] += $valor['total'];
                    } elseif ($valor['status'] == 2 || $valor['status'] == 12) {
                        $cash['totalClosed'] += $valor['total'];
                    } elseif ($valor['status'] == 9) {
                        $cash['totalVoid'] += $valor['total'];
                    }
                }
            }
            $array[] = $rec;
            $array[] = $ship;
            $array[] = $cash;

            return $this->sendSuccess('', array('data' => $array));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->sendError('Bad Request', array(), 400);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function show($id, Request $request)
    {
        $this->callbackElements($request);
        $this->filter = $request->input('filter', 0);
        $string = $request->input('q', '');
        $status = $request->input('status', []);
        $select = 'id, tank, drivername, source_id, status';
        $selectRaw = 'id,"" as tankname, "" as tank, source_id, drivername, status, tank as tank_id';
        $date_field = 'date_start';
        if (!is_array($status))
            $status = explode(',', $status);
        try {
            $location = \App\Locations::findOrFail($id);
            if ($this->filter == 0) {
                $query = \App\TransactionsIn::where('branch_id', $location->id);
            } elseif ($this->filter == 1) {
                $query = \App\TransactionsOut::where('branch_id', $location->id);
            } elseif ($this->filter == 2) {
                $query = \App\Cashsales::where('branch_id', $location->id);
                $date_field = 'selled_at';
                $select = 'id, tank_id, buyer, source_id, transactions_in.status';
                $selectRaw = 'id,"" as tankname, "" as tank, source_id, buyer, status,tank_id';
            }
            $query->whereBetween($date_field, [$this->dIni, $this->dOut])
                ->whereRaw("source_id LIKE ?", ['%' . strtolower($string) . '%'])
                ->whereIn('status', [1, 2, 9, 10, 11, 12])
                ->selectRaw($select);

            $totals = clone $query;
            $totals = $totals->groupBy('status')->select(DB::Raw('count(*) as total,status'))->get();
            if (count($status))
                $query->whereIn('status', $status);
            $query->selectRaw($selectRaw);
            $tickets = $this->parseTickets($query->paginate(25), $totals);
            if (isset($tickets['per_page']))
                $tickets['per_page'] = strval($tickets['per_page']);
            return $this->sendSuccess('', array('data' => $tickets));
        } catch (Exception $e) {
            return $this->sendError("Internal server error", [], 500);
        }
    }

    public function open_tickets(Request $request)
    {
        $this->callbackElements($request);
        $fecha = '';
        if (isset($request->fecha) && $request->fecha == '1M') {
            $fecha = date("Y-m-d", strtotime("-1 month"));
        } elseif (isset($request->fecha) && $request->fecha == '3M') {
            $fecha = date("Y-m-d", strtotime("-3 month"));
        } elseif (isset($request->fecha) && $request->fecha == '6M') {
            $fecha = date("Y-m-d", strtotime("-6 month"));
        } elseif (isset($request->fecha) && $request->fecha == '1Y') {
            $fecha = date("Y-m-d", strtotime("-1 year"));
        } elseif ($request->fecha === null) {
            $fecha = ['2000-01-01', date('Y-m-d', strtotime("1 day"))];
        }

        $this->filter = $request->input('filter', 0);
        $string = $request->input('q', '');
        $status = $request->input('status', []);
        $date_field = '';
        $selectRaw = '';
        $select = '';
        $query = '';
        $table = '';
        if (!is_array($status)) {
            $status = explode(',', $status);
        }
        try {
            if (isset($request->id)) {
                $location = \App\Locations::findOrFail($request->id);
            }
            if ($this->filter == 0) {
                $date_field = 'date_start';
                $table = 'transactions_in';
                $query = \App\TransactionsIn::join('commodities', 'commodities.id', 'transactions_in.commodity')->join('locations', 'locations.id', 'transactions_in.branch_id');
                if (isset($request->id)) {
                    $query = $query->where('transactions_in.branch_id', $location->id);
                }
                $select = 'transactions_in.id, tank, drivername, transactions_in.source_id, transactions_in.status';
                $selectRaw = 'transactions_in.id,"" as tankname, "" as tank, transactions_in.source_id, drivername, transactions_in.status, tank as tank_id, date_start, commodities.name as commodity, locations.name as locations';
            } elseif ($this->filter == 1) {
                $date_field = 'date_start';
                $table = 'transactions_out';
                $query = \App\TransactionsOut::join('commodities', 'commodities.id', 'transactions_out.commodity')->join('locations', 'locations.id', 'transactions_out.branch_id');
                if (isset($request->id)) {
                    $query = $query->where('transactions_out.branch_id', $location->id);
                }
                $select = 'transactions_out.id, tank, drivername, transactions_out.source_id, transactions_out.status';
                $selectRaw = 'transactions_out.id,"" as tankname, "" as tank, transactions_out.source_id, drivername, transactions_out.status, tank as tank_id, date_start, commodities.name as commodity, locations.name as locations';
            } elseif ($this->filter == 2) {
                $table = 'cashsales';
                $query = \App\Cashsales::join('commodities', 'commodities.id', 'cashsales.commodity_id')->join('locations', 'locations.id', 'cashsales.branch_id');
                if (isset($request->id)) {
                    $query = $query->where('cashsales.branch_id', $location->id);
                }
                $date_field = 'selled_at';
                $select = 'cashsales.id, tank_id, cashsales.buyer, cashsales.source_id, cashsales.status';
                $selectRaw = 'cashsales.id,"" as tankname, "" as tank, cashsales.source_id, cashsales.buyer, cashsales.status, tank_id, selled_at, locations.name as locations, commodities.name as commodity';
            }

            $query->whereBetween($date_field, $request->fecha !== null ? [$fecha, date('Y-m-d', strtotime("1 day"))] : $fecha)
                ->whereRaw($table . ".source_id LIKE ?", ['%' . strtolower($string) . '%'])
                //->whereRaw($table.".source_id", $string)
                ->whereIn($table . '.status', [1, 2, 9, 10, 11, 12])
                ->selectRaw($select);

            $totals = clone $query;
            $totals = $totals->groupBy($table . '.status')->select(DB::Raw('count(*) as total,' . $table . '.status'))->get();

            if (count($status)) {
                $query->whereIn($table . '.status', $status);
            }
            $query->selectRaw($selectRaw);
            $tickets = $this->parseTickets($query->paginate(25), $totals);
            if (isset($tickets['per_page'])) {
                $tickets['per_page'] = strval($tickets['per_page']);
            }
            return $this->sendSuccess('', array('data' => $tickets));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    protected function parseTickets($data, $totals)
    {
        $data = $data->toArray();
        $tickets = $data['data'];
        $status = [
            1 => 'Open',
            2 => 'Close',
            9 => 'Void',
            10 => 'RevertedFromSQS',
            11 => 'RevertedFromGUI',
            12 => 'InProcess'
        ];

        foreach ($tickets as &$ticket) {
            $ticket = (Object)($ticket);
            $tank = Tanks::find($ticket->tank_id);
            if ($tank)
                $tank = $tank->name;
            $ticket->tank = !empty($tank) ? $tank : '';
            $ticket->tankname = $ticket->tank;
            $ticket->status = @$status[$ticket->status];
            unset($ticket->tank_id);
        }
        foreach ($totals as &$total) {

            $total->status = @$status[$total->status];
        }
        $data['data'] = $tickets;
        $data['totals'] = $totals;

        return $data;
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
        return $this->sendError('Bad Request', array(), 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->sendError('Bad Request', array(), 400);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/mobile/open-tickets/{lid}/detail/{tkid}?filter={filter}",
     *     summary="Ticket detail",
     *     @OA\Parameter(description="ID of location",in="path", name="lid", required=true,
     *         @OA\Schema( schema="location", type="integer", format="int64")
     *     ),
     *     @OA\Parameter(description="ID of ticket",in="path", name="tkid",required=true,
     *         @OA\Schema( type="integer", format="int64")
     *     ),
     *     @OA\Parameter(description="Ticket type: Receiving:0, Shipping:1, CashSale:2", in="path", name="filter",required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Request Invalid"
     *     ),
     *     security={
     *          {"bearerAuth": {}}
     *     }
     * )
     */
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

        if (!empty($father)) {
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
                        $array['show_id'] = isset($ticket->show_id) ? strval($ticket->show_id) : "0";
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
                        $nameUser = "N/A"; $address = "N/A";
                        if ($this->filter == 0 || $this->filter == 1) {
                            $user = \DB::table('users')->where('id', $ticket->user)->selectRaw("CONCAT( name, ' ', lastname ) As name_complete")->first();
                            if ($user) {
                                $nameUser = $user->name_complete;
                            }

                            $array['usercreator'] = $nameUser;
                            $array['address'] = isset($ticket->address) ? $ticket->address : '';
                            $array['date_start'] = \Carbon\Carbon::parse($ticket->date_start)->format('Y-m-d H:i:s T');
                            //$array['time_in'] = \Carbon\Carbon::parse($ticket->date_start)->format('H:i:s T');
                            $array['date_end'] = ($ticket->date_end !== "0000-00-00 00:00:00") ? \Carbon\Carbon::parse($ticket->date_end)->format('Y-m-d H:i:s T') : "0000-00-00";
                        } else {
                            $array['usercreator'] = $nameUser;
                            $array['address'] = $address;
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
                            $array['features'] = \DB::table($table . ' as tcf')
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
        try {
            $searchId = 0;
            $sp = \DB::select("call parentReference($searchId,$tkid)");
            foreach ($sp as $key => $value) {
                $object = $sp[$key];
                if ($filter == 0)
                    $table = 'transactions_in_commodities_features';
                else if ($filter == 1)
                    $table = 'transactions_out_commodities_features';

                $sp[$key]->ticket = strval($value->ticket);
                if ($filter == 0 or $filter == 1)
                    $object->features = \DB::table($table . ' as tcf')
                        ->selectRaw('cf.name as title, tcf.value')
                        ->join('commodities_features as cf', 'cf.commodities_features_id', 'tcf.commodities_features_id')
                        ->where([
                            ['tcf.source_id', $value->ticket],
                            ['tcf.branch_id', $value->location_id]
                        ])->get()->toArray();

                $array[] = $object;
            }

            return $array;

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError('Internal Server Error', array($e->getMessage()), 500);
        }
    }

    public function getTicket($type, $central_id, $branch_id, Request $request)
    {
        if (!isset($type) && !isset($type))
            return $this->sendError('Bad request, parameter (type) missing', array(), 400);
        if (!isset($central_id) && !isset($central_id))
            return $this->sendError('Bad request, parameter ($number) missing', array(), 400);

        try {
            $fileParams = self::getFileParams($type, $central_id, $branch_id);
            $file = $fileParams["file"];
            $fileS3 = $fileParams["fileS3"];
            $dates = $fileParams["dates"];
            $nameFile = $fileParams["nameFile"];
            $content = null;
            if (Storage::disk('s3')->exists($fileS3)) {
                $content = Storage::disk('s3')->get($fileS3);
            }
            if (empty($content)) {
                try {
                    $dropbox = new Dropbox(new DropboxApp(env('DROPBOX_APP_KEY'), env('DROPBOX_APP_SECRET'), env('DROPBOX_APP_TOKEN')));
                    $dropbox->getMetadata($file, ["include_media_info" => true, "include_deleted" => true]);
                    $pdf = $dropbox->download($file);
                    $content = $pdf->getContents();
                    Storage::disk('s3')->put($fileS3, $content);
                } catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
                    return $this->sendError('File with Ticket number ' . $central_id . ' not found', $dates, 404);
                }
            }
            return response($content, 200)->withHeaders([
                'Content-Disposition' => 'attachment; filename="contract_' . $nameFile
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError('Ticket number ' . $central_id . ' not found', $dates, 404);
        }
    }

    public function getFileUri($type, $central_id, $branch_id) : String
    {
        $fileParams = self::getFileParams($type, $central_id, $branch_id);
        return Storage::disk("s3")->url($fileParams["fileS3"]);
    }

    private function getFileParams($type, $central_id, $branch_id)
    {
        $folder = $type;
        switch ($type) {
            case 'receive':
                $folder = 'Receive';
                break;
            case 'shipping':
                $folder = 'Ship';
                break;
            case 'cash':
                $folder = 'Cash';
                break;
            case 'weight':
                $folder = 'Weight';
                break;
            default:
                break;
        }

        $months_name_short = [
            'Jan' => 'Ene',
            'Feb' => 'Feb',
            'Mar' => 'Mar',
            'Apr' => 'Abr',
            'May' => 'May',
            'Jun' => 'Jun',
            'Jul' => 'Jul',
            'Aug' => 'Ago',
            'Sep' => 'Sep',
            'Oct' => 'Oct',
            'Nov' => 'Nov',
            'Dec' => 'Dic'
        ];
        $dates = [];
        $dates['current'] = \Carbon\Carbon::now();

        $t = TransactionsIn::with('branchs')
            ->where('source_id', $central_id)
            ->where('branch_id', $branch_id)
            ->firstOrFail();

        $date = new \Carbon\Carbon($t->date_end);
        $dates['origin'] = $date;
        $date = $date->subHours(5);
        $dates['modify'] = $date;

        $actuallyMonth = (env('APP_LANG') == 'ES') ? $months_name_short[ucwords(strtolower($date->format('M')))] : ucwords(strtolower($date->format('M')));
        $path = '/' . $t->branchs['pdfpath'] . '/' . $date->format('Y') . '/' . $date->format('m') . '-' . $actuallyMonth . '/' . $date->format('d') . '/' . $folder;
        $nameFile = str_pad($t->source_id, 8, '0', STR_PAD_LEFT) . '.pdf';
        $file = $path . '/' . $nameFile;
        $separator = substr( $file, 0, 1) === "/" ? '' : '/';
        return array(
            "file" => $file,
            "fileS3" => env('INSTANCE_ID') . $separator  . $file,
            "dates" => $dates,
            "nameFile" => $nameFile
        );
    }
}
