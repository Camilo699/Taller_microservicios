<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetroItem extends Model
{
    protected $table = 'retro_items';
    public $timestamps = true;

    public function sprint()
    {
        return $this->belongsTo(Sprint::class, 'sprint_id');
    }
}