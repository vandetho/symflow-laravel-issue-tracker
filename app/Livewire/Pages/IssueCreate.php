<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Enums\Priority;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('New issue — Symflow Demo')]
class IssueCreate extends Component
{
    #[Validate('required|string|max:140')]
    public string $title = '';

    #[Validate('nullable|string|max:2000')]
    public string $description = '';

    #[Validate('required|string')]
    public string $priority = 'medium';

    #[Validate('nullable|string|max:32')]
    public string $label = '';

    public ?int $assignee_id = null;

    public function save()
    {
        $this->validate();

        $user = Auth::user();
        if (! $user instanceof User) {
            $user = User::query()->where('role', 'developer')->first() ?? User::query()->first();
            Auth::login($user);
        }

        $reference = 'ENG-' . str_pad((string) ((Issue::query()->max('id') ?? 0) + 101), 3, '0', STR_PAD_LEFT);

        $issue = Issue::query()->create([
            'reference' => $reference,
            'reporter_id' => $user->id,
            'assignee_id' => $this->assignee_id ?: $user->id,
            'title' => $this->title,
            'description' => $this->description !== '' ? $this->description : null,
            'priority' => $this->priority,
            'label' => $this->label !== '' ? $this->label : null,
            'marking' => 'open',
        ]);

        session()->flash('flash.success', "Created {$reference}.");

        return $this->redirectRoute('issues.show', $issue, navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.issue-create', [
            'priorities' => Priority::cases(),
            'developers' => User::query()->where('role', 'developer')->orderBy('name')->get(),
        ]);
    }
}
