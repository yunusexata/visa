<?php

namespace App\Enums\Form;

enum VisaFormLinkStatus: string
{
    case STATUS_ACTIVE = 'ACTIVE';
    case STATUS_INACTIVE = 'INACTIVE';
    case STATUS_EXPIRED = 'EXPIRED';
    case STATUS_SUCCESS = 'SUCCESS';

    public function label(): string
    {
        return match ($this) {
            self::STATUS_ACTIVE => 'ACTIVE',
            self::STATUS_INACTIVE => 'INACTIVE',
            self::STATUS_EXPIRED => 'EXPIRED',
            self::STATUS_SUCCESS => 'SUCCESS',
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
            self::STATUS_ACTIVE => '<span class="btn btn-sm btn-warning">'.$this->label().'</span>',
            self::STATUS_INACTIVE => '<span class="btn btn-sm btn-success">'.$this->label().'</span>',
            self::STATUS_EXPIRED => '<span class="btn btn-sm btn-success">'.$this->label().'</span>',
            self::STATUS_SUCCESS => '<span class="btn btn-sm btn-success">'.$this->label().'</span>',
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
