<?php
session_start();

// ÖNCE session_start() çağrılmalı
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: index.php');
    exit;
}

// admin_id session kontrolü
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['admin_id'] = 1; // Varsayılan admin ID
}

// Veritabanı bağlantısı
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Admin bilgilerini güvenli şekilde çek
$admin = ['full_name' => 'Admin', 'username' => 'admin'];
try {
    if (isset($_SESSION['admin_id'])) {
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $admin = $result;
        }
    }
} catch (PDOException $e) {
    // Hata durumunda varsayılan değerler kullanılır
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Veritabanı bağlantısını dahil et
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Oturumda admin_id yoksa giriş sayfasına yönlendir
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

// Admin bilgilerini çek
$admin = null;
try {
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $admin = [
        'full_name' => 'Admin',
        'username' => 'admin'
    ];
}

// Ziyaretçi sayacını dahil et
require_once '../includes/visitor_counter.php';
$visitor_counter = new VisitorCounter($db);

// İstatistikler için değişkenler
$total_visitors = $visitor_counter->getTotalVisitors();
$total_projects = 0;
$total_messages = 0;

// Blog sayısını otomatik olarak hesapla
$total_blogs = 0;
try {
    $blog_json_file = '../data/blog.json';
    if (file_exists($blog_json_file)) {
        $blog_data = json_decode(file_get_contents($blog_json_file), true);
        if (is_array($blog_data)) {
            $total_blogs = count($blog_data);
        }
    }
} catch (Exception $e) {
    $total_blogs = 0;
}

// Proje sayısını çek
try {
    // Önce projects tablosunun var olup olmadığını kontrol et
    $check_table = "SHOW TABLES LIKE 'projects'";
    $table_result = $db->query($check_table);

    if ($table_result->rowCount() > 0) {
        $query = "SELECT COUNT(*) as total FROM projects";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_projects = $result['total'];
    }
} catch (PDOException $e) {
    // Hata durumunda varsayılan değer kullan
    $total_projects = 0;
}

// Mesaj sayısını çek
try {
    // Önce contact_forms tablosunun var olup olmadığını kontrol et
    $check_table = "SHOW TABLES LIKE 'contact_forms'";
    $table_result = $db->query($check_table);

    if ($table_result->rowCount() > 0) {
        $query = "SELECT COUNT(*) as total FROM contact_forms";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_messages = $result['total'];
    }
} catch (PDOException $e) {
    // Hata durumunda varsayılan değer kullan
    $total_messages = 0;
}

// Aylık ziyaretçi istatistiklerini çek
$monthly_stats = $visitor_counter->getMonthlyStats(6);
$chart_labels = [];
$chart_data = [];

foreach ($monthly_stats as $stat) {
    // Ay-Yıl formatını Türkçe'ye çevir
    $date = new DateTime($stat['month'] . '-01');
    $month_name = $date->format('F Y');
    $chart_labels[] = $month_name;

    // 'visitors' anahtarı yoksa 0 kullan
    $chart_data[] = isset($stat['visitors']) ? $stat['visitors'] : 0;
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WebDev Pro Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <a href="../index.php" class="text-gray-600 hover:text-gray-900" target="_blank">
                            Siteyi Görüntüle
                        </a>
                        <a href="?logout=1" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                            Çıkış Yap 
                        </a>

                        <a class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors"><?php echo htmlspecialchars($admin['username'] ?? ''); ?></a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-gray-600">WebDev Pro yönetim paneline hoş geldiniz <?php echo htmlspecialchars($admin['full_name'] ?? 'Admin'); ?></p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Toplam Ziyaretçi</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($total_visitors); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 01-3.138-3.138z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Tamamlanan Proje</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($total_projects); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="bg-yellow-100 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Yeni Mesaj</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($total_messages); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Blog Yazısı</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($total_blogs); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visitor Chart -->
            <div class="bg-white p-6 rounded-lg shadow mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Aylık Ziyaretçi İstatistikleri</h3>
                <div class="h-64">
                    <canvas id="visitorChart"></canvas>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Hızlı İşlemler</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <a href="add-project.php" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center space-x-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span>Yeni Proje Ekle</span>
                            </a>
                            <a href="view-projects.php" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center space-x-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                                <span>Projeleri Görüntüle</span>
                            </a>
                            <a href="edit-content.php" class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center space-x-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                                <span>İçerik Düzenle</span>
                            </a>
                            <a href="view-reports.php" class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg hover:bg-purple-700 transition-colors flex items-center justify-center space-x-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span>Raporları Görüntüle</span>
                            </a>
                            <a href="view-messages.php" class="w-full bg-yellow-600 text-white py-3 px-4 rounded-lg hover:bg-yellow-700 transition-colors flex items-center justify-center space-x-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span>Mesajları Görüntüle</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Son Mesajlar</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php
                            // Son mesajları veritabanından çek
                            try {
                                $check_table = "SHOW TABLES LIKE 'contact_forms'";
                                $table_result = $db->query($check_table);

                                if ($table_result->rowCount() > 0) {
                                    $query = "SELECT * FROM contact_forms ORDER BY created_at DESC LIMIT 3";
                                    $stmt = $db->prepare($query);
                                    $stmt->execute();

                                    if ($stmt->rowCount() > 0) {
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            // Rastgele renk seç
                                            $colors = ['blue', 'green', 'yellow', 'purple', 'red'];
                                            $color = $colors[array_rand($colors)];

                                            // Mesaj zamanını formatla
                                            $created_at = new DateTime($row['created_at']);
                                            $now = new DateTime();
                                            $interval = $created_at->diff($now);

                                            if ($interval->d > 0) {
                                                $time_ago = $interval->d . ' gün önce';
                                            } elseif ($interval->h > 0) {
                                                $time_ago = $interval->h . ' saat önce';
                                            } else {
                                                $time_ago = $interval->i . ' dakika önce';
                                            }

                                            // Mesaj içeriğini kısalt
                                            $short_message = strlen($row['message']) > 50 ? substr($row['message'], 0, 50) . '...' : $row['message'];

                                            echo '<div class="flex items-start space-x-3">';
                                            echo '<div class="bg-' . $color . '-100 p-2 rounded-full">';
                                            echo '<svg class="h-4 w-4 text-' . $color . '-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                                            echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>';
                                            echo '</svg>';
                                            echo '</div>';
                                            echo '<div class="flex-1">';
                                            echo '<p class="text-sm font-medium text-gray-900">' . htmlspecialchars($row['name']) . '</p>';
                                            echo '<p class="text-sm text-gray-600">' . htmlspecialchars($short_message) . '</p>';
                                            echo '<p class="text-xs text-gray-400">' . $time_ago . '</p>';
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                    } else {
                                        echo '<p class="text-gray-500 text-center">Henüz mesaj bulunmuyor.</p>';
                                    }
                                } else {
                                    echo '<p class="text-gray-500 text-center">Mesaj tablosu bulunamadı.</p>';
                                }
                            } catch (PDOException $e) {
                                echo '<p class="text-gray-500 text-center">Mesajlar yüklenirken bir hata oluştu.</p>';
                            }
                            ?>

                            <div class="mt-4 text-center">
                                <a href="view-messages.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Tüm mesajları görüntüle</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <br><br>
            <!-- Admin Actions -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Yönetim İşlemleri</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="view-visitors.php" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-base font-medium text-gray-900">Ziyaretçi Kayıtları</h4>
                            <p class="text-sm text-gray-600">Tüm ziyaretçi kayıtlarını görüntüle</p>
                        </div>
                    </a>

                    <a href="account/user.php" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-base font-medium text-gray-900">Kullanıcı Yönetimi</h4>
                            <p class="text-sm text-gray-600">Kullanıcıları yönet ve ekle</p>
                        </div>
                    </a>

                    <a href="account" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-base font-medium text-gray-900">Ayarlar</h4>
                            <p class="text-sm text-gray-600">Site ayarlarını yönet</p>
                        </div>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Ziyaretçi grafiği
        const ctx = document.getElementById('visitorChart').getContext('2d');
        const visitorChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Aylık Ziyaretçi Sayısı',
                    data: <?php echo json_encode($chart_data); ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    </script>
</body>

<?php include '../includes/footer.php'; ?>

</html>