<?php

namespace models;

use classes\{Hash, Config, Session, DB, Cookie};

class User implements \JsonSerializable {
    private $db,
        $sessionName,
        $cookieName,

        $id,
        $username='',
        $email='',
        $password='',
        $salt='',
        $firstname='',
        $lastname='',
        $joined='',
        $user_type=1,
        $bio='',
        $cover='',
        $picture='',
        $private=-1,
        $last_active_update='',

        $isLoggedIn=false;

   // user classımızda veri paylaşımında json formatını kullanacağımızı söyledik ve private değişkenlerimizi yazdık


   // constructor fonksiyonumuz her yeni user nesnesi oluşturulduğunda çalışır
   // db değişkenine DB classından getInstance fonksiyonu ile dönen değeri atadık
   // confingden session name ve cookie name değişkenlerini aldık
   // Eğer session name değişkeni varsa yani kullanıcı giriş yapmışsa true döndürüyoruz.dt değişkeni mesela 13,id sütununda böyle değer var mı diye bakar
    public function __construct() {
        $this->db = DB::getInstance();
        $this->sessionName = Config::get('session/session_name');
        $this->cookieName = Config::get('remember/cookie_name');

        if(Session::exists($this->sessionName)) {
            $dt = Session::get($this->sessionName);
            
            if($this->fetchUser("id", $dt)) {
                $this->isLoggedIn = true;
                // Session'ı yenile
                Session::put($this->sessionName, $dt);
            } else {
                // Session geçersizse temizle
                Session::delete($this->sessionName);
                $this->isLoggedIn = false;
            }
        }
    }

    public function getPropertyValue($propertyName) {
        return $this->$propertyName;
    }
    
    public function setPropertyValue($propertyName, $propertyValue) {
        $this->$propertyName = $propertyValue;
    }

    // Metadata, kullanıcıya özgü anahtar-değer çiftleri (örneğin, "bio: Yazılım mühendisi") olabilir. 
    public function get_metadata($label="") {
        $metadata = array();
        $values = array($this->id);
        $query = "SELECT * FROM user_metadata WHERE `user_id` = ?";

        
        if(!empty($label)) {
            $query .= " AND `label` = ?";
            $values[] = $label;
        }

        $this->db->query($query, $values);

        return $this->db->results();

        /*Öncelikle query ve results fonksiyonları DB classında tanımlı.Label anahtar demek eğer başka bir
        şekilde aramak istiyorsak diye. 
        1-metadata arrayi oluşturduk,values arrayi oluşturduk ve user_id değerini atadık

        2-query değişkenine user_metadata tablosundan user_id değerini atdık buarada diğer kodlarda yaptığımız
        gibi "SELECT * FROM user_metadata WHERE `user_id` = ? , array($this->id) şeklinde yapmadık çünkü
        label boşsa sadece user_id değerini alacağız, eğer label doluysa o zaman label değerini de alacağız"

        3-if ile label boş mu diye baktık,değilse query değişkenine label değerini de ekledik ve values arrayine
        label değerini de ekledik

        4-Son olarak db classındaki query fonksiyonunu çağırdık ve sonuçları döndürdük.

        
         */
    
        }

    public function get_metadata_items_number() {
        $this->db->query("SELECT COUNT(*) as number_of_labels FROM user_metadata WHERE `user_id` = ?", array($this->id));

        
        if(count($this->db->results()) > 0) {
            return $this->db->results()[0]->number_of_labels;
        }
        return array();

        /*
        SQL sorgusu, user_metadata tablosunda belirtilen user_id için toplam metadata sayısını hesaplar.
        Eğer sonuç döndüyse, ilk satırın number_of_labels(toplam metadata) alanını döndürür.
        Aksi halde array() döndürür.
        */

    }

