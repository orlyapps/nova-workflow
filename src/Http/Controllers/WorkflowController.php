<?php

namespace Orlyapps\NovaWorkflow\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Nova\Nova;
use Orlyapps\NovaWorkflow\Http\Resources\UserResource;

class WorkflowController
{
    use AuthorizesRequests;

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $model = $this->getModelForResource($request->resourceName, $request->resourceId);
        return $this->getWorkflowForModel($model);
    }

    public function update(Request $request, $logId)
    {
        $request->validate([
            'dueAt' => 'nullable|date_format:d.m.Y|after:today'
        ]);

        $model = $this->getModelForResource($request->resourceName, $request->resourceId);

        $this->authorize('changeDue', $model);

        $log = config('workflow.log_model')::find($logId);

        $commentLog = config('workflow.log_model')::create();
        $commentLog->subject()->associate($model);
        $commentLog->causer()->associate(\Auth::user());

        if (empty($request->dueAt)) {
            $log->due_at = null;
            $commentLog->comment = __('Hat die Fälligkeit entfernt');
        } else {
            $log->due_at = \DateTime::createFromFormat('d.m.Y', $request->dueAt);
            $commentLog->comment = __('Has adjusted the maturity to :date', ['date' => $log->due_at->format('d.m.Y')]);
        }
        $log->save();
        $commentLog->save();
        return $this->getWorkflowForModel($model);
    }

    private function getModelForResource($resourceName, $resourceId)
    {
        $resource = Nova::resourceInstanceForKey($resourceName);
        return $resource->model()->newQueryWithoutScopes()->find($resourceId);
    }

    private function getWorkflowForModel($model)
    {
        $policy = \Gate::getPolicyFor($model);

        $workflow = \Workflow::get($model, Str::lower(class_basename($model)));

        $definition = \Workflow::getDefinitionForClass(get_class($model));

        $place = $definition->place($model->status);

        if ($place == null) {
            return null;
        }
        $metadataStore = $workflow->getMetadataStore();
        $placeMetadata = $place->metadata();

        $placeMetadata['responsibleUsers'] = UserResource::collection($definition->users($model, $model->status));
        $placeMetadata['lastLog'] = $model->lastLog;
        $placeMetadata['can'] = [
            'changeDue' => \Auth::user()->can('changeDue', $model)
        ];

        if ($model->dueIn) {
            $placeMetadata['dueAt'] = $model->dueIn->format('d.m.Y');
            $placeMetadata['dueIn'] = $model->dueInDiffForHumans;
            $placeMetadata['duePast'] = (new \DateTime() > $model->dueIn);
        } else {
            unset($placeMetadata['dueIn']);
        }

        $transitionsArray = $workflow->getEnabledTransitions($model);

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
                $canSee = \Auth::user()->can($policyName, $model);
            }

            if ($canSee) {
                $transitions[] = [
                    'name' => $transitionName,
                    'title' => $metadata['title'],
                    'action' => Arr::get($metadata, 'action', null),
                    'userInteraction' => Arr::get($metadata, 'userInteraction', true),
                ];
            }
        }

        return array_merge($placeMetadata, [
            'transitions' => collect($transitions)
        ]);
    }
}
