<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Account Credentials</title>
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
                            <h2 style="color: #4a4a4a; font-size: 24px; font-weight: 500; margin: 0 0 25px 0;">Account Credentials</h2>
                            <div style="max-width: 450px; margin: 0 auto; text-align: left;">
                                <p style="color: #4a4a4a; font-size: 15px; line-height: 1.6; margin: 0 0 15px 0;">
                                    Hello <strong>{{ $user->name }}</strong>,
                                </p>
                                <p style="color: #4a4a4a; font-size: 15px; line-height: 1.6; margin: 0 0 15px 0;">
                                    An account has been successfully created for you in <strong>AviaTrack</strong>. You can now access the platform to view and manage your operational tasks.
                                </p>
                                <p style="color: #4a4a4a; font-size: 15px; line-height: 1.6; margin: 0 0 20px 0;">
                                    Below are the credentials you will need to log in to the system:
                                </p>
                                
                                <div style="background-color: #f8fafc; border-left: 4px solid #55b4f4; padding: 20px; margin-bottom: 20px; border-radius: 0 4px 4px 0;">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="color: #64748b; font-size: 14px; font-weight: 600; padding: 5px 0; width: 130px;">Access Role</td>
                                            <td style="color: #0f172a; font-size: 14px;">: <span style="text-transform: uppercase; font-weight: bold; color: #55b4f4;">{{ $user->role ?? 'User' }}</span></td>
                                        </tr>
                                        <tr>
                                            <td style="color: #64748b; font-size: 14px; font-weight: 600; padding: 5px 0;">Login Email</td>
                                            <td style="color: #0f172a; font-size: 14px;">: <strong>{{ $user->email }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td style="color: #64748b; font-size: 14px; font-weight: 600; padding: 5px 0;">Temporary Password</td>
                                            <td style="color: #0f172a; font-size: 14px;">: <span style="font-family: monospace; font-size: 15px; font-weight: bold; background-color: #e2e8f0; padding: 3px 8px; border-radius: 4px; letter-spacing: 0.5px;">{{ $password }}</span></td>
                                        </tr>
                                    </table>
                                </div>

                                <p style="font-size: 13px; color: #dc2626; font-style: italic; background-color: #fef2f2; padding: 10px; border-radius: 4px; text-align: center; margin: 0 0 25px 0;">
                                    ⚠️ <strong>IMPORTANT:</strong> For security purposes, please change your password in the "My Profile" section immediately after your first successful login.
                                </p>
                            </div>
                            
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 25px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('login') }}" style="display: inline-block; background-color: #55b4f4; color: #ffffff; text-decoration: none; padding: 15px 30px; font-size: 15px; font-weight: bold; border-radius: 3px;">
                                            Log In to Your Account
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <div style="max-width: 450px; margin: 0 auto; text-align: left;">
                                <p style="color: #8c8c8c; font-size: 13px; line-height: 1.5; margin: 0; padding-top: 15px; border-top: 1px solid #eee;">
                                    If you did not request this account or if you encounter any issues logging in, please contact our IT Administrator immediately.
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
