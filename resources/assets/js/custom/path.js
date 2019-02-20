$(function () {
    L.Draw.Path = L.Draw.Polyline.extend({
        statics: {
            TYPE: 'path'
        },
        initialize: function (map, options) {
            options.showLength = false;
            // Save the type so super can fire, need to do this as cannot do this.TYPE :(
            this.type = L.Draw.Path.TYPE;
            L.Draw.Feature.prototype.initialize.call(this, map, options);
        }
    });

    // Copy pasted from https://github.com/Leaflet/Leaflet.draw/blob/develop/src/draw/handler/Draw.Polyline.js#L470
    // Adjusted so that it uses the correct drawing strings
    L.Draw.Path.prototype._getTooltipText = function () {
        var showLength = this.options.showLength,
            labelText, distanceStr;
        if (this._markers.length === 0) {
            labelText = {
                text: L.drawLocal.draw.handlers.route.tooltip.start
            };
        } else {
            distanceStr = showLength ? this._getMeasurementString() : '';

            if (this._markers.length === 1) {
                labelText = {
                    text: L.drawLocal.draw.handlers.route.tooltip.cont,
                    subtext: distanceStr
                };
            } else {
                labelText = {
                    text: L.drawLocal.draw.handlers.route.tooltip.end,
                    subtext: distanceStr
                };
            }
        }
        return labelText;
    }
});

class Path extends Polyline {
    constructor(map, layer) {
        super(map, layer);

        this.label = 'Path';
        this.saving = false;
        this.deleting = false;
        this.decorator = null;

        this.setColor(c.map.path.defaultColor);
        this.setSynced(false);
    }

    /**
     * Rebuild the decorators for this route (directional arrows etc).
     * @private
     */
    _getDecorator() {
        console.assert(this instanceof Path, this, 'this is not a Path');
        return L.polylineDecorator(this.layer, {
            patterns: [
                {
                    offset: 25,
                    repeat: 100,
                    symbol: L.Symbol.arrowHead({
                        pixelSize: 12,
                        pathOptions: {fillOpacity: 1, weight: 0, color: this.polylineColor}
                    })
                }
            ]
        });
    }

    edit() {
        console.assert(this instanceof Path, this, 'this was not a Path');
        this.save();
    }

    delete() {
        let self = this;
        console.assert(this instanceof Path, this, 'this was not a Path');

        let successFn = function (json) {
            self.signal('object:deleted', {response: json});
        };

        // No network traffic if this is enabled!
        if (!this.map.isTryModeEnabled()) {
            $.ajax({
                type: 'POST',
                url: '/ajax/path',
                dataType: 'json',
                data: {
                    _method: 'DELETE',
                    id: self.id
                },
                beforeSend: function () {
                    self.deleting = true;
                },
                success: successFn,
                complete: function () {
                    self.deleting = false;
                },
                error: function () {
                    self.setSynced(false);
                }
            });
        } else {
            successFn();
        }
    }

    save() {
        let self = this;
        console.assert(this instanceof Path, this, 'this was not a Path');

        let successFn = function (json) {
            self.id = json.id;

            self.setSynced(true);
            self.map.leafletMap.closePopup();
        };

        // No network traffic if this is enabled!
        if (!this.map.isTryModeEnabled()) {
            $.ajax({
                type: 'POST',
                url: '/ajax/path',
                dataType: 'json',
                data: {
                    id: self.id,
                    dungeonroute: this.map.getDungeonRoute().publicKey,
                    floor_id: self.map.getCurrentFloor().id,
                    color: self.polylineColor,
                    weight: self.weight,
                    vertices: self.getVertices(),
                },
                beforeSend: function () {
                    self.saving = true;
                    $('#map_path_edit_popup_submit_' + self.id).attr('disabled', 'disabled');
                },
                success: successFn,
                complete: function () {
                    $('#map_path_edit_popup_submit_' + self.id).removeAttr('disabled');
                    self.saving = false;
                },
                error: function () {
                    // Even if we were synced, make sure user knows it's no longer / an error occurred
                    self.setSynced(false);
                }
            });
        } else {
            // We have to supply an ID to keep everything working properly
            successFn({id: self.id === 0 ? parseInt((Math.random() * 10000000)) : self.id })
        }
    }

    // To be overridden by any implementing classes
    onLayerInit() {
        console.assert(this instanceof Path, this, 'this is not a Path');
        super.onLayerInit();

        let self = this;

        // Only when we're editing
        if (this.map.edit) {
            // Popup trigger function, needs to be outside the synced function to prevent multiple bindings
            // This also cannot be a private function since that'll apparently give different signatures as well.
            let popupOpenFn = function (event) {
                let $color = $('#map_path_edit_popup_color_' + self.id);
                $color.val(self.polylineColor);

                // Class color buttons
                let $classColors = $('.map_polyline_edit_popup_class_color ');
                $classColors.unbind('click');
                $classColors.bind('click', function () {
                    $color.val($(this).data('color'));
                });

                // Prevent multiple binds to click
                let $submitBtn = $('#map_path_edit_popup_submit_' + self.id);
                $submitBtn.unbind('click');
                $submitBtn.bind('click', function () {
                    self.setColor($('#map_path_edit_popup_color_' + self.id).val());

                    self.edit();
                });
            };

            // When we're synced, construct the popup.  We don't know the ID before that so we cannot properly bind the popup.
            self.register('synced', this, function (event) {
                let customPopupHtml = $('#map_path_edit_popup_template').html();
                // Remove template so our
                let template = Handlebars.compile(customPopupHtml);

                let data = {id: self.id};

                // Build the status bar from the template
                customPopupHtml = template(data);

                let customOptions = {
                    'maxWidth': '400',
                    'minWidth': '300',
                    'className': 'popupCustom'
                };

                self.layer.unbindPopup();
                self.layer.bindPopup(customPopupHtml, customOptions);

                self.layer.off('popupopen');
                self.layer.on('popupopen', popupOpenFn);
            });
        }
    }
}