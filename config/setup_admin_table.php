<?php
require_once 'database.php';

try {
    // Admin kullanıcı tablosunu oluştur
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id INTEGER PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            phone VARCHAR(20),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME,
            last_ip VARCHAR(45),
            login_count INTEGER DEFAULT 0,
            is_active BOOLEAN DEFAULT 1
        )
    ");

    // Varsayılan admin kullanıcısı ekle (eğer yoksa)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO admin_users (username, email, password, full_name) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute(['admin', 'admin@example.com', $hashed_password, 'Admin User']);
        echo "Admin kullanıcısı oluşturuldu. Kullanıcı adı: admin, Şifre: admin123\n";
    }

    echo "Admin tablosu başarıyla oluşturuldu.\n";
    
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>