<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id int
 * @property $user_id int
 * @property $access_token string
 * @property $refresh_token string
 * @property $expires_at datetime
 * @property $user User
 */
class PatreonData extends Model
{
    protected $table = 'patreon_data';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function user()
    {
        return $this->hasOne('App\Models\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function tiers()
    {
        return $this->hasMany('App\Models\PatreonTier');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    function paidtiers()
    {
        return $this->belongsToMany('App\Models\PaidTier', 'patreon_tiers');
    }

    public static function boot()
    {
        parent::boot();

        // Delete route properly if it gets deleted
        static::deleting(function ($item) {
            PatreonTier::where('patreon_data_id', '=', $item->id)->delete();
        });
    }
}
