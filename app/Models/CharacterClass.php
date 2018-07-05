<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property \Illuminate\Support\Collection $specializations
 */
class CharacterClass extends Model
{
    public $timestamps = false;

    function specializations()
    {
        return $this->hasMany('App\Models\CharacterSpecialization');
    }

    function race()
    {
        return $this->belongsToMany('App\Models\CharacterRace', 'character_race_class_couplings');
    }
}
