class EnemyVisualManager extends Signalable {

    constructor(map) {
        super();
        let self = this;

        this.map = map;
        // Keeping track if the map is being dragged; if so don't handle mouse move events as mouse is locked in place on map
        this._isMapBeingDragged = false;
        // The last time we checked moving the mouse and triggering mouse out/in events
        this._lastMouseMoveDistanceCheckTime = 0
        this._lastMapMoveDistanceCheckTime = 0;
        // Keep track of some data between mouse and enemy for each enemy
        this._enemyMouseMoveDistanceData = [];
        // Used for storing previous visibility states so we know when to refresh the enemy or not
        this._enemyVisibilityMap = [];

        // There is no mousemoveend function. Every time the mouse of moved a timeout is set for X MS to trigger the mouse
        // move function one last time to properly wrap everything up. If mouse of moved before the timeout, the timeout
        // is refreshed. This is because the final mouse move may not be executed due to optimizations (not executing every
        // time but only once every 50 ms, for example).
        this._mouseStoppedMovingTimeoutId = -1;

        let enemyMapObjectGroup = self.map.mapObjectGroupManager.getByName(MAP_OBJECT_GROUP_ENEMY);
        enemyMapObjectGroup.register(['object:add', 'save:success'], this, function (objectAddEvent) {
            let addedEnemy = objectAddEvent.data.object;
            if (addedEnemy.id > 0 && !self._enemyVisibilityMap.hasOwnProperty(addedEnemy.id)) {
                self._enemyVisibilityMap[addedEnemy.id] = {
                    wasVisible: objectAddEvent.data.object.isVisibleOnScreen(),
                    lastRefreshedZoomLevel: getState().getMapZoomLevel()
                };
                self._enemyMouseMoveDistanceData[addedEnemy.id] = {
                    lastCheckTime: 0,
                    lastDistanceSquared: 99999
                };
            }
        });

        getState().register('mapzoomlevel:changed', this, this._onZoomLevelChanged.bind(this));
        this.map.register('map:refresh', this, function () {
            self.map.leafletMap.on('mousemove', self._onLeafletMapMouseMove.bind(self));

            self.map.leafletMap.on('movestart', self._onLeafletMapMoveStart.bind(self));
            self.map.leafletMap.on('move', self._onLeafletMapMove.bind(self));
            self.map.leafletMap.on('moveend', self._onLeafletMapMoveEnd.bind(self));
        });
        this.map.register('map:beforerefresh', this, function () {
            self.map.leafletMap.off('mousemove', self._onLeafletMapMouseMove.bind(self));

            self.map.leafletMap.off('movestart', self._onLeafletMapMoveStart.bind(self));
            self.map.leafletMap.off('move', self._onLeafletMapMove.bind(self));
            self.map.leafletMap.off('moveend', self._onLeafletMapMoveEnd.bind(self));
        });
    }

    /**
     * Called when the map's zoom level was changed.
     * @param zoomLevelChangedEvent
     * @private
     */
    _onZoomLevelChanged(zoomLevelChangedEvent) {
        console.assert(this instanceof EnemyVisualManager, 'this is not an EnemyVisualManager!', this);

        let currentZoomLevel = getState().getMapZoomLevel();
        let enemyMapObjectGroup = this.map.mapObjectGroupManager.getByName(MAP_OBJECT_GROUP_ENEMY);
        for (let i = 0; i < enemyMapObjectGroup.objects.length; i++) {
            let enemy = enemyMapObjectGroup.objects[i];

            // Only refresh what we can see
            if (enemy.id > 0 && enemy.isVisibleOnScreen()) {
                // If we're mouse hovering the visual, just rebuild it entirely. There are a few things which need
                // reworking to support a full refresh of the visual
                if (enemy.visual.isHighlighted()) {
                    window.requestAnimationFrame(enemy.visual.buildVisual.bind(enemy.visual));
                } else {
                    window.requestAnimationFrame(enemy.visual.refreshSize.bind(enemy.visual));
                }
                // Keep track that we already refreshed all these so they won't be refreshed AGAIN upon move
                this._enemyVisibilityMap[enemy.id].lastRefreshedZoomLevel = currentZoomLevel;
            }
        }
    }

