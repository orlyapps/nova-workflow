<?php

namespace Orlyapps\NovaWorkflow\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Makeable;

class WorkflowTransisitionAction extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use Makeable;

    /**
     * Indicates if this action is available on the resource's table row.
     *
     * @var bool
     */
    public $showOnTableRow = true;

    public $workflowClass = null;

    public function __construct($workflowClass)
    {
        $this->workflowClass = $workflowClass;
    }

    public function uriKey()
    {
        return 'status-change';
    }

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Status ändern';

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
            if (! $model->workflow_can($fields->transition)) {
                return Action::danger('Dieser Statuswechsel aus dem aktuellen Status ist nicht möglich');
            }
            $model->workflow_apply($fields->transition)->save();
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Select::make('Status', 'transition')
                ->options($this->workflowClass::make()->transistionsOptionArray()),
        ];
    }
}
