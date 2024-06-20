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
                    <p> {{ $coname }} </p>
                    <p> SHIPPING VOID REPORT </p>
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
                                        if($lang == "en"){
                                            $date = new DateTime($row->date_start);
                                            $fecha = $date->format('m/d/y');
                                        }else{
                                            $date = new DateTime($row->date_start);
                                            $fecha = $date->format('d/m/y');
                                        }
                                        ?>
                                        <tr id="fieldRow">
                                            <td style="text-align: center"> {{ $fecha }} </td>
                                            @if( $display_show_id == 0 )
                                                <td style="text-align: center"> {{ $row->source_id }} </td>
                                            @else
                                                <td style="text-align: center"> {{ $row->show_id }} </td>
                                            @endif
                                            <td> {{ $row->name .' '. $row->lastname }} </td>
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