    /**
     * Called when the mouse has moved over the leaflet map
     * @param mouseMoveEvent
     * @param organic boolean True if fired from organic source (user moving mouse), false if from a programmatic source
     * @private
     */
    _onLeafletMapMouseMove(mouseMoveEvent, organic = true) {
        console.assert(this instanceof EnemyVisualManager, 'this is not an EnemyVisualManager!', this);

        if (!this._isMapBeingDragged && typeof mouseMoveEvent.originalEvent !== 'undefined') {
            let currTime = (new Date()).getTime();

            // Once every 50 ms, calculation is expensive
            if (currTime - this._lastMouseMoveDistanceCheckTime > 50) {
                let enemyMapObjectGroup = this.map.mapObjectGroupManager.getByName(MAP_OBJECT_GROUP_ENEMY);
                for (let i = 0; i < enemyMapObjectGroup.objects.length; i++) {
                    let enemy = enemyMapObjectGroup.objects[i];

                    if (enemy.id > 0 && enemy.isVisibleOnScreen()) {
                        let lastCheckData = this._enemyMouseMoveDistanceData[enemy.id];
                        if (currTime - lastCheckData.lastCheckTime > 500 * (lastCheckData.lastDistanceSquared / 1000000)) {
                            this._enemyMouseMoveDistanceData[enemy.id].lastDistanceSquared =
                                enemy.visual.checkMouseOver(mouseMoveEvent.originalEvent.pageX, mouseMoveEvent.originalEvent.pageY);

                            // Direct manipulation
                            this._enemyMouseMoveDistanceData[enemy.id].lastCheckTime = currTime;
                        }
                    }
                }

                this._lastMouseMoveDistanceCheckTime = currTime;
                // Cancel any previously set timeouts
                clearTimeout(this._mouseStoppedMovingTimeoutId);
                if (organic) {
                    // Set a new timeout
                    this._mouseStoppedMovingTimeoutId = setTimeout(this._onLeafletMapMouseMove.bind(this, mouseMoveEvent, false), 25);
                }
            }
        }
    }

    /**
     * Called when the user starts dragging the map.
     * @param mouseMoveEvent
     * @private
     */
    _onLeafletMapMoveStart(mouseMoveEvent) {
        console.assert(this instanceof EnemyVisualManager, 'this is not an EnemyVisualManager!', this);

        this._isMapBeingDragged = true;
    }

    /**
     * Called when the user is actively dragging the mouse (repeatedly).
     * @param mouseMoveEvent
     * @private
     */
    _onLeafletMapMove(mouseMoveEvent) {
        console.assert(this instanceof EnemyVisualManager, 'this is not an EnemyVisualManager!', this);

        let currentZoomLevel = getState().getMapZoomLevel();

        let currTime = (new Date()).getTime();
        // Once every 100 ms, calculation is expensive
        if (currTime - this._lastMapMoveDistanceCheckTime > 50) {
            let enemyMapObjectGroup = this.map.mapObjectGroupManager.getByName(MAP_OBJECT_GROUP_ENEMY);
            for (let i = 0; i < enemyMapObjectGroup.objects.length; i++) {
                let enemy = enemyMapObjectGroup.objects[i];

                if (enemy.id > 0) {
                    let isVisible = enemy.isVisibleOnScreen();

                    // If panned into view AND we didn't already refresh the zoom earlier
                    if (this._enemyVisibilityMap[enemy.id].wasVisible === false && isVisible &&
                        this._enemyVisibilityMap[enemy.id].lastRefreshedZoomLevel !== currentZoomLevel) {
                        window.requestAnimationFrame(enemy.visual.refreshSize.bind(enemy.visual));
                        this._enemyVisibilityMap[enemy.id].lastRefreshedZoomLevel = currentZoomLevel;
                    }

                    // Write new visible state
                    this._enemyVisibilityMap[enemy.id].wasVisible = isVisible;
                }
            }

            this._lastMapMoveDistanceCheckTime = currTime;
        }
    }

    /**
     * Called when the user stops dragging the mouse.
     * @param mouseMoveEvent
     * @private
     */
    _onLeafletMapMoveEnd(mouseMoveEvent) {
        console.assert(this instanceof EnemyVisualManager, 'this is not an EnemyVisualManager!', this);

        // Force a refresh
        this._lastMapMoveDistanceCheckTime = 0;
        this._onLeafletMapMove(mouseMoveEvent);

        this._isMapBeingDragged = false;
    }
}