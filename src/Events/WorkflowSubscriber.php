<?php

namespace Orlyapps\NovaWorkflow\Events;

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

        $policy = \Gate::getPolicyFor($object);        
        $policyName = \Str::camel($transitionName); 
        $policyExists = false;

        if($policy) {                   
            $policyExists = method_exists($policy, $policyName);    
        }

        if ($user && $policyExists) {
            $event->setBlocked(!$user->can($policyName, $object));
        } else {
            $event->setBlocked(false);
        }

        event('workflow.guard', $event);
        event(sprintf('workflow.%s.guard', $workflowName), $event);
        event(sprintf('workflow.%s.guard.%s', $workflowName, $transitionName), $event);
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

        $logModelClass = config('workflow.log_model');
        $log = new $logModelClass();
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
        // only save when a place change
        if (optional($object->lastLog)->to !== $to[0]) {
            $log->save();
            event('nova-workflow.entered', $log);
            event(sprintf('nova-workflow.%s.entered', $workflowName), $log);
            // Observer Events werden aufgerufen
            $method = \Str::camel($transitionName);
            $object->fire($method);
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

            'workflow.entered' => ['enteredEvent'],
        ];
    }
}
