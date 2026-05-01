<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Laraflow\Contracts\WorkflowRegistryInterface;
use Laraflow\Export\MermaidExporter;
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

        $base = MermaidExporter::export($workflow->definition);

        $highlight = "\n    classDef active fill:#6366f120,stroke:#4338ca,stroke-width:2px,color:#3730a3,font-weight:600;\n";
        $highlight .= "    classDef done fill:#a855f71a,stroke:#7c3aed,stroke-width:1.5px,color:#5b21b6;\n";
        $highlight .= "    classDef closed fill:#f43f5e1a,stroke:#e11d48,stroke-width:2px,color:#9f1239,font-weight:600;\n";
        $highlight .= "    classDef merged fill:#10b9811a,stroke:#059669,stroke-width:2px,color:#065f46,font-weight:600;\n";

        foreach ($this->activePlaces as $place) {
            $class = match (true) {
                $place === 'closed' => 'closed',
                $place === 'merged' => 'merged',
                str_ends_with($place, '_approved') => 'done',
                default => 'active',
            };
            $highlight .= "    class {$place} {$class};\n";
        }

        $diagram = $base . $highlight;

        return view('livewire.components.workflow-diagram', [
            'diagram' => $diagram,
        ]);
    }
}
