<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        body {
            background-color: #f6f6f6;
            font-family: sans-serif;
            -webkit-font-smoothing: antialiased;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
        .body {
            background-color: #f6f6f6;
            width: 100%;
        }
        .container {
            display: block;
            margin: 0 auto !important;
            max-width: 580px;
            padding: 10px;
            width: 580px;
        }
        .content {
            box-sizing: border-box;
            display: block;
            margin: 0 auto;
            max-width: 580px;
            padding: 10px;
        }
        .main {
            background: #ffffff;
            border-radius: 3px;
            width: 100%;
        }
        .wrapper {
            box-sizing: border-box;
            padding: 20px;
        }
        .footer {
            clear: both;
            margin-top: 10px;
            text-align: center;
            width: 100%;
        }
        .footer td,
        .footer p,
        .footer span,
        .footer a {
            color: #999999;
            font-size: 12px;
            text-align: center;
        }
        h1, h2, h3, h4 {
            color: #000000;
            font-family: sans-serif;
            font-weight: 400;
            line-height: 1.4;
            margin: 0;
            margin-bottom: 30px;
        }
        h1 { font-size: 24px; font-weight: 300; text-align: center; text-transform: capitalize; }
        p, ul, ol {
            font-family: sans-serif;
            font-size: 14px;
            font-weight: normal;
            margin: 0;
            margin-bottom: 15px;
        }
        .credentials-box {
            background-color: #f8f9fa;
            border: 1px dashed #ced4da;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .credentials-box p {
            margin: 5px 0;
            font-family: monospace;
            font-size: 16px;
        }
        .label {
            font-weight: bold;
            color: #6c757d;
        }
        .value {
            color: #212529;
            font-weight: bold;
        }
        .btn {
            box-sizing: border-box;
            width: 100%;
        }
        .btn > tbody > tr > td {
            padding-bottom: 15px;
        }
        .btn table {
            width: auto;
        }
        .btn table td {
            background-color: #ffffff;
            border-radius: 5px;
            text-align: center;
        }
        .btn a {
            background-color: #ffffff;
            border: solid 1px #3b82f6;
            border-radius: 5px;
            box-sizing: border-box;
            color: #3b82f6;
            cursor: pointer;
            display: inline-block;
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            padding: 12px 25px;
            text-decoration: none;
            text-transform: capitalize;
        }
        .btn-primary table td {
            background-color: #3b82f6;
        }
        .btn-primary a {
            background-color: #3b82f6;
            border-color: #3b82f6;
            color: #ffffff;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
        <tr>
            <td>&nbsp;</td>
            <td class="container">
                <div class="content">

                    <!-- START CENTERED WHITE CONTAINER -->
                    <table role="presentation" class="main">

                        <!-- START MAIN CONTENT AREA -->
                        <tr>
                            <td class="wrapper">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <div class="logo-container">
                                                <h1>Asset Monitoring</h1>
                                            </div>
                                            <p>Hi {{ $user->name }},</p>
                                            <p>An account has been created for you in the Asset Monitoring System.</p>
                                            <p>Here are your login credentials:</p>
                                            
                                            <div class="credentials-box">
                                                <p><span class="label">Email:</span> <span class="value">{{ $user->email }}</span></p>
                                                <p><span class="label">Password:</span> <span class="value">{{ $password }}</span></p>
                                            </div>
                                            
                                            <p>For security, please change your password immediately after logging in.</p>
                                            
                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                <tbody>
                                                    <tr>
                                                        <td align="center">
                                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                                <tbody>
                                                                    <tr>
                                                                        <td> <a href="{{ route('login') }}" target="_blank">Login Now</a> </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <p>If you have any questions, please contact the IT Administrator.</p>
                                            <p>Best Regards,<br>Asset Monitoring Team</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- END MAIN CONTENT AREA -->
                    </table>
                    <!-- END CENTERED WHITE CONTAINER -->

                    <!-- START FOOTER -->
                    <div class="footer">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="content-block">
                                    <span class="apple-link">PT. Angkasa Pura II (Persero) - Asset Monitoring Division</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="content-block powered-by">
                                    &copy; {{ date('Y') }} Asset Monitoring System
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- END FOOTER -->

                </div>
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
</body>
</html>
