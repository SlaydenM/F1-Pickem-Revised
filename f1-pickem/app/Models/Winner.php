<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Winner extends Model
{
    // The columns in your NEW f1db2 schema
    protected $fillable = ['name', 'team', 'number', 'position', 'session_key', 'path'];
}
