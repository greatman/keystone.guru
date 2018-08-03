<?php

$isAdmin = isset($admin) && $admin;
/** @var \Illuminate\Support\Collection $dungeons */
// Hide the floor selection if it's just one dungeon with no additional floors
$dungeonSelection = (!isset($dungeonSelect) || $dungeonSelect) && $dungeons->count() > 1;
// Enabled by default if it's not set, but may be explicitly disabled
// Do not show if it does not make sense (only one floor)
$floorSelection = (!isset($floorSelect) || $floorSelect) && !($dungeons->count() === 1 && $dungeons->first()->floors->count() === 1);
?>

@section('head')
    {{-- Make sure we don't override the head of the page this thing is included in --}}
    @parent

    <style>
        /* css to customize Leaflet default styles  */
        .popupCustom .leaflet-popup-tip,
        .popupCustom .leaflet-popup-content-wrapper {
            background: #e0e0e0;
            color: #234c5e;
        }

        .enemy_edit_popup_npc {
            width: 300px;
        }

        #map {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        #map_controls .map_controls_custom {
            width: 50px;
            background-image: none;
        }

        #map_controls .map_controls_custom {
            width: 50px;
            background-image: none;
        }
    </style>
@endsection

@section('scripts')
    {{-- Make sure we don't override the scripts of the page this thing is included in --}}
    @parent

    <script>
        // Data of the dungeon(s) we're selecting in the map
        let _dungeonData = {!! $dungeons !!};
        let _switchDungeonSelect = "#map_dungeon_selection";
        let _switchDungeonFloorSelect = "#map_floor_selection";

        let dungeonMap;

        $(function () {

            @if(!isset($manualInit) || !$manualInit)
            // Make sure that the select options have a valid value
            _refreshDungeonSelect();
            _refreshFloorSelect();

            @if($isAdmin)
                dungeonMap = new AdminDungeonMap('map', _dungeonData, $(_switchDungeonSelect).val(), $(_switchDungeonFloorSelect).val());
            @else
                dungeonMap = new DungeonMap('map', _dungeonData, $(_switchDungeonSelect).val(), $(_switchDungeonFloorSelect).val());
            @endif
            @endif

            $(_switchDungeonSelect).change(function () {
                // Pass the new dungeon to the map
                dungeonMap.currentDungeonId = $(_switchDungeonSelect).val();
                // If the dungeon switched, the floor needs to refresh to reflect the new dungeon
                _refreshFloorSelect();
                dungeonMap.refreshLeafletMap();
            });

            $(_switchDungeonFloorSelect).change(function () {
                // Pass the new floor ID to the map
                dungeonMap.currentFloorId = $(_switchDungeonFloorSelect).val();
                dungeonMap.refreshLeafletMap();
            });

        });

        /**
         * Refreshes the dungeon select and fills it with all the current dungeons.
         * @private
         */
        function _refreshDungeonSelect() {
            let $switchDungeonSelect = $(_switchDungeonSelect);
            if ($switchDungeonSelect.is("select")) {
                // Clear of all options
                $switchDungeonSelect.find('option').remove();
                // Add new ones
                $.each(_dungeonData, function (index, dungeon) {
                    $(_switchDungeonSelect).append($('<option>', {
                        text: dungeon.name,
                        value: dungeon.id
                    }));
                });
            }
        }

        /**
         * Refreshes the floor select and fills it with the floors that fit the currently selected dungeon.
         * @private
         */
        function _refreshFloorSelect() {
            let $switchDungeonFloorSelect = $(_switchDungeonFloorSelect);
            if ($switchDungeonFloorSelect.is("select")) {
                // Clear of all options
                $switchDungeonFloorSelect.find('option').remove();
                // Add new ones
                $.each(_dungeonData, function (index, dungeon) {
                    // Find the dungeon..
                    if (parseInt(dungeon.id) === parseInt($(_switchDungeonSelect).val())) {
                        // Add each new floor to the select
                        $.each(dungeon.floors, function (index, floor) {
                            // Reconstruct the dungeon floor select
                            $switchDungeonFloorSelect.append($('<option>', {
                                text: floor.name,
                                value: floor.id
                            }));
                        });
                    }
                });
            }
        }
    </script>

    <script id="map_controls_template" type="text/x-handlebars-template">
        <div id="map_controls" class="leaflet-draw-section">
            <div class="leaflet-draw-toolbar leaflet-bar leaflet-draw-toolbar-top">
                @{{#mapobjectgroups}}
                <a id='map_controls_hide_@{{name}}' class="map_controls_custom" href="#" title="@{{title}}">
                    <i id='map_controls_hide_@{{name}}_checkbox' class="fas fa-check-square" style="width: 15px"></i>
                    <i class="fas @{{fa_class}}" style="width: 15px"></i>
                    <span class="sr-only">@{{title}}</span>
                </a>
                @{{/mapobjectgroups}}
            </div>
            <ul class="leaflet-draw-actions"></ul>
        </div>
    </script>

    @if($isAdmin)
        <script id="enemy_edit_popup_template" type="text/x-handlebars-template">
            <div id="enemy_edit_popup_inner" class="popupCustom">
                <div class="form-group">
                    <label for="enemy_edit_popup_npc">{{ __('NPC') }}</label>
                    <select data-live-search="true" id="enemy_edit_popup_npc" name="enemy_edit_popup_npc"
                            class="selectpicker enemy_edit_popup_npc" data-width="300px">
                        @foreach($npcs as $npc)
                            <option value="{{$npc->id}}">{{ sprintf("%s (%s)", $npc->name, $npc->game_id) }}</option>
                        @endforeach
                    </select>
                </div>
                {!! Form::button(__('Submit'), ['id' => 'enemy_edit_popup_submit_template', 'class' => 'btn btn-info']) !!}
            </div>
        </script>

        <script id="dungeon_floor_select_edit_popup_template" type="text/x-handlebars-template">
            <div id="dungeon_floor_select_edit_popup_inner" class="popupCustom">
                <div class="form-group">
                    <label for="dungeon_floor_select_edit_popup_floor">{{ __('Connected floor') }}</label>
                    <select id="dungeon_floor_select_edit_popup_floor" name="dungeon_floor_select_edit_popup_floor"
                            class="selectpicker dungeon_floor_select_edit_popup_floor" data-width="300px">
                        @{{#floors}}
                            <option value="@{{id}}">@{{name}}</option>
                        @{{/floors}}
                    </select>
                </div>
                {!! Form::button(__('Submit'), ['id' => 'dungeon_floor_select_edit_popup_submit', 'class' => 'btn btn-info']) !!}
            </div>
        </script>
    @endif
@endsection

<div class="container">
    {{-- Only show the dungeon selector when the amount of dungeons we want to show is greater than 1, otherwise just show the first --}}
    @if($dungeonSelection)
        <div class="form-group">
            {!! Form::label('map_dungeon_selection', __('Select dungeon')) !!}
            {!! Form::select('map_dungeon_selection', [], 0, ['class' => 'form-control']) !!}
        </div>
    @else
        {!! Form::input('hidden', 'map_dungeon_selection', $dungeons[0]->id, ['id' => 'map_dungeon_selection']) !!}
    @endif

    @if($floorSelection)
        <div class="form-group">
            {!! Form::label('map_floor_selection', __('Select floor')) !!}
            {!! Form::select('map_floor_selection', [], 1, ['class' => 'form-control']) !!}
        </div>
    @else
        {!! Form::input('hidden', 'map_floor_selection', $dungeons[0]->floors[0]->id, ['id' => 'map_floor_selection']) !!}
    @endif
</div>

<div class="form-group">
    <div id="map" class="col-md-12"></div>
    @if($isAdmin)
        {{-- @include('common.maps.mapadmintools') --}}
    @endif
</div>