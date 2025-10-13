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
    <title><?php echo htmlspecialchars($project['name']); ?> - Proje Detayı - WebDev Pro Admin</title>
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
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($project['name']); ?></h1>
                    <p class="text-gray-600">Proje detayları</p>
                </div>
                <div class="flex space-x-4">
                    <a href="edit-project.php?id=<?php echo $project['id']; ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center space-x-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                        <span>Düzenle</span>
                    </a>
                    <a href="view-projects.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors flex items-center space-x-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>Geri Dön</span>
                    </a>
                </div>
            </div>
            
            <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Proje Bilgileri</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Proje Adı</p>
                                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($project['name']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Kategori</p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php if (!empty($project['category'])): ?>
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <?php echo htmlspecialchars($project['category']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-500">Kategori belirtilmemiş</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Müşteri</p>
                                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($project['client']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Proje Tarihi</p>
                                    <p class="mt-1 text-sm text-gray-900"><?php echo date('d.m.Y', strtotime($project['date'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Görünürlük Durumu</p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php if (isset($project['is_visible']) && $project['is_visible']): ?>
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Görünür</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Gizli</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Proje Durumu</p>
                                    <p class="mt-1 text-sm text-gray-900">
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
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_color; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">İletişim Bilgileri</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Proje URL</p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php if (!empty($project['url'])): ?>
                                            <a href="<?php echo htmlspecialchars($project['url']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                <?php echo htmlspecialchars($project['url']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-500">URL belirtilmemiş</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Telefon Numarası</p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php if (!empty($project['phone'])): ?>
                                            <a href="tel:<?php echo htmlspecialchars($project['phone']); ?>" class="text-blue-600 hover:text-blue-800">
                                                <?php echo htmlspecialchars($project['phone']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-500">Telefon numarası belirtilmemiş</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">WhatsApp Numarası</p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php if (!empty($project['whatsapp'])): ?>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $project['whatsapp']); ?>" target="_blank" class="text-green-600 hover:text-green-800">
                                                <?php echo htmlspecialchars($project['whatsapp']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-500">WhatsApp numarası belirtilmemiş</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Proje Açıklaması</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-900 whitespace-pre-line"><?php echo htmlspecialchars($project['description']); ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-500">
                                <span>Oluşturulma Tarihi: <?php echo date('d.m.Y H:i:s', strtotime($project['created_at'])); ?></span>
                            </div>
                            <div class="flex space-x-4">
                                <a href="edit-project.php?id=<?php echo $project['id']; ?>" class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors">Düzenle</a>
                                <a href="view-projects.php?delete=<?php echo $project['id']; ?>" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors" onclick="return confirm('Bu projeyi silmek istediğinizden emin misiniz?')">Sil</a>
                                <a href="view-projects.php" class="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">Geri Dön</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>