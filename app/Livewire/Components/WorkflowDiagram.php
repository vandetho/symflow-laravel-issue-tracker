<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Laraflow\Contracts\WorkflowRegistryInterface;
use Laraflow\Data\WorkflowDefinition;
use Livewire\Component;

class WorkflowDiagram extends Component
{
    public string $workflowName;

    /** @var array<string> */
    public array $activePlaces = [];

    /**
     * @param  array<string>  $activePlaces
     */
    public function mount(string $workflowName, array $activePlaces = []): void
    {
        $this->workflowName = $workflowName;
        $this->activePlaces = $activePlaces;
    }

    public function render()
    {
        $registry = app(WorkflowRegistryInterface::class);
        $workflow = $registry->get($this->workflowName);

        $diagram = $this->buildFlowchart($workflow->definition, $this->activePlaces);

        return view('livewire.components.workflow-diagram', [
            'diagram' => $diagram,
        ]);
    }

    /**
     * @param  array<string>  $active
     */
    private function buildFlowchart(WorkflowDefinition $definition, array $active): string
    {
        $lines = [];
        $lines[] = 'flowchart LR';

        foreach ($definition->places as $place) {
            $description = $place->metadata['description'] ?? null;
            $label = $description ? "{$place->name}\\n{$description}" : $place->name;
            $lines[] = "    {$place->name}([\"{$label}\"])";
        }

        foreach ($definition->transitions as $t) {
            $label = $t->name;
            if ($t->guard !== null) {
                $label .= " [{$t->guard}]";
            }

            foreach ($t->froms as $from) {
                foreach ($t->tos as $to) {
                    $lines[] = "    {$from} -->|{$label}| {$to}";
                }
            }
        }

        $lines[] = '';
        $lines[] = '    classDef base fill:#fafafa,stroke:#a1a1aa,stroke-width:1px,color:#27272a';
        $lines[] = '    classDef active fill:#c7d2fe,stroke:#4338ca,stroke-width:3px,color:#1e1b4b,font-weight:700';
        $lines[] = '    classDef done fill:#ddd6fe,stroke:#7c3aed,stroke-width:2px,color:#4c1d95,font-weight:600';
        $lines[] = '    classDef closed fill:#fecdd3,stroke:#e11d48,stroke-width:3px,color:#9f1239,font-weight:700';
        $lines[] = '    classDef merged fill:#a7f3d0,stroke:#059669,stroke-width:3px,color:#064e3b,font-weight:700';

        $allPlaceNames = array_map(fn ($p) => $p->name, $definition->places);
        $lines[] = '    class ' . implode(',', $allPlaceNames) . ' base';

        foreach ($active as $place) {
            $class = match (true) {
                $place === 'closed' => 'closed',
                $place === 'merged' => 'merged',
                str_ends_with($place, '_approved') => 'done',
                default => 'active',
            };
            $lines[] = "    class {$place} {$class}";
        }

        return implode("\n", $lines) . "\n";
    }
}
