<?php
require_once "C:/xampp/htdocs/chat/vendor/autoload.php";
require_once "C:/xampp/htdocs/chat/core/init.php";

use classes\{DB, Config, Validation, Common, Session, Token, Hash, Redirect};
use models\User;

// Kullanıcı zaten giriş yapmışsa index.php'ye yönlendirilir
if ($user->getPropertyValue("isLoggedIn")) {
    Redirect::to("../index.php");
}

$validate = new Validation();
$reg_success_message = '';
$login_failure_message = '';

// Eğer form gönderilmişse
if (isset($_POST["login"])) {
    // CSRF Token kontrolü
    if (Token::check(Common::getInput($_POST, "token_log"), "login")) {
        // Giriş için doğrulama kuralları
        $validate->check($_POST, array(
            "email-or-username" => array(
                "name" => "Email or username",
                "required" => true,
                "max" => 255,
                "min" => 6,
                "email-or-username" => true
            ),
            "password" => array(
                "name" => "Password",
                "required" => true,
                "strength" => true // Şifre kontrolü
            )
        ));

        if ($validate->passed()) {
            // Giriş işlemi
            $remember = isset($_POST["remember"]) ? true : false;
            $log = $user->login(Common::getInput($_POST, "email-or-username"), Common::getInput($_POST, "password"), $remember);

            if ($log) {
                // Kullanıcının user_type değerini al
                $db = DB::getInstance();
                $userId = $user->getPropertyValue("id");
                $result = $db->query("SELECT user_type FROM user_info WHERE id = ?", array($userId));

               if ($result && $result->count()) {
                    $results = $result->results(); // Sonuçları al
                    $userType = (int)$results[0]->user_type; // İlk sonucu al
                    if ($userType === 2) {
                        Redirect::to("../admin.php");
                    } else {
                        Redirect::to("../index.php");
                    }
                } else {
                    $login_failure_message = "Kullanıcı bilgisi alınamadı!";
                }
            } else {
                $login_failure_message = "Email veya şifre hatalı!";
            }
        } else {
            // Doğrulama hatalarını yakala
            $login_failure_message = $validate->errors()[0];
        }
    } else {
        $login_failure_message = "Geçersiz CSRF Token!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEW WORLD-GİRİŞ</title>
    <link rel="stylesheet" href="../public/css/giris.css">
    <link rel='shortcut icon' type='image/x-icon' href='../public/assets/images/favicons/favicon.png' />
</head>
<body>
    
    <section>
        <div class="login-box">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" autocomplete="off" class="flex-form" id="login-form">
                <h2>GİRİŞ</h2>

                <!-- Eğer hata varsa göster -->
                <?php if (!empty($login_failure_message)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($login_failure_message); ?></div>
                <?php endif; ?>

                <div class="input-box">
                    <span class="icon"><ion-icon name="mail"></ion-icon></span>
                    <input type="text" name="email-or-username" id="username-or-email" value="<?php echo htmlspecialchars(Common::getInput($_POST, 'email-or-username')); ?>" autocomplete="off" placeholder="Kullanıcı Adı Veya Email" required>
                    <label>Kullanıcı Adı Veya Email Giriniz</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                    <input type="password" name="password" id="password" autocomplete="off" placeholder="Şifre" required>
                    <label>Şifre Giriniz</label>
                </div>
                
                <div class="remember-forget">
                    <label><input type="checkbox" name="remember"> Beni Hatırla</label>
                    <a href="<?php echo Config::get("root/path"); ?>login/passwordRecover.php">Şifremi Unuttum?</a>
                </div>
                
                <input type="hidden" name="token_log" value="<?php echo Token::generate("login"); ?>">
                
                <button type="submit" name="login" value="Login">Giriş</button>
                <div class="register-link">
                    <p>Hesabın Yok Mu? <a href="../Login/signing.php">Kayıt Ol</a></p>
                </div>
            </form>
        </div>
    </section>
    
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
