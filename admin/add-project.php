<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: index.php');
    exit;
}

// Veritabanı bağlantısını dahil et
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Başarı ve hata mesajları için değişkenler
$success_message = '';
$error_message = '';

if (isset($_POST['submit'])) {
    // Form verilerini al
    $project_name = htmlspecialchars($_POST['project_name']);
    $project_description = htmlspecialchars($_POST['project_description']);
    $project_category = htmlspecialchars($_POST['project_category']);
    $project_client = htmlspecialchars($_POST['project_client']);
    $project_date = htmlspecialchars($_POST['project_date']);
    $project_url = htmlspecialchars($_POST['project_url']);
    $project_phone = htmlspecialchars($_POST['project_phone'] ?? '');
    $project_whatsapp = htmlspecialchars($_POST['project_whatsapp'] ?? '');
    $project_visible = isset($_POST['project_visible']) ? 1 : 0;
    $project_status = htmlspecialchars($_POST['project_status'] ?? 'not_started');
    
    try {
        // Önce projects tablosunun var olup olmadığını kontrol et
        $check_table = "SHOW TABLES LIKE 'projects'";
        $table_result = $db->query($check_table);
        
        // Tablo yoksa oluştur
        if ($table_result->rowCount() == 0) {
            $create_table = "CREATE TABLE projects (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                category VARCHAR(50) NOT NULL,
                client VARCHAR(255) NOT NULL,
                date DATE NOT NULL,
                url VARCHAR(255),
                phone VARCHAR(50),
                whatsapp VARCHAR(50),
                status VARCHAR(20) DEFAULT 'not_started',
                is_visible BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->exec($create_table);
        } else {
            // Tablo varsa, phone ve whatsapp alanlarının var olup olmadığını kontrol et ve boyutlarını güncelle
            $check_phone = "SHOW COLUMNS FROM projects LIKE 'phone'";
            $phone_result = $db->query($check_phone);
            
            // Eğer phone alanı yoksa, ekle
            if ($phone_result->rowCount() == 0) {
                $add_phone = "ALTER TABLE projects ADD COLUMN phone VARCHAR(50) DEFAULT NULL";
                $db->exec($add_phone);
            } else {
                // Eğer phone alanı varsa, boyutunu güncelle
                $modify_phone = "ALTER TABLE projects MODIFY COLUMN phone VARCHAR(50)";
                $db->exec($modify_phone);
            }
            
            // WhatsApp alanını kontrol et
            $check_whatsapp = "SHOW COLUMNS FROM projects LIKE 'whatsapp'";
            $whatsapp_result = $db->query($check_whatsapp);
            
            // Eğer whatsapp alanı yoksa, ekle
            if ($whatsapp_result->rowCount() == 0) {
                $add_whatsapp = "ALTER TABLE projects ADD COLUMN whatsapp VARCHAR(50) DEFAULT NULL";
                $db->exec($add_whatsapp);
            } else {
                // Eğer whatsapp alanı varsa, boyutunu güncelle
            $modify_whatsapp = "ALTER TABLE projects MODIFY COLUMN whatsapp VARCHAR(50)";
            $db->exec($modify_whatsapp);
            
            // is_visible alanını kontrol et
            $check_visible = "SHOW COLUMNS FROM projects LIKE 'is_visible'";
            $visible_result = $db->query($check_visible);
            
            // Eğer is_visible alanı yoksa, ekle
            if ($visible_result->rowCount() == 0) {
                $add_visible = "ALTER TABLE projects ADD COLUMN is_visible BOOLEAN DEFAULT TRUE";
                $db->exec($add_visible);
            }
        }
        }
        
        // Projeyi veritabanına ekle
        $query = "INSERT INTO projects (name, description, category, client, date, url, phone, whatsapp, status, is_visible) 
                 VALUES (:name, :description, :category, :client, :date, :url, :phone, :whatsapp, :status, :is_visible)";
        
        $stmt = $db->prepare($query);
        
        // Parametreleri bağla
        $stmt->bindParam(':name', $project_name);
        $stmt->bindParam(':description', $project_description);
        $stmt->bindParam(':category', $project_category);
        $stmt->bindParam(':client', $project_client);
        $stmt->bindParam(':date', $project_date);
        $stmt->bindParam(':url', $project_url);
        $stmt->bindParam(':phone', $project_phone);
        $stmt->bindParam(':whatsapp', $project_whatsapp);
        $stmt->bindParam(':status', $project_status);
        $stmt->bindParam(':is_visible', $project_visible);
        
        // Sorguyu çalıştır
        if ($stmt->execute()) {
            $success_message = "Proje başarıyla kaydedildi!";
        } else {
            $error_message = "Proje kaydedilirken bir hata oluştu.";
        }
    } catch(PDOException $e) {
        $error_message = "Veritabanı hatası: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Proje Ekle - WebDev Pro Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
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
                        <span class="text-xl font-bold text-gray-900">WebDev Pro Admin</span>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <a href="dashboard.php" class="text-gray-600 hover:text-gray-900">
                            Dashboard'a Dön
                        </a>
                        <a href="?logout=1" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                            Çıkış Yap
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Yeni Proje Ekle</h1>
                <p class="text-gray-600">Portfolyonuza yeni bir proje ekleyin</p>
            </div>
            
            <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow p-6">
                <form action="" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="project_name" class="block text-sm font-medium text-gray-700">Proje Adı</label>
                            <input type="text" name="project_name" id="project_name" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="project_category" class="block text-sm font-medium text-gray-700">Kategori</label>
                            <select name="project_category" id="project_category" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="web">Web Geliştirme</option>
                                <option value="mobile">Mobil Uygulama</option>
                                <option value="desktop">Masaüstü Uygulama</option>
                                <option value="design">UI/UX Tasarım</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="project_client" class="block text-sm font-medium text-gray-700">Müşteri</label>
                            <input type="text" name="project_client" id="project_client" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="project_date" class="block text-sm font-medium text-gray-700">Proje Tarihi</label>
                            <input type="date" name="project_date" id="project_date" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="project_url" class="block text-sm font-medium text-gray-700">Proje URL</label>
                            <input type="url" name="project_url" id="project_url" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="project_phone" class="block text-sm font-medium text-gray-700">Telefon Numarası</label>
                            <input type="tel" name="project_phone" id="project_phone" 
                                placeholder="+90 (555) 123-4567"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Proje için iletişim telefon numarası</p>
                        </div>
                        
                        <div>
                            <label for="project_whatsapp" class="block text-sm font-medium text-gray-700">WhatsApp Numarası</label>
                            <input type="tel" name="project_whatsapp" id="project_whatsapp" 
                                placeholder="+90 (555) 123-4567"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-sm text-gray-500">WhatsApp üzerinden iletişim için numara</p>
                        </div>
                        
                        <div>
                            <label for="project_status" class="block text-sm font-medium text-gray-700">Proje Durumu</label>
                            <select name="project_status" id="project_status" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="not_started">Başlamadı</option>
                                <option value="started">Başladı</option>
                                <option value="in_progress">Devam Ediyor</option>
                                <option value="completed">Tamamlandı</option>
                            </select>
                        </div>

                        <div class="col-span-1 md:col-span-2 flex items-center">
                            <input type="checkbox" name="project_visible" id="project_visible" checked
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="project_visible" class="ml-2 block text-sm font-medium text-gray-700">
                                Proje bilgilerini görünür yap (Telefon ve WhatsApp numaraları dahil)
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label for="project_description" class="block text-sm font-medium text-gray-700">Proje Açıklaması</label>
                        <textarea name="project_description" id="project_description" rows="4" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <a href="dashboard.php" class="bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600 transition-colors">
                            Geri Dön
                        </a>
                        <button type="submit" name="submit" 
                            class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                            Projeyi Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>