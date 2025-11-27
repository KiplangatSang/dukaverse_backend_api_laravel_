<?php
namespace App\Models;

use App\Casts\JsonCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    const DEFAULT_COLORS = [
        "background_color" => "#000000",
        'color'            => "#00FF00",
    ];

    const PROJECT_PRIORITIES = ['low', 'medium', 'high'];

    const PROJECT_DEFAULTS = [
        "color"  => "#00FF00",
        'avatar' => "https://storage.googleapis.com/dukaverse-e4f47.appspot.com/app/nofile.png",
    ];

    const ONGOING     = 'ongoing';
    const FINISHED    = 'finished';
    const NOT_STARTED = 'pending';
    const OVERDUE     = 'overdue';

    const PROJECT_STATUS = [
        self::NOT_STARTED,
        self::ONGOING,
        self::FINISHED,
        self::OVERDUE,
    ];

    protected $casts = [
        'colors' => JsonCast::class,
    ];

    protected $appends = ['progress'];

    public function getProgressAttribute()
    {
        return $this->progress() ? $this->progress() : 0;
    }

    public function progress()
    {
        $totalTasks = $this->tasks()->count();

        if ($totalTasks === 0) {
            return 0; // Avoid division by zero
        }

        $completedTasks = $this->tasks()->where('status', 'completed')->count();

        return round(($completedTasks / $totalTasks) * 100, 2); // Returns progress in percentage
    }

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function projectable()
    {
        return $this->morphTo();
    }

    public function media()
    {
        return $this->morphMany(Medium::class, 'mediumable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->whereNull('parent_id')->with('replies');
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function teams()
    {
        return $this->morphToMany(Team::class, 'teamables');
    }
    public function members()
    {

        return $this->hasManyThrough(
            User::class,   // The related model
            Team::class,   // The intermediate model
            'teamable_id', // Foreign key on the teams table
            'id',          // Foreign key on the users table
            'id',          // Local key on the projects table
            'user_id'      // Local key on the teams table
        )->where('teamable_type', Project::class);

    }

}
