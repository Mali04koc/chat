<?php

require_once "vendor/autoload.php";
require_once "core/init.php";

use classes\{DB, Config, Validation, Common, Session, Token, Hash, Redirect, Cookie};
use models\{Post, User, Comment, Like};

// Check if user is logged in and is an admin
if(!$user->getPropertyValue("isLoggedIn")) {
    Redirect::to("login/login.php");
}

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchType = isset($_GET['search_type']) ? $_GET['search_type'] : 'title';

// Fetch all posts with user information
$db = DB::getInstance();
$query = "SELECT p.*, u.username 
          FROM post p 
          JOIN user_info u ON p.post_owner = u.id 
          WHERE 1=1";

if (!empty($search)) {
    if ($searchType === 'title') {
        $query .= " AND p.text_content LIKE ?";
        $searchParam = "%$search%";
    } else {
        $query .= " AND u.username LIKE ?";
        $searchParam = "%$search%";
    }
    $query .= " ORDER BY p.post_date DESC";
    $db->query($query, array($searchParam));
} else {
    $query .= " ORDER BY p.post_date DESC";
    $db->query($query);
}

$posts = $db->results();

// Handle post deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {
    $post_id = $_POST['post_id'];
    $post = new Post();
    $post->set_property('post_id', $post_id);
    
    if ($post->delete()) {
        Session::flash('success', 'Gönderi başarıyla silindi');
    } else {
        Session::flash('error', 'Gönderi silinirken bir hata oluştu');
    }
    Redirect::to('admin-posts.php');
}

// Handle comment deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    $comment_id = $_POST['comment_id'];
    $comment = new Comment();
    $comment->set_property('id', $comment_id);
    
    if ($comment->delete()) {
        Session::flash('success', 'Yorum başarıyla silindi');
    } else {
        Session::flash('error', 'Yorum silinirken bir hata oluştu');
    }
    Redirect::to('admin-posts.php');
}

// Function to check if content is an image
function isImage($content) {
    // Check if content is a URL
    if (filter_var($content, FILTER_VALIDATE_URL)) {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $url = parse_url($content, PHP_URL_PATH);
        $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        return in_array($extension, $imageExtensions);
    }
    return false;
}

// Function to check if content is a video
function isVideo($content) {
    if (filter_var($content, FILTER_VALIDATE_URL)) {
        $videoExtensions = ['mp4', 'webm', 'ogg'];
        $url = parse_url($content, PHP_URL_PATH);
        $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        return in_array($extension, $videoExtensions);
    }
    return false;
}

// Function to check if content is a YouTube video
function isYouTube($content) {
    return (strpos($content, 'youtube.com') !== false || strpos($content, 'youtu.be') !== false);
}

// Function to get YouTube embed URL
function getYouTubeEmbedUrl($url) {
    $videoId = '';
    if (strpos($url, 'youtube.com') !== false) {
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $videoId = isset($params['v']) ? $params['v'] : '';
    } elseif (strpos($url, 'youtu.be') !== false) {
        $videoId = basename(parse_url($url, PHP_URL_PATH));
    }
    return $videoId ? "https://www.youtube.com/embed/$videoId" : '';
}

// Function to get image URL
function getImageUrl($content) {
    // If content is a URL, return it directly
    if (filter_var($content, FILTER_VALIDATE_URL)) {
        return $content;
    }
    
    // If content is a relative path, prepend the base URL
    if (strpos($content, '/') === 0) {
        return 'http://' . $_SERVER['HTTP_HOST'] . $content;
    }
    
    // If content is just a filename, prepend the uploads directory
    return 'public/uploads/' . $content;
}

