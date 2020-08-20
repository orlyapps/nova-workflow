<?php

namespace Orlyapps\NovaWorkflow\Models;

class Place
{
    public $label;

    public $name;

    public $color;

    public $dueIn;

    public function __construct(string $label, string $name)
    {
        $this->label = $label;
        $this->name = $name;
    }

    public static function make(string $label, string $name)
    {
        return new static($label,$name);
    }

    public function dueIn($dueIn)
    {
        $this->dueIn = $dueIn;
        return $this;
    }

    public function color($color)
    {
        $this->color = $color;
        return $this;
    }

    public function toArray()
    {
        return [
            $this->name => [
                'metadata' => $this->metadata()
            ]
        ];
    }

    public function metadata()
    {
        return [
            'title' => $this->label,
            'color' => $this->color,
            'dueIn' => $this->dueIn
        ];
    }
}
