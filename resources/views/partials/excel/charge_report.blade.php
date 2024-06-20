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
                    <p> CHARGE REPORT </p>
        			<p> REPORT DATE </p>
                    <p> FROM: {{ $date_from }} </p>
        		</div>
        	</div>
        	<div class="panel-body">
        		<div class="row">
        			<div class="col-lg-12 col-md-12 col-sm-12">
        				<div class="panel">
        					<div class="panel-body">
        						<table style="border:1px solid #000000;">
                                    <thead>
                                    <tr style="border:1px solid #000000; background-color: #ebedef;">
                                        @foreach( $fields as $campo )
                                            <th style="border:1px solid #000000;">{{ $campo }}</th>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $grand_ele_t = 0; $grand_ele_total = 0;
                                        $grand_dry_t = 0; $grand_dry_total = 0;
                                    ?>
                                    @foreach( $groupBy as $row )
                                    <?php
                                        $elevator_chr = number_format($row->elevator_chr, $decimals_in_tickets);
                                        $drying_chr = number_format($row->drying_chr, $decimals_in_tickets);
                                        $grand_ele_t = $grand_ele_t + $row->elevator_chr;
                                        $grand_dry_t = $grand_dry_t + $row->drying_chr;
                                        $grand_ele_total = number_format($grand_ele_t, 2);
                                        $grand_dry_total = number_format($grand_dry_t, 2);
                                    ?>
                                        <tr>
                                            <td style="border:1px solid #000000;"> {{ $row->seller_name }} </td>
                                            <td style="border:1px solid #000000;"> {{ $elevator_chr }} </td>
                                            <td style="border:1px solid #000000;"> {{ $drying_chr }} </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td> </td>
                                            <td style="border:1px solid #000000;"> $ {{ $grand_ele_total }} </td>
                                            <td style="border:1px solid #000000;"> $ {{ $grand_dry_total }} </td>
                                        </tr>
                                    </tfoot>
        						</table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
