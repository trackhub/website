class AppTrack {
    constructor(map, polyline) {
        this.map = map;
        this.polyline = polyline;

        var points = this.polyline.getLatLngs();
        var firstPoint = points[0];
        var lastPoint = points[points.length - 1];
        this.markers = [];

        var startMarker = L.marker([firstPoint.lat, firstPoint.lng]);

        var finishIcon = L.icon({
            iconUrl: '/images/flaticoncom/Smashicons/racing-flag-32.png',
            iconSize: [32, 32],
            iconAnchor: [0, 32],
            popupAnchor: [9, -32]
        });
        var endMarker = L.marker([lastPoint.lat, lastPoint.lng], {icon: finishIcon});
        var polylinePop = polyline.getPopup();

        // copy polyline events to markers
        if (polylinePop) {
            endMarker.bindPopup(polylinePop.getContent());
            startMarker.bindPopup(polylinePop.getContent());
        }

        this.markers.push(startMarker, endMarker);
    }

    show() {
        this.visible = true;
        this.polyline.addTo(this.map);

        var marker;
        for (marker in this.markers) {
            this.markers[marker].addTo(this.map);
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