    public function metadata_exists($label) {
        $this->db->query("SELECT COUNT(*) as number_of_labels FROM user_metadata WHERE `label`=?  AND `user_id`=?", array(
            $label,
            $this->id
        ));

        
        if($this->db->results()[0]->number_of_labels != 0) {
            return true;
        }
        return false;

        // number_of_labels boş değilse demek ki metadata exist
    }

    public function add_metadata($label, $content) {
        if($this->get_metadata_items_number() < 6 && $content != "") {
            $this->db->query("INSERT INTO user_metadata (`label`, `content`, `user_id`) values(?, ?, ?);", array(
                $label,
                $content,
                $this->id
            ));

            return true;
        }

        return false;
        // eğer kullanıcının metadata sayısı 6'dan azsa ve content boş değilse value ekle
    }

    public function update_metadata($label, $content) {
        $this->db->query("UPDATE user_metadata SET `content`=? WHERE `label`=? AND `user_id`=?", array(
            $content,
            $label,
            $this->id
        ));

        return $this->db->error() == false ? true : false;

        // hata oluştu false ise yani hata oluşmadıysa 
    }
    // eğer metadata varsa update et yoksa add et
    public function set_metadata($metadata) {
        foreach($metadata as $mdata) {
            if($this->metadata_exists($mdata["label"])) {
                $this->update_metadata($mdata["label"], $mdata["content"]);
            } else {
                $this->add_metadata($mdata["label"], $mdata["content"]);
            }
        }
    }


    // field isimli sütun adımız ve value isimli aramak istediğimiz değer var.
    //Eğer bu değer user_info tablosunda varsa true döner
    public static function user_exists($field, $value) {
        DB::getInstance()->query("SELECT * FROM user_info WHERE $field = ?", array($value));

        if(DB::getInstance()->count() > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function fetchUser($field_name, $field_value) {
        $this->db->query("SELECT * FROM user_info WHERE $field_name = ?", array($field_value));

        // Eğer kullanıcı varsa aramada bir şey çıkmalı (>0) o zaman result dizisinin ilk değerini
        //fetcheduser a atar.Muhtemelem o değer id dir.Sonra fetcheduserın tüm bilgilerini çeker
        if($this->db->count() > 0) {
            $fetchedUser = $this->db->results()[0];

            $this->id = $fetchedUser->id;
            $this->username = $fetchedUser->username;
            $this->email = $fetchedUser->email;
            $this->password = $fetchedUser->password;
            $this->salt = $fetchedUser->salt;
            $this->firstname = $fetchedUser->firstname;
            $this->lastname = $fetchedUser->lastname;
            $this->joined = $fetchedUser->joined;
            $this->user_type = $fetchedUser->user_type;
            $this->bio = $fetchedUser->bio;
            $this->cover = $fetchedUser->cover;
            $this->picture = $fetchedUser->picture;
            $this->private = $fetchedUser->private;
            $this->last_active_update = $fetchedUser->last_active_update;

            return $this;
        }

        return false;
    }

    public function setData($data = array()) {
        $this->id = $data["id"];
        $this->username = $data["username"];
        $this->email = $data["email"];
        $this->password = $data["password"];
        $this->salt = $data["salt"];
        $this->firstname = $data["firstname"];
        $this->lastname = $data["lastname"];
        $this->joined = isset($data["joined"]) ? $data["joined"] : date("Y/m/d h:i:s");
        $this->user_type = $data["user_type"];
        $this->bio = $data["bio"];
        $this->cover = $data["cover"];
        $this->picture = $data["picture"];
        $this->private = $data["private"];
    }

    /* 

   $data arrayi ile user_info tablosunu güncelliyoruz.
   isset($data["joined"]) ? $data["joined"] : date("Y/m/d h:i:s");
   Eğer joined yani siteye katılma değeri verilmemişse o zaman date fonksiyonu ile
   güncel tarihi atıyoruz.
    */
    public function add() {
        $this->db->query("INSERT INTO user_info 
        (username, email, password, salt, firstname, lastname, joined, user_type) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)", array(
            $this->username,
            $this->email,
            $this->password,
            $this->salt,
            $this->firstname,
            $this->lastname,
            $this->joined,
            $this->user_type
        ));

        // Eğer hata yoksa true döner(Kontrol mekanizması)
        return $this->db->error() == false ? true : false;
    }

