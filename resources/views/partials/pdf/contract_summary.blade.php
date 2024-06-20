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
            <p style="text-align: center;"> {{ isset($coname) ? $coname : '' }} </p>
            <p style="text-align: center;"> CONTRACT SUMMARY </p>
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
                        <?php
                            $totalContratado = 0; $totalAcopiado = 0; $totalFacturado = 0;
                            $totalFaltante = 0; $totalEmbarcado = 0; $totalExedente = 0;
                        ?>
                        @foreach( $infoFormato as $key => $rows)
                            <h3> {{ $key }} </h3>
                                <table id="" class="table table-striped table-bordered">
                                    <thead>
                                    <tr class="table_style">
                                        @foreach( $fields as $campo )
                                            <th class="upper">{{ $campo }}</th>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($rows as $clave => $row)
                                        @if( $row["contract_number"] !== '')
                                            <?php
                                                $contratado = number_format($row["contratado"], $decimals_in_tickets);
                                                $acopiado = number_format($row["acopiado"], $decimals_in_tickets);
                                                $facturado = number_format($row["facturado"] , $decimals_in_tickets);
                                                $embarcado = number_format($row["embarcado"] , $decimals_in_tickets);
                                                $faltante = number_format($row["faltante"] , $decimals_in_tickets);
                                                $exedente = number_format($row["exedente"] , $decimals_in_tickets);
                                                $totalContratado += $row["contratado"];
                                                $totalAcopiado += $row["acopiado"];
                                                $totalFacturado += $row["facturado"];
                                                $totalEmbarcado += $row["embarcado"];
                                                $totalFaltante += $row["faltante"];
                                                $totalExedente += $row["exedente"];
                                            ?>
                                            <tr>
                                                <td style="text-align: left;"> {{ $row["contract_number"] }} </td>
                                                <td style="text-align: left;"> {{ $row["name"] }} </td>
                                                <td style="text-align: right;"> {{ $contratado }} </td>
                                                <td style="text-align: right;"> {{ $acopiado }} </td>
                                                <td style="text-align: right;"> {{ $facturado }} </td>
                                                <td style="text-align: right;"> {{ $embarcado }} </td>
                                                <td style="text-align: right;"> {{ $faltante }} </td>
                                                <td style="text-align: right;"> {{ $exedente }} </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="2">Total:</td>
                                        <td class="col-lg-4 col-md-4 col-sm-6" style="text-align: right;"> {{ number_format($totalContratado, $decimals_in_tickets) }} </td>
                                        <td class="col-lg-4 col-md-4 col-sm-6" style="text-align: right;"> {{ number_format($totalAcopiado, $decimals_in_tickets) }} </td>
                                        <td class="col-lg-4 col-md-4 col-sm-6" style="text-align: right;"> {{ number_format($totalFacturado, $decimals_in_tickets) }} </td>
                                        <td class="col-lg-4 col-md-4 col-sm-6" style="text-align: right;"> {{ number_format($totalEmbarcado, $decimals_in_tickets) }} </td>
                                        <td class="col-lg-4 col-md-4 col-sm-6" style="text-align: right;"> {{ number_format($totalFaltante, $decimals_in_tickets) }} </td>
                                        <td class="col-lg-4 col-md-4 col-sm-6" style="text-align: right;"> {{ number_format($totalExedente, $decimals_in_tickets) }} </td>
                                    </tr>
                                    </tfoot>
                                </table>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
