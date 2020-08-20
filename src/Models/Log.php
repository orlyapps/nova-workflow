<?php

namespace Orlyapps\NovaWorkflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Log extends Model
{
    protected $table = 'workflow_log';
    protected $fillable = [
        'from', 'to', 'transition', 'comment', 'due_in'
    ];
    protected $dates = [
      'created_at', 'updated_at', 'due_at'
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeCausedBy($query, $causer)
    {
        return $query
            ->where('causer_type', $causer->getMorphClass())
            ->where('causer_id', $causer->getKey());
    }

    public function scopeForSubject($query, $subject)
    {
        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }

    public function resetDue()
    {
        $this->due_at = null;
        $this->save();
    }

    public function copyTo($subject)
    {
        $new = $this->replicate();
        $new->subject()->associate($subject);
        $new->save();
    }
}
