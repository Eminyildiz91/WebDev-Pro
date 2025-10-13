<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Veritabanı bağlantısını dahil et
require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Veritabanı bağlantısı kurulamadı.");
    }
} catch(Exception $e) {
    $_SESSION['error_message'] = 'Veritabanı bağlantı hatası: ' . $e->getMessage();
    $db = null;
}

// Ayarlar tablosunu oluştur (eğer yoksa)
try {
    // Veritabanı bağlantısı başarılıysa
    if ($db) {
        // Veritabanı türünü kontrol et
        $is_sqlite = $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        
        if ($is_sqlite) {
            // SQLite için tablo oluştur
            $db->exec("CREATE TABLE IF NOT EXISTS settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                setting_key TEXT NOT NULL UNIQUE,
                setting_value TEXT NOT NULL,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
        } else {
            // MySQL için tablo oluştur
            $db->exec("CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(255) NOT NULL UNIQUE,
                setting_value TEXT NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        
        // Varsayılan ayarları ekle (eğer yoksa)
        $default_settings = [
            'site_title' => 'Web Development Pro',
            'site_description' => 'Profesyonel web geliştirme ve tasarım hizmetleri',
            'site_logo' => '',
            'site_email' => 'info@webdevpro.com', // Yeni eklenen satır
            'social_facebook' => 'https://facebook.com/webdevpro',
            'social_twitter' => 'https://twitter.com/webdevpro',
            'social_instagram' => 'https://instagram.com/webdevpro',
            'social_linkedin' => 'https://linkedin.com/company/webdevpro'
        ];
        
        foreach ($default_settings as $key => $value) {
            $check = $db->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = :key");
            $check->bindParam(':key', $key);
            $check->execute();
            
            if ($check->fetchColumn() == 0) {
                $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)");
                $stmt->bindParam(':key', $key);
                $stmt->bindParam(':value', $value);
                $stmt->execute();
            }
        }
    }
} catch(Exception $e) {
    $_SESSION['error_message'] = 'Ayarlar tablosu oluşturma hatası: ' . $e->getMessage();
}

// Ayarlar verilerini çek
$settings_data = [];
try {
    if ($db) {
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings_data[$row['setting_key']] = $row['setting_value'];
        }
    }
} catch(Exception $e) {
    $_SESSION['error_message'] = 'Ayarlar verilerini çekme hatası: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($db) {
            // Logo yükleme işlemi
            $site_logo = isset($settings_data['site_logo']) ? $settings_data['site_logo'] : '';
            
            if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/logo/';
                
                // Uploads dizini yoksa oluştur
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = basename($_FILES['site_logo']['name']);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // İzin verilen dosya uzantıları
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
                
                if (in_array($file_ext, $allowed_exts)) {
                    // Benzersiz dosya adı oluştur
                    $new_file_name = 'site_logo_' . time() . '.' . $file_ext;
                    $target_file = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $target_file)) {
                        // Eski logo dosyasını sil (varsa ve yeni yüklenen dosyadan farklıysa)
                        if (!empty($site_logo) && file_exists('../' . $site_logo) && '../' . $site_logo !== $target_file) {
                            unlink('../' . $site_logo);
                        }
                        
                        $site_logo = 'uploads/logo/' . $new_file_name;
                    } else {
                        $_SESSION['error_message'] = 'Logo yüklenirken bir hata oluştu.';
                    }
                } else {
                    $_SESSION['error_message'] = 'Sadece JPG, JPEG, PNG, GIF ve SVG dosyaları yüklenebilir.';
                }
            }
            
            // Güncellenecek ayarlar
            $settings = [
                'site_title' => isset($_POST['site_title']) ? trim($_POST['site_title']) : '',
                'site_description' => isset($_POST['site_description']) ? trim($_POST['site_description']) : '',
                'site_logo' => $site_logo,
                'site_email' => isset($_POST['site_email']) ? trim($_POST['site_email']) : '',
                'theme_color' => isset($_POST['theme_color']) ? trim($_POST['theme_color']) : '#3490dc',
            ];
            
            // Her bir ayarı güncelle
            foreach ($settings as $key => $value) {
                // Mevcut değeri al (e-posta değişikliğini kontrol etmek için)
                $current_value = '';
                if ($key === 'site_email') {
                    $get_current = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = :key");
                    $get_current->bindParam(':key', $key);
                    $get_current->execute();
                    $result = $get_current->fetch(PDO::FETCH_ASSOC);
                    if ($result) {
                        $current_value = $result['setting_value'];
                    }
                }
                
                // Ayarın daha önce var olup olmadığını kontrol et
                $check = $db->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = :key");
                $check->bindParam(':key', $key);
                $check->execute();
                
                if ($check->fetchColumn() > 0) {
                    // Güncelle
                    $stmt = $db->prepare("UPDATE settings SET setting_value = :value WHERE setting_key = :key");
                } else {
                    // Ekle
                    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)");
                }
                
                $stmt->bindParam(':key', $key);
                $stmt->bindParam(':value', $value);
                $stmt->execute();
                
                // E-posta değiştiyse log tablosuna kaydet
                if ($key === 'site_email' && $value !== $current_value) {
                    // Veritabanı türünü kontrol et
                    $is_sqlite = $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
                    
                    // Veritabanı türüne göre tablo kontrolü yap
                    $table_exists = false;
                    
                    if ($is_sqlite) {
                        // SQLite için tablo kontrolü
                        $table_check = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='email_logs'");
                        $table_exists = $table_check && $table_check->fetchColumn();
                    } else {
                        // MySQL için tablo kontrolü
                        $table_check = $db->query("SHOW TABLES LIKE 'email_logs'");
                        $table_exists = $table_check && $table_check->rowCount() > 0;
                    }
                    
                    // Tablo yoksa oluştur
                    if (!$table_exists) {
                        if ($is_sqlite) {
                            // SQLite için tablo oluştur
                            $db->exec("CREATE TABLE IF NOT EXISTS email_logs (
                                id INTEGER PRIMARY KEY AUTOINCREMENT,
                                email TEXT NOT NULL,
                                changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                changed_by TEXT
                            )");
                        } else {
                            // MySQL için tablo oluştur
                            $db->exec("CREATE TABLE IF NOT EXISTS email_logs (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                email VARCHAR(255) NOT NULL,
                                changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                changed_by VARCHAR(100)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                        }
                        $table_exists = true;
                    }
                    
                    // Tablo varsa log ekle
                    if ($table_exists) {
                        $log_stmt = $db->prepare("INSERT INTO email_logs (email, changed_by) VALUES (:email, :changed_by)");
                        $log_stmt->bindParam(':email', $value);
                        $admin_username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'admin';
                        $log_stmt->bindParam(':changed_by', $admin_username);
                        $log_stmt->execute();
                    }
                }
            }
            
            $_SESSION['success_message'] = 'Ayarlar başarıyla güncellendi.';
        } else {
            $_SESSION['error_message'] = 'Veritabanı bağlantısı olmadan ayarlar güncellenemez.';
        }
    } catch(Exception $e) {
        $_SESSION['error_message'] = 'Ayarlar güncellenirken hata oluştu: ' . $e->getMessage();
    }
    
    header('Location: settings.php');
    exit();
}

