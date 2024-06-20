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
                    <p> RECEIVE TICKETS COMMODITY SUMMARY </p>
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
                                    <?php
                                    $location_old = '';
                                    ?>
                                    @foreach( $groupBy as $field )
                                        <?php
                                        $location_current = $field->location_name;
                                        $totalnet = number_format($field->totalnet , $decimals_in_tickets);
                                        $totalnetdrywt = number_format($field->totalnetdrywt , $decimals_in_tickets);
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
                                            <td> {{ $field->commoditie_name }} </td>
                                            <td style="text-align: right"> {{ $totalnet }} </td>
                                            <td style="text-align: right"> {{ $totalnetdrywt }} </td>
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

