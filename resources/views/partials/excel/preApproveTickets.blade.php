<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="application/vnd.sealed-xls; charset=utf-8"/>
    <title> General Report </title>
</head>
<body>
<div class="content">
    <div class="panel panel-default">
        <div>
            <table>
                <tbody>
                <tr>
                    <th> {{ $contract['cmodity_contract_id'] }} </th>
                </tr>
                <tr>
                    <td> REPORT </td>
                    <td>
                    </td>
                </tr>
                </tbody>
            </table>
            <p>Details</p>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="panel">
                <div class="panel-body">
                    <table>
                        <tbody>
                        <tr>
                            <td> Batch No:  </th>
                            <td> {{ isset($contract['no_batch'] ) ? $contract['no_batch'] : '' }} </td>
                            <td>
								<span>
									{{ $contract['status'] }}
								</span>
                            </td>
                        </tr>
                        <tr>
                            <td> Seller: </td>
                            <td> {{ isset($contract['seller']) ? $contract['seller'] : '' }} </td>
                            <?php
                            $fecha = '';
                            if( isset($contract['end_date']) ) {
                                if($contract['lang'] == "en"){
                                    $date = new DateTime($contract['end_date']);
                                    $fecha = $date->format('m/d/Y');
                                }else{
                                    $date = new DateTime($contract['end_date']);
                                    $fecha = $date->format('d/m/Y');
                                }
                            }
                            ?>
                            <td> Date: <span> {{ $fecha }} </span></td>
                        </tr>
                        <tr>
                            <td>Buyer</td>
                            <td> {{ isset($contract['buyer']) ? $contract['buyer'] : '' }} </td>
                            <td>Commodity: <span> {{ isset($contract['name']) ? $contract['name'] : '' }}</span> </td>

                        </tr>
                        <?php
                        $total = 0;
                        if(isset( $tickets )){
                            foreach( $tickets as $k => $value ){
                                $total += isset($value->net) ? $value->net : 0;
                            }
                        }
                        ?>
                        <tr>
                            <td>Elevator:</td>
                            <td> {{ $contract['elevator'] }} </td>
                            <td>Quantity: <span>{{ number_format($total, $contract['decimals_in_tickets']) }} {{ $contract['metric_system'] }} </span> </td>
                        </tr>
                        <tr></tr>
                        </tbody>
                    </table>
                    <p>@if( isset( $tickets ) ) Total Tickets:  {{ $k + 1 }}  @endif </p>
                    @if(isset( $tickets ))
                        <table>
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>FIELD TICKET</th>
                                <th>CREATED</th>
                                <th>WEIGHT</th>
                                <th>CHARACTERISTICS</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $tickets as $k => $value )
                                <tr>
                                    <td> {{ $value->ticket_id }} </td>
                                    <td> {{ ( $value->orgticket === '' ) ? 'N/A' : $value->orgticket }} </td>
                                    <?php
                                    $fecha = '';
                                    if( isset( $value->date_start )){

                                        if($contract['lang'] == "en"){
                                            $date = new DateTime($value->date_start);
                                            $fecha = $date->format('m / d / Y');
                                        }else{
                                            $date = new DateTime($value->date_start);
                                            $fecha = $date->format('d / m / Y');
                                        }
                                    }
                                    ?>
                                    <td> {{ $fecha }} </td>
                                    <td> {{ number_format(isset($value->net) ? $value->net : 0, $contract['decimals_in_tickets']) }} {{ $contract['metric_system'] }} </td>
                                    <td> @foreach( $value->characteristics as $key => $row ) {{ $row->cmodity_characteristic_id. ': ' }} <b> {{  $row->value }} </b> @endforeach </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
