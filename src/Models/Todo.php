<?php

namespace Orlyapps\NovaWorkflow\Models;

abstract class Todo
{
    public $group = 'Default';

    public function todos()
    {
        return [];
    }
}
