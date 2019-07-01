<?php

namespace App;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    //protected $casts = ['checklist' => 'array', 'items' => 'array'];

    public function getCreatedAtAttribute()
    {
        return  Carbon::parse($this->attributes['created_at'])->toIso8601String();
    }

    public function getUpdatedAtAttribute()
    {
        return  Carbon::parse($this->attributes['updated_at'])->toIso8601String();
    }
}
