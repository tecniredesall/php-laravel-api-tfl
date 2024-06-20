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
			<p>{{ $coname }} </p>
			<p> RECEIVE TICKETS REPORT </p>
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
						$totalnet_seller_bushel = 0; $totalnetdrywt_seller_bushel = 0;
						$totalnet_commoditie_bushel = 0; $totalnetdrywt_commoditie_bushel = 0;
						$count_iteration = 0;
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
							$totalnetdrywt = number_format($group->totalnetdrywt , $decimals_in_tickets);
							$totalnet_bushel = number_format($group->totalnet / 56 , $decimals_in_tickets);
							$totalnetdrywt_bushel = number_format($group->totalnetdrywt / 56, $decimals_in_tickets);
							//Validación para mostrar totales Seller y Commodity
							$longitud = sizeof($groupBy);
							?>
							@if( ($commoditie_current.$seller_current !== $commoditie_old.$seller_old ) && ( $totalnetdrywt_seller !== 0 && $totalnet_seller !== 0 ) )
								<table style="margin-top:0px" class="without_border">
									<tr>
										<td style="" class="without_border"> Total {{ isset($seller_old) ? $seller_old : 'N/A' }}</td>
										<td class="without_border"> {{ $totalnet_seller }} </td>
										<td class="without_border"> {{ $totalnetdrywt_seller }} </td>
										<td class="without_border"> {{ $totalnet_seller_bushel }} </td>
										<td class="without_border"> {{ $totalnetdrywt_seller_bushel }} </td>
									</tr>
								</table>
							@endif
							@if( ( $commoditie_current !== $commoditie_old ) && ( $totalnetdrywt_commoditie !== 0 && $totalnet_commoditie !== 0 ) )
								<table style="margin-top:0px" class="without_border">
									<tr>
										<td style="" class="without_border"> Total {{ isset($commoditie_old) ? $commoditie_old : 'N/A' }}</td>
										<td class="without_border"> {{ $totalnet_commoditie }} </td>
										<td class="without_border"> {{ $totalnetdrywt_commoditie }} </td>
										<td class="without_border"> {{ $totalnet_commoditie_bushel }} </td>
										<td class="without_border"> {{ $totalnetdrywt_commoditie_bushel }} </td>
									</tr>
								</table>
							@endif
							<?php
							if( $commoditie_old === '' || ( $commoditie_old === $commoditie_current )){
								$totalnet_c = $totalnet_c + $group->totalnet;
								$totalnetdrywt_c = $totalnetdrywt_c + $group->totalnetdrywt;
								$totalnet_commoditie = number_format($totalnet_c , $decimals_in_tickets);
								$totalnetdrywt_commoditie = number_format($totalnetdrywt_c , $decimals_in_tickets);
								$totalnet_commoditie_bushel = number_format($totalnet_c / 56, $decimals_in_tickets);
								$totalnetdrywt_commoditie_bushel = number_format($totalnetdrywt_c / 56, $decimals_in_tickets);
							}else{
								$totalnet_c = $group->totalnet; //$totalnet_commoditie = 0;
								$totalnetdrywt_c = $group->totalnetdrywt; //$totalnetdrywt_commoditie = 0;
								$totalnet_commoditie = number_format($totalnet_c , $decimals_in_tickets);
								$totalnetdrywt_commoditie = number_format($totalnetdrywt_c , $decimals_in_tickets);
								$totalnet_commoditie_bushel = number_format($totalnet_c / 56, $decimals_in_tickets);
								$totalnetdrywt_commoditie_bushel = number_format($totalnetdrywt_c / 56, $decimals_in_tickets);
							}
							?>
							@if( ( $commoditie_old !== $commoditie_current) ||  $commoditie_old == ''  )
								<p>{{ $commoditie_current }}</p>
							@endif
							@if( $commoditie_current.$seller_current !== $commoditie_old.$seller_old )
								<p style="padding-left: 5%;">{{ $seller_current }}</p>
							@endif
							<p style="padding-left: 10%;">{{ $farm_current }}</p>
							<table id="" class="table table-striped table-bordered">
								<thead>
								<tr class="table_style">
									@foreach($fields as $campo)
										<th class="upper" style="width: 20">{{ $campo }}</th>
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

									$net = number_format($field->net , $decimals_in_tickets);
									$netdrywt = number_format($field->netdrywt , $decimals_in_tickets);
									$bushel_net = number_format($field->net / 56 , $decimals_in_tickets);
									$bushel_netdrywt = number_format($field->netdrywt / 56 , $decimals_in_tickets);
									?>
									@if( ($field->commoditie_id == $group->commoditie_id) && ($field->seller_id == $group->seller_id) && ($field->farm_id == $group->farm_id))
										<tr id="fieldRow">
											<td style="text-align: center"> {{ $fecha }} </td>
											@if( $display_show_id == 0 )
												<td style="text-align: center"> {{ $field->source_id }} </td>
											@else
												<td style="text-align: center"> {{ $field->show_id }} </td>
											@endif
											<td style="text-align: center"> {{ $field->orgticket }} </td>
											<td> {{ $field->farm_name }} </td>
											<td style="text-align: right"> {{ $field->moisture }} </td>
											<td style="text-align: right"> {{ $field->testwt }} </td>
											<td style="text-align: right"> {{ $net }} </td>
											<td style="text-align: right"> {{ $netdrywt }} </td>
											<td style="text-align: right"> {{ $bushel_net }} </td>
											<td style="text-align: right"> {{ $bushel_netdrywt }} </td>
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
										$totalnet_seller_bushel = number_format($totalnet_s / 56, $decimals_in_tickets);
										$totalnetdrywt_seller_bushel = number_format($totalnetdrywt_s / 56 , $decimals_in_tickets);
										?>
									@endif
								@endforeach
								</tbody>
							</table>
							<table class="without_border">
								<tr>
									<td style="" class="without_border"> Total {{ isset($group->farm_name) ? $group->farm_name : 'N/A' }}</td>
									<td class="without_border"> {{ $totalnet }} </td>
									<td class="without_border"> {{ $totalnetdrywt }} </td>
									<td class="without_border"> {{ $totalnet_bushel  }} </td>
									<td class="without_border"> {{ $totalnetdrywt_bushel }} </td>
								</tr>
								<?php
								$count_iteration++;
								?>
								@if( $longitud == $count_iteration )
									<tr>
										<td style="" class="without_border"> Total {{ isset($group->seller_name) ? $group->seller_name : 'N/A' }}</td>
										<td class="without_border"> {{ $totalnet_seller }} </td>
										<td class="without_border"> {{ $totalnetdrywt_seller }} </td>
										<td class="without_border"> {{ $totalnet_seller_bushel }} </td>
										<td class="without_border"> {{ $totalnetdrywt_seller_bushel }} </td>
									</tr>
								@endif
								@if( $longitud == $count_iteration )
									<tr>
										<td style=""  class="without_border"> Total {{ isset($group->commoditie_name) ? $group->commoditie_name : 'N/A' }}</td>
										<td class="without_border"> {{ $totalnet_commoditie }} </td>
										<td class="without_border"> {{ $totalnetdrywt_commoditie }} </td>
										<td class="without_border"> {{ $totalnet_commoditie_bushel }} </td>
										<td class="without_border"> {{ $totalnetdrywt_commoditie_bushel }} </td>
									</tr>
								@endif
							</table>
							</table>
							<?php
							$totalnet_s = 0;
							$totalnetdrywt_s = 0;
							$count_commoditie = 0;
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
