fullScreenControl = L.Control.extend({
    options: {
        position: 'topleft'
    },
    onAdd: function (map) {
        let container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');

        container.innerHTML = "&#9857;";
        container.title = "Toggle full screen";

        container.style.backgroundColor = 'white';
        container.style.width = '32px';
        container.style.height = '32px';
        container.style.cursor = 'pointer';
        container.style['font-size'] = '31px';
        container.style['line-height'] = '31px';

        container.onclick = function(e) {
            map.getContainer().scrollIntoView();
            let currentHeight = map.getContainer().style.height;
            if (currentHeight === "90vh") {
                map.gestureHandling.addHooks();
                map.getContainer().style.height='30vh';
            } else {
                map.gestureHandling.removeHooks();
                map.getContainer().style.height='90vh';
            }

            // there are click listeners on the map layer
            // do not trigger these listeners when click is on the control
            e.stopPropagation();

            // redraw map
            window.dispatchEvent(new Event('resize'));
        };

        return container;
    },
});

module.exports = fullScreenControl;
