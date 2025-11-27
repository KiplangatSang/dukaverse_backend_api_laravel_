<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    const COMMENTABLES = [
        "project_comments"  => "project_comments",
        "task_comments"     => "task_comments",
        "campaign_comments" => "campaign_comments",
    ];

    const COMMENTABLE_TYPES = [
        "project_comments"  => Project::class,
        "task_comments"     => Task::class,
        "campaign_comments" => Campaign::class,
    ];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('replies.user'); // Recursive replies
    }

    public function media()
    {
        return $this->morphMany(Medium::class, 'mediumable');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); // Recursive replies
    }

}
