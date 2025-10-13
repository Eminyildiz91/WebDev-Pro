<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Sayfa numarasını al
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);

// Blog yazılarını getir
$blogs = getPublishedBlogs();
$blogs = array_reverse($blogs); // En yeni yazılar önce

// Sayfalama
$pagination = paginate($blogs, $page, 6);
$paginatedBlogs = $pagination['items'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Blog - WebDev Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Header -->


    <main class="flex-grow">
        <!-- Hero Section -->
        <section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">Blog</h1>
                <p class="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto">
                    Teknoloji, web geliştirme ve dijital dünya hakkında yazılarımız
                </p>
            </div>
        </section>

        <!-- Blog Posts -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <?php if (empty($paginatedBlogs)): ?>
                    <div class="text-center py-12">
                        <div class="bg-white rounded-lg shadow-lg p-8 max-w-md mx-auto">
                            <svg class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Henüz blog yazısı yok</h3>
                            <p class="text-gray-600">Yakında ilginç içeriklerle burada olacağız!</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <?php foreach ($paginatedBlogs as $blog): ?>
                            <article class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                                <?php if (isset($blog['image']) && !empty($blog['image'])): ?>
                                    <div class="aspect-w-16 aspect-h-9">
                                        <img src="<?php echo htmlspecialchars($blog['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                             class="w-full h-48 object-cover">
                                    </div>
                                <?php else: ?>
                                    <div class="w-full h-48 bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                        <svg class="h-16 w-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="p-6">
                                    <div class="flex items-center text-sm text-gray-500 mb-3">
                                        <span><?php echo htmlspecialchars($blog['author']); ?></span>
                                        <span class="mx-2">•</span>
                                        <span><?php echo formatDate($blog['date']); ?></span>
                                    </div>
                                    
                                    <h2 class="text-xl font-bold text-gray-900 mb-3 hover:text-blue-600 transition-colors">
                                        <a href="blog-detail.php?id=<?php echo $blog['id']; ?>">
                                            <?php echo htmlspecialchars($blog['title']); ?>
                                        </a>
                                    </h2>
                                    
                                    <p class="text-gray-600 mb-4 line-clamp-3">
                                        <?php echo htmlspecialchars($blog['excerpt']); ?>
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
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <div class="mt-12 flex justify-center">
                            <nav class="flex items-center space-x-2">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <a href="?page=<?php echo $pagination['current_page'] - 1; ?>" 
                                       class="px-3 py-2 rounded-md bg-white text-gray-500 hover:bg-gray-50 border">
                                        Önceki
                                    </a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>" 
                                       class="px-3 py-2 rounded-md <?php echo $i == $pagination['current_page'] ? 'bg-blue-600 text-white' : 'bg-white text-gray-500 hover:bg-gray-50'; ?> border">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <a href="?page=<?php echo $pagination['current_page'] + 1; ?>" 
                                       class="px-3 py-2 rounded-md bg-white text-gray-500 hover:bg-gray-50 border">
                                        Sonraki
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <?php 
    if (file_exists('includes/footer.php')) {
        include 'includes/footer.php';
    } else {
        echo '<footer class="bg-white border-t py-8 mt-8">
            <div class="container mx-auto px-4 text-center">
                <p class="text-gray-600">&copy; ' . date('Y') . ' WebDev Pro. Tüm hakları saklıdır.</p>
            </div>
        </footer>';
    }
    ?>
</body>
</html>