// Şifre değişikliği işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Şifre doğrulama
    try {
        // Veritabanından admin bilgilerini çek
        $stmt = $db->prepare("SELECT password FROM admin_users WHERE username = :username");
        $stmt->bindParam(':username', $_SESSION['admin_username']);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Mevcut şifreyi doğrula
        if (!password_verify($current_password, $admin['password'])) {
            throw new Exception("Mevcut şifre yanlış.");
        }

        // Yeni şifre kontrolü
        if (strlen($new_password) < 8) {
            throw new Exception("Yeni şifre en az 8 karakter olmalıdır.");
        }

        if ($new_password !== $confirm_password) {
            throw new Exception("Yeni şifreler eşleşmiyor.");
        }

        // Yeni şifreyi hash'le
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Şifreyi güncelle
        $update_stmt = $db->prepare("UPDATE admin_users SET password = :password WHERE username = :username");
        $update_stmt->bindParam(':password', $hashed_password);
        $update_stmt->bindParam(':username', $_SESSION['admin_username']);
        $update_stmt->execute();

        $_SESSION['success_message'] = "Şifre başarıyla değiştirildi.";
        header('Location: settings.php');
        exit();

    } catch(Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}

// E-posta değişikliği işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_email'])) {
    $current_password = $_POST['current_password_email'] ?? '';
    $new_email = $_POST['new_email'] ?? '';

    // E-posta doğrulama
    try {
        // E-posta formatını kontrol et
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Geçerli bir e-posta adresi girin.");
        }

        // Veritabanından admin bilgilerini çek
        $stmt = $db->prepare("SELECT password, email FROM admin_users WHERE username = :username");
        $stmt->bindParam(':username', $_SESSION['admin_username']);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Mevcut şifreyi doğrula
        if (!password_verify($current_password, $admin['password'])) {
            throw new Exception("Şifre yanlış. E-posta değişikliği yapılamadı.");
        }

        // Aynı e-posta kontrolü
        if ($admin['email'] === $new_email) {
            throw new Exception("Yeni e-posta mevcut e-posta ile aynı olamaz.");
        }

        // E-postayı güncelle
        $update_stmt = $db->prepare("UPDATE admin_users SET email = :email WHERE username = :username");
        $update_stmt->bindParam(':email', $new_email);
        $update_stmt->bindParam(':username', $_SESSION['admin_username']);
        $update_stmt->execute();

        $_SESSION['success_message'] = "E-posta adresi başarıyla değiştirildi.";
        header('Location: settings.php');
        exit();

    } catch(Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}

