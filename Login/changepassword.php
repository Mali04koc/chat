<?php

    require_once "../vendor/autoload.php";
    require_once "../core/init.php";

    use classes\{DB, Config, Validation, Common, Session, Token, Hash, Redirect};
    use models\User;

    // First we check if the user put his email and confirmatin code sent succesfully, if there's something wrong we redirect
    if(!Session::exists("password-change-allow")) {
        Redirect::to("login.php");
    }

    $validate = new Validation();
    
    // Empty the flash password change
    Session::delete("Password_changed");

    $user->fetchUser("id", Session::get("u_id"));

    if(isset($_POST["save"])) {
        if(Token::check(Common::getInput($_POST, "token_password_save"), "reset-pasword")) {
            $validate->check($_POST, array(
                "password"=>array(
                    "name"=>"Password",
                    "required"=>true,
                    "min"=>6
                    /* (later)
                    "strength"=>array(

                    )*/
                ),
                "password_again"=>array(
                    "name"=>"Repeated password",
                    "required"=>true,
                    "matches"=>"password"
                )
            ));

            if($validate->passed()) {
                if(Common::getInput($_POST, "email") != $user->getPropertyValue("email")) {
                    $validate->addError("Email adresinizi yanlış girdiniz.");
                } else {
                    // yeni bir hash oluşturuyoruz
                    $newSalt = Hash::salt(16);
                    $newPassword = Hash::make(Common::getInput($_POST, "password"), $newSalt);

                    /*
                    şifre ve hashi updateliyoruz
                    */
                    $user->setPropertyValue("password", $newPassword);
                    $user->setPropertyValue("salt", $newSalt);
                    
                    $user->update();

                    Session::flash("Password_changed", "Şifreniz başarıyla değiştirildi.");
                     
                }
            }

            
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ŞİFRE YENİLEME</title>
    <link rel="stylesheet" href="../public/css/giris.css">
    <link rel='shortcut icon' type='image/x-icon' href='../public/assets/images/favicons/favicon.png' />
</head>
<body>
    
    <section>
        <div class="login-box">
            <div id="reset-section">
            
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" autocomplete="off" class="flex-form" id="login-form">
                <h2>ŞİFRE YENİLE</h2>

                <?php if ($validate->errors()): ?>
                    <div class="error-message">
                        <?php echo implode('<br>', $validate->errors()); ?>
                    </div>
                <?php endif; ?>

                 
        <?php if (Session::exists("Password_changed")): ?>
        <div class="success-container">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="success-message">
                <?php echo Session::flash("Password_changed"); ?>
            </div>
        </div>
        <?php endif; ?>
                
              

                <div class="input-box">
                    <span class="icon"><ion-icon name="mail"></ion-icon></span>
                    <input type="text" name="email"  value="<?php echo htmlspecialchars($user->getPropertyValue("email")); ?>" autocomplete="off" placeholder="Kullanıcı Adı Veya Email" required>
                    <label>Email Giriniz</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                    <input type="password" name="password"  autocomplete="off" placeholder="Yeni Şifre" required>
                    <label>Yeni Şifre Giriniz</label>
                </div>
                
                <div class="input-box">
                    <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                    <input type="password" name="password_again"  autocomplete="off" placeholder="Yeni Şifre Tekrar" required>
                    <label >Yeni Şifreyi Tekrar Giriniz</label>
                </div>              

                <input type="hidden" name="token_password_save" value="<?php echo Token::generate("reset-pasword"); ?>">                
                <button type="submit" name="save" value="Save">Kaydet</button>
                <a href="../Login/login.php" class="icon">
                <ion-icon name="home-outline"></ion-icon>
            </a>>
                            
            </form>
        </div>
    </section>
    
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    
</body>
</html>
