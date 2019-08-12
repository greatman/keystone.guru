<?php

namespace App\Http\Controllers;

use App\Logic\MDT\Data\MDTDungeon;
use App\Logic\MDT\IO\ImportString;
use App\Logic\MDT\IO\ImportWarning;
use App\Models\Dungeon;
use App\Models\DungeonFloorSwitchMarker;
use App\Models\DungeonRoute;
use App\Models\DungeonStartMarker;
use App\Models\Enemy;
use App\Models\EnemyPack;
use App\Models\EnemyPatrol;
use App\Models\Floor;
use App\Models\MapComment;
use App\Models\Npc;
use App\Models\NpcType;
use App\Models\Release;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AdminToolsController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('admin.tools.list');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\
     */
    public function mdtview()
    {
        return view('admin.tools.mdt.string');
    }

    /**
     * @param Request $request
     */
    public function mdtviewsubmit(Request $request)
    {
        $string = $request->get('import_string');
        $importString = new ImportString();
        echo json_encode($importString->setEncodedString($string)->getDecoded());
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\
     */
    public function mdtdiff()
    {
        $warnings = new Collection();
        $npcs = Npc::with(['enemies', 'type'])->get();

        // For each dungeon
        foreach (Dungeon::active()->get() as $dungeon) {
            $mdtNpcs = (new MDTDungeon($dungeon->name))->getMDTNPCs();

            // For each NPC that is found in the MDT Dungeon
            foreach ($mdtNpcs as $mdtNpc) {
                $mdtNpc = (object)$mdtNpc;
                // Find our own NPC
                /** @var Npc $npc */
                $npc = $npcs->where('id', $mdtNpc->id)->first();

                // Not found..
                if ($npc === null) {
                    $warnings->push(
                        new ImportWarning('missing_npc',
                            sprintf(__('Unable to find npc for id %s'), $mdtNpc->id),
                            ['mdt_npc' => $mdtNpc, 'npc' => $npc]
                        )
                    );
                } // Found, compare
                else {
                    // Match health
                    if ($npc->base_health !== (int)$mdtNpc->health) {
                        $warnings->push(
                            new ImportWarning('mismatched_health',
                                sprintf(__('NPC %s has mismatched health values, MDT: %s, KG: %s'), $mdtNpc->id, (int)$mdtNpc->health, $npc->base_health),
                                ['mdt_npc' => $mdtNpc, 'npc' => $npc, 'old' => $npc->base_health, 'new' => (int)$mdtNpc->health]
                            )
                        );
                    }

                    // Match enemy forces
                    if ($npc->enemy_forces !== (int)$mdtNpc->count) {
                        $warnings->push(
                            new ImportWarning('mismatched_enemy_forces',
                                sprintf(__('NPC %s has mismatched enemy forces, MDT: %s, KG: %s'), $mdtNpc->id, (int)$mdtNpc->count, $npc->enemy_forces),
                                ['mdt_npc' => $mdtNpc, 'npc' => $npc, 'old' => $npc->enemy_forces, 'new' => (int)$mdtNpc->count]
                            )
                        );
                    }

                    // Match clone count, should be equal
                    if ($npc->enemies->count() !== count($mdtNpc->clones)) {
                        $warnings->push(
                            new ImportWarning('mismatched_enemy_count',
                                sprintf(__('NPC %s has mismatched enemy count, MDT: %s, KG: %s'),
                                    $mdtNpc->id, count($mdtNpc->clones), $npc->enemies === null ? 0 : $npc->enemies->count()),
                                ['mdt_npc' => $mdtNpc, 'npc' => $npc]
                            )
                        );
                    }

                    // Match npc type, should be equal
                    if ($npc->type->type !== $mdtNpc->creatureType) {
                        $warnings->push(
                            new ImportWarning('mismatched_enemy_type',
                                sprintf(__('NPC %s has mismatched enemy type, MDT: %s, KG: %s'),
                                    $mdtNpc->id, $mdtNpc->creatureType, $npc->type->type),
                                ['mdt_npc' => $mdtNpc, 'npc' => $npc, 'old' => $npc->type->type, 'new' => $mdtNpc->creatureType]
                            )
                        );
                    }
                }
            }
        }

        return view('admin.tools.mdt.diff', ['warnings' => $warnings]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function applychange(Request $request)
    {
        $category = $request->get('category');
        $npcId = $request->get('npc_id');
        $value = $request->get('value');

        /** @var Npc $npc */
        $npc = Npc::find($npcId);

        switch ($category) {
            case 'mismatched_health':
                $npc->base_health = $value;
                $npc->save();
                break;
            case 'mismatched_enemy_forces':
                $npc->enemy_forces = $value;
                $npc->save();
                break;
            case 'mismatched_enemy_type':
                $npcType = NpcType::where('type', $value)->first();
                $npc->npc_type_id = $npcType->id;
                $npc->save();
                break;
            default:
                abort(500, 'Invalid category');
                break;
        }

        // Whatever
        return [];
    }

    /**
     * @param Request $request
     */
    public function exportreleases(Request $request)
    {
        $result = [];

        foreach (Release::all() as $release) {
            $releaseArr = $release->toArray();

            /** @var $release Release */
            $rootDirPath = database_path('/seeds/releases/');
            $this->_saveData($releaseArr, $rootDirPath, sprintf('%s.json', $release->version));

            $result[] = $releaseArr;
        }

        dd($result);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function exportdungeondata(Request $request)
    {
        $result = [];

        // Save all NPCs which aren't directly tied to a dungeon
        $npcs = Npc::all()->where('dungeon_id', -1)->values();
        $npcs->makeHidden(['type', 'class']);

        // Save NPC data in the root of folder
        $dungeonDataDir = database_path('/seeds/dungeondata/');
        $this->_saveData($npcs, $dungeonDataDir, 'npcs.json');

        foreach (Dungeon::all() as $dungeon) {
            /** @var $dungeon Dungeon */
            // HoV is our test dungeon so keep there here so I don't have to rewrite this every time I want to debug
//            if( $dungeon->getKeyAttribute() !== 'hallsofvalor' ){
//                continue;
//            }

            $rootDirPath = $dungeonDataDir . $dungeon->expansion->shortname . '/' . $dungeon->key;

            // Demo routes, load it in a specific way to make it easier to import it back in again
            $demoRoutes = $dungeon->dungeonroutes->where('demo', true)->values();
            foreach ($demoRoutes as $demoRoute) {
                /** @var $demoRoute DungeonRoute */
                unset($demoRoute->relations);
                // Do not reload them
                $demoRoute->setAppends([]);
                // Ids cannot be guaranteed with users uploading dungeonroutes as well. As such, a new internal ID must be created
                // for each and every re-import
                $demoRoute->setHidden(['id']);
                $demoRoute->load(['playerspecializations', 'playerraces', 'playerclasses',
                    'routeattributesraw', 'affixgroups', 'brushlines', 'paths', 'killzones', 'enemyraidmarkers', 'mapcomments']);

                // Routes and killzone IDs (and dungeonRouteIDs) are not determined by me, users will be adding routes and killzones.
                // I cannot serialize the IDs in the dev environment and expect it to be the same on the production instance
                // Thus, remove the IDs from both Paths and KillZones as we need to make new IDs when the DungeonRoute
                // is imported into the production environment
                $toHide = new \Illuminate\Database\Eloquent\Collection();
                // No ->merge() :( -> https://medium.com/@tadaspaplauskas/quick-tip-laravel-eloquent-collections-merge-gotcha-moment-e2a56fc95889
                foreach ($demoRoute->playerspecializations as $item) {
                    $toHide->add($item);
                }
                foreach ($demoRoute->playerraces as $item) {
                    $toHide->add($item);
                }
                foreach ($demoRoute->playerclasses as $item) {
                    $toHide->add($item);
                }
                foreach ($demoRoute->routeattributesraw as $item) {
                    $toHide->add($item);
                }
                foreach ($demoRoute->affixgroups as $item) {
                    $toHide->add($item);
                }
                foreach ($demoRoute->brushlines as $item) {
                    $item->setVisible(['floor_id', 'polyline']);
                    $toHide->add($item);
                }
                foreach ($demoRoute->paths as $item) {
                    $item->setVisible(['floor_id', 'polyline']);
                    $toHide->add($item);
                }
                foreach ($demoRoute->killzones as $item) {
                    // Hidden by default to save data
                    $item->addVisible(['floor_id']);
                    $toHide->add($item);
                }
                foreach ($demoRoute->enemyraidmarkers as $item) {
                    $toHide->add($item);
                }
                foreach ($demoRoute->mapcomments as $item) {
                    $toHide->add($item);
                }
                foreach ($toHide as $item) {
                    /** @var $item Model */
                    $item->makeHidden(['id', 'dungeon_route_id']);
                }
            }

            $this->_saveData($demoRoutes, $rootDirPath, 'dungeonroutes.json');

            $npcs = Npc::all()->where('dungeon_id', $dungeon->id)->values();
            $npcs->makeHidden(['type', 'class']);

            // Save NPC data in the root of the dungeon folder
            $this->_saveData($npcs, $rootDirPath, 'npcs.json');

            /** @var Dungeon $dungeon */
            foreach ($dungeon->floors as $floor) {
                /** @var Floor $floor */
                // Only export NPC->id, no need to store the full npc in the enemy
                $enemies = Enemy::where('floor_id', $floor->id)->without(['npc', 'type'])->with('npc:id')->get()->values();
                foreach ($enemies as $enemy) {
                    /** @var $enemy Enemy */
                    if ($enemy->npc !== null) {
                        $enemy->npc->unsetRelation('type');
                        $enemy->npc->unsetRelation('class');
                    }
                }
                $enemyPacks = EnemyPack::where('floor_id', $floor->id)->get()->values();
                $enemyPatrols = EnemyPatrol::where('floor_id', $floor->id)->get()->values();
                $dungeonStartMarkers = DungeonStartMarker::where('floor_id', $floor->id)->get()->values();
                $dungeonFloorSwitchMarkers = DungeonFloorSwitchMarker::where('floor_id', $floor->id)->get()->values();
                $mapComments = MapComment::where('floor_id', $floor->id)->where('always_visible', true)->get()->values();
                // Map comments can ALSO be added by users, thus we never know where this thing comes. As such, insert it
                // at the end of the table instead.
                $mapComments->makeHidden(['id']);

                $result['enemies'] = $enemies;
                $result['enemy_packs'] = $enemyPacks;
                $result['enemy_patrols'] = $enemyPatrols;
                $result['dungeon_start_markers'] = $dungeonStartMarkers;
                $result['dungeon_floor_switch_markers'] = $dungeonFloorSwitchMarkers;
                $result['map_comments'] = $mapComments;

                foreach ($result as $category => $categoryData) {
                    // Save enemies, packs, patrols, markers on a per-floor basis
                    $this->_saveData($categoryData, $rootDirPath . '/' . $floor->index, $category . '.json');
                }
            }
        }

        return view('admin.tools.datadump.viewexporteddungeondata', ['data' => $result]);
    }

    /**
     * @param $dataArr array
     * @param $dir string
     * @param $filename string
     */
    private function _saveData($dataArr, $dir, $filename)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 755, true);
        }

        $filePath = $dir . '/' . $filename;
        $file = fopen($filePath, 'w') or die('Cannot create file');
        fwrite($file, json_encode($dataArr, JSON_PRETTY_PRINT));
        fclose($file);
    }
}