<?php

namespace Orlyapps\NovaWorkflow\Fields;

use Laravel\Nova\Fields\Badge as NovaBadge;

class WorkflowBadge extends NovaBadge
{
    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|callable|null  $attribute
     * @param  callable|null  $resolveCallback
     * @return void
     */
    public function __construct($name, $className)
    {
        parent::__construct($name, config('workflow.marking_store_field'), null);

        $this->map($className::statusColors());
        $this->labels($className::statusLabels());
    }

    /**
     * The built-in badge types and their corresponding CSS classes.
     *
     * @var array
     */
    public $types = [
        'blue' => 'bg-blue-light text-blue-dark',
        'green' => 'bg-green-light text-green-dark',
        'orange' => 'bg-orange-light text-orange-dark',
        'red' => 'bg-red-light text-red-dark',
        'purple' => 'bg-purple-light text-purple-dark',
        'gray' => 'bg-gray-light text-gray-dark',
        'yellow' => 'bg-yellow-light text-yellow-dark',
        'indigo' => 'bg-indigo-light text-indigo-dark',
        'pink' => 'bg-pink-light text-pink-dark',
        'cyan' => 'bg-cyan-light text-cyan-dark',
    ];
}
