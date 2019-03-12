class DungeonrouteEdit extends InlineCode {

    constructor() {
        super();
    }

    /**
     *
     * @param path
     */
    activate() {
        let self = this;

        // Save settings in the modal
        $('#save_settings').bind('click', this._saveSettings);

        $('#map_route_publish').bind('click', function () {
            self._setPublished(true);
        });

        $('#map_route_unpublish').bind('click', function () {
            self._setPublished(false);
        });
    }

    _setPublished(value) {
        $.ajax({
            type: 'POST',
            url: '/ajax/dungeonroute/' + dungeonMap.getDungeonRoute().publicKey + '/publish',
            dataType: 'json',
            data: {
                published: value === true ? 1 : 0
            },
            beforeSend: function () {
                $('#map_route_publish').css('pointer-events', 'none');
                $('#map_route_unpublish').css('pointer-events', 'none');
            },
            success: function (json) {
                if (value) {
                    // Published
                    $('#map_route_publish').addClass('d-none');
                    $('#map_route_unpublish').removeClass('d-none');
                    $('#map_route_unpublished_info').addClass('d-none');

                    addFixedFooterSuccess(lang.get('messages.route_published'));
                } else {
                    // Unpublished
                    $('#map_route_publish').removeClass('d-none');
                    $('#map_route_unpublish').addClass('d-none');
                    $('#map_route_unpublished_info').removeClass('d-none');

                    addFixedFooterWarning(lang.get('messages.route_unpublished'));
                }
            },
            complete: function () {
                $('#map_route_publish').css('pointer-events', 'auto');
                $('#map_route_unpublish').css('pointer-events', 'auto');
            }
        });
    }

    _saveSettings() {
        $.ajax({
            type: 'POST',
            url: '/ajax/edit/' + dungeonMap.getDungeonRoute().publicKey,
            dataType: 'json',
            data: {
                dungeon_route_title: $('#dungeon_route_title').val(),
                attributes: $('#attributes').val(),
                faction_id: $('#faction_id').val(),
                specialization:
                    $('.specializationselect select').map(function () {
                        return $(this).val();
                    }).get()
                ,
                class:
                    $('.classselect select').map(function () {
                        return $(this).val();
                    }).get()
                ,
                race:
                    $('.raceselect select').map(function () {
                        return $(this).val();
                    }).get()
                ,
                unlisted: $('#unlisted').is(':checked') ? 1 : 0,
                demo: $('#demo').is(':checked') && isUserAdmin ? 1 : 0,
                affixes: $('#affixes').val(),
                _method: 'PATCH'
            },
            beforeSend: function () {
                $('#save_settings').hide();
                $('#save_settings_saving').show();
            },
            success: function (json) {
                addFixedFooterSuccess(lang.get('messages.settings_saved'));
            },
            complete: function () {
                $('#save_settings').show();
                $('#save_settings_saving').hide();
            }
        });
    }
}