    // Burada update fonksiyonu ile user_info tablosunu güncelliyoruz
    public function update() {
        $this->db->query("UPDATE user_info SET username=?, email=?, password=?, salt=?, firstname=?, lastname=?, joined=?, user_type=?, bio=?, cover=?, picture=?, private=? WHERE id=?",
        array(
            $this->username,
            $this->email,
            $this->password,
            $this->salt,
            $this->firstname,
            $this->lastname,
            $this->joined,
            $this->user_type,
            $this->bio,
            $this->cover,
            $this->picture,
            $this->private,
            $this->id
        ));

        return ($this->db->error()) ? false : true;
    }

    // Sadece belli bir özelliği güncellemek için kullanılır
    public function update_property($property, $new_value) {
        $this->db->query("UPDATE user_info SET $property=? WHERE id=?",
        array(
            $new_value,
            $this->id
        ));

        return ($this->db->error()) ? false : true;
    }

    // Burada user_info tablosundaki kullanıcının id'sine göre silme işlemi yapıyoruz
    public function delete() {
        $this->db->query("DELETE FROM user_info WHERE id = ?", array($this->id));

        return ($this->db->error()) ? false : true;
    }
    
    // Arama mantığı-- Eğer arama kutusuna bir şey yazılmadıysa boş array döner
    public static function search($keyword) {
        if(empty($keyword)) {
            return array();
        }

        $keywords = mb_strtolower($keyword, 'UTF-8');
        $keywords = htmlspecialchars($keywords);
        $keywords = trim($keywords);

<<<<<<< Updated upstream
=======
        /*
        strtolower fonksiyonu ile arama kutusuna yazılan kelimeleri küçük harfe çeviriyoruz.mb_strtolower
        fonksiyonu ise Türkçe karakterleri de küçük harfe çeviriyor.

        htmlspecialchars fonksiyonu ile HTML özel karakterleri (<, >, ", ', &) güvenli hale getirir.

        trim fonksiyonu ile başındaki ve sonundaki boşlukları temizliyoruz.
        */

>>>>>>> Stashed changes
        $keywords = explode(' ', $keyword);

        if($keywords[0] == '') {
            $query = "";
        } else {
            $query = "SELECT * FROM user_info WHERE user_type != 2 "; // Exclude admin users (user_type = 2)
            for($i=0;$i<count($keywords);$i++) {
                $k = $keywords[$i];
                if($i==0)
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
                    $query .= "WHERE (username LIKE '%$k%' OR firstname LIKE '%$k%' OR lastname LIKE '%$k%') AND user_type != 2 ";
                else
                    $query .= "OR (username LIKE '%$k%' OR firstname LIKE '%$k%' OR lastname LIKE '%$k%') ";
            }
            // Eğer birden fazla anahtar kelime varsa, sonrasında eklenen OR'lar için de adminleri hariç tutmak için AND user_type != 2 eklenmeli
            if(count($keywords) > 1) {
                $query .= " AND user_type != 2";
=======
                    $query .= "AND (username LIKE '%$k%' OR firstname LIKE '%$k%' OR lastname LIKE '%$k%') ";
                else
                    $query .= "OR (username LIKE '%$k%' OR firstname LIKE '%$k%' OR lastname LIKE '%$k%') ";
>>>>>>> Stashed changes
=======
                    $query .= "AND (username LIKE '%$k%' OR firstname LIKE '%$k%' OR lastname LIKE '%$k%') ";
                else
                    $query .= "OR (username LIKE '%$k%' OR firstname LIKE '%$k%' OR lastname LIKE '%$k%') ";
>>>>>>> Stashed changes
=======
                    $query .= "AND (username LIKE '%$k%' OR firstname LIKE '%$k%' OR lastname LIKE '%$k%') ";
                else
                    $query .= "OR (username LIKE '%$k%' OR firstname LIKE '%$k%' OR lastname LIKE '%$k%') ";
>>>>>>> Stashed changes
=======
                    $query .= "AND (username LIKE '%$k%' OR firstname LIKE '%$k%' OR lastname LIKE '%$k%') ";
                else
                    $query .= "OR (username LIKE '%$k%' OR firstname LIKE '%$k%' OR lastname LIKE '%$k%') ";
>>>>>>> Stashed changes
            }
        }

        DB::getInstance()->query($query);
        return DB::getInstance()->results();
    }

