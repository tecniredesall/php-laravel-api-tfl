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
                    <p> ALL RECEIVE TICKETS </p>
                    <?php
                    if($lang == "en"){
                        $fecha = date('m-d-y');
                    }else{
                        $fecha = date('d-m-y');
                    }
                    ?>
                    <p> REPORT DATE: "{{$fecha}}" </p>
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
                                    @foreach( $infoFormato as $row )
                                        <?php
                                            $weight = number_format($row->weight, $decimals_in_tickets);
                                            $tare = number_format($row->tare , 0);
                                            $netdrywt = number_format($row->netdrywt , $decimals_in_tickets);
                                            $wmoisture = number_format($row->moisture , $decimals_in_tickets);

                                        ?>
                                        <tr>
                                            <td> {{ $row->date_end }} </td>
                                            <td> {{ $row->seller_name }} </td>
                                            <td> {{ $row->drivername }} </td>
                                            <td style="text-align: right;"> {{ $weight }} </td>
                                            <td style="text-align: right;"> {{ $tare }} </td>
                                            <td style="text-align: right;"> {{ $netdrywt }} </td>
                                            <td style="text-align: right;"> {{ $wmoisture }} </td>
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
