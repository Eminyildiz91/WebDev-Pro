<?php
require_once 'database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Veritabanı bağlantısı kurulamadı.");
    }
    
    echo "<p style='color:green'>Veritabanı bağlantısı başarılı!</p>";
    
    // Veritabanı türünü kontrol et
    $is_sqlite = $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
    
    if ($is_sqlite) {
        // SQLite için tablo kontrolü
        $table_check = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='email_logs'");
        $table_exists = $table_check && $table_check->fetchColumn();
        
        if (!$table_exists) {
            // SQLite için tablo oluştur
            $db->exec("CREATE TABLE IF NOT EXISTS email_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL,
                changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                changed_by TEXT
            )");
            echo "<p style='color:green'>SQLite için email_logs tablosu başarıyla oluşturuldu!</p>";
        } else {
            echo "<p style='color:blue'>SQLite için email_logs tablosu zaten mevcut.</p>";
        }
    } else {
        // MySQL için tablo kontrolü
        $table_check = $db->query("SHOW TABLES LIKE 'email_logs'");
        $table_exists = $table_check && $table_check->rowCount() > 0;
        
        if (!$table_exists) {
            // MySQL için tablo oluştur
            $db->exec("CREATE TABLE IF NOT EXISTS email_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                changed_by VARCHAR(100)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "<p style='color:green'>MySQL için email_logs tablosu başarıyla oluşturuldu!</p>";
        } else {
            echo "<p style='color:blue'>MySQL için email_logs tablosu zaten mevcut.</p>";
        }
    }
    
    // Test kaydı ekle
    $log_stmt = $db->prepare("INSERT INTO email_logs (email, changed_by) VALUES (:email, :changed_by)");
    $email = 'test@example.com';
    $admin_username = 'admin';
    $log_stmt->bindParam(':email', $email);
    $log_stmt->bindParam(':changed_by', $admin_username);
    $log_stmt->execute();
    echo "<p style='color:green'>Test kaydı başarıyla eklendi!</p>";
    
    // Kayıtları göster
    $stmt = $db->query("SELECT * FROM email_logs");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>E-posta Log Kayıtları:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>E-posta</th><th>Değiştirilme Zamanı</th><th>Değiştiren</th></tr>";
    
    foreach ($logs as $log) {
        echo "<tr>";
        echo "<td>{$log['id']}</td>";
        echo "<td>{$log['email']}</td>";
        echo "<td>{$log['changed_at']}</td>";
        echo "<td>{$log['changed_by']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch(Exception $e) {
    echo "<p style='color:red'>Hata: " . $e->getMessage() . "</p>";
}
?>