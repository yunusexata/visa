<?php

namespace App\Enums\Form;

enum VisaAttachmentType: string
{
    case COE = 'coe';
    case E_KTLN = 'e_ktln';
    case KTP = 'ktp';
    case PASPOR = 'paspor';
    case TIKET = 'tiket';
    case PAS_PHOTO = 'pas_photo';
    case PERSYARATAN_PENGURUSAN_VISA = 'persyaratan_pengurusan_visa';

    public function label(): string
    {
        return match ($this) {
            self::COE => 'Photo COE',
            self::E_KTLN => 'E-KTLN',
            self::KTP => 'KTP Indonesia',
            self::PASPOR => 'Photo Paspor',
            self::TIKET => 'Berkas Tiket',
            self::PAS_PHOTO => 'Pas Photo Latar Putih',
            self::PERSYARATAN_PENGURUSAN_VISA => 'PDF Persyaratan Visa'
        };
    }

    public static function mergeAllIdentity(): array
    {
        return [
            self::COE,
            self::E_KTLN,
            self::KTP,
            self::PASPOR,
            self::TIKET,
            self::PAS_PHOTO,
        ];
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

    public function isGenerated(): bool
    {
        return match ($this) {
            self::PERSYARATAN_PENGURUSAN_VISA => true,
            default => false,
        };
    }

    public function is(self $type): bool
    {
        return $this === $type;
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
