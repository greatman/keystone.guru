<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagFormRequest;
use App\Models\DungeonRoute;
use App\Models\Tags\Tag;
use App\Models\Tags\TagCategory;
use App\Models\Tags\TagModel;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Teapot\StatusCode\Http;

class APITagController
{
    /**
     * @param Request $request
     * @return Tag[]|Collection
     */
    public function all(Request $request)
    {
        return Tag::all();
    }

    /**
     * @param Request $request
     * @param TagCategory $category
     * @return Tag[]|Collection
     */
    public function list(Request $request, TagCategory $category)
    {
        return TagModel::where($category->category)->where('user_id', Auth::id())->get();
    }

    /**
     * @param TagFormRequest $request
     *
     * @return Application|ResponseFactory|Response
     */
    public function store(TagFormRequest $request)
    {
        /** @var TagCategory $tagCategory */
        $tagCategory = TagCategory::where('name', $request->get('category', null))->firstOrFail();

        $modelId = $request->get('model_id');
        if( $tagCategory->name === 'dungeon_route' ){
            /** @var DungeonRoute $dungeonRoute */
            $dungeonRoute = DungeonRoute::where('public_key', $modelId)->firstOrFail();
            $modelId = $dungeonRoute->id;
        }

        $tagName = $request->get('name');

        /** @var Tag $tag */
        $tag = Tag::where('name', $tagName)->firstOrCreate(['name' => $tagName]);

        // Couple the tag to the model
        $tagModel = new TagModel();
        $tagModel->user_id = Auth::id();
        $tagModel->tag_id = $tag->id;
        $tagModel->model_id = $modelId;
        $tagModel->model_class = $tagCategory->model_class;
        $color = $request->get('color');
        $tagModel->color = empty($color) ? null : $color;

        if ($tagModel->save()) {
            $tagModel->load(['tag']);
            $result = $tagModel;
        } else {
            $result = response('Unable to save Tag', Http::INTERNAL_SERVER_ERROR);
        }

        return $result;
    }

    /**
     * @param Request $request
     * @param TagModel $tagmodel
     *
     * @return array|ResponseFactory|Response
     * @throws Exception
     */
    function delete(Request $request, TagModel $tagmodel)
    {
        if ($tagmodel->delete()) {
            $result = response()->noContent();
        } else {
            $result = response('Unable to delete Tag', Http::INTERNAL_SERVER_ERROR);
        }

        return $result;
    }
}