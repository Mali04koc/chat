<?php

use classes\{Cookie, DB, Config, Session};
use models\User;
//kullanacağımız sınıfları ve modelleri include ediyoruz

// kullanıcı bilgilerini tutmak için session başlamadıysa başlatıyoruz
if (session_status() == PHP_SESSION_NONE) {
    // Session ayarlarını yapılandır
    ini_set('session.cookie_lifetime', 86400); // 24 saat
    ini_set('session.gc_maxlifetime', 86400); // 24 saat

    session_start();

}

$GLOBALS["config"] = array(
    "mysql" => array(
        'host'=>'localhost',
        'username'=>'root',
        'password'=>'',
        'db'=>'chat'
    ),
    "remember"=> array(
        'cookie_name'=>'hash',
        'cookie_expiry'=>604800
    ),
    "session"=>array(
        'session_name'=>'user',
        "token_name" => "token",
        "tokens"=>array(
            "register"=>"register",
            "login"=>"login",
            "reset-pasword"=>"reset-pasword",
            "saveEdits"=>"saveEdits",
            "share-post"=>"share-post",
            "logout"=>"logout"
        )
    ),
    "root"=> array(
        'path'=>'http://localhost/chat/',
        'project_name'=>"chat"
    ),
    'mailgun' => [
        'api_key' => 'dbe9d26c33233ca8c080f12f4dd9f76f-e71583bb-20998d10',
        'domain' => 'sandbox6182845ee4934bc7a5a6bd859a5e4b4a.mailgun.org',
        'sender' => 'NEW WORLD <postmaster@sandbox6182845ee4934bc7a5a6bd859a5e4b4a.mailgun.org>'
    ]
);

/*
 Yukarıda genel config ismini verdiğimiz ayarları tüm global değişkenler üzerinde yaptık.

 1-Database ayarları yapıldı
 2-Beni Hatırla cookie ayarları yapıldı.Cookie name değişkenlerine hash dedik ve 604800 saniye yani 7 gün süre verdik
 3-Session ayarları yapıldı. Session name değişkenine user dedik ve token name değişkenine token dedik.
 4-Root ayarları yapıldı. Projenin pathi belli oldu
*/
$root = Config::get("root/path");
$proj_name = Config::get("root/project_name");

// Confing classındaki statik get fonksiyonu ile path ve project_name değişkenlerini aldık

$user = new User();

if(Cookie::exists(Config::get("remember/cookie_name")) && !Session::exists(Config::get("session/session_name"))) {
    $hash = Cookie::get(Config::get("remember/cookie_name"));
    $res = DB::getInstance()->query("SELECT * FROM users_session WHERE hash = ?", array($hash));

    if($res->count()) {

           
        $user->fetchUser("id", $res->results()[0]->user_id);
        $user->login($user->getPropertyValue("username"),$user->getPropertyValue("password"),true);
         

        }
}

// admin panelinde kullanıcı aktif mi yoksa değil mi olduğunu görmek için anlık olarak aktiflik durumunu güncelliyoruz
if($user->getPropertyValue("isLoggedIn")) {
    $user->update_active();
}

if (isset($user) && $user->getPropertyValue("isLoggedIn")) {
    $db = DB::getInstance();
    $db->query(
        "UPDATE user_info SET last_active_update = ? WHERE id = ?",
        [date('Y-m-d H:i:s'), $user->getPropertyValue('id')]
    );
}

/*

!!!BENİ HATIRLANIN OLAYI NORMALDE LOGİN YAPMADAN urlye bunu yazıp http://127.0.0.1/CHAT/index.php
giriş yapamayız ama beni hatırla butonuna bastığımızda cookie oluşturuluyor ve bu cookie bizim girişimizi
tutuyor bu sayede http://127.0.0.1/CHAT/ ile direkt giriş yapabiliyoruz.


*/ 
/*

BENİ HATIRLA ÖZELLİĞİ AŞAĞI GİBİ ÇALIŞIYOR:

1-Önce tabbi user modelimizden bir user nesnesi oluşturuyoruz
2-Cookie::exists fonksiyonu ile cookie var mı diye kontrol ediyoruz
3-Cookie var ise session var mı diye kontrol ediyoruz.Cookiemiz var ve session yoksa o zaman if
içine giriyoruz,session zaten olmamalı eğer kullanıcı oturumu açtıysa neden beni hatırla özelliğine 
gerek var ki
4-Cookie::get fonksiyonu ile cookie değerini alıyoruz ve hash değişkenine atıyoruz
5-DB::getInstance()->query fonksiyonu ile users_session tablosundan hash değeri ile sorgulama yapıyoruz
Böyle bir kullanıcı var mı diye kontrol ediyoruz
6-Eğer böyle bir kullanıcı varsa o zaman user nesnesinin fetchUser fonksiyonu ile id ve user_id değerini alıyoruz
7-User nesnesinin login fonksiyonu ile kullanıcıyı giriş yaptırıyoruz
8-isLoggedIn true ise giriş yapmış demektir o zaman update_active fonksiyonu ile kullanıcının aktiflik durumunu güncelliyoruz

*/
