<?php

declare(strict_types=1);

namespace App\Enums;

enum Priority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Critical => 'Critical',
        };
    }

    public function classes(): string
    {
        return match ($this) {
            self::Low => 'bg-zinc-100 text-zinc-700 ring-zinc-200',
            self::Medium => 'bg-sky-50 text-sky-700 ring-sky-200',
            self::High => 'bg-amber-50 text-amber-700 ring-amber-200',
            self::Critical => 'bg-rose-50 text-rose-700 ring-rose-200',
        };
    }

    public function dot(): string
    {
        return match ($this) {
            self::Low => 'bg-zinc-400',
            self::Medium => 'bg-sky-500',
            self::High => 'bg-amber-500',
            self::Critical => 'bg-rose-500',
        };
    }
}
