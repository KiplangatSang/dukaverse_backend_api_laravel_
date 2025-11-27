<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskDependancy extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    const TASK_DEPENDENCIES = ['FS', 'SS', 'FF', 'SF'];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function taskables()
    {
        return $this->morphTo();

    }

    public function task()
    {
        return $this->belongsTo(Task::class, "task_id");

    }

    public function dependedTask()
    {
        return $this->belongsTo(Task::class, "depends_on");

    }

    public function project()
    {
        return $this->belongsTo(Project::class, "project_id");

    }
}
