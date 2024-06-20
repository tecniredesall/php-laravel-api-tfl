<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="application/vnd.sealed-xls; charset=utf-8"/>
    <title>Reporte General </title>
</head>
<body>
<div class="content">
    <div class="panel panel-default">
        <div class="sty_header">
            <p> {{ isset($coname) ? $coname : '' }} </p>
            <p> GLOBAL STOCK </p>
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
                            @foreach( $infoFormato as $key => $row)
                                <?php
                                $pesoCampo = number_format($row->pesoCampo, $decimals_in_tickets);
                                $pesoAnalizado = number_format($row->pesoAnalizado , $decimals_in_tickets);
                                $salidas = number_format($row->salidas , $decimals_in_tickets);
                                $totalPeso = number_format($row->totalPeso , $decimals_in_tickets);
                                $exisIni = number_format(0.00000 , $decimals_in_tickets);
                                ?>
                                <tr>
                                    <td> {{ $key +1 }} </td>
                                    <td> {{ $row->commodities }} </td>
                                    <td> {{ $row->metric }} </td>
                                    <td style="text-align: center;"> {{ $exisIni }} </td>
                                    <td style="text-align: right;"> {{ $pesoCampo }} </td>
                                    <td style="text-align: right;"> {{ $pesoAnalizado }} </td>
                                    <td style="text-align: right;"> {{ $salidas }} </td>
                                    <td style="text-align: right;"> {{ $totalPeso }} </td>
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
