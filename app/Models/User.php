<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    
    use HasFactory, Notifiable;

    
    protected $fillable = [
        'name',
        'lastname',
        'tel',
        'email',
        'password',
        'avatar',
    ];

    
    protected $hidden = [
        'password',
        'remember_token',
    ];

    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

	public function applications(){
		return $this->hasMany(Application::class);
	}

	public function events(){
		return $this->hasMany(Event::class);
	}

	public function notifications(){
		return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
	}

	public function isAdmin(): bool
	{
		return $this->role === 'admin';
	}
}
