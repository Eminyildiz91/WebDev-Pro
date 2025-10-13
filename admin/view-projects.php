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

// Proje silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $project_id = $_GET['delete'];
    
    try {
        $delete_query = "DELETE FROM projects WHERE id = :id";
        $stmt = $db->prepare($delete_query);
        $stmt->bindParam(':id', $project_id);
        
        if ($stmt->execute()) {
            $success_message = "Proje başarıyla silindi!";
        } else {
            $error_message = "Proje silinirken bir hata oluştu.";
        }
    } catch(PDOException $e) {
        $error_message = "Veritabanı hatası: " . $e->getMessage();
    }
}

// Projeleri veritabanından çek
$projects = [];
try {
    // Önce projects tablosunun var olup olmadığını kontrol et
    $table_exists = false;
    if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'mysql') {
        $check_table = "SHOW TABLES LIKE 'projects'";
        $table_result = $db->query($check_table);
        $table_exists = $table_result->rowCount() > 0;
    } elseif ($db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
        $check_table = "SELECT name FROM sqlite_master WHERE type='table' AND name='projects'";
        $table_result = $db->query($check_table);
        $table_exists = $table_result->rowCount() > 0;
    }
    
    if ($table_exists) {
        $query = "SELECT * FROM projects ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Projeleri Görüntüle - WebDev Pro Admin</title>
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
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Projeleri Görüntüle</h1>
                    <p class="text-gray-600">Tüm projelerinizi yönetin</p>
                </div>
                <a href="add-project.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span>Yeni Proje Ekle</span>
                </a>
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
            
            <!-- Buton Renk Açıklamaları -->
            <div class="bg-gray-100 border border-gray-300 text-gray-700 px-4 py-3 rounded relative mb-6" role="alert">
                <div class="font-medium mb-1">İşlem Butonları Renk Kodlaması:</div>
                <div class="flex items-center space-x-4 text-sm">
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 bg-blue-600 rounded-full mr-2"></span>
                        <span>Mavi: Görüntüleme işlemi</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 bg-indigo-600 rounded-full mr-2"></span>
                        <span>İndigo: Düzenleme işlemi</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 bg-red-600 rounded-full mr-2"></span>
                        <span>Kırmızı: Silme işlemi</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <?php if (empty($projects)): ?>
                <div class="p-8 text-center">
                    <svg class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-600 mb-4">Henüz hiç proje eklenmemiş.</p>
                    <a href="add-project.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center space-x-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>İlk Projeyi Ekle</span>
                    </a>
                </div>
                <?php else: ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proje Adı</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefon</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">WhatsApp</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Görünür</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($projects as $project): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <a href="view-project.php?id=<?php echo $project['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                        <?php echo htmlspecialchars($project['name']); ?>
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($project['category'])): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?php echo htmlspecialchars($project['category']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($project['client']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d.m.Y', strtotime($project['date'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo !empty($project['phone']) ? htmlspecialchars($project['phone']) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo !empty($project['whatsapp']) ? htmlspecialchars($project['whatsapp']) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if (isset($project['is_visible']) && $project['is_visible']): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Evet</span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Hayır</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php 
                                $status = isset($project['status']) ? $project['status'] : 'not_started';
                                $status_text = '';
                                $status_color = '';
                                
                                switch($status) {
                                    case 'not_started':
                                        $status_text = 'Başlamadı';
                                        $status_color = 'bg-gray-100 text-gray-800';
                                        break;
                                    case 'started':
                                        $status_text = 'Başladı';
                                        $status_color = 'bg-blue-100 text-blue-800';
                                        break;
                                    case 'in_progress':
                                        $status_text = 'Devam Ediyor';
                                        $status_color = 'bg-yellow-100 text-yellow-800';
                                        break;
                                    case 'completed':
                                        $status_text = 'Tamamlandı';
                                        $status_color = 'bg-green-100 text-green-800';
                                        break;
                                    default:
                                        $status_text = 'Belirsiz';
                                        $status_color = 'bg-gray-100 text-gray-800';
                                }
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_color; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="view-project.php?id=<?php echo $project['id']; ?>" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">Görüntüle</a>
                                    <a href="edit-project.php?id=<?php echo $project['id']; ?>" class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors">Düzenle</a>
                                    <a href="?delete=<?php echo $project['id']; ?>" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors" onclick="return confirm('Bu projeyi silmek istediğinizden emin misiniz?')">Sil</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>