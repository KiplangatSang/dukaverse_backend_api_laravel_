<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    const TASK_PRIORITIES = ['low', 'medium', 'high'];
    const TASK_DEFAULTS   = [
        "color"  => "#00FF00",
        'avatar' => "https://storage.googleapis.com/dukaverse-e4f47.appspot.com/app/nofile.png",
    ];

    const ONGOING     = 'ongoing';
    const FINISHED    = 'completed';
    const NOT_STARTED = 'pending';
    const OVERDUE     = 'overdue';

    const TASK_STATUS = [
        self::NOT_STARTED,
        self::ONGOING,
        self::FINISHED,
        self::OVERDUE,

    ];

    const KANBAN_COLUMNS = [
        ["id" => 1, "title" => self::NOT_STARTED, "tasks" => []],

        ["id" => 2, "title" => self::ONGOING, "tasks" => []],

        ["id" => 3, "title" => self::FINISHED, "tasks" => []],
    ];

    protected $casts = [
    ];

    public function ownerable()
    {
        return $this->morphTo();
    }
    public function taskable()
    {
        return $this->morphTo();
    }

    public function campaign()
    {
        return $this->taskable();
    }

    public function project()
    {
        return $this->taskable();

    }

    public function media()
    {
        return $this->morphMany(Medium::class, 'mediumable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->whereNull('parent_id')->with('replies');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subTasks()
    {
        return $this->hasMany(Task::class, 'parent_id'); // Recursive replies
    }

    public function dependencies()
    {
        return $this->hasMany(TaskDependancy::class, "task_id");
    }

    public function teams()
    {
        return $this->morphedByMany(Task::class, 'teamables');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, "task_assignees", "task_id", "assignee_id");
    }

    public function calendarEvents()
    {
        return $this->hasMany(Calendar::class);
    }

}
