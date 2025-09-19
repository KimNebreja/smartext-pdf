// Prerequisite: Ensure there is a <div> element in the HTML with an id of "notify".
// Example: <div id="notify" hidden></div>

var alertTimeout

/**
 * Displays an alert message with a specific type. Optionally hides it automatically after a set duration.
 *
 * @param {string} type - The type of the alert (e.g., "success", "danger", "warning").
 * @param {string} message - The message to display in the alert.
 * @param {boolean} [autoHide=true] - Whether to automatically hide the alert after a timeout.
 * @param {number} [duration=5000] - Duration (in milliseconds) before the alert is hidden, if autoHide is true.
 */
const showAlert = (type, message, autoHide = true, duration = 5000) => {
    // Clear any existing timeout to prevent premature hiding of alerts
    clearTimeout(alertTimeout)

    // Get the alert notification element from the DOM
    let notifyElement = document.getElementById('notify')

    // Make the alert visible
    notifyElement.hidden = false

    // Set the appropriate class for the alert based on the type
    notifyElement.className = 'alert alert-' + type

    // Set the alert message content
    notifyElement.innerHTML = message

    // Scroll the alert into view smoothly, aligning it to the center of the viewport
    notifyElement.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
        inline: 'nearest'
    })

    // Check if autoHide is enabled
    if (autoHide) {
        // Set a timeout to hide the alert after the specified duration
        alertTimeout = setTimeout(function() {
            notifyElement.hidden = true
        }, duration)
    }
}


/**
 * Hides the alert notification immediately.
 */
const hideAlert = () => {
    // Clear any existing timeout to avoid conflicts
    clearTimeout(alertTimeout)

    // Get the alert notification element from the DOM
    let notifyElement = document.getElementById('notify')

    // Hide the alert notification
    notifyElement.hidden = true
}