<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = ['id', 'name', 'team', 'number', 'year', 'primary_color', 'secondary_color'];

    public function getPath(): string
    {
        return route('private.image', [
            'year'     => $this->year,
            'filename' => $this->getFileName(),
        ]);
    }

    public function getFileName(): string
    {
        return 'f1_' . $this->number . '.png';
    }
}
