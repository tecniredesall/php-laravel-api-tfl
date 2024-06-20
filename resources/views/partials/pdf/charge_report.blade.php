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
            <p style="text-align: center;"> CHARGE REPORT </p>
            <p style="text-align: center;"> REPORT DATE </p>
            <p style="text-align: center;"> FROM: {{ $date_from }}  </p>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="panel">
                    <div class="panel-body">
                        <table id="" class="table table-striped table-bordered">
                            <thead>
                            <tr class="table_style">
                                @foreach( $fields as $campo )
                                    <th class="upper">{{ $campo }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                                $grand_ele_t = 0; $grand_ele_total = 0;
                                $grand_dry_t = 0; $grand_dry_total = 0;
                            ?>
                            @foreach( $groupBy as $row )
                            <?php
                                $elevator_chr = number_format($row->elevator_chr, $decimals_in_tickets);
                                $drying_chr = number_format($row->drying_chr, $decimals_in_tickets);
                                $grand_ele_t = $grand_ele_t + $row->elevator_chr;
                                $grand_dry_t = $grand_dry_t + $row->drying_chr;
                                $grand_ele_total = number_format($grand_ele_t, 2);
                                $grand_dry_total = number_format($grand_dry_t, 2);
                            ?>
                                <tr id="fieldRow">
                                    <td> {{ $row->seller_name }} </td>
                                    <td style="text-align: right"> TOTALS: $ {{ $elevator_chr }} </td>
                                    <td style="text-align: right"> $ {{ $drying_chr }} </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td> </td>
                                    <td> $ {{ $grand_ele_total }} </td>
                                    <td> $ {{ $grand_dry_total }} </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
