<?php
/**
 * Secure Contact Form Handler
 * Includes input validation, CSRF protection, and rate limiting
 */

// Load global configuration
if (!file_exists('../config.php')) {
    http_response_code(500);
    die('Configuration file not found');
}
require_once('../config.php');

// Set security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

// ============================================
// CSRF PROTECTION
// ============================================
if (ENABLE_CSRF_PROTECTION) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'CSRF token validation failed']));
    }
}

// ============================================
// RATE LIMITING
// ============================================
$client_ip = $_SERVER['REMOTE_ADDR'];
$rate_limit_key = 'form_submissions_' . $client_ip;

if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = ['count' => 0, 'timestamp' => time()];
}

// Reset counter if timeout expired
if (time() - $_SESSION[$rate_limit_key]['timestamp'] > FORM_SUBMISSION_TIMEOUT) {
    $_SESSION[$rate_limit_key] = ['count' => 0, 'timestamp' => time()];
}

// Check rate limit
if ($_SESSION[$rate_limit_key]['count'] >= MAX_SUBMISSIONS_PER_IP) {
    http_response_code(429);
    die(json_encode(['success' => false, 'message' => 'Too many submission attempts. Please try again later.']));
}

// Increment counter
$_SESSION[$rate_limit_key]['count']++;

// ============================================
// INPUT VALIDATION AND SANITIZATION
// ============================================
$errors = [];

// Validate and sanitize name
$name = trim($_POST['name'] ?? '');
$name = filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
if (empty($name)) {
    $errors[] = 'Name is required';
} elseif (strlen($name) < MIN_NAME_LENGTH || strlen($name) > MAX_NAME_LENGTH) {
    $errors[] = "Name must be between " . MIN_NAME_LENGTH . " and " . MAX_NAME_LENGTH . " characters";
}

// Validate and sanitize email
$email = trim($_POST['email'] ?? '');
$email = filter_var($email, FILTER_VALIDATE_EMAIL);
if (!$email) {
    $errors[] = 'Valid email address is required';
}

// Validate and sanitize subject
$subject = trim($_POST['subject'] ?? '');
$subject = filter_var($subject, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
if (empty($subject)) {
    $errors[] = 'Subject is required';
} elseif (strlen($subject) > MAX_SUBJECT_LENGTH) {
    $errors[] = "Subject must not exceed " . MAX_SUBJECT_LENGTH . " characters";
}

// Validate and sanitize message
$message = trim($_POST['message'] ?? '');
$message = filter_var($message, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
if (empty($message)) {
    $errors[] = 'Message is required';
} elseif (strlen($message) > MAX_MESSAGE_LENGTH) {
    $errors[] = "Message must not exceed " . MAX_MESSAGE_LENGTH . " characters";
}

// Return validation errors
if (!empty($errors)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => implode(', ', $errors)]));
}

// ============================================
// LOAD PHP EMAIL FORM LIBRARY
// ============================================
if (file_exists($php_email_form = '../assets/vendor/php-email-form/php-email-form.php')) {
    include($php_email_form);
} else {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Unable to load the PHP Email Form Library']));
}

// ============================================
// PREPARE EMAIL
// ============================================
$contact = new PHP_Email_Form;
$contact->ajax = true;

// Use email from config file
$contact->to = CONTACT_EMAIL;
$contact->from_name = $name;
$contact->from_email = $email;
$contact->subject = $subject;

// Configure SMTP if enabled
if (USE_SMTP) {
    $contact->smtp = array(
        'host' => SMTP_HOST,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
        'port' => SMTP_PORT
    );
}

// Add message fields
$contact->add_message($name, 'From');
$contact->add_message($email, 'Email');
$contact->add_message($message, 'Message', 10);

// ============================================
// SEND EMAIL AND RETURN RESPONSE
// ============================================
try {
    $result = $contact->send();
    
    if ($result === true) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Your message has been sent successfully!']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $result ?? 'Failed to send message']);
    }
} catch (Exception $e) {
    http_response_code(500);
    
    if (SHOW_ERRORS) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
    }
    
    // Log error for debugging
    if (LOG_ERRORS) {
        error_log('Contact Form Error: ' . $e->getMessage() . ' (' . $client_ip . ')', 0);
    }
}
?>
