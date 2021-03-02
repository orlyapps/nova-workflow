<?php

namespace Orlyapps\NovaWorkflow\Traits;

use App\User;
use Orlyapps\NovaWorkflow\Models\Log;
use Workflow;

trait HasWorkflow
{
    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getObservableEvents()
    {
        $reflection = new \ReflectionClass(get_called_class());
        $definition = \Workflow::getDefinitionForClass($reflection->getName());
        $observables = collect($definition->transitions())->map(function ($transition) {
            return \Str::camel($transition->name);
        });
        return array_merge(
            array_merge($this->observables, parent::getObservableEvents()),
            $observables->toArray()
        );
    }

    public function fire($event)
    {
        $this->fireModelEvent($event, false);
    }

    public function workflow_apply($transition, $workflow = null)
    {
        if ($this->workflow_can($transition)) {
            Workflow::get($this, $workflow)->apply($this, $transition);
        }

        return $this;
    }

    public function workflow_can($transition, $workflow = null)
    {
        return Workflow::get($this, $workflow)->can($this, $transition);
    }

    public function workflow_transitions()
    {
        return Workflow::get($this)->getEnabledTransitions($this);
    }

    public function getStatusColorAttribute()
    {
        return data_get($this->statusColors(), $this->status);
    }

    public function getStatusLabelAttribute()
    {
        return data_get($this->statusLabels(), $this->status);
    }

    public static function statusColors()
    {
        // $this klappt nicht weil es ein Trait ist
        $reflection = new \ReflectionClass(get_called_class());
        $definition = \Workflow::getDefinitionForClass($reflection->getName());

        $status = [];
        foreach ($definition->places() as $place) {
            $status[$place->name] = $place->color;
        }
        return $status;
    }

    public static function statusLabels()
    {
        // $this klappt nicht weil es ein Trait ist
        $reflection = new \ReflectionClass(get_called_class());
        $definition = \Workflow::getDefinitionForClass($reflection->getName());

        $status = [];
        foreach ($definition->places() as $place) {
            $status[$place->name] = $place->label;
        }
        return $status;
    }

    public static function getColorMappingForStatus()
    {
        $reflection = new \ReflectionClass(get_called_class());
        $definition = \Workflow::getDefinitionForClass($reflection->getName());

        $colors = [];
        foreach ($definition->places() as $place) {
            $colors[$place->label] = 'var(--' . $place->color . ')';
        }
        return $colors;
    }

    public function logComment(User $user, $comment)
    {
        $log = Log::create(['comment' => $comment]);
        $log->subject()->associate($this);
        $log->causer()->associate($user);
        $log->save();
    }

    /**
     * Logged den aktuellen Status vom Object in die activity Log
     */
    public function logStatus()
    {
        $reflection = new \ReflectionClass(get_called_class());

        $definition = \Workflow::getDefinitionForClass($reflection->getName());

        if ($this->status == null) {
            $this->status = $definition->initialPlace;
        }

        $log = new Log(['to' => $this->status]);
        $log->subject()->associate($this);

        if (\Auth::user()) {
            $log->causer()->associate(\Auth::user());
        }
        $log->save();
    }

    /**
     * Gibt die Workflow Configuration zurück
     */
    public function getWorkflowDefinition()
    {
        $reflection = new \ReflectionClass($this);
        $definition = \Workflow::getDefinitionForClass($reflection->getName());
        return $definition;
    }

    public function getWorkflowMetadataForStatus()
    {
        $definition = $this->getWorkflowDefinition();
        return $definition->place($this->status)->metadata();
    }

    public function getDueInDiffForHumansAttribute()
    {
        return optional($this->dueIn)->diffForHumans();
    }

    public function getDueInAttribute()
    {
        return optional($this->lastLog)->due_at;
    }

    public function getLastLogAttribute()
    {
        return Log::forSubject($this)->whereNotNull('to')->orderByDesc('created_at')->first();
    }

    public function getLastLogCommentAttribute()
    {
        return Log::forSubject($this)->whereNotNull('comment')->orderByDesc('created_at')->first();
    }
}
