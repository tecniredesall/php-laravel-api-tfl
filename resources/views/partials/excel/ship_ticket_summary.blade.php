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
			<p> SHIPPING TICKETS SUMMARY</p>
			<p> FROM: {{ $date_from }} </p>
		</div>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="panel">
					<div class="panel-body">
						<?php
						//Inicializacion de variables para header names y variables para totales.
						$commoditie_old = '';	$totalnet_c = 0; $totalnet_commoditie = 0;
						$commoditie_total_old = '';	$grand_t = 0;	$grand_total = 0;
						?>
						@foreach( $groupBy as $key => $group )
							<?php
								//Headers names
								$commoditie_current = $group->commoditie_name;
								//Totales para cada Commodity
								$totalnet = number_format($group->net , $decimals_in_tickets);
								//Validación para mostrar totales Seller y Commodity
								$longitud = sizeof($groupBy);
							?>
							<?php
							if( $commoditie_old === '' || ( $commoditie_old === $commoditie_current )){
								$totalnet_c = $totalnet_c + $group->net;
								$totalnet_commoditie = number_format($totalnet_c , $decimals_in_tickets);
							}else{
								$totalnet_c = $group->net;
								$totalnet_commoditie = number_format($totalnet_c , $decimals_in_tickets);
							}
							?>
							@if( ( $commoditie_old !== $commoditie_current) ||  $commoditie_old == ''  )
								<p>{{ $commoditie_current }}</p>
							@endif
							<table id="" class="table table-striped table-bordered">
								<thead>
								<tr class="table_style">
									@foreach($fields as $campo)
										<th class="upper" style="width:35">{{ $campo }}</th>
									@endforeach
								</tr>
								</thead>
								<tbody>
								@foreach($infoFormato as $clave => $row)
									<?php
										$net = number_format($row->net , $decimals_in_tickets);
									?>
									@if( ($row->commoditie_id == $group->commoditie_id))
										<tr id="fieldRow" style="padding-top: -200px">
											<td style=""> {{ $row->buyer_name }} </td>
											<td style="text-align: right"> {{ $net }} </td>
										</tr>
										<?php
										$commoditie_total_old = $row->commoditie_id;
										//Variables para cabeceras
										$commoditie_old = $group->commoditie_name;
										?>
									@endif
								@endforeach
								</tbody>
								<?php
									$group_net = number_format($group->net , $decimals_in_tickets);
								?>
								<tfoot>
								<tr>
									<td class="without_border"> {{ isset($group->commoditie_name) ? $group->commoditie_name : 'N/A' }} TOTALS:</td>
									<td class="without_border" style="text-align: right"> {{ $group_net }} </td>
								</tr>
								</tfoot>
							</table>
							<?php
							$grand_total = $grand_total + $group->net;
							$grand_t = number_format($grand_total , $decimals_in_tickets);
							?>
						@endforeach
						<table>
							<tr>
								<th style=" text-align: center">GRAND TOTAL: </th>
								<th style=" text-align: right">{{ $grand_t }}	</th>
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

