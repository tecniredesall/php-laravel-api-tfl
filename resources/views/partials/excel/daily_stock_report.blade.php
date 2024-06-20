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
                    <p style="text-align: center;"> {{ $coname }} </p>
                    <p style="text-align: center;"> DAILY STOCK REPORT </p>
                    <p style="text-align: center;"> FROM: {{ $date_from }} </p>
        		</div>
        	</div>
            <table style="border:0px;">
                <body>
                <tr>
                    <td style="border:0px; width: 70%">
                        @if( !is_null($commodities))
                            {{ \App\Commodities::where('id',  strval(collect($commodities)->pluck('value')[0]))->pluck('name')[0]}}
                        @else
                            N/A
                        @endif
                    </td>
                    <td style="border:0px; width: 15%"> In bushels: {{ ($bushel == 0) ? 'No' : 'Yes' }} </td>
                    <td style="border:0px; width: 15%"> By CWT: {{ ($cwt == 0) ? 'No' : 'Yes' }} </td>
                </tr>
                </body>
            </table>
        	<div class="panel-body">
        		<div class="row">
        			<div class="col-lg-12 col-md-12 col-sm-12">
        				<div class="panel">
        					<div class="panel-body">
        						<table style="border:1px solid #000000;">
                                    <thead>
                                    <tr style="border:1px solid #000000; background-color: #ebedef;">
                                        @foreach( $fields as $campo )
                                            <th style="border:1px solid #000000;">{{ $campo }}</th>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach( $infoFormato as $field )
                                        <?php
                                        if($lang == "en"){
                                            $date = new DateTime($field->StockDate);
                                            $fecha = $date->format('m/d/y');
                                        }else{
                                            $date = new DateTime($field->StockDate);
                                            $fecha = $date->format('d/m/y');
                                        }
                                        ?>
                                        <tr>
                                            <td style="border:1px solid #000000;"> {{ $fecha }} </td>
                                            <td style="border:1px solid #000000;"> {{ $field->StartStock }} </td>
                                            <td style="border:1px solid #000000;"> {{ number_format($field->Receive, 2) }} </td>
                                            <td style="border:1px solid #000000;"> {{ number_format($field->Shipping, 2) }} </td>
                                            <td style="border:1px solid #000000;"> {{ number_format($field->EndStock, 2) }} </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
        						</table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
