<?php 

namespace classes;

class AuthMiddleware {
    private $_db;
    private $_user;

    // Type 1 (normal user) allowed pages
    private $type1Pages = [
        'index.php',
        'chat.php',
        'search.php',
        'profile.php',
        'settings.php',
        'settings-account.php'
    ];

    // Type 2 (admin) allowed pages
    private $type2Pages = [
        'admin.php',
        'admin-settings.php',
        'admin-settings-account.php',
        'admin-search.php',
        'admin-user.php',
        'admin-posts.php',
        'admin-activity.php'
    ];

    public function __construct() {
        $this->_db = \classes\DB::getInstance();
        $this->_user = new \models\User();
    }

    public function handle() {
        // Check if user is logged in
        if (!$this->_user->isLoggedIn()) {
            // Login sayfalarında değilsek yönlendir
            if (!strpos($_SERVER['REQUEST_URI'], 'login.php') && 
                !strpos($_SERVER['REQUEST_URI'], 'log-header.php') && 
                !strpos($_SERVER['REQUEST_URI'], 'signing.php')) {
                \classes\Session::flash('danger', 'Önce giriş yapmalısınız!');
                \classes\Redirect::to('login/login.php');
                exit;
            }
            return false;
        }

        $userType = $this->getUserType();
        $currentPage = basename($_SERVER['PHP_SELF']);

        // Admin kullanıcılar için kontrol
        if ($userType === 2) {
            // Admin sadece admin sayfalarına erişebilir
            if (!in_array($currentPage, $this->type2Pages) && 
                !strpos($_SERVER['REQUEST_URI'], 'login.php') && 
                !strpos($_SERVER['REQUEST_URI'], 'log-header.php') && 
                !strpos($_SERVER['REQUEST_URI'], 'signing.php')) {
                \classes\Session::flash('danger', 'Bu sayfaya erişim izniniz yok!');
                \classes\Redirect::to('admin.php');
                return false;
            }
        } else {
            // Normal kullanıcılar admin sayfalarına erişemez
            if (in_array($currentPage, $this->type2Pages)) {
                \classes\Session::flash('danger', 'Bu sayfaya erişim izniniz yok!');
                \classes\Redirect::to('index.php');
                return false;
            }
        }

        return true;
    }

    private function getUserType() {
        if ($this->_user->isLoggedIn()) {
            return $this->_user->getPropertyValue("user_type");
        }
        return null;
    }
}
