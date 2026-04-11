<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Verifikasi Alamat Email - AviaTrack</title>
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
            padding: 35px 30px;
        }
        .wrapper p {
            font-size: 15px;
            font-weight: normal;
            margin: 0 0 16px;
            color: #475569;
        }

        /* Titles */
        .greeting {
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 20px !important;
        }
        
        .sub-heading {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 20px 0;
            text-align: center;
        }

        /* Call to Action Button */
        .btn-container {
            width: 100%;
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            background-color: #2563eb;
            border-radius: 6px;
            color: #ffffff !important;
            display: inline-block;
            font-size: 16px;
            font-weight: bold;
            padding: 14px 35px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #1d4ed8;
        }

        /* Fallback Link Box */
        .fallback-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 15px;
            margin-top: 25px;
            border-radius: 6px;
            font-size: 12px !important;
            color: #64748b !important;
            word-break: break-all;
        }
        .fallback-box a {
            color: #2563eb;
            text-decoration: none;
        }
        .fallback-box a:hover {
            text-decoration: underline;
        }

        /* Security Info */
        .security-info {
            font-size: 13px !important;
            color: #64748b !important;
            margin-top: 25px !important;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }

        /* Signature */
        .signature {
            margin-top: 30px;
        }
        .signature p {
            margin-bottom: 5px;
            font-size: 14px;
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
            table.body h1 { font-size: 20px !important; }
            .sub-heading { font-size: 18px !important; }
            table.body .wrapper { padding: 25px 20px !important; }
            table.body .content { padding: 0 !important; }
            table.body .container { padding: 15px !important; width: 100% !important; }
            table.body .main { border-left-width: 0 !important; border-radius: 0 !important; border-right-width: 0 !important; }
            .btn { width: 100% !important; box-sizing: border-box !important; padding: 15px 20px !important; }
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
                                <img src="{{ $message->embed(public_path('logo.jpg')) }}" alt="AviaTrack Logo" style="width: 100px; height: 100px; margin-bottom: 10px; object-fit: contain;">
                                <h1>AVIATRACK</h1>
                            </td>
                        </tr>

                        <tr>
                            <td class="wrapper">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <h2 class="sub-heading">Verifikasi Email Anda</h2>
                                            
                                            <p class="greeting">Yth. {{ $user->name }},</p>
                                            
                                            <p>Terima kasih telah mendaftar di <strong>Aplikasi AviaTrack</strong>. Untuk alasan keamanan dan memastikan bahwa alamat email ini milik Anda, kami perlu memverifikasi email Anda.</p>
                                            
                                            <p>Silakan klik tombol di bawah ini untuk menyelesaikan proses verifikasi dan mengaktifkan akun Anda:</p>
                                            
                                            <div class="btn-container">
                                                <a href="{{ $url }}" target="_blank" class="btn">Verifikasi Alamat Email</a>
                                            </div>
                                            
                                            <p class="security-info">
                                                Jika Anda tidak pernah mendaftar atau tidak merasa membuat permintaan ini, Anda dapat mengabaikan email ini dengan aman. Tautan verifikasi ini akan kedaluwarsa secara otomatis dan akun yang tidak diverifikasi akan dihapus oleh sistem.
                                            </p>
                                            
                                            <div class="fallback-box">
                                                Jika tombol di atas tidak berfungsi, salin dan tempel tautan berikut ke browser web Anda:<br>
                                                <br>
                                                <a href="{{ $url }}" target="_blank">{{ $url }}</a>
                                            </div>
                                            
                                            <div class="signature">
                                                <p>Salam Hormat,</p>
                                                <p><strong>Tim IT Administrator</strong></p>
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
                                    <p>Pesan ini dihasilkan secara otomatis oleh sistem. Mohon untuk tidak membalas ke alamat email ini.</p>
                                    <p>&copy; {{ date('Y') }} AviaTrack. Seluruh Hak Cipta Dilindungi.</p>
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
