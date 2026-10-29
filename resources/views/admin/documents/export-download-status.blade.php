<table>
    <thead>
        <tr>
            <th style="font-weight: bold; background-color: #001B5E; color: #ffffff;">No</th>
            <th style="font-weight: bold; background-color: #001B5E; color: #ffffff;">Client ID / No JP</th>
            <th style="font-weight: bold; background-color: #001B5E; color: #ffffff;">WA Update</th>
            <th style="font-weight: bold; background-color: #001B5E; color: #ffffff;">Nama Klien</th>
            <th style="font-weight: bold; background-color: #001B5E; color: #ffffff;">PIC</th>
            <th style="font-weight: bold; background-color: #001B5E; color: #ffffff;">Batch ID &amp; Tanggal Upload</th>
            <th style="font-weight: bold; background-color: #001B5E; color: #ffffff;">Status</th>
            <th style="font-weight: bold; background-color: #001B5E; color: #ffffff;">Document Type</th>
            <th style="font-weight: bold; background-color: #001B5E; color: #ffffff;">Timestamp</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($collection as $index => $item)
            @php
                $isDownloaded = !empty($item->download_id) || !empty($item->downloaded_at);
                $statusText = $isDownloaded ? 'Mendownload' : 'Belum Download';
                $rowStyle = !$isDownloaded ? 'background-color: #FFC7CE; color: #9C0006; font-weight: bold;' : '';
                $docTypeLabel = 'Semua Dokumen (General)';
                $batchInfo = 'Batch #' . ($item->batch_id ?? '-') . ' (' . ($item->batch_created_at ? \Carbon\Carbon::parse($item->batch_created_at)->format('Y-m-d H:i') : '-') . ')';
                $picLabel = $item->client_pic ? strtoupper($item->client_pic) : '-';
            @endphp
            <tr style="{{ $rowStyle }}">
                <td style="{{ $rowStyle }}">{{ $index + 1 }}</td>
                <td style="{{ $rowStyle }}">{{ $item->no_jp ?? '-' }}</td>
                <td style="{{ $rowStyle }}">{{ App\Helpers\NumberFormatter::formatForWhatsApp( $item->current_phone ?? $item->phone) }}</td>
                <td style="{{ $rowStyle }}">{{ $item->client_name }}</td>
                <td style="{{ $rowStyle }}">{{ $picLabel }}</td>
                <td style="{{ $rowStyle }}">{{ $batchInfo }}</td>
                <td style="{{ $rowStyle }}">{{ $statusText }}</td>
                <td style="{{ $rowStyle }}">{{ $docTypeLabel }}</td>
                <td style="{{ $rowStyle }}">{{ $item->downloaded_at ? \Carbon\Carbon::parse($item->downloaded_at)->format('Y-m-d H:i:s') : '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
