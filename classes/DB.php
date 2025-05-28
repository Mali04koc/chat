<?php

namespace classes;
/* 
    $users = DB::getInstance()->query("SELECT * FROM users");
   DB::getInstance()->get("user", array('username', '=', 'alik.04'))amaç bu hale getirmek

    
   PDO (PHP Data Objects), PHP'nin veritabanı işlemleri için sunduğu bir veritabanı erişim katmanıdır.

        PDO, birçok farklı veritabanı türüyle çalışabilir: MySQL, SQLite, PostgreSQL vb.

        Avantajları:

        Veritabanı türünden bağımsız çalışır.

        Hazırlanmış ifadeler (prepared statements) destekler, böylece SQL enjeksiyonlarına karşı güvenlidir.

        Esnek bir hata yönetimi sunar.

        Bizde _pdo private tanımlandı ve sadece functiondan getter ile alabiliyoruz ekstra güvenlik


*/


class DB {
    
    private static $_instance = null;
    private $_pdo,
            $_error = false,
            $_query,
            $_results,
            $_count = 0;



    // $db = new DB(); // Hata verir, çünkü db sınıfı constructorı private.Sadece getInstance() metodu ile erişilebilir.

    private function __construct() {
        $this->_pdo = new \PDO("mysql:host=" . Config::get('mysql/host') . ";dbname=" . Config::get('mysql/db'), Config::get('mysql/username'), Config::get('mysql/password'));
    }

    public static function getInstance() {
        
        // Eğer DB sınıfının bir örneği yoksa, yeni bir örnek oluştur    
        if(!isset(self::$_instance)) {
            self::$_instance = new DB();
        }

        return self::$_instance;
    }
    
    

    /*
    $sql: SQL sorgusunu içeren bir stringdir. Örneğin:
    "SELECT * FROM user_info WHERE username = ? AND password = ?"

    $params: Sorguda kullanılan ? yer tutucularının yerine geçecek değerlerin bir dizisidir. Örneğin:
    array('alik.04', '123456')
    */

    public function query($sql, $params = array()) {
        /*  Önceki sorguların hatalarını sıfırlar. Böylece her sorgu başlangıcında hata durumu temizlenir. */
        $this->_error = false;

        // prepare($sql): PDO'nun bir metodudur ve SQL sorgusunu hazırlar.
        if($this->_query = $this->_pdo->prepare($sql)) {
            // en azından 1 tane params varsa , yani ? sorgu varsa 
            if(count($params)) {
                $count = 1;
                foreach($params as $param) {

                    /* 

                    if query = "SELECT * FROM user_info WHERE username = ? AND password = ?;
                                                                         ^                ^
                                                                   count:1          count:2

                    params dizisindeki her bir paramı countta yerine koyuyor bindValue ile 
                    $count: SQL sorgusundaki ? yer tutucusunun sırasını belirtir. 1'den başlar (1-indexed).

                    $param: Yer tutucuya atanacak değerdir.                                               
                    */
                    $this->_query->bindValue($count, $param);
                    $count++;
                }
            }

            /*
                execute(): Hazırlanmış sorguyu çalıştırır.

                Eğer başarılı olursa:

                $this->_results: Sonuçları alır ve nesne tabanlı bir yapı olarak döndürür (PDO::FETCH_OBJ).

                $this->_count: Etkilenen satır sayısını saklar (rowCount()).

                Eğer başarısız olursa:

                $this->_error: Hata durumunu true yapar.

            
            */ 
            if($this->_query->execute()) {
                $this->_results = $this->_query->fetchAll(\PDO::FETCH_OBJ);
                $this->_count = $this->_query->rowCount();
            } else {
                $this->_error = true;
            }
        }

        
        return $this;
    }

    public function pdo() {
        return $this->_pdo;
    }

    public function error() {
        return $this->_error;
    }

    public function results() {
        return $this->_results;
    }

    public function count() {
        return $this->_count;
    }

     public function delete($table, $conditions = array()) {
        /*
            Kullanım: 
            DB::getInstance()->delete('user_info', ['id', '=', '5']);
        */

        $this->_error = false;

        if (count($conditions) === 3) {
            $field    = $conditions[0];
            $operator = $conditions[1];
            $value    = $conditions[2];

            $allowedOperators = ['=', '>', '<', '>=', '<=', '<>'];
            if (in_array($operator, $allowedOperators)) {
                $sql = "DELETE FROM {$table} WHERE {$field} {$operator} ?";
                if ($this->query($sql, [$value])->error()) {
                    $this->_error = true;
                }
            }
        } else {
            $this->_error = true;
        }

        return !$this->_error;
    }
}


