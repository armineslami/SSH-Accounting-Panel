import './bootstrap';

import Alpine from 'alpinejs';
import ProgressBar from 'progressbar.js'

window.Alpine = Alpine;
window.ProgressBar = ProgressBar;

Alpine.start();

Echo.private('App').listen(
    'AppUpdateAvailable', function(data) {
        // console.log(data);
        showUpdateBanner(data.version);
        setCookie(data.cookie.name, data.version, data.cookie.expire_date)
});

function showUpdateBanner(version) {
    const versionElement = document.getElementById("update-banner-version");
    const bannerElement = document.getElementById("update-banner");
    versionElement.textContent = version;
    bannerElement.classList.remove("hidden");
}

function setCookie(cookieName, cookieValue, expirationDays) {
    // Convert the timestamp to milliseconds
    const timestampInMilliseconds = expirationDays * 1000;
    const d = new Date(timestampInMilliseconds);
    const expires = "expires="+d.toUTCString();
    document.cookie = cookieName + "=" + cookieValue + ";" + expires + ";path=/";
}
