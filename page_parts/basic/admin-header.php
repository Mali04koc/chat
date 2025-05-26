
<?php
    use classes\{Config, Token, Session, Common, Redirect};
    use models\{UserRelation, User, Message};

    if(isset($_POST["logout"])) {
        if(Token::check(Common::getInput($_POST, "token_logout"), "logout")) {
            $user->logout();
            Redirect::to("login/login.php");
        }
    }
    $setting_path = Config::get("root/path") . "admin-settings.php";

?>

<header>
    <div id="top-header">
        <div id="header-logo-container">
           
            <a href="<?php echo Config::get("root/path");?>admin.php" style="text-decoration: none; color: white; font-family: Arial, sans-serif; font-weight: bold; font-size: 24px; letter-spacing: 1px; text-transform: uppercase;" class="logo">NEW WORLD</a>
        </div>
        <div class="inline-logo-separator">〡</div>
        <div class="row-v-flex">
            <form action="<?php echo Config::get("root/path") . htmlspecialchars('admin-search.php'); ?>" method="GET" id="header-search-form">
                <input type="text" name="q" value="<?php echo isset($_GET["q"]) ? trim(htmlspecialchars($_GET["q"])) : '' ?>" class="input-text-style-1 search-back black-search-back" placeholder="Kullanıcı ara">
                <input type="submit" value="Ara" class="search-button-style-1">
            </form>
        </div>
        <div id="global-header-strip-container">
           
            <div class="menu-items-separator">〡</div>
            <div class="row-v-flex header-menu">

                <div class="horizontal-menu-item-wrapper">
                        <div id="header-picture-container" class="flex-row-column">
                            <img id="header-picture" src="<?php echo Config::get("root/path") . ($user->getPropertyValue("picture") != "" ? $user->getPropertyValue("picture") : "public/assets/images/icons/user.png"); ?>">
                        </div>
                        <?php echo $user->getPropertyValue("username");?>
                    
                </div>
            
            </div>
            <div class="menu-items-separator">〡</div>
            <div class="row-v-flex header-menu">
                <div class="horizontal-menu-item-wrapper">
                    <a href="" id="user-photo-button" class="button-with-suboption"></a>
                    <div class="sub-options-container sub-options-container-style-1">
                        <!-- When this link get pressed you need to redirect the user to the notification post -->
                        <div class="options-container">
                                <div class="message-option-item" style="align-items: center">
                                    <div class="header-menu-profile-picture-container">
                                        <img src="<?php echo Config::get("root/path") . ($user->getPropertyValue("picture") != "" ? $user->getPropertyValue("picture") : "public/assets/images/icons/user.png"); ?>" class="header-menu-profile-picture" alt="user's profile picture">
                                    </div>
                                     <div class="message-content-container">
                                        <p class="account-user"><?php echo $user->getPropertyValue("username"); ?></p>
                                    </div>
                                </div>
                            <div class="options-separator-style-1"></div>
                            <a href="<?php echo $setting_path; ?>" class="sub-option">
                                <div class="row-v-flex">
                                    <div>
                                        <img src="<?php echo Config::get("root/path") . "public/assets/images/icons/settings.png" ?>" class="image-style-2" alt="user's profile picture">
                                    </div>
                                    <div class="message-content-container">
                                        <p style="margin: 4px">Ayarlar</p>
                                    </div>
                                </div>
                            </a>
                           
                            <button name="logout" type="submit" form="logout-form" class="sub-option logout-button">
                                <div class="row-v-flex">
                                    <div>
                                        <img src="<?php echo Config::get("root/path") . "public/assets/images/icons/logout.png" ?>" class="image-style-2" alt="user's profile picture">
                                    </div>
                                    <div class="message-content-container">
                                        <p style="margin: 4px">Çıkış</p>
                                    </div>
                                </div>
                            </button>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="logout-form">
                                <input type="hidden" name="token_logout" value="<?php 
                                    if(Session::exists("logout")) 
                                        echo Session::get("logout");
                                    else {
                                        echo Token::generate("logout");
                                    }
                                ?>">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</header>

