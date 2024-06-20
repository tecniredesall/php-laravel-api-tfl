<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="application/pdf; charset=utf-8"/>
	<base href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" />
	<base href="{{ env('APP_ENV') === 'local' ? asset('assets/css/dashboard.min.css') : secure_asset('assets/css/dashboard.min.css') }}"/>
	<base href="{{ env('APP_ENV') === 'local' ? asset('assets/dashboard.css') : secure_asset('assets/dashboard.css') }}"/>
	<link href="{{ env('APP_ENV') === 'local' ? asset('assets/css/global_reports.css') : secure_asset('assets/css/global_reports.css') }}" rel="stylesheet" >
	<title>Reporte General </title>
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
<div class="content">
	<div class="panel panel-default">
		<div class="sty_header">
			<p style="text-align: center;"> {{ $coname }} </p>
			<p style="text-align: center;"> SHIPPING TICKETS REPORT </p>
			<p style="text-align: center;"> FROM: {{ $date_from }}</p>
		</div>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="panel">
					<div class="panel-body">
					<?php
					    //Inicializacion de variables para header names.
						$commoditie_old = '';	$buyer_old = '';
						//Inicializacion de variables para totales. 
						$totalnet_c = 0;	$totalnet_commoditie = 0;
						$totalnetdrywt_c = 0;	$totalnetdrywt_commoditie = 0;
						$totalnetdrywt_s = 0;	$totalnetdrywt_buyer = 0;
						$buyer_total_old = ''; $buyer_total_current = '';
						$count_iteration = 0;
					?>
					@foreach( $groupBy as $key => $group )
					<?php
						//Variables para totales
						$buyer_total_current = $buyer_total_old;
						//Headers names
						$commoditie_current = $group->commoditie_name;
						$buyer_current = $group->buyer_name;
						//Totales para cada Commodity
        				$totalnetdrywt = number_format($group->totalnetdrywt , $decimals_in_tickets);
						$longitud = sizeof($groupBy);
					?>
						@if( ($commoditie_current.$buyer_current !== $commoditie_old.$buyer_old ) && ( $totalnetdrywt_buyer !== 0 ) )			
							<table style="margin-top:0px" class="without_border">
								<tr>
									<td style="width: 650px;" class="without_border"> Total {{ isset($buyer_old) ? $buyer_old : 'N/A' }}</td>
									<td style="width: 250px" class="without_border"> {{ $totalnetdrywt_buyer }} </td>
								</tr>
							</table>			
						@endif
						@if( ( $commoditie_current !== $commoditie_old ) && ( $totalnetdrywt_commoditie !== 0) )			
							<table style="margin-top:0px" class="without_border">							
								<tr>
									<td style="width:650px" class="without_border"> Total {{ isset($commoditie_old) ? $commoditie_old : 'N/A' }}</td>
									<td style="width:250px" class="without_border"> {{ $totalnetdrywt_commoditie }} </td>
									{{--<td style="width:220px" class="without_border"> {{ $totalnet_commoditie }} </td>--}}
								</tr>
							</table>
						@endif
						<?php
	        				if( $commoditie_old === '' || ( $commoditie_old === $commoditie_current )){
								$totalnetdrywt_c = $totalnetdrywt_c + $group->totalnetdrywt;
								$totalnet_commoditie = number_format($totalnet_c , $decimals_in_tickets);
	        					$totalnetdrywt_commoditie = number_format($totalnetdrywt_c , $decimals_in_tickets);
							}else{
								$totalnetdrywt_c = $group->totalnetdrywt;
								$totalnet_commoditie = number_format($totalnet_c , $decimals_in_tickets);
	        					$totalnetdrywt_commoditie = number_format($totalnetdrywt_c , $decimals_in_tickets);
							}
						?>
						@if( ( $commoditie_old !== $commoditie_current) ||  $commoditie_old == ''  )
							<p>{{ $commoditie_current }}</p>
						@endif
						@if( $commoditie_current.$buyer_current !== $commoditie_old.$buyer_old )
								<p style="padding-left: 5%;">{{ $buyer_current }}</p>
						@endif
						<table id="" class="table table-striped table-bordered">
							<thead>
								<tr class="table_style">
									@foreach($fields as $campo)
										<th class="upper">{{ $campo }}</th>
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
	        							$netdrywt = number_format($row->netdrywt , $decimals_in_tickets);
									?>
									@if( ($row->commoditie_id == $group->commoditie_id) && ($row->buyer_id == $group->buyer_id) )
										<tr id="fieldRow" style="padding-top: -200px">
											<td style="width: 10%;text-align: center"> {{ $fecha }} </td>
											@if( $display_show_id == 0 )
												<td style="text-align: center"> {{ $row->source_id }} </td>
											@else
												<td style="text-align: center"> {{ $row->show_id }} </td>
											@endif
											<td style="width: 15%"> {{ $row->commoditie_name }} </td>
											<td style="width: 10%"> {{ $row->trucklicense }} </td>
											<td style="width: 15%"> {{ $row->location_name }} </td>
											<td style="width: 10%"> {{ $row->drivername }} </td>
											<td style="width: 10%;text-align: right"> {{ number_format($row->moisture, $decimals_in_tickets) }} </td>
											<td style="width: 10%;text-align: right"> {{ $net }} </td>
											<td style="width: 10%;text-align: right"> {{ $netdrywt }} </td>

										</tr>
									<?php
										$buyer_total_old = $row->buyer_id; //1
										//Variables para cabeceras
										$commoditie_old = $group->commoditie_name;
										$buyer_old = $group->buyer_name;
									?>
									@endif
									@if( ($row->commoditie_id == $group->commoditie_id) && ($row->buyer_id == $group->buyer_id) )
										<?php
											$totalnetdrywt_s = $totalnetdrywt_s + $row->netdrywt; 
	        								$totalnetdrywt_buyer = number_format($totalnetdrywt_s , $decimals_in_tickets);
										?>											
									@endif
								@endforeach
							</tbody>
						</table>
						<table class="without_border">
							<?php
								$count_iteration++;
							?>
							@if( $longitud == $count_iteration )
								<tr>
									<td style="width:650px" class="without_border"> Total {{ isset($group->buyer_name) ? $group->buyer_name : 'N/A' }}</td>
									<td style="width:250px" class="without_border"> {{ $totalnetdrywt_buyer }} </td>
								</tr>
							@endif
							@if( $longitud == $count_iteration )
								<tr>
									<td style="width:650px" class="without_border"> Total {{ isset($group->commoditie_name) ? $group->commoditie_name : 'N/A' }}</td>
									<td style="width:250px" class="without_border"> {{ $totalnetdrywt_commoditie }} </td>
								</tr>
							@endif
						</table>
						<?php
							$totalnetdrywt_s = 0;
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

