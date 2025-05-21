<?php

    use classes\{Config, Session, Token, Common, Redirect};

    if(isset($_POST["logout"])) {
        if(Token::check(Common::getInput($_POST, "token_logout"), "logout")) {
            $user->logout();
            Redirect::to("login/login.php");
        }
    }

    $logo_path = $root . "public/assets/images/logos/large.png";
    $index_page_path = $root . "admin.php";

    $setting_profile_path = $root . "settings.php";
    $setting_account_path = $root . "settings-account.php";

?>
<div id="setting-left-pannel">
<div id="header" style="padding: 20px; text-align: center;">

    <!-- Logo Container (Ortalanmış) -->
    <div id="header-logo-container" style="margin-bottom: 15px;">
        <a href="<?php echo Config::get("root/path");?>admin.php" 
           style="text-decoration: none;
                  color: white;
                  font-family: Arial, sans-serif;
                  font-size: 24px;
                  font-weight: bold;">
           NEW WORLD
        </a>
    </div>

    <!-- Geri Dön Linki (Logo Altında) -->
    <div class="flex">
        <a href="<?php echo $index_page_path ?>" class="flex link go-back-to right-pos-margin" style="margin-right: 6px">NEW WORLD'e GERİ DÖN</a>
    </div>

</div>
  
    <div id="left-panel-menu">
        <div class="button-with-suboption relative <?php if(isset($profile_selected)) echo $profile_selected; ?>">
            <div class="relative">
                <div class="menu-button-icon profile-button-icon absolute"></div>
                <a href="<?php echo $setting_profile_path; ?>" class="menu-button">Profil</a>
            </div>
        </div>
        <div class="button-with-suboption relative">
            <div class="relative">
                <div class="menu-button-icon account-button-icon absolute"></div>
                <a href="<?php echo $setting_account_path; ?>" class="menu-button <?php if(isset($account_selected)) echo $account_selected; ?>">Hesap</a>
            </div>
        </div>
       
        <div class="button-with-suboption relative">
            <button name="logout" type="submit" form="logout-form" class="logout-btn">
            
            </button>
            <div class="relative">
                <div class="menu-button-icon logout-button-icon absolute"></div>
                <a href="<?php echo $setting_profile_path; ?>" class="menu-button profile-button">Çıkış</a>
            </div>
        </div>
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