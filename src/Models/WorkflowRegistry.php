<?php

namespace Orlyapps\NovaWorkflow\Models;

use Orlyapps\NovaWorkflow\Events\WorkflowSubscriber;
use Orlyapps\NovaWorkflow\SupportStrategy\ClassSupportStrategy;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Metadata\InMemoryMetadataStore;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

class WorkflowRegistry
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var array
     */
    protected $definitions = [];

    /**
     * WorkflowRegistry constructor
     *
     * @param  array $config
     * @param  array $registryConfig
     * @throws \ReflectionException
     */
    public function __construct()
    {
        $this->registry = new Registry();
        $this->dispatcher = new EventDispatcher();

        $subscriber = new WorkflowSubscriber();
        $this->dispatcher->addSubscriber($subscriber);
    }

    /**
     * Return the $subject workflow
     *
     * @param  object $subject
     * @param  string $workflowName
     * @return Workflow
     */
    public function get($subject, $workflowName = null)
    {
        return $this->registry->get($subject, $workflowName);
    }

    /**
     * Return the workflow definition for name
     *
     * @param  object $subject
     * @param  string $workflowName
     * @return Workflow
     */
    public function getDefinitionForClass($class)
    {
        foreach ($this->definitions as $definition) {
            if (in_array($class, $definition->supports)) {
                return $definition;
            }
        }
    }

    /**
     * Returns all workflows for the given subject
     *
     * @param object $subject
     *
     * @return Workflow[]
     */
    public function all($subject): array
    {
        return $this->registry->all($subject);
    }

    /**
     * Add a workflow to the registry from array
     *
     * @param  string $name
     * @param  array  $workflowData
     * @throws \ReflectionException
     *
     * @return void
     */
    public function add(WorkflowDefinition $workflow)
    {
        $this->definitions[$workflow->name] = $workflow;
        $workflowData = $workflow->toArray();

        $places = $workflowData['places']->map(function ($place) {
            return key($place);
        })->toArray();

        $builder = new DefinitionBuilder($places);

        $transitionsMetadata = new \SplObjectStorage;

        foreach ($workflowData['transitions'] as $transitionData) {
            $transition = $transitionData[key($transitionData)];

            foreach ($transition['from'] as $form) {
                $transitionObj = new Transition(key($transitionData), $form, $transition['to']);

                $builder->addTransition($transitionObj);
                if (isset($transition['metadata'])) {
                    $transitionsMetadata->attach($transitionObj, $transition['metadata']);
                }
            }
        }
        $placesMetadata = $workflowData['places']->flatMap(function ($place) {
            return [key($place) => $place[key($place)]['metadata']];
        })->toArray();

        $metadataStore = new InMemoryMetadataStore(
            [],
            $placesMetadata,
            $transitionsMetadata
        );

        $builder->setMetadataStore($metadataStore);

        if (isset($workflow->initialPlace)) {
            $builder->setInitialPlaces($workflow->initialPlace);
        }

        $definition = $builder->build();
        $markingStore = new MethodMarkingStore(true, config('workflow.marking_store_field'));
        $workflowObj = new Workflow($definition, $markingStore, $this->dispatcher, $workflow->name);

        foreach ($workflow->supports as $supportedClass) {
            $supportStrategy = $workflow->supportStrategy();
            $this->registry->addWorkflow($workflowObj, new $supportStrategy($supportedClass));
        }
    }
}
