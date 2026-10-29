<!DOCTYPE html>
<html>

<head>
    <title>{{ $request['title'] }}</title>
    <style>
        .table-border {
            border-collapse: collapse;
            font-size: 7px;
        }

        .table-border td {
            border: 1px solid;
            padding: 3px;
        }

        .table-border th {
            border: 1px solid;
            font-weight: bold;
            padding: 3px;
        }
        .bg-green  { background-color: #00ff3c; }
        .bg-red    { background-color: #ff0015; }
        .bg-orange { background-color: #ff9900; }
        .bg-yellow { background-color: #fffb00; }
    </style>
</head>

<body>

    <table class="table-border" style="width: 100%">
        <thead>
            <tr>
                <th class="text-center">NO INPUT JPN</th>
                <th class="text-center">NAMA</th>
                <th class="text-center">TGL LAHIR</th>
                <th class="text-center">NAMA FB</th>
                <th class="text-center">NAMA IG</th>
                <th class="text-center">NO TELP</th>
                <th class="text-center">WA UPDATE</th>
                <th class="text-center">PIC</th>
                <th class="text-center">LPK</th>
                <th class="text-center">NOTE</th>
                <th class="text-center">JENIS PENCAIRAN</th>
                <th class="text-center">TANDA TANGAN</th>
                <th class="text-center">POSISI SAAT INI</th>
                <th class="text-center">BULAN RE ENTRY</th>
                <th class="text-center">ALASAN RE ENTRY</th>
                <th class="text-center">STATUS EKSPOR</th>
                <th class="text-center">Waktu CLosing</th>
            </tr>
        </thead>
        <tbody>

            @php
                $isNumberFormat = $request['type'] == App\Helpers\ExportHelper::TYPE_PDF;
            @endphp
            @if ($collection)
                @php
                    $total_ai = 0;
                    $total_mt = 0;
                    $total_sn = 0;

                    $total_jpn = 0;
                    $total_re = 0;
                    $total_speed = 0;
                    $total_adjusment = 0;
                    $total_pending = 0;
                    $total_kokumin = 0;
                @endphp
                @foreach ($collection as $index => $data)
                    @php
                        switch (mb_strtoupper($data['pic'])) {
                            case mb_strtoupper('ainul'):
                                $total_ai++;
                                break;
                            case mb_strtoupper('kim'):
                                $total_mt++;
                                break;
                            case mb_strtoupper('slamet'):
                                $total_sn++;
                                break;
                            default:
                                # code...
                                break;
                        }
                        /*
                         * Colour matrix:
                         * SPEED                                           → green  #00ff3c
                         * NORMAL + ALASAN RE ENTRY = akan kembali jepang → red    #ff0015
                         * NORMAL + ALASAN RE ENTRY = sudah di jepang     → orange #ff9900
                         * ADJUSTMENT                                      → yellow #fffb00
                         */
                        $rowColor = '';

                        $claimType       = $data?->confirmation?->claim_type ?? '';
                        $currentPosition = $data->confirmation?->current_position;
                        $backToJpType    = $data->confirmation?->back_to_jp_type;
                        if($data->claim_type === App\Models\Client\Client::CLAIM_TYPE_KOKUMIN){
                            $rowColor = '#FF00FF';
                            $total_kokumin++;
                        }elseif(in_array($claimType, [
                            App\Models\Client\Client::CLAIM_TYPE_SPEED,
                            App\Models\Client\Client::CLAIM_TYPE_ADJUSTMENT
                        ]) && !$data?->esign
                        ){
                            $rowColor = '#465FFF';
                            $total_pending++;
                        }elseif ($claimType === App\Models\Client\Client::CLAIM_TYPE_SPEED) {
                            $rowColor = '#00ff3c'; // hijau
                            $total_speed++;
                        } elseif ($claimType === App\Models\Client\Client::CLAIM_TYPE_ADJUSTMENT) {
                            $rowColor = '#fffb00'; // kuning
                            $total_adjusment++;
                        } elseif ($claimType === App\Models\Client\Client::CLAIM_TYPE_NORMAL) {
                            if ($currentPosition === App\Models\Client\ClientConfirmation::CURRENT_POSITION_JEPANG) {
                                $rowColor = '#ff9900'; // orange — sudah di jepang
                                $total_jpn++;
                            } elseif (
                                $currentPosition === App\Models\Client\ClientConfirmation::CURRENT_POSITION_INDONESIA
                                && $backToJpType === App\Models\Client\ClientConfirmation::BACK_TO_JP_TYPE_YES
                            ) {
                                $rowColor = '#ff0015'; // merah — akan kembali ke jepang
                                $total_re++;
                            }
                        }else{
                            switch (mb_strtoupper($data['pic'])) {
                                case mb_strtoupper('ainul'):
                                    $total_ai++;
                                    break;
                                case mb_strtoupper('kim'):
                                    $total_mt++;
                                    break;
                                case mb_strtoupper('slamet'):
                                    $total_sn++;
                                    break;
                                default:
                                    # code...
                                    break;
                            }
                        }


                        // WHITE = MATRIKS SPEED/ADJUSMENT NOT ESIGNED

                        $rowStyle = $rowColor ? "background-color: {$rowColor};" : '';

                        // Alasan re entry text
                        
                        $alasanReEntry = '';
                        if ($currentPosition === App\Models\Client\ClientConfirmation::CURRENT_POSITION_JEPANG) {
                            $alasanReEntry = 'SUDAH DIJEPANG';
                        } elseif (
                            $currentPosition === App\Models\Client\ClientConfirmation::CURRENT_POSITION_INDONESIA
                            && $backToJpType === App\Models\Client\ClientConfirmation::BACK_TO_JP_TYPE_NO
                        ) {
                            $alasanReEntry = 'TIDAK KEMBALI KE JEPANG';
                        } elseif (
                            $currentPosition === App\Models\Client\ClientConfirmation::CURRENT_POSITION_INDONESIA
                            && $backToJpType === App\Models\Client\ClientConfirmation::BACK_TO_JP_TYPE_YES
                        ) {
                            $alasanReEntry = 'AKAN KEMBALI KE JEPANG';
                        }
                    @endphp
                    <tr style="{{ $rowStyle }}">
                        <td style="{{ $rowStyle }}">{{ mb_strtoupper($data['no_jp']) }}</td>
                        <td style="{{ $rowStyle }}">{{ mb_strtoupper($data['name']) }}</td>
                        <td style="{{ $rowStyle }}">{{ $data['dob'] ? Carbon\Carbon::parse($data['dob'])->format('Ymd') : '' }}</td>
                        <td style="{{ $rowStyle }}">{{ mb_strtoupper($data['facebook']) }}</td>
                        <td style="{{ $rowStyle }}">{{ mb_strtoupper($data['instagram']) }}</td>
                        <td style="{{ $rowStyle }}">{{ $data['phone'] }}</td>
                        <td style="{{ $rowStyle }}">{{ $data->confirmation?->current_phone }}</td>
                        <td style="{{ $rowStyle }}">{{ mb_strtoupper($data['pic']) }}</td>
                        <td style="{{ $rowStyle }}">{{ mb_strtoupper($data['lpk']) }}</td>
                        <td style="{{ $rowStyle }}">{{ mb_strtoupper($data['note']) }}</td>
                        @php
                            $claimType = $data?->confirmation?->claim_type;
                            $text = '';

                            if (!$data?->confirmation || $claimType === App\Models\Client\Client::CLAIM_TYPE_NORMAL) {
                                $text = App\Models\Client\Client::CLAIM_TYPE_NORMAL;
                            } elseif (in_array($claimType, [App\Models\Client\Client::CLAIM_TYPE_ADJUSTMENT, App\Models\Client\Client::CLAIM_TYPE_SPEED])) {
                                // Jika esign true -> pakai claim_type asli, jika false -> PENDING
                                $text = $data?->esign ? $claimType : App\Models\Client\Client::CLAIM_TYPE_PENDING;
                            }
                        @endphp
                        <td style="{{ $rowStyle }}">{{ mb_strtoupper($text) }}</td>
                        <td style="{{ $rowStyle }}">{{ mb_strtoupper($data['esign_exists'] ? 'Sudah' : 'Belum') }}</td>
                        <td style="{{ $rowStyle }}">{{ mb_strtoupper($data->confirmation?->current_position) }}</td>
                        <td style="{{ $rowStyle }}">{{ mb_strtoupper($data->confirmation?->departure_month ? Carbon\Carbon::createFromFormat('n-Y', $data->confirmation?->departure_month.'-'.$data->confirmation?->departure_year)->locale('id')->translatedFormat('F Y') : '') }}</td>
                        <td style="{{ $rowStyle }}">{{ mb_strtoupper($alasanReEntry) }}</td>
                        <td style="{{ $rowStyle }}">{{ $data['is_exported'] ? 'EXPORTED' : 'UN-EXPORTED' }}</td>
                        <td style="{{ $rowStyle }}">{{ $data->confirmation?->closing?->created_at ? Carbon\Carbon::parse($data->confirmation?->closing?->created_at)->format('Ymd H:i:s') : '' }}</td>
                    </tr>
                @endforeach
                <tr>
                </tr>
                <tr>
                </tr>
                <tr>
                </tr>
                <tr>
                </tr>
                <tr>
                    <td></td>
                    <td style="text-align: center; background-color:#FFCCFF">PIC</td>
                    <td style="text-align: center; background-color:#FFCCFF">TOTAL</td>
                </tr>
                <tr style="text-align: center; background-color:#">
                    <td style="text-align: center; background-color:#"></td>
                    <td style="text-align: center; background-color:#FF00FF">AI</td>
                    <td style="text-align: center; background-color:#">{{ $total_ai }}</td>
                </tr>
                <tr style="text-align: center; background-color:#">
                    <td style="text-align: center; background-color:#"></td>
                    <td style="text-align: center; background-color:#FF00FF">MT</td>
                    <td style="text-align: center; background-color:#">{{ $total_mt }}</td>
                </tr>
                <tr style="text-align: center; background-color:#">
                    <td style="text-align: center; background-color:#"></td>
                    <td style="text-align: center; background-color:#FF00FF">SN</td>
                    <td style="text-align: center; background-color:#">{{ $total_sn }}</td>
                </tr>
                <tr>
                    <td style="text-align: center;"></td>
                    <td style="text-align: center;">TOTAL</td>
                    <td style="text-align: center;">{{ $total_ai + $total_mt + $total_sn }}</td>
                </tr>
                <tr></tr>
                <tr>
                    <td></td>
                    <td style="text-align: center; background-color:#FF9900">JPN</td>
                    <td style="text-align: center; background-color:#FF9900">{{ $total_jpn }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td style="text-align: center; background-color:#FF0000">RE-ENTRY</td>
                    <td style="text-align: center; background-color:#FF0000">{{ $total_re }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td style="text-align: center; background-color:#00FF00">SPEED</td>
                    <td style="text-align: center; background-color:#00FF00">{{ $total_speed }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td style="text-align: center; background-color:#FFFF00">SPEED ADJUSMENT</td>
                    <td style="text-align: center; background-color:#FFFF00">{{ $total_adjusment }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td style="text-align: center; background-color:#FF00FF">KOKUMIN</td>
                    <td style="text-align: center; background-color:#FF00FF">{{ $total_kokumin }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>

</html>
