<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @property $id int The ID of this DungeonRoute.
 * @property $author_id int
 * @property $dungeon_id int
 * @property $faction_id int
 * @property $title string
 */
class DungeonRoute extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dungeon()
    {
        return $this->belongsTo('App\Models\Dungeon');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function faction()
    {
        return $this->belongsTo('App\Models\Faction');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function races()
    {
        return $this->belongsToMany('App\Models\CharacterRace', 'dungeon_route_player_races');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function classes()
    {
        return $this->belongsToMany('App\Models\CharacterClass', 'dungeon_route_player_classes');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function affixgroups()
    {
        return $this->hasMany('App\Models\DungeonRouteAffixGroup');
    }

    /**
     * Saves this DungeonRoute with information from the passed Request.
     * 
     * @param Request $request
     * @return bool
     */
    public function saveFromRequest(Request $request)
    {
        $result = false;

        // Overwrite the author_id if it's not been set yet
        if (!isset($this->id)) {
            $this->author_id = \Auth::user()->id;
        }
        
        $this->dungeon_id = $request->get('dungeon_id', $this->dungeon_id);
        $this->faction_id = $request->get('faction_id', $this->faction_id);
        $this->title = $request->get('title', $this->title);

        // Update or insert it
        if ($this->save()) {
            $newRaces = $request->get('race', array());

            if (!empty($newRaces)) {
                // Remove old races
                $this->races()->delete();

                // We don't _really_ care if this doesn't get saved properly, they can just set it again when editing.
                foreach ($newRaces as $key => $value) {
                    $drpRace = new DungeonRoutePlayerRace();
                    $drpRace->index = $key;
                    $drpRace->character_race_id = $value;
                    $drpRace->dungeon_route_id = $this->id;
                    $drpRace->save();
                }
            }

            $newClasses = $request->get('class', array());
            if (!empty($newClasses)) {
                // Remove old classes
                $this->classes()->delete();
                foreach ($newClasses as $key => $value) {
                    $drpClass = new DungeonRoutePlayerClass();
                    $drpClass->index = $key;
                    $drpClass->character_class_id = $value;
                    $drpClass->dungeon_route_id = $this->id;
                    $drpClass->save();
                }
            }

            $newAffixes = $request->get('affixes', array());
            if (!empty($newAffixes)) {
                // Remove old affixgroups
                $this->affixgroups()->delete();
                foreach ($newAffixes as $key => $value) {
                    $drAffixGroup = new DungeonRouteAffixGroup();
                    $drAffixGroup->affix_group_id = $value;
                    $drAffixGroup->dungeon_route_id = $this->id;
                    $drAffixGroup->save();
                }
            }
            $result = true;
        }

        return $result;
    }
}
