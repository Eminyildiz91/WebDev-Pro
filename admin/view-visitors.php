<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: index.php');
    exit;
}

// Database connection
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Database bağlantısı kontrolü
if (!$db) {
    die('Veritabanı bağlantısı kurulamadı!');
}

// Include visitor counter
require_once '../includes/visitor_counter.php';
$visitor_counter = new VisitorCounter($db);

// Handle test visitor addition
if (isset($_GET['test_visitor'])) {
    $test_ip = '192.168.' . rand(1, 255) . '.' . rand(1, 255);
    $test_agent = 'Test Browser ' . date('Y-m-d H:i:s');
    
    $result = $visitor_counter->recordVisit($test_ip, $test_agent);
    
    if ($result) {
        $success_message = "Test ziyaretçi başarıyla eklendi! IP: $test_ip";
    } else {
        $error = "Test ziyaretçi eklenemedi. Lütfen tekrar deneyin.";
    }
}

// Get visitor data
$visitors = [];
$total_visitors = 0;
$unique_visitors = 0;

try {
    $visitors = $visitor_counter->getVisitorLogs(100);
    $total_visitors = $visitor_counter->getTotalVisitors();
    $unique_visitors = $visitor_counter->getUniqueVisitors();
} catch (Exception $e) {
    $error = 'Ziyaretçi verileri alınırken hata oluştu: ' . $e->getMessage();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ziyaretçi Kayıtları - WebDev Pro Admin</title>
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
                <h1 class="text-3xl font-bold text-gray-900">Ziyaretçi Kayıtları</h1>
                <p class="text-gray-600">Son 100 ziyaretçinin IP adresleri ve ziyaret tarihleri</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Toplam Ziyaret</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($total_visitors); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Benzersiz Ziyaretçi</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($unique_visitors); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Gösterilen Kayıt</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo count($visitors); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="mb-4 flex space-x-2">
                <a href="?test_visitor=1" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Test Ziyaretçi Ekle
                </a>
                <a href="view-visitors.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    Yenile
                </a>
            </div>
            
            <!-- Visitors Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                IP Adresi
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ziyaret Tarihi
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User Agent
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($visitors) && is_array($visitors) && count($visitors) > 0): ?>
                            <?php foreach ($visitors as $visitor): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($visitor['ip_address'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php 
                                        if (isset($visitor['created_at']) && !empty($visitor['created_at'])) {
                                            try {
                                                $date = new DateTime($visitor['created_at']);
                                                echo $date->format('d.m.Y H:i:s');
                                            } catch (Exception $e) {
                                                echo 'Geçersiz tarih';
                                            }
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?php echo htmlspecialchars($visitor['user_agent'] ?? 'N/A'); ?>">
                                        <?php echo htmlspecialchars(substr($visitor['user_agent'] ?? 'N/A', 0, 50)) . (strlen($visitor['user_agent'] ?? '') > 50 ? '...' : ''); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                    <?php echo isset($error) ? $error : 'Henüz ziyaretçi kaydı bulunmuyor.'; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Debug Info -->
            <?php if (isset($_GET['debug'])): ?>
                <div class="mt-8 bg-gray-100 p-4 rounded-lg">
                    <h3 class="font-bold mb-2">Debug Bilgileri:</h3>
                    <pre><?php 
                    echo "Visitors array: ";
                    var_dump($visitors);
                    echo "\nDatabase connection: ";
                    var_dump($db);
                    ?></pre>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>