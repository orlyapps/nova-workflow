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

    public function logComment($user, $comment)
    {
        $log = config('workflow.log_model')::create(['comment' => $comment]);
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
        $logModelClass = config('workflow.log_model');
        $log = new $logModelClass(['to' => $this->status]);
        $log->subject()->associate($this);

        if (\Auth::user()) {
            $log->causer()->associate(\Auth::user());
        }
        $log->save();
    }

    public function getCurrentPlace()
    {
        return $this->getPlace($this->status);
    }

    public function getPlace($place)
    {
        $definition = $this->getWorkflowDefinition();
        $meta = $definition->place($place)->metadata();

        return [
            'name' => $place,
            'title' => $meta['title'],
            'emoji' => $meta['emoji'],
            'color' => $meta['color'],
            'description' => $meta['description'],
            'externalLabel' => $meta['externalLabel'] ?? $meta['title'],
            'externalColor' => $meta['externalColor'] ?? $meta['color'],
        ];
    }

    public function getTransitions()
    {
        $policy = \Gate::getPolicyFor($this);

        $transitionsArray = $this->workflow_transitions();
        $metadataStore = Workflow::get($this)->getMetadataStore();

        $transitions = [];
        foreach ($transitionsArray as $transition) {
            $transitionName = $transition->getName();
            $metadata = $metadataStore->getTransitionMetadata($transition);

            $policyName = \Str::camel('can_see_' . $transitionName);
            $policyExists = false;
            if ($policy) {
                $policyExists = method_exists($policy, $policyName);
            }

            // Default die actions anzeigen, nur wenn eine Policy existiert prüfen
            $canSee = true;
            if ($policyExists) {
                $canSee = \Auth::user()->can($policyName, $this);
            }

            if ($canSee) {
                $transitions[] = [
                    'name' => $transitionName,
                    'title' => $metadata['title'],
                    'action' => \Arr::get($metadata, 'action', null),
                    'to' => $this->getPlace($transition->getTos()[0]),
                    'userInteraction' => \Arr::get($metadata, 'userInteraction', true),
                ];
            }
        }

        return $transitions;
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
        return config('workflow.log_model')::forSubject($this)->whereNotNull('to')->orderByDesc('created_at')->first();
    }

    public function getLastLogCommentAttribute()
    {
        return config('workflow.log_model')::forSubject($this)->whereNotNull('comment')->orderByDesc('created_at')->first();
    }

    public function getTransitionForAction(\Laravel\Nova\Actions\Action $action)
    {
        $transitions = $this->getTransitions();

        foreach ($transitions as $transition) {
            if ($transition['action'] == $action->uriKey()) {
                return $transition['name'];
            }
        }

        return null;
    }
}
