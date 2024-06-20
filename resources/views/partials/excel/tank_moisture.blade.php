<!DOCTYPE html>
<html lang="en">
    <head>
    	<meta http-equiv="Content-Type" content="application/pdf; charset=utf-8"/>
    </head>
    <body>
        <div class="content">
        	<div class="panel panel-default">
        		<div class="sty_header">
                    <p> {{ isset($coname) ? $coname : '' }} </p>
                    <p> TANK MOISTURE REPORT </p>
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
                                    @foreach( $groupBy as $row )
                                        <?php
                                            $location_current = $row->location_name;
                                            $avg_moisture = number_format($row->avg_moisture, $decimals_in_tickets);
                                            $max_moisture = number_format($row->max_moisture, $decimals_in_tickets);
                                            $min_moisture = number_format($row->min_moisture, $decimals_in_tickets);
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
                                            <td> {{ $row->tank_name }} </td>
                                            <td> {{ $row->commoditie_name }} </td>
                                            <td style="text-align: right"> {{ $avg_moisture }} </td>
                                            <td style="text-align: right"> {{ $max_moisture }} </td>
                                            <td style="text-align: right"> {{ $min_moisture }} </td>
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

