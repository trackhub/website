class AppTrack {
    constructor(map, polyline) {
        this.map = map;
        this.polyline = polyline;

        var points = this.polyline.getLatLngs();
        var firstPoint = points[0];
        var lastPoint = points[points.length - 1];
        this.markers = [];
        var startMarker = L.marker([firstPoint.lat, firstPoint.lng]);
        var endmarker = L.marker([lastPoint.lat, lastPoint.lng]);
        this.markers.push(startMarker, endmarker);

    }

    show() {
        this.visible = true;
        this.polyline.addTo(this.map);

        var marker;
        for (marker in this.markers) {
            this.markers[marker].addTo(this.map).bindPopup('Start');
        }
    }

    hide() {
        this.visible = false;
        this.polyline.remove();

        var marker;
        for (marker in this.markers) {
            this.markers[marker].remove();
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
