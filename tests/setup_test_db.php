<?php
/**
 * Script to set up test database
 */

// Load environment
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set testing environment
$_ENV['APP_ENV'] = 'testing';
$_ENV['DB_DATABASE'] = 'estacionamiento_test';

// Connect to MySQL without specifying database
$dsn = "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};charset={$_ENV['DB_CHARSET']}";
$pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'] ?? '');

// Create test database if it doesn't exist
$pdo->exec("CREATE DATABASE IF NOT EXISTS estacionamiento_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

// Connect to test database
$dsn = "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname=estacionamiento_test;charset={$_ENV['DB_CHARSET']}";
$pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'] ?? '');

// Load and execute schema
$schema = file_get_contents(__DIR__ . '/../database/schema.sql');

// Split by semicolon and execute each statement
$statements = array_filter(array_map('trim', explode(';', $schema)));

foreach ($statements as $statement) {
    if (!empty($statement) &&
        !preg_match('/^(DELIMITER|USE|DROP DATABASE|CREATE DATABASE)/i', $statement) &&
        !preg_match('/^--/', $statement)) {
        try {
            $pdo->exec($statement);
        } catch (Exception $e) {
            echo "Warning: Could not execute statement: " . $e->getMessage() . "\n";
        }
    }
}

echo "Test database setup completed!\n";