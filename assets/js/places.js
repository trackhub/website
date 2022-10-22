document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('map', {
            gestureHandling: false,
        }
    );

    // Create layer for markers
    const markersLayer = L.layerGroup().addTo(map);

    // Set default coordinates
    map.setView([42.15, 24.75], 12);
    map.addControl(new MapControlFullScreen());

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);


    jQuery('.icon-view-on-map').click(function () {
        // Get place id
        const lat = this.dataset.lat;
        const lng = this.dataset.lng;

        // Show marker on the map
        markersLayer.clearLayers();
        L.marker([lat, lng]).addTo(markersLayer);

        // Center the map
        map.setView([lat, lng], 15);
    })
});
