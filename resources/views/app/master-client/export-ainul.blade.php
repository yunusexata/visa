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
                <th class="text-center">NO INPUT JPN</th>
                <th class="text-center">NAMA</th>
                <th class="text-center">EMAIL</th>
                <th class="text-center">ALAMAT INDO</th>
            </tr>
        </thead>
        <tbody>

            @php
                $isNumberFormat = $request['type'] == App\Helpers\ExportHelper::TYPE_PDF;
            @endphp
            @if ($collection)
                @foreach ($collection as $index => $data)
                    <tr>
                        <td>{{ mb_strtoupper($data['no_jp']) }}</td>
                        <td>{{ mb_strtoupper($data['name']) }}</td>
                        <td>{{ mb_strtoupper($data->user['email']) }}</td>
                        <td>{{ mb_strtoupper($data?->confirmation?->indonesia_address) }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</body>

</html>
