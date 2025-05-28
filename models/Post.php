<?php

namespace models;
use classes\{DB, Config, Common};

class Post {
    private $db,
    $post_id,
    $post_owner,
    $post_visibility=1,
    $post_place=1,
    $post_date='',
    $post_edit_date='',
    $text_content='',
    $picture_media='',
    $video_media='',
    $is_shared=0,
    $post_shared_id=null;

    public function __construct() {
        $this->db = DB::getInstance();
        $this->post_date = date("Y/m/d H:i:s");
    }

    public function get_property($propertyName) {
        return $this->$propertyName;
    }
    
    public function set_property($propertyName, $propertyValue) {
        $this->$propertyName = $propertyValue;
    }

    public function setData($data = array()) {
        $this->post_owner = $data["post_owner"];
        $this->post_visibility = isset($data["post_visibility"]) ? $data["post_visibility"] : 1;
        $this->post_place = isset($data["post_place"]) ? $data["post_place"] : 1;
        $this->post_date = isset($data["post_date"]) ? $data["post_date"] : $this->post_date;
        $this->text_content = $data["text_content"];
        $this->picture_media = $data["picture_media"];
        $this->video_media = $data["video_media"];
        $this->is_shared = isset($data["is_shared"]) ? $data["is_shared"] : 0;
        $this->post_shared_id = isset($data["post_shared_id"]) ? $data["post_shared_id"] : null;
    }

    public function add() {
        $this->db->query("INSERT INTO post 
        (post_owner, post_visibility, post_place, post_date, text_content, picture_media, video_media, is_shared, post_shared_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", array(
            $this->post_owner,
            $this->post_visibility,
            $this->post_place,
            $this->post_date,
            $this->text_content,
            $this->picture_media,
            $this->video_media,
            $this->is_shared,
            $this->post_shared_id
        ));

        $res = $this->db->error();
        return $res == false ? true : false;
    }

    public static function exists($value, $field_name='id') {
        DB::getInstance()->query("SELECT * FROM post WHERE $field_name = $value");

        return DB::getInstance()->count() > 0 ? true : false;
    }

    // kullanıcın idsini alıp  o kullanıcıyı fetchler ve değerlerini sınıf değişkenlerine atar 
    public function fetchPost($id) {
        $this->db->query("SELECT * FROM post WHERE id = ?", array($id));

        if($this->db->count() > 0) {
            $fetchedPost = $this->db->results()[0];

            $this->post_id = $fetchedPost->id;
            $this->post_owner = $fetchedPost->post_owner;
            $this->post_visibility = $fetchedPost->post_visibility;
            $this->post_place = $fetchedPost->post_place;
            $this->post_date = $fetchedPost->post_date;
            $this->text_content = $fetchedPost->text_content;
            $this->picture_media = $fetchedPost->picture_media;
            $this->video_media = $fetchedPost->video_media;
            $this->is_shared = $fetchedPost->is_shared;
            $this->post_shared_id = $fetchedPost->post_shared_id;

            return true;
        }

        return false;
    }

    public static function get_last_post() {
        DB::getInstance()->query("SELECT * FROM post ORDER BY id DESC LIMIT 1");

        $res = DB::getInstance()->results();
        return DB::getInstance()->results()[0];
    }

