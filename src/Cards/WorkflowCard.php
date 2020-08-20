<?php

namespace Orlyapps\NovaWorkflow\Cards;

use Laravel\Nova\Card;

class WorkflowCard extends Card
{
    /**
     * Indicates if the element is only shown on the detail screen.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/3';

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return 'workflow-card';
    }
}
