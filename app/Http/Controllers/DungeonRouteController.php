<?php

namespace App\Http\Controllers;

use App\Http\Requests\DungeonRouteFormRequest;
use App\Models\Dungeon;
use App\Models\DungeonRoute;
use Illuminate\Support\Facades\Auth;
use Teapot\StatusCode\Http;

class DungeonRouteController extends BaseController
{
    public function __construct()
    {
        parent::__construct('dungeonroute', '\App\Models\DungeonRoute');
    }

    public function getNewHeaderTitle()
    {
        return __('New dungeonroute');
    }

    public function getEditHeaderTitle()
    {
        return __('Edit dungeonroute');
    }

    /**
     * Redirect new to a 'new' page, since the new page is different from the edit page in this case.
     * @return string
     */
    protected function _getNewActionView()
    {
        return 'new';
    }

    public function new()
    {
        $this->_addVariable("dungeons", Dungeon::all());

        return parent::new();
    }

    /**
     * @param DungeonRouteFormRequest $request
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function store($request, int $id = -1)
    {
        // Do an internal API request
        $controller = new APIDungeonRouteController();

        $storeResult = $controller->store($request, $id);
        if (!is_array($storeResult)) {
            abort(500, 'Unable to save dungeonroute');
        }

        \Session::flash('status', sprintf(__('Dungeonroute %s'), $id !== -1 ? __("updated") : __("saved")));
        return $storeResult['id'];
    }

    public function editImplicit(DungeonRoute $dungeonroute)
    {
        /** @var DungeonRoute $dungeonroute */
//        $dungeonroute = DungeonRoute::findOrFail($id);
//        // If the current user is not an admin AND the current user is the author of the route
//        if ($dungeonroute->author_id !== Auth::getUser()->id && !Auth::getUser()->hasRole('admin')) {
//            abort(Http::UNAUTHORIZED, __('You are not authorized to view this page'));
//        }
        return parent::edit($dungeonroute->id); // TODO: Change the autogenerated stub
    }


    /**
     * Override to give the type hint which is required.
     *
     * @param DungeonRouteFormRequest $request
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function update(DungeonRouteFormRequest $request, $id)
    {
        return parent::_update($request, $id);
    }

    /**
     * Override to give the type hint which is required.
     *
     * @param DungeonRouteFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function savenew(DungeonRouteFormRequest $request)
    {
        // Store it and show the edit page for the new item upon success
        return parent::_savenew($request);
    }
}
