<?php
// Blog yazılarını ve projeleri birlikte gösteren component

// Blog verilerini yükle
$blogs = [];
$blog_file = __DIR__ . '/../data/blog.json';
if (file_exists($blog_file)) {
    $blog_data = json_decode(file_get_contents($blog_file), true);
    if (is_array($blog_data)) {
        // Sadece yayınlanmış blogları al
        $blogs = array_filter($blog_data, function($blog) {
            return isset($blog['status']) && $blog['status'] === 'published';
        });
        // Tarihe göre sırala (en yeni önce)
        usort($blogs, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        // İlk 3 blogu al
        $blogs = array_slice($blogs, 0, 3);
    }
}

// Projeleri veritabanından çek
$projects = [];
try {
    require_once __DIR__ . '/../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    // Önce projects tablosunun var olup olmadığını kontrol et
    $check_table = "SHOW TABLES LIKE 'projects'";
    $table_result = $db->query($check_table);
    
    if ($table_result->rowCount() > 0) {
        $query = "SELECT * FROM projects WHERE is_visible = 1 ORDER BY date DESC LIMIT 3";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $db_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Veritabanından gelen projeleri formatla
        foreach ($db_projects as $project) {
            $projects[] = [
                'id' => $project['id'],
                'title' => $project['name'],
                'description' => substr($project['description'], 0, 100) . (strlen($project['description']) > 100 ? '...' : ''),
                'image' => !empty($project['image']) ? $project['image'] : 'https://images.pexels.com/photos/4050290/pexels-photo-4050290.jpeg?auto=compress&cs=tinysrgb&w=600',
                'category' => ucfirst($project['category']),
                'url' => $project['url'],
                'date' => $project['date']
            ];
        }
    }
} catch(PDOException $e) {
    // Hata durumunda varsayılan projeler
    $projects = [
        [
            'id' => 1,
            'title' => 'Modern Web Sitesi',
            'description' => 'Responsive tasarım ve modern teknolojilerle geliştirilmiş web sitesi',
            'image' => 'https://images.pexels.com/photos/196644/pexels-photo-196644.jpeg?auto=compress&cs=tinysrgb&w=600',
            'category' => 'Web Geliştirme',
            'url' => '#',
            'date' => date('Y-m-d')
        ]
    ];
}
?>

<section id="blog-portfolio" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Blog Portföyümüz</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                En son teknoloji yazılarımız ve başarılı projelerimizle dijital dünyada fark yaratıyoruz.
            </p>
        </div>

        <!-- Blog Yazıları -->
        <div class="mb-16">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-bold text-gray-900">Son Blog Yazıları</h3>
                <a href="blog.php" class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
                    Tüm Yazılar
                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (empty($blogs)): ?>
                    <div class="col-span-3 text-center py-12">
                        <p class="text-gray-600 text-lg">Henüz blog yazısı eklenmemiş.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($blogs as $blog): ?>
                        <article class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover">
                            <?php if (isset($blog['image']) && !empty($blog['image'])): ?>
                                <img src="<?php echo htmlspecialchars($blog['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                     class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                    <svg class="h-16 w-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-6">
                                <div class="flex items-center text-sm text-gray-500 mb-3">
                                    <span><?php echo htmlspecialchars($blog['author'] ?? 'Admin'); ?></span>
                                    <span class="mx-2">•</span>
                                    <span><?php echo date('d M Y', strtotime($blog['date'])); ?></span>
                                </div>
                                
                                <h3 class="text-xl font-bold text-gray-900 mb-3 hover:text-blue-600 transition-colors">
                                    <a href="blog-detail.php?id=<?php echo $blog['id']; ?>">
                                        <?php echo htmlspecialchars($blog['title']); ?>
                                    </a>
                                </h3>
                                
                                <p class="text-gray-600 mb-4 line-clamp-3">
                                    <?php echo htmlspecialchars($blog['excerpt'] ?? substr($blog['content'] ?? '', 0, 150) . '...'); ?>
                                </p>
                                
                                <a href="blog-detail.php?id=<?php echo $blog['id']; ?>" 
                                   class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                                    Devamını Oku
                                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Projeler -->
        <div>
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-bold text-gray-900">Son Projeler</h3>
                <a href="#portfolio" class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
                    Tüm Projeler
                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (empty($projects)): ?>
                    <div class="col-span-3 text-center py-12">
                        <p class="text-gray-600 text-lg">Henüz proje eklenmemiş.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($projects as $project): ?>
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover group">
                            <div class="relative overflow-hidden">
                                <img src="<?php echo $project['image']; ?>" 
                                     alt="<?php echo $project['title']; ?>"
                                     class="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-300">
                                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                    <a href="<?php echo $project['url']; ?>" 
                                       target="_blank" 
                                       class="bg-white text-gray-900 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                                        Projeyi Gör
                                    </a>
                                </div>
                                <div class="absolute top-4 left-4">
                                    <span class="bg-green-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                                        <?php echo $project['category']; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                <h4 class="text-xl font-bold text-gray-900 mb-2">
                                    <?php echo $project['title']; ?>
                                </h4>
                                <p class="text-gray-600 text-sm mb-3">
                                    <?php echo $project['description']; ?>
                                </p>
                                <div class="text-xs text-gray-500">
                                    <?php echo date('d M Y', strtotime($project['date'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>