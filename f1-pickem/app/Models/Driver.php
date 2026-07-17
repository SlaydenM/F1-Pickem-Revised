<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    // The columns in your NEW f1db2 schema
    protected $fillable = ['id', 'name', 'team', 'number', 'year'];

    public function getPath()
    {
        return route('private.image', [
            'year' => $this->year,
            'filename' => $this->getFileName(),
        ]);
    }

    public function getFileName()
    {
        return 'f1_' . $this->number . '.png';
    }
}
