<?php

namespace models;

use classes\{DB};
use models\User;

class Follow {
    private $db,
    $id,
    $follower,
    $followed;
    
    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function get_property($propertyName) {
        return $this->$propertyName;
    }
    
    public function set_property($propertyName, $propertyValue) {
        $this->$propertyName = $propertyValue;
    }

    public function set_data($data = array()) {
        $this->follower = $data["follower"];
        $this->followed = $data["followed"];
    }

    // Takip ilişkisi var mı diye bakıyor , eğer takip ilişkisi varsa count>0 olur zaten 
    // eşleşen kişilerin idsi felan sınıf id değişkenine atanıyor 
    public function fetch_follow() {
        $this->db->query("SELECT * FROM user_follow WHERE follower_id = ? AND followed_id = ?", 
        array(
            $this->follower,
            $this->followed
        ));

        if($this->db->count() > 0) {
            $fetched_follow = $this->db->results()[0];

            $this->id = $fetched_follow->id;
            $this->follower = $fetched_follow->follower_id;
            $this->followed = $fetched_follow->followed_id;

            return true;
        }

        return false;
    }

    //Belirtilen kullanıcıyı takip eden kullanıcıların listesini döner.
    public static function get_user_followers($id) {
        DB::getInstance()->query("SELECT * FROM user_follow WHERE followed_id = ?", array($id));
        // followers arrayi oluşturduk listeyi tutacağımız yer
        $followers = array();

        if(DB::getInstance()->count() > 0) {
            $fetched_followers = DB::getInstance()->results();

            foreach($fetched_followers as $fetched_follower) {

                // kullanıcıların bilgilerini user nesnesinden aldık
                $follower = new User();
                $follower->fetchUser("id", $fetched_follower->followed_id);

                //bilgileri diziye ekledik
                $followers[] = $follower;
            }
        }

        return $followers;
    }


    //Kullanıcının takip ettiği ile takipçi sayılarını buluyor.
    public static function get_user_followers_number($id) {
        DB::getInstance()->query("SELECT * FROM user_follow WHERE followed_id = ?", array($id));

        return DB::getInstance()->count();
    }

    public static function get_followed_users_number($id) {
        DB::getInstance()->query("SELECT * FROM user_follow WHERE follower_id = ?", array($id));

        return DB::getInstance()->count();
    }


    // get_follower_users ile aynı mantık

    public static function get_followed_users($id) {
        DB::getInstance()->query("SELECT * FROM user_follow WHERE follower_id = ?", array($id));

        
        $followed_users = array();

        if(DB::getInstance()->count() > 0) {
            $fetched_followed_users = DB::getInstance()->results();

            foreach($fetched_followed_users as $fetched_followed_user) {
                $followed_user = new User();
                $followed_user->fetchUser("id", $fetched_followed_user->followed_id);

                $followed_users[] = $followed_user;
            }
        }

        return $followed_users;
    }


    // veritabanına bir takip ilişkisi ekler.add butonu 
    public function add() {
        $this->db->query("INSERT INTO user_follow 
        (follower_id, followed_id) 
        VALUES (?, ?)", array(
            $this->follower,
            $this->followed
        ));

        return $this->db->error() == false ? true : false;
    }
   
    // veritabanındaki takip ilişkisini siler
    public function delete() {
        $this->db->query("DELETE FROM user_follow WHERE id = ?", array($this->id));

        return ($this->db->error()) ? false : true;
    }
  
    // takip isteği var mı diye bakar 
    public static function follow_exists($follower, $followed) {
        DB::getInstance()->query("SELECT * FROM user_follow WHERE follower_id = ? AND followed_id = ?", 
        array(
            $follower,
            $followed
        ));

        return DB::getInstance()->count() > 0 ? true : false;
    }
}
