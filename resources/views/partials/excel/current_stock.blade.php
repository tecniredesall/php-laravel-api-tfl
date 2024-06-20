<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8"/>
        <title>Reporte General </title>
    </head>
    <body>

        <div class="content">
            <div class="panel panel-default">
                <div class="sty_header">
                    <p> {{ isset($coname) ? $coname : '' }} </p>
                    <p> CURRENT INVENTORY REPORT </p>
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
                                    @foreach( $infoFormato as $row )
                                        <?php
                                            $stock = number_format(intval($row->stock) , $decimals_in_tickets);
                                        ?>
                                        <tr>
                                            <td> {{ $row->cname }} </td>
                                            <td style="text-align: right;">{{ $stock }}</td>
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
