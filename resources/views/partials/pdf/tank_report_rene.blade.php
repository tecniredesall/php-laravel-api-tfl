<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8"/>
    <base href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" />
    <base href="{{ env('APP_ENV') === 'local' ? asset('assets/css/dashboard.min.css') : secure_asset('assets/css/dashboard.min.css') }}"/>
    <base href="{{ env('APP_ENV') === 'local' ? asset('assets/dashboard.css') : secure_asset('assets/dashboard.css') }}"/>
    <link href="{{ env('APP_ENV') === 'local' ? asset('assets/css/global_reports.css') : secure_asset('assets/css/global_reports.css') }}" rel="stylesheet" >
    <title>Reporte General </title>
    <style>
        .page {
            padding: 20px;
        }
        table, tr, td, th, tbody, thead, tfoot {
            page-break-inside: avoid !important;
        }
    </style>
</head>
<body>
        <div class="content">
            <div class="panel panel-default">
                <div class="sty_header">
                    <p style="text-align: center;"> {{ $coname }} </p>
                    <p style="text-align: center;"> TANK REPORT </p><?php
                    if($lang == "en"){
                        $fecha = date('m-d-y');
                    }else{
                        $fecha = date('d-m-y');
                    }
                    ?>
                    <p style="text-align: center;"> REPORT DATE: "{{$fecha}}" </p>
                </div>
            </div>
            <div class="panel-body">
                <div class="group">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="panel">
                            <div class="panel-body">
                            <?php
                                $stock_lb_g = 0; $stock_lbd_g = 0; $stkMT_g = 0; $capMT_g = 0; 
                                $stkMT_cal_g = 0; $capMT_cal_g = 0; $bal_cal_g = 0; $bal_g = 0;
                                $stock_lb_total_g = 0; $stock_lbd_total_g = 0; $stkMT_total_g = 0; 
                                $capMT_total_g = 0; $bal_total_g = 0;
                                $stock_lb = 0; $stock_lbd = 0; $stkMT = 0; $capMT = 0; 
                                $stkMT_cal = 0; $capMT_cal = 0; $bal = 0;
                                $stock_lb_total = 0; $stock_lbd_total = 0; $stkMT_total = 0; 
                                $capMT_total = 0; $bal_total = 0;
                            ?>
                            @foreach( $groupBy_commodity as $key => $group_commodity )
                            <?php
                                $stock_lb_total_g = $stock_lb_total_g + $group_commodity->stock_lb;
                                $stock_lbd_total_g = $stock_lbd_total_g + $group_commodity->stock_lbd;
                                $stkMT_cal_g = $group_commodity->stock_lb / 2204.62;
                                $capMT_cal_g = ($group_commodity->capacity * 56) / 2204.62;  
                                $bal_cal_g = $capMT_cal_g - $stkMT_cal_g;
                                $stkMT_g = number_format($stkMT_cal_g, $decimals_in_tickets);
                                $capMT_g = number_format($capMT_cal_g, $decimals_in_tickets);
                                $bal_g = number_format($bal_cal_g, $decimals_in_tickets);
                                $stkMT_total_g = $stkMT_total_g + $stkMT_cal_g;
                                $capMT_total_g = $capMT_total_g + $capMT_cal_g;
                                $bal_total_g = $bal_total_g + $bal_cal_g;
                            ?>
                                <div>
                                    <p style="text-align: left;"> {{ $group_commodity->cname }} </p>
                                </div>
                                <table class="table table-striped table-bordered">
                                    <tr class="table_style">
                                        <th class="upper"> LOCATION NAME </th>
                                        <th class="upper"> NET LBS </th>
                                        <th class="upper"> NET DRY LBS </th>
                                        <th class="upper"> NET MT </th>
                                        <th class="upper"> CAP MT </th>
                                        <th class="upper"> BALANCE </th>
                                    </tr>
                                    @foreach($infoFormato as $clave => $row)
                                        <?php
                                            $location_current = $row->locationname;
                                            $stock_lb_g = number_format($row->stock_lb ,$decimals_in_tickets);
                                            $stock_lbd_g = number_format($row->stock_lbd , $decimals_in_tickets);
                                            $stkMT_cal_g = $row->stock_lb / 2204.62;
                                            $capMT_cal_g = ($row->capacity * 56) / 2204.62;
                                            $stkMT_g = number_format($stkMT_cal_g, $decimals_in_tickets);
                                            $capMT_g = number_format($capMT_cal_g, $decimals_in_tickets);
                                            $bal_cal_g = $capMT_cal_g - $stkMT_cal_g;
                                            $bal_g = number_format($bal_cal_g, $decimals_in_tickets);
                                        ?>
                                        @if( $group_commodity->cname == $row->cname)
                                            <tr id="fieldRow">
                                                <td> {{ $location_current }} </td>
                                                <td style="text-align: right"> {{ $stock_lb_g }} </td>
                                                <td style="text-align: right"> {{ $stock_lbd_g }} </td>
                                                <td style="text-align: right"> {{ $stkMT_g }} </td>
                                                <td style="text-align: right"> {{ $capMT_g }} </td>
                                                <td style="text-align: right"> {{ $bal_g }} </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                        <tr>
                                            <th style="text-align: center">TOTAL: </th>
                                            <th style="text-align: right">{{ number_format($stock_lb_total_g, $decimals_in_tickets) }} </th>
                                            <th style="text-align: right">{{ number_format($stock_lbd_total_g, $decimals_in_tickets) }}</th>
                                            <th style="text-align: right">{{ number_format($stkMT_total_g, $decimals_in_tickets) }}</th>
                                            <th style="text-align: right">{{ number_format($capMT_total_g, $decimals_in_tickets) }}</th>
                                            <th style="text-align: right">{{ number_format($bal_total_g, $decimals_in_tickets) }}</th>
                                        </tr>
                                </table>
                                <?php
                                    $tank_old_group = '';
                                    $stkMT_g = 0; 
                                    $capMT_g = 0; $bal_g = 0;
                                ?>
                                <table style="margin-bottom: 70px">
                                    <tr class="table_style">
                                        @foreach( $fields as $campo )
                                            <th class="upper">{{ $campo }}</th>
                                        @endforeach
                                    </tr>
                                    @foreach($groupBy as $clave => $group)
                                        <?php
                                            $location_current_group = $group->locationname;
                                            $tank_current_group = $group->tank_name;
                                            $stock_lb = number_format($group->stock_lb , $decimals_in_tickets);
                                            $stock_lbd = number_format($group->stock_lbd , $decimals_in_tickets);
                                            $stkMT_cal = $group->stock_lb / 2204.62;
                                            $capMT_cal = ($group->capacity * 56) / 2204.62;
                                            $stkMT = number_format($stkMT_cal, $decimals_in_tickets);
                                            $capMT = number_format($capMT_cal, $decimals_in_tickets);
                                            $bal_cal = $capMT_cal - $stkMT_cal;
                                            $bal = number_format($bal_cal, $decimals_in_tickets);
                                            $stock_lb_total = $stock_lb_total + $group->stock_lb;
                                            $stock_lbd_total = $stock_lbd_total + $group->stock_lbd;
                                            $stkMT_total = $stkMT_total + $stkMT_cal;
                                            $capMT_total = $capMT_total + $capMT_cal;
                                            $bal_total = $bal_total + $bal_cal;
                                        ?>          
                                        @if( $group_commodity->cname == $group->cname)
                                            <tr id="fieldRow">
                                                <td> {{ $location_current_group }} </td>
                                                <td> {{ $tank_current_group }} </td>
                                                <td style="text-align: right"> {{ $stock_lb }} </td>
                                                <td style="text-align: right"> {{ $stock_lbd }} </td>
                                                <td style="text-align: right"> {{ $stkMT }} </td>
                                                <td style="text-align: right"> {{ $capMT }} </td>
                                                <td style="text-align: right"> {{ $bal }} </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    <tr>
                                        <th colspan="2" style="text-align: center">TOTAL: </th>
                                        <th style="text-align: right">{{ number_format($stock_lb_total_g, $decimals_in_tickets) }} </th>
                                        <th style="text-align: right">{{ number_format($stock_lbd_total_g, $decimals_in_tickets) }}</th>
                                        <th style="text-align: right">{{ number_format($stkMT_total_g, $decimals_in_tickets) }}</th>
                                        <th style="text-align: right">{{ number_format($capMT_total_g, $decimals_in_tickets) }}</th>
                                        <th style="text-align: right">{{ number_format($bal_total_g, $decimals_in_tickets) }}</th>
                                    </tr>
                                </table>
                            <?php
                                $stock_lb_total_g = 0;
                                $stock_lbd_total_g = 0;
                                $stkMT_total_g = 0;
                                $capMT_total_g = 0;
                                $bal_total_g = 0;
                                $stkMT = 0; 
                                $capMT = 0; $bal = 0;
                                $location_current = ''; $location_current = '';
                                $location_current_group = ''; $tank_current_group = '';
                            ?>
                            @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
