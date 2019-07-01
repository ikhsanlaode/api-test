<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Item extends Model
{
	public function getCreatedAtAttribute()
    {
        return  Carbon::parse($this->attributes['created_at'])->toIso8601String();
    }

    public function getUpdatedAtAttribute()
    {
        return  Carbon::parse($this->attributes['updated_at'])->toIso8601String();
    }
}
