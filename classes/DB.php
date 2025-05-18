<?php

namespace classes;

class DB {
    // Singleton Instance
    private static $_instance = null;
    private $_pdo, $_error = false, $_query, $_results, $_count = 0;

    private function __construct() {
        try {
            $this->_pdo = new \PDO(
                "mysql:host=" . Config::get('mysql/host') . ";dbname=" . Config::get('mysql/db'),
                Config::get('mysql/username'),
                Config::get('mysql/password'),
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
                    \PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (\PDOException $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new DB();
        }
        return self::$_instance;
    }

    public function query($sql, $params = []) {
        $this->_error = false;
        try {
            $this->_query = $this->_pdo->prepare($sql);

            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $this->_query->bindValue(is_int($key) ? $key + 1 : $key, $value);
                }
            }

            $this->_query->execute();
            $this->_results = $this->_query->fetchAll();
            $this->_count = $this->_query->rowCount();
        } catch (\PDOException $e) {
            $this->_error = true;
            echo "Query Error: " . $e->getMessage();
        }

        return $this;
    }

    public function error() { return $this->_error; }
    public function results() { return $this->_results; }
    public function first() { return $this->_results ? $this->_results[0] : null; }
    public function count() { return $this->_count; }
}
