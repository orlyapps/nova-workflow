<?php

namespace Orlyapps\NovaWorkflow\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Nova\Nova;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $userResource = new \ReflectionProperty(Nova::resourceForKey(config('workflow.activity_user_resource')), 'title');
        $titleField = $userResource->getValue(null);

        $user = optional($this->causer)->$titleField ?? 'System';

        $definition = $this->subject->getWorkflowDefinition();

        $data = [];
        if ($this->to) {
            $data['status'] = $definition->place($this->to)->metadata();
        }

        if ($this->transition) {
            $data['transition'] = optional($definition->transition($this->transition))->metadata();
        }

        return [
            'id' => $this->id,
            'user' => [
                'fullname' => $user,
            ],
            'comment' => $this->comment,
            'data' => count($data) === 0 ? null : $data,
            'created_at' => $this->created_at->format('d.m.Y H:i')
        ];
    }
}
