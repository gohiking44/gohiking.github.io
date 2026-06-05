<?php
/**
 * Global Configuration File
 * Store all environment variables and sensitive data here
 */

// ============================================
// EMAIL CONFIGURATION
// ============================================
define('CONTACT_EMAIL', '123@xyz.com');
define('SITE_NAME', 'Portellis Portfolio');

// ============================================
// GOOGLE MAPS CONFIGURATION
// ============================================
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY'); // Replace with your actual API key
define('GOOGLE_MAPS_LOCATION', [
    'latitude' => 40.7101282,
    'longitude' => -74.0062269,
    'name' => 'Downtown Conference Center, New York',
    'zoom' => 15,
    'center' => 'New York, NY'
]);

// ============================================
// SMTP CONFIGURATION (Optional)
// ============================================
define('USE_SMTP', false); // Set to true to use SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_PORT', 587);

// ============================================
// SECURITY CONFIGURATION
// ============================================
define('ENABLE_CSRF_PROTECTION', true);
define('FORM_SUBMISSION_TIMEOUT', 3600); // 1 hour in seconds
define('MAX_SUBMISSIONS_PER_IP', 5); // Max 5 submissions per IP per hour

// ============================================
// VALIDATION RULES
// ============================================
define('MIN_NAME_LENGTH', 2);
define('MAX_NAME_LENGTH', 100);
define('MAX_SUBJECT_LENGTH', 200);
define('MAX_MESSAGE_LENGTH', 5000);

// ============================================
// ERROR HANDLING
// ============================================
define('SHOW_ERRORS', false); // Set to false in production
define('LOG_ERRORS', true);
define('ERROR_LOG_PATH', __DIR__ . '/logs/');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create logs directory if it doesn't exist
if (!is_dir(ERROR_LOG_PATH)) {
    mkdir(ERROR_LOG_PATH, 0755, true);
}
