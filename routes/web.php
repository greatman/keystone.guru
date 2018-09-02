<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::group(['middleware' => ['viewcachebuster']], function () {


    Route::get('about', function () {
        return view('misc.about');
    })->name('misc.about');

    Route::get('privacy', function () {
        return view('legal.privacy');
    })->name('legal.privacy');

    Route::get('terms', function () {
        return view('legal.terms');
    })->name('legal.terms');

    Route::get('cookies', function () {
        return view('legal.cookies');
    })->name('legal.cookies');

    Route::get('/', 'HomeController@index')->name('home');

    // ['auth', 'role:admin|user']

    Route::get('profile/(user}', 'ProfileController@view')->name('profile.view');

    Route::group(['middleware' => ['auth', 'role:user|admin']], function () {
        Route::get('profile', 'ProfileController@edit')->name('profile.edit');
        Route::patch('profile/{user}', 'ProfileController@update')->name('profile.update');
        Route::patch('profile', 'ProfileController@changepassword')->name('profile.changepassword');
    });

    Route::group(['middleware' => ['auth', 'role:admin']], function () {
        // Only admins may view a list of profiles
        Route::get('profiles', 'ProfileController@list')->name('profile.list');

        // Dungeons
        Route::get('admin/dungeon/new', 'DungeonController@new')->name('admin.dungeon.new');
        Route::get('admin/dungeon/{dungeon}', 'DungeonController@edit')->name('admin.dungeon.edit');

        Route::post('admin/dungeon/new', 'DungeonController@savenew')->name('admin.dungeon.savenew');
        Route::patch('admin/dungeon/{dungeon}', 'DungeonController@update')->name('admin.dungeon.update');

        Route::get('admin/dungeons', 'DungeonController@list')->name('admin.dungeons');

        // Floors
        Route::get('admin/floor/new', 'FloorController@new')->name('admin.floor.new')->where(['dungeon' => '[0-9]+']);
        Route::get('admin/floor/{floor}', 'FloorController@edit')->name('admin.floor.edit');

        Route::post('admin/floor/new', 'FloorController@savenew')->name('admin.floor.savenew')->where(['dungeon' => '[0-9]+']);
        Route::patch('admin/floor/{floor}', 'FloorController@update')->name('admin.floor.update');

        // Expansions
        Route::get('admin/expansion/new', 'ExpansionController@new')->name('admin.expansion.new');
        Route::get('admin/expansion/{expansion}', 'ExpansionController@edit')->name('admin.expansion.edit');

        Route::post('admin/expansion/new', 'ExpansionController@savenew')->name('admin.expansion.savenew');
        Route::patch('admin/expansion/{expansion}', 'ExpansionController@update')->name('admin.expansion.update');

        Route::get('admin/expansions', 'ExpansionController@list')->name('admin.expansions');

        // NPCs
        Route::get('admin/npc/new', 'NpcController@new')->name('admin.npc.new');
        Route::get('admin/npc/{npc}', 'NpcController@edit')->name('admin.npc.edit');

        Route::post('admin/npc/new', 'NpcController@savenew')->name('admin.npc.savenew');
        Route::patch('admin/npc/{npc}', 'NpcController@update')->name('admin.npc.update');

        Route::get('admin/npcs', 'NpcController@list')->name('admin.npcs');

        Route::get('admin/datadump/exportdungeondata', 'ExportDungeonDataController@view')->name('admin.datadump.exportdungeondata');
        Route::post('admin/datadump/exportdungeondata', 'ExportDungeonDataController@submit')->name('admin.datadump.viewexporteddungeondata');

        Route::get('admin/makeadmin', function () {
            $adminRole = \App\Role::all()->where('name', '=', 'admin')->first();
            foreach (\App\User::all() as $user) {
                /** @var $user \App\User */

                if (!$user->hasRole('admin')) {
                    $user->attachRole($adminRole);
                }
            }

            return view('home');
        })->name('admin.makeadmin');
    });

    // Put this down below, since it contains a catch all
    Route::get('new', 'DungeonRouteController@new')->name('dungeonroute.new');
    Route::post('new', 'DungeonRouteController@savenew')->name('dungeonroute.savenew');
    Route::get('dungeonroutes', 'DungeonRouteController@list')->name('dungeonroutes');

    // Edit your own dungeon routes
    Route::get('edit/{dungeonroute}', 'DungeonRouteController@edit')
        ->middleware('can:edit,dungeonroute')
        ->name('dungeonroute.edit');
    // Submit a patch for your own dungeon route
    Route::patch('edit/{dungeonroute}', 'DungeonRouteController@update')
        ->middleware('can:edit,dungeonroute')
        ->name('dungeonroute.update');
    // View any dungeon route
    Route::get('{dungeonroute}', 'DungeonRouteController@view')
        ->name('dungeonroute.view');


    Route::group(['prefix' => 'ajax', 'middleware' => 'ajax'], function () {
        Route::get('/enemypacks', 'APIEnemyPackController@list');

        Route::get('/enemies', 'APIEnemyController@list');

        Route::get('/enemypatrols', 'APIEnemyPatrolController@list');

        Route::get('/dungeonroutes', 'APIDungeonRouteController@list')->name('api.dungeonroutes');

        Route::get('/routes', 'APIRouteController@list')->where(['dungeonroute' => '[a-zA-Z0-9]+'])->where(['floor_id' => '[0-9]+']);

        Route::get('/dungeonstartmarkers', 'APIDungeonStartMarkerController@list');

        Route::get('/dungeonfloorswitchmarkers', 'APIDungeonFloorSwitchMarkerController@list')->where(['floor_id' => '[0-9]+']);

        Route::group(['middleware' => ['auth', 'role:user']], function () {
            Route::post('/route', 'APIRouteController@store');
            Route::delete('/route', 'APIRouteController@delete');

            Route::patch('/dungeonroute/{dungeonroute}', 'APIDungeonRouteController@store')->name('api.dungeonroute.update');
            Route::post('/dungeonroute/{dungeonroute}/rate', 'APIDungeonRouteController@rate')->name('api.dungeonroute.rate');
        });

        Route::group(['middleware' => ['auth', 'role:admin']], function () {
            Route::post('/enemypack', 'APIEnemyPackController@store');
            Route::delete('/enemypack', 'APIEnemyPackController@delete');

            Route::post('/enemy', 'APIEnemyController@store');
            Route::delete('/enemy', 'APIEnemyController@delete');

            Route::post('/enemypatrol', 'APIEnemyPatrolController@store');
            Route::delete('/enemypatrol', 'APIEnemyPatrolController@delete');

            Route::post('/dungeonstartmarker', 'APIDungeonStartMarkerController@store')->where(['dungeon' => '[0-9]+']);
            Route::delete('/dungeonstartmarker', 'APIDungeonStartMarkerController@delete');

            Route::post('/dungeonfloorswitchmarker', 'APIDungeonFloorSwitchMarkerController@store')->where(['floor_id' => '[0-9]+']);
            Route::delete('/dungeonfloorswitchmarker', 'APIDungeonFloorSwitchMarkerController@delete');
        });
    });

});