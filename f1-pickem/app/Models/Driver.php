<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    // The columns in your NEW f1db2 schema
    protected $fillable = ['name', 'team', 'number', 'year'];
}