    /* keywordu düzenledikten sonra boşluklara göre ayırdık 
    mesela ali koç u hem ali hemde koç olarak alır 2 eleman oldu

    keywordsun ilk elemanı boşsa demek ki arama kutusuna bir şey yazılmadı,o zaman boş sorgu yapar

    Ondan sonra normal sorgu yapıyor ali için daha sonra koç için yapıyor 
*/


// Aşağıdaki fonksiyon username'e göre arama yapıyor

    public static function search_by_username($username) {
        if(empty($username)) {
            return array();
        }

        $keyword = mb_strtolower($username, 'UTF-8');
        $keyword = htmlspecialchars($username);
        $keyword = trim($username);

        DB::getInstance()->query("SELECT * FROM user_info WHERE username LIKE '$keyword%'");

        return DB::getInstance()->results();
    }

    
    public function login($email_or_username='', $password='', $remember=false) {
        if($this->id) {
            Session::put($this->sessionName, $this->id);
            $this->isLoggedIn = true;
            return true;
        } else {
            $fetchBy = "username";
            if(strpos($email_or_username, "@")) {
                $fetchBy = "email";
            }
            
            if($this->fetchUser($fetchBy, $email_or_username)) {
                if($this->password === Hash::make($password, $this->salt)) {
                    Session::put($this->sessionName, $this->id);
                    $this->isLoggedIn = true;
                    
                    if($remember) {
                        $hash = Hash::unique();
                        $this->db->query('INSERT INTO users_session (user_id, hash) VALUES (?, ?)', 
                            array($this->id, $hash));
                        Cookie::put($this->cookieName, $hash, Config::get("remember/cookie_expiry"));
                    }
                    return true;
                }
            }
        }
        return false;
    }

    // Logout yaptığımızda bizim session,cookie hash ve databaseden sessionı silmemiz gerekir
    public function logout() {
        // Kullanıcıyı inaktif yap
        $this->db->query("UPDATE user_info SET last_active_update = NULL WHERE id = ?", array($this->id));

        $this->db->query("DELETE FROM users_session WHERE user_id = ?", array($this->id));
        Session::delete($this->sessionName);
        Session::delete(Config::get("session/tokens/logout"));
        Cookie::delete($this->cookieName);
    }
    
    // en son ne zaman aktifti
    public function update_active() {
        $this->db->query("UPDATE user_info SET last_active_update=? WHERE id=?",
        array(
            date("Y/m/d h:i:s A"),
            $this->id
        ));

        return ($this->db->error()) ? false : true;
    }

    public function isLoggedIn() {
        if(Session::exists($this->sessionName)) {
            $dt = Session::get($this->sessionName);
            if($this->fetchUser("id", $dt)) {
                $this->isLoggedIn = true;
                return true;
            }
        }
        return false;
    }

    public function jsonSerialize():mixed
    {
        // JSON formatında döndürmek istediğimiz verileri burada tanımlıyoruz
        $vars = array(
            "id"=>$this->id,
            "username"=>$this->username
        );
        return $vars;
    }

    public function isAdmin() {
        return $this->user_type == 2;
    }
}