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
    </style>
</head>

<body>

    <table class="table-border" style="width: 100%">
        <thead>
            <tr>
                <th class="text-center">NAMA</th>
                <th class="text-center">PIC</th>
                <th class="text-center">Jenis Pencairan</th>
                <th class="">1. Seberapa puas Anda dengan kecepatan layanan kami?</th>
                <th class="">2. Seberapa puas Anda dengan keramahan dan responsifitas staff kami?</th>
                <th class="">3. Apakah informasi yang kami berikan cukup jelas dan mudah dipahami?</th>
                <th class="">4. Apakah Anda bersedia merekomendasikan layanan kami kepada rekan atau keluarga Anda?</th>
                <th class="">5. Apa alasan utama Anda bersedia merekomendasikan kami?</th>
                <th class="">6. Apakah ada saran agar layanan kami bisa lebih baik lagi?</th>
                <th class="">7. Bagaimana pengalaman Anda menggunakan Portal Online Nenkin ini?</th>
                <th class="">8. Apakah Anda merasa proses pencairan dana Nenkin kami transparan?</th>
                <th class="">9. Seberapa besar tingkat kepercayaan Anda terhadap PT Sumber Rezeki Exata Indonesia?</th>
                <th class="">10. Secara keseluruhan, seberapa puas Anda dengan seluruh rangkaian layanan kami?</th>

            </tr>
        </thead>
        <tbody>

            @php
                $isNumberFormat = $request['type'] == App\Helpers\ExportHelper::TYPE_PDF;
            @endphp
            @if ($collection)
                @foreach ($collection as $index => $data)
                    <tr>
                        <td>{{ $data['name'] }}</td>
                        <td>{{ $data['pic'] }}</td>
                        <td>{{ $data['claim_type'] }}</td>
                        <td>{{ $data->upodSurveyResponse->responses['q1'] }}</td>
                        <td>{{ $data->upodSurveyResponse->responses['q2'] }}</td>
                        <td>{{ $data->upodSurveyResponse->responses['q3'] }}</td>
                        <td>{{ $data->upodSurveyResponse->responses['q4'] }}</td>
                        <td>{{ $data->upodSurveyResponse->responses['q5'] }}</td>
                        <td>{{ $data->upodSurveyResponse->responses['q6'] }}</td>
                        <td>{{ $data->upodSurveyResponse->responses['q7'] }}</td>
                        <td>{{ $data->upodSurveyResponse->responses['q8'] }}</td>
                        <td>{{ $data->upodSurveyResponse->responses['q9'] }}</td>
                        <td>{{ $data->upodSurveyResponse->responses['q10'] }}</td>

                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</body>

</html>
