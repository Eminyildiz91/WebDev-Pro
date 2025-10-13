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

// Proje ID'sini kontrol et
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: view-projects.php');
    exit;
}

$project_id = $_GET['id'];

// Projeyi veritabanından çek
try {
    $query = "SELECT * FROM projects WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $project_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        header('Location: view-projects.php');
        exit;
    }
} catch(PDOException $e) {
    $error_message = "Veritabanı hatası: " . $e->getMessage();
}

// Telefon, WhatsApp ve durum alanlarının varlığını kontrol et ve yoksa ekle, varsa boyutunu güncelle
try {
    // Phone alanını kontrol et
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
    }
    
    // Proje durumu alanını kontrol et
    $check_status = "SHOW COLUMNS FROM projects LIKE 'status'";
    $status_result = $db->query($check_status);
    
    // Eğer status alanı yoksa, ekle
    if ($status_result->rowCount() == 0) {
        $add_status = "ALTER TABLE projects ADD COLUMN status VARCHAR(20) DEFAULT 'not_started'";
        $db->exec($add_status);
    }
    
    // is_visible alanını kontrol et
    $check_visible = "SHOW COLUMNS FROM projects LIKE 'is_visible'";
    $visible_result = $db->query($check_visible);
    
    // Eğer is_visible alanı yoksa, ekle
    if ($visible_result->rowCount() == 0) {
        $add_visible = "ALTER TABLE projects ADD COLUMN is_visible BOOLEAN DEFAULT TRUE";
        $db->exec($add_visible);
    }
    
    // phone_visible alanını kontrol et
    $check_phone_visible = "SHOW COLUMNS FROM projects LIKE 'phone_visible'";
    $phone_visible_result = $db->query($check_phone_visible);
    
    // Eğer phone_visible alanı yoksa, ekle
    if ($phone_visible_result->rowCount() == 0) {
        $add_phone_visible = "ALTER TABLE projects ADD COLUMN phone_visible BOOLEAN DEFAULT TRUE";
        $db->exec($add_phone_visible);
    }
    
    // whatsapp_visible alanını kontrol et
    $check_whatsapp_visible = "SHOW COLUMNS FROM projects LIKE 'whatsapp_visible'";
    $whatsapp_visible_result = $db->query($check_whatsapp_visible);
    
    // Eğer whatsapp_visible alanı yoksa, ekle
    if ($whatsapp_visible_result->rowCount() == 0) {
        $add_whatsapp_visible = "ALTER TABLE projects ADD COLUMN whatsapp_visible BOOLEAN DEFAULT TRUE";
        $db->exec($add_whatsapp_visible);
    }
} catch(PDOException $e) {
    $error_message = "Veritabanı hatası: " . $e->getMessage();
}

// Form gönderildi mi kontrol et
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
    $project_status = htmlspecialchars($_POST['project_status'] ?? 'not_started');
    $project_visible = isset($_POST['project_visible']) ? 1 : 0;
    $project_phone_visible = isset($_POST['project_phone_visible']) ? 1 : 0;
    $project_whatsapp_visible = isset($_POST['project_whatsapp_visible']) ? 1 : 0;
    
    try {
        // Projeyi güncelle
        $query = "UPDATE projects 
                 SET name = :name, 
                     description = :description, 
                     category = :category, 
                     client = :client, 
                     date = :date, 
                     url = :url,
                     phone = :phone,
                     whatsapp = :whatsapp,
                     status = :status,
                     is_visible = :is_visible,
                     phone_visible = :phone_visible,
                     whatsapp_visible = :whatsapp_visible
                 WHERE id = :id";
        
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
        $stmt->bindParam(':phone_visible', $project_phone_visible);
        $stmt->bindParam(':whatsapp_visible', $project_whatsapp_visible);
        $stmt->bindParam(':id', $project_id);
        
        // Sorguyu çalıştır
        if ($stmt->execute()) {
            $success_message = "Proje başarıyla güncellendi!";
        } else {
            $error_message = "Proje güncellenirken bir hata oluştu.";
        }
    } catch(PDOException $e) {
        $error_message = "Veritabanı hatası: " . $e->getMessage();
    }
}

