<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Checklist extends Model
{
	protected $casts = ['items' => 'json'];

	//protected $hidden = ['items'];
    
    public function items() {
        return $this->hasMany(Item::class,'checklist_id');
    }

    public function getCreatedAtAttribute()
    {
        return  Carbon::parse($this->attributes['created_at'])->toIso8601String();
    }

    public function getUpdatedAtAttribute()
    {
        return  Carbon::parse($this->attributes['updated_at'])->toIso8601String();
    }
}
