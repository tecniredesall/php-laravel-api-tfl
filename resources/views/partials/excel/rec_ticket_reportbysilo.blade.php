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
			<p> RECEIVE TICKETS REPORT BY SILO </p>
			<p> FROM: {{ $date_from }} </p>
		</div>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="panel">
					<div class="panel-body">
						<?php
						//Inicializacion de variables para header names.
						$location_old = ''; $commoditie_old = ''; $date_old = '';
						//Inicializacion de variables para totales.
						$totalnet_l = 0; $totalnet_location = 0;
						$totalnetdrywt_l = 0; $totalnetdrywt_location = 0;
						$totalnet_c = 0; $totalnetdrywt_c = 0;
						$location_total_old = ''; $location_total_current = '';
						$commoditie_total_old = ''; $commoditie_total_current = '';
						$date_total_old = ''; $date_total_current = '';
						$totalnet_commoditie = 0; $totalnetdrywt_commoditie = 0;
						$count_iteration = 0;
						?>
						@foreach( $groupBy as $key => $group )
							<?php
							//Variables para totales
							$date_current = $group->date_end;
							$location_total_current = $location_total_old;
							$commoditie_total_current = $commoditie_total_old;
							$date_total_current = $date_total_old;
							//Headers names
							$location_current = $group->location_name;
							$commoditie_current = $group->commoditie_name;
							//$date_current = $group->date_end;
							//Totales para cada Farm
							$totalnet = number_format($group->totalnet , $decimals_in_tickets);
							$totalnetdrywt = number_format($group->totalnetdrywt , $decimals_in_tickets);
							//Validación para mostrar totales Seller y Commodity
							$longitud = sizeof($groupBy);
							?>
							@if( ($location_current.$commoditie_current !== $location_old.$commoditie_old ) && ( $totalnetdrywt_commoditie !== 0 && $totalnet_commoditie !== 0 ) )
								<table style="margin-top:0px" class="without_border">
									<tr>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style=""> Total {{ isset($commoditie_old) ? $commoditie_old : 'N/A' }}</td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border"> {{ $totalnet_commoditie }} </td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border"> {{ $totalnetdrywt_commoditie }} </td>
									</tr>
								</table>
							@endif
							@if( ( $location_current !== $location_old ) && ( $totalnetdrywt_location !== 0 && $totalnet_location !== 0 ) )
								<table style="margin-top:0px" class="without_border">
									<tr>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style=""> Total {{ isset($location_old) ? $location_old : 'N/A' }}</td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border"> {{ $totalnet_location }} </td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border"> {{ $totalnetdrywt_location }} </td>
									</tr>
								</table>
							@endif
							<?php
							if( $location_old === '' || ( $location_old === $location_current )){
								$totalnet_l = $totalnet_l + $group->totalnet;
								$totalnetdrywt_l = $totalnetdrywt_l + $group->totalnetdrywt;
								$totalnet_location = number_format($totalnet_l , $decimals_in_tickets);
								$totalnetdrywt_location = number_format($totalnetdrywt_l , $decimals_in_tickets);
							}else{
								$totalnet_l = $group->totalnet;
								$totalnetdrywt_l = $group->totalnetdrywt;
								$totalnet_location = number_format($totalnet_l, $decimals_in_tickets);
								$totalnetdrywt_location = number_format($totalnetdrywt_l, $decimals_in_tickets);
							}
							?>
							@if( ( $location_old !== $location_current) ||  $location_old == ''  )
								<p>{{ $location_current }}</p>
							@endif
							@if( $location_current.$commoditie_current !== $location_old.$commoditie_old )
								<p style="padding-left: 5%;">{{ $commoditie_current }}</p>
							@endif
							<?php
							$date_current = explode('-', $date_current);
							if($lang == 'en'){
								$date_current = $date_current[1].'-'.$date_current[2].'-'.$date_current[0];
							}else{
								$date_current = $date_current[2].'-'.$date_current[1].'-'.$date_current[0];
							}
							?>
							<p style="padding-left: 10%;">{{ $date_current }}</p>
							<table id="" class="table table-striped table-bordered">
								<thead>
								<tr class="table_style">
									@foreach($fields as $campo)
										<th class="upper" style="width: 20">{{ $campo }}</th>
									@endforeach
								</tr>
								</thead>
								<tbody>
								@foreach($infoFormato as $clave => $row)
									<?php
									if($lang == "en"){
										$date = new DateTime($row->date_end);
										$fecha = $date->format('m/d/y');
									}else{
										$date = new DateTime($row->date_end);
										$fecha = $date->format('d/m/y');
									}

									$net = number_format($row->net , $decimals_in_tickets);
									$netdrywt = number_format($row->netdrywt, $decimals_in_tickets);
									?>
									@if( ($row->date_end == $group->date_end) && ($row->commoditie_id == $group->commoditie_id) && ($row->location_id == $group->location_id))
										<tr id="fieldRow">
											<td style="text-align: center"> {{ $fecha }} </td>
											@if( $display_show_id == 0 )
												<td style="text-align: center"> {{ $row->source_id }} </td>
											@else
												<td style="text-align: center"> {{ $row->show_id }} </td>
											@endif
											<td style="text-align: center"> {{ $row->orgticket }} </td>
											<td style="text-align: center"> {{ $row->trailerlicense }} </td>
											<td> {{ $row->farm_name }} </td>
											<td> {{ $row->location_name }} </td>
											<td style="text-align: right"> {{ number_format($row->moisture , $decimals_in_tickets) }} </td>
											<td style="text-align: right"> {{ $net }} </td>
											<td style="text-align: right"> {{ $netdrywt }} </td>
										</tr>
										<?php
										$commoditie_total_old = $row->commoditie_id;
										$location_total_old = $row->location_id; //1
										$date_total_old = $row->date_end;
										//Variables para cabeceras
										$location_old = $group->location_name;
										$commoditie_old = $group->commoditie_name;
										?>
									@endif
									@if( ($row->location_id == $group->location_id) && ($row->commoditie_id == $group->commoditie_id) )
										<?php
										$totalnet_c = $totalnet_c + $row->net;
										$totalnetdrywt_c = $totalnetdrywt_c + $row->netdrywt;
										$totalnet_commoditie = number_format($totalnet_c, $decimals_in_tickets);
										$totalnetdrywt_commoditie = number_format($totalnetdrywt_c , $decimals_in_tickets);
										?>
									@endif
								@endforeach
								</tbody>
							</table>
							<table class="without_border">
								<?php
								$fecha = explode('-', $group->date_end);
								if($lang == 'en'){
									$fecha = $fecha[1].'-'.$fecha[2].'-'.$fecha[0];
								}else{
									$fecha = $fecha[2].'-'.$fecha[1].'-'.$fecha[0];
								}
								?>
								<tr>
									<td class="col-lg-4 col-md-4 col-sm-6 without_border" style=""> Total {{ isset($group->date_end) ? $fecha : 'N/A' }}</td>
									<td class="col-lg-4 col-md-4 col-sm-6 without_border"> {{ $totalnet }} </td>
									<td class="col-lg-4 col-md-4 col-sm-6 without_border"> {{ $totalnetdrywt }} </td>
								</tr>
								<?php
								$count_iteration++;
								?>
								@if( $longitud == $count_iteration )
									<tr>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style=""> Total {{ isset($group->commoditie_name) ? $group->commoditie_name : 'N/A' }}</td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border"> {{ $totalnet_commoditie }} </td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border"> {{ $totalnetdrywt_commoditie }} </td>
									</tr>
								@endif
								@if( $longitud == $count_iteration )
									<tr>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border" style=""> Total {{ isset($group->location_name) ? $group->location_name : 'N/A' }}</td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border"> {{ $totalnet_location }} </td>
										<td class="col-lg-4 col-md-4 col-sm-6 without_border"> {{ $totalnetdrywt_location }} </td>
									</tr>
								@endif
							</table>
							<?php
							$totalnet_c = 0;
							$totalnetdrywt_c = 0;
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

