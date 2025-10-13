<?php
// Veritabanı bağlantısını dahil et
require_once __DIR__ . '/../config/database.php';

// Projeleri veritabanından çek
$projects = [];
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Önce projects tablosunun var olup olmadığını kontrol et
    $check_table = "SHOW TABLES LIKE 'projects'";
    $table_result = $db->query($check_table);
    
    if ($table_result->rowCount() > 0) {
        $query = "SELECT * FROM projects WHERE is_visible = 1 ORDER BY date DESC LIMIT 6";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $db_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Veritabanından gelen projeleri formatlayarak diziye ekle
        foreach ($db_projects as $project) {
            // Kategori adını belirle
            $category_name = '';
            switch ($project['category']) {
                case 'web':
                    $category_name = 'Web Geliştirme';
                    break;
                case 'mobile':
                    $category_name = 'Mobil Uygulama';
                    break;
                case 'desktop':
                    $category_name = 'Masaüstü Uygulama';
                    break;
                case 'design':
                    $category_name = 'UI/UX Tasarım';
                    break;
                default:
                    $category_name = $project['category'];
            }
            
            // Rastgele etiketler (gerçek projede bu veriler de veritabanında saklanabilir)
            $tags = ['PHP', 'MySQL', 'JavaScript', 'HTML/CSS', 'React', 'Vue.js', 'Laravel', 'Bootstrap', 'Tailwind'];
            shuffle($tags);
            $project_tags = array_slice($tags, 0, rand(2, 4));
            
            // Rastgele resim (gerçek projede bu da veritabanında saklanabilir)
            $images = [
                'https://images.pexels.com/photos/4050290/pexels-photo-4050290.jpeg?auto=compress&cs=tinysrgb&w=600',
                'https://images.pexels.com/photos/196644/pexels-photo-196644.jpeg?auto=compress&cs=tinysrgb&w=600',
                'https://images.pexels.com/photos/607812/pexels-photo-607812.jpeg?auto=compress&cs=tinysrgb&w=600',
                'https://images.pexels.com/photos/265087/pexels-photo-265087.jpeg?auto=compress&cs=tinysrgb&w=600',
                'https://images.pexels.com/photos/261662/pexels-photo-261662.jpeg?auto=compress&cs=tinysrgb&w=600',
                'https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg?auto=compress&cs=tinysrgb&w=600'
            ];
            
            $projects[] = [
                'id' => $project['id'],
                'title' => $project['name'],
                'description' => substr($project['description'], 0, 100) . (strlen($project['description']) > 100 ? '...' : ''),
                'image' => $images[array_rand($images)],
                'tags' => $project_tags,
                'category' => $category_name,
                'url' => $project['url'],
                'phone' => isset($project['phone']) ? $project['phone'] : '',
                'whatsapp' => isset($project['whatsapp']) ? $project['whatsapp'] : ''
            ];
        }
    }
} catch(PDOException $e) {
    // Hata durumunda varsayılan projeler göster
    $projects = [
        [
            'id' => 1, // Varsayılan ID
            'title' => 'E-Ticaret Platformu',
            'description' => 'Modern ve kullanıcı dostu online mağaza çözümü',
            'image' => 'https://images.pexels.com/photos/4050290/pexels-photo-4050290.jpeg?auto=compress&cs=tinysrgb&w=600',
            'tags' => ['React', 'Node.js', 'MongoDB', 'Stripe'],
            'category' => 'E-Ticaret',
            'url' => '#',
            'phone' => '',
            'whatsapp' => ''
        ],
        [
            'id' => 2, // Varsayılan ID
            'title' => 'Kurumsal Web Sitesi',
            'description' => 'Profesyonel kurumsal kimlik ve modern tasarım',
            'image' => 'https://images.pexels.com/photos/196644/pexels-photo-196644.jpeg?auto=compress&cs=tinysrgb&w=600',
            'tags' => ['PHP', 'MySQL', 'Bootstrap', 'jQuery'],
            'category' => 'Kurumsal',
            'url' => '#',
            'phone' => '',
            'whatsapp' => ''
        ],
        [
            'id' => 3, // Varsayılan ID
            'title' => 'Mobil Uygulama',
            'description' => 'iOS ve Android için cross-platform çözüm',
            'image' => 'https://images.pexels.com/photos/607812/pexels-photo-607812.jpeg?auto=compress&cs=tinysrgb&w=600',
            'tags' => ['React Native', 'Firebase', 'Redux'],
            'category' => 'Mobil',
            'url' => '#',
            'phone' => '',
            'whatsapp' => ''
        ]
    ];
}
?>

<section id="portfolio" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Portföyümüz</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Farklı sektörlerden müşterilerimiz için geliştirdiğimiz başarılı projelerimizi keşfedin.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (empty($projects)): ?>
                <div class="col-span-3 text-center py-12">
                    <p class="text-gray-600 text-lg">Henüz proje eklenmemiş.</p>
                </div>
            <?php else: ?>
                <?php foreach($projects as $project): ?>
                    <div class="bg-white rounded-2xl overflow-hidden shadow-lg card-hover group">
                        <div class="relative overflow-hidden">
                            <img 
                                src="<?php echo $project['image']; ?>" 
                                alt="<?php echo $project['title']; ?>"
                                class="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-300"
                            />
                            <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center space-x-4">
                                <?php if (!empty($project['url'])): ?>
                                <a href="<?php echo $project['url']; ?>" target="_blank" class="bg-white text-gray-900 p-3 rounded-full hover:bg-gray-100 transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                </a>
                                <?php endif; ?>
                                <a href="view-project.php?id=<?php echo $project['id']; ?>" class="bg-white text-gray-900 p-3 rounded-full hover:bg-gray-100 transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </div>
                            <div class="absolute top-4 left-4">
                                <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                                    <?php echo $project['category']; ?>
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">
                                <a href="view-project.php?id=<?php echo $project['id']; ?>" class="hover:text-blue-600 transition-colors">
                                    <?php echo $project['title']; ?>
                                </a>
                            </h3>
                            <p class="text-gray-600 mb-4 leading-relaxed"><?php echo $project['description']; ?></p>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <?php foreach($project['tags'] as $tag): ?>
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-md text-sm">
                                        <?php echo $tag; ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Telefon ve WhatsApp numaraları görüntülenmiyor -->
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>