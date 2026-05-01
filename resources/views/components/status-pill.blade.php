@props(['status' => 'open'])

@php
    $map = [
        'open'        => ['label' => 'Open',         'class' => 'bg-zinc-100 text-zinc-700 ring-zinc-200'],
        'in_progress' => ['label' => 'In progress',  'class' => 'bg-sky-50 text-sky-700 ring-sky-200'],
        'in_review'   => ['label' => 'In review',    'class' => 'bg-amber-50 text-amber-700 ring-amber-200'],
        'review_done' => ['label' => 'Partial review', 'class' => 'bg-violet-50 text-violet-700 ring-violet-200'],
        'merged'      => ['label' => 'Merged',       'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'closed'      => ['label' => 'Closed',       'class' => 'bg-rose-50 text-rose-700 ring-rose-200'],
    ];
    $entry = $map[$status] ?? $map['open'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ' . $entry['class']]) }}>
    {{ $entry['label'] }}
</span>
