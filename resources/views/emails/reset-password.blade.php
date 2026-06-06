<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f5f7;
            font-family: Arial, Helvetica, sans-serif;
        }
        @media screen and (max-width: 600px) {
            .email-body { padding: 30px 20px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f5f7; font-family: Arial, Helvetica, sans-serif;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f5f7; padding: 40px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="width: 100%; max-width: 600px;">
                    <tr>
                        <td align="center" style="padding-bottom: 30px;">
                            <img src="{{ $message->embed(public_path('logo.svg')) }}" alt="System Logo" style="max-height: 40px; display: block; border: 0;">
                        </td>
                    </tr>
                    <tr>
                        <td class="email-body" align="center" style="background-color: #ffffff; padding: 50px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <h2 style="color: #4a4a4a; font-size: 24px; font-weight: 500; margin: 0 0 25px 0;">Password Reset</h2>
                            <div style="max-width: 450px; margin: 0 auto; text-align: left;">
                                <p style="color: #4a4a4a; font-size: 15px; line-height: 1.6; margin: 0 0 15px 0;">
                                    Hello <strong>{{ $user->name }}</strong>,
                                </p>
                                <p style="color: #4a4a4a; font-size: 15px; line-height: 1.6; margin: 0 0 25px 0;">
                                    We received a request to reset the password for your AviaTrack account. If you made this request, please click the button below to create a new password.
                                </p>
                            </div>
                            
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 25px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $url }}" style="display: inline-block; background-color: #55b4f4; color: #ffffff; text-decoration: none; padding: 15px 30px; font-size: 15px; font-weight: bold; border-radius: 3px;">
                                            Reset Password
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <div style="max-width: 450px; margin: 0 auto; text-align: left;">
                                <p style="color: #4a4a4a; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">
                                    This password reset link is only valid for <strong>60 minutes</strong>.
                                </p>
                                <p style="color: #8c8c8c; font-size: 13px; line-height: 1.5; margin: 0; padding-top: 15px; border-top: 1px solid #eee;">
                                    If you did not request a password reset, you can safely ignore this email. Only someone with access to your email can reset your account password.
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>