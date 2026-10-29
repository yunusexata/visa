<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Tangan Dibutuhkan — Proses Speed Service Kamu Menunggu</title>
    <style>
        body {font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f7f9; margin: 0; padding: 0; color: #333333;}
        .container {max-width: 600px; margin: 20px auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;}
        .header {background-color: #2c3e50; color: #ffffff; padding: 30px; text-align: center;}
        .header h1 {margin: 0; font-size: 20px; letter-spacing: 1px;}
        .content {padding: 30px; line-height: 1.6;}
        .greeting {font-size: 16px; font-weight: bold; margin-bottom: 20px;}
        .info-box {background-color: #f0fdf4; border-left: 4px solid #1DAA61; padding: 20px; margin: 20px 0;}
        .btn-table td {padding-right: 15px;}
        .btn-primary {display: inline-block; background-color: #1DAA61; color: #ffffff !important; text-decoration: none; padding: 12px 28px; font-size: 15px; font-weight: 600; border-radius: 6px;}
        .footer {background-color: #f9f9f9; padding: 20px; text-align: center; font-size: 11px; color: #888888;}
        @media only screen and (max-width: 600px) {
            .container {width: 100% !important; margin: 0 !important; border-radius: 0 !important;}
            .content {padding: 20px !important;}
            .btn-table td {display: block !important; width: 100% !important; padding-right: 0 !important; padding-bottom: 12px !important;}
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>EXATA INDONESIA</h1></div>
        <div class="content">
            <div class="greeting">Halo {{ $clientName }},</div>
            <p>Kabar baik! Dokumen perjanjian Anda sudah siap dan proses Speed Service menunggu tanda tangan elektronik.</p>
            <div class="info-box">
                <strong>⚡ Speed Service</strong><br>
                <br>
                <strong>SEGERA TANDA TANGAN</strong> Proses NENKIN Speed Service <strong>tidak akan dimulai</strong> jika kamu belum melakukan proses tanda tangan.<br>
                <br>
                Dana Nenkin 20% akan ditransfer dalam estimasi <strong>2 minggu</strong> setelah Tanda tangan.
            </div>
            <p>Silakan klik tombol di bawah ini untuk melanjutkan proses</p>
            <table class="btn-table" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="center"><a href="{{ $esignUrl }}" class="btn-primary" target="_blank">Tanda Tangani Sekarang</a></td>
                </tr> 
            </table>
                </br>
            <table class="btn-table" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="center"><a href="{{ $dashboardUrl }}" class="btn-primary" style="background-color:#ffffff; color:#1DAA61 !important; border:2px solid #1DAA61;" target="_blank">Buka Dashboard</a></td>
                </tr>
            </table>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} PT Sumber Rezeki Exata Indonesia. All Rights Reserved.<br>
            WhatsApp: <a href="https://wa.me/{{ config('exata.admin_wa_number') }}" style="color:#1DAA61; text-decoration:none;">{{ $formattedWa }}</a>
        </div>
    </div>
</body>
</html>
