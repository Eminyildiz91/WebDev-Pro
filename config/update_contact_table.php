<?php
require_once 'database.php';

echo "Veritabanı bağlantısı kuruluyor...\n";
echo "Host: " . DB_HOST . "\n";
echo "Database: " . DB_NAME . "\n";
echo "User: " . DB_USER . "\n";

try {
    // Veritabanı bağlantısını al
    $database = new Database();
    $db = $database->getConnection();
    
    // contact_info tablosuna is_visible sütununu ekle
    $sql = "ALTER TABLE contact_info ADD COLUMN IF NOT EXISTS is_visible BOOLEAN DEFAULT TRUE";
    
    // SQL komutunu çalıştır
    if ($db) {
        $result = $db->exec($sql);
        echo "contact_info tablosu başarıyla güncellendi. is_visible sütunu eklendi.\n";
        
        // Mevcut sütunları kontrol et
        $stmt = $db->query("DESCRIBE contact_info");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\ncontact_info tablosunun mevcut sütunları:\n";
        foreach ($columns as $column) {
            echo $column['Field'] . " - " . $column['Type'] . "\n";
        }
    } else {
        echo "Veritabanı bağlantısı kurulamadı.\n";
    }
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?>