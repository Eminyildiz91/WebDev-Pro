<?php
// Fix admin_users table structure
require_once '../config/database.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    echo "Database driver: $driver\n";
    
    // Check current table structure
    echo "\nChecking admin_users table structure...\n";
    
    if ($driver === 'mysql') {
        $stmt = $pdo->query("DESCRIBE admin_users");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Current columns in admin_users:\n";
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']})\n";
        }
    } else {
        $stmt = $pdo->query("PRAGMA table_info(admin_users)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Current columns in admin_users:\n";
        foreach ($columns as $column) {
            echo "- {$column['name']} ({$column['type']})\n";
        }
    }
    
    // Check if columns exist
    $lastLoginExists = false;
    $statusExists = false;
    $loginCountExists = false;
    $lastIpExists = false;
    
    foreach ($columns as $column) {
        $columnName = $driver === 'mysql' ? $column['Field'] : $column['name'];
        if ($columnName === 'last_login') $lastLoginExists = true;
        if ($columnName === 'status') $statusExists = true;
        if ($columnName === 'login_count') $loginCountExists = true;
        if ($columnName === 'last_ip') $lastIpExists = true;
    }
    
    echo "\nColumn status:\n";
    echo "- last_login: " . ($lastLoginExists ? 'EXISTS' : 'MISSING') . "\n";
    echo "- status: " . ($statusExists ? 'EXISTS' : 'MISSING') . "\n";
    echo "- login_count: " . ($loginCountExists ? 'EXISTS' : 'MISSING') . "\n";
    echo "- last_ip: " . ($lastIpExists ? 'EXISTS' : 'MISSING') . "\n";
    
    // Add missing columns
    echo "\nAdding missing columns...\n";
    
    if (!$lastLoginExists) {
        if ($driver === 'mysql') {
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN last_login TIMESTAMP NULL");
        } else {
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN last_login TIMESTAMP NULL");
        }
        echo "- Added last_login column\n";
    }
    
    if (!$statusExists) {
        if ($driver === 'mysql') {
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN status TINYINT DEFAULT 1");
        } else {
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN status INTEGER DEFAULT 1");
        }
        echo "- Added status column\n";
    }
    
    if (!$loginCountExists) {
        if ($driver === 'mysql') {
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN login_count INT DEFAULT 0");
        } else {
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN login_count INTEGER DEFAULT 0");
        }
        echo "- Added login_count column\n";
    }
    
    if (!$lastIpExists) {
        if ($driver === 'mysql') {
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN last_ip VARCHAR(45)");
        } else {
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN last_ip VARCHAR(45)");
        }
        echo "- Added last_ip column\n";
    }
    
    echo "\nDatabase structure updated successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}