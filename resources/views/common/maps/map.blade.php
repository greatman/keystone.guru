<?php

$isAdmin = isset($admin) && $admin;
/** @var \Illuminate\Support\Collection $dungeons */
// Hide the floor selection if it's just one dungeon with no additional floors
$dungeonSelection = (!isset($dungeonSelect) || $dungeonSelect) && $dungeons->count() > 1;
// Enabled by default if it's not set, but may be explicitly disabled
// Do not show if it does not make sense (only one floor)
$floorSelection = (!isset($floorSelect) || $floorSelect) && !($dungeons->count() === 1 && $dungeons->first()->floors->count() === 1);
?>

@section('scripts')
    {{-- Make sure we don't override the scripts of the page this thing is included in --}}
    @parent

    <script>
        var _dungeonData = [
                @foreach ($dungeons as $dungeon)
                {{-- @var $dungeon \App\Models\Dungeon --}}
            {
                "id": "{{ $dungeon->id }}",
                "key": "{{ strtolower(str_replace(" ", "", $dungeon->name)) }}",
                "name": "{{ $dungeon->name }}",
                "floors": [
                        @foreach ($dungeon->floors as $floor)
                    {
                        "id": "{{ $floor->id }}",
                        "index": "{{ $floor->index }}",
                        "name": "{{ $floor->name }}"
                    },
                    @endforeach
                ]
            },
            @endforeach
        ];

        let _switchDungeonSelect = "#map_dungeon_selection";
        let _switchDungeonFloorSelect = "#map_floor_selection";

        let dungeonMap;

        $(function () {

            @if(!isset($manualInit) || !$manualInit)
            // Make sure that the select options have a valid value
            _refreshDungeonSelect();
            _refreshFloorSelect();

            dungeonMap = new DungeonMap('map', _dungeonData, $(_switchDungeonSelect).val(), $(_switchDungeonFloorSelect).val());
            @endif

            @if($isAdmin)
            adminInitControls();
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
                    if (dungeon.id === $(_switchDungeonSelect).val()) {
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
    <div id="map" class="col-md-{{ $isAdmin ? "10" : "12" }}"></div>
    @if($isAdmin)
        {{-- @include('common.maps.mapadmintools') --}}
    @endif
</div>