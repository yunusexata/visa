<div class="agreement-content p-8 bg-white text-gray-800" style="font-family: 'Times New Roman', Times, serif; line-height: 1.5; font-size: 12pt;">
    <div class="text-center mb-6">
        <h2 class="font-bold text-xl uppercase leading-tight">PT. SUMBER REZEKI EXATA INDONESIA</h2>
        <p class="text-sm">Grand Galaxy City RRG 5 NO. 9 Jaka Setia Bekasi</p>
        <p class="text-sm">Nomor Telephone : 021 82744576 / 0812 2841 7051</p>
        <p class="text-sm">Email: info@exataindonesia.com</p>
        <hr class="border-t-2 border-black mt-2">
    </div>

    <div class="text-center mb-6">
        <h3 class="font-bold text-lg uppercase underline">PERJANJIAN LAYANAN PERCEPATAN PENCAIRAN NENKIN</h3>
        <h3 class="font-bold text-lg uppercase">(SPEED NENKIN 20%)</h3>
        <p>Nomor: {{ $letterNumber }}</p>
    </div>

    <div class="mb-6 text-justify">
        <p>Perjanjian ini dibuat dan disepakati pada hari <strong>{{ $dateFormatted }}</strong>, oleh dan antara:</p>
    </div>

    <div class="mb-6">
        <h4 class="font-bold uppercase mb-2">PIHAK PERTAMA</h4>
        <table class="w-full">
            <tr>
                <td style="width: 150px;">Nama</td>
                <td>: Novi Prayitno Setiawan</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>: Pimpinan PT Sumber Rezeki Exata Indonesia</td>
            </tr>
        </table>
        <p>(selanjutnya disebut “Pihak Pertama”)</p>
    </div>

    <div class="mb-6">
        <h4 class="font-bold uppercase mb-2">PIHAK KEDUA</h4>
        <table class="w-full">
            <tr>
                <td style="width: 150px;">Nama</td>
                <td>: {{ Str::of($client->name)
    ->replaceMatches('/[^A-Za-z0-9\s]/u', '')
    ->squish()
    ->toString() }}</td>
            </tr>
            <tr>
                <td>NIK</td>
                <td>: {{ $nik ?: '.........................................................................' }}</td>
            </tr>
            <tr>
                <td>Tgl Lahir</td>
                <td>: {{ \Carbon\Carbon::parse($client->dob)->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td class="align-top">Alamat</td>
                <td>: {{ $address ?: '.........................................................................' }}</td>
            </tr>
        </table>
        <p>(selanjutnya disebut “Pihak Kedua”)</p>
    </div>

    <div class="mb-6 text-justify">
        <p>Para Pihak, dengan kesadaran penuh dan tanpa paksaan, sepakat mengikatkan diri dalam perjanjian ini.</p>
    </div>

    <div class="mb-4 text-justify">
        <h4 class="font-bold uppercase">Pasal 1 — POKOK PERJANJIAN</h4>
        <p>Pihak Pertama memberikan layanan percepatan pembayaran (Speed Nenkin 20%), yaitu talangan dana sebesar estimasi 20% dari nilai Dattai Ichijikin (脱退一時金) milik Pihak Kedua, dikurangi biaya administrasi yang disepakati, sebagaimana tercantum dalam bukti transfer/kwitansi yang merupakan bagian tidak terpisahkan dari Perjanjian ini.</p>
    </div>

    <div class="mb-4 text-justify">
        <h4 class="font-bold uppercase">Pasal 2 — PERNYATAAN & JAMINAN PIHAK KEDUA</h4>
        <p>Pihak Kedua menyatakan dan menjamin bahwa seluruh data dan informasi yang diberikan kepada Pihak Pertama adalah benar, lengkap, dan tidak menyesatkan. Pihak Kedua juga menyatakan tidak terdapat tunggakan pajak apapun, termasuk namun tidak terbatas pada Jumin-zei (住民税), selama masa tinggal di Jepang; atau apabila terdapat tunggakan, hal tersebut telah diinformasikan secara tertulis, dicetak, dan diserahkan langsung kepada Pihak Pertama sebelum perjanjian ini ditandatangani. Selain itu, Pihak Kedua menyatakan tidak pernah dan tidak akan mengajukan klaim Dattai Ichijikin untuk periode iuran yang sama melalui pihak manapun (double claim).</p>
    </div>

    <div class="mb-4 text-justify">
        <h4 class="font-bold uppercase">Pasal 3 — TANGGUNG JAWAB & GANTI RUGI</h4>
        <p>Apabila nominal aktual Dattai Ichijikin yang diterima lebih rendah dari estimasi, atau terjadi penolakan maupun pemotongan oleh Japan Pension Service (JPS), yang disebabkan oleh tunggakan pajak atau pemblokiran rekening oleh otoritas Jepang, ketidakbenaran atau ketidaklengkapan data yang diberikan Pihak Kedua, ataupun pengajuan klaim ganda (double claim) oleh Pihak Kedua, maka Pihak Kedua wajib mengganti seluruh kerugian Pihak Pertama, mencakup dana talangan, biaya administrasi, dan biaya hukum yang timbul, paling lambat 14 (empat belas) hari kalender sejak Pihak Pertama menerima pemberitahuan resmi dari Japan Pension Service. Kewajiban ini berlaku penuh dan tidak dapat dikurangi dengan alasan ketidaktahuan Pihak Kedua.</p>
    </div>

    <div class="mb-4 text-justify">
        <h4 class="font-bold uppercase">Pasal 4 — WANPRESTASI</h4>
        <p>Kegagalan Pihak Kedua melaksanakan kewajiban sebagaimana Pasal 3 merupakan wanprestasi berdasarkan Pasal 1239 KUHPerdata dan dapat dituntut secara perdata maupun pidana sesuai hukum yang berlaku di Indonesia. Perjanjian ini sekaligus berfungsi sebagai pengakuan utang Pihak Kedua kepada Pihak Pertama atas nilai kerugian yang timbul.</p>
    </div>

    <div class="mb-4 text-justify">
        <h4 class="font-bold uppercase">Pasal 5 — PENYELESAIAN SENGKETA</h4>
        <p>Perselisihan diselesaikan secara musyawarah mufakat dalam 30 (tiga puluh) hari kalender. Apabila tidak tercapai, Para Pihak memilih domisili hukum tetap di Pengadilan Negeri Jakarta Pusat, Republik Indonesia.</p>
    </div>

    <div class="mb-6 text-justify">
        <p>Perjanjian ini dibuat dalam 2 (dua) rangkap, bermaterai cukup, ditandatangani dalam keadaan sadar dan tanpa paksaan, serta memiliki kekuatan hukum yang sama bagi Para Pihak.</p>
    </div>

    <div class="grid grid-cols-2 gap-10 mt-12">
        <div class="text-center">
            <h4 class="font-bold uppercase mb-16 text-xs sm:text-base">PIHAK PERTAMA</h4>
            <div class="mt-2">
                <p class="font-bold underline">Novi Prayitno Setiawan</p>
                <p class="text-sm">Pimpinan PT Sumber Rezeki Exata Indonesia</p>
            </div>
        </div>
        <div class="text-center">
            <h4 class="font-bold uppercase mb-16 text-xs sm:text-base">PIHAK KEDUA</h4>
            <div class="mt-2">
                <p class="font-bold underline">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $client->name }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</p>
                @if(isset($signature_data) && $signature_data)
                    <img src="{{ $signature_data }}" alt="Signature" class="mx-auto" style="max-height: 100px; margin-top: -80px;">
                @endif
            </div>
        </div>
    </div>
</div>
