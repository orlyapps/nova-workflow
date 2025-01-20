<?php

namespace Orlyapps\NovaWorkflow\Models;

use Orlyapps\NovaWorkflow\Actions\WorkflowAction;

class Transition
{
    public $from = [];
    
    public $to = [];
    
    public $action;
    
    public $entered;
    
    public $label;

    public $name;

    protected $userInteraction = true;

    public function __construct(string $label, string $name)
    {
        $this->label = $label;
        $this->name = $name;
        $this->action = (new WorkflowAction())->uriKey();
    }

    public static function make(string $label, string $name)
    {
        return new static($label,$name);
    }

    public function from($place)
    {
        $this->from = $place;
        return $this;
    }

    public function to($place)
    {
        $this->to = $place;
        return $this;
    }

    public function action($action)
    {
        $this->action = $action->uriKey();
        return $this;
    }

    public function noUserInteraction()
    {
        $this->userInteraction = false;
        return $this;
    }

    public function entered()
    {
        return $this;
    }

    public function toArray()
    {
        return [
            $this->name => [
                'from' => $this->from,
                'to' => $this->to,
                'metadata' => $this->metadata(),
            ]
        ];
    }

    public function metadata()
    {
        return [
            'title' => $this->label,
            'action' => $this->action,
            'userInteraction' => $this->userInteraction
        ];
    }
}
