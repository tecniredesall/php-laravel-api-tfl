<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="application/vnd.sealed-xls; charset=utf-8"/>
</head>
<body>
<div class="content">
	<div class="panel panel-default">
		<div class="sty_header">
			<p> {{ $coname }} </p>
			<p> CASH TICKET REPORT </p>
			<p> FROM: {{ $date_from }}</p>
		</div>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="panel">
					<div class="panel-body">
					<?php
					    //Inicializacion de variables para header names y grand total.
						$commoditie_old = '';	$grand_total = 0; $grand_t = 0;
					?>
					@foreach( $groupBy as $key => $group )
						<?php
							$commoditie_current = $group->commoditie_name;
						?>
						@if( ( $commoditie_old !== $commoditie_current) ||  $commoditie_old == ''  )
							<h5>{{ $commoditie_current }}</h5>
						@endif
						<table style="border:1px solid #000000;">
							<thead>
								<tr style="border:1px solid #000000; background-color: #ebedef;">
									@foreach($fields as $campo)
										<th style="border:1px solid #000000;">{{ $campo }}</th>
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
										<tr>
											<td style="border:1px solid #000000;"> {{ $fecha }} </td>
											<td style="border:1px solid #000000;"> {{ $row->source_id }} </td>
											<td style="border:1px solid #000000;"> {{ $row->commoditie_name }} </td>
											<td style="border:1px solid #000000;"> {{ $row->tank_name }} </td>
											<td style="border:1px solid #000000;"> {{ $row->buyer }} </td>
											<td style="text-align: right"> {{ number_format($row->price, $decimals_in_tickets) }} </td>
											<td style="border:1px solid #000000;"> {{ $weight }} </td>
											<td style="border:1px solid #000000;"> ${{ $total }} </td>
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
									<td style="border:1px solid #000000;"> {{ isset($group->commoditie_name) ? $group->commoditie_name : 'N/A' }} TOTALS:</td>
									<td style="border:1px solid #000000;"> {{ $group_weight }} </td>
									<td style="border:1px solid #000000;"> {{ $group_total }} </td>
								</tr>
							</tfoot>
						</table>
						<?php
							$grand_total = $grand_total + $group->total;
							$grand_t = number_format($grand_total , $decimals_in_tickets);
						?>
					@endforeach
					<table style="border:1px solid #000000;">
						<tr>
							<th style="border:1px solid #000000;">GRAND TOTAL: </th>
							<th style="border:1px solid #000000;">{{ $grand_t }}</th>
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

