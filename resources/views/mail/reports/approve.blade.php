<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html"; charset=utf-8"/>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style type="text/css">
        html {
            font-family: 'Open Sans', sans-serif;
        }
        table, th, td {
            /*border: 1px solid black;*/
        }
        header {
            margin: 0;
            margin-top: -1px;
            margin-left: -1px;
            margin-right: -1px;
            height: 150px;
            background-image: url( "{{ env('APP_ENV') === 'local' ? asset('assets/bg-header.png') : secure_asset('assets/bg-header.png') }}" );
            background-repeat: no-repeat;
            background-attachment: scroll;
            background-size:100% 100%;
            color: #FFFFFF;
        }
        .responsive {
            width: 100%;
            height: auto;
        }
        footer{
            margin-top: -1px;
            margin-left: -1px;
            margin-right: -1px;
            height: 150px;
            background-color: #2e384d;
            text-align: center;
        }

    </style>
</head>
<body>
<article>
    <header>
        <table width="100%">
            <tr>
                <td rowspan="2" style="width: 90%;">
                    <img src="{{ env('APP_ENV') === 'local' ? asset('assets/logo-white.png') : secure_asset('assets/logo-white.png') }}" width="210" style="margin-top: 35px; margin-left:70px">
                </td>
                <td>
                    <div align="left">
                        <span style="font-size: 15.8px;">BATCH</span><br>
                        <span style="font-size: 21.4px;"><b>REPORT</b> </span>
                    </div>
                </td>
            </tr>

        </table>
    </header>
    {{--            <hr style="width: 150px; height: 3px; background-color: #005ea5; margin-top: -5.5px">--}}
    <div>
        <div class="row">
            <table style="width: 80%;">
                <tr>
                    <td rowspan="6">
                        <img src="{{ env('APP_ENV') === 'local' ? asset('assets/sheet.jpg') : secure_asset('assets/sheet.jpg') }}">
                    </td>
                </tr>
                <tr>
                    <td>
                        <span style="font-size: 18px;"> Hello <b style="color: #0068d1;text-decoration: underline;">{{ $email }}:</b></span>
                        <br><br>
                        <span style="font-size: 15px; margin-top: 20px">We're sending the Batch Report for review.
                            <br>
                                Contact your storage operator if updates are required.</span>
                        <br> <br> <br>
                        <img src="{{ env('APP_ENV') === 'local' ? asset('assets/logo.png') : secure_asset('assets/logo.png') }}" style="width:120px;margin-left: -17px">

                        <br>
                        <span style="color:#0068d1;font-style: italic; font-weight: bold; font-size: 18px"> I.T. Team</span>
                        <br>
                        <span style="color:#828894;font-style: italic; font-size: 16px">Developed by SiloSys</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</article>
<footer>
    <div style="padding-top: 30px">
        <p style="color: #FFFFFF;">www.silosys.ag</p>
        <p style="color: #7a7f89;">Buying · Selling · Receiving · Shipping</p>
        <p>
            <a href="www.google.com"><img class="responsive" src="{{ env('APP_ENV') === 'local' ? asset('assets/social/facebook.png') : secure_asset('assets/social/facebook.png') }}" style="margin-right: 35px; width: 15px; height: 15px;"></a>
            <a href="www.google.com"><img class="responsive" src="{{ env('APP_ENV') === 'local' ? asset('assets/social/twitter.png') : secure_asset('assets/social/twitter.png') }}" style="margin-right: 35px; width: 15px; height: 15px;"></a>
            <a href="www.google.com"><img class="responsive" src="{{ env('APP_ENV') === 'local' ? asset('assets/social/instagram.png') : secure_asset('assets/social/instagram.png') }}" style="margin-right: 35px; width: 15px; height: 15px;"></a>
            <a href="www.google.com"><img class="responsive" src="{{ env('APP_ENV') === 'local' ? asset('assets/social/linkedin.png') : secure_asset('assets/social/linkedin.png') }}" style="width: 15px; height: 15px;"></a>
        </p>
    </div>
</footer>
</body>
</html>
