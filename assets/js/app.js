/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');
require('../css/global.scss');

require('bootstrap');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// var $ = require('jquery');
import * as L from "leaflet"
window.jQuery = require('jquery');
window.chartJs = require('chart.js');
window.lighBox = require('ekko-lightbox/dist/ekko-lightbox.js');
require('leaflet.markercluster')
window.MapControlFullScreen =  require('./map/control/fullScreen')

import { GestureHandling } from "leaflet-gesture-handling";
L.Map.addInitHook("addHandler", "gestureHandling", GestureHandling);

// see https://github.com/PaulLeCam/react-leaflet/issues/255#issuecomment-269750542
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: require('leaflet/dist/images/marker-icon-2x.png'),
    iconUrl: require('leaflet/dist/images/marker-icon.png'),
    shadowUrl: require('leaflet/dist/images/marker-shadow.png'),
});

window.AppTrack = require('./components/appTrack.js');
window.AppPlace = require('./map/appPoint.js');
window.StarRating = require('./components/starRating');

window.jsUpload = require('./components/fileUpload');
window.bsCustomFileInput = require('bs-custom-file-input');

window.EXIF = require('exif-js')
