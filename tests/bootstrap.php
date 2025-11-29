<?php
/**
 * Bootstrap file for PHPUnit tests
 */

// Load composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables for testing
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set testing environment
$_ENV['APP_ENV'] = 'testing';

// Initialize database connection for testing
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Load test helpers
require_once __DIR__ . '/TestCase.php';