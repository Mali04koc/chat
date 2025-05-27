<?php

require_once "vendor/autoload.php";
require_once "core/init.php";
require_once "classes/middleware.php";


use classes\{DB, Validation, Common, Session, Token, Redirect, Hash};

error_reporting();

// DONT'T FORGET $user OBJECT IS DECLARED WITHIN INIT.PHP (REALLY IMPORTANT TO SEE TO SEE [IMPORTANT#4]
// Here we check if the user is not logged in and we redirect him to login page

$middleware = new \classes\AuthMiddleware();
if (!$middleware->handle()) {
    exit;
}

if(!$user->getPropertyValue("isLoggedIn")) {
   Redirect::to("login/login.php");
 }

$welcomeMessage = '';
if(Session::exists("register_success") && $user->getPropertyValue("username") == Session::get("new_username")) {
    $welcomeMessage = Session::flash("new_username") . ", " . Session::flash("register_success");
}

$profile_selected = 'selected-button';

$fullname = $user->getPropertyValue("firstname") . " " . $user->getPropertyValue("lastname");
$username = $user->getPropertyValue("username");
$bio = $user->getPropertyValue("bio");
$picture = $root . (empty($user->getPropertyValue("picture")) ? "public/assets/images/logos/logo512.png" : $user->getPropertyValue("picture"));
$cover = $root . $user->getPropertyValue("cover");
$profile = $root . "profile.php?username=" . $user->getPropertyValue("username");
$private = $user->getPropertyValue("private");

$user_metadata = array(
    array(
        "label"=>"",
        "content"=>""
    ),
    array(
        "label"=>"",
        "content"=>""
    ),
    array(
        "label"=>"",
        "content"=>""
    ),
    array(
        "label"=>"",
        "content"=>""
    ),
    array(
        "label"=>"",
        "content"=>""
    ),
    array(
        "label"=>"",
        "content"=>""
    ),
);

$metadata = $user->get_metadata();
$count = 0;
foreach($metadata as $mdata) {
    $user_metadata[$count]["label"] = $mdata->label;
    $user_metadata[$count]["content"] = $mdata->content;

    $count++;
}

