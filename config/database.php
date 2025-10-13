<?php
// Veritabanı bağlantı ayarları
define('DB_HOST', 'localhost');
define('DB_NAME', 'webdevpro');
define('DB_USER', 'root');
define('DB_PASS', 'root');

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;
    
    // SQLite veritabanını oluştur
    private function setupSQLiteDatabase() {
        try {
            // visitors tablosunu oluştur - SQLite sözdizimi
            $this->conn->exec("CREATE TABLE IF NOT EXISTS visitors (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // E-posta log tablosunu oluştur...
            $this->conn->exec("CREATE TABLE IF NOT EXISTS email_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email VARCHAR(255) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT,
                status VARCHAR(50) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
        } catch(PDOException $e) {
            echo "<p style='color:red'>SQLite veritabanı oluşturma hatası: " . $e->getMessage() . "</p>";
        }
    }

    private function setupMySQLDatabase() {
        try {
            // visitors tablosunu oluştur - MySQL sözdizimi
            $this->conn->exec("CREATE TABLE IF NOT EXISTS visitors (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ip (ip_address),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            
            // E-posta log tablosunu oluştur...
            $this->conn->exec("CREATE TABLE IF NOT EXISTS email_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT,
                status VARCHAR(50) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            
        } catch(PDOException $e) {
            echo "<p style='color:red'>MySQL veritabanı oluşturma hatası: " . $e->getMessage() . "</p>";
        }
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            // MAMP'in MySQL sunucusu çalışmıyorsa veya bağlantı bilgileri yanlışsa
            // alternatif olarak SQLite kullanabiliriz
            if (strpos($this->host, 'localhost') !== false) {
                try {
                    // Önce MySQL bağlantısını dene
                    $this->conn = new PDO(
                        "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                        $this->username,
                        $this->password
                    );
                    
                    // MySQL veritabanını oluştur
                    $this->setupMySQLDatabase();
                } catch(PDOException $e) {
                    // MySQL bağlantısı başarısız olursa SQLite kullan
                    $sqlite_path = __DIR__ . '/../data/webdevpro.sqlite';
                    $this->conn = new PDO('sqlite:' . $sqlite_path);
                    
                    // SQLite veritabanını oluştur
                    $this->setupSQLiteDatabase();
                }
            } else {
                // Standart MySQL bağlantısı
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                    $this->username,
                    $this->password
                );
                
                // MySQL veritabanını oluştur
                $this->setupMySQLDatabase();
            }
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // MySQL için karakter setini ayarla, SQLite için gerekli değil
            if ($this->conn->getAttribute(PDO::ATTR_DRIVER_NAME) == 'mysql') {
                $this->conn->exec("set names utf8");
            }
        } catch(PDOException $exception) {
            throw $exception; // Hatayı fırlat, böylece null değer kullanılmaz
        }
        
        return $this->conn;
    }
}
?>