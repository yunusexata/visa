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
                <th class="text-center">NAMA FB</th>
                <th class="text-center">LPK</th>
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
                        <td>{{ $data['dob'] ? Carbon\Carbon::parse($data['dob'])->format('Ymd') : '' }}</td>
                        <td>{{ mb_strtoupper($data['phone']) }}</td>
                        <td>{{ mb_strtoupper($data['pic']) }}</td>
                        @if (!$data->user && $data->magicLink)
                            <td>{{ route('client.create', $data->magicLink['token']) }}
                         </td>
                        @elseif ($data->user && !$data->confirmation)
                            <td>BELUM KONFIRMASI</td>
                        @endif
                        <td>{{ mb_strtoupper($data['facebook']) }}</td>
                        <td>{{ mb_strtoupper($data['lpk']) }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</body>

</html>
