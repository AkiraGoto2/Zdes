<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = [];

	public function applications(){
		return $this->hasMany(Application::class);
	}

	public function photos(){
		return $this->hasMany(Photo::class);
	}

	public function socials(){
		return $this->hasMany(Socials::class);
	}

	public function user(){
		return $this->belongsTo(User::class);
	}

	public function category(){
		return $this->belongsTo(Category::class);
	}
}
