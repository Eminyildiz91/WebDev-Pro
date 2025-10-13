<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: index.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Blog yazılarını yönetmek için basit bir JSON dosya sistemi kullanacağız
$blog_file = '../data/blog.json';

// Blog verilerini yükle
function loadBlogData() {
    global $blog_file;
    if (file_exists($blog_file)) {
        $data = file_get_contents($blog_file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

// Blog verilerini kaydet
function saveBlogData($data) {
    global $blog_file;
    $dir = dirname($blog_file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($blog_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Fotoğraf yükleme fonksiyonu
function uploadImage($file) {
    $upload_dir = '../uploads/blog/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Sadece JPG, PNG ve GIF dosyaları yüklenebilir.'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'Dosya boyutu 5MB\'dan küçük olmalıdır.'];
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = time() . '_' . uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'filename' => 'uploads/blog/' . $new_filename];
    }
    
    return ['success' => false, 'message' => 'Dosya yüklenirken bir hata oluştu.'];
}

// Form işlemleri
$message = '';
$blogs = loadBlogData();

// Yeni blog ekleme
if (isset($_POST['add_blog'])) {
    $image_path = '';
    
    // Fotoğraf yükleme işlemi
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_result = uploadImage($_FILES['image']);
        if ($upload_result['success']) {
            $image_path = $upload_result['filename'];
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">' . $upload_result['message'] . '</div>';
        }
    }
    
    if (empty($message)) {
        $new_blog = [
            'id' => time(),
            'title' => $_POST['title'],
            'content' => $_POST['content'],
            'excerpt' => $_POST['excerpt'],
            'author' => $_POST['author'],
            'date' => date('Y-m-d H:i:s'),
            'status' => $_POST['status'],
            'image' => $image_path
        ];
        
        $blogs[] = $new_blog;
        saveBlogData($blogs);
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Blog yazısı başarıyla eklendi!</div>';
    }
}

// Blog silme
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $blogs = array_filter($blogs, function($blog) use ($delete_id) {
        return $blog['id'] != $delete_id;
    });
    saveBlogData($blogs);
    $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Blog yazısı silindi!</div>';
}

// Blog düzenleme
if (isset($_POST['edit_blog'])) {
    $edit_id = $_POST['blog_id'];
    $image_path = $_POST['existing_image'];
    
    // Yeni fotoğraf yükleme işlemi
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_result = uploadImage($_FILES['image']);
        if ($upload_result['success']) {
            $image_path = $upload_result['filename'];
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">' . $upload_result['message'] . '</div>';
        }
    }
    
    if (empty($message)) {
        foreach ($blogs as &$blog) {
            if ($blog['id'] == $edit_id) {
                $blog['title'] = $_POST['title'];
                $blog['content'] = $_POST['content'];
                $blog['excerpt'] = $_POST['excerpt'];
                $blog['author'] = $_POST['author'];
                $blog['status'] = $_POST['status'];
                $blog['image'] = $image_path;
                break;
            }
        }
        saveBlogData($blogs);
        $message = '<div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">Blog yazısı güncellendi!</div>';
    }
}

