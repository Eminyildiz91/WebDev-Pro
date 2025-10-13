<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Veritabanı bağlantısını dahil et
require_once '../config/database.php';

// Sonuçları göster
echo "<h2>Veritabanı Tablosu Güncelleme</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Veritabanı bağlantısı kurulamadı.");
    }
    
    echo "<p style='color:green'>Veritabanı bağlantısı başarılı!</p>";
    
    // Veritabanı türünü kontrol et
    $is_sqlite = $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
    
    // Mevcut sütunları kontrol et
    if ($is_sqlite) {
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='contact_info'");
    } else {
        $stmt = $db->query("DESCRIBE contact_info");
    }
    
    if ($stmt) {
        if ($is_sqlite) {
            $table_exists = $stmt->fetchColumn();
            if ($table_exists) {
                echo "<p>contact_info tablosu mevcut.</p>";
                
                // SQLite için is_visible sütununu kontrol et ve ekle
                $pragma = $db->query("PRAGMA table_info(contact_info)");
                $columns = $pragma->fetchAll(PDO::FETCH_ASSOC);
                
                $is_visible_exists = false;
                echo "<p>contact_info tablosunun mevcut sütunları:</p>";
                echo "<ul>";
                foreach ($columns as $column) {
                    echo "<li>{$column['name']} - {$column['type']}</li>";
                    if ($column['name'] === 'is_visible') {
                        $is_visible_exists = true;
                    }
                }
                echo "</ul>";
                
                if (!$is_visible_exists) {
                    $db->exec("ALTER TABLE contact_info ADD COLUMN is_visible INTEGER DEFAULT 1");
                    echo "<p style='color:green'>is_visible sütunu başarıyla eklendi!</p>";
                } else {
                    echo "<p style='color:blue'>is_visible sütunu zaten mevcut.</p>";
                }
            }
        } else {
            // MySQL için mevcut kod
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p>contact_info tablosunun mevcut sütunları:</p>";
            echo "<ul>";
            $is_visible_exists = false;
            foreach ($columns as $column) {
                echo "<li>{$column['Field']} - {$column['Type']}</li>";
                if ($column['Field'] === 'is_visible') {
                    $is_visible_exists = true;
                }
            }
            echo "</ul>";
            
            // is_visible sütunu yoksa ekle
            if (!$is_visible_exists) {
                $sql = "ALTER TABLE contact_info ADD COLUMN is_visible BOOLEAN DEFAULT TRUE";
                $db->exec($sql);
                echo "<p style='color:green'>is_visible sütunu başarıyla eklendi!</p>";
                
                // Tekrar sütunları kontrol et
                $stmt = $db->query("DESCRIBE contact_info");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<p>Güncellenmiş sütunlar:</p>";
                echo "<ul>";
                foreach ($columns as $column) {
                    echo "<li>{$column['Field']} - {$column['Type']}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='color:blue'>is_visible sütunu zaten mevcut.</p>";
            }
        }
    }
    
    // email_logs tablosunu oluştur
    echo "<h3>E-posta Logları Tablosu Kontrolü</h3>";
    
    if ($is_sqlite) {
        // SQLite için email_logs tablosunu kontrol et
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='email_logs'");
        $email_logs_exists = $stmt->fetchColumn();
        
        if (!$email_logs_exists) {
            // SQLite için email_logs tablosunu oluştur
            $db->exec("CREATE TABLE IF NOT EXISTS email_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL,
                changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                changed_by TEXT
            )");
            echo "<p style='color:green'>email_logs tablosu başarıyla oluşturuldu!</p>";
        } else {
            echo "<p style='color:blue'>email_logs tablosu zaten mevcut.</p>";
        }
    } else {
        // MySQL için email_logs tablosunu kontrol et
        $stmt = $db->query("SHOW TABLES LIKE 'email_logs'");
        $email_logs_exists = $stmt->rowCount() > 0;
        
        if (!$email_logs_exists) {
            // MySQL için email_logs tablosunu oluştur
            $db->exec("CREATE TABLE IF NOT EXISTS email_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                changed_by VARCHAR(100)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "<p style='color:green'>email_logs tablosu başarıyla oluşturuldu!</p>";
        } else {
            echo "<p style='color:blue'>email_logs tablosu zaten mevcut.</p>";
        }
    }
    
    echo "<p><a href='settings.php'>Ayarlar Sayfasına Dön</a></p>";
    
} catch(Exception $e) {
    echo "<p style='color:red'>Hata: " . $e->getMessage() . "</p>";
}
?>