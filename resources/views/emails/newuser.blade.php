<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8"> <!-- utf-8 works for most cases -->
    <meta name="viewport" content="width=device-width"> <!-- Forcing initial-scale shouldn't be necessary -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- Use the latest (edge) version of IE rendering engine -->
    <title>Silosys</title> <!-- The title tag shows in email notifications, like Android 4.4. -->

    <!-- Web Font / @font-face : BEGIN -->
    <!-- NOTE: If web fonts are not required, lines 9 - 26 can be safely removed. -->

    <!-- Desktop Outlook chokes on web font references and defaults to Times New Roman, so we force a safe fallback font. -->
    <!--[if mso]>
    <style>
        * {
            font-family: "Arial", sans-serif !important;
        }
    </style>
    <![endif]-->

    <!-- All other clients get the webfont reference; some will render the font and others will silently fail to the fallbacks. More on that here: http://stylecampaign.com/blog/2015/02/webfont-support-in-email/ -->
    <!--[if !mso]><!-->
    <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800&display=swap" rel="stylesheet">
    <!--<![endif]-->

    <!-- Web Font / @font-face : END -->

    <!-- CSS Reset -->
    <style>

        /* What it does: Remove spaces around the email design added by some email clients. */
        /* Beware: It can remove the padding / margin and add a background color to the compose a reply window. */
        html,
        body {
            margin: 0 auto !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
        }

        body, table, td, a {
            font-family: 'Open Sans', sans-serif;
            font-size: 14px;
        }

        /* What it does: Stops email clients resizing small text. */
        * {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        /* What is does: Centers email on Android 4.4 */
        div[style*="margin: 16px 0"] {
            margin: 0 !important;
        }

        /* What it does: Stops Outlook from adding extra spacing to tables. */
        table,
        td {
            mso-table-lspace: 0pt !important;
            mso-table-rspace: 0pt !important;
        }

        /* What it does: Fixes webkit padding issue. Fix for Yahoo mail table alignment bug. Applies table-layout to the first 2 tables then removes for anything nested deeper. */
        table {
            border-spacing: 0 !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            margin: 0 auto !important;
        }

        table table table {
            table-layout: auto;
        }

        /* What it does: Uses a better rendering method when resizing images in IE. */
        img {
            -ms-interpolation-mode: bicubic;
        }

        /* What it does: A work-around for iOS meddling in triggered links. */
        .mobile-link--footer a,
        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: underline !important;
        }

    </style>

    <!-- Progressive Enhancements -->
    <style>

        /*colores fondos*/
        .bg-body {
            background-color: #F8F8F8 !important;
        }

        .bg-color-1 {
            background-color: #ffffff;
        }

        .bg-color-2 {
            background-color: #f4f4f4;
        }

        .bg-color-3 {
            background-color: #363636;
        }

        /*titulos, textos*/
        h1 {
            font-size: 20px !important;
        }

        h2 {
            font-size: 18px !important;
        }

        h3 {
            font-size: 16px !important;
        }

        h4 {
            font-size: 14px !important;
        }

        p {
            font-size: 14px !important;
            line-height: 22px;
            mso-height-rule: exactly;
        }

        hr {
            border: 1px #000000 solid;
        }

        .pstrong {
            font-size: 18px !important;
            font-weight: 700;
            color: #6C7282;
        }

        .spanbody {
            color: #6C7282;
            line-height: 1.7;
        }

        .text-color-1 {
            color: #616677 !important;
        }

        .text-color-2 {
            color: #838383 !important;
        }

        .text-color-3 {
            color: #616677 !important;
        }

        .text-r {
            text-align: right !important;
        }

        .text-l {
            text-align: left !important;
        }

        .text-j {
            text-align: justify !important;
        }

        .text-c {
            text-align: center !important;
        }

        .bg-img-1 {
            text-align: center;
            background-position: center center !important;
            background-size: cover !important;
        }

        /*links*/
        .link-1 {
            color: #ffffff !important;
        }

        .link-1:hover {
            color: #bcbcbc !important;
            text-decoration: none;
        }

        .link-2 a {
            color: #999999 !important;
        }

        .link-2:hover {
            color: #666be3 !important;
        }

        /*botones*/
        /* What it does: Hover styles for buttons */
        .button-td,
        .button-a {
            transition: all 100ms ease-in;
        }

        .button-td {
            border-radius: 23px;
            background: #222222;
            text-align: center;
            transition: all 100ms ease-in;
        }

        .button-a {
            background: #222222;
            border: 15px solid #222222;
            font-family: sans-serif;
            font-size: 13px;
            line-height: 1.1;
            text-align: center;
            text-decoration: none;
            display: block;
            border-radius: 23px;
            font-weight: bold;
        }

        .button-td:hover,
        .button-a:hover {
            background: #555555 !important;
            border-color: #555555 !important;
        }

        .inner-padding {
            padding: 30px;
        }

        .footer-links {
            padding: 40px 10px;
            width: 100%;
            font-size: 12px;
            font-family: sans-serif;
            mso-height-rule: exactly;
            line-height: 18px;
            text-align: center;
            color: #888888;
        }

        /* Media Queries */
        @media screen and (max-width: 600px) {

            .email-container {
                width: 100% !important;
                margin: auto !important;
            }

            /* What it does: Forces elements to resize to the full width of their container. Useful for resizing images beyond their max-width. */
            .fluid,
            .fluid-centered {
                max-width: 100% !important;
                height: auto !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }

            /* And center justify these ones. */
            .fluid-centered {
                margin-left: auto !important;
                margin-right: auto !important;
            }

            /* What it does: Forces table cells into full-width rows. */
            .stack-column,
            .stack-column-center {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                direction: ltr !important;
            }

            /* And center justify these ones. */
            .stack-column-center {
                text-align: center !important;
            }

            /* What it does: Generic utility class for centering. Useful for images, buttons, and nested tables. */
            .center-on-narrow {
                text-align: center !important;
                display: block !important;
                margin-left: auto !important;
                margin-right: auto !important;
                float: none !important;
            }

            table.center-on-narrow {
                display: inline-block !important;
            }

        }

    </style>

