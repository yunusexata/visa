<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nenkin Kamu Sudah Cair! 🎉</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f0f4ff;
            margin: 0;
            padding: 0;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #001B5E 0%, #0049FF 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 30px;
            line-height: 1.6;
        }
        .greeting {
            font-size: 18px;
            font-weight: bold;
            color: #001B5E;
            margin-bottom: 15px;
        }
        .step-list {
            margin: 20px 0;
            padding-left: 20px;
        }
        .step-list li {
            margin-bottom: 8px;
        }
        .btn-table {
            margin: 30px auto 20px auto;
        }
        .btn-primary {
            display: inline-block;
            background-color: #0049FF;
            color: #ffffff !important;
            padding: 12px 24px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 10px;
            font-size: 14px;
            box-shadow: 0 4px 10px rgba(0, 73, 255, 0.25);
            text-align: center;
        }
        .btn-secondary {
            display: inline-block;
            background-color: #FF4800;
            color: #ffffff !important;
            padding: 12px 24px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 10px;
            font-size: 14px;
            box-shadow: 0 4px 10px rgba(255, 72, 0, 0.25);
            text-align: center;
        }
        .footer {
            background-color: #f8fafc;
            padding: 25px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid #f1f5f9;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>NENKIN 20% KAMU SUDAH CAIR!</h1>
        </div>

        <!-- Main Content -->
        <div class="content">
            <div class="greeting">Halo {{ $clientName }},</div>
            
            <p>Kabar bahagia untukmu hari ini! <strong>Uang Nenkin 20% kamu sudah cair</strong> dan bukti transfer kamu sudah siap untuk diunduh.</p>
            
            <h3 style="color: #001B5E; margin-top: 25px;">Cara Mengunduh Bukti transfer Kamu:</h3>
            <ol class="step-list">
                <li>Klik tombol <strong>"Buka Dashboard"</strong> di bawah ini.</li>
                <li>Login menggunakan Akun yang sudah kamu buat.</li>
                <li>Pilih dokumen yang ingin kamu unduh pada halaman utama dashboard.</li>
                <li>Klik tombol unduh dan simpan dokumen kamu.</li>
            </ol>

            <!-- CTA Buttons Table for Email Compatibility -->
            <table class="btn-table" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" style="padding-right: 15px;">
                        <a href="{{ $dashboardUrl }}" class="btn-primary" target="_blank">Buka Dashboard</a>
                    </td>
                    <td align="center">
                        <a href="{{ $reviewUrl }}" class="btn-secondary" target="_blank">Tulis Ulasan Google</a>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>PT Sumber Rezeki Exata Indonesia</strong></p>
            <p>Konsultan Nenkin dan Gensen Nomor 1 Di Indonesia</p>
            <p>Kontak: +{{ config('exata.admin_wa_number') }} | Email: info@exata-indonesia.id</p>
            <p style="margin-top: 15px; font-size: 10px; color: #94a3b8;">
                Anda menerima email ini karena Anda adalah klien terdaftar PT Sumber Rezeki Exata Indonesia.<br>
                <a href="#" style="color: #94a3b8; text-decoration: underline;">Berhenti berlangganan</a>
            </p>
        </div>
    </div>
</body>
</html>
