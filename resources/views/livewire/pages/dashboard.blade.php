<div class="space-y-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Issues</h1>
            <p class="mt-1 text-sm text-zinc-500">A live Petri-net workflow with parallel code-review &amp; QA before merge.</p>
        </div>
        <a href="{{ route('issues.create') }}" wire:navigate
           class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-zinc-800">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3.75a.75.75 0 0 1 .75.75v4.75h4.75a.75.75 0 0 1 0 1.5h-4.75v4.75a.75.75 0 0 1-1.5 0v-4.75H4.5a.75.75 0 0 1 0-1.5h4.75V4.5a.75.75 0 0 1 .75-.75z"/></svg>
            New issue
        </a>
    </div>

    @php
        $stats = [
            ['label' => 'All issues', 'value' => $totals['count'], 'tone' => null],
            ['label' => 'Open / in progress', 'value' => $totals['open'], 'tone' => 'sky'],
            ['label' => 'Awaiting review', 'value' => $totals['review'], 'tone' => 'amber'],
            ['label' => 'Shipped', 'value' => $totals['shipped'], 'tone' => 'emerald'],
        ];
    @endphp
    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $s)
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs">
                <dt class="text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ $s['label'] }}</dt>
                <dd class="mt-2 text-2xl font-semibold tracking-tight {{ $s['tone'] === 'amber' ? 'text-amber-700' : ($s['tone'] === 'emerald' ? 'text-emerald-700' : ($s['tone'] === 'sky' ? 'text-sky-700' : 'text-zinc-900')) }}">{{ $s['value'] }}</dd>
            </div>
        @endforeach
    </dl>

    <div class="flex items-center gap-3">
        <div class="relative flex-1 max-w-md">
            <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.41 9.83l3.13 3.13a.75.75 0 1 0 1.06-1.06l-3.13-3.13A5.5 5.5 0 0 0 9 3.5zM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0z" clip-rule="evenodd"/></svg>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by reference, title, label…"
                   class="w-full rounded-lg border border-zinc-200 bg-white py-2 pl-9 pr-3 text-sm shadow-xs placeholder:text-zinc-400 focus:border-indigo-500 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20"/>
        </div>
        <div class="inline-flex rounded-lg border border-zinc-200 bg-white p-1 shadow-xs">
            <button wire:click="$set('view', 'kanban')"
                    class="rounded-md px-3 py-1 text-xs font-semibold transition {{ $view === 'kanban' ? 'bg-zinc-900 text-white' : 'text-zinc-600 hover:text-zinc-900' }}">Kanban</button>
            <button wire:click="$set('view', 'table')"
                    class="rounded-md px-3 py-1 text-xs font-semibold transition {{ $view === 'table' ? 'bg-zinc-900 text-white' : 'text-zinc-600 hover:text-zinc-900' }}">Table</button>
        </div>
    </div>

    @if ($view === 'kanban')
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
            @foreach ($columns as $column)
                @php
                    $tones = [
                        'zinc' => ['border' => 'border-zinc-200', 'dot' => 'bg-zinc-400', 'text' => 'text-zinc-700'],
                        'sky' => ['border' => 'border-sky-200', 'dot' => 'bg-sky-500', 'text' => 'text-sky-800'],
                        'amber' => ['border' => 'border-amber-200', 'dot' => 'bg-amber-500', 'text' => 'text-amber-800'],
                        'violet' => ['border' => 'border-violet-200', 'dot' => 'bg-violet-500', 'text' => 'text-violet-800'],
                        'emerald' => ['border' => 'border-emerald-200', 'dot' => 'bg-emerald-500', 'text' => 'text-emerald-800'],
                        'rose' => ['border' => 'border-rose-200', 'dot' => 'bg-rose-500', 'text' => 'text-rose-800'],
                    ];
                    $t = $tones[$column['tone']];
                @endphp
                <div class="flex flex-col rounded-xl border {{ $t['border'] }} bg-white">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full {{ $t['dot'] }}"></span>
                            <span class="text-xs font-semibold uppercase tracking-wider {{ $t['text'] }}">{{ $column['label'] }}</span>
                        </div>
                        <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-semibold text-zinc-700">{{ $column['items']->count() }}</span>
                    </div>
                    <div class="flex flex-col gap-2 p-3">
                        @forelse ($column['items'] as $issue)
                            <a href="{{ route('issues.show', $issue) }}" wire:navigate
                               class="group rounded-lg border border-zinc-100 bg-zinc-50/50 p-3 transition hover:border-indigo-200 hover:bg-white hover:shadow-xs">
                                <div class="flex items-center gap-2 text-[11px]">
                                    <code class="font-mono font-semibold text-zinc-700">{{ $issue->reference }}</code>
                                    @if ($issue->label)
                                        <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-zinc-600">{{ $issue->label }}</span>
                                    @endif
                                    <span class="ml-auto inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ring-inset {{ $issue->priority->classes() }}">
                                        <span class="size-1.5 rounded-full {{ $issue->priority->dot() }}"></span>{{ $issue->priority->label() }}
                                    </span>
                                </div>
                                <p class="mt-1.5 line-clamp-2 text-sm font-semibold text-zinc-900 group-hover:text-indigo-700">{{ $issue->title }}</p>
                                <div class="mt-3 flex items-center justify-between">
                                    @if ($issue->assignee)
                                        <div class="flex items-center gap-1.5">
                                            <span class="grid size-5 place-items-center rounded-full bg-zinc-200 text-[9px] font-semibold text-zinc-700">{{ $issue->assignee->initials() }}</span>
                                            <span class="text-[11px] text-zinc-500">{{ $issue->assignee->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-zinc-400">Unassigned</span>
                                    @endif
                                    <span class="text-[11px] text-zinc-400">{{ $issue->updated_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-lg border border-dashed border-zinc-200 px-3 py-6 text-center text-xs text-zinc-400">Nothing here.</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
                        <th class="px-4 py-3">Ref</th>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Assignee</th>
                        <th class="px-4 py-3">Priority</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Updated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($issues as $issue)
                        <tr class="cursor-pointer transition hover:bg-zinc-50" onclick="window.location='{{ route('issues.show', $issue) }}'">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-zinc-700">{{ $issue->reference }}</td>
                            <td class="px-4 py-3"><a href="{{ route('issues.show', $issue) }}" wire:navigate class="text-sm font-semibold text-zinc-900 hover:text-indigo-700">{{ $issue->title }}</a></td>
                            <td class="px-4 py-3">
                                @if ($issue->assignee)
                                    <div class="flex items-center gap-2">
                                        <span class="grid size-6 place-items-center rounded-full bg-zinc-200 text-[10px] font-semibold text-zinc-700">{{ $issue->assignee->initials() }}</span>
                                        <span class="text-sm text-zinc-700">{{ $issue->assignee->name }}</span>
                                    </div>
                                @else
                                    <span class="text-sm text-zinc-400">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ring-inset {{ $issue->priority->classes() }}">
                                    <span class="size-1.5 rounded-full {{ $issue->priority->dot() }}"></span>{{ $issue->priority->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3"><x-status-pill :status="$issue->status" /></td>
                            <td class="px-4 py-3 text-sm text-zinc-500">{{ $issue->updated_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-zinc-500">No issues match.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
