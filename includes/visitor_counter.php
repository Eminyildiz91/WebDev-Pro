<?php
class VisitorCounter {
    private $conn;
    private $table_name = "visitors";
    
    public function __construct($db) {
        $this->conn = $db;
        $this->createTable();
    }
    
    private function createTable() {
        $driver = $this->conn->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        if ($driver == 'sqlite') {
            $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            // MySQL
            $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ip (ip_address),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        }
        
        try {
            $this->conn->exec($query);
        } catch(PDOException $e) {
            error_log("Visitor table creation error: " . $e->getMessage());
        }
    }
    
    public function recordVisit($ip_address = null, $user_agent = null) {
        if ($ip_address === null) {
            $ip_address = $this->getClientIP();
        }
        if ($user_agent === null) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }
        
        $query = "INSERT INTO " . $this->table_name . " (ip_address, user_agent) VALUES (?, ?)";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$ip_address, $user_agent]);
        } catch(PDOException $e) {
            error_log("Record visit error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getVisitorLogs($limit = 100) {
        $query = "SELECT ip_address, user_agent, created_at FROM " . $this->table_name . " 
                  ORDER BY created_at DESC LIMIT ?";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([(int)$limit]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result ? $result : [];
        } catch(PDOException $e) {
            error_log("Get visitor logs error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTotalVisitors() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch(PDOException $e) {
            error_log("Get total visitors error: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getUniqueVisitors() {
        $query = "SELECT COUNT(DISTINCT ip_address) as unique_visitors FROM " . $this->table_name;
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['unique_visitors'] ?? 0;
        } catch(PDOException $e) {
            error_log("Get unique visitors error: " . $e->getMessage());
            return 0;
        }
    }
    
    // Eksik olan getMonthlyStats metodu ekleniyor
    public function getMonthlyStats($months = 6) {
        $stats = [];
        
        try {
            $driver = $this->conn->getAttribute(PDO::ATTR_DRIVER_NAME);
            
            // Son X ay için istatistikleri çek - SQLite ve MySQL için farklı sorgular
            if ($driver == 'sqlite') {
                $query = "SELECT 
                            strftime('%Y-%m', created_at) as month,
                            COUNT(*) as total_visits,
                            COUNT(DISTINCT ip_address) as unique_visitors
                          FROM " . $this->table_name . "
                          WHERE created_at >= datetime('now', '-" . (int)$months . " months')
                          GROUP BY strftime('%Y-%m', created_at)
                          ORDER BY month ASC";
            } else {
                // MySQL
                $query = "SELECT 
                            DATE_FORMAT(created_at, '%Y-%m') as month,
                            COUNT(*) as total_visits,
                            COUNT(DISTINCT ip_address) as unique_visitors
                          FROM " . $this->table_name . "
                          WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL ? MONTH)
                          GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                          ORDER BY month ASC";
            }
            
            $stmt = $this->conn->prepare($query);
            if ($driver == 'sqlite') {
                $stmt->execute();
            } else {
                $stmt->execute([(int)$months]);
            }
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Eğer veri yoksa, varsayılan veri oluştur
            if (empty($results)) {
                for ($i = $months - 1; $i >= 0; $i--) {
                    $month = date('Y-m', strtotime("-$i month"));
                    $monthName = date('M Y', strtotime("-$i month"));
                    $stats[] = [
                        'month' => $month,
                        'month_name' => $monthName,
                        'total_visits' => 0,
                        'unique_visitors' => 0
                    ];
                }
                return $stats;
            }
            
            // Eksik ayları doldur
            $currentMonth = date('Y-m', strtotime("-" . ($months - 1) . " month"));
            $endMonth = date('Y-m');
            $allMonths = [];
            
            while ($currentMonth <= $endMonth) {
                $allMonths[$currentMonth] = [
                    'month' => $currentMonth,
                    'month_name' => date('M Y', strtotime($currentMonth)),
                    'total_visits' => 0,
                    'unique_visitors' => 0
                ];
                $currentMonth = date('Y-m', strtotime($currentMonth . ' +1 month'));
            }
            
            // Veritabanından gelen verileri ekle
            foreach ($results as $row) {
                if (isset($allMonths[$row['month']])) {
                    $allMonths[$row['month']]['total_visits'] = (int)$row['total_visits'];
                    $allMonths[$row['month']]['unique_visitors'] = (int)$row['unique_visitors'];
                }
            }
            
            return array_values($allMonths);
            
        } catch(PDOException $e) {
            error_log("Get monthly stats error: " . $e->getMessage());
            
            // Hata durumunda varsayılan veri döndür
            for ($i = $months - 1; $i >= 0; $i--) {
                $month = date('Y-m', strtotime("-$i month"));
                $monthName = date('M Y', strtotime("-$i month"));
                $stats[] = [
                    'month' => $month,
                    'month_name' => $monthName,
                    'total_visits' => 0,
                    'unique_visitors' => 0
                ];
            }
            return $stats;
        }
    }
    
    // Günlük istatistikler için yeni metod
    public function getDailyStats($days = 7) {
        $stats = [];
        
        try {
            $driver = $this->conn->getAttribute(PDO::ATTR_DRIVER_NAME);
            
            // Son X gün için istatistikleri çek - SQLite ve MySQL için farklı sorgular
            if ($driver == 'sqlite') {
                $query = "SELECT 
                            DATE(created_at) as day,
                            COUNT(*) as total_visits,
                            COUNT(DISTINCT ip_address) as unique_visitors
                          FROM " . $this->table_name . "
                          WHERE created_at >= datetime('now', '-" . (int)$days . " days')
                          GROUP BY DATE(created_at)
                          ORDER BY day ASC";
            } else {
                // MySQL
                $query = "SELECT 
                            DATE(created_at) as day,
                            COUNT(*) as total_visits,
                            COUNT(DISTINCT ip_address) as unique_visitors
                          FROM " . $this->table_name . "
                          WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL ? DAY)
                          GROUP BY DATE(created_at)
                          ORDER BY day ASC";
            }
            
            $stmt = $this->conn->prepare($query);
            if ($driver == 'sqlite') {
                $stmt->execute();
            } else {
                $stmt->execute([(int)$days]);
            }
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Eksik günleri doldur
            $allDays = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $day = date('Y-m-d', strtotime("-$i day"));
                $dayName = date('d M', strtotime("-$i day"));
                $allDays[$day] = [
                    'day' => $day,
                    'day_name' => $dayName,
                    'total_visits' => 0,
                    'unique_visitors' => 0
                ];
            }
            
            // Veritabanından gelen verileri ekle
            foreach ($results as $row) {
                if (isset($allDays[$row['day']])) {
                    $allDays[$row['day']]['total_visits'] = (int)$row['total_visits'];
                    $allDays[$row['day']]['unique_visitors'] = (int)$row['unique_visitors'];
                }
            }
            
            return array_values($allDays);
            
        } catch(PDOException $e) {
            error_log("Get daily stats error: " . $e->getMessage());
            
            // Hata durumunda varsayılan veri döndür
            for ($i = $days - 1; $i >= 0; $i--) {
                $day = date('Y-m-d', strtotime("-$i day"));
                $dayName = date('d M', strtotime("-$i day"));
                $stats[] = [
                    'day' => $day,
                    'day_name' => $dayName,
                    'total_visits' => 0,
                    'unique_visitors' => 0
                ];
            }
            return $stats;
        }
    }
    
    private function getClientIP() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}