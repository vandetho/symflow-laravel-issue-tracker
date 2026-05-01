<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Issue;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Issues — Symflow Demo')]
class Dashboard extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'view')]
    public string $view = 'kanban';

    #[On('user-changed')]
    public function onUserChanged(): void
    {
    }

    public function render()
    {
        $query = Issue::query()->with(['reporter', 'assignee'])->latest();

        if ($this->search !== '') {
            $needle = '%' . $this->search . '%';
            $query->where(fn ($q) => $q
                ->where('title', 'like', $needle)
                ->orWhere('reference', 'like', $needle)
                ->orWhere('label', 'like', $needle));
        }

        $issues = $query->get();

        $columns = [
            ['key' => 'open',         'label' => 'Open',          'tone' => 'zinc',    'items' => collect()],
            ['key' => 'in_progress',  'label' => 'In progress',   'tone' => 'sky',     'items' => collect()],
            ['key' => 'in_review',    'label' => 'In review',     'tone' => 'amber',   'items' => collect()],
            ['key' => 'review_done',  'label' => 'Partial',       'tone' => 'violet',  'items' => collect()],
            ['key' => 'merged',       'label' => 'Merged',        'tone' => 'emerald', 'items' => collect()],
            ['key' => 'closed',       'label' => 'Closed',        'tone' => 'rose',    'items' => collect()],
        ];

        foreach ($issues as $issue) {
            foreach ($columns as &$col) {
                if ($col['key'] === $issue->status) {
                    $col['items']->push($issue);
                }
            }
        }
        unset($col);

        return view('livewire.pages.dashboard', [
            'columns' => $columns,
            'issues' => $issues,
            'totals' => [
                'count' => $issues->count(),
                'open' => $issues->whereIn('status', ['open', 'in_progress'])->count(),
                'review' => $issues->whereIn('status', ['in_review', 'review_done'])->count(),
                'shipped' => $issues->where('status', 'merged')->count(),
            ],
        ]);
    }
}
