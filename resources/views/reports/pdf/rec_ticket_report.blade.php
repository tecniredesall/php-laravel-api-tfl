<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="application/vnd.sealed-xls; charset=utf-8"/>
    <title>Reporte General </title>
</head>
<body>
<script type="text/php">
  	 	//Add page number
    if ( isset($pdf) ) {
        $pdf->page_script('
            if ($PAGE_COUNT > 1) {
                $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                $size = 12;
                $pageText = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT;
                $y = 770;
                $x = 530;
                $pdf->text($x, $y, $pageText, $font, $size);
            }
        ');
    }
  </script>
<div class="content">
    <div class="panel panel-default">
        <div class="sty_header">
            <p style="text-align: center;"> {{ $coname }} </p>
            <p style="text-align: center;"> REPORT DATE </p>
            <p style="text-align: center;"> FROM: {{ $date_from }} TO {{ $date_to }} </p>
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
                            $totalnet = number_format($group->totalnet , 2, ',', ' ');
                            $totalnetdrywt = number_format($group->totalnetdrywt , 2, ',', ' ');

                            //Validación para mostrar totales Seller y Commodity
                            $longitud = sizeof($groupBy);
                            ?>

                            @if( ($commoditie_current.$seller_current !== $commoditie_old.$seller_old ) && ( $totalnetdrywt_seller !== 0 && $totalnet_seller !== 0 ) )
                                <table style="margin-top:0px" class="without_border">
                                    <tr>
                                        <td style="width: 580px;" class="without_border"> Total {{ isset($seller_old) ? $seller_old : 'N/A' }}</td>
                                        <td class="without_border"> {{ $totalnet_seller }} </td>
                                        <td class="without_border"> {{ $totalnetdrywt_seller }} </td>
                                    </tr>
                                </table>



                            @endif

                            @if( ( $commoditie_current !== $commoditie_old ) && ( $totalnetdrywt_commoditie !== 0 && $totalnet_commoditie !== 0 ) )

                                <table style="margin-top:0px" class="without_border">
                                    <tr>
                                        <td style="width:580px" class="without_border"> Total {{ isset($commoditie_old) ? $commoditie_old : 'N/A' }}</td>
                                        <td class="without_border"> {{ $totalnet_commoditie }} </td>
                                        <td class="without_border"> {{ $totalnetdrywt_commoditie }} </td>
                                    </tr>
                                </table>

                            @endif

                            <?php

                            if(  $commoditie_old === '' || ( $commoditie_old === $commoditie_current )){
                                //echo $group->totalnet;
                                $totalnet_c = $totalnet_c + $group->totalnet;
                                $totalnetdrywt_c = $totalnetdrywt_c + $group->totalnetdrywt;
                                //echo $totalnet_c;

                                $totalnet_commoditie = number_format($totalnet_c , 2, ',', ' ');
                                $totalnetdrywt_commoditie = number_format($totalnetdrywt_c , 2, ',', ' ');

                            }else{
                                $totalnet_c = $group->totalnet; //$totalnet_commoditie = 0;
                                $totalnetdrywt_c = $group->totalnetdrywt; //$totalnetdrywt_commoditie = 0;
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

                            <table id="" class="table table-striped table-bordered">
                                <thead>
                                <tr class="table_style">
                                    @foreach($fields as $campo)
                                        <th class="upper" style="width: 40px">{{ $campo }}</th>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($infoFormato as $clave => $field)
                                    <?php
                                    $date = new DateTime($field->date_end);
                                    $fecha = $date->format('d/m/y');

                                    $net = number_format($field->net , 2, ',', ' ');
                                    $netdrywt = number_format($field->netdrywt , 2, ',', ' ');

                                    ?>
                                    @if( ($field->commoditie_id == $group->commoditie_id) && ($field->seller_id == $group->seller_id) && ($field->farm_id == $group->farm_id))
                                        <tr id="fieldRow">
                                            <td> {{ $fecha }} </td>
                                            <td> {{ $field->source_id }} </td>
                                            <td> {{ $field->orgticket }} </td>
                                            <td> {{ $field->trailerlicense }} </td>
                                            <td> {{ $field->farm_name }} </td>
                                            <td> {{ $field->location_name }} </td>
                                            <td> {{ $field->moisture }} </td>
                                            <td> {{ $net }} </td>
                                            <td> {{ $netdrywt }} </td>
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

                                        $totalnet_seller = number_format($totalnet_s , 2, ',', ' ');
                                        $totalnetdrywt_seller = number_format($totalnetdrywt_s , 2, ',', ' ');

                                        ?>
                                    @endif

                                @endforeach


                                </tbody>
                            </table>

                            <table class="without_border">
                                <tr>
                                    <td style="width: 580px" class="without_border"> Total {{ isset($group->farm_name) ? $group->farm_name : 'N/A' }}</td>
                                    <td class="without_border"> {{ $totalnet }} </td>
                                    <td class="without_border"> {{ $totalnetdrywt }} </td>
                                </tr>
                                {{--@if( $totalnet_s !== 0 &&  $totalnetdrywt_s !== 0 && ( $commoditie_total_old === $commoditie_total_current ) && ( $seller_total_old === $seller_total_current) || ( $farm_total_old !== $group->farm_id) || ( $seller_total_current == ''))--}}
                                <?php
                                $count_iteration++;
                                ?>
                                @if( $longitud == $count_iteration )
                                    <tr>
                                        <td style="width: 580px" class="without_border"> Total {{ isset($group->seller_name) ? $group->seller_name : 'N/A' }}</td>
                                        <td class="without_border"> {{ $totalnet_seller }} </td>
                                        <td class="without_border"> {{ $totalnetdrywt_seller }} </td>
                                    </tr>

                                @endif
                                {{--@if( $totalnet_c !== 0 &&  $totalnetdrywt_c !== 0  && ( $commoditie_total_old === $group->commoditie_id ) )--}}
                                @if( $longitud == $count_iteration )
                                    <tr>
                                        <td style="width: 580px"  class="without_border"> Total {{ isset($group->commoditie_name) ? $group->commoditie_name : 'N/A' }}</td>
                                        <td class="without_border"> {{ $totalnet_commoditie }} </td>
                                        <td class="without_border"> {{ $totalnetdrywt_commoditie }} </td>
                                    </tr>
                                @endif

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
<style type="text/css">
    .sty_header{
        border-style: solid;
        border-color: #2a2a2a;
    }

    table, th, td {
        border: 1px solid black;
        margin-top: 30px;
    }
    table, th, td {
        font-size: 12px;
    }

    .without_border, tr {
        border: 0px solid black;
        margin-top: 10px;
    }

</style>