</head>
<body width="100%" style="margin: 0;" class="bg-body">
<center style="width: 100%;" class="bg-body">

    <!-- Visually Hidden Preheader Text : BEGIN -->
    <div style="display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;font-family: sans-serif;">
        {!! $previous !!}
    </div>
    <!-- Visually Hidden Preheader Text : END -->

    <!-- Email Header : BEGIN -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="600"
           style="margin: auto;" class="email-container">
        <tr>
            <td style="padding:20px 0px 0px 0px;text-align:center;background-color: #FFFFFF;">
                <img src="{{$logo}}" width="600" alt="alt_text" border="0">
            </td>
        </tr>
    </table>
    <!-- Email Header : END -->

    <!-- Email Body : BEGIN -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="600"
           style="margin: auto;" class="email-container">


        <!-- 2 Even Columns : BEGIN -->
        <tr>
            <td bgcolor="#ffffff" align="center" valign="top" style="padding: 10px;">
                <hr style="border-top: 1px solid #dbdbdb;border-bottom: none;border-right: none;border-left: none;" />
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <!-- Column : BEGIN -->
                        <td class="stack-column-center" valign="top">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">

                                <tr>
                                    <td style="padding: 0 10px 50px; text-align: left;" class="center-on-narrow">
                                        {!! $body !!}
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <!-- Column : END -->
                    </tr>
                </table>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0">

                    <tr>
                        <td style="padding: 0 10px 10px; text-align: left;" class="center-on-narrow">
                            <hr style="border-top: 1px solid #dbdbdb;border-bottom: none;border-right: none;border-left: none;">
                            {!! $footer !!}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>


        <!-- Clear Spacer : BEGIN -->
        <tr>
            <td height="10" style="font-size: 0; line-height: 0;">
                &nbsp;
            </td>
        </tr>
        <!-- Clear Spacer : END -->

        <!-- 1 Column Text + Button : BEGIN -->
        <tr>
            <td bgcolor="#F8F8F8" align="center" valign="top" style="padding: 0px 10px 10px 10px; font-size: 11px !important;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <!-- Column : BEGIN -->
                        <td class="stack-column-center" valign="top">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%">

                                <tr>
                                    <td style="text-align: left;" class="center-on-narrow">
                                        <p style="color: #616677;font-weight: 600; font-size: 12px!important;">
                                            {!! $privacy !!}
                                        </p>
                                    </td>

                                </tr>
                            </table>
                        </td>
                        <!-- Column : END -->
                        <!-- Column : BEGIN -->
                        <td class="stack-column-center" valign="top">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%">
                                <tr>
                                    <td style="text-align: right;" class="center-on-narrow">
                                        <p style="color: #616677;font-weight: 600; font-size: 12px!important;">
                                            {{ $copyright }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                        <!-- Column : END -->
                    </tr>
                </table>
            </td>
        </tr>
        <!-- 1 Column Text + Button : BEGIN -->

    </table>
    <!-- Email Body : END -->
</center>
</body>
</html>
