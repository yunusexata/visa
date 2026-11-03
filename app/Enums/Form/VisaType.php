<?php

namespace App\Enums\Form;

enum VisaType: string
{
    case SPECIFIED_SKILLED_WORKER_I = 'SPECIFIED_SKILLED_WORKER_I';
    case SPECIFIED_SKILLED_WORKER_II = 'SPECIFIED_SKILLED_WORKER_II';
    case TECHNICAL_INTERN_TRAINING_I_B = 'TECHNICAL_INTERN_TRAINING_I_B';
    case TECHNICAL_INTERN_TRAINING_II_B = 'TECHNICAL_INTERN_TRAINING_II_B';
    case TECHNICAL_INTERN_TRAINING_III_B = 'TECHNICAL_INTERN_TRAINING_III_B';
    case DESIGNATED_ACTIVITIES = 'DESIGNATED_ACTIVITIES';
    case DEPENDENT = 'DEPENDENT';
    case ENGINEERING = 'ENGINEERING';
    case MEDICAL_SERVICES = 'MEDICAL_SERVICES';
    case INTRA_COMPANY_TRANSFEREE = 'INTRA_COMPANY_TRANSFEREE';
    case SKILLED_LABOR = 'SKILLED_LABOR';
    case NURSING_CARE = 'NURSING_CARE';
    case FAMILY_VISIT = 'FAMILY_VISIT';
    case WAIVER = 'WAIVER';


    public function label(): string
    {
        return match ($this) {
            self::SPECIFIED_SKILLED_WORKER_I => 'Specified Skilled Worker (I)',
            self::SPECIFIED_SKILLED_WORKER_II => 'Specified Skilled Worker (II)',
            self::TECHNICAL_INTERN_TRAINING_I_B => 'Technical Intern Training (I) (B)',
            self::TECHNICAL_INTERN_TRAINING_II_B => 'Technical Intern Training (II) (B)',
            self::TECHNICAL_INTERN_TRAINING_III_B => 'Technical Intern Training (III) (B)',
            self::DESIGNATED_ACTIVITIES => 'Designated Activities',
            self::DEPENDENT => 'Dependent',
            self::ENGINEERING => 'Engineering',
            self::MEDICAL_SERVICES => 'Medical Services',
            self::INTRA_COMPANY_TRANSFEREE => 'Intra Company Transferee',
            self::SKILLED_LABOR => 'Skilled Labor',
            self::NURSING_CARE => 'Nursing Care',
            self::FAMILY_VISIT => 'Family Visit',
            self::WAIVER => 'Wisata/Waiver',
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
    // 1. Map multiple types to a single matrix code
    public function matrixCode(): ?string
    {
        return match ($this) {
            self::WAIVER => 'WAIVER',

            // Explicitly declare TG group to prevent future enums from defaulting here
            // self::NURSING_CARE,
            // self::FAMILY_VISIT => 'TG',

            default => 'TG', // Safer than 'false', matches ?string return type
        };
    }

    // 2. Backend Helper: Get all Enum cases that share a specific matrix code
    /** @return array<int, self> */
    public static function getTypesByCode(string $code): array
    {
        return array_filter(
            self::cases(),
            fn(self $case) => $case->matrixCode() === $code
        );
    }

    public function is(self $status): bool
    {
        return $this === $status;
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }
}
