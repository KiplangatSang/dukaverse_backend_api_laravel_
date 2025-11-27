<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CountryList extends Model
{
    /** @use HasFactory<\Database\Factories\CountryListFactory> */
    use HasFactory, SoftDeletes;
}
