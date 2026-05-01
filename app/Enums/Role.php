<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case Developer = 'developer';
    case Reviewer = 'reviewer';
    case Qa = 'qa';

    public function label(): string
    {
        return match ($this) {
            self::Developer => 'Developer',
            self::Reviewer => 'Reviewer',
            self::Qa => 'QA',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Developer => 'bg-zinc-100 text-zinc-700 ring-zinc-200',
            self::Reviewer => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
            self::Qa => 'bg-fuchsia-50 text-fuchsia-700 ring-fuchsia-200',
        };
    }
}
