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
			<p style="text-align: center;"> RECEIVE TICKETS SUMMARY </p>
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
						$commoditie_old = ''; $seller_old = '';
						//Inicializacion de variables para totales. 
						$totalnet_c = 0; $totalnet_commoditie = 0;
						$totalnetdrywt_c = 0; $totalnetdrywt_commoditie = 0;
						$totalnet_s = 0; $totalnetdrywt_s = 0; 
						$totalnet_seller = 0; $totalnetdrywt_seller = 0;
						$count_iteration = 0;
					?>
					@foreach( $groupBy as $key => $group )
						<?php
							//Headers names
							$commoditie_current = $group->commoditie_name;
							$seller_current = $group->seller_name;
							//Totales para cada Commodity
							$totalnet = number_format($group->totalnet , $decimals_in_tickets);
							$totalnetdrywt = number_format($group->totalnetdrywt , $decimals_in_tickets);
							//Validación para mostrar totales Seller y Commodity
							$longitud = sizeof($groupBy);
						?>
						@if( ($commoditie_current.$seller_current !== $commoditie_old.$seller_old ) && ( $totalnetdrywt_seller !== 0 && $totalnet_seller !== 0 ) )			
							<table style="margin-top:0px" class="without_border">
								<tr>
									<td style="width: 220px;" class="without_border"> Total {{ isset($seller_old) ? $seller_old : 'N/A' }}</td>
									<td style="width: 220px" class="without_border"> {{ $totalnetdrywt_seller }} </td>
									<td style="width: 220px" class="without_border"> {{ $totalnet_seller }} </td>
								</tr>
							</table>			
						@endif
						@if( ( $commoditie_current !== $commoditie_old ) && ( $totalnetdrywt_commoditie !== 0 && $totalnet_commoditie !== 0 ) )			
							<table style="margin-top:0px" class="without_border">							
								<tr>
									<td style="width:220px" class="without_border"> Total {{ isset($commoditie_old) ? $commoditie_old : 'N/A' }}</td>
									<td style="width:220px" class="without_border"> {{ $totalnetdrywt_commoditie }} </td>
									<td style="width:220px" class="without_border"> {{ $totalnet_commoditie }} </td>
								</tr>
							</table>
						@endif
						<?php
	        				if( $commoditie_old === '' || ( $commoditie_old === $commoditie_current )){
		        				$totalnet_c = $totalnet_c + $group->totalnet;
								$totalnetdrywt_c = $totalnetdrywt_c + $group->totalnetdrywt;
								$totalnet_commoditie = number_format($totalnet_c , $decimals_in_tickets);
	        					$totalnetdrywt_commoditie = number_format($totalnetdrywt_c , $decimals_in_tickets);
							}else{
								$totalnet_c = $group->totalnet; 
								$totalnetdrywt_c = $group->totalnetdrywt;
								$totalnet_commoditie = number_format($totalnet_c , $decimals_in_tickets);
	        					$totalnetdrywt_commoditie = number_format($totalnetdrywt_c , $decimals_in_tickets);
							}
						?>
						@if( ( $commoditie_old !== $commoditie_current) ||  $commoditie_old == ''  )
							<p>{{ $commoditie_current }}</p>
						@endif
						@if( $commoditie_current.$seller_current !== $commoditie_old.$seller_old )
							<p style="padding-left: 5%;">{{ $seller_current }}</p>
						@endif
						<table id="" class="table table-striped table-bordered">
							<thead>
								<tr class="table_style">
									@foreach($fields as $campo)
										<th class="upper" style="width: 40px">{{ $campo }}</th>
									@endforeach
								</tr>
							</thead>
							<tbody>
								@foreach($infoFormato as $clave => $row)
									<?php
										$net = number_format($row->totalnet , $decimals_in_tickets);
	        							$netdrywt = number_format($row->totalnetdrywt , $decimals_in_tickets);
									?>
									@if( ($row->commoditie_id == $group->commoditie_id) && ($row->seller_id == $group->seller_id) )
										<tr id="fieldRow" style="padding-top: -200px">
											<td style="width: 200px"> {{ $row->farm_name }} </td>
											<td style="width: 200px; text-align: right"> {{ $netdrywt }} </td>
											<td style="width: 200px; text-align: right"> {{ $net }} </td>
										</tr>
									<?php
										//Variables para cabeceras
										$commoditie_old = $group->commoditie_name;
										$seller_old = $group->seller_name;
									?>
									@endif
									@if( ($row->commoditie_id == $group->commoditie_id) && ($row->seller_id == $group->seller_id) )
										<?php
											$totalnet_s = $totalnet_s + $row->totalnet; 
											$totalnetdrywt_s = $totalnetdrywt_s + $row->totalnetdrywt;
											$totalnet_seller = number_format($totalnet_s , $decimals_in_tickets);
	        								$totalnetdrywt_seller = number_format($totalnetdrywt_s , $decimals_in_tickets);
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
									<td style="width:220px" class="without_border"> Total {{ isset($group->seller_name) ? $group->seller_name : 'N/A' }}</td>
									<td style="width:220px" class="without_border"> {{ $totalnetdrywt_seller }} </td>
									<td style="width:220px" class="without_border"> {{ $totalnet_seller }} </td>
								</tr>
							@endif
							@if( $longitud == $count_iteration )
								<tr>
									<td style="width:220px" class="without_border"> Total {{ isset($group->commoditie_name) ? $group->commoditie_name : 'N/A' }}</td>
									<td style="width:220px" class="without_border"> {{ $totalnetdrywt_commoditie }} </td>
									<td style="width:220px" class="without_border"> {{ $totalnet_commoditie }} </td>
								</tr>
							@endif
						</table>
						<?php
							$totalnet_s = 0; 
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