// Proje verilerini al
try {
    $query = "SELECT * FROM projects WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $project_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        header('Location: view-projects.php');
        exit;
    }
} catch(PDOException $e) {
    $error_message = "Veritabanı hatası: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proje Düzenle - WebDev Pro Admin</title>
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
                <h1 class="text-3xl font-bold text-gray-900">Proje Düzenle</h1>
                <p class="text-gray-600">Mevcut projeyi güncelleyin</p>
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
                                value="<?php echo htmlspecialchars($project['name']); ?>" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="project_category" class="block text-sm font-medium text-gray-700">Kategori</label>
                            <select name="project_category" id="project_category" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="web" <?php echo ($project['category'] == 'web') ? 'selected' : ''; ?>>Web Geliştirme</option>
                                <option value="mobile" <?php echo ($project['category'] == 'mobile') ? 'selected' : ''; ?>>Mobil Uygulama</option>
                                <option value="desktop" <?php echo ($project['category'] == 'desktop') ? 'selected' : ''; ?>>Masaüstü Uygulama</option>
                                <option value="design" <?php echo ($project['category'] == 'design') ? 'selected' : ''; ?>>UI/UX Tasarım</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="project_status" class="block text-sm font-medium text-gray-700">Proje Durumu</label>
                            <select name="project_status" id="project_status" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="not_started" <?php echo (!isset($project['status']) || $project['status'] == 'not_started') ? 'selected' : ''; ?>>Başlamadı</option>
                                <option value="started" <?php echo (isset($project['status']) && $project['status'] == 'started') ? 'selected' : ''; ?>>Başladı</option>
                                <option value="in_progress" <?php echo (isset($project['status']) && $project['status'] == 'in_progress') ? 'selected' : ''; ?>>Devam Ediyor</option>
                                <option value="completed" <?php echo (isset($project['status']) && $project['status'] == 'completed') ? 'selected' : ''; ?>>Tamamlandı</option>
                            </select>
                            <p class="mt-1 text-sm text-gray-500">Projenin mevcut durumunu seçin</p>
                        </div>
                        
                        <div>
                            <label for="project_client" class="block text-sm font-medium text-gray-700">Müşteri</label>
                            <input type="text" name="project_client" id="project_client" required 
                                value="<?php echo htmlspecialchars($project['client']); ?>" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="project_date" class="block text-sm font-medium text-gray-700">Proje Tarihi</label>
                            <input type="date" name="project_date" id="project_date" required 
                                value="<?php echo htmlspecialchars($project['date']); ?>" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="project_url" class="block text-sm font-medium text-gray-700">Proje URL</label>
                            <input type="url" name="project_url" id="project_url" 
                                value="<?php echo htmlspecialchars($project['url']); ?>" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex-grow">
                                <label for="project_phone" class="block text-sm font-medium text-gray-700">Telefon Numarası</label>
                                <input type="tel" name="project_phone" id="project_phone" 
                                    value="<?php echo htmlspecialchars($project['phone'] ?? ''); ?>" 
                                    placeholder="+90 (555) 123-4567"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <p class="mt-1 text-sm text-gray-500">Proje için iletişim telefon numarası</p>
                            </div>
                            <div class="ml-4 flex items-center">
                                <input type="checkbox" name="project_phone_visible" id="project_phone_visible" 
                                    <?php echo (isset($project['phone_visible']) && $project['phone_visible']) ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="project_phone_visible" class="ml-2 block text-sm text-gray-700">Görünür</label>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex-grow">
                                <label for="project_whatsapp" class="block text-sm font-medium text-gray-700">WhatsApp Numarası</label>
                                <input type="tel" name="project_whatsapp" id="project_whatsapp" 
                                    value="<?php echo htmlspecialchars($project['whatsapp'] ?? ''); ?>" 
                                    placeholder="+90 (555) 123-4567"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <p class="mt-1 text-sm text-gray-500">WhatsApp üzerinden iletişim için numara</p>
                            </div>
                            <div class="ml-4 flex items-center">
                                <input type="checkbox" name="project_whatsapp_visible" id="project_whatsapp_visible" 
                                    <?php echo (isset($project['whatsapp_visible']) && $project['whatsapp_visible']) ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="project_whatsapp_visible" class="ml-2 block text-sm text-gray-700">Görünür</label>
                            </div>
                        </div>
                        
                        <div class="col-span-1 md:col-span-2 flex items-center">
                            <input type="checkbox" name="project_visible" id="project_visible" 
                                <?php echo (isset($project['is_visible']) && $project['is_visible']) ? 'checked' : ''; ?>
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="project_visible" class="ml-2 block text-sm font-medium text-gray-700">
                                Proje bilgilerini görünür yap (Genel proje bilgileri)
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label for="project_description" class="block text-sm font-medium text-gray-700">Proje Açıklaması</label>
                        <textarea name="project_description" id="project_description" rows="4" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($project['description']); ?></textarea>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div class="flex space-x-2">
                            <a href="view-project.php?id=<?php echo $project_id; ?>" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">Görüntüle</a>
                            <a href="view-projects.php?delete=<?php echo $project_id; ?>" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors" onclick="return confirm('Bu projeyi silmek istediğinizden emin misiniz?')">Sil</a>
                            <a href="view-projects.php" class="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">Geri Dön</a>
                        </div>
                        <button type="submit" name="submit" 
                            class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                            Projeyi Güncelle
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>