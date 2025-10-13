<?php
// Veritabanı bağlantı bilgileri
$host = 'localhost';
$port = '3306'; 
$dbname = 'eminyild_web';
$username = 'eminyild_web';
$password = 'zubzero15';

// Sonuçları göster
echo "<h2>Veritabanı Tablosu Güncelleme</h2>";
echo "<p>Bağlantı bilgileri:</p>";
echo "<ul>";
echo "<li>Host: {$host}:{$port}</li>";
echo "<li>Database: {$dbname}</li>";
echo "<li>Username: {$username}</li>";
echo "</ul>";

try {
    // PDO bağlantısı oluştur
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color:green'>Veritabanı bağlantısı başarılı!</p>";
    
    // Mevcut sütunları kontrol et
    $stmt = $pdo->query("DESCRIBE contact_info");
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
        $pdo->exec($sql);
        echo "<p style='color:green'>is_visible sütunu başarıyla eklendi!</p>";
        
        // Tekrar sütunları kontrol et
        $stmt = $pdo->query("DESCRIBE contact_info");
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
    
    echo "<p><a href='/admin/edit-contact.php'>İletişim Düzenleme Sayfasına Dön</a></p>";
    
} catch(PDOException $e) {
    echo "<p style='color:red'>Bağlantı hatası: " . $e->getMessage() . "</p>";
    
    // Farklı port denemeleri
    echo "<p>Farklı port denemeleri:</p>";
    $ports = ['3306', '8889', ''];
    
    foreach ($ports as $test_port) {
        $port_text = $test_port ? ":{$test_port}" : "";
        echo "<p>Deneniyor: {$host}{$port_text}...</p>";
        
        try {
            $test_dsn = "mysql:host={$host}" . ($test_port ? ";port={$test_port}" : "") . ";dbname={$dbname}";
            $test_pdo = new PDO($test_dsn, $username, $password);
            echo "<p style='color:green'>Bağlantı başarılı! Doğru port: {$test_port}</p>";
            break;
        } catch(PDOException $e2) {
            echo "<p style='color:red'>Başarısız: " . $e2->getMessage() . "</p>";
        }
    }
}
?>