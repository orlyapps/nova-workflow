<?php

namespace Orlyapps\NovaWorkflow\Models;

class WorkflowDefinition
{
    /**
    * The class the workflow corresponds to.
    *
    * @var string
    */
    public $name = null;

    public $initialPlace = null;

    /**
     * The class the workflow corresponds to.
     *
     * @var string
     */
    public $supports = [];

    /**
     * The place field in the database
     *
     * @var string
     */
    public static $title = 'status';

    /**
     *
     */
    public function places()
    {
        return [];
    }

    public function transitions()
    {
        return  [];
    }

    public function users($model, string $status)
    {
        return collect();
    }

    public function toArray()
    {
        return [
            'places' => collect($this->places())->map(function ($place) {
                return $place->toArray();
            }),
            'transitions' => collect($this->transitions())->map(function ($transition) {
                return $transition->toArray();
            })
        ];
    }
    
    public function placesOptionArray()
    {
        return $this->toArray()['places']->mapWithKeys(
            function ($place) {
                return [array_key_first($place) => head($place)['metadata']['title']];
            }
        );
    }
    

    public function place($placeName)
    {
        return collect($this->places())->where('name', $placeName)->first();
    }

    public function transition($transitionName)
    {
        return collect($this->transitions())->where('name', $transitionName)->first();
    }
}
