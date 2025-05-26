<?php
require_once "vendor/autoload.php";
require_once "core/init.php";
require_once "classes/middleware.php";

use classes\{DB, Config, Validation, Common, Session, Token, Hash, Redirect, Cookie};
use models\{Post, UserRelation, Follow, User};

global $user;

$middleware = new \classes\AuthMiddleware();
$middleware->handle();

// Start output buffering
ob_start();

// Kullanıcı giriş kontrolü
if (!$user->getPropertyValue("isLoggedIn")) {
    Redirect::to("login/login.php");
}

// Handle logout
if(isset($_POST["logout"])) {
    if(Token::check(Common::getInput($_POST, "token_logout"), "logout")) {
        $user->logout();
        Redirect::to("login/login.php");
    }
}

// Normal sayfa yüklemeleri için
$db = DB::getInstance();
$query = $db->query("SELECT * FROM user_info WHERE user_type != 2 ORDER BY joined DESC");
$users = $query->results();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEW WORLD-AKTİFLİK YÖNETİMİ</title>
    <link rel='shortcut icon' type='image/x-icon' href='public/assets/images/favicons/favicon.png' />
    <link rel="stylesheet" href="public/css/global.css">
    <link rel="stylesheet" href="public/css/header.css">
    <link rel="stylesheet" href="public/css/index.css">
    <link rel="stylesheet" href="public/css/create-post-style.css">
    <link rel="stylesheet" href="public/css/master-left-panel.css">
    <link rel="stylesheet" href="public/css/master-right-contacts.css">
    <link rel="stylesheet" href="public/css/post.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
     
            #master-left {
            color: white !important;
        }

        #master-left .title-style-2 {
            color: white !important;
        }

        #master-left .label-style-3 {
            color: white !important;
        }

        .profile-container p {
            color: white !important;
        }
        body {
            background: #232323 !important;
        }
        .admin-table-wrapper {
            max-width: 98vw;
            width: 98vw;
            margin: 40px auto 0 auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.13);
            padding: 32px 24px 24px 24px;
            position: relative;
            left: -60px;
        }
        .admin-title {
            color: #232323;
            margin-bottom: 18px;
            font-weight: 700;
            font-size: 1.4em;
            text-align: left;
        }
        .admin-table {
            width: 100%;
            background: transparent;
            color: #222;
            font-size: 0.97em;
            border-collapse: separate;
            border-spacing: 0 6px;
        }
        .admin-table thead th {
            background: #f4f4f4;
            border-bottom: 2px solid #e0e0e0;
            color: #232323;
            font-weight: 600;
            padding: 10px 16px;
            text-align: left;
            white-space: nowrap;
        }
        .admin-table tbody td {
            padding: 10px 16px;
            vertical-align: middle;
            background: #fafafa;
            border-bottom: 1px solid #ececec;
            white-space: nowrap;
            transition: background 0.2s;
        }
        .admin-table tbody tr:last-child td {
            border-bottom: none;
        }
        .admin-table tbody tr:hover td {
            background: #f0f0f0 !important;
        }
        
     
        @media (max-width: 900px) {
            .admin-table-wrapper {
                padding: 8px 2px;
            }
            .admin-table {
                font-size: 0.93em;
            }
            .admin-title {
                font-size: 1.1em;
            }
            .admin-username {
                font-size: 1em;
                padding: 4px 10px 4px 8px;
            }
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="public/javascript/config.js" defer></script>
    <script src="public/javascript/index.js" defer></script>
    <script src="public/javascript/global.js" defer></script>
    <script src="public/javascript/master-right.js" defer></script>
    <script src="public/javascript/post.js" defer></script>
    <script src="public/javascript/delete_user.js" defer></script>
</head>
<body>
    <?php include_once "page_parts/basic/admin-header.php"; ?>
    <main>

        <div id="global-container" class="relative">
            <div class="post-viewer-only">
                <div class="viewer-post-wrapper">
                    <img src="" class="post-view-image" alt="Gönderi Görüntüsü">
                    <div class="close-view-post"></div>
                </div>
            </div>
            <?php include_once "page_parts/basic/admin-master-left.php"; ?>
            <div id="master-middle">
                <h2 class="admin-title">Kullanıcı Listesi</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kullanıcı Adı</th>
                            <th>Ad</th>
                            <th>Soyad</th>
                            <th>E-posta</th>
                            <th>Aktiflik</th>
                            <th>Durum</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user->id) ?></td>
                                <td><?= htmlspecialchars($user->username) ?></td>
                                <td><?= htmlspecialchars($user->firstname) ?></td>
                                <td><?= htmlspecialchars($user->lastname) ?></td>
                                <td><?= htmlspecialchars($user->email) ?></td>
                                <td><?= htmlspecialchars($user->last_active_update) ?></td>
                                <td>
                                    <?php
                                        // Kullanıcı aktif mi kontrolü (örnek: son aktiflik 5 dakika içinde ise aktif)
                                        $isActive = false;
                                        if (!empty($user->last_active_update)) {
                                            $lastActive = strtotime($user->last_active_update);
                                            $now = time();
                                            // 5 dakika (300 saniye) içinde aktifse
                                            if ($now - $lastActive <= 300) {
                                                $isActive = true;
                                            }
                                        }
                                    ?>
                                    <?php if ($isActive): ?>
                                        <span style="display: inline-flex; align-items: center; gap: 6px;">
                                            <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: #4caf50;"></span>
                                            <span style="color: #4caf50; font-weight: 600;">Aktif</span>
                                        </span>
                                    <?php else: ?>
                                        <span style="display: inline-flex; align-items: center; gap: 6px;">
                                            <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: #7a8a7a;"></span>
                                            <span style="color: #7a8a7a; font-weight: 600;">İnaktif</span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>


