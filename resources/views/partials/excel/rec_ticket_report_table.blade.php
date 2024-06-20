<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="application/vnd.sealed-xls; charset=utf-8"/>
	<title>Reporte General </title>
</head>
<body>
<div class="page"></div>
<div class="content">
    <div class="panel panel-default">
        <div class="sty_header">
            <p> {{ $coname }} </p>
            <p> RECEIVE TICKETS REPORT TABLE </p>
            <p> FROM: {{ $date_from }} </p>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="panel">
                    <div class="panel-body">
                        <?php
                            $totaldeductions = 0;
                        ?>
                        @foreach( $groupBy as $key => $group )
                            <p>{{ $group->commoditie_name }}</p>
                            <table id="">
                                <thead>
                                <tr>
                                    @foreach($fields as $campo)
                                        <th>{{ $campo }}</th>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($infoFormato as $clave => $field)
                                    <?php
                                        if($lang == "en"){
                                            $date = new DateTime($field->date_end);
                                            $fecha = $date->format('m/d/y');
                                        }else{
                                            $date = new DateTime($field->date_end);
                                            $fecha = $date->format('d/m/y');
                                        }
                                        $ded = $field->net - $field->netdrywt;
                                        $deduction = number_format($ded, $decimals_in_tickets);
                                    ?>
                                    @if( ($field->commoditie_id == $group->commoditie_id))
                                        <?php
                                            $totaldeductions = $totaldeductions + $ded;
                                        ?>
                                        <tr id="fieldRow">
                                            <td style="text-align: center"> {{ $fecha }} </td>
                                            @if( $display_show_id == 0 )
                                                <td style="text-align: center"> {{ $field->source_id }} </td>
                                            @else
                                                <td style="text-align: center"> {{ $field->show_id }} </td>
                                            @endif
                                            <td style="text-align: left"> {{ $field->seller_name }} </td>
                                            <td style="text-align: left"> {{ $field->drivername }} </td>
                                            <td style="text-align: left"> {{ $field->trailerlicense }} </td>
                                            <td style="text-align: right"> {{ number_format($field->moisture, 2) }} </td>
                                            <td style="text-align: right"> {{ number_format($field->weight, $decimals_in_tickets) }} </td>
                                            <td style="text-align: right"> {{ number_format($field->tare, $decimals_in_tickets) }} </td>
                                            <td style="text-align: right"> {{ number_format($field->net, $decimals_in_tickets) }} </td>
                                            <td style="text-align: right"> {{ $deduction }} </td>
                                            <td style="text-align: right"> {{ number_format($field->netdrywt, $decimals_in_tickets) }} </td>
                                        </tr>
                                @endif
                                @endforeach
                                <tfoot>
                                    <tr>
                                        <td colspan="8"> Total {{ isset($group->commoditie_name) ? $group->commoditie_name : 'N/A' }}</td>
                                        <td style="text-align: right"> {{ number_format($group->totalnet, $decimals_in_tickets) }} </td>
                                        <td style="text-align: right"> {{ number_format($totaldeductions, $decimals_in_tickets) }} </td>
                                        <td style="text-align: right"> {{ number_format($group->totalnetdrywt, $decimals_in_tickets) }} </td>
                                    </tr>
                                </tfoot>
                            </table>
                            <?php
                                $totaldeductions = 0;
                            ?>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