// Düzenlenecek blog
$edit_blog = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    foreach ($blogs as $blog) {
        if ($blog['id'] == $edit_id) {
            $edit_blog = $blog;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Yönetimi - WebDev Pro Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#content'), {
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'underline', 'strikethrough', '|',
                    'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', '|',
                    'alignment', '|',
                    'numberedList', 'bulletedList', '|',
                    'link', 'blockQuote', 'codeBlock', '|',
                    'undo', 'redo'
                ],
                fontFamily: {
                    options: [
                        'default',
                        'Arial, sans-serif',
                        'Verdana, sans-serif',
                        'Georgia, serif',
                        'Courier New, monospace',
                        'Comic Sans MS, cursive'
                    ]
                },
                fontSize: {
                    options: [10, 12, 14, 'default', 18, 20, 24, 28, 32]
                }
            })
            .catch(error => {
                console.error(error);
            });
    </script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center space-x-2">
                        <div class="bg-orange-600 p-2 rounded-lg">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-gray-900">Blog Yönetimi</span>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <a href="edit-content.php" class="text-gray-600 hover:text-gray-900">
                            İçerik Düzenle
                        </a>
                        <a href="dashboard.php" class="text-gray-600 hover:text-gray-900">
                            Dashboard
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
            <?php echo $message; ?>
            
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-3xl font-bold text-gray-900">
                        <?php echo $edit_blog ? 'Blog Yazısını Düzenle' : 'Blog Yönetimi'; ?>
                    </h1>
                    <a href="edit-content.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors flex items-center space-x-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                        </svg>
                        <span>Geri Dön</span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Blog Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">
                            <?php echo $edit_blog ? 'Blog Yazısını Düzenle' : 'Yeni Blog Yazısı Ekle'; ?>
                        </h2>
                        
                        <form method="POST" enctype="multipart/form-data" class="space-y-6">
                            <?php if ($edit_blog): ?>
                                <input type="hidden" name="blog_id" value="<?php echo $edit_blog['id']; ?>">
                                <input type="hidden" name="existing_image" value="<?php echo isset($edit_blog['image']) ? $edit_blog['image'] : ''; ?>">
                            <?php endif; ?>
                            
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Başlık</label>
                                <input type="text" id="title" name="title" required
                                       value="<?php echo $edit_blog ? htmlspecialchars($edit_blog['title']) : ''; ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">Özet</label>
                                <textarea id="excerpt" name="excerpt" rows="3" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                          placeholder="Blog yazısının kısa özeti..."><?php echo $edit_blog ? htmlspecialchars($edit_blog['excerpt']) : ''; ?></textarea>
                            </div>
                            
                            <!-- Fotoğraf Yükleme -->
                            <div>
                                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Kapak Fotoğrafı</label>
                                
                                <?php if ($edit_blog && isset($edit_blog['image']) && !empty($edit_blog['image'])): ?>
                                    <div class="mb-4">
                                        <img src="../<?php echo $edit_blog['image']; ?>" alt="Mevcut fotoğraf" class="w-32 h-32 object-cover rounded-lg border">
                                        <p class="text-sm text-gray-500 mt-1">Mevcut fotoğraf</p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="flex items-center space-x-4">
                                    <label for="image" class="cursor-pointer bg-orange-100 text-orange-700 px-4 py-2 rounded-lg hover:bg-orange-200 transition-colors flex items-center space-x-2">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>Fotoğraf Seç</span>
                                    </label>
                                    <input type="file" id="image" name="image" accept="image/*" class="hidden">
                                    <span id="file-name" class="text-sm text-gray-500">Dosya seçilmedi</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG veya GIF formatında, maksimum 5MB</p>
                            </div>
                            
                            <div>
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">İçerik</label>
                                <textarea id="content" name="content" rows="10" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"><?php echo $edit_blog ? htmlspecialchars($edit_blog['content']) : ''; ?></textarea>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="author" class="block text-sm font-medium text-gray-700 mb-2">Yazar</label>
                                    <input type="text" id="author" name="author" required
                                           value="<?php echo $edit_blog ? htmlspecialchars($edit_blog['author']) : ''; ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                                    <select id="status" name="status" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                        <option value="published" <?php echo ($edit_blog && $edit_blog['status'] == 'published') ? 'selected' : ''; ?>>Yayınlandı</option>
                                        <option value="draft" <?php echo ($edit_blog && $edit_blog['status'] == 'draft') ? 'selected' : ''; ?>>Taslak</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="flex space-x-4">
                                <button type="submit" name="<?php echo $edit_blog ? 'edit_blog' : 'add_blog'; ?>"
                                        class="bg-orange-600 text-white px-6 py-2 rounded-lg hover:bg-orange-700 transition-colors">
                                    <?php echo $edit_blog ? 'Güncelle' : 'Ekle'; ?>
                                </button>
                                
                                <?php if ($edit_blog): ?>
                                    <a href="edit-blog.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                                        İptal
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Blog Listesi -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Mevcut Blog Yazıları</h2>
                        
                        <?php if (empty($blogs)): ?>
                            <p class="text-gray-500 text-center py-8">Henüz blog yazısı bulunmuyor.</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($blogs as $blog): ?>
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-start justify-between mb-2">
                                            <h3 class="font-medium text-gray-900 text-sm"><?php echo htmlspecialchars($blog['title']); ?></h3>
                                            <span class="px-2 py-1 text-xs rounded-full <?php echo $blog['status'] == 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                <?php echo $blog['status'] == 'published' ? 'Yayında' : 'Taslak'; ?>
                                            </span>
                                        </div>
                                        
                                        <p class="text-gray-600 text-sm mb-2"><?php echo htmlspecialchars(substr($blog['excerpt'], 0, 100)) . '...'; ?></p>
                                        
                                        <div class="text-xs text-gray-500 mb-3">
                                            <span><?php echo htmlspecialchars($blog['author']); ?></span> • 
                                            <span><?php echo date('d.m.Y H:i', strtotime($blog['date'])); ?></span>
                                        </div>
                                        
                                        <div class="flex space-x-2">
                                            <a href="?edit=<?php echo $blog['id']; ?>" 
                                               class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600 transition-colors">
                                                Düzenle
                                            </a>
                                            <a href="?delete=<?php echo $blog['id']; ?>" 
                                               onclick="return confirm('Bu blog yazısını silmek istediğinizden emin misiniz?')"
                                               class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600 transition-colors">
                                                Sil
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>