    //Burada kullanıcının takip ettiklerinin ve arkadaşlarının postlarını görmemizi sağlıyor
    public static function fetch_journal_posts($user_id) {
        // Gösterilcek postları saklayan array
        $fetched_users = array();

        // İlk önce UserRelation modelinden arkadaşlarının idlerini alıyoruz
        $friends = UserRelation::get_friends($user_id);

        // 2.olarak takip ettiklerinin idlerini alıyoruz
        $followed_users = Follow::get_followed_users($user_id);
        
        $fetched_users = array_merge($friends, $followed_users);
        // 2 arrayi birleştiriyoruz 
        $fetched_users = Common::unique_multidim_array($fetched_users, "id");
        // Tekrar eden kullanıcı varsa hem arkadaşı hem takip ettiği onun bir satırı siliyoruz, uniq yapıyoruz.
        $posts = array();

        foreach($fetched_users as $friend) {
            $poster_id = $friend->getPropertyValue("id");

            DB::getInstance()->query("SELECT * FROM post WHERE post_owner = ?", array($poster_id));
    
            if(DB::getInstance()->count() > 0) {
                $fetched_posts = DB::getInstance()->results();
    
                foreach($fetched_posts as $post) {
                    // Post nesnesi üzerinden her bir id nin post verilerini alıyoruz
                    $f_post = new Post();
    
                    $f_post->post_id = $post->id;
                    $f_post->post_owner = $post->post_owner;
                    $f_post->post_visibility = $post->post_visibility;
                    $f_post->post_place = $post->post_place;
                    $f_post->post_date = $post->post_date;
                    $f_post->text_content = $post->text_content;
                    $f_post->picture_media = $post->picture_media;
                    $f_post->video_media = $post->video_media;
                    $f_post->is_shared = $post->is_shared;
                    $f_post->post_shared_id = $post->post_shared_id;
    
                    $posts[] = $f_post;
                }
            }
        }

        return $posts;
    }

    public static function get($field_name, $field_value) {
        DB::getInstance()->query("SELECT * FROM post WHERE $field_name = ?", array($field_value));

        //bilgileri atıcağımız array
        $posts = array();

        if(DB::getInstance()->count() > 0) {
            $fetched_posts = DB::getInstance()->results();

            foreach($fetched_posts as $post) {
                //yeni Post nesnesi oluşturup o nesnenin özelliklerini arraye atıyoruz
                $f_post = new Post();

                $f_post->post_id = $post->id;
                $f_post->post_owner = $post->post_owner;
                $f_post->post_visibility = $post->post_visibility;
                $f_post->post_place = $post->post_place;
                $f_post->post_date = $post->post_date;
                $f_post->text_content = $post->text_content;
                $f_post->picture_media = $post->picture_media;
                $f_post->video_media = $post->video_media;
                $f_post->is_shared = $post->is_shared;
                $f_post->post_shared_id = $post->post_shared_id;

                $posts[] = $f_post;
            }
        }

        return $posts;
    }

    public static function get_post_owner($post_id) {
        DB::getInstance()->query("SELECT * FROM post WHERE id = ?", array($post_id));

        if(DB::getInstance()->count() > 0) {
            return DB::getInstance()->results()[0];
        }

        return false;
    }

    public static function get_posts_number($user_id) {
        DB::getInstance()->query("SELECT * FROM post WHERE post_owner = ?", array($user_id));

        return DB::getInstance()->count();
    }

    public static function get_post_share_numbers($post_id) {
        DB::getInstance()->query("SELECT * FROM post WHERE post_shared_id = ?", array($post_id));

        return DB::getInstance()->count();
    }

    public function update() {
        $this->db->query("UPDATE post 
        SET post_owner=?, post_visibility=?, post_place=?, post_date=?, 
        text_content=?, picture_media=?, video_media=?, is_shared=?, post_shared_id=? WHERE id=?"
        , array(
            $this->post_owner,
            $this->post_visibility,
            $this->post_place,
            $this->post_date,
            $this->text_content,
            $this->picture_media,
            $this->video_media,
            $this->is_shared,
            $this->post_shared_id,
            $this->post_id
        ));

        return ($this->db->error()) ? false : true;
    }

    public function delete() {
        $this->db->query('DELETE FROM post WHERE id = ?', array($this->post_id));

        return $this->db->error() == false ? true : false;
    }

    public function toString() {
        return 'Post with id: ' . $this->post_id . " and owner of id: " . $this->post_owner . " published at: " . $this->post_date . "<br>";
    }
}
