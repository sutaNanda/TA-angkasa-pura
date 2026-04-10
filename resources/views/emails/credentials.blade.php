<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>New Account Credentials - AviaTrack</title>
    <style>
        /* Base Reset */
        body {
            background-color: #f3f4f6;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
            color: #334155;
        }
        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            width: 100%;
        }
        table td {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            vertical-align: top;
        }

        /* Container Structure */
        .body {
            background-color: #f3f4f6;
            width: 100%;
        }
        .container {
            display: block;
            margin: 0 auto !important;
            max-width: 600px;
            padding: 20px;
            width: 600px;
        }
        .content {
            box-sizing: border-box;
            display: block;
            margin: 0 auto;
            max-width: 600px;
            padding: 10px;
        }

        /* Main Box */
        .main {
            background: #ffffff;
            border-radius: 8px;
            width: 100%;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        
        /* Header Section */
        .header-top {
            background-color: #1e3a8a; /* Corporate Blue */
            padding: 24px;
            text-align: center;
        }
        .header-top h1 {
            color: #ffffff;
            font-size: 20px;
            font-weight: 600;
            margin: 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header-top p {
            color: #93c5fd;
            font-size: 12px;
            margin: 5px 0 0 0;
            font-weight: 400;
        }

        /* Content Wrapper */
        .wrapper {
            box-sizing: border-box;
            padding: 30px;
        }
        .wrapper p {
            font-size: 14px;
            font-weight: normal;
            margin: 0 0 15px;
            color: #475569;
        }

        /* Greeting */
        .greeting {
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 20px !important;
        }

        /* Credentials Box */
        .credentials-box {
            background-color: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 6px 6px 0;
        }
        .credentials-box table {
            width: 100%;
        }
        .credentials-box td {
            padding: 5px 0;
            font-size: 14px;
        }
        .label {
            font-weight: 600;
            color: #64748b;
            width: 120px;
        }
        .value {
            color: #0f172a;
            font-family: monospace;
            font-size: 15px;
            font-weight: bold;
            background-color: #e2e8f0;
            padding: 3px 8px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }

        /* Call to Action Button */
        .btn-container {
            width: 100%;
            text-align: center;
            margin: 35px 0 25px;
            border: 1px solid #2563eb;
            border-radius: 6px;
        }

        .btn {
            display: inline-block;
            font-size: 15px;
            font-weight: bold;
            padding: 12px 30px;
            text-decoration: none;
            text-transform: capitalize;
        }

        /* Warning Text */
        .warning-text {
            font-size: 12px !important;
            color: #dc2626 !important;
            font-style: italic;
            text-align: center;
            background-color: #fef2f2;
            padding: 10px;
            border-radius: 4px;
        }

        /* Signature */
        .signature {
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
        .signature p {
            margin-bottom: 5px;
            font-size: 13px;
        }
        .signature strong {
            color: #1e293b;
        }

        /* Footer */
        .footer {
            clear: both;
            margin-top: 20px;
            text-align: center;
            width: 100%;
        }
        .footer td, .footer p {
            color: #94a3b8;
            font-size: 11px;
            text-align: center;
            margin-bottom: 5px;
        }
        .footer a {
            color: #64748b;
            text-decoration: underline;
        }

        /* Responsive */
        @media only screen and (max-width: 620px) {
            table.body h1 {
                font-size: 20px !important;
            }
            table.body .wrapper, table.body .article {
                padding: 20px !important;
            }
            table.body .content {
                padding: 0 !important;
            }
            table.body .container {
                padding: 15px !important;
                width: 100% !important;
            }
            table.body .main {
                border-left-width: 0 !important;
                border-radius: 0 !important;
                border-right-width: 0 !important;
            }
        }
    </style>
</head>
<body>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
        <tr>
            <td>&nbsp;</td>
            <td class="container">
                <div class="content">

                    <table role="presentation" class="main">
                        <tr>
                            <td class="header-top">
                                <h1>AVIATRACK</h1>
                                <p>Asset & Operational M/E Management System</p>
                            </td>
                        </tr>

                        <tr>
                            <td class="wrapper">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <p class="greeting">Dear {{ $user->name }},</p>
                                            <p>An account has been successfully created for you in the <strong>AviaTrack</strong>. You can now access the platform to view and manage your operational tasks.</p>
                                            
                                            <p>Below are the credentials you will need to log in to the system:</p>
                                            
                                            <div class="credentials-box">
                                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td class="label">Access Role</td>
                                                        <td>: <span style="text-transform: uppercase; font-weight:bold; color: #3b82f6;">{{ $user->role ?? 'User' }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">Login Email</td>
                                                        <td>: <strong>{{ $user->email }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">Temporary Password</td>
                                                        <td>: <span class="value">{{ $password }}</span></td>
                                                    </tr>
                                                </table>
                                            </div>
                                            
                                            <p class="warning-text">
                                                ⚠️ <strong>IMPORTANT:</strong> For security purposes, please change your password in the "My Profile" section immediately after your first successful login.
                                            </p>
                                            
                                            <div class="btn-container">
                                                <a href="{{ route('login') }}" target="_blank" class="btn">Log In to Your Account</a>
                                            </div>
                                            
                                            <div class="signature">
                                                <p>If you did not request this account or if you encounter any issues logging in, please contact our IT Administrator immediately.</p>
                                                <br>
                                                <p>Best regards,</p>
                                                <p><strong>System Administration Team</strong></p>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div class="footer">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <p>This is an automated message generated by the system. Please do not reply to this email.</p>
                                    <p>&copy; {{ date('Y') }} AviaTrack. All rights reserved.</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    </div>
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
</body>
</html>
