<?php

    require_once "../vendor/autoload.php";
    require_once "../core/init.php";

    use classes\{DB, Config, Validation, Common, Session, Token, Hash, Redirect};
    use models\User;

    // First we check if the user put his email and confirmatin code sent succesfully, if there's something wrong we redirect
    if(!Session::exists("email-confirmation")) {
        Redirect::to("login.php");
    }

    $validate = new Validation();

    if(isset($_POST["confirm"])) {
        if(Token::check(Common::getInput($_POST, "token_code_conf"), "reset-pasword")) {
            $validate->check($_POST, array(
                "code"=>array(
                    "name"=>"Confirmation code",
                    "required"=>true,
                    "max"=>16
                )
            ));

            if($validate->passed()) {
                if(Session::get("email-confirmation") == Common::getInput($_POST, "code")) {
                   // Here the confirmation code is good
                    Session::delete("email-confirmation");
                    Session::put("password-change-allow", "allowed");
                    Redirect::to("changepassword.php");
                } else {
                    $validate->addError("Kodunuz geçersizdir. Lütfen tekrar deneyin.");
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
    <title>KODU ONAYLA</title>
    <link rel='shortcut icon' type='image/x-icon' href='../public/assets/images/favicons/favicon.png' />
    <link rel="stylesheet" href="../public/css/giris.css">
</head>

<body>
    <section>
        <div class="login-box">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" class="flex-column">
                
            
             <h2>KODU ONAYLAYIN</h2>
            
                <?php if ($validate->errors()): ?>
                    <div class="error-message">
                        <?php echo implode('<br>', $validate->errors()); ?>
                    </div>
                <?php endif; ?>
                
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail"></ion-icon></span>
                    <input type="text" name="code" autocomplete="off" placeholder="Kod" required>
                    <label>Kodunuzu Girin</label>
                </div>
                <input type="hidden" name="token_code_conf" value="<?php echo Token::generate("reset-pasword"); ?>"> 
                <button type="submit" value="confirm" name="confirm">Onayla</button>
            </form>
        </div>
    </section>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>