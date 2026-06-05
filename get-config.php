<?php
/**
 * Configuration Endpoint
 * Returns configuration as JSON for client-side usage
 */

require_once('config.php');

// Build Google Maps embed URL dynamically
$lat = GOOGLE_MAPS_LOCATION['latitude'];
$lng = GOOGLE_MAPS_LOCATION['longitude'];
$location_name = GOOGLE_MAPS_LOCATION['name'];

$google_maps_url = "https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d12097.433213460943!2d" . $lng . "!3d" . $lat . "!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xb89d1fe6bc499443!2s" . urlencode($location_name) . "!5e0!3m2!1sen!2s!4v1539943755621";

// Generate CSRF token if not present
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Prepare configuration response
$config = [
    'googleMapsEmbedUrl' => $google_maps_url,
    'contactEmail' => CONTACT_EMAIL,
    'siteName' => SITE_NAME,
    'csrfToken' => $_SESSION['csrf_token']
];

// Set response headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Return JSON response
echo json_encode($config);
?>
