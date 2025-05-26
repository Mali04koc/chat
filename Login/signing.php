<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "C:/xampp/htdocs/chat/vendor/autoload.php";;
require_once "C:/xampp/htdocs/chat/core/init.php";

use classes\{DB, Config, Validation, Common, Session, Token, Hash, Redirect};
use models\User;

if(isset($_POST["register"])) {
    $validate = new Validation();
    if(Token::check(Common::getInput($_POST, "token_reg"), "register")) {
        $validate->check($_POST, array(
            "firstname"=>array(
                "name"=>"Firstname",
                "min"=>2,
                "max"=>50
            ),
            "lastname"=>array(
                "name"=>"Lastname",
                "min"=>2,
                "max"=>50
            ),
            "username"=>array(
                "name"=>"Username",
                "required"=>true,
                "min"=>6,
                "max"=>20,
                "unique"=>true
            ),
            "email"=>array(
                "name"=>"Email",
                "required"=>true,
                "email-or-username"=>true
            ),
            "password"=>array(
                "name"=>"Password",
                "required"=>true,
                "min"=>6
            ),
            "password_again"=>array(
                "name"=>"Repeated password",
                "required"=>true,
                "matches"=>"password"
            ),
        ));
        if($validate->passed()) {
            $salt = Hash::salt(16);
            $user = new User();
            $user->setData(array(
                "firstname"=>Common::getInput($_POST, "firstname"),
                "lastname"=>Common::getInput($_POST, "lastname"),
                "username"=>Common::getInput($_POST, "username"),
                "email"=>Common::getInput($_POST, "email"),
                "password"=> Hash::make(Common::getInput($_POST, "password"), $salt),
                "salt"=>$salt,
                "joined"=> date("Y/m/d h:i:s"),
                "user_type"=>1,
                "cover"=>'',
                "picture"=>'',
                "private"=>-1
            ));
            if($user->add()) {
                $reg_success_message = "Hesabın başarıyla oluşturuldu. Şimdi giriş yapabilirsin.";
                Session::flash("register_success", "New World'e hoş geldin " . Common::getInput($_POST, "firstname") . " " . Common::getInput($_POST, "lastname") . "!");
                Session::flash("new_username", Common::getInput($_POST, "username"));
            } else {
                $login_failure_message = "Veritabanına kullanıcı eklenirken bir hata oluştu.";
            }
        } else {
            $login_failure_message = $validate->errors()[0];
        }
    } else {
        $login_failure_message = "Token doğrulaması başarısız oldu.";
    }
}
?>







<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- tarayıcının en güncel dökümanını kullanır -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEW WORLD-KAYIT </title>
    <link rel="stylesheet" href="../public/css/giris.css">
    <link rel='shortcut icon' type='image/x-icon' href='../public/assets/images/favicons/favicon.png' />

</head>

<body>

    <section>
    <div class="login-box">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
            <h2>KAYIT OL</h2>
               
           <?php  if (isset($reg_success_message)): ?>
                        <div class="error-message"> 
                            <?php echo $reg_success_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($login_failure_message)): ?>
                        <div class="error-message" style="color: red;">
                            <?php echo $login_failure_message; ?>
                        </div>
                    <?php endif; ?>
                  
                        
                  
                
            <div class="input-box">
                    <input type="text" name="firstname" value="<?php echo htmlspecialchars(Common::getInput($_POST, "firstname")); ?>" id="firstname" autocomplete="off" placeholder="Ad" required>
                    <label for="firstname">Adınızı Giriniz</label>
            </div>
             <div class="input-box">
                    <input type="text" name="lastname" value="<?php echo htmlspecialchars(Common::getInput($_POST, "lastname")); ?>" id="lastname"autocomplete="off" placeholder="Soyad" required>
                    <label for="lastname">Soyadınızı Giriniz</label>
            </div>

             <div class="input-box">
                    <input type="text"   name="username" value="<?php echo htmlspecialchars(Common::getInput($_POST, "username")); ?>" id="username" autocomplete="off"placeholder="Kullanıcı Adı" required>
                    <label for="username">Kullanıcı Adınızı Giriniz</label>
            </div>
             <div class="input-box">
                    <input type="mail" name="email" value="<?php echo htmlspecialchars(Common::getInput($_POST, "email")); ?>" id="email" autocomplete="off" placeholder="Email" required>
                    <label for="email">Emailinizi Giriniz</label>
            </div>
             <div class="input-box">
                    <input type="password"  name="password" id="password" autocomplete="off" placeholder="Şifre" required>
                    <label for="password">Şifrenizi Giriniz</label>
            </div>
            <div class="input-box">
                    <input type="password" name="password_again" id="password_again" autocomplete="off"placeholder="Tekrar Şifre" required>
                    <label for="password_again">Tekrar Şifrenizi Giriniz</label>
            </div>

            <input type="hidden" name="token_reg" value="<?php echo Token::generate("register"); ?>">

            <button type="submit"  name="register" >Kayıt</button>
            <a href="../Login/login.php" class="icon">
            <ion-icon name="home-outline"></ion-icon>
        </form>
    </div>

</section>


    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>


</html>