$pageTitle = "Ayarlar";
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Admin Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center space-x-2">
                        <div class="bg-blue-600 p-2 rounded-lg">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                        </div>
                        <a href="dashboard.php" class="text-xl font-bold text-gray-900">WebDev Pro Admin</a>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="dashboard.php" class="text-gray-600 hover:text-blue-600">Dashboard</a>
                        <a href="view-projects.php" class="text-gray-600 hover:text-blue-600">Projeler</a>
                        <a href="view-messages.php" class="text-gray-600 hover:text-blue-600">Mesajlar</a>
                        <a href="settings.php" class="text-blue-600 font-medium">Ayarlar</a>
                        <a href="dashboard.php?logout=1" class="text-red-600 hover:text-red-800">Çıkış Yap</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white p-8 rounded-lg shadow-md">
                    <div class="flex items-center justify-between mb-6">
                        <h1 class="text-2xl font-bold text-gray-800"><?php echo $pageTitle; ?></h1>
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">Site Yapılandırması</span>
                    </div>

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm"><?php echo $_SESSION['success_message']; ?></p>
                                </div>
                            </div>
                            <?php unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm"><?php echo $_SESSION['error_message']; ?></p>
                                </div>
                            </div>
                            <?php unset($_SESSION['error_message']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="settings.php" method="POST" enctype="multipart/form-data">
                        <!-- Genel Ayarlar Bölümü -->
                        <div class="mb-8 pb-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Genel Ayarlar
                            </h2>
                            <div class="grid grid-cols-1 gap-6">
                                <div class="mb-4">
                                    <label for="site_title" class="block text-gray-700 text-sm font-medium mb-2">Site Başlığı</label>
                                    <input type="text" id="site_title" name="site_title" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="<?php echo isset($settings_data['site_title']) ? htmlspecialchars($settings_data['site_title']) : ''; ?>">
                                    <p class="mt-1 text-sm text-gray-500">Sitenizin başlığı tarayıcı sekmesinde ve arama sonuçlarında görünecektir.</p>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="site_description" class="block text-gray-700 text-sm font-medium mb-2">Site Açıklaması</label>
                                    <textarea id="site_description" name="site_description" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" rows="3"><?php echo isset($settings_data['site_description']) ? htmlspecialchars($settings_data['site_description']) : ''; ?></textarea>
                                    <p class="mt-1 text-sm text-gray-500">Bu açıklama arama motorlarında ve sosyal medya paylaşımlarında görünecektir.</p>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="site_email" class="block text-gray-700 text-sm font-medium mb-2">Site E-posta Adresi</label>
                                    <input type="email" id="site_email" name="site_email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="<?php echo isset($settings_data['site_email']) ? htmlspecialchars($settings_data['site_email']) : ''; ?>">
                                    <p class="mt-1 text-sm text-gray-500">İletişim için kullanılacak site e-posta adresi.</p>
                                </div>

                                <div class="mb-4">
                                    <label for="theme_color" class="block text-gray-700 text-sm font-medium mb-2">Tema Rengi</label>
                                    <input type="color" id="theme_color" name="theme_color" value="<?php echo isset($settings_data['theme_color']) ? htmlspecialchars($settings_data['theme_color']) : '#3490dc'; ?>" class="w-16 h-10 p-0 border-none">
                                    <p class="mt-1 text-sm text-gray-500">Site ana rengini seçin.</p>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="site_logo" class="block text-gray-700 text-sm font-medium mb-2">Site Logosu</label>
                                    <div class="flex items-center space-x-4">
                                        <?php if (!empty($settings_data['site_logo'])): ?>
                                        <div class="relative w-32 h-32 border rounded-md overflow-hidden bg-gray-100 flex items-center justify-center">
                                            <img src="../<?php echo htmlspecialchars($settings_data['site_logo']); ?>" alt="Site Logosu" class="max-w-full max-h-full object-contain">
                                        </div>
                                        <?php else: ?>
                                        <div class="w-32 h-32 border rounded-md overflow-hidden bg-gray-100 flex items-center justify-center text-gray-400">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <?php endif; ?>
                                        <div class="flex-1">
                                            <div class="relative">
                                                <input type="file" id="site_logo" name="site_logo" class="hidden" accept="image/*">
                                                <label for="site_logo" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-200 transition cursor-pointer">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                                    </svg>
                                                    Logo Yükle
                                                </label>
                                            </div>
                                            <p class="mt-2 text-sm text-gray-500">Önerilen boyut: 200x200 piksel. Maksimum dosya boyutu: 2MB.<br>İzin verilen formatlar: JPG, PNG, GIF, SVG</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sosyal Medya Bölümü -->
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Sosyal Medya Bağlantıları
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="mb-4">
                                    <label for="social_facebook" class="block text-gray-700 text-sm font-medium mb-2">Facebook</label>
                                    <div class="flex">
                                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500">
                                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M20 10c0-5.523-4.477-10-10-10S0 4.477 0 10c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V10h2.54V7.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V10h2.773l-.443 2.89h-2.33v6.988C16.343 19.128 20 14.991 20 10z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                        <input type="url" id="social_facebook" name="social_facebook" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent border border-gray-300" value="<?php echo isset($settings_data['social_facebook']) ? htmlspecialchars($settings_data['social_facebook']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="social_twitter" class="block text-gray-700 text-sm font-medium mb-2">Twitter</label>
                                    <div class="flex">
                                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500">
                                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M6.29 18.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0020 3.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.073 4.073 0 01.8 7.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 010 16.407a11.616 11.616 0 006.29 1.84"/>
                                            </svg>
                                        </span>
                                        <input type="url" id="social_twitter" name="social_twitter" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent border border-gray-300" value="<?php echo isset($settings_data['social_twitter']) ? htmlspecialchars($settings_data['social_twitter']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="social_instagram" class="block text-gray-700 text-sm font-medium mb-2">Instagram</label>
                                    <div class="flex">
                                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500">
                                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                        <input type="url" id="social_instagram" name="social_instagram" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent border border-gray-300" value="<?php echo isset($settings_data['social_instagram']) ? htmlspecialchars($settings_data['social_instagram']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="social_linkedin" class="block text-gray-700 text-sm font-medium mb-2">LinkedIn</label>
                                    <div class="flex">
                                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500">
                                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.338 16.338H13.67V12.16c0-.995-.017-2.277-1.387-2.277-1.39 0-1.601 1.086-1.601 2.207v4.248H8.014v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.778 3.203 4.092v4.711zM5.005 6.575a1.548 1.548 0 11-.003-3.096 1.548 1.548 0 01.003 3.096zm-1.337 9.763H6.34v-8.59H3.667v8.59zM17.668 1H2.328C1.595 1 1 1.581 1 2.298v15.403C1 18.418 1.595 19 2.328 19h15.34c.734 0 1.332-.582 1.332-1.299V2.298C19 1.581 18.402 1 17.668 1z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                        <input type="url" id="social_linkedin" name="social_linkedin" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent border border-gray-300" value="<?php echo isset($settings_data['social_linkedin']) ? htmlspecialchars($settings_data['social_linkedin']) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                      
                        <!-- Şifre Değişikliği Bölümü - Form etiketi kaldırıldı -->
                        <div class="mt-8">
                            <div class="bg-white p-8 rounded-lg shadow-md">
                                <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                                    <svg class="w-6 h-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Şifre Değişikliği
                                </h2>
                                
                                <!-- <form action="settings.php" method="POST"> - BU SATIR KALDIRILDI -->
                                    <input type="hidden" name="change_password" value="1">
                                    
                                    <div class="grid grid-cols-1 gap-6">
                                        <div>
                                            <label for="current_password" class="block text-sm font-medium text-gray-700">Mevcut Şifre</label>
                                            <div class="mt-1">
                                                <input type="password" name="current_password" id="current_password" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                                            </div>
                                        </div>
                                
                                        <div>
                                            <label for="new_password" class="block text-sm font-medium text-gray-700">Yeni Şifre</label>
                                            <div class="mt-1">
                                                <input type="password" name="new_password" id="new_password" required minlength="8" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                                                <p class="mt-2 text-sm text-gray-500">En az 8 karakter uzunluğunda olmalıdır</p>
                                            </div>
                                        </div>
                                
                                        <div>
                                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Yeni Şifreyi Onayla</label>
                                            <div class="mt-1">
                                                <input type="password" name="confirm_password" id="confirm_password" required minlength="8" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                                            </div>
                                        </div>
                                    </div>
                                
                                    <div class="mt-6">
                                        <button type="submit" name="password_submit" class="w-full bg-red-600 text-white py-3 px-4 rounded-md hover:bg-red-700 transition-colors flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                            </svg>
                                            Şifreyi Değiştir
                                        </button>
                                    </div>
                                <!-- </form> - BU SATIR KALDIRILDI -->
                            </div>
                        </div>
                        
                        <!-- E-posta Değişikliği Bölümü - Form etiketi kaldırıldı -->
                        <div class="mt-8">
                            <div class="bg-white p-8 rounded-lg shadow-md">
                                <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                                    <svg class="w-6 h-6 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    E-posta Değişikliği
                                </h2>
                                
                                <!-- <form action="settings.php" method="POST"> - BU SATIR KALDIRILDI -->
                                    <input type="hidden" name="change_email" value="1">
                                    
                                    <div class="grid grid-cols-1 gap-6">
                                        <div>
                                            <label for="current_email" class="block text-sm font-medium text-gray-700">Mevcut E-posta</label>
                                            <div class="mt-1">
                                                <input type="email" value="<?php echo htmlspecialchars($settings_data['admin_email'] ?? ''); ?>" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100">
                                            </div>
                                        </div>
                                
                                        <div>
                                            <label for="new_email" class="block text-sm font-medium text-gray-700">Yeni E-posta Adresi</label>
                                            <div class="mt-1">
                                                <input type="email" name="new_email" id="new_email" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="ornek@email.com">
                                            </div>
                                        </div>
                                
                                        <div>
                                            <label for="current_password_email" class="block text-sm font-medium text-gray-700">Şifrenizi Onaylayın</label>
                                            <div class="mt-1">
                                                <input type="password" name="current_password_email" id="current_password_email" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                                                <p class="mt-2 text-sm text-gray-500">E-posta değişikliği için mevcut şifrenizi girin</p>
                                            </div>
                                        </div>
                                    </div>
                                
                                    <div class="mt-6">
                                        <button type="submit" name="email_submit" class="w-full bg-green-600 text-white py-3 px-4 rounded-md hover:bg-green-700 transition-colors flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            E-postayı Güncelle
                                        </button>
                                    </div>
                                <!-- </form> - BU SATIR KALDIRILDI -->
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                            <a href="dashboard.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Dashboard'a Dön
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Ayarları Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t py-4 mt-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        &copy; <?php echo date('Y'); ?> WebDev Pro Admin Panel. Tüm hakları saklıdır.
                    </div>
                    <div class="text-sm text-gray-500">
                        Sürüm 1.0
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>