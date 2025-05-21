<?php 

namespace classes;

class AuthMiddleware {
    const USER_TYPE_NORMAL = 1;
    const USER_TYPE_ADMIN = 2;
    
    private static $_instance = null;
    private $_db;
    private $_user;
    
    private function __construct() {
        $this->_db = DB::getInstance();
        $this->_user = new \models\User();
    }
    
    public static function getInstance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new AuthMiddleware();
        }
        return self::$_instance;
    }
    
    public function handle() {
        if (!$this->_user->isLoggedIn()) {
            Session::flash('danger', 'Önce giriş yapmalısınız!');
            Redirect::to('../login.php');
            return;
        }
        
        $userTypeId = $this->getUserTypeId();
        $currentPage = basename($_SERVER['PHP_SELF']);

        if ($userTypeId === self::USER_TYPE_ADMIN) {
            if ($currentPage !== 'admin.php' && !$this->isAdminPage($currentPage)) {
                Redirect::to('../admin.php');
            }
        } else {
            if ($currentPage === 'admin.php' || $this->isAdminPage($currentPage)) {
                Session::flash('danger', 'Bu sayfaya erişim yetkiniz yok!');
                Redirect::to('../index.php');
            }
        }
    }
    
    private function getUserTypeId() {
        $userId = $this->_user->getPropertyValue("id");

        $result = $this->_db->query(
            "SELECT user_type FROM user_info WHERE id = ?",
            array($userId)
        );

        if (!$result) {
            return self::USER_TYPE_NORMAL;
        }

        $results = $result->results();

        if ($results && count($results)) {
            return (int)$results[0]->user_type;
        }

        return self::USER_TYPE_NORMAL;
    }
    
    public function adminOnly() {
        $userTypeId = $this->getUserTypeId();
        
        if ($userTypeId !== self::USER_TYPE_ADMIN) {
            Session::flash('danger', 'Bu sayfa sadece yöneticiler içindir!');
            Redirect::to('../index.php');
        }
    }
    
    private function isAdminPage($page) {
        $adminPages = ['admin.php'];
        return in_array($page, $adminPages);
    }
}
