<?php

    use classes\Config;
    use models\{Post, Follow, UserRelation};

    $current_user_id = $user->getPropertyValue("id");

    $user_profile = Config::get("root/path") . (empty($user->getPropertyValue("picture")) ? "public/assets/images/logos/logo512.png" : $user->getPropertyValue("picture"));
    if(empty($user->getPropertyValue("cover"))) {
        $user_cover = "";
    } else {
        $user_cover = Config::get("root/path") . $user->getPropertyValue("cover");
    }
   
?>

<div id="master-left">
    <div class="flex-space">
        <h3 class="title-style-2">ADMİN PANELİ</h3>
        <div class="flex">
            <a href="<?php echo Config::get("root/path") . "admin-settings.php"; ?>" class="menu-button-style-3 settings-back" id="go-to-settings"></a>
            <a href="<?php echo Config::get("root/path") . "admin-search.php"; ?>" class="menu-button-style-3 search-background go-to-search"></a>
        </div>
    </div>
    <div>
        <div>
            
                <div class="profile-container" style="display: flex; align-items: center; gap: 15px;">
                    <div class="profile-owner-picture-left-panel-container">
                        <img src="<?php echo $user_profile; ?>" class="profile-owner-picture-left-panel" alt="">
                    </div>
                    <p class="label-style-3"><?php echo $user->getPropertyValue("username"); ?></p>
                </div>
                        
            <div id="master-left-container" style=" padding: 16px; border-radius: 8px;">
                <p style="color: #00ff00; font-size: 18px; margin-bottom: 16px; font-weight: bold;">Menü</p>

                <a href="<?php echo Config::get('root/path') . 'admin.php'; ?>" style="display: flex; align-items: center; text-decoration: none; color: #ffffff; padding: 12px 8px; border-radius: 8px; transition: background-color 0.3s ease; margin-bottom: 12px;">
                    <ion-icon name="people-outline" style="font-size: 20px; margin-right: 8px; color: #ffffff;"></ion-icon>
                    <span style="font-size: 16px; font-weight: 500;">Kullanıcılar</span>
                </a>

                <a href="<?php echo Config::get('root/path') . 'admin.php'; ?>" style="display: flex; align-items: center; text-decoration: none; color: #ffffff; padding: 12px 8px; border-radius: 8px; transition: background-color 0.3s ease; margin-bottom: 12px;">
                    <ion-icon name="send-outline" style="font-size: 20px; margin-right: 8px; color: #ffffff;"></ion-icon>
                    <span style="font-size: 16px; font-weight: 500;">Gönderiler</span>
                </a>

                <a href="<?php echo Config::get('root/path') . 'admin.php'; ?>" style="display: flex; align-items: center; text-decoration: none; color: #ffffff; padding: 12px 8px; border-radius: 8px; transition: background-color 0.3s ease; margin-bottom: 12px;">
                    <ion-icon name="refresh-outline" style="font-size: 20px; margin-right: 8px; color: #ffffff;"></ion-icon>
                    <span style="font-size: 16px; font-weight: 500;">Aktiflik Saatleri</span>
                </a>

                <a href="<?php echo Config::get('root/path') . 'admin.php'; ?>" style="display: flex; align-items: center; text-decoration: none; color: #ffffff; padding: 12px 8px; border-radius: 8px; transition: background-color 0.3s ease; margin-bottom: 12px;">
                    <ion-icon name="camera-outline" style="font-size: 20px; margin-right: 8px; color: #ffffff;"></ion-icon>
                    <span style="font-size: 16px; font-weight: 500;">Profil Resimleri</span>
                </a>

                <a href="<?php echo Config::get('root/path') . 'admin.php'; ?>" style="display: flex; align-items: center; text-decoration: none; color: #ffffff; padding: 12px 8px; border-radius: 8px; transition: background-color 0.3s ease; margin-bottom: 12px;">
                    <ion-icon name="ellipsis-horizontal-outline" style="font-size: 20px; margin-right: 8px; color: #ffffff;"></ion-icon>
                    <span style="font-size: 16px; font-weight: 500;">Yorumlar</span>
                </a>
            </div>


        </div>
    </div>
</div>
 <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
 <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>