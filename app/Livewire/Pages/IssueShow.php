<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Issue;
use App\Workflow\WorkflowReasonContext;
use Illuminate\Support\Facades\Auth;
use Laraflow\Contracts\WorkflowRegistryInterface;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Issue — Symflow Demo')]
class IssueShow extends Component
{
    public Issue $issue;

    public string $reason = '';

    public function mount(Issue $issue): void
    {
        $this->issue = $issue->load(['reporter', 'assignee', 'auditLogs.actor']);
    }

    public function fire(string $transition): void
    {
        $workflow = app(WorkflowRegistryInterface::class)->get('issue_tracking');
        $result = $workflow->can($this->issue, $transition);

        if (! $result->allowed) {
            $messages = collect($result->blockers)->map(fn ($b) => $b->message)->implode(' / ');
            session()->flash('flash.error', "Can't fire \"{$transition}\": {$messages}");

            return;
        }

        WorkflowReasonContext::set($this->reason !== '' ? $this->reason : null);

        try {
            $workflow->apply($this->issue, $transition);
        } catch (\Throwable $e) {
            session()->flash('flash.error', $e->getMessage());

            return;
        }

        if ($transition === 'start_work') {
            $this->issue->started_at = now();
        }
        if (in_array($transition, ['merge', 'close', 'reject_code', 'reject_qa'], true)) {
            $this->issue->closed_at = now();
        }
        $this->issue->save();
        $this->reason = '';

        $this->issue->refresh()->load(['auditLogs.actor']);
        session()->flash('flash.success', "Fired transition \"{$transition}\".");
    }

    #[On('user-changed')]
    public function onUserChanged(): void
    {
        $this->issue->refresh();
    }

    public function getEnabledTransitionsProperty(): array
    {
        $workflow = app(WorkflowRegistryInterface::class)->get('issue_tracking');
        $rows = [];

        foreach ($workflow->definition->transitions as $transition) {
            $result = $workflow->can($this->issue, $transition->name);
            $blockerReason = $result->blockers[0]->message ?? null;

            $intent = match (true) {
                str_starts_with($transition->name, 'reject'), $transition->name === 'close' => 'destructive',
                $transition->name === 'merge' => 'success',
                str_starts_with($transition->name, 'approve') => 'primary',
                default => 'neutral',
            };

            $rows[] = [
                'transition' => $transition,
                'allowed' => $result->allowed,
                'reason' => $blockerReason,
                'intent' => $intent,
            ];
        }

        return $rows;
    }

    public function render()
    {
        return view('livewire.pages.issue-show', [
            'enabledTransitions' => $this->enabledTransitions,
            'activePlaces' => $this->issue->activePlaces(),
            'currentUser' => Auth::user(),
        ]);
    }
}
