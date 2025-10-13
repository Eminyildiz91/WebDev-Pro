<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Veritabanı bağlantısını dahil et
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Ziyaretçi verilerini al (son 6 ay)
$monthly_visits = [];
$current_month = date('n');
$current_year = date('Y');

try {
    for ($i = 5; $i >= 0; $i--) {
        $month = $current_month - $i;
        $year = $current_year;
        
        if ($month <= 0) {
            $month += 12;
            $year--;
        }
        
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date = date('Y-m-t', strtotime($start_date));
        
        $query = "SELECT COUNT(*) as visit_count FROM visitor_logs WHERE visit_date BETWEEN :start_date AND :end_date";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $month_name = date('F', strtotime($start_date));
         $tr_months = [
             'January' => 'Ocak',
             'February' => 'Şubat',
             'March' => 'Mart',
             'April' => 'Nisan',
             'May' => 'Mayıs',
             'June' => 'Haziran',
             'July' => 'Temmuz',
             'August' => 'Ağustos',
             'September' => 'Eylül',
             'October' => 'Ekim',
             'November' => 'Kasım',
             'December' => 'Aralık'
         ];
         $tr_month_name = $tr_months[$month_name];
         $monthly_visits[$tr_month_name] = (int)$result['visit_count'];
    }
} catch(PDOException $e) {
    // Hata durumunda varsayılan değerler
    $tr_months = [
         'January' => 'Ocak',
         'February' => 'Şubat',
         'March' => 'Mart',
         'April' => 'Nisan',
         'May' => 'Mayıs',
         'June' => 'Haziran',
         'July' => 'Temmuz',
         'August' => 'Ağustos',
         'September' => 'Eylül',
         'October' => 'Ekim',
         'November' => 'Kasım',
         'December' => 'Aralık'
     ];
     $monthly_visits = array_combine(
         array_map(function($m) use ($tr_months) { 
             $month_name = date('F', mktime(0, 0, 0, date('n') - $m, 1));
             return $tr_months[$month_name];
         }, range(5, 0)),
         [0, 0, 0, 0, 0, 0]
     );
}

// Son iletişim formlarını al
try {
    $query = "SELECT * FROM contact_forms ORDER BY created_at DESC LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $contact_forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $contact_forms = [];
}

// Projeleri al
try {
    $query = "SELECT name, client, status, revenue FROM projects ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Proje durumlarını Türkçeleştir
    foreach ($projects as &$project) {
        switch($project['status']) {
            case 'completed':
                $project['status'] = 'Tamamlandı';
                break;
            case 'in_progress':
                $project['status'] = 'Devam Ediyor';
                break;
            case 'started':
                $project['status'] = 'Başladı';
                break;
            case 'not_started':
                $project['status'] = 'Başlamadı';
                break;
            default:
                $project['status'] = 'Belirsiz';
        }
        
        // Gelir formatını düzenle
        if (!empty($project['revenue'])) {
            $project['revenue'] = '₺' . number_format((float)$project['revenue'], 0, ',', '.');
        } else {
            $project['revenue'] = '₺0';
        }
    }
} catch(PDOException $e) {
    $projects = [];
}

// Toplam gelir hesaplama
$total_revenue = 0;
foreach ($projects as $project) {
    $revenue = str_replace(['₺', ','], '', $project['revenue']);
    $total_revenue += (float)$revenue;
}
$total_revenue = '₺' . number_format($total_revenue, 0, ',', '.');
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raporlar - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">Raporlar</h1>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-gray-600 hover:text-gray-900">Dashboard'a Dön</a>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-red-600 hover:text-red-900">Çıkış Yap</a>
                    <form id="logout-form" action="index.php" method="POST" class="hidden">
                        <input type="hidden" name="logout" value="1">
                    </form>
                </div>
            </div>
        </header>

        <!-- Ana İçerik -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Özet Kartları -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Toplam Ziyaret</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo array_sum($monthly_visits); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">İletişim Formları</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo count($contact_forms); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Toplam Gelir</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $total_revenue; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Grafikler ve Tablolar -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Ziyaretçi Grafiği -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Aylık Ziyaretçi İstatistikleri</h3>
                    </div>
                    <div class="p-6">
                        <canvas id="visitsChart" height="300"></canvas>
                    </div>
                </div>
                
                <!-- Proje Durumları -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Proje Durumları</h3>
                    </div>
                    <div class="p-6">
                        <canvas id="projectsChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Tablolar -->
            <div class="grid grid-cols-1 gap-8">
                <!-- İletişim Formları Tablosu -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Son İletişim Formları</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İsim</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-posta</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Konu</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($contact_forms as $form): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($form['name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($form['email']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($form['subject']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($form['date']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Projeler Tablosu -->
                <div class="bg-white rounded-lg shadow mt-8">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Projeler</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proje Adı</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gelir</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($project['name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($project['client']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $project['status'] === 'Tamamlandı' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo htmlspecialchars($project['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($project['revenue']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    // Ziyaretçi Grafiği
    const visitsCtx = document.getElementById('visitsChart').getContext('2d');
    const visitsChart = new Chart(visitsCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_keys($monthly_visits)); ?>,
            datasets: [{
                label: 'Aylık Ziyaretçi Sayısı',
                data: <?php echo json_encode(array_values($monthly_visits)); ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Proje Durumları Grafiği
    const projectsCtx = document.getElementById('projectsChart').getContext('2d');
    
    // Proje durumlarını say
    const projectStatus = {
        'Tamamlandı': 0,
        'Devam Ediyor': 0,
        'Başladı': 0,
        'Başlamadı': 0,
        'Belirsiz': 0
    };
    <?php foreach ($projects as $project): ?>
        projectStatus['<?php echo $project['status']; ?>'] = (projectStatus['<?php echo $project['status']; ?>'] || 0) + 1;
    <?php endforeach; ?>
    
    const projectsChart = new Chart(projectsCtx, {
        type: 'doughnut',
        data: {
            labels: ['Tamamlandı', 'Devam Ediyor', 'Başladı', 'Başlamadı', 'Belirsiz'],
            datasets: [{
                data: [
                    projectStatus['Tamamlandı'],
                    projectStatus['Devam Ediyor'],
                    projectStatus['Başladı'],
                    projectStatus['Başlamadı'],
                    projectStatus['Belirsiz']
                ],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.7)',   // Yeşil (Tamamlandı)
                    'rgba(245, 158, 11, 0.7)',   // Turuncu (Devam Ediyor)
                    'rgba(59, 130, 246, 0.7)',   // Mavi (Başladı)
                    'rgba(107, 114, 128, 0.7)',  // Gri (Başlamadı)
                    'rgba(239, 68, 68, 0.7)'     // Kırmızı (Belirsiz)
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    </script>
</body>
</html>