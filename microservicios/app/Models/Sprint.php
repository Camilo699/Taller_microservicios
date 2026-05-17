<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    protected $table = 'sprints';
    public $timestamps = true;

    public function retroItems()
    {
        return $this->hasMany(RetroItem::class, 'sprint_id');
    }
}