<?php

namespace Orlyapps\NovaWorkflow\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Nova\Nova;
use Orlyapps\NovaWorkflow\Http\Resources\ActivityResource;

class LogController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $model = $this->getModelForResource($request->resourceName, $request->resourceId);

        $activities = config('workflow.log_model')::forSubject($model)->orderBy('created_at', 'desc')->get();
        return ActivityResource::collection($activities);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $model = $this->getModelForResource($request->resourceName, $request->resourceId);

        $log = config('workflow.log_model')::create($request->only(['comment']));
        $log->subject()->associate($model);
        $log->causer()->associate(\Auth::user());
        $log->save();

        return ActivityResource::make($log);
    }

    private function getModelForResource($resourceName, $resourceId)
    {
        $resource = Nova::resourceInstanceForKey($resourceName);
        return $resource->model()->newQueryWithoutScopes()->find($resourceId);
    }
}
