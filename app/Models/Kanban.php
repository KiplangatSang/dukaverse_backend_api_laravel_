<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kanban extends Model
{
    /** @use HasFactory<\Database\Factories\KanbanFactory> */
    use HasFactory, SoftDeletes;
}
