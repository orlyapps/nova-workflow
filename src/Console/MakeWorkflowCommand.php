<?php

namespace Orlyapps\NovaWorkflow\Console;

use Illuminate\Console\GeneratorCommand;

class MakeWorkflowCommand extends GeneratorCommand
{
    protected $name = 'make:workflow';

    protected $description = 'Create a new Workflow';

    protected $type = 'Workflow';

    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/workflow.stub');
    }

    public function handle()
    {
        parent::handle();

        $class = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath($class);
        rename($path, dirname($path) . '/' . $this->getNameInput() . "Workflow.php");
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Nova\Workflows';
    }
}
