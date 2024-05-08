<?php
function trackEvent($eventCategory, $eventAction, $eventLabel = null, $eventValue = null) {
    echo '<script>';
    echo "gtag('event', '{$eventCategory}', { 'event_action': '{$eventAction}'";
    if ($eventLabel) {
        echo ", 'event_label': '{$eventLabel}'";
    }
    if ($eventValue) {
        echo ", 'value': {$eventValue}";
    }
    echo '});';
    echo '</script>';
}

?>
