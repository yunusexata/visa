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
                <th class="text-center">TGL LAHIR</th>
                <th class="text-center">NO TELP</th>
                <th class="text-center">PIC</th>
                <th class="text-center">LINK</th>
            </tr>
        </thead>
        <tbody>

            @php
                $isNumberFormat = $request['type'] == App\Helpers\ExportHelper::TYPE_PDF;
            @endphp
            @if ($collection)
                @foreach ($collection as $index => $data)
                    <tr>
                        <td>{{ $data['no_jp'] }}</td>
                        <td>{{ $data['name'] }}</td>
                        <td>{{ $data['dob'] ? Carbon\Carbon::parse($data['dob'])->format('Ymd') : '' }}</td>
                        <td>{{ $data['phone'] }}</td>
                        <td>{{ $data['pic'] }}</td>
                        <td>{{ route('client.create', $data->magicLink['token']) }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</body>

</html>
