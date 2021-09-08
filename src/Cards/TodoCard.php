<?php

namespace Orlyapps\NovaWorkflow\Cards;

use Laravel\Nova\Card;

class TodoCard extends Card
{
    public $providers = [];

    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/2';  

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return 'todo-card';
    }

    public function with($providers)
    {
        foreach ($providers as $provider) {
            $this->providers[] = $provider;
        }

        return $this;
    }

    /**
     * Prepare the element for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge([
            'providers' => $this->providers,
        ], parent::jsonSerialize());
    }
}
