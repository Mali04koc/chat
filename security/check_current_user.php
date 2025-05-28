<?php

/*

"Remember Me" Özelliği ve İstemci Tarafı Kaynağı (Hash):

"Remember Me" özelliği, kullanıcı giriş yaptıktan sonra oturumunun belirli bir süre boyunca hatırlanmasını sağlar.

Bu genellikle istemci tarafında saklanan bir hash ile yapılır. Bu hash, kullanıcıdan bağımsız olarak istemcide saklanır (örneğin, tarayıcıdaki çerezlerde).

Sunucu Tarafındaki Kullanıcı Durumu:

Sunucu tarafında oturum bilgileri veya kullanıcıya ait bir "aktif kullanıcı" durumu saklanır.

Kullanıcı oturum açarken veya bir işlem yaparken, sunucudaki bu bilgiler doğrulanır.

Sorun:

Bu kontrol hem istemcide saklanan bir kaynağa (hash) hem de sunucuda saklanan bir kaynağa (mevcut kullanıcı) dayalıdır.

RESTful API'ler stateless (durumsuz) olduğundan, sunucu tarafında bir "durum" saklamaz. Yani, istemci ve sunucu arasında "hatırlanan" bir bağlantı olmamalıdır.

RESTful API'ler, her isteği birbirinden bağımsız olarak işler ve bu tür bir kontrol REST prensiplerini ihlal eder.

Sonuç:

Bu kontrolün API'nin bir parçası olmaması gerektiği, çünkü bu kontrolün işleyişi RESTful API'lerin temel durumsuzluk ilkesiyle çelişiyor.


*/

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once "../vendor/autoload.php";
require_once "../core/init.php";

use models\User;

require_once "../functions/sanitize_id.php";

$id = isset($_POST["current_user_id"]) ? $_POST["current_user_id"] : false;

if($id = sanitize_id($id)) {
    if(User::user_exists("id", $id) && $user->getPropertyValue("id") == $id) {
        echo json_encode(1);
    } else {
        echo json_encode(0);
    }
} else {
    echo json_encode(0);
}
