<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Verifikasi Email - Asset Monitoring</title>
    <style>
        /* -------------------------------------
            GLOBAL RESETS & TYPOGRAPHY
        ------------------------------------- */
        body {
            background-color: #F8FAFC;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            font-size: 15px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
            color: #334155;
        }
        table {
            border-collapse: separate;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            width: 100%;
        }
        table td {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 15px;
            vertical-align: top;
        }

        /* -------------------------------------
            BODY & CONTAINER
        ------------------------------------- */
        .body {
            background-color: #F8FAFC;
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
            padding: 0;
        }

        /* -------------------------------------
            MAIN CARD & WRAPPER
        ------------------------------------- */
        .main {
            background: #ffffff;
            border-radius: 8px;
            width: 100%;
            border: 1px solid #E2E8F0;
            border-top: 4px solid #0284C7; /* Aksen warna korporat (Biru Angkasa Pura) */
            overflow: hidden;
        }
        .wrapper {
            box-sizing: border-box;
            padding: 40px;
        }

        /* -------------------------------------
            TYPOGRAPHY & CONTENT
        ------------------------------------- */
        .header {
            margin-bottom: 30px;
            text-align: left;
            border-bottom: 1px solid #F1F5F9;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #0F172A;
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .header p {
            color: #64748B;
            font-size: 13px;
            margin: 5px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        h2 {
            color: #0F172A;
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 20px 0;
        }
        p {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 15px;
            font-weight: normal;
            margin: 0 0 20px 0;
            color: #475569;
        }

        /* -------------------------------------
            BUTTON
        ------------------------------------- */
        .btn-container {
            margin: 30px 0;
        }
        .btn {
            box-sizing: border-box;
            width: 100%;
        }
        .btn table {
            width: auto;
        }
        .btn table td {
            background-color: #0284C7;
            border-radius: 6px;
            text-align: center;
        }
        .btn a {
            background-color: #0284C7;
            border: solid 1px #0284C7;
            border-radius: 6px;
            box-sizing: border-box;
            color: #ffffff;
            cursor: pointer;
            display: inline-block;
            font-size: 15px;
            font-weight: 600;
            margin: 0;
            padding: 12px 28px;
            text-decoration: none;
        }

        /* -------------------------------------
            FALLBACK LINK
        ------------------------------------- */
        .fallback-link {
            font-size: 12px;
            color: #64748B;
            word-break: break-all;
            background-color: #F8FAFC;
            padding: 15px;
            border-radius: 6px;
            margin-top: 30px;
        }
        .fallback-link a {
            color: #0284C7;
        }

        /* -------------------------------------
            FOOTER
        ------------------------------------- */
        .footer {
            clear: both;
            margin-top: 20px;
            text-align: center;
            width: 100%;
        }
        .footer td,
        .footer p,
        .footer span,
        .footer a {
            color: #94A3B8;
            font-size: 12px;
            text-align: center;
            line-height: 1.5;
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
                            <td class="wrapper">
                                
                                <div class="header">
                                    <h1>PT. Angkasa Pura Indonesia</h1>
                                    <p>Asset Monitoring System</p>
                                </div>

                                <h2>Verifikasi Alamat Email</h2>
                                <p>Yth. <strong>{{ $user->name }}</strong>,</p>
                                <p>Terima kasih telah bergabung dengan Sistem Monitoring Aset. Untuk alasan keamanan dan guna mengaktifkan akun Anda sepenuhnya, kami perlu memverifikasi alamat email Anda.</p>
                                
                                <p>Silakan klik tombol di bawah ini untuk menyelesaikan proses verifikasi:</p>

                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn-container btn">
                                    <tbody>
                                        <tr>
                                            <td align="left">
                                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                    <tbody>
                                                        <tr>
                                                            <td> <a href="{{ $url }}" target="_blank">Verifikasi Email Saya</a> </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <p>Jika Anda tidak pernah mendaftar untuk akun ini, Anda dapat mengabaikan email ini dengan aman. Akun yang tidak diverifikasi akan dihapus secara otomatis oleh sistem.</p>
                                
                                <p>Hormat kami,<br><strong>Tim IT Asset Management</strong></p>

                                <div class="fallback-link">
                                    Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut ke peramban (browser) Anda:<br>
                                    <a href="{{ $url }}" target="_blank">{{ $url }}</a>
                                </div>

                            </td>
                        </tr>
                    </table>
                    <div class="footer">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="content-block">
                                    <span class="apple-link">Email ini dihasilkan secara otomatis oleh sistem. Harap tidak membalas email ini.</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="content-block">
                                    &copy; {{ date('Y') }} PT. Angkasa Pura Indonesia - Asset Monitoring Division.
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