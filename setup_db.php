<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;
// use Dotenv\Dotenv;

// $dotenv = Dotenv::createImmutable(__DIR__);
// $dotenv->load();

try {
    // Connect without DB name first to create it
    $host = '127.0.0.1'; // $_ENV['DB_HOST'];
    $user = 'root'; // $_ENV['DB_USER'];
    $pass = 'password'; // $_ENV['DB_PASS'];
    $dbname = 'php_mvc_db'; // $_ENV['DB_NAME'];
    
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . $dbname);
    echo "Database created or already exists.\n";
    
    // Now use the App\Core\Database class (which connects to the specific DB)
    // But we need to make sure the singleton connects to the right DB.
    // Actually, simpler to just use the PDO instance we have or just run the SQL directly here.
    
    $pdo->exec("USE " . $dbname);
    
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($sql);
    
    echo "Schema imported successfully.\n";
    
} catch (PDOException $e) {
    echo "DB Setup Error: " . $e->getMessage() . "\n";
    exit(1);
}
