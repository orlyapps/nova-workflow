<?php

namespace Orlyapps\NovaWorkflow\Models;

class Place
{
    public $label;

    public $name;

    public $color;

    public $dueIn;

    public $emoji;

    public $externalLabel;

    public $externalColor;

    public $description;

    public function __construct(string $label, string $name)
    {
        $this->label = $label;
        $this->name = $name;
    }

    public static function make(string $label, string $name)
    {
        return new static($label,$name);
    }

    public function externalLabel($externalLabel)
    {
        $this->externalLabel = $externalLabel;

        return $this;
    }

    public function dueIn($dueIn)
    {
        $this->dueIn = $dueIn;

        return $this;
    }

    public function description($description)
    {
        $this->description = $description;

        return $this;
    }

    public function color($color)
    {
        $this->color = $color;

        return $this;
    }

    public function emoji($emoji)
    {
        $this->emoji = $emoji;

        return $this;
    }

    public function externalColor($externalColor)
    {
        $this->externalColor = $externalColor;

        return $this;
    }

    public function toArray()
    {
        return [
            $this->name => [
                'metadata' => $this->metadata(),
            ],
        ];
    }

    public function metadata()
    {
        return [
            'title' => $this->label,
            'description' => $this->description,
            'color' => $this->color,
            'emoji' => $this->emoji,
            'dueIn' => $this->dueIn,
            'externalLabel' => $this->externalLabel,
            'externalColor' => $this->externalColor,
        ];
    }
}
