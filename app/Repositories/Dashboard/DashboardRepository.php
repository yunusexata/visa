<?php

namespace App\Repositories\Dashboard;

use App\Models\GensenForm\GensenForm;
use App\Repositories\MasterDataRepository;
use Carbon\Carbon;

class DashboardRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return GensenForm::class;
    }

    // MONTHLY SUMMARY
    public static function transactionMonthly(?Carbon $month = null)
    {
        $start = now()->startOfMonth();
        $end   = now()->endOfMonth();

        return GensenForm::query()
            ->selectRaw("
            CAST(created_at AS DATE) as transaction_date,
            pic_code,
            COUNT(*) as total
        ")
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('pic_code')
            ->groupByRaw("
            CAST(created_at AS DATE),
            pic_code
        ")
            ->orderBy('transaction_date')
            ->get();
    }

    public static function transactionAchievementMonthly(?Carbon $month = null)
    {
        $month ??= now();

        return GensenForm::query()
            ->selectRaw("
            pic_code,
            COUNT(*) as total_transaction
        ")
            ->whereBetween('created_at', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])
            ->whereNotNull('pic_code')
            ->groupBy('pic_code')
            ->orderByDesc('total_transaction')
            ->get();
    }
}