include_once 'functions/sanitize_text.php';
if(isset($_POST["save-changes"])) {
    if(Token::check(Common::getInput($_POST, "token_save_changes"), "saveEdits")) {
        // Think to add middle name
        // First we get the new first and last names
        $fn = sanitize_text($_POST["full-name"]);
        $fn = explode(" ", $fn);

        if(count($fn) > 1) {
            $new_firstname = $fn[0];
            /* IF you add middle name, first name will take the first one, middle the second one, and then lastname will take all the remaining
            $middlename = $fullname[1]; please add middle name .. o.- lol
            */
            $new_lastname = $fn[1];
        } else {
            $new_firstname = $fn[0];
            $new_lastname = "";
        }

        $new_bio = sanitize_text($_POST["bio"]);
        $new_username = sanitize_text($_POST["username"]);
        $new_private = is_numeric($_POST["private"]) 
                        && ($_POST["private"] == '1' || $_POST["private"] == '-1') 
                        ? $_POST["private"] 
                        : $user->getPropertyValue("private");

        $validator = new Validation();
        // Validate bio
        $validator->check($_POST, array(
            "bio"=>array(
                "name"=>"Bio",
                "max"=>799
            )
        ));

        if($new_username != $username) {
            $validator->check($_POST, array(
                "username"=>array(
                    "name"=>"Username",
                    "required"=>true,
                    "unique"=>true,
                    "max"=>255,
                    "min"=>6,
                )
            ));
        }

        if(file_exists($_FILES['cover']['tmp_name']) && is_uploaded_file($_FILES['cover']['tmp_name'])) {
            $validator->check($_FILES, array(
                "cover"=>array(
                    "name"=>"Cover",
                    "image"=>"image"
                )
            ));
        }
        
        if(file_exists($_FILES['avatar']['tmp_name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
            $validator->check($_FILES, array(
                "avatar"=>array(
                    "name"=>"Avatar",
                    "image"=>"image"
                )
            ));
        }
        
        if($validator->passed()) {
            $user->setPropertyValue("username", $new_username);
            $user->setPropertyValue("firstname", $new_firstname);
            $user->setPropertyValue("lastname", $new_lastname);
            $user->setPropertyValue("bio", $new_bio);
            $user->setPropertyValue("private", $new_private);

            /*
                IMPORTANT: notice when we change the username in th database we need also to change it in data folder where the user
                images and posts and covers are stored so after storing the assets and change the database we have to change the folder
                name as well !!!
                IMPORTANT: If the user change the cover or picture or both but not username, we don't have to rename the directory !
                Notice here, if the user has already a picture, its path is stored with the older username, so we use $username not $new_username
            */
            $profilePicturesDir = 'data/users/' . $username . "/media/pictures/";
            $coversDir = 'data/users/' . $username . "/media/covers/";

            // Here we check if the user change the cover again; if so it is valide because we already check it using validator
            if(file_exists($_FILES['cover']['tmp_name']) && is_uploaded_file($_FILES['cover']['tmp_name'])) {
                // we generate a unique hash to name the image
                $generatedName = Hash::unique();
                $generatedName = trim(htmlspecialchars($generatedName));

                // Then we fetch the image type t o concatenate it with the generated name
                $file = $_FILES["cover"]["name"];
                $original_extension = (false === $pos = strrpos($file, '.')) ? '' : substr($file, $pos);

                $targetFile = $coversDir . $generatedName . $original_extension;
                if (move_uploaded_file($_FILES["cover"]["tmp_name"], $targetFile)) {
                    /*
                        Here we don't have to store the path with the older username, but we need to change the username in the path
                        to the new username so that we can access it later successfully, otherwise we'll get errors while fetcheing
                        Notice we'll change the folder name just after moving the image to the folder
                    */
                    $new_target = 'data/users/' . $new_username . "/media/covers/" . $generatedName . $original_extension;
                    $user->setPropertyValue("cover", $new_target);
                } else {
                    $validator->addError("Üzgünüz, kapak fotoğrafınız yüklenirken bir hata oluştu.");
                }
            }
            if(file_exists($_FILES['avatar']['tmp_name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
                // we generate a unique hash to name the image
                $generatedName = Hash::unique();
                $generatedName = trim(htmlspecialchars($generatedName));

                // Then we fetch the image type t o concatenate it with the generated name
                $file = $_FILES["avatar"]["name"];
                $original_extension = (false === $pos = strrpos($file, '.')) ? '' : substr($file, $pos);

                $targetFile = $profilePicturesDir . $generatedName . $original_extension;
                if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFile)) {
                    /*
                        Here we don't have to store the path with the older username, but we need to change the username in the path
                        to the new username so that we can access it later successfully, otherwise we'll get errors while fetcheing
                        Notice we'll change the folder name just after moving the image to the folder
                    */
                    $new_target = 'data/users/' . $new_username . "/media/pictures/" . $generatedName . $original_extension;
                    $user->setPropertyValue("picture", $new_target);
                } else {
                    $validator->addError("Üzgünüz, profil fotoğrafınız yüklenirken bir hata oluştu.");
                }
            }

            if($new_username != $username) {
                // Here we need to give recursive function the directory of the data folder
                $old_user_data_dir = __DIR__ . '/data/users/' . $username;
                $new_user_data_dir = __DIR__ . '/data/users/' . $new_username;
                recurse_copy($old_user_data_dir, $new_user_data_dir);
                deleteDir($old_user_data_dir);
                $user->setPropertyValue("username", $new_username);
            }

            // Get the new version labels along with their contents
            $new_user_metadata = array(
                array(
                    "label"=>sanitize_text($_POST["label1"]),
                    "content"=>sanitize_text($_POST["content1"])
                ),
                array(
                    "label"=>sanitize_text($_POST["label2"]),
                    "content"=>sanitize_text($_POST["content2"])
                ),
                array(
                    "label"=>sanitize_text($_POST["label3"]),
                    "content"=>sanitize_text($_POST["content3"])
                ),
                array(
                    "label"=>sanitize_text($_POST["label4"]),
                    "content"=>sanitize_text($_POST["content4"])
                ),
                array(
                    "label"=>sanitize_text($_POST["label5"]),
                    "content"=>sanitize_text($_POST["content5"])
                ),
                array(
                    "label"=>sanitize_text($_POST["label6"]),
                    "content"=>sanitize_text($_POST["content6"])
                ),
            );

            $user->set_metadata($new_user_metadata);

            $user->update();

            $fullname = $new_firstname . (empty($new_lastname) ? "" : " " . $new_lastname);
            $username = $new_username;
            $bio = $new_bio;
            $picture = $root . $user->getPropertyValue("picture");
            $cover = $root . $user->getPropertyValue("cover");
            $profile = $root . "profile.php?username=" . $new_username;
            $private = $new_private;
            $user_metadata = $new_user_metadata;
        } else {
            echo '<div class="alert-box"><div class="alert alert-danger">';
            foreach($validator->errors() as $error) {
                echo htmlspecialchars($error) . "<br>";
            }
            // Dosya yükleme hatalarını da ekle
            if (isset($_FILES['cover']) && $_FILES['cover']['error'] !== UPLOAD_ERR_OK && $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {
                echo "Cover yüklenirken hata oluştu: " . $_FILES['cover']['error'] . "<br>";
            }
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_OK && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                echo "Avatar yüklenirken hata oluştu: " . $_FILES['avatar']['error'] . "<br>";
            }
            echo '</div></div>';
        }
    }
}

function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}

function recurse_copy($src,$dst) {
    $dir = opendir($src); 
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEW WORLD-Ayarlar</title>
<link rel='shortcut icon' type='image/x-icon' href='public/assets/images/favicons/favicon.ico' />
<link rel="stylesheet" href="public/css/global.css">
<link rel="stylesheet" href="public/css/settings.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="public/javascript/config.js" defer></script>
<script src="public/javascript/settings.js" defer></script>
</head>
<body>
<main>
    <?php require_once "page_parts/settings/left-panel.php" ?>
    <div id="global-container">
        <div id="setting-master-container">
            <h1 class="no-margin">Profili Düzenle</h1>
            <div class="setting-block-line-separator"></div>
            <div>
                <div class="flex-column">
                    <label for="username" class="setting-label1">Kullanıcı Adı</label>
                    <input type="text" form="save-form" class="setting-input-text-style" value="<?php echo $username; ?>" name="username" id="username">
                </div>
                <div class="flex-column">
                    <label for="fullname" class="setting-label1">AD-SOYAD</label>
                    <input type="text" form="save-form" class="setting-input-text-style" value="<?php echo $fullname; ?>" name="full-name" id="fullname">
                </div>
                <div class="flex-column">
                    <label for="bio" class="setting-label1">Biyografi</label>
                    <textarea form="save-form" spellcheck="false" name="bio" id="bio" class="setting-input-text-style textarea-style"><?php echo $bio; ?></textarea>
                </div>
                <div id="profile-asset-wrapper" class="flex">
                    <a href="<?php echo $profile; ?>" id="assets-wrapper">
                        <div id="setting-cover-container">
                            <img src="<?php echo $cover; ?>" class="setting-cover" alt="">
                        </div>
                        <div class="flex padding16">
                            <div id="setting-picture-container" style="margin-right: 10px">
                                <img src="<?php echo $picture; ?>" alt="avatar" id="setting-picture">
                            </div>
                            <div>
                                <h3 class="no-margin"><?php echo $fullname; ?></h3>
                                <p class="no-margin">@<?php echo $username; ?></p>
                            </div>
                        </div>
                    </a>
                    <div id="setting-file-inputs-container">
                        <div>
                            <label for="cover-input" class="setting-label" style="font-size: 16px; margin-bottom: 5px">Arka Plan</label>
                            <input type="file" form="save-form" name="cover" class="block" id="cover-input">
                            <p class="no-margin input-hint">PNG, JPG, JPEG veya GIF. En fazla 2 MB. 1500x500px boyutuna getirilecek.</p>
                        </div>
                        <div style="margin-top: 10px">
                            <label for="avatar-input" class="setting-label" style="font-size: 16px; margin-bottom: 5px">Avatar</label>
                            <input type="file" form="save-form" name="avatar" class="block" id="avatar-input">
                            <p class="no-margin input-hint">PNG, JPG, JPEG veya GIF. En fazla 5 MB. 400x400px boyutuna getirilecek.</p>
                        </div>
                        <div style="margin-top: 10px">
                          
                            <input name="private" form="save-form" value="<?php echo $private; ?>" id="private-account-state" type="hidden">
                        </div>
                    </div>
                </div>
                <div style="margin-top: 26px">
                    <label for="fullname" class="setting-label1">Profil Bilgileri</label>
                    <p class="input-hint">6 farklı özelliğin profilinde gösterilir.</p>
                    <div class="flex">
                        <input type="text" form="save-form" class="setting-input-text-style meta-data-input" value="<?php echo $user_metadata[0]["label"] ?>" placeholder="Başlık" name="label1" id="label1">
                        <input type="text" form="save-form" class="setting-input-text-style meta-data-input" value="<?php echo $user_metadata[0]["content"] ?>" placeholder="İçerik" name="content1" id="content1">
                    </div>
                    <div class="flex">
                        <input type="text" form="save-form" class="setting-input-text-style meta-data-input" value="<?php echo $user_metadata[1]["label"] ?>" placeholder="Başlık" name="label2" id="label2">
                        <input type="text" form="save-form" class="setting-input-text-style meta-data-input" value="<?php echo $user_metadata[1]["content"] ?>" placeholder="İçerik" name="content2" id="content2">
                    </div>
                    <div class="flex">
                        <input type="text" form="save-form" class="setting-input-text-style meta-data-input" value="<?php echo $user_metadata[2]["label"] ?>" placeholder="Başlık" name="label3" id="label3">
                        <input type="text" form="save-form" class="setting-input-text-style meta-data-input" value="<?php echo $user_metadata[2]["content"] ?>" placeholder="İçerik" name="content3" id="content3">
                    </div>
                    <div class="flex">
                        <input type="text" form="save-form" class="setting-input-text-style meta-data-input" value="<?php echo $user_metadata[3]["label"] ?>" placeholder="Başlık" name="label4" id="label4">
                        <input type="text" form="save-form" class="setting-input-text-style meta-data-input" value="<?php echo $user_metadata[3]["content"] ?>" placeholder="İçerik" name="content4" id="content4">
                    </div>
                    <div class="flex">
                        <input type="text" form="save-form" class="setting-input-text-style meta-data-input" value="<?php echo $user_metadata[4]["label"] ?>" placeholder="Başlık" name="label5" id="label5">
                        <input type="text" form="save-form" class="setting-input-text-style meta-data-input" value="<?php echo $user_metadata[4]["content"] ?>" placeholder="İçerik" name="content5" id="content5">
                    </div>
                    <div class="flex">
                        <input type="text" form="save-form" class="setting-input-text-style meta-data-input" value="<?php echo $user_metadata[5]["label"] ?>" placeholder="Başlık" name="label6" id="label6">
                        <input type="text" form="save-form" class="setting-input-text-style meta-data-input" value="<?php echo $user_metadata[5]["content"] ?>" placeholder="İçerik" name="content6" id="content6">
                    </div>
                </div>

                <form action="" method="POST" id="save-form" enctype="multipart/form-data">
                    <input type="hidden" name="token_save_changes" value="<?php echo Token::generate("saveEdits"); ?>">
                    <input type="submit" value="Değişiklikleri Kaydet" name="save-changes" id="save-button">
                </form>
            </div>
        </div>
    </div>
</main>
</body>
</html>