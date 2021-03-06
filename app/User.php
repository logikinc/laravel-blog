<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;
use App\Role;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'provider', 'provider_id', 'registered_at'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'registered_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Scope a query to only include users registered last week.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLastWeek($query)
    {
        return $query->whereBetween('registered_at', [Carbon::now()->subWeek(), Carbon::now()])
                     ->orderBy('registered_at', 'desc');
    }

    /**
    * Check if the user has a role
    *
    * @param string $role
    * @return boolean
    */
    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
    * Check if the user has role admin
    *
    * @return boolean
    */
    public function isAdmin()
    {
        return $this->hasRole(Role::ROLE_ADMIN);
    }

    /**
    * Return the user's posts
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function posts()
    {
        return $this->hasMany('App\Post', 'author_id');
    }

    /**
    * Return the user's comments
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function comments()
    {
        return $this->hasMany('App\Comment', 'author_id');
    }

    /**
    * Return the user's roles
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function roles()
    {
        return $this->belongsToMany('App\Role')->withTimestamps();
    }
}
