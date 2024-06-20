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
                    <p> SHIPPING TICKETS COMMODITY SUMMARY </p>
                    <p> FROM: {{ $date_from }} </p>
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
                                    @foreach( $groupBy as $group )
                                        <?php
                                            $totalnet = number_format($group->net , $decimals_in_tickets);
                                        ?>
                                        <tr id="fieldRow">
                                            <td> {{ $group->commoditie_name }}</td>
                                            <td> {{ $group->commoditie_name }} {{ $totalnet }} </td>
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

