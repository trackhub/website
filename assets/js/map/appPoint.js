class AppPoint {
    constructor(map, lat, lng) {
        this.map = map;
        this.lat = lat;
        this.lng = lng;
        this.marker = L.marker([lat, lng]);
    }

    show() {
        this.marker.addTo(this.map);
    }
}

module.exports = AppPoint;
