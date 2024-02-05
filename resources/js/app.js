import './bootstrap';

import Alpine from 'alpinejs';
import ProgressBar from 'progressbar.js'

window.Alpine = Alpine;
window.ProgressBar = ProgressBar;

Alpine.start();

Echo.private('App').listen(
    'AppUpdateAvailable', function(data) {
        console.log(JSON.stringify(data));
});
