<?php

    require_once "../vendor/autoload.php";
    require_once "../core/init.php";

    use classes\{DB, Config, Validation, Common, Session, Token, Hash, Redirect};
    use models\User;
    use Mailgun\Mailgun;

    // User sınıfı init.php'de zaten oluşturulmuş
    $validate = new Validation();



    if(isset($_POST["send"])) {
        if(Token::check(Common::getInput($_POST, "token_conf_send"), "reset-pasword")) {
            $validate->check($_POST, array(
                "email"=>array(
                    "name"=>"Email",
                    "required"=>true,
                    "max"=>255,
                    "min"=>6,
                    "email"=>true
                )
            ));

            if($validate->passed()) {
                $email = Common::getInput($_POST, "email");
                $exists = $user->fetchUser("email", $email);
                
                if($exists) {
                    $conf_code = substr(Hash::unique(), 16, 16);
                    
                    // API anahtarını ve domain'i güvenli bir şekilde saklayın
                    // Bu değerleri bir config dosyasında tutmanız daha güvenli olacaktır
                    $mailgun_api_key =  Config::get('mailgun/api_key');
                    $mailgun_domain = Config::get('mailgun/domain');
                    
                    try {
                        // Güncel Mailgun SDK kullanımı
                        $mgClient = Mailgun::create($mailgun_api_key);
                        
                        $result = $mgClient->messages()->send($mailgun_domain, [
                            'from'    => Config::get('mailgun/sender'),
                            'to'      => $user->getPropertyValue("email"),
                            'subject' => "Şifre Sıfırlama - Doğrulama Kodu",
                            'text'    => "Şifre sıfırlama işleminiz için doğrulama kodunuz: " . $conf_code
                        ]);

                        // Doğrulama kodu ve kullanıcı ID'sini session'a kaydet
                        Session::put("email-confirmation", $conf_code);
                        Session::put("u_id", $user->getPropertyValue("id"));
                        
                        // Başarılı gönderimden sonra confirmation sayfasına yönlendir
                        Redirect::to("confirmationcode.php");
                        
                    } catch(\Exception $e) {
                        // Hata detaylarını logla
                        error_log("Mailgun Hatası: " . $e->getMessage());
                        
                        // Kullanıcıya daha anlaşılır bir hata mesajı göster
                        $validate->addError("Doğrulama kodu gönderilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.");
                        
                        // SSL hatası varsa özel bir mesaj göster
                        if(strpos($e->getMessage(), 'SSL') !== false) {
                            $validate->addError("SSL sertifika hatası oluştu. Z_IMPORTANT.txt dosyasındaki [IMPORTANT#5] bölümünü inceleyiniz.");
                        }
                    }
                    
                } else {
                    // Kullanıcı bulunamadı hatası
                    $validate->addError("Bu e-posta adresine sahip bir kullanıcı bulunamadı!");
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
    <title>NEW WORLD-ŞİFREMİ UNUTTUM</title>
    <link rel='shortcut icon' type='image/x-icon' href='../public/assets/images/favicons/favicon.png' />
    <link rel="stylesheet" href="../public/css/giris.css">
</head>

<body>
    <section>
        <div class="login-box">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" class="flex-column">
                <h2>ŞİFREMİ UNUTTUM</h2>
                
                <?php if($validate->errors()): ?>
                    <div class="error-message">
                        <?php foreach($validate->errors() as $error): ?>
                            <p class="error-messages"><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail"></ion-icon></span>
                    <input type="text" name="email" autocomplete="off" placeholder="Email" required>
                    <label>Emailinizi Girin</label>
                </div>
                <input type="hidden" name="token_conf_send" value="<?php echo Token::generate("reset-pasword"); ?>"> 
                <button type="submit" value="send" name="send">Kod Gönder</button>
            </form>
        </div>
    </section>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>