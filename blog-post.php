<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();

// Blog yazısı ID'sini al
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Blog yazısını çek
$query = "SELECT * FROM blog_posts WHERE id = :id AND status = 'published'";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    // Yazı bulunamazsa ana sayfaya yönlendir
    header('Location: blog.php');
    exit;
}

// Sayfa başlığını ayarla
$pageTitle = htmlspecialchars($post['title']) . ' - WebDev Pro Blog';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Header -->
   

    <div class="container mx-auto px-4 py-8">
        <article class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <?php if (!empty($post['featured_image'])): ?>
                <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-96 object-cover">
            <?php endif; ?>
            
            <div class="p-8">
                <h1 class="text-4xl font-bold mb-4"><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <div class="flex items-center text-gray-600 mb-6">
                    <span>
                        <?php 
                        $date = new DateTime($post['created_at']);
                        echo $date->format('d.m.Y H:i'); 
                        ?>
                    </span>
                </div>
                
                <div class="prose max-w-none">
                    <?php echo $post['content']; ?>
                </div>
            </div>
        </article>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t py-8 mt-8">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-600">&copy; <?php echo date('Y'); ?> WebDev Pro. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</body>
</html>