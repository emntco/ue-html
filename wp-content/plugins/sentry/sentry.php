<?php
/*
Plugin Name: Sentry
Description: Log WordPress errors to Sentry.
Version: 0.3
Author: emnt.co
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once __DIR__ . '/vendor/autoload.php';

// Initialize Sentry using the DSN from environment variables
$dsn = getenv('SENTRY_DSN');
if ($dsn) {
    Sentry\init(['dsn' => $dsn]);

    // Set error handler to capture unhandled exceptions
    set_error_handler(function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            // This error code is not included in error_reporting
            return false;
        }
        Sentry\captureException(new ErrorException($message, 0, $severity, $file, $line));
        return true;
    });

    // Register shutdown function to catch fatal errors
    register_shutdown_function(function () {
        $lastError = error_get_last();
        if ($lastError && ($lastError['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) {
            Sentry\captureException(new ErrorException(
                $lastError['message'],
                0,
                $lastError['type'],
                $lastError['file'],
                $lastError['line']
            ));
        }
    });
} else {
    error_log('Sentry DSN not found in environment variables.');
}
