class AppTrack {
    constructor(map, polylines) {
        this.map = map;
        this.polylines = polylines;
        this.type = null;
        this.wayPoints = [];

        let points = this.polylines[0].getLatLngs();
        let firstPoint = points[0];
        let lastPoint = points[points.length - 1];
        this.markers = [];

        let startMarker = L.marker([firstPoint.lat, firstPoint.lng]);

        let finishIcon = L.icon({
            iconUrl: '/images/trackhub/flag/fllag_32.png',
            iconSize: [32, 32],
            iconAnchor: [0, 32],
            popupAnchor: [9, -32]
        });
        let endMarker = L.marker([lastPoint.lat, lastPoint.lng], {icon: finishIcon});
        let polylinePop = polylines[0].getPopup();

        // copy first polyline events to markers
        if (polylinePop) {
            endMarker.bindPopup(polylinePop.getContent());
            startMarker.bindPopup(polylinePop.getContent());
        }

        this.markers.push(startMarker, endMarker);
    }

    addWaypoint(waypoint) {
        this.wayPoints.push(waypoint);
    }

    setType(type) {
        this.type = type;
    }

    getType() {
        return this.type;
    }

    show() {
        this.visible = true;
        for (let i = 0; i < this.polylines.length; i++) {
            this.polylines[i].addTo(this.map);
        }

        let marker;
        for (marker in this.markers) {
            this.markers[marker].addTo(this.map);
        }

        let waypoint;
        for (waypoint in this.wayPoints) {
            this.wayPoints[waypoint].addTo(this.map);
        }
    }

    hide() {
        this.visible = false;
        for (let i = 0; i < this.polylines.length; i++) {
            this.polylines[i].remove();
        }

        var marker;
        for (marker in this.markers) {
            this.markers[marker].remove();
        }

        let waypoint;
        for (waypoint in this.wayPoints) {
            this.wayPoints[waypoint].remove();
        }
    }

    toggle() {
        if (this.visible) {
            this.hide();
        } else {
            this.show();
        }
    }
};

module.exports = AppTrack;
