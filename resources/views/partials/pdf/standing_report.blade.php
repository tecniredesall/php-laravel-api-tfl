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
                    <p style="text-align: center;"> STANDING REPORT </p>
                    <p style="text-align: center;"> INVENTORY UP TO "{{$fecha}}" </p>
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
                                    ?>
                                    @foreach( $infoFormato as $row )
                                        <?php
                                            $location_current = $row->location_name;
                                            $net = number_format($row->net , $decimals_in_tickets);
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
                                            <td> {{ $row->commoditie_name }} </td>
                                            <td style="text-align: right"> {{ $net }} </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot> </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
