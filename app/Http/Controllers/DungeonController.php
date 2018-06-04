<?php

namespace App\Http\Controllers;

use App\Http\Requests\DungeonFormRequest;
use App\Models\Dungeon;
use App\Models\Expansion;

class DungeonController extends BaseController
{
    public function __construct()
    {
        parent::__construct('dungeon', 'admin');
    }

    public function getNewHeaderTitle()
    {
        return __('New dungeon');
    }

    public function getEditHeaderTitle()
    {
        return __('Edit dungeon');
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        // Override so we can set the expansions and floors for the edit page
        $this->_setVariables(array(
            'expansions' => Expansion::all()->pluck('name', 'id'),
            // 'floors' => DB::table('floors')->where('dungeon_id', '=', $id)
        ));

        return parent::edit($id);
    }

    /**
     * @param DungeonFormRequest $request
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function store($request, int $id = -1)
    {
        $dungeon = new Dungeon();
        $edit = $id !== -1;

        $dungeon->name = $request->get('name');
        // May not be set when editing
        $dungeon->expansion_id = $request->get('expansion');

        // Update or insert it
        if (!$dungeon->save()) {
            abort(500, 'Unable to save dungeon');
        }

        \Session::flash('status', sprintf(__('Dungeon %s'), $edit ? __("updated") : __("saved")));

        return $dungeon->id;
    }

    /**
     * Override to give the type hint which is required.
     *
     * @param DungeonFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function savenew(DungeonFormRequest $request)
    {
        // Store it and show the edit page for the new item upon success
        return parent::_savenew($request);
    }
}
