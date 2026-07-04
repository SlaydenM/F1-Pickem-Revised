<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pick extends Model
{
    // The columns in your revised f1db2 schema
    protected $fillable = ['user_id', 'score', 'd1_id', 'd2_id', 'd3_id', 'bonus', 'session_key'];
}
