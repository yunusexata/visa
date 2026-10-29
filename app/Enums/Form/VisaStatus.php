<?php

namespace App\Enums\Form;

enum VisaStatus: string
{
    case STATUS_BELUM_LENGKAP = 'BELUM LENGKAP';
    case STATUS_LENGKAP = 'LENGKAP';
    case STATUS_VERIFIED = 'VERIFIED';
    case STATUS_DALAM_PENGAJUAN = 'DALAM PENGAJUAN';
    case STATUS_SELESAI = 'SELESAI';

    public function label(): string
    {
        return match ($this) {
            self::STATUS_BELUM_LENGKAP => 'BELUM LENGKAP',
            self::STATUS_LENGKAP => 'LENGKAP',
            self::STATUS_VERIFIED => 'VERIFEID',
            self::STATUS_DALAM_PENGAJUAN => 'DALAM PENGAJUAN',
            self::STATUS_SELESAI => 'SELESAI',
        };
    }

    public static function fromLabel(string $label): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->label() === $label) {
                return $case;
            }
        }

        return null;
    }

    public function print(): string
    {
        return match ($this) {
            self::STATUS_BELUM_LENGKAP => '<span class="btn btn-sm btn-warning">'.$this->label().'</span>',
            self::STATUS_LENGKAP => '<span class="btn btn-sm btn-success">'.$this->label().'</span>',
            self::STATUS_VERIFIED => '<span class="btn btn-sm btn-success">'.$this->label().'</span>',
            self::STATUS_DALAM_PENGAJUAN => '<span class="btn btn-sm btn-success">'.$this->label().'</span>',
            self::STATUS_SELESAI => '<span class="btn btn-sm btn-success">'.$this->label().'</span>',
        };
    }

    public function is(self $status): bool
    {
        return $this === $status;
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }
}
