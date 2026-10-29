<?php

namespace App\Livewire\MonitoringBuktiTransfer;

use App\Helpers\ExportHelper;
use App\Helpers\PermissionHelper;
use App\Models\Client\Client;
use App\Traits\Livewire\WithDatatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class Datatable extends Component
{
    use WithDatatable;

    public $isCanGetAll = false;

    #[Url(except: '')]
    public $documentType = 'all';   // all | resi | kwitansi | ichijikin | general

    #[Url(except: '')]
    public $selectedBatch = 'all';  // source_file_name (batch import client)

    #[Url(except: '')]
    public $selectedClosing = 'all'; // client_closing_batch_number

    #[Url(except: '')]
    public $filterStatus = 'all';   // all | downloaded | seeded | not_downloaded

    #[Url(except: '')]
    public $bulkPic = '';

    public $batches = [];

    public $closings = [];

    public function updatingSelectedBatch()
    {
        $this->resetPage();
    }

    public function updatingSelectedClosing()
    {
        $this->resetPage();
    }

    public function updatingDocumentType()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingBulkPic()
    {
        $this->resetPage();
    }

    public function onMount()
    {
        // PIC scoping: sales = pic sendiri; all-pic jika punya permission
        $this->isCanGetAll = auth()->user()->hasPermissionTo(
            PermissionHelper::transform(PermissionHelper::ACCESS_FILTER_ALL_PIC, PermissionHelper::TYPE_READ)
        );
        if (! $this->isCanGetAll) {
            $this->bulkPic = auth()->user()->pic;
        }

        $this->batches = Client::select('source_file_name as name', DB::raw('count(*) as total'))
            ->whereNotNull('source_file_name')
            ->groupBy('source_file_name')
            ->orderByDesc(DB::raw('MAX(imported_at)'))
            ->get()
            ->toArray();

        $this->closings = DB::table('client_confirmations')
            ->select('client_closing_batch_number as name', DB::raw('count(*) as total'))
            ->whereNotNull('client_closing_batch_number')
            ->whereNull('deleted_at')
            ->groupBy('client_closing_batch_number')
            ->orderBy('client_closing_batch_number')
            ->get()
            ->map(fn ($item) => (array) $item)
            ->toArray();
    }

    public function getColumns(): array
    {
        return [

            [
                'name' => 'Action',
                'sortable' => false,
                'searchable' => false,
                'render' => function ($item) {
                    $credential = $item->email.' '.$item->password;
                    $credentialHtml = "<div class='col-auto'>
                                <button type='button' class='inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-2 py-1 text-sm bg-success-500 text-white shadow-theme-xs hover:bg-success-600 disabled:bg-brand-300' onclick=\"copyToClipboard('$credential')\">
                                    <span class='material-symbols-outlined text-lg' data-icon='edit'>vpn_key</span>
                                </button>
                            </div>
                    ";

                    $html = "<div class='flex flex-nowrap justify-start p-0 m-0 gap-1'>
                        $credentialHtml
                    </div>";

                    return $html;
                },
            ],
            ['key' => 'clients.no_jp', 'name' => 'No Input JPN'],
            ['key' => 'clients.name', 'name' => 'Nama Klien'],
            [
                'key' => 'clients.phone',
                'name' => 'Whatsapp',
            ],
            ['key' => 'clients.pic', 'name' => 'PIC'],
            ['key' => 'clients.source_file_name', 'name' => 'Batch Upload'],
            ['key' => 'client_confirmations.client_closing_batch_number', 'name' => 'Kloter Closing'],
            [
                'searchable' => false,
                'sortable' => false,
                'key' => 'documents.type',
                'name' => 'Tipe Dokumen',
            ],
            ['key' => 'status', 'name' => 'Status Download', 'sortable' => false],
            [
                'searchable' => false,
                'sortable' => false,
                'key' => 'dd_meta.downloaded_at',
                'name' => 'Waktu Download',
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Aksi',
                'render' => function ($item) {
                    $client = $item;
                    $hasAllDocs = false;
                    if ($client) {
                        dd($client);
                        $clientDocs = $client->documents;
                        $hasIchijikin = $clientDocs->where('type', 'ichijikin')->isNotEmpty();
                        $hasResi = $clientDocs->where('type', 'resi')->isNotEmpty();
                        $hasKwitansi = $clientDocs->where('type', 'kwitansi')->isNotEmpty();
                        $hasAllDocs = $hasIchijikin && $hasResi && $hasKwitansi;
                    }
                    $html = '';

                    if ($client) {
                        if ($hasAllDocs) {
                            $html = "<a href='".route('admin.documents.download-zip', $client->id)."' 
                                                class='inline-flex items-center text-brand-600 dark:text-brand-400 hover:underline font-semibold'
                                                title='Download ZIP'>
                                                Download ZIP
                                                <svg class='w-4 h-4 ml-0.5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'></path></svg>
                                            </a>";
                        } else {
                            $html = '<button class="inline-flex items-center text-gray-400 dark:text-gray-500 font-semibold cursor-not-allowed opacity-50" 
                                                title="Dokumen belum lengkap" 
                                                disabled>
                                                Download ZIP
                                                <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </button>';
                        }
                    }

                    return $html;
                },
            ],
        ];
    }

    public function getQuery(): Builder
    {
        $pic = auth()->user()->pic;

        // INNER JOIN documents: hanya menampilkan client yang SUDAH punya dokumen bukti transfer.
        $query = Client::query()
            ->select(
                'clients.id',
                'clients.no_jp',
                'clients.name',
                'clients.phone',
                'clients.pic',
                'clients.source_file_name',
                'client_confirmations.current_phone',
                'client_confirmations.client_closing_batch_number',
                'client_confirmations.closed_at',
                'client_magic_links.email',
                'client_magic_links.password',
                DB::raw('MAX(CASE WHEN documents.type IS NOT NULL THEN documents.type END) as document_type'),
                DB::raw('MAX(dd_meta.downloaded_at) as last_downloaded_at'),
                // Status dihitung di SELECT dan dipakai untuk display + export
                DB::raw("CASE
                            WHEN MAX(CASE WHEN dd_meta.source = 'live_tracked' THEN 1 ELSE 0 END) = 1 THEN 'downloaded'
                            WHEN MAX(CASE WHEN dd_meta.source IS NOT NULL THEN 1 ELSE 0 END) = 1 THEN 'seeded'
                            ELSE 'not_downloaded'
                         END as status")
            )
            ->join('documents', function ($j) {
                $j->on('documents.client_id', '=', 'clients.id')
                    ->whereNull('documents.deleted_at');
                if ($this->documentType && $this->documentType !== 'all') {
                    $j->where('documents.type', $this->documentType);
                }
            })
            ->join('client_magic_links', function ($j) {
                $j->on('client_magic_links.client_id', '=', 'clients.id')
                    ->whereNull('client_magic_links.deleted_at');
            })
            ->leftJoin('client_confirmations', function ($j) {
                $j->on('client_confirmations.client_id', '=', 'clients.id')
                    ->whereNull('client_confirmations.deleted_at');
            })
            // Sub-query downloads (source-based, filtered by doc type)
            ->leftJoinSub(
                $this->downloadsSubquery(),
                'dd_meta',
                'dd_meta.client_id',
                '=',
                'clients.id'
            )
            ->whereNull('clients.deleted_at');

        // Server-side PIC scoping
        $query->where(function ($q) use ($pic) {
            if (! $this->isCanGetAll) {
                $q->where('clients.pic', $pic);
            } elseif ($this->bulkPic && $this->bulkPic !== 'all') {
                $q->where('clients.pic', 'ILIKE', '%'.$this->bulkPic.'%');
            }
        });

        if ($this->selectedBatch && $this->selectedBatch !== 'all') {
            $query->where('clients.source_file_name', $this->selectedBatch);
        }
        if ($this->selectedClosing && $this->selectedClosing !== 'all') {
            $query->where('client_confirmations.client_closing_batch_number', $this->selectedClosing);
        }

        $query->groupBy(
            'clients.id',
            'clients.no_jp',
            'clients.name',
            'clients.pic',
            'clients.source_file_name',
            'client_confirmations.current_phone',
            'client_confirmations.client_closing_batch_number',
            'client_confirmations.closed_at',
            'client_magic_links.email',
            'client_magic_links.password',
        );

        return $this->applyStatusFilter($query);
    }

    private function downloadsSubquery()
    {
        $sub = DB::table('document_downloads')
            ->select(
                'client_id',
                'document_id',
                'batch_id',
                'source',
                DB::raw('MAX(downloaded_at) as downloaded_at')
            )
            ->whereIn('source', ['live_tracked', 'proof_high', 'notified_only', 'infra_log']);

        if ($this->documentType && $this->documentType !== 'all') {
            $sub->whereExists(function ($w) {
                $w->select(DB::raw(1))
                    ->from('documents')
                    ->whereColumn('documents.id', 'document_downloads.document_id')
                    ->where('documents.type', $this->documentType);
            });
        }

        return $sub->groupBy('client_id', 'document_id', 'batch_id', 'source');
    }

    private function applyStatusFilter(Builder $query): Builder
    {
        // Keterbatasan PostgreSQL: alias 'status' dari SELECT tidak bisa dirujuk langsung di HAVING
        return $query->havingRaw(match ($this->filterStatus) {
            'downloaded' => "COALESCE(MAX(CASE WHEN dd_meta.source = 'live_tracked' THEN 1 ELSE 0 END), 0) = 1",
            'seeded' => "COALESCE(MAX(CASE WHEN dd_meta.source = 'live_tracked' THEN 1 ELSE 0 END), 0) = 0
                                 AND COALESCE(MAX(CASE WHEN dd_meta.source IS NOT NULL THEN 1 ELSE 0 END), 0) = 1",
            'not_downloaded' => 'COALESCE(MAX(CASE WHEN dd_meta.source IS NOT NULL THEN 1 ELSE 0 END), 0) = 0',
            default => '1=1',
        });
    }

    #[On('export')]
    public function export($type)
    {
        $fileName = 'Monitoring Bukti Transfer '.Carbon::now()->format('Y-m-d H_i_s');
        $query = $this->datatableGetProcessedQuery();

        $request = [
            'title' => 'Monitoring Bukti Transfer',
            'type' => $type,
            'documentType' => $this->documentType,
            'pic' => $this->bulkPic,
        ];

        return ExportHelper::export(
            $type,
            $fileName,
            $query,
            'app.monitoring-bukti-transfer.export',
            $request
        );
    }

    public function getTotals()
    {
        $allRecords = $this->datatableGetProcessedQuery()->get();

        $totals = [
            'total_downloaded' => 0,
            'total_not_downloaded' => 0,
            'total_ai' => 0,
            'total_ai_downloaded' => 0,
            'total_ai_not_downloaded' => 0,
            'total_mt' => 0,
            'total_mt_downloaded' => 0,
            'total_mt_not_downloaded' => 0,
            'total_sn' => 0,
            'total_sn_downloaded' => 0,
            'total_sn_not_downloaded' => 0,
        ];

        foreach ($allRecords as $row) {
            $isDownloaded = ($row->status === 'downloaded' || $row->status === 'seeded');

            if ($isDownloaded) {
                $totals['total_downloaded']++;
            } else {
                $totals['total_not_downloaded']++;
            }

            if ($row->pic === 'ainul') {
                $totals['total_ai']++;
                if ($isDownloaded) {
                    $totals['total_ai_downloaded']++;
                } else {
                    $totals['total_ai_not_downloaded']++;
                }
            }

            if ($row->pic === 'kim') {
                $totals['total_mt']++;
                if ($isDownloaded) {
                    $totals['total_mt_downloaded']++;
                } else {
                    $totals['total_mt_not_downloaded']++;
                }
            }

            if ($row->pic === 'slamet') {
                $totals['total_sn']++;
                if ($isDownloaded) {
                    $totals['total_sn_downloaded']++;
                } else {
                    $totals['total_sn_not_downloaded']++;
                }
            }
        }

        return $totals;
    }

    public function render()
    {
        return view($this->getView(), [
            'data' => $this->datatableGetData(),
            'columns' => $this->getColumns(),
            'totals' => $this->getTotals(),
        ]);
    }

    public function getView(): string
    {
        return 'livewire.monitoring-bukti-transfer.datatable';
    }
}
