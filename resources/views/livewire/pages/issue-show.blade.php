<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm text-zinc-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-zinc-900">Issues</a>
        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02z" clip-rule="evenodd"/></svg>
        <code class="font-mono font-medium text-zinc-700">{{ $issue->reference }}</code>
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-3">
                    <code class="font-mono text-sm font-semibold text-zinc-500">{{ $issue->reference }}</code>
                    <h1 class="text-xl font-semibold tracking-tight text-zinc-900">{{ $issue->title }}</h1>
                    <x-status-pill :status="$issue->status" />
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ring-inset {{ $issue->priority->classes() }}">
                        <span class="size-1.5 rounded-full {{ $issue->priority->dot() }}"></span>{{ $issue->priority->label() }}
                    </span>
                    @if ($issue->label)
                        <span class="rounded bg-zinc-100 px-2 py-0.5 text-[11px] font-medium text-zinc-700">{{ $issue->label }}</span>
                    @endif
                </div>
                @if ($issue->description)
                    <p class="mt-3 max-w-2xl text-sm text-zinc-600">{{ $issue->description }}</p>
                @endif
                <dl class="mt-5 grid grid-cols-2 gap-x-8 gap-y-3 sm:grid-cols-4">
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Reporter</dt>
                        <dd class="mt-1 flex items-center gap-2 text-sm text-zinc-900">
                            <span class="grid size-6 place-items-center rounded-full bg-zinc-200 text-[10px] font-semibold text-zinc-700">{{ $issue->reporter->initials() }}</span>
                            {{ $issue->reporter->name }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Assignee</dt>
                        <dd class="mt-1 flex items-center gap-2 text-sm text-zinc-900">
                            @if ($issue->assignee)
                                <span class="grid size-6 place-items-center rounded-full bg-zinc-200 text-[10px] font-semibold text-zinc-700">{{ $issue->assignee->initials() }}</span>
                                {{ $issue->assignee->name }}
                            @else
                                <span class="text-zinc-400">Unassigned</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Started</dt>
                        <dd class="mt-1 text-sm text-zinc-900">{{ optional($issue->started_at)->diffForHumans() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Closed</dt>
                        <dd class="mt-1 text-sm text-zinc-900">{{ optional($issue->closed_at)->diffForHumans() ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 text-right">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Active places</div>
                <div class="mt-1 flex flex-wrap justify-end gap-1">
                    @forelse ($activePlaces as $place)
                        <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] text-zinc-700 ring-1 ring-zinc-200">{{ $place }}</code>
                    @empty
                        <span class="text-xs text-zinc-400">none</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs">
                <div class="border-b border-zinc-100 px-6 py-4">
                    <h2 class="text-sm font-semibold tracking-tight text-zinc-900">Workflow actions</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">
                        @if ($currentUser)
                            Acting as <span class="font-medium text-zinc-700">{{ $currentUser->name }}</span> ({{ $currentUser->role->label() }}).
                        @else
                            Sign in via the button in the top-right to fire role-guarded transitions.
                        @endif
                    </p>
                </div>
                <div class="space-y-4 p-6">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500">Comment / reason (optional)</label>
                    <textarea wire:model="reason" rows="2" placeholder="Captured in the audit log alongside the transition…"
                              class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-xs placeholder:text-zinc-400 focus:border-indigo-500 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20"></textarea>

                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($enabledTransitions as $row)
                            @php
                                $btnClass = match ($row['intent']) {
                                    'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700 disabled:bg-indigo-600/30',
                                    'destructive' => 'bg-rose-600 text-white hover:bg-rose-700 disabled:bg-rose-600/30',
                                    'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 disabled:bg-emerald-600/30',
                                    default => 'bg-zinc-900 text-white hover:bg-zinc-800 disabled:bg-zinc-300',
                                };
                            @endphp
                            <button type="button"
                                    wire:click="fire('{{ $row['transition']->name }}')"
                                    @disabled(! $row['allowed'])
                                    title="{{ $row['allowed'] ? 'Fire this transition' : ($row['reason'] ?? 'Not available') }}"
                                    class="group flex flex-col items-start gap-1 rounded-lg px-4 py-3 text-left text-sm font-semibold transition disabled:cursor-not-allowed {{ $btnClass }}">
                                <span class="flex items-center gap-2">
                                    {{ $row['transition']->name }}
                                    @if ($row['transition']->guard)
                                        <span class="rounded bg-white/20 px-1.5 py-0.5 text-[10px] font-mono">{{ $row['transition']->guard }}</span>
                                    @endif
                                </span>
                                <span class="text-[11px] font-normal opacity-75">
                                    {{ implode(', ', $row['transition']->froms) }} → {{ implode(', ', $row['transition']->tos) }}
                                </span>
                                @if (! $row['allowed'] && $row['reason'])
                                    <span class="text-[11px] font-normal text-white/80">{{ $row['reason'] }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs">
                <div class="border-b border-zinc-100 px-6 py-4">
                    <h2 class="text-sm font-semibold tracking-tight text-zinc-900">Activity timeline</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">Captured by the <code class="font-mono">AuditLogMiddleware</code>.</p>
                </div>
                <ul class="divide-y divide-zinc-100">
                    @forelse ($issue->auditLogs as $log)
                        <li class="flex gap-4 px-6 py-4">
                            <div class="flex-none pt-1">
                                <span class="grid size-8 place-items-center rounded-full bg-zinc-100 text-[11px] font-semibold text-zinc-700">{{ $log->actor?->initials() ?? '··' }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-baseline gap-x-2">
                                    <span class="text-sm font-semibold text-zinc-900">{{ $log->actor?->name ?? 'System' }}</span>
                                    <span class="text-xs text-zinc-500">fired</span>
                                    <code class="rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-[11px] text-zinc-800">{{ $log->transition }}</code>
                                    <span class="ml-auto text-xs text-zinc-400">{{ $log->occurred_at->diffForHumans() }}</span>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-1 text-[11px] text-zinc-500">
                                    @foreach ((array) $log->marking_before as $p)
                                        <code class="rounded bg-zinc-50 px-1 py-0.5 font-mono text-zinc-500 ring-1 ring-zinc-200">{{ $p }}</code>
                                    @endforeach
                                    <svg class="size-3 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02z" clip-rule="evenodd"/></svg>
                                    @foreach ((array) $log->marking_after as $p)
                                        <code class="rounded bg-indigo-50 px-1 py-0.5 font-mono text-indigo-700 ring-1 ring-indigo-200">{{ $p }}</code>
                                    @endforeach
                                </div>
                                @if ($log->reason)
                                    <p class="mt-2 rounded-md bg-zinc-50 px-3 py-2 text-sm italic text-zinc-700">"{{ $log->reason }}"</p>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-12 text-center text-sm text-zinc-500">No activity yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs">
                <div class="border-b border-zinc-100 px-6 py-4">
                    <h2 class="text-sm font-semibold tracking-tight text-zinc-900">Workflow diagram</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">Active places highlighted live.</p>
                </div>
                <div class="p-4">
                    <livewire:components.workflow-diagram
                        workflow-name="issue_tracking"
                        :active-places="$activePlaces"
                        :key="'diagram-'.$issue->id.'-'.implode('-', $activePlaces)" />
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs">
                <div class="border-b border-zinc-100 px-6 py-4">
                    <h2 class="text-sm font-semibold tracking-tight text-zinc-900">Marking</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">Raw Petri-net token count per place.</p>
                </div>
                <div class="p-4">
                    @php $marking = $issue->getWorkflowMarking()->toArray(); @endphp
                    <ul class="grid grid-cols-2 gap-2 text-xs">
                        @foreach ($marking as $place => $tokens)
                            <li class="flex items-center justify-between rounded-md px-2 py-1 {{ $tokens > 0 ? 'bg-indigo-50 text-indigo-800 ring-1 ring-indigo-200' : 'text-zinc-500' }}">
                                <code class="font-mono">{{ $place }}</code>
                                <span class="font-semibold">{{ $tokens }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
