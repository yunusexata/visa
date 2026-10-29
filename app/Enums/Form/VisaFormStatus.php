<?php

namespace App\Enums\Form;

enum VisaFormStatus: string
{
    case DRAFT = 'DRAFT';
    case DIAJUKAN = 'DIAJUKAN';
    case SIAP_VERIFIKASI = 'SIAP VERIFIKASI';
    case VERIFIKASI = 'VERIFIKASI';
    case SELESAI = 'SELESAI';
    case DITOLAK = 'DITOLAK';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::DIAJUKAN => 'Diajukan',
            self::SIAP_VERIFIKASI => 'Siap Verifikasi',
            self::VERIFIKASI => 'Terverifikasi',
            self::SELESAI => 'Selesai',
            self::DITOLAK => 'Ditolak',
        };
    }

    public static function fromLabel(string $label): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->label() === $label || $case->value === $label) {
                return $case;
            }
        }

        return null;
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
