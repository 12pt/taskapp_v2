"use strict";

/**
 * Hide the warning message div. Idempotent.
 */
function hideError() {
    var warning = document.querySelector("#warning");
    warning.style.visibility = "hidden";
    warning.style.display = "none";
}

/**
 * Display an error message.
 * This will hide after 5 seconds if shouldEventuallyHide is true.
 * @param {string} message The message to display and log.
 * @param {boolean} shouldEventuallyHide Whether or not this warning message should disappear after 5 seconds.
 */
function error(message, shouldEventuallyHide) {
    var warning = document.querySelector("#warning");
    warning.style.visibility = "show";
    warning.style.display = "block";

    console.warn(message); // log it for history
    warning.querySelector("p").innerText = message;

    if(shouldEventuallyHide) {
        window.setTimeout(hideError, 5000);
    }
}
