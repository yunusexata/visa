==========================================
KONFIRMASI E-SIGN — PT SUMBER REZEKI EXATA INDONESIA
==========================================

Halo {{ $clientName }},

Tanda tangan elektronik kamu telah kami terima dan 
dikonfirmasi dengan sukses.

------------------------------------------
DETAIL KONFIRMASI
------------------------------------------
Nama             : {{ $clientName }}
Tanggal E-Sign   : {{ $signedAtFormatted }} WIB
Jenis Layanan    : {{ $serviceTypeLabel }}
Status           : Selesai ✓

@if($client && in_array($client->claim_type, [\App\Models\Client\Client::CLAIM_TYPE_SPEED, \App\Models\Client\Client::CLAIM_TYPE_ADJUSTMENT]))
------------------------------------------
ESTIMASI PENCAIRAN SPEED SERVICE
------------------------------------------
Tanggal Konfirmasi    : {{ $signedAtFormatted }}
Estimasi Transfer     : 7-14 hari kerja sejak tanggal konfirmasi
@endif

------------------------------------------
DOKUMEN TERLAMPIR
------------------------------------------
Dokumen perjanjian yang sudah ditandatangani terlampir 
dalam email ini (Dokumen_Perjanjian_Exata_Indonesia.pdf).
Simpan dokumen ini sebagai bukti layanan kamu.

------------------------------------------
LANGKAH SELANJUTNYA
------------------------------------------
1. Simpan dokumen perjanjian yang terlampir
2. Pantau status pencairan kamu melalui dashboard:
   {{ config('app.url') }}/client/dashboard
3. Hubungi kami jika ada pertanyaan

------------------------------------------
BUTUH BANTUAN?
------------------------------------------
WhatsApp : {{ $formattedWa }}
Dashboard: {{ config('app.url') }}/client/dashboard

Email ini dikirim otomatis. Mohon jangan membalas email ini.

Salam,
Tim PT Sumber Rezeki Exata Indonesia
