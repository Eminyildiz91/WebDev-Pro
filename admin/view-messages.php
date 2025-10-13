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

// Mesaj silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $query = "DELETE FROM contact_forms WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $delete_success = true;
        } else {
            $delete_error = true;
        }
    } catch(PDOException $e) {
        $delete_error = true;
    }
}

// Mesajları çek
$messages = [];
try {
    // Önce contact_forms tablosunun var olup olmadığını kontrol et
    $check_table = "SHOW TABLES LIKE 'contact_forms'";
    $table_result = $db->query($check_table);
    
    if ($table_result->rowCount() > 0) {
        $query = "SELECT * FROM contact_forms ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesajlar - WebDev Pro Admin</title>
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
                            Dashboard
                        </a>
                        <a href="../index.php" class="text-gray-600 hover:text-gray-900" target="_blank">
                            Siteyi Görüntüle
                        </a>
                        <a href="dashboard.php?logout=1" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                            Çıkış Yap
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Mesajlar</h1>
                <a href="dashboard.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    Geri Dön
                </a>
            </div>
            
            <?php if (isset($delete_success)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>Mesaj başarıyla silindi.</p>
            </div>
            <?php endif; ?>
            
            <?php if (isset($delete_error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p>Mesaj silinirken bir hata oluştu.</p>
            </div>
            <?php endif; ?>
            
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <?php if (empty($messages)): ?>
                <div class="p-6 text-center text-gray-500">
                    <p>Henüz mesaj bulunmuyor.</p>
                </div>
                <?php else: ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İsim</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-posta</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefon</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Konu</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mesaj</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($messages as $message): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($message['name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($message['email']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($message['phone']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($message['subject']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-500 max-w-xs truncate"><?php echo htmlspecialchars($message['message']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-900 mr-3" onclick="viewMessage(<?php echo $message['id']; ?>)">Görüntüle</button>
                                <a href="?delete=<?php echo $message['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Bu mesajı silmek istediğinize emin misiniz?')">Sil</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Modal -->
    <div id="messageModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl max-w-lg w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Mesaj Detayı</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal()">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="px-6 py-4" id="modalContent">
                <!-- Modal içeriği JavaScript ile doldurulacak -->
            </div>
            <div class="px-6 py-4 border-t border-gray-200 text-right">
                <button type="button" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors" onclick="closeModal()">Kapat</button>
            </div>
        </div>
    </div>
    
    <script>
        // Mesaj görüntüleme modalı
        function viewMessage(id) {
            // Mesaj verilerini bul
            <?php echo 'const messages = ' . json_encode($messages) . ';'; ?>
            const message = messages.find(m => m.id == id);
            
            if (message) {
                // Modal başlığını ayarla
                document.getElementById('modalTitle').textContent = message.subject;
                
                // Modal içeriğini oluştur
                let content = `
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Gönderen</p>
                            <p class="text-base">${message.name} (${message.email})</p>
                            <p class="text-base">${message.phone}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tarih</p>
                            <p class="text-base">${new Date(message.created_at).toLocaleString('tr-TR')}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Mesaj</p>
                            <p class="text-base whitespace-pre-line">${message.message}</p>
                        </div>
                    </div>
                `;
                
                document.getElementById('modalContent').innerHTML = content;
                
                // Modalı göster
                document.getElementById('messageModal').classList.remove('hidden');
            }
        }
        
        // Modalı kapat
        function closeModal() {
            document.getElementById('messageModal').classList.add('hidden');
        }
        
        // ESC tuşu ile modalı kapatma
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>