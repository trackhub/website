class AppPoint {
    constructor(map, lat, lng) {
        this.map = map;
        this.lat = lat;
        this.lng = lng;
        this.marker = L.marker([lat, lng]);
        this.attraction = false;
    }

    makeAttraction() {
        this.attraction = true;
    }

    isAttraction() {
        return this.attraction;
    }

    setLink(link) {
        this.link = link;
    }

    setIcon(icon) {
        this.icon = icon;
    }

    show() {
        this.marker.addTo(this.map);
    }

    hide() {
        this.map.removeLayer(this.marker);
    }

    bindPopup(pop) {
        this.marker.bindPopup(pop);
    }

    exportAsMarker() {
        var contentElement;

        if (this.link) {
            contentElement = document.createElement('a');
            contentElement.href = this.link;
            contentElement.innerText = 'View details';
        } else {
            contentElement = document.createElement('span');
            contentElement.innerText = 'View details';
        }

        var markerOptions = {};

        if (this.icon) {
            let finishIcon = L.icon({
                iconUrl: this.icon,
                iconSize: [21, 32],
                iconAnchor: [0, 32],
                popupAnchor: [10, -32]
            });

            markerOptions = {
                icon: finishIcon
            };
        }

        const marker = L.marker([this.lat, this.lng], markerOptions);

        const popup = L.popup()
            .setLatLng([this.lat, this.lng])
            .setContent(contentElement.outerHTML);

        marker.bindPopup(popup);

        return marker;
    }
}

module.exports = AppPoint;
