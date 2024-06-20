<?php


namespace App\Helpers;


use App\Commodities;
use App\Company_info;
use App\Report;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportPDFQuery
{
    public $data;
    public $request;
    public $queryDB;

    public function __construct($request, $report_id, $lang)
    {
        $this->request = $request;
        $this->i = $report_id;
        $this->lang = $lang;
    }

    public function InitReport()
    {
        $request = $this->request;
        $queryBuilder = Report::find(intval($this->i));
        $lang = $this->lang;
        $i = intval($this->i) - 1;
        $report_name = strtolower($queryBuilder->report_name);
        if (isset($request['fromInput']) && !is_null($request['fromInput'])) {
            $fecha = explode(' - ', $request['fromInput']);
            $fromI = $fecha[0];
            $fullDate = $request['fromInput'];
            $fromInput = explode('/', $fromI);
            $toI = $fecha[1];
            $toInput = explode('/', $toI);

            if ($lang == 'en') {
                $fromInput = $fromInput[2] . '-' . $fromInput[0] . '-' . $fromInput[1]. ' 00:00:00';
                $toInput = $toInput[2] . '-' . $toInput[0] . '-' . $toInput[1]. ' 23:59:59';
            } else {
                $fromInput = $fromInput[2] . '-' . $fromInput[1] . '-' . $fromInput[0]. ' 00:00:00';
                $toInput = $toInput[2] . '-' . $toInput[1] . '-' . $toInput[0]. ' 23:59:59';
            }
        }

        $query = '';
        $groupBy = '';
        $groupBy_commodity = '';
        $field_ticket = json_decode($queryBuilder->field_report);
        $commodities = "";
        $commodities_array = [];
        $totalRecords = 0;
        if (isset($request['commodities']) && ($request['commodities'] !== '')) {
            for ($j = 0; $j < sizeof($request['commodities']); $j++) {
                $commodities_id = Commodities::select('id', 'name')->where('id', $request['commodities'][$j]["value"])->first();
                if ($commodities_id)
                    array_push($commodities_array, '"' . $commodities_id->name . '"');
            }
            $commodities = implode(', ', array_values($commodities_array));
        }
        if ($i === 16) {
            $select = 'SELECT tankstock.cname,tankstock.locationname,tankstock.tname, sum(tankstock.stocklb) as stock_lb, sum(tankstock.stocklbd) as stock_lbd, sum(tankstock.tcapacity) as capacity FROM tankstock WHERE tankstock.locationname <> "Offsite"';
            if ($commodities !== "") {
                $query = DB::select($select . ' AND cname in (' . $commodities . ') GROUP BY tankstock.cname, tankstock.locationname');
                $totalRecords = count($query);
            } else {
                $query = DB::select($select . ' GROUP BY tankstock.cname, tankstock.locationname');
                $totalRecords = count($query);
            }
        } else if ($i === 17) {
            $select = 'SELECT tankstock.cname,tankstock.locationname,tankstock.tname, sum(tankstock.stocklb) as stock_lb,sum(tankstock.stocklbd) as stock_lbd, sum(tankstock.tcapacity) as capacity FROM tankstock WHERE tankstock.locationname <> "Offsite"';
            $select2 = 'SELECT tankstock.capacity,tankstock.locationname,tankstock.cname,tankstock.tname as tank_name, sum(tankstock.stocklb) as stock_lb, sum(tankstock.stocklbd) as stock_lbd, sum(tankstock.tcapacity) as capacity FROM tankstock WHERE tankstock.locationname <> "Offsite"';
            if ($commodities !== "") {
                $query = DB::select($select . ' AND cname in (' . $commodities . ') GROUP BY tankstock.cname, tankstock.locationname');
                $totalRecords = count($query);
                $groupBy = DB::select($select2 . ' AND cname in (' . $commodities . ') GROUP BY tankstock.cname,tankstock.tname, tankstock.locationname');
                $groupBy_commodity = \DB::select($select2 . ' AND cname in (' . $commodities . ') GROUP BY tankstock.cname');
            } else {
                $query = DB::select($select . ' GROUP BY tankstock.cname, tankstock.locationname');
                $totalRecordsQuery = count($query);
                $groupBy = DB::select($select2 . ' GROUP BY tankstock.cname,tankstock.tname, tankstock.locationname');
                $totalRecordsGroup = count($groupBy);
                $totalRecords = $totalRecordsQuery + $totalRecordsGroup;
                $groupBy_commodity = DB::select($select2 . ' GROUP BY tankstock.cname');
            }
        } else if ($i === 18) {
            $query = 'SELECT locations.id as location_id, locations.name as location_name, commodities.id as commoditie_id,commodities.name as commoditie_name,total_union.id, sum(total_union.net) as net, DATE(total_union.date_end) as date_end FROM total_union INNER JOIN commodities ON total_union.commodity=commodities.id INNER JOIN locations ON total_union.branch_id=locations.id WHERE ';
            if(isset($request['silos']) and $request['silos'] !== NULL) {
                $silos = implode(',', array_values($request['silos']));
                $query .= 'locations.id  in (' . $silos . ') AND ';
            }
            if(isset($request['toInput']) and $request['toInput'] !== NULL){
                $fecha = explode('/', $request['toInput']);
                if ($lang == 'en') {
                    $toInput = $fecha[2] . '-' . $fecha[0] . '-' . $fecha[1];
                } else {
                    $toInput = $fecha[2] . '-' . $fecha[1] . '-' . $fecha[0];
                }
                $query .= 'total_union.date_end <= "' . $toInput . '" group by locations.name, commodities.name';
            }else{
                $query .= 'total_union.date_end <= "' . DATE('Y-m-d') . '" group by locations.name, commodities.name';
            }
            $query = DB::select(DB::raw($query));
            $totalRecords = count($query);
        } else if ($i === 19) {
            $select = 'SELECT tankstock.capacity, tankstock.stock, tankstock.dryWeight, tankstock.tcapacity, tankstock.tstock, tankstock.stocklbd, tankstock.tname, tankstock.stock_lb, tankstock.cname, tankstock.locationname FROM tankstock';
            if ($commodities !== "") {
                $query = DB::select($select . ' WHERE cname in (' . $commodities . ') group by cname');
            } else {
                $query = DB::select($select);
            }
            $totalRecords = count($query);
        } else if ($i === 20) {
            $query = DB::select('SELECT commostock.cname,commostock.stock FROM commostock');
            $totalRecords = count($query);
        } else if ($i === 26) {
            $query = DB::select('CALL globalStock');
            $totalRecords = count($query);
        }else if ($i === 27) {
            try {
                $locations = DB::table('locations')->select('name', 'aws_host', 'slave_username', 'slave_password', 'database_name');
                if( isset($request['silos']) && (!is_null($request['silos'])) ) {
                    if (sizeof($request['silos']) > 0) {
                        $silos = implode(', ', array_values($request['silos']));
                        $locations = $locations->whereIn('id', array(DB::raw($silos)));
                    }
                }

                $locations = $locations->where('status', 1)
                    ->whereNotNull('aws_host')->whereNotNull('slave_username')
                    ->whereNotNull('slave_password')->whereNotNull('database_name')->get()->toArray();
                $query = [];$totalRecords = 0;

                foreach ($locations as $loc) {
                    $servername = $loc->aws_host;
                    $username = $loc->slave_username;
                    $password = $loc->slave_password;
                    $database_name = $loc->database_name;

                    $conn=new \PDO("mysql:host=$servername;dbname=$database_name", $username, $password);
                    //$conn = new \PDO("mysql:host=192.168.100.120;dbname=silosysrc", 'root', 'grainchain2018');

                    $sp='CALL contractSummary()';
                    $q=$conn->query($sp);
                    if ($q) {
                        $array=$q->fetchAll(\PDO::PARAM_NULL);

                        $query[$loc->name]=$array;
                        $totalRecords=$totalRecords + count($array);
                    }
                }
                $totalRecords = $totalRecords;
            }catch(\Exception $e)
            {
                echo "Connection failed: " . $e->getMessage();
            }
        } else if($i === 28) {
            if(!is_null($request['commodities']) ){
                $commodity = strval(collect($request['commodities'])->pluck('value')[0]);
                $bushel = isset($request['bushel']) ? $request['bushel'] : 0;
                $cwt = isset($request['cwt']) ? $request['cwt'] : 0;
                $query = DB::select(DB::raw("CALL BalanceByCommodity('".date('Y/m/d', strtotime($fromInput))."', '". date('Y/m/d', strtotime($toInput))."', $commodity, $bushel, $cwt)"));
                $totalRecords = count($query);
            }
        }else {
            $valores_group = $queryBuilder->group_by;
            if (isset($queryBuilder->inner_params) && ($queryBuilder->inner_params !== '')) {
                $query = DB::table($queryBuilder->main_table)->select([DB::raw($queryBuilder->select_params)]);
                $groupBy = DB::table($queryBuilder->main_table)->select([DB::raw($queryBuilder->group_by_select)]);
                $inner = json_decode($queryBuilder->inner_params, true);
                foreach ($inner as $clave => $posicion) {
                    if ($clave == 'farms' && ($i == 0 || $i == 1 || $i == 2 || $i == 3 || $i == 4)) {
                        $query = $query->leftJoin($clave, $posicion[1], $posicion[2]);
                        $groupBy = $groupBy->leftJoin($clave, $posicion[1], $posicion[2]);
                    } else if ($clave == 'farms' && ($i == 23)) {
                        $query = $query->join($clave, $posicion[1], $posicion[2]);
                        $groupBy = $groupBy->join($clave, $posicion[1], $posicion[2]);
                    } else if ($clave == 'locations' && ($i == 9 || $i == 10 || $i == 24)) {
                        $query = $query->leftJoin($clave, $posicion[1], $posicion[2]);
                        $groupBy = $groupBy->leftJoin($clave, $posicion[1], $posicion[2]);
                    } else if (($clave == 'commodities' || $clave == 'tanks') && $i == 15) {
                        $query = $query->leftJoin($clave, $posicion[1], $posicion[2]);
                        $groupBy = $groupBy->leftJoin($clave, $posicion[1], $posicion[2]);
                    } else {
                        if ($clave == 'sellers as p') {
                            $query = $query->leftJoin($clave, $posicion[1], $posicion[2]);
                            $groupBy = $groupBy->leftJoin($clave, $posicion[1], $posicion[2]);
                        } else {
                            $query = $query->join($clave, $posicion[1], $posicion[2]);
                            $groupBy = $groupBy->join($clave, $posicion[1], $posicion[2]);
                        }
                    }
                }
            }

            if (($queryBuilder->where_params !== "") && isset($queryBuilder->where_params)) {
                $where = json_decode($queryBuilder->where_params, true);
                foreach ($where as $clave => $condicion) {
                    $query = $query->where($clave, $condicion[0], $condicion[1]);
                    $groupBy = $groupBy->where($clave, $condicion[0], $condicion[1]);
                }
            }

            //$query = $query->where('locations.id',  \Session::get('userId'));
            if (isset($request['fromInput']) && !is_null($request['fromInput'])) {
                if ($i == 15) {
                    $query = $query->whereBetween(DB::raw('DATE(cashsales.selled_at)'), [$fromInput, $toInput]);
                    $groupBy = $groupBy->whereBetween(DB::raw('DATE(cashsales.selled_at)'), [$fromInput, $toInput]);
                } else if ($i == 21) {
                    $query = $query->whereBetween('transactions_in.date_end', [$fromInput, $toInput]);
                    $groupBy = $groupBy->whereBetween('transactions_in.date_end', [$fromInput, $toInput]);
                }else {
                    $query = $query->whereBetween('date_end', [$fromInput, $toInput]);
                    $groupBy = $groupBy->whereBetween('date_end', [$fromInput, $toInput]);
                }
            }

            if (isset($queryBuilder->group_params) && ($queryBuilder->group_params !== '')) {
                foreach (json_decode($queryBuilder->group_params) as $group) {
                    if (isset($request['farms']) && (!is_null($request['farms'])) && ($group == 'farms.id')) {
                        if(is_string($request['farms'])){
                            $query = $query->whereIn($group, array( $request['farms'] ));
                            $groupBy = $groupBy->whereIn($group, array( $request['farms'] ));
                        }else {
                            $farms = implode(', ', array_values($request['farms']));
                            $query = $query->whereIn($group, array(DB::raw($farms)));
                            $groupBy = $groupBy->whereIn($group, array(DB::raw($farms)));
                        }
                    }

                    if ( Schema::hasColumn('users', 'user_type') ) {
                        $request['user_id'] = isset($request['user_id']) ? $request['user_id'] : auth()->id();
                        $user = \App\Users::where('id', $request['user_id'])->first();
                        if(isset($user->user_type) && ($user->user_type === 2) && ($group == 'commodities.id') ){
                            if( is_null($request['commodities']) ) {
                                $request['commodities'] = \App\Commodities::where('buyer', $user->user_type)->select('id as value')->get()->toArray();
                            }
                            $commodities_array = [];
                            for ($j = 0; $j < sizeof($request['commodities']); $j++) {
                                array_push($commodities_array, $request['commodities'][$j]["value"]);
                            }
                            $commodities = implode(', ', array_values($commodities_array));
                            $query = $query->whereIn($group, array(DB::raw($commodities)));
                            $groupBy = $groupBy->whereIn($group, array(DB::raw($commodities)));

                        }else{
                            if (isset($request['commodities']) && (!is_null($request['commodities'])) && ($group == 'commodities.id')) {
                                $commodities_array = [];
                                for ($j = 0; $j < sizeof($request['commodities']); $j++) {
                                    array_push($commodities_array, $request['commodities'][$j]["value"]);
                                }
                                $commodities = implode(', ', array_values($commodities_array));
                                $query = $query->whereIn($group, array(DB::raw($commodities)));
                                $groupBy = $groupBy->whereIn($group, array(DB::raw($commodities)));
                            }
                        }
                    }else{
                        if (isset($request['commodities']) && (!is_null($request['commodities'])) && ($group == 'commodities.id')) {
                            $commodities_array = [];
                            for ($j = 0; $j < sizeof($request['commodities']); $j++) {
                                array_push($commodities_array, $request['commodities'][$j]["value"]);
                            }
                            $commodities = implode(', ', array_values($commodities_array));
                            $query = $query->whereIn($group, array(DB::raw($commodities)));
                            $groupBy = $groupBy->whereIn($group, array(DB::raw($commodities)));
                        }
                    }

                    if (isset($request['owner']) && (!is_null($request['owner'])) && ($group == 'sellers.id')) {
                        if(is_string($request['owner'])){
                            $query = $query->whereIn($group, array( $request['owner'] ));
                            $groupBy = $groupBy->whereIn($group, array( $request['owner'] ));
                        }else {

                            $owner = implode(', ', $request['owner']);
                            $query = $query->whereIn($group, array(DB::raw($owner)));
                            $groupBy = $groupBy->whereIn($group, array(DB::raw($owner)));
                        }
                    }

                    if (isset($request['buyer']) && (!is_null($request['buyer'])) && ($group == 'buyers.id')) {
                        if(is_string($request['buyer'])){
                            $query = $query->whereIn($group, array( $request['buyer'] ));
                            $groupBy = $groupBy->whereIn($group, array( $request['buyer'] ));
                        }else {
                            $buyer = implode(', ', array_values($request['buyer']));
                            $query = $query->whereIn($group, array(DB::raw($buyer)));
                            $groupBy = $groupBy->whereIn($group, array(DB::raw($buyer)));
                        }
                    }

                    if (isset($request['silos']) && (!is_null($request['silos'])) && ($group == 'locations.id')) {
                        if(is_string($request['silos'])){
                            $query = $query->whereIn($group, array( $request['silos'] ));
                            $groupBy = $groupBy->whereIn($group, array( $request['silos'] ));
                        }else if (sizeof($request['silos']) > 0) {
                            $silos = implode(', ', array_values($request['silos']));
                            $query = $query->whereIn($group, array(DB::raw($silos)));
                            $groupBy = $groupBy->whereIn($group, array(DB::raw($silos)));
                        }
                    }
                }
            }
            if (isset($valores_group) && ($valores_group != '')) {
                if ($i == 6) {
                    $query = $query->groupBy(DB::raw($valores_group));
                    $valores_agrupados = str_replace(', farms.id', '', $valores_group);
                    $groupBy = $groupBy->groupBy(DB::raw($valores_agrupados))->get();
                } else if ($i == 13) {
                    $query = $query->groupBy(DB::raw($valores_group));
                    $valores_agrupados = str_replace(', buyers.id', '', $valores_group);
                    $groupBy = $groupBy->groupBy(DB::raw($valores_agrupados))->get();
                } else if ($i !== 8 || $i !== 15) {
                    $groupBy = $groupBy->groupBy(DB::raw($valores_group));
                    if( $i == 9 ){
                        $groupBy = $groupBy->orderBy(DB::raw('commodities.name, buyers.name'));
                    }
                    $groupBy = $groupBy->get();
                }
            }

            if (isset($queryBuilder->order_params) && ($queryBuilder->order_params != '')) {
                if ($i !== 6 && $i !== 13) {
                    $query = $query->orderBy(DB::raw($queryBuilder->order_params));
                }
            }
        }
        if ($i == 8 || $i == 16 || $i == 19 || $i == 20) {
            $groupBy = '';
            $fullDate = '';
            $toI = '';
        } else if ($i == 21 || $i == 22 || $i == 17) {
            $fullDate = '';
            $toI = '';
        }else if( $i == 24 ){
            $query = '';
            $totalRecords = 0;
        }
        if(!isset($totalRecords)){
            $totalRecords=$query->count();
        }
        //$totalRecords = is_array($query) ? count($query) : $query->count();
        $nombre = $this->getConame();
        $data = [
            'i' => $i,
            'report_name' => (isset($report_name) && ($report_name !== '')) ? $report_name : '',
            'total' => $totalRecords,
            'groupBy' => (isset($groupBy) && ($groupBy !== '')) ? $groupBy : '',
            'fields' => (isset($field_ticket) && ($field_ticket !== '')) ? $field_ticket : '',
            'date_from' => (isset($fullDate) && ($fullDate !== '')) ? $fullDate : '',
            'date_to' => (isset($toI) && ($toI !== '')) ? $toI : '',
            'groupBy_commodity' => (isset($groupBy_commodity) && ($groupBy_commodity !== '')) ? $groupBy_commodity : '',
            'coname' => (isset($nombre) && ($nombre !== '')) ? $nombre : '',
            'lang' => (isset($lang) && ($lang !== '')) ? $lang : '',
            'decimals_in_tickets' => \App\Company_info::select('*')->pluck('decimals_in_tickets')[0],
            'display_show_id' => Schema::hasColumn('company_info', 'display_show_id') ? \App\Company_info::pluck('display_show_id')[0] : 0,
            'commodities' => isset($request['commodities']) ? $request['commodities'] : null,
            'bushel' => isset($request['bushel']) ? $request['bushel'] : 0,
            'cwt' => isset($request['cwt']) ? $request['cwt'] : 0,
        ];

        $this->data = $data;
        $this->queryDB = $query;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getInfoDB()
    {
        if ( ($this->i >= 17 && $this->i <= 21) || ($this->i >= 27 and $this->i <= 29) ) {
            $query = $this->queryDB;
        }else if( $this->i == 25 ) {
            $query = '';
        }else{
            $query = $this->queryDB->get();
        }

        $this->data['infoFormato'] = (isset($query) && ($query !== '')) ? $query : '';
        return $this;

    }

    private function getConame()
    {
        return Company_info::select('name')->pluck('name')[0];

    }
}