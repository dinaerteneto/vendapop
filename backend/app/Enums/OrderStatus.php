<?php

namespace App\Enums;

enum OrderStatus: string
{
    case NEW = 'NEW';
    case DONE = 'DONE';
    case CANCELED = 'CANCELED';

    /**
     * Get label in Portuguese
     */
    public function label(): string
    {
        return match($this) {
            self::NEW => 'Novo',
            self::DONE => 'Concluído',
            self::CANCELED => 'Cancelado',
        };
    }

    /**
     * Get all statuses as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all statuses with labels
     */
    public static function options(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}

