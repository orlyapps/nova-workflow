<?php

namespace Orlyapps\NovaWorkflow\Http\Controllers;

use Illuminate\Http\Request;

class TodoController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $providers = explode(',', $request->providers);
        $todos = [];
        foreach ($providers as $provider) {
            $object = new $provider();
            if ($object->todos()->count() == 0) {
                continue;
            }
            if (!isset($todos[$object->group])) {
                $todos[$object->group] = [];
            }

            $updatedTodos = $this->updateDueDate($object->todos()->sortBy('dueIn'))->toArray();

            $todos[$object->group] = array_merge($todos[$object->group], $updatedTodos);
        }

        return $todos;
    }

    private function updateDueDate($todos)
    {
        return $todos->map(function ($todo) {
            if ($todo['dueIn']) {
                $todo['duePast'] = (new \DateTime() > $todo['dueIn']);
                $todo['dueFormatted'] = $todo['dueIn']->diffForHumans();
            }
            return $todo;
        });
    }
}
