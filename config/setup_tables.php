<?php
require_once 'database.php';

try {
    // Veritabanı bağlantısını al
    $database = new Database();
    $db = $database->getConnection();
    
    // SQL dosyasını oku
    $sql = file_get_contents(__DIR__ . '/contact_tables.sql');
    
    // SQL komutlarını çalıştır
    if ($db) {
        $result = $db->exec($sql);
        echo "<p>Tablolar başarıyla oluşturuldu ve varsayılan veriler eklendi.</p>";
        
        // contact_info tablosuna is_visible sütununu ekle
        try {
            $db->exec("ALTER TABLE contact_info ADD COLUMN IF NOT EXISTS is_visible BOOLEAN DEFAULT TRUE");
            echo "<p>contact_info tablosuna is_visible sütunu eklendi veya zaten mevcut.</p>";
            
            // Mevcut sütunları kontrol et
            $stmt = $db->query("DESCRIBE contact_info");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p>contact_info tablosunun mevcut sütunları:</p>";
            echo "<ul>";
            foreach ($columns as $column) {
                echo "<li>{$column['Field']} - {$column['Type']}</li>";
            }
            echo "</ul>";
            
            echo "<p><a href='/admin/edit-contact.php'>İletişim Düzenleme Sayfasına Dön</a></p>";
        } catch(PDOException $e2) {
            echo "<p>Sütun ekleme hatası: " . $e2->getMessage() . "</p>";
        }
    } else {
        echo "<p>Veritabanı bağlantısı kurulamadı.</p>";
    }
} catch(PDOException $e) {
    echo "<p>Hata: " . $e->getMessage() . "</p>";
}
?>