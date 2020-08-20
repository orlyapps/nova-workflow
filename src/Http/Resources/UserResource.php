<?php

namespace Orlyapps\NovaWorkflow\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Nova\Nova;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $userResource = new \ReflectionProperty(Nova::resourceForKey('users'), 'title');
        $titleField = $userResource->getValue(null);

        return [
            'id' => $this->id,
            'name' => $this->$titleField
        ];
    }
}
