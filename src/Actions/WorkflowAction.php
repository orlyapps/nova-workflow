<?php

namespace Orlyapps\NovaWorkflow\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Symfony\Component\Workflow\Exception\TransitionException;
use Throwable;

/**
 * Wendet den Übergang an, den die Person in der Workflow-Card angeklickt hat.
 *
 * Die Card schreibt den Übergang als `?transition=` in die Seiten-URL und löst danach die
 * zugehörige Action aus; hier kommt er über den Referer an. Ohne Card gilt der Übergang,
 * an den die Action per `uriKey()` gebunden ist (`Transition::action()`).
 *
 * Unterklassen mit eigener Fachlogik überschreiben {@see applyTransition()} statt
 * `handle()` – Übergangs-Ermittlung, Schleife über alle Datensätze und Fehlermeldung
 * bleiben dann hier.
 */
class WorkflowAction extends Action
{
    public $name = 'Status Change';

    public function uriKey()
    {
        return 'workflow-status-change';
    }

    public $showOnDetail = false;

    public $showOnIndex = false;

    /**
     * Der angeklickte Übergang aus der Workflow-Card, `null` ohne Card.
     *
     * @var string|null
     */
    public $transition = null;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $this->transition = $this->clickedTransition();
        $failure = null;

        foreach ($models as $model) {
            try {
                $this->applyTransition($model, $this->transitionFor($model), $fields);
            } catch (Throwable $exception) {
                $failure ??= $exception;
            }
        }

        return $failure === null ? null : $this->transitionFailed($failure);
    }

    /**
     * Der Übergang für diesen Datensatz: angeklickt oder per uriKey gebunden.
     */
    protected function transitionFor(Model $model): ?string
    {
        return $this->transition ?? $model->getTransitionForAction($this);
    }

    /**
     * Standard: nur den Übergang anwenden. Unterklassen ersetzen das durch ihre Fachlogik.
     */
    protected function applyTransition(Model $model, ?string $transition, ActionFields $fields): void
    {
        \Workflow::get($model, Str::lower(class_basename($model)))->apply($model, $transition);
        $model->save();
    }

    /**
     * Ein nicht möglicher Übergang ist ein Bedienfehler; alles andere wird gemeldet. Meldungen von
     * Exceptions aus `workflow.user_exceptions` sind für Nutzer:innen gedacht und werden angezeigt.
     *
     * Bewusst nicht `failed()`: diesen Namen ruft Nova bei Actions in der Queue selbst auf.
     */
    protected function transitionFailed(Throwable $exception): ActionResponse
    {
        if ($this->isUserException($exception)) {
            return Action::danger($exception->getMessage());
        }

        if ($exception instanceof TransitionException) {
            return Action::danger(__('This status change is not possible from the current status.'));
        }

        report($exception);

        return Action::danger(__('The status change failed.'));
    }

    private function isUserException(Throwable $exception): bool
    {
        foreach (config('workflow.user_exceptions', []) as $class) {
            if ($exception instanceof $class) {
                return true;
            }
        }

        return false;
    }

    private function clickedTransition(): ?string
    {
        $query = parse_url((string) request()->headers->get('referer'), PHP_URL_QUERY);
        parse_str((string) $query, $params);

        return data_get($params, 'transition');
    }
}
