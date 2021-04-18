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
        let contentElement;
        if (this.link) {
            contentElement = document.createElement('a');
            contentElement.href = this.link;
            contentElement.innerText = 'View details';
        }

        if (this.icon) {
            let customIcon = L.icon({
                iconUrl: this.icon,
                iconSize: [21, 32],
                iconAnchor: [0, 32],
                popupAnchor: [10, -32]
            });

            this.marker.setIcon(customIcon)
        }

        if (contentElement) {
            const popup = L.popup()
                .setLatLng([this.lat, this.lng])
                .setContent(contentElement.outerHTML);

            this.marker.bindPopup(popup);
        }
        this.marker.addTo(this.map);
    }

    hide() {
        this.map.removeLayer(this.marker);
    }

    bindPopup(pop) {
        this.marker.bindPopup(pop);
    }
}

module.exports = AppPoint;