// Function to get media content
function getMediaContent($post) {
    $content = '';
    
    // Check for images
    if (!empty($post->picture_media)) {
        $imageDir = $post->picture_media;
        if (is_dir($imageDir)) {
            $files = glob($imageDir . '*.*');
            foreach ($files as $file) {
                if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $content .= '<div class="post-media">';
                    $content .= '<img src="' . htmlspecialchars($file) . '" alt="Post image" class="post-image">';
                    $content .= '</div>';
                }
            }
        }
    }
    
    // Check for videos
    if (!empty($post->video_media)) {
        $videoDir = $post->video_media;
        if (is_dir($videoDir)) {
            $files = glob($videoDir . '*.*');
            foreach ($files as $file) {
                if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['mp4', 'webm', 'ogg'])) {
                    $content .= '<div class="post-media">';
                    $content .= '<video controls class="post-video">';
                    $content .= '<source src="' . htmlspecialchars($file) . '" type="video/' . pathinfo($file, PATHINFO_EXTENSION) . '">';
                    $content .= 'Your browser does not support the video tag.';
                    $content .= '</video>';
                    $content .= '</div>';
                }
            }
        }
    }
    
    return $content;
}

// Function to get post comments
function getPostComments($post_id) {
    $db = DB::getInstance();
    $db->query("SELECT c.*, u.username 
                FROM comment c 
                JOIN user_info u ON c.comment_owner = u.id 
                WHERE c.post_id = ? 
                ORDER BY c.comment_date DESC", array($post_id));
    return $db->results();
}

// Function to get post likes
function getPostLikes($post_id) {
    $db = DB::getInstance();
    $db->query("SELECT l.*, u.username 
                FROM `like` l 
                JOIN user_info u ON l.user_id = u.id 
                WHERE l.post_id = ? 
                ORDER BY l.like_date DESC", array($post_id));
    return $db->results();
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEW WORLD - GÖNDERİ YÖNETİMİ</title>
    <link rel='shortcut icon' type='image/x-icon' href='public/assets/images/favicons/favicon.png' />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/global.css">
    <link rel="stylesheet" href="public/css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            background-color: #1a1a1a;
            color: #e0e0e0;
        }
        .container {
            max-width: 1400px;
            padding: 20px;
        }
        .search-container {
            margin: 20px 0;
            padding: 20px;
            background: #2d2d2d;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .table-container {
            margin-top: 20px;
            background: #2d2d2d;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            padding: 20px;
        }
        .flash-message {
            margin: 10px 0;
            padding: 15px;
            border-radius: 8px;
            font-size: 15px;
        }
        .flash-success {
            background-color: #1e4620;
            color: #a3d9a5;
        }
        .flash-error {
            background-color: #462020;
            color: #d9a3a3;
        }
        .table {
            margin-bottom: 0;
            color: #e0e0e0;
        }
        .table td {
            padding: 16px;
            vertical-align: top;
            font-size: 15px;
            border-bottom: 1px solid #404040;
        }
        .table th {
            padding: 16px;
            background-color: #333333;
            font-weight: 600;
            font-size: 15px;
            color: #ffffff;
            border-bottom: 2px solid #404040;
        }
        .post-content {
            max-width: 100%;
            line-height: 1.8;
            font-size: 15px;
            color: #e0e0e0;
            white-space: pre-wrap;
            word-wrap: break-word;
            padding: 15px;
            background: #333333;
            border-radius: 8px;
            border: 1px solid #404040;
            margin: 10px 0;
        }
        .post-media {
            margin: 10px 0;
            border-radius: 8px;
            overflow: hidden;
            background: #333333;
            padding: 10px;
        }
        .post-image {
            max-width: 100%;
            height: auto;
            display: block;
            border-radius: 8px;
            object-fit: contain;
        }
        .post-video {
            max-width: 100%;
            height: auto;
            display: block;
            border-radius: 8px;
        }
        .post-stats {
            margin-top: 15px;
            padding: 15px;
            background: #333333;
            border-radius: 8px;
            border: 1px solid #404040;
        }
        .post-stats-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #404040;
        }
        .post-stats-count {
            font-size: 14px;
            color: #b0b0b0;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .comments-section {
            margin-top: 15px;
        }
        .comment-item {
            padding: 15px;
            margin-bottom: 10px;
            background: #333333;
            border-radius: 8px;
            border: 1px solid #404040;
        }
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .comment-username {
            font-weight: 600;
            color: #ffffff;
        }
        .comment-date {
            color: #b0b0b0;
            font-size: 12px;
        }
        .comment-text {
            font-size: 14px;
            color: #e0e0e0;
            margin: 0;
            padding: 10px;
            background: #2d2d2d;
            border-radius: 4px;
        }
        .comment-actions {
            margin-top: 10px;
            text-align: right;
        }
        .delete-comment-btn {
            color: #ff6b6b;
            background: none;
            border: none;
            padding: 5px 10px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            border-radius: 4px;
        }
        .delete-comment-btn:hover {
            color: white;
            background-color: #ff6b6b;
        }
        .likes-section {
            margin-top: 15px;
        }
        .like-item {
            padding: 10px;
            margin-bottom: 5px;
            background: #333333;
            border-radius: 8px;
            border: 1px solid #404040;
            font-size: 14px;
            color: #e0e0e0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal-content {
            border-radius: 12px;
            border: none;
            background-color: #2d2d2d;
            color: #e0e0e0;
        }
        .modal-header {
            background-color: #333333;
            border-bottom: 1px solid #404040;
            border-radius: 12px 12px 0 0;
            padding: 20px;
        }
        .modal-body {
            padding: 20px;
            font-size: 16px;
            line-height: 1.8;
            background-color: #2d2d2d;
        }
        .modal-footer {
            background-color: #333333;
            border-top: 1px solid #404040;
            border-radius: 0 0 12px 12px;
            padding: 15px 20px;
        }
        .btn-danger {
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 6px;
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .btn-secondary {
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 6px;
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        .form-control {
            padding: 10px 15px;
            font-size: 15px;
            border-radius: 6px;
            background-color: #333333;
            border: 1px solid #404040;
            color: #e0e0e0;
        }
        .form-control:focus {
            background-color: #404040;
            border-color: #505050;
            color: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.1);
        }
        .form-select {
            padding: 10px 15px;
            font-size: 15px;
            border-radius: 6px;
            background-color: #333333;
            border: 1px solid #404040;
            color: #e0e0e0;
        }
        .form-select:focus {
            background-color: #404040;
            border-color: #505050;
            color: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.1);
        }
        .btn-primary {
            padding: 10px 20px;
            font-size: 15px;
            border-radius: 6px;
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        .content-type {
            font-size: 12px;
            color: #b0b0b0;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .post-meta {
            display: flex;
            gap: 20px;
            color: #b0b0b0;
            font-size: 14px;
            margin-bottom: 15px;
            padding: 15px;
            background: #333333;
            border-radius: 8px;
            border: 1px solid #404040;
        }
        .post-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .post-meta strong {
            color: #ffffff;
        }
        .post-row:hover {
            background-color: #333333 !important;
        }
        .modal-title {
            color: #ffffff;
        }
        .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        .post-content-full {
            background: #333333;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #404040;
            margin-top: 15px;
            font-size: 16px;
            line-height: 1.8;
            white-space: pre-wrap;
            word-wrap: break-word;
            color: #e0e0e0;
        }
        .page-title {
            margin-top: 40px;
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: #fff;
        }
    </style>
</head>
<body>
<?php include_once "page_parts/basic/admin-header.php"; ?>

<main class="container py-4">
    <h1 class="mb-4 page-title">Gönderi Yönetimi</h1>

    <?php if (Session::exists('success')): ?>
        <div class="flash-message flash-success">
            <?php echo Session::flash('success'); ?>
        </div>
    <?php endif; ?>

    <?php if (Session::exists('error')): ?>
        <div class="flash-message flash-error">
            <?php echo Session::flash('error'); ?>
        </div>
    <?php endif; ?>

    <div class="search-container">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <input type="text" class="form-control" name="search" placeholder="Gönderilerde ara..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-4">
                <select class="form-select" name="search_type">
                    <option value="title" <?php echo $searchType === 'title' ? 'selected' : ''; ?>>Başlığa Göre Ara</option>
                    <option value="username" <?php echo $searchType === 'username' ? 'selected' : ''; ?>>Kullanıcı Adına Göre Ara</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Ara</button>
            </div>
        </form>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th style="width: 150px;">Kullanıcı Adı</th>
                        <th style="width: 250px;">Başlık</th>
                        <th style="width: 500px;">İçerik</th>
                        <th style="width: 150px;">Oluşturulma Tarihi</th>
                        <th style="width: 100px;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr class="post-row" data-bs-toggle="modal" data-bs-target="#postModal<?php echo $post->id; ?>">
                            <td class="id-cell"><?php echo htmlspecialchars($post->id); ?></td>
                            <td class="username-cell"><?php echo htmlspecialchars($post->username); ?></td>
                            <td class="post-title"><?php echo htmlspecialchars($post->text_content); ?></td>
                            <td>
                                <div class="post-content">
                                    <?php 
                                    // Display text content
                                    if (!empty($post->text_content)) {
                                        echo '<div class="content-type">Metin İçeriği</div>';
                                        echo nl2br(htmlspecialchars($post->text_content));
                                    }
                                    
                                    // Display media content
                                    $mediaContent = getMediaContent($post);
                                    if (!empty($mediaContent)) {
                                        echo '<div class="content-type">Medya İçeriği</div>';
                                        echo $mediaContent;
                                    }
                                    
                                    // Display post stats
                                    $comments = getPostComments($post->id);
                                    $likes = getPostLikes($post->id);
                                    
                                    echo '<div class="post-stats">';
                                    echo '<div class="post-stats-header">';
                                    echo '<div class="post-stats-count"><i class="fas fa-comments"></i> ' . count($comments) . ' Yorum</div>';
                                    echo '<div class="post-stats-count"><i class="fas fa-heart"></i> ' . count($likes) . ' Beğeni</div>';
                                    echo '</div>';
                                    
                                    // Display comments
                                    if (!empty($comments)) {
                                        echo '<div class="comments-section">';
                                        foreach ($comments as $comment) {
                                            echo '<div class="comment-item">';
                                            echo '<div class="comment-header">';
                                            echo '<span class="comment-username"><i class="fas fa-user"></i> ' . htmlspecialchars($comment->username) . '</span>';
                                            echo '<span class="comment-date"><i class="fas fa-clock"></i> ' . date('d.m.Y H:i', strtotime($comment->comment_date)) . '</span>';
                                            echo '</div>';
                                            echo '<p class="comment-text">' . nl2br(htmlspecialchars($comment->comment_text)) . '</p>';
                                            echo '<div class="comment-actions">';
                                            echo '<form method="POST" style="display: inline;" onsubmit="event.stopPropagation(); return confirm(\'Bu yorumu silmek istediğinizden emin misiniz?\');">';
                                            echo '<input type="hidden" name="comment_id" value="' . $comment->id . '">';
                                            echo '<button type="submit" name="delete_comment" class="delete-comment-btn">';
                                            echo '<i class="fas fa-trash"></i> Yorumu Sil';
                                            echo '</button>';
                                            echo '</form>';
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                        echo '</div>';
                                    }
                                    
                                    // Display likes
                                    if (!empty($likes)) {
                                        echo '<div class="likes-section">';
                                        foreach ($likes as $like) {
                                            echo '<div class="like-item">';
                                            echo '<i class="fas fa-heart"></i> ' . htmlspecialchars($like->username);
                                            echo '</div>';
                                        }
                                        echo '</div>';
                                    }
                                    
                                    echo '</div>';
                                    ?>
                                </div>
                            </td>
                            <td class="date-cell"><?php echo date('d.m.Y H:i', strtotime($post->post_date)); ?></td>
                            <td>
                                <form method="POST" style="display: inline;" onsubmit="event.stopPropagation(); return confirm('Bu gönderiyi silmek istediğinizden emin misiniz?');">
                                    <input type="hidden" name="post_id" value="<?php echo $post->id; ?>">
                                    <button type="submit" name="delete_post" class="btn btn-danger btn-sm">Sil</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Post Modal -->
                        <div class="modal fade" id="postModal<?php echo $post->id; ?>" tabindex="-1" aria-labelledby="postModalLabel<?php echo $post->id; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="postModalLabel<?php echo $post->id; ?>">Gönderi Detayları</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="post-meta">
                                            <span><i class="fas fa-hashtag"></i> <strong>ID:</strong> <?php echo htmlspecialchars($post->id); ?></span>
                                            <span><i class="fas fa-user"></i> <strong>Kullanıcı:</strong> <?php echo htmlspecialchars($post->username); ?></span>
                                            <span><i class="fas fa-calendar"></i> <strong>Tarih:</strong> <?php echo date('d.m.Y H:i', strtotime($post->post_date)); ?></span>
                                        </div>
                                        <div class="post-content-full">
                                            <?php 
                                            // Display text content
                                            if (!empty($post->text_content)) {
                                                echo '<div class="content-type">Metin İçeriği</div>';
                                                echo nl2br(htmlspecialchars($post->text_content));
                                            }
                                            
                                            // Display media content
                                            $mediaContent = getMediaContent($post);
                                            if (!empty($mediaContent)) {
                                                echo '<div class="content-type">Medya İçeriği</div>';
                                                echo $mediaContent;
                                            }
                                            
                                            // Display post stats
                                            $comments = getPostComments($post->id);
                                            $likes = getPostLikes($post->id);
                                            
                                            echo '<div class="post-stats">';
                                            echo '<div class="post-stats-header">';
                                            echo '<div class="post-stats-count"><i class="fas fa-comments"></i> ' . count($comments) . ' Yorum</div>';
                                            echo '<div class="post-stats-count"><i class="fas fa-heart"></i> ' . count($likes) . ' Beğeni</div>';
                                            echo '</div>';
                                            
                                            // Display comments in modal
                                            if (!empty($comments)) {
                                                echo '<div class="comments-section">';
                                                foreach ($comments as $comment) {
                                                    echo '<div class="comment-item">';
                                                    echo '<div class="comment-header">';
                                                    echo '<span class="comment-username"><i class="fas fa-user"></i> ' . htmlspecialchars($comment->username) . '</span>';
                                                    echo '<span class="comment-date"><i class="fas fa-clock"></i> ' . date('d.m.Y H:i', strtotime($comment->comment_date)) . '</span>';
                                                    echo '</div>';
                                                    echo '<p class="comment-text">' . nl2br(htmlspecialchars($comment->comment_text)) . '</p>';
                                                    echo '<div class="comment-actions">';
                                                    echo '<form method="POST" style="display: inline;">';
                                                    echo '<input type="hidden" name="comment_id" value="' . $comment->id . '">';
                                                    echo '<button type="submit" name="delete_comment" class="delete-comment-btn" onclick="return confirm(\'Bu yorumu silmek istediğinizden emin misiniz?\');">';
                                                    echo '<i class="fas fa-trash"></i> Yorumu Sil';
                                                    echo '</button>';
                                                    echo '</form>';
                                                    echo '</div>';
                                                    echo '</div>';
                                                }
                                                echo '</div>';
                                            }
                                            
                                            // Display likes in modal
                                            if (!empty($likes)) {
                                                echo '<div class="likes-section">';
                                                foreach ($likes as $like) {
                                                    echo '<div class="like-item">';
                                                    echo '<i class="fas fa-heart"></i> ' . htmlspecialchars($like->username);
                                                    echo '</div>';
                                                }
                                                echo '</div>';
                                            }
                                            
                                            echo '</div>';
                                            ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="post_id" value="<?php echo $post->id; ?>">
                                            <button type="submit" name="delete_post" class="btn btn-danger" onclick="return confirm('Bu gönderiyi silmek istediğinizden emin misiniz?');">Gönderiyi Sil</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-hide flash messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(function(message) {
            setTimeout(function() {
                message.style.display = 'none';
            }, 5000);
        });
    });
</script>
</body>
</html>