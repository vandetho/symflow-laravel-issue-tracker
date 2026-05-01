<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-2 text-sm text-zinc-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-zinc-900">Issues</a>
        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02z" clip-rule="evenodd"/></svg>
        <span class="font-medium text-zinc-700">New</span>
    </div>

    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">File a new issue</h1>
        <p class="mt-1 text-sm text-zinc-500">New issues start in <code class="rounded bg-zinc-100 px-1 py-0.5 font-mono text-xs">open</code>. Sign in as the assignee to start work.</p>
    </div>

    <form wire:submit="save" class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs">
        <div>
            <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500">Title</label>
            <input type="text" id="title" wire:model="title" placeholder="What needs to happen?"
                   class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-xs placeholder:text-zinc-400 focus:border-indigo-500 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20"/>
            @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500">Description</label>
            <textarea id="description" wire:model="description" rows="4" placeholder="Background, repro steps, scope…"
                      class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-xs placeholder:text-zinc-400 focus:border-indigo-500 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20"></textarea>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label for="priority" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500">Priority</label>
                <select id="priority" wire:model="priority"
                        class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-xs focus:border-indigo-500 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20">
                    @foreach ($priorities as $p)
                        <option value="{{ $p->value }}">{{ $p->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="label" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500">Label</label>
                <input type="text" id="label" wire:model="label" placeholder="bug, ui, infra…"
                       class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-xs focus:border-indigo-500 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20"/>
            </div>
            <div>
                <label for="assignee" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500">Assignee</label>
                <select id="assignee" wire:model="assignee_id"
                        class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-xs focus:border-indigo-500 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">— me —</option>
                    @foreach ($developers as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-medium text-zinc-500 hover:text-zinc-900">Cancel</a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-indigo-700">
                Create issue
            </button>
        </div>
    </form>
</div>
