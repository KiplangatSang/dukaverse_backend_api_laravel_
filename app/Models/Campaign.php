<?php
namespace App\Models;

use App\Casts\JsonCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model {
    use HasFactory, SoftDeletes;

    use HasFactory;

    protected $guarded      = [];
    const ACTIVE_CAMPAIGN   = "active";
    const CLOSED_CAMPAIGN   = "closed";
    const UPCOMING_CAMPAIGN = "upcoming";
    const ABORTED_CAMPAIGN  = "aborted";

    const CAMPAIGN_STATUS = [
        self::ACTIVE_CAMPAIGN,
        self::CLOSED_CAMPAIGN,
        self::UPCOMING_CAMPAIGN,
        self::ABORTED_CAMPAIGN,
    ];

    const CAMPAIGN_TARGET = [
        'profile'   => Profile::class,
        'sales'     => Sale::class,
        'customers' => Customer::class,

    ];

    const DEFAULT_COLORS = [
        "background_color" => "#000000",
        'color'            => "#00FF00",
    ];

    const CAMPAIGN_DEFAULTS = [
        "colors" => self::DEFAULT_COLORS,
        'avatar' => "https://storage.googleapis.com/dukaverse-e4f47.appspot.com/app/nofile.png",
    ];

    const CAMPAIGN_TYPES = [
        'profile',
        'sales',
        'customers',
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

    public function campaignable()
    {
        return $this->morphTo();
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

    public function leads()
    {
        return $this->morphMany(Lead::class, 'leadable');

    }

    public function deals()
    {
        return $this->leads()->where('status', Lead::WON_LEAD);
    }

    public function newLeads()
    {
        return $this->leads()->where('status', Lead::PENDING_LEAD);
    }

    public function contactedLeads()
    {
        return $this->leads()->where('is_contacted', true);
    }

    public function reachedLeads()
    {
        return $this->leads()->where('is_replied', true);
    }

    public function nonReplyingLeads()
    {
        return $this->leads()->where('is_contacted', true)->where('is_replied', false);
    }

    public function coldLeads()
    {
        return $this->leads()->where('status', Lead::COLD_LEAD);
    }

    public function revenues()
    {
        # code...
        return $this->morphMany(Revenue::class, 'revenueable');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');

    }
    public function tasks()
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->whereNull('parent_id')->with('replies');
    }
}
