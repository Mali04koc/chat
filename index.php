<?php

    require_once "vendor/autoload.php";
    require_once "core/init.php";
    require_once "classes/middleware.php";


    use classes\{DB, Config, Validation, Common, Session, Token, Hash, Redirect, Cookie};
    use models\{Post, UserRelation, Follow};
    use layouts\post\Post as Post_View;
    use layouts\master_right\Right as MasterRightComponents;

    // user nesnesini init.phpde oluşturduk
    // Aşağıdaki kod sayesinde kullanıcı giriş yapmadan üstten url ile sitede dolaşmasını engelliyoruz.Giriş sayfasına yönlendiriyoruz
    $middleware = new \classes\AuthMiddleware();
    $middleware->handle();
    
    if(!$user->getPropertyValue("isLoggedIn")) {
        Redirect::to("login/login.php");
    }

    $welcomeMessage = '';
    if(Session::exists("register_success") && $user->getPropertyValue("username") == Session::get("new_username")) {
        $welcomeMessage = Session::flash("new_username") . ", " . Session::flash("register_success");
    }


    $current_user_id = $user->getPropertyValue("id");
    $journal_posts = Post::fetch_journal_posts($current_user_id);
    // Let's randomly sort array for now
    shuffle($journal_posts);
    /*usort($journal_posts, 'post_date_latest_sort');

    function post_date_latest_sort($post1, $post2) {
        return $post1->get_property('post_date') == $post2->get_property('post_date') ? 0 : ($post1->get_property('post_date') > $post2->get_property('post_date')) ? -1 : 1;
    }*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEW WORLD</title>
    <link rel='shortcut icon' type='image/x-icon' href='public/assets/images/favicons/favicon.png' />
    <link rel="stylesheet" href="public/css/global.css">
    <link rel="stylesheet" href="public/css/header.css">
    <link rel="stylesheet" href="public/css/index.css">
    <link rel="stylesheet" href="public/css/create-post-style.css">
    <link rel="stylesheet" href="public/css/master-left-panel.css">
    <link rel="stylesheet" href="public/css/master-right-contacts.css">
    <link rel="stylesheet" href="public/css/post.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="public/javascript/config.js" defer></script>
    <script src="public/javascript/index.js" defer></script>
    <script src="public/javascript/global.js" defer></script>
    <script src="public/javascript/master-right.js" defer></script>
    <script src="public/javascript/post.js" defer></script>
</head>
<body>
    <?php include_once "page_parts/basic/header.php"; ?>
    <main>
        <div id="global-container" class="relative">
            <div class="post-viewer-only">
                <div class="viewer-post-wrapper">
                    <img src="" class="post-view-image" alt="Gönderi Görüntüsü">
                    <div class="close-view-post"></div>
                </div>
            </div>
            <?php include_once "page_parts/basic/master-left.php"; ?>
            <div id="master-middle">
                <div class="green-message">
                    <p class="green-message-text"><?php echo $welcomeMessage; ?></p>
                    <script type="text/javascript" defer>
                        if($(".green-message-text").text() !== "") {
                            $(".green-message").css("display", "block");
                        }
                    </script>
                </div>
                <div class="red-message">
                    <p class="red-message-text"></p>
                    <div class="delete-message-hint">
                    </div>
                </div>
                <?php if (\classes\Session::exists('danger')): ?>
                    <div class="alert-box">
                        <div class="alert alert-danger">
                            <?php echo \classes\Session::flash('danger'); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <script>
                    setTimeout(function() {
                        var alertBox = document.querySelector('.alert-box');
                        if(alertBox){
                            alertBox.style.transition = "opacity 0.5s";
                            alertBox.style.opacity = 0;
                            setTimeout(function() {
                                if(alertBox.parentNode) alertBox.parentNode.removeChild(alertBox);
                            }, 500);
                        }
                    }, 3000); // 5000 ms = 5 saniye
                </script>
                <style>
            .alert-box {
                position: fixed;
                top: 70px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 1;
                width: 90%;
                max-width: 400px;
                box-shadow: 0 4px 16px rgba(0,0,0,0.15);
                animation: slideDown 0.5s ease;
            }

            .alert {
                padding: 18px 24px;
                border-radius: 8px;
                font-size: 1.1rem;
                font-weight: 500;
                text-align: center;
                margin-bottom: 0;
                border: 1.5px solid #f5c6cb;
                background: linear-gradient(90deg, #f8d7da 80%, #fff 100%);
                color: #721c24;
                letter-spacing: 0.5px;
            }

            @keyframes slideDown {
                0% {
                    opacity: 0;
                    transform: translate(-50%, -40px);
                }
                100% {
                    opacity: 1;
                    transform: translate(-50%, 0);
                }
            }
                </style>

                <?php include_once "page_parts/basic/post_creator.php"; ?>
                <div id="posts-container">
                    <?php if(count($journal_posts) == 0) { ?>
                        <div id="empty-posts-message">
                            <h2>Arkadaş ekle ve onların yeni dünyalarını gör</h1>
                            <p>Buraya <a href="http://127.0.0.1/CHAT/search.php" class="link" style="color: rgb(66, 219, 66)">tıkla</a> ve arama sayfasına git</p>
                        </div>
                    <?php } else { 
                        foreach($journal_posts as $post) {
                            $post_view = new Post_View();

                            echo $post_view->generate_post($post, $user);
                        }
                    }
                    ?>

                </div>
            </div>
            <?php include_once "page_parts/basic/master-right.php" ?>
        </div>
    </main>
</body>
</html>