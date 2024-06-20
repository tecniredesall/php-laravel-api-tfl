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
			<p> ALL CONTRACTS REPORT </p>
		</div>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="panel">
					<div class="panel-body">
					<?php
					    //Inicializacion de variables para header names.
						$seller_old = ''; $contract_old = ''; 
						//Inicializacion de variables para totales. 
						$contract_total_old = ''; 
						$term_total_old = '';
						$total_p = 0; $total_price = 0;
						$table_total_p = 0; $table_total_price = 0;
						$total_c = 0; $total_seller = 0;
						$count_iteration = 0;
						$total_contrato = 0;
						$grand_t = 0; $grand_total = 0; $longitud = 0;
					?>
					@foreach( $groupBy as $key => $group )
					<?php
						//Headers names
						$seller_current = $group->seller_name;
						$contract_current = $group->contract_name;
						$longitud = sizeof($groupBy);
						?>
						@if( ($seller_current !== $seller_old ) && ( $total_seller !== 0 ) )
							<table style="border:1px solid #000000">
								<tr>
									<td style="border:1px solid #000000"> Total {{ isset($seller_old) ? $seller_old : 'N/A' }}</td>
									<td style="border:1px solid #000000"> $ {{ $total_seller }} </td>
								</tr>
							</table>
						@endif
						@if( ( $seller_old !== $seller_current) ||  $seller_old == ''  )
							<h5>Seller: {{ $seller_current }}</h5>
						@endif
						@if( $seller_current.$contract_current !== $seller_old.$contract_old )
							<h5 style="padding-left: 5%;">Contract: {{ $contract_current }}</h5>
						@endif
							<h5 style="padding-left: 10%;">Terms: {{ $group->cars .' @ '. $group->price .' per 100 Lbs' }}</h5>
						<table style="border:1px solid #000000">
							<thead>
							<tr style="border:1px solid #000000; background-color: #ebedef;">
								@foreach($fields as $campo)
									<th style="border:1px solid #000000">{{ $campo }}</th>
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
        							$total_p = ( $field->weight / 100 ) * ( $field->price - $field->assessment - $field->discount );
									$total_price =  number_format($total_p , $decimals_in_tickets);
        							$grand_t = $grand_t + $total_p;
								?>
								@if( ($field->seller_id == $group->seller_id) && ($field->contract_id == $group->contract_id) && ($field->term_id == $group->term_id))
									<tr id="fieldRow">
										<td style="text-align: center"> {{ $fecha }} </td>
										@if( $display_show_id == 0 )
											<td style="text-align: center"> {{ $field->source_id }} </td>
										@else
											<td style="text-align: center"> {{ $field->show_id }} </td>
										@endif
										<td style="text-align: center"> {{ $field->orgticket }} </td>
										<td style="text-align: center"> {{ $field->trailerlicense }} </td>
										<td> {{ $field->farm_name }} </td>
										<td style="text-align: right"> {{ number_format($field->net, $decimals_in_tickets) }} </td>
										<td style="text-align: right"> {{ number_format($field->netdrywt, $decimals_in_tickets) }} </td>
										<td style="text-align: right"> {{ number_format($field->moisture, $decimals_in_tickets) }} </td>
										<td style="text-align: right"> {{ number_format($field->weight, $decimals_in_tickets) }} </td>
										<td style="text-align: right"> {{ number_format($field->balance, $decimals_in_tickets) }} </td>
										<td style="text-align: right"> {{ $field->assessment }} </td>
										<td style="text-align: right"> {{ number_format($field->discount, $decimals_in_tickets) }} </td>
										<td style="text-align: right"> {{ number_format($field->price, $decimals_in_tickets) }} </td>
										<td style="text-align: right"> $ {{ $total_price }} </td>
									</tr>
								<?php
									$seller_total_old = $field->seller_id;
									$contract_total_old = $field->contract_id; //1
									$term_total_old = $field->term_id;
									//Variables para cabeceras
									$seller_old = $group->seller_name;
									$contract_old = $group->contract_name;
									$table_total_p = $table_total_p + $total_p;
									$table_total_price =  number_format($table_total_p , 2);
								?>
								@endif
								@if( $field->seller_id == $group->seller_id )
									<?php
										$total_c = $total_c + ( $field->weight / 100 ) * ( $field->price - $field->assessment - $field->discount );
										$total_seller = number_format($total_c , 2);
									?>
								@endif	
							@endforeach
							</tbody>
						</table>
						<table style="border:1px solid #000000;">
							<?php
								$count_iteration++;
							?>
							<tr>
								<td style="border:1px solid #000000">TERM TOTAL: </td>
								<td style="border:1px solid #000000"> $ {{ $table_total_price }}</td>
							</tr>
							@if( $longitud == $count_iteration )
								<tr>
									<td style="border:1px solid #000000"> Total {{ isset($group->seller_name) ? $group->seller_name : 'N/A' }}</td>
									<td style="border:1px solid #000000"> $ {{ $total_seller }} </td>
								</tr>
							@endif
						</table>
						<?php
						    $table_total_p = 0;
						    $total_c = 0;
						    $total_contrato = 0;
						?>
					@endforeach
					<?php
						$grand_total = $longitud > 0 ? number_format($grand_t/$longitud, 2) : 0;
					 ?>
						<table style="border:1px solid #000000;">
							<tr>
								<td style="border:1px solid #000000">GRAND TOTAL: </td>
								<td style="border:1px solid #000000"> $ {{ $grand_total }}</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</body>
</html>

