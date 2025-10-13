<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Blog ID'sini al
$blog_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$blog_id) {
    header('Location: blog.php');
    exit;
}

// Blog yazısını getir
$blog = getBlogById($blog_id);

if (!$blog || $blog['status'] !== 'published') {
    header('Location: blog.php');
    exit;
}
?>

<main class="flex-grow">
    <!-- Blog Detail -->
    <article class="py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Blog Header -->
            <header class="mb-8">
                <div class="flex items-center text-sm text-gray-500 mb-4">
                    <a href="blog.php" class="hover:text-blue-600">Blog</a>
                    <span class="mx-2">/</span>
                    <span><?php echo htmlspecialchars($blog['title']); ?></span>
                </div>
                
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                    <?php echo htmlspecialchars($blog['title']); ?>
                </h1>
                
                <div class="flex items-center text-gray-600 mb-6">
                    <span><?php echo htmlspecialchars($blog['author']); ?></span>
                    <span class="mx-3">•</span>
                    <span><?php echo formatDate($blog['date']); ?></span>
                </div>
                
                <?php if (isset($blog['image']) && !empty($blog['image'])): ?>
                    <div class="mb-8">
                        <img src="<?php echo htmlspecialchars($blog['image']); ?>" 
                             alt="<?php echo htmlspecialchars($blog['title']); ?>"
                             class="w-full h-64 md:h-96 object-cover rounded-lg shadow-lg">
                    </div>
                <?php endif; ?>
            </header>
            
            <!-- Blog Content -->
            <div class="prose prose-lg max-w-none">
                <?php echo nl2br(htmlspecialchars($blog['content'])); ?>
            </div>
            
            <!-- Back to Blog -->
            <div class="mt-12 pt-8 border-t">
                <a href="blog.php" 
                   class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Blog'a Geri Dön
                </a>
            </div>
        </div>
    </article>
</main>

<?php require_once 'includes/footer.php'; ?>