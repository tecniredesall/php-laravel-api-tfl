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
                    <?php
                        if($lang == "en"){
                            $fecha = date('m-d-y');
                        }else{
                            $fecha = date('d-m-y');
                        }
                    ?>
                    <p style="text-align: center;"> TANK REPORT </p>
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
                                        $tank_old = ''; $comoditie_old = '';
                                        $full = 0; $full_total = 0; 
                                    ?>
                                    @foreach( $infoFormato as $row )
                                        <?php
                                            $tank_current = $row->tname;
                                            $commoditie_current = $row->cname;
                                            $full = ($row->tstock > 0) ? ( $row->tstock / $row->tcapacity) * 100 : 0;
                                            $full_total = number_format($full , 2);
                                        ?>
                                            <tr>
                                                @if( $tank_current !== $tank_old )
                                                    <td> {{ $tank_current }} </td>
                                                <?php 
                                                    $tank_old = $tank_current;
                                                ?>
                                                @else
                                                    <td> </td>
                                                @endif
                                                <td style="text-align: right"> {{ number_format(round($row->tcapacity)) }} </td>
                                                <td style="text-align: right"> {{ number_format(round($row->tstock)) }} </td>
                                                <td style="text-align: right"> {{ number_format(round($row->stocklbd)) }} </td>
                                                @if ( $full > 95 )
                                                    <td style="background:red;text-align: right"> {{ $full_total }} %</td>
                                                @elseif( $full == '' )
                                                    <td style="text-align: right"> - % </td>
                                                @else
                                                    <td> {{ $full_total }} % </td>
                                                @endif
                                                @if( $commoditie_current !== $comoditie_old )
                                                    <td> {{ $commoditie_current }} </td>
                                                <?php 
                                                    $comoditie_old = $commoditie_current;
                                                ?>
                                                @else
                                                    <td> </td>
                                                @endif
                                                <td> {{ isset($row->locationname) ? $row->locationname : 'N/A' }} </td>
                                            </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot></tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
