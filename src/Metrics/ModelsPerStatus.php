<?php

namespace Orlyapps\NovaWorkflow\Metrics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;

class ModelsPerStatus extends Partition
{
    public $resourceClass = null;

    public function before(Builder $query)
    {
        return $query;
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $offerModel = new $this->resourceClass::$model();
        $workflowDefinition = $offerModel->getWorkflowDefinition();
        $query = $this->resourceClass::indexQuery($request, $this->resourceClass::$model::query());
        $query = $this->before($query);
        /**
         * In der Index Query wird ein OrderBy gesetzt - das darf beim GROUP nicht sein
         * Darum wird der Default Order hart genulled
         */
        $query->getQuery()->orders = null;

        return $this->count(
            $request,
            $query,
            'status'
        )->label(function ($value) use ($workflowDefinition) {
            return $workflowDefinition->place($value)->label;
        })->colors($this->resourceClass::$model::getColorMappingForStatus());
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'models-per-status-' . class_basename($this->resourceClass);
    }

    public function resourceClass($resourceClass)
    {
        $this->resourceClass = $resourceClass;
        return $this;
    }

    public function title($name)
    {
        $this->name = $name;
        return $this;
    }
}
