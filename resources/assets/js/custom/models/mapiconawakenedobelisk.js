/**
 * @TODO find a better way to do this? getState() is not available in the global scope yet outside this function
 */
function initAwakenedObeliskGatewayIcon() {
    L.Draw.AwakenedObeliskGatewayMapIcon = L.Draw.Marker.extend({
        statics: {
            TYPE: MAP_OBJECT_GROUP_MAPICON_AWAKENED_OBELISK
        },
        options: {
            icon: getMapIconLeafletIcon(getState().getAwakenedObeliskGatewayMapIconType(), false)
        },
        initialize: function (map, options) {
            // Save the type so super can fire, need to do this as cannot do this.TYPE :(
            this.type = L.Draw.AwakenedObeliskGatewayMapIcon.TYPE;
            L.Draw.Feature.prototype.initialize.call(this, map, options);
        }
    });
}

class MapIconAwakenedObelisk extends MapIcon {
    constructor(map, layer) {
        super(map, layer);
    }

    _synced() {
        super._synced();

        let self = this;

        if (!getState().isMapAdmin()) {
            this.layer.on('click', function () {
                if (self.map.getMapState() === null) {
                    self.map.setMapState(
                        new AddAwakenedObeliskGatewayMapState(self.map, self)
                    )
                }
            });
        }
    }
}