<?php
// 1. Hata raporlamayı aktif edelim
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Session kontrolü
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

// 3. Veritabanı bağlantısını kontrol edelim
try {
    require_once '../../config/database.php';
    $database = new Database();
    $pdo = $database->getConnection();
} catch (PDOException $e) {
    die('Veritabanı bağlantı hatası: ' . $e->getMessage());
}

// 4. Gerekli tabloları oluştur (önce admin_users, sonra settings)
// Admin kullanıcıları tablosunu oluştur (MYSQL ve SQLite uyumlu)
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
try {
    if ($driver === 'mysql') {
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            email VARCHAR(100),
            phone VARCHAR(20),
            status TINYINT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            last_ip VARCHAR(45),
            login_count INT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        // SQLite için
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            email VARCHAR(100),
            phone VARCHAR(20),
            status INTEGER DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            last_ip VARCHAR(45),
            login_count INTEGER DEFAULT 0
        )");
    }
    
    // Varsayılan admin kullanıcısı ekle (admin_users tablosunda kayıt yoksa)
    $stmt = $pdo->query("SELECT COUNT(*) FROM admin_users");
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, full_name, email, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            'admin',
            password_hash('admin123', PASSWORD_DEFAULT),
            'Admin User',
            'admin@example.com',
            1
        ]);
    }
} catch (PDOException $e) {
    die('admin_users tablosu hatası: ' . $e->getMessage());
}

// Settings tablosunu oluştur
try {
    if ($driver === 'mysql') {
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_title VARCHAR(255) DEFAULT 'Hesap Ayarları',
            page_subtitle VARCHAR(255) DEFAULT 'Admin Panel',
            theme_color VARCHAR(7) DEFAULT '#3490dc',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        // SQLite için
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            page_title VARCHAR(255) DEFAULT 'Hesap Ayarları',
            page_subtitle VARCHAR(255) DEFAULT 'Admin Panel',
            theme_color VARCHAR(7) DEFAULT '#3490dc',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }
    
    // Varsayılan ayarları ekle (kayıt yoksa)
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO settings (page_title, page_subtitle, theme_color) VALUES ('Hesap Ayarları', 'Admin Panel', '#3490dc')");
    }
} catch (PDOException $e) {
    // Hata durumunda varsayılan değerleri kullanıyoruz.
    $settings_data = [
        'page_title' => 'Hesap Ayarları',
        'page_subtitle' => 'Admin Panel',
        'theme_color' => '#3490dc'
    ];
}

// 5. Artık admin_users tablosu mevcut olduğuna göre admin bilgilerini alalım
try {
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    if (!$stmt->execute([$_SESSION['admin_id']])) {
        throw new PDOException('Sorgu çalıştırılamadı');
    }
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        session_destroy();
        header("Location: ../index.php?error=invalid_session");
        exit();
    }
} catch (PDOException $e) {
    die('Admin bilgileri alınamadı: ' . $e->getMessage());
}

// Varsayılan admin kontrolü (tekrar eklenmiyor çünkü tabloda en az bir kayıt zaten var)

// Mesaj değişkenleri
$success_message = '';
$error_message = '';

// Sanitize fonksiyonu
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Profil güncelleme
if (isset($_POST['update_profile'])) {
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    
    $update = $pdo->prepare("UPDATE admin_users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
    $update->execute([$full_name, $email, $phone, $admin['id']]);
    $success_message = 'Profil başarıyla güncellendi.';
    
    // Güncellenmiş bilgileri tekrar al
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$admin['id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Şifre değiştirme
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $stmt = $pdo->prepare("SELECT password FROM admin_users WHERE id = ?");
    $stmt->execute([$admin['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
                $update->execute([$hashed_password, $admin['id']]);
                $success_message = 'Şifreniz başarıyla değiştirildi.';
            } else {
                $error_message = 'Yeni şifre en az 6 karakter olmalıdır.';
            }
        } else {
            $error_message = 'Yeni şifreler eşleşmiyor.';
        }
    } else {
        $error_message = 'Mevcut şifre yanlış.';
    }
}

// Hesap silme
if (isset($_POST['delete_account'])) {
    $password = $_POST['confirm_password_delete'];
    $stmt = $pdo->prepare("SELECT password FROM admin_users WHERE id = ?");
    $stmt->execute([$admin['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify($password, $user['password'])) {
        $delete = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
        $delete->execute([$admin['id']]);
        session_destroy();
        header("Location: ../index.php?deleted=1");
        exit();
    } else {
        $error_message = 'Hesabı silmek için şifrenizi doğru girmelisiniz.';
    }
}

// Session değişkenlerini ayarla
$_SESSION['admin_id'] = $admin['id'];

// Settings verisini al
try {
    $stmt = $pdo->query("SELECT theme_color FROM settings LIMIT 1");
    $settings_data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $settings_data = ['theme_color' => '#3490dc'];
}

// Sayfa başlığını güncelleme özelliği
if (isset($_POST['update_page_title'])) {
    $page_title = sanitize_input($_POST['page_title']);
    $page_subtitle = sanitize_input($_POST['page_subtitle']);
    
    // Settings tablosunu kontrol et ve oluştur
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            page_title VARCHAR(255) DEFAULT 'Hesap Ayarları',
            page_subtitle VARCHAR(255) DEFAULT 'Admin Panel',
            theme_color VARCHAR(7) DEFAULT '#3490dc',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        $stmt = $pdo->prepare("UPDATE settings SET page_title = ?, page_subtitle = ?, updated_at = CURRENT_TIMESTAMP WHERE id = 1");
        $stmt->execute([$page_title, $page_subtitle]);
        
        // Eğer kayıt yoksa ekle
        if ($stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO settings (page_title, page_subtitle) VALUES (?, ?)");
            $stmt->execute([$page_title, $page_subtitle]);
        }
        
        $success_message = 'Sayfa başlığı başarıyla güncellendi.';
    } catch (Exception $e) {
        $error_message = 'Sayfa başlığı güncellenirken hata oluştu.';
    }
}

// Settings verisini al
try {
    $stmt = $pdo->query("SELECT page_title, page_subtitle, theme_color FROM settings LIMIT 1");
    $settings_data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $settings_data = [
        'page_title' => 'Hesap Ayarları',
        'page_subtitle' => 'Admin Panel',
        'theme_color' => '#3490dc'
    ];
}

// Varsayılan settings oluştur
if (empty($settings_data)) {
    $settings_data = [
        'page_title' => 'Hesap Ayarları',
        'page_subtitle' => 'Admin Panel',
        'theme_color' => '#3490dc'
    ];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesap Ayarları - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .prose {
            max-width: none;
        }
        .prose p {
            margin-bottom: 1rem;
            line-height: 1.7;
        }
        .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .prose ul, .prose ol {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }
        .prose li {
            margin-bottom: 0.5rem;
        }

        :root {
            --primary-theme-color: <?php echo isset($settings_data['theme_color']) ? htmlspecialchars($settings_data['theme_color']) : '#3490dc'; ?>;
        }
        .bg-theme {
            background-color: var(--primary-theme-color);
        }
        .text-theme {
            color: var(--primary-theme-color);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($settings_data['page_title']); ?></h1>
                    <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($settings_data['page_subtitle']); ?></p>
                </div>
                <a href="../dashboard.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Dashboard'a Dön
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="flex">
            <!-- Küçük Sidebar -->
            <div class="w-64 bg-white shadow rounded-lg mr-6 h-fit">
                <div class="p-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Hızlı Erişim</h2>
                    <nav class="space-y-2">
                        <a href="#security" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-gray-900">
                            <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Güvenlik Bilgileri
                        </a>
                      
                        <a href="#profile" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-gray-900">
                            <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profil Bilgileri
                        </a>
                        <a href="#logo" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-gray-900">
                            <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Logo & İkon
                        </a>
                        <a href="#password" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-gray-900">
                            <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            Şifre Değiştir
                        </a>
                        <a href="#delete" class="flex items-center px-3 py-2 text-sm font-medium text-red-600 rounded-md hover:bg-red-50 hover:text-red-700">
                            <svg class="h-5 w-5 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hesabı Sil
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Ana İçerik -->
            <div class="flex-1">
                <div class="px-4 py-6 sm:px-0">
                    <!-- Mesajlar -->
                    <?php if ($success_message): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline"><?php echo $success_message; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline"><?php echo $error_message; ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Güvenlik Bilgileri -->
                        <div id="security" class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:px-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Güvenlik Bilgileri</h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">Hesap güvenliği detayları ve son aktiviteler.</p>
                            </div>
                            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <!-- Kullanıcı ID ekledik -->
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500">Kullanıcı ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($admin['id']); ?></dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500">Kullanıcı Adı</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($admin['username']); ?></dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500">Tam Ad</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <?php 
                                            echo !empty($admin['full_name']) ? htmlspecialchars($admin['full_name']) : 'Belirtilmemiş';
                                            ?>
                                        </dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500">E-posta</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($admin['email']); ?></dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500">Telefon</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($admin['phone'] ?? 'Belirtilmemiş'); ?></dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500">Son Giriş Tarihi</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <?php 
                                            echo (isset($admin['last_login']) && $admin['last_login']) 
                                                ? date('d.m.Y H:i', strtotime($admin['last_login'])) 
                                                : 'Henüz giriş yapılmadı';
                                            ?>
                                        </dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500">Son IP Adresi</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($admin['last_ip'] ?? 'Bilinmiyor'); ?></dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500">Hesap Oluşturma Tarihi</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?php echo date('d.m.Y H:i', strtotime($admin['created_at'])); ?></dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500">Son Güncelleme</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <?php echo ($admin['updated_at']) ? date('d.m.Y H:i', strtotime($admin['updated_at'])) : 'Güncelleme yok'; ?>s
                                        </dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500">Toplam Giriş Sayısı</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?php echo number_format($admin['login_count'] ?? 0); ?></dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500">Hesap Durumu</dt>
                                        <dd class="mt-1">
                                            <?php if (($admin['status'] ?? 0) == 1): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Pasif</span>
                                            <?php endif; ?>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Logo ve İkon Yönetimi -->
                        <div id="logo" class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:px-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Logo ve İkon Yönetimi</h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">Site logosu ve favicon yönetimi.</p>
                            </div>
                            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                                <p class="text-gray-500 text-center py-8">Logo yönetimi özelliği yakında eklenecek.</p>
                            </div>
                        </div>

                        <!-- Profil Bilgileri -->
                        <div id="profile" class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:px-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Profil Bilgileri</h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">Hesap bilgilerinizi güncelleyin.</p>
                            </div>
                            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                                <form method="POST" class="space-y-6">
                                    <div>
                                        <label for="full_name" class="block text-sm font-medium text-gray-700">Tam Ad</label>
                                        <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($admin['full_name'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">E-posta</label>
                                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700">Telefon</label>
                                        <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <button type="submit" name="update_profile" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Profili Güncelle
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Şifre Değiştirme -->
                        <div id="password" class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:px-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Şifre Değiştir</h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">Hesap şifrenizi güvenli bir şekilde değiştirin.</p>
                            </div>
                            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                                <form method="POST" class="space-y-6">
                                    <div>
                                        <label for="current_password" class="block text-sm font-medium text-gray-700">Mevcut Şifre</label>
                                        <input type="password" name="current_password" id="current_password" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="new_password" class="block text-sm font-medium text-gray-700">Yeni Şifre</label>
                                        <input type="password" name="new_password" id="new_password" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Yeni Şifre (Tekrar)</label>
                                        <input type="password" name="confirm_password" id="confirm_password" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <button type="submit" name="change_password" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Şifreyi Değiştir
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Hesap Silme -->
                        <div id="delete" class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:px-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Hesabı Sil</h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">Hesabınızı kalıcı olarak silmek istediğinizden emin misiniz?</p>
                            </div>
                            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                                <form method="POST" class="space-y-6" onsubmit="return confirm('Hesabınızı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')">
                                    <div>
                                        <label for="confirm_password_delete" class="block text-sm font-medium text-gray-700">Şifrenizi Onaylayın</label>
                                        <input type="password" name="confirm_password_delete" id="confirm_password_delete" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500">
                                    </div>
                                    <div>
                                        <button type="submit" name="delete_account" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            Hesabı Kalıcı Olarak Sil
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white shadow mt-8">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    © <?php echo date('Y'); ?> Tüm hakları saklıdır.
                </div>
                <div class="flex items-center space-x-4">
                    <a href="../dashboard.php" class="text-sm text-gray-500 hover:text-gray-700">Dashboard</a>
                    <a href="../settings.php" class="text-sm text-gray-500 hover:text-gray-700">Ayarlar</a>
                    <a href="../help.php" class="text-sm text-gray-500 hover:text-gray-700">Yardım</a>
                    <a href="../logout.php" class="text-sm text-red-500 hover:text-red-700">Çıkış Yap</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Form gönderimlerinde onay mesajı
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (form.querySelector('button[name="delete_account"]')) {
                    if (!confirm('Hesabınızı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')) {
                        e.preventDefault();
                    }
                }
            });
        });

        // Başarı ve hata mesajlarını otomatik gizle
        setTimeout(() => {
            document.querySelectorAll('.bg-green-100, .bg-red-100').forEach(el => {
                el.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>
