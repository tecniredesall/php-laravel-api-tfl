<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="application/pdf; charset=utf-8"/>
	<base href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" />
	<base href="{{ env('APP_ENV') === 'local' ? asset('assets/css/dashboard.min.css') : secure_asset('assets/css/dashboard.min.css') }}"/>
	<base href="{{ env('APP_ENV') === 'local' ? asset('assets/dashboard.css') : secure_asset('assets/dashboard.css') }}"/>
	<link href="{{ env('APP_ENV') === 'local' ? asset('assets/css/global_reports.css') : secure_asset('assets/css/global_reports.css') }}" rel="stylesheet" >
	<title> General Report </title>
	<style>
		.page {
			padding: 20px;
		}
		table, tr, td, th, tbody, thead, tfoot {
			page-break-inside: avoid !important;
		}
	</style>
</head>
<body>
<div class="page"></div>
<div class="content">
	<div class="panel panel-default">
		<div class="sty_header">
			<p style="text-align: center;"> {{ $coname }} </p>
			<p style="text-align: center;"> RECEIVING TICKETS REPORT BY FARMER WITH DEDUCTION </p>
			<p style="text-align: center;"> FROM: {{ $date_from }} </p>
		</div>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="panel">
					<div class="panel-body">
						<?php
						//Inicializacion de variables para header names.
						$commoditie_old = ''; $seller_old = ''; $farm_old = '';
						//Inicializacion de variables para totales.
						$totalnet_c = 0; $totalnet_commoditie = 0;
						$totalnetdrywt_c = 0; $totalnetdrywt_commoditie = 0;
						$totalnet_s = 0; //$totalnet_commoditie = 0;
						$totalnetdrywt_s = 0; //$totalnetdrywt_commoditie = 0;
						$commoditie_total_old = ''; $commoditie_total_current = '';
						$seller_total_old = ''; $seller_total_current = '';
						$farm_total_old = ''; $farm_total_current = '';
						$totalnet_seller = 0; $totalnetdrywt_seller = 0;
						$count_iteration = 0;
						$ded = 0; $totalded = 0;
						$totalded_s = 0; $totalded_seller = 0;
						$totalded_c = 0; $totalded_commoditie = 0;

						?>
						@foreach( $groupBy as $key => $group )
							<?php
							//Variables para totales
							$farm_total_current = $farm_total_old;
							$seller_total_current = $seller_total_old; // 0
							$commoditie_total_current = $commoditie_total_old;
							//Headers names
							$commoditie_current = $group->commoditie_name;
							$seller_current = $group->seller_name;
							$farm_current = $group->farm_name;
							//Totales para cada Farm
							$totalnet = number_format($group->totalnet , $decimals_in_tickets);
							$ded = $group->totalnet - $group->totalnetdrywt;
							$totalded = $totalded + $ded;
							$totalnetdrywt = number_format($group->totalnetdrywt , $decimals_in_tickets);
							//Validación para mostrar totales Seller y Commodity
							$longitud = sizeof($groupBy);
							?>
							@if( ($commoditie_current.$seller_current !== $commoditie_old.$seller_old ) && ( $totalnetdrywt_seller !== 0 && $totalnet_seller !== 0 ) )
								<table style="margin-top:0px" class="without_border">
									<tr>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 60%; text-align: right"> Total {{ isset($seller_old) ? $seller_old : 'N/A' }}</td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 13%; text-align: right"> {{ $totalnet_seller }} </td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 13%; text-align: right"> {{ $totalded_seller }} </td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 14%; text-align: right"> {{ $totalnetdrywt_seller }} </td>
									</tr>
								</table>
							@endif
							@if( ( $commoditie_current !== $commoditie_old ) && ( $totalnetdrywt_commoditie !== 0 && $totalnet_commoditie !== 0 ) )
								<table style="margin-top:0px" class="without_border">
									<tr>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 60%; text-align: right"> Total {{ isset($commoditie_old) ? $commoditie_old : 'N/A' }}</td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 13%; text-align: right"> {{ $totalnet_commoditie }} </td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 13%; text-align: right"> {{ $totalded_commoditie }} </td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 14%; text-align: right"> {{ $totalnetdrywt_commoditie }} </td>
									</tr>
								</table>
							@endif
							<?php
							if(  $commoditie_old === '' || ( $commoditie_old === $commoditie_current )){
								$totalnet_c = $totalnet_c + $group->totalnet;
								$totalnetdrywt_c = $totalnetdrywt_c + $group->totalnetdrywt;
								$totalnet_commoditie = number_format($totalnet_c , $decimals_in_tickets);
								$totalnetdrywt_commoditie = number_format($totalnetdrywt_c , $decimals_in_tickets);
								$totalded_c = $totalnet_c - $totalnetdrywt_c;
								$totalded_commoditie = number_format($totalded_c , $decimals_in_tickets);
							}else{
								$totalnet_c = $group->totalnet;
								$totalnetdrywt_c = $group->totalnetdrywt;
								$totalnet_commoditie = number_format($totalnet_c , $decimals_in_tickets);
								$totalnetdrywt_commoditie = number_format($totalnetdrywt_c , $decimals_in_tickets);
								$totalded_c = $totalnet_c - $totalnetdrywt_c;
								$totalded_commoditie = number_format($totalded_c , $decimals_in_tickets);
							}
							?>
							{{--@if( ( $commoditie_old !== $commoditie_current &&  $seller_old !== $seller_current ) ||  $commoditie_old == ''  )--}}
							@if( ( $commoditie_old !== $commoditie_current) ||  $commoditie_old == ''  )
								<p>{{ $commoditie_current }}</p>
							@endif
							{{--@if(  ($commoditie_old !== $commoditie_current &&  $seller_old !== $seller_current ) || $seller_old == '' ) --}}
							@if( $commoditie_current.$seller_current !== $commoditie_old.$seller_old )
								<p style="padding-left: 5%;">{{ $seller_current }}</p>
							@endif
							<p style="padding-left: 10%;">{{ $farm_current }}</p>
							<table id="">
								<thead>
								<tr>
									@foreach($fields as $campo)
										<th style="width: 40px">{{ $campo }}</th>
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
									$net = number_format($field->net, $decimals_in_tickets);
									$ded = $field->net - $field->netdrywt;
									$deduction = number_format($ded, $decimals_in_tickets);
									$netdrywt = number_format($field->netdrywt, $decimals_in_tickets);
									?>
									@if( ($field->commoditie_id == $group->commoditie_id) && ($field->seller_id == $group->seller_id) && ($field->farm_id == $group->farm_id))
										<tr id="fieldRow">
											<td style="text-align: center"> {{ $fecha }} </td>
											@if( $display_show_id == 0 )
												<td style="text-align: center"> {{ $field->source_id }} </td>
											@else
												<td style="text-align: center"> {{ $field->show_id }} </td>
											@endif
											<td style="text-align: center"> {{ $field->tank_name }} </td>
											<td style="text-align: right"> {{ number_format($field->moisture, 2) }} </td>
											<td style="text-align: right"> {{ number_format($field->testwt, 2) }} </td>
											<td style="text-align: right"> {{ $net }} </td>
											<td style="text-align: right"> {{ $deduction }} </td>
											<td style="text-align: right"> {{ $netdrywt }} </td>
										</tr>
										<?php
											$commoditie_total_old = $field->commoditie_id;
											$seller_total_old = $field->seller_id; //1
											$farm_total_old = $field->farm_id;
											//Variables para cabeceras
											$commoditie_old = $group->commoditie_name;
											$seller_old = $group->seller_name;
										?>
									@endif
									@if( ($field->commoditie_id == $group->commoditie_id) && ($field->seller_id == $group->seller_id) )
										<?php
										$totalnet_s = $totalnet_s + $field->net;
										$totalnetdrywt_s = $totalnetdrywt_s + $field->netdrywt;
										$totalnet_seller = number_format($totalnet_s , $decimals_in_tickets);
										$totalnetdrywt_seller = number_format($totalnetdrywt_s , $decimals_in_tickets);
										$totalded_s = $totalnet_s - $totalnetdrywt_s;
										$totalded_seller = number_format($totalded_s , $decimals_in_tickets);
										?>
									@endif
								@endforeach
								</tbody>
							</table>
							<table class="without_border">
								<tr>
									<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 60%; text-align: right"> Total {{ isset($group->farm_name) ? $group->farm_name : 'N/A' }}</td>
									<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 13%; text-align: right"> {{ $totalnet }} </td>
									<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 13%; text-align: right"> {{ number_format($totalded, $decimals_in_tickets) }} </td>
									<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 14%; text-align: right"> {{ $totalnetdrywt }} </td>
								</tr>
								{{--@if( $totalnet_s !== 0 &&  $totalnetdrywt_s !== 0 && ( $commoditie_total_old === $commoditie_total_current ) && ( $seller_total_old === $seller_total_current) || ( $farm_total_old !== $group->farm_id) || ( $seller_total_current == ''))--}}
								<?php
								$count_iteration++;
								?>
								@if( $longitud == $count_iteration )
									<tr>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 60%; text-align: right"> Total {{ isset($group->seller_name) ? $group->seller_name : 'N/A' }}</td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 13%; text-align: right"> {{ $totalnet_seller }} </td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 13%; text-align: right"> {{ $totalded_seller }} </td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 14%; text-align: right"> {{ $totalnetdrywt_seller }} </td>
									</tr>
								@endif
								{{--@if( $totalnet_c !== 0 &&  $totalnetdrywt_c !== 0  && ( $commoditie_total_old === $group->commoditie_id ) )--}}
								@if( $longitud == $count_iteration )
									<tr>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 60%; text-align: right"> Total {{ isset($group->commoditie_name) ? $group->commoditie_name : 'N/A' }}</td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 13%; text-align: right"> {{ $totalnet_commoditie }} </td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 13%; text-align: right"> {{ $totalded_commoditie }} </td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style="width: 14%; text-align: right"> {{ $totalnetdrywt_commoditie }} </td>
									</tr>
								@endif
							</table>
							<?php
								$totalnet_s = 0;
								$totalnetdrywt_s = 0;
								$count_commoditie = 0;
								$totaldeductions = 0;
								$totalded = 0;
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
