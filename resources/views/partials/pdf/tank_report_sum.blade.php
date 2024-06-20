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
                    <p style="text-align: center;"> TANK REPORT SUM </p>
                    <?php
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
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="panel">
                            <div class="panel-body">
                                <table id="" class="table table-striped table-bordered">
                                    <thead>
                                    <tr class="table_style">
                                        @foreach( $fields as $campo )
                                            <th class="upper">{{ $campo }}</th>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                        $location_old = '';
                                        $stock_lb = 0; $stock_lbd = 0; $stkMT = 0; $capMT = 0; 
                                        $stkMT_cal = 0; $capMT_cal = 0; $bal = 0;
                                        $stock_lb_total = 0; $stock_lbd_total = 0; $stkMT_total = 0; 
                                        $capMT_total = 0; $bal_total = 0;
                                    ?>
                                    @foreach( $infoFormato as $row )
                                        <?php
                                            $location_current = $row->locationname;
                                            $stock_lb = number_format($row->stock_lb , $decimals_in_tickets);
                                            $stock_lbd = number_format($row->stock_lbd , $decimals_in_tickets);
                                            $stkMT_cal = $row->stock_lb / 2204.62;
                                            $capMT_cal = ($row->capacity * 56) / 2204.62;
                                            $stkMT = number_format($stkMT_cal, $decimals_in_tickets);
                                            $capMT = number_format($capMT_cal, $decimals_in_tickets);
                                            $bal_cal = $capMT_cal - $stkMT_cal;
                                            $bal = number_format($bal_cal, $decimals_in_tickets);
                                            $stock_lb_total = $stock_lb_total + $row->stock_lb;
                                            $stock_lbd_total = $stock_lbd_total + $row->stock_lbd;
                                            $stkMT_total = $stkMT_total + $stkMT_cal;
                                            $capMT_total = $capMT_total + $capMT_cal;
                                            $bal_total = $bal_total + $bal_cal; 
                                        ?>
                                            <tr id="fieldRow">
                                                 @if( $location_current !== $location_old )
                                                    <td> {{ $location_current }} </td>
                                                <?php 
                                                    $location_old = $location_current;
                                                ?>
                                                @else
                                                    <td> </td>
                                                @endif
                                                <td> {{ $row->cname }} </td>
                                                <td style="text-align: right"> {{ $stock_lb }} </td>
                                                <td style="text-align: right"> {{ $stock_lbd }} </td>
                                                <td style="text-align: right"> {{ $stkMT }} </td>
                                                <td style="text-align: right"> {{ $capMT }} </td>
                                                <td style="text-align: right"> {{ $bal }} </td>
                                            </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" style="text-align: center">TOTAL: </th>
                                            <th style="text-align: right">{{ number_format($stock_lb_total, $decimals_in_tickets) }} </th>
                                            <th style="text-align: right">{{ number_format($stock_lbd_total, $decimals_in_tickets) }}</th>
                                            <th style="text-align: right">{{ number_format($stkMT_total, $decimals_in_tickets) }}</th>
                                            <th style="text-align: right">{{ number_format($capMT_total, $decimals_in_tickets) }}</th>
                                            <th style="text-align: right">{{ number_format($bal_total, $decimals_in_tickets) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

