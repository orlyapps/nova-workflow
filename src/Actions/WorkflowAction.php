<?php

namespace Orlyapps\NovaWorkflow\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;

class WorkflowAction extends Action
{
    public $name = 'Status Change';

    /**
     * Get the URI key for the action.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'workflow-status-change';
    }

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $showOnDetail = false;

    /**
     * Indicates if this action is available on the resource index view.
     *
     * @var bool
     */
    public $showOnIndex = false;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $model) {
            $workflow = \Workflow::get($model, \Str::lower(class_basename($model)));
            $workflow->apply($model, request()->transition);
            $model->save();
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('transition')
        ];
    }
}
