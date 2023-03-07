<?php

namespace Orlyapps\NovaWorkflow\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class StatusFilter extends Filter
{
    /**
     * The displayable name of the filter.
     *
     * @var string
     */
    public $name = 'Status';

    public $defaultStatus = '';

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public function __construct(public $resource, public $whereField = 'status')
    {
    }

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->where($this->whereField, $value);
    }

    public function defaultStatus($status)
    {
        $this->defaultStatus = $status;

        return $this;
    }

    /**
     * Set the default options for the filter.
     *
     * @return array|mixed
     */
    public function default()
    {
        return $this->defaultStatus;
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $model = new $this->resource::$model();

        return array_flip(get_class($model)::statusLabels());
    }
}
