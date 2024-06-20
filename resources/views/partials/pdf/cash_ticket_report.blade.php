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
			<p style="text-align: center;"> CASH TICKET REPORT </p>
			<p style="text-align: center;"> FROM: {{ $date_from }} </p>
		</div>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="panel">
					<div class="panel-body">
					<?php
					    //Inicializacion de variables para header names y grand total.
						$commoditie_old = '';
						$grand_total = 0; $grand_t = 0;
					?>
					@foreach( $groupBy as $key => $group )
						<?php
							$commoditie_current = $group->commoditie_name;
						?>
						@if( ( $commoditie_old !== $commoditie_current) ||  $commoditie_old == ''  )
							<p>{{ $commoditie_current }}</p>
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
										if($lang == "en"){
											$date = new DateTime($row->selled_at);
											$fecha = $date->format('m/d/y');
										}else{
											$date = new DateTime($row->selled_at);
											$fecha = $date->format('d/m/y');
										}
										$weight = number_format($row->weight , $decimals_in_tickets);
										$total = number_format($row->total , $decimals_in_tickets);
									?>
									@if( ($row->commoditie_id == $group->commoditie_id))
										<tr id="fieldRow" style="padding-top: -200px">
											<td style="text-align: center"> {{ $fecha }} </td>
											<td style="text-align: center"> {{ $row->source_id }} </td>
											<td> {{ $row->commoditie_name }} </td>
											<td> {{ $row->tank_name }} </td>
											<td> {{ $row->buyer }} </td>
											<td style="text-align: right"> {{ number_format($row->price, $decimals_in_tickets) }} </td>
											<td style="text-align: right"> {{ $weight }} </td>
											<td style="text-align: right"> ${{ $total }} </td>
										</tr>
									<?php
										//Variable para cabecera
										$commoditie_old = $group->commoditie_name;
									?>
									@endif
								@endforeach
							</tbody>
							<?php
								$group_weight = number_format($group->weight , $decimals_in_tickets);
								$group_total = number_format($group->total , $decimals_in_tickets);
							?>
							<tfoot>
								<tr>
									<td colspan="6" class="without_border"> {{ isset($group->commoditie_name) ? $group->commoditie_name : 'N/A' }} TOTALS:</td>
									<td class="without_border"> {{ $group_weight }} </td>
									<td class="without_border"> {{ $group_total }} </td>
								</tr>
							</tfoot>
						</table>						
						<?php
							$grand_total = $grand_total + $group->total;
							$grand_t = number_format($grand_total , $decimals_in_tickets);
						?>
					@endforeach
					<table>
						<tr>
							<th>GRAND TOTAL: </th>
							<th>{{ $grand_t }}</th>
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

