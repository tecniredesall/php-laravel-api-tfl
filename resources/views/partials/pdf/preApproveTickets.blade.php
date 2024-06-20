<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8"/>
    <link href="{{ env('APP_ENV') === 'local' ? asset('assets/css/report.css') : secure_asset('assets/css/report.css') }}" rel="stylesheet" >
    <title> General Report </title>
</head>
<body>
<div class="content">
    <div class="panel panel-default">
        <div class="sty_header">
            <table style="width: 100%; margin-bottom: 20px">
                <tbody>
                <tr>
                    <th class="upper batch"> {{ $contract['cmodity_contract_id'] }} </th>
                </tr>
                <tr>
                    <td class="repot-title"> REPORT </td>
                    <td style="width: 20%; padding-left: 30px;">
                        <img src="{{ asset('assets/logo_silosys.png') }}" width="110">
                    </td>
                </tr>
                </tbody>
            </table>
            <p style="font-weight: bold; font-size: 13px;">Details</p>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="panel">
                <div class="panel-body">
                    <table style="width: 100%; background-color: #f6f7f8; margin-bottom: 27px;">
                        <tbody>
                        <tr class="customRow">
                            <td class="left-title-header pad-top"> Batch No:  </th>
                            <td class="left-data-header id-batch pad-top"> {{ isset($contract['no_batch'] ) ? $contract['no_batch'] : '' }} </td>
                            <td class="pad-top status-report-title">
                                <?php
                                if( $contract['status'] == 'Draft' ){	$background = 'draft-status';	$icon = 'draft_icon';
                                }else if( $contract['status'] == 'Sent' ){	$background = 'sent-status';	$icon = 'sent_icon';
                                }else if( $contract['status'] == 'Approved' ){	$background = 'approved-status';	$icon = 'approved_icon';
                                }else{	$background = 'cmodity-status';	$icon = 'cmodity_icon';	}
                                ?>
                                <span class="statusbatch {{ $background }}">
									<img src="{{ asset("assets/$icon.png") }}" style="width: 12px;">
										{{ $contract['status'] }}
								</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="left-title-header"> Seller: </td>
                            <td class="left-data-header"> {{ isset($contract['seller']) ? $contract['seller'] : '' }} </td>
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
                            <td class="right-data-header"> Date: <span> {{ $fecha }}</span></td>
                        </tr>
                        <tr>
                            <td class="left-title-header">Buyer</td>
                            <td class="left-data-header"> {{ isset($contract['buyer']) ? $contract['buyer'] : '' }} </td>
                            <td class="right-data-header">Commodity: <span> {{ isset($contract['name']) ? $contract['name'] : '' }}</span> </td>

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
                            <td class="left-title-header">Elevator:</td>
                            <td class="left-data-header"> {{ $contract['elevator'] }} </td>
                            <td class="right-data-header marg-btm">Quantity: <span class="id-batch">{{ number_format($total, $contract['decimals_in_tickets']) }} {{ $contract['metric_system'] }} </span> </td>
                        </tr>
                        <tr></tr>
                        </tbody>
                    </table>
                    <p style="font-weight: bold; font-size: 14px;">@if( isset( $tickets ) ) Total Tickets:  {{ $k + 1 }}  @endif </p>
                    @if(isset( $tickets ))
                        <table style="width: 100%;" class="table-data-report">
                            <thead>
                            <tr class="table_style" style="background-color:#0068d1; font-size: 12px;">
                                <th class="upper">ID</th>
                                <th class="upper">FIELD TICKET</th>
                                <th class="upper">CREATED</th>
                                <th class="upper">WEIGHT</th>
                                <th class="upper">CHARACTERISTICS</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $tickets as $k => $value )
                                <tr style="font-size: 12px;">
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
                                    <td style="text-align: center"> {{ $fecha }} </td>
                                    <td style="text-align: center"> {{ number_format(isset($value->net) ? $value->net : 0, $contract['decimals_in_tickets']) }} {{ $contract['metric_system'] }}</td>
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