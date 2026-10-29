<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Tangan Diterima — Speed Service Kamu Resmi Aktif!</title>
    <style>
        body {font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f7f9; margin: 0; padding: 0; color: #333333;}
        .container {max-width: 600px; margin: 20px auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;}
        .header {background-color: #2c3e50; color: #ffffff; padding: 30px; text-align: center;}
        .header h1 {margin: 0; font-size: 20px; letter-spacing: 1px;}
        .content {padding: 30px; line-height: 1.6;}
        .greeting {font-size: 16px; font-weight: bold; margin-bottom: 20px;}
        .info-box {background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 20px; margin: 20px 0;}
        .btn-table td {padding-right: 15px;}
        .btn-primary {display: inline-block; background-color: #f59e0b; color: #ffffff !important; text-decoration: none; padding: 12px 28px; font-size: 15px; font-weight: 600; border-radius: 6px;}
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
            <p>Selamat! Tanda tangan digital Anda telah berhasil diterima. Layanan <strong>Speed Service</strong> kini resmi diaktifkan.</p>
            <div class="info-box">
                <strong>⚡ Status Speed Service Anda</strong><br>
                <strong>Jenis Layanan:</strong> Speed Service<br>
                <strong>Status E‑Sign:</strong> Selesai ✓<br>
                <strong>Estimasi Transfer:</strong> 2 minggu sejak tanggal konfirmasi ({{ $signedAtFormatted }})
            </div>
            <p style="margin-bottom: 20px; font-size: 14px; color: #555555;">📎 Dokumen perjanjian yang sudah ditandatangani terlampir dalam email ini. Simpan dokumen ini sebagai bukti layanan kamu.</p>
            <table class="btn-table" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="center"><a href="{{ $dashboardUrl }}" class="btn-primary" target="_blank">Pantau Status di Dashboard</a></td>
                </tr>
            </table>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} PT Sumber Rezeki Exata Indonesia. All Rights Reserved.<br>
            WhatsApp: <a href="https://wa.me/{{ config('exata.admin_wa_number') }}" style="color:#f59e0b; text-decoration:none;">{{ $formattedWa }}</a>
        </div>
    </div>
</body>
</html>
