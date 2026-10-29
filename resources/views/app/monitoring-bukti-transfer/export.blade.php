<table>
    <thead>
        <tr>
            <th>No</th>
            <th>No. JP</th>
            <th>Nama Klien</th>
            <th>Whatsapp</th>
            <th>PIC</th>
            <th>Batch Upload</th>
            <th>Kloter Closing</th>
            <th>Tipe Dokumen</th>
            <th>Status Download</th>
            <th>Waktu Download</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($collection as $index => $item)
            @php
                $status = $item->status ?? 'not_downloaded';
                $pic = $item->pic ?? null;
                $source_file_name = $item->source_file_name ?? null;
                $client_closing_batch_number = $item->client_closing_batch_number ?? null;
                $document_type = $item->document_type ?? null;
                $last_downloaded_at = $item->last_downloaded_at ?? null;
                $no_jp = $item->no_jp ?? null;
                $name = $item->name ?? '';
                $phone = $item->current_phone ?? $item->phone ;

                $statusText = match($status) {
                    'downloaded' => 'Mendownload',
                    'seeded' => 'Downloaded*',
                    default => 'Belum Mendownload'
                };
                $statusStyle = match($status) {
                    'downloaded' => 'background-color: #C3E6CB; color: #155724;',
                    'seeded' => 'background-color: #FFEEBA; color: #856404;',
                    default => 'background-color: #F5C6CB; color: #721C24;'
                };
                $picLabel = $pic ? strtoupper($pic) : '-';
                $batchLabel = $source_file_name ?? '-';
                $kloterLabel = $client_closing_batch_number ? 'Kloter ' . $client_closing_batch_number : '-';
                $docTypeLabel = ($request['documentType'] ?? 'all') === 'all' ? 'General (Ichijikin, Resi, Kwitansi)' : ucfirst($document_type);
                $timestampLabel = $last_downloaded_at ? \Carbon\Carbon::parse($last_downloaded_at)->format('Y-m-d H:i:s') : '-';
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $no_jp ?? '-' }}</td>
                <td>{{ $name }}</td>
                <td>{{ App\Helpers\NumberFormatter::formatForWhatsApp($phone) }}</td>
                <td>{{ $picLabel }}</td>
                <td>{{ $batchLabel }}</td>
                <td>{{ $kloterLabel }}</td>
                <td>{{ $docTypeLabel }}</td>
                <td style="{{ $statusStyle }}">{{ $statusText }}</td>
                <td>{{ $timestampLabel }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

