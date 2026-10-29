<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembuatan Akun Berhasil</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 0;
            color: #333333;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background-color: #2c3e50;
            /* Professional Charcoal */
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            letter-spacing: 1px;
        }

        .content {
            padding: 30px;
            line-height: 1.6;
        }

        .greeting {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .success-box {
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
            padding: 20px;
            margin: 20px 0;
        }

        .signature {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
        }

        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 11px;
            color: #888888;
        }


        .btn-container {
            text-align: center;
            margin: 25px 0 15px 0;
        }

        .btn-action {
            display: inline-block;
            background-color: #1DAA61;
            color: white !important;
            padding: 12px 30px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            font-size: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>FORMULIR BERHASIL TERSIMPAN</h1>
        </div>

        <!-- Main Content -->
        <div class="content">
            <div class="greeting">Halo {{ $form->name }},</div>

            <p>Terima kasih telah mempercayakan pengajuan Nenkin Anda kepada <strong>PT Sumber Rezeki Exata Indonesia</strong>. Kami menginformasikan bahwa data Anda telah berhasil kami terima dalam sistem kami.</p>

            <div class="success-box">
                <strong>Apa langkah selanjutnya?</strong><br>
                Silahkan masuk ke <a target="_blank" href="{{ route('dashboard.index') }}">dashboard</a> untuk mengisi Formulir Informasi Klien
            </div>

            <p>Mohon simpan email ini sebagai bukti bahwa Anda telah berhasil terdaftar.</p>
            
            <div class="btn-container">
                <a target="_blank"
                href="{{ route('dashboard.index') }}"
                class="btn-action">
                    <span style="vertical-align: middle;">
                        Formulir Informasi Klien
                    </span>
                </a>
            </div>

            <div class="signature">
                Terima kasih atas kerja sama Anda.<br><br>
                Best Regards,<br>
                <strong>Tim PT Sumber Rezeki Exata Indonesia</strong>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            &copy; {{ date('Y') }} PT Sumber Rezeki Exata Indonesia. All Rights Reserved.
        </div>
    </div>
</body>

</html>