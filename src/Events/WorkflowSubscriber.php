<?php

namespace Orlyapps\NovaWorkflow\Events;

use Orlyapps\NovaWorkflow\Models\Log;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent as SymfonyGuardEvent;

class WorkflowSubscriber implements EventSubscriberInterface
{
    public function guardEvent(SymfonyGuardEvent $event)
    {
        $workflowName = $event->getWorkflowName();
        $transitionName = $event->getTransition()->getName();

        $object = $event->getSubject();
        $user = \Auth::user();
        $policyName = \Str::camel($transitionName);
        if ($user) {
            $event->setBlocked(!$user->can($policyName, $object));
        } else {
            $event->setBlocked(false);
        }

        event('workflow.guard', $event);
        event(sprintf('workflow.%s.guard', $workflowName), $event);
        event(sprintf('workflow.%s.guard.%s', $workflowName, $transitionName), $event);
    }

    public function leaveEvent(Event $event)
    {
        $places = $event->getTransition()->getFroms();
        $workflowName = $event->getWorkflowName();

        event('workflow.leave', $event);
        event(sprintf('workflow.%s.leave', $workflowName), $event);

        foreach ($places as $place) {
            event(sprintf('workflow.%s.leave.%s', $workflowName, $place), $event);
        }
    }

    public function transitionEvent(Event $event)
    {
        $workflowName = $event->getWorkflowName();
        $transitionName = $event->getTransition()->getName();

        event('workflow.transition', $event);
        event(sprintf('workflow.%s.transition', $workflowName), $event);
        event(sprintf('workflow.%s.transition.%s', $workflowName, $transitionName), $event);
    }

    public function enterEvent(Event $event)
    {
        $places = $event->getTransition()->getTos();
        $workflowName = $event->getWorkflowName();

        event('workflow.enter', $event);
        event(sprintf('workflow.%s.enter', $workflowName), $event);

        foreach ($places as $place) {
            event(sprintf('workflow.%s.enter.%s', $workflowName, $place), $event);
        }
    }

    public function enteredEvent(Event $event)
    {
        $places = $event->getTransition() ? $event->getTransition()->getTos() : [];
        $workflowName = $event->getWorkflowName();
        $transitionName = $event->getTransition()->getName();
        $object = $event->getSubject();
        $definition = $object->getWorkflowDefinition();

        if ($transitionName == null) {
            $transitionName = $definition->initialPlace;
        }

        // $object->status_changed_at = new \DateTime();
        // $object->save();

        $to = $event->getTransition()->getTos();
        $from = $event->getTransition()->getFroms();

        $log = new Log();
        $log->fill(['from' => $from[0], 'to' => $to[0], 'transition' => $transitionName]);
        $log->subject()->associate($object);

        if (\Auth::user()) {
            $log->causer()->associate(\Auth::user());
        }

        // Fälligkeit setzen: Heute + Due In aus Workflow Definition
        $place = $definition->place($to[0]);
        if ($place->dueIn) {
            $log->due_at = (new \DateTime())->add(\DateInterval::createFromDateString($place->dueIn));
        }

        $log->save();

        // Observer Events werden aufgerufen
        $method = \Str::camel($transitionName);
        $object->fire($method);

        event('workflow.entered', $event);
        event(sprintf('workflow.%s.entered', $workflowName), $event);

        foreach ($places as $place) {
            event(sprintf('workflow.%s.entered.%s', $workflowName, $place), $event);
        }
    }

    public function completedEvent(Event $event)
    {
        $workflowName = $event->getWorkflowName();
        $transitionName = $event->getTransition()->getName();

        event('workflow.completed', $event);
        event(sprintf('workflow.%s.completed', $workflowName), $event);
        event(sprintf('workflow.%s.completed.%s', $workflowName, $transitionName), $event);
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.guard' => ['guardEvent'],
            'workflow.leave' => ['leaveEvent'],
            'workflow.transition' => ['transitionEvent'],
            'workflow.enter' => ['enterEvent'],
            'workflow.entered' => ['enteredEvent'],
            'workflow.completed' => ['completedEvent'],
        ];
    }
}
