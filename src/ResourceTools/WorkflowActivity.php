<?php

namespace Orlyapps\NovaWorkflow\ResourceTools;

use Laravel\Nova\ResourceTool;

class WorkflowActivity extends ResourceTool
{
    /**
     * Get the displayable name of the resource tool.
     *
     * @return string
     */
    public function name()
    {
        return 'Workflow';
    }

    /**
     * Get the component name for the resource tool.
     *
     * @return string
     */
    public function component()
    {
        return 'workflow-activity';
    }
}
