<?php

namespace classes;

class Validation {
    private $_passed = true,
            $_errors = array(),
            $_db = null;

    public function __construct() {
        $this->_db = DB::getInstance();
    }

    /*
      Burada genel bir check var email formatı doğru mu , resim boyutu video boyutu doğru mu gibi  
    */
    public function check($source, $items = array()) {

       //error_reporting(E_ERROR | E_PARSE): Sadece çalışmayı etkileyen ciddi hataların gösterilmesini sağlar. Küçük hatalar (örneğin uyarılar) bastırılır.

        error_reporting(E_ERROR | E_PARSE);

        if($source === $_FILES) {
            $counter = 0;
            foreach($items as $item=>$rules) {
                foreach($rules as $rule => $rule_value) {
                    $name = $item;
                    if($rule === "required" && $rule_value == true && empty($name)) {
                        $this->addError("{$rules['name']} zorunlu bir alandır");
                    } else if(!empty($name)) {
                        switch($rule) {
                            case 'image':
                                foreach($_FILES as $key=>$value) {
                                    if(!strpos($key, '.') && strpos($key, '_')) {
                                        $_FILES[$value['name']] = $value;
                                        unset($_FILES[$key]);
                                    }
                                }
                                $img = $_FILES[$name];
                                $name = $img["name"];

                                $allowedImageExtensions = array(".png", ".jpeg", ".gif", ".jpg", ".jfif");

                                $original_extension = (false === $pos = strrpos($name, '.')) ? '' : substr($name, $pos);
                                $original_extension = strtolower($original_extension);

                                if (!in_array($original_extension, $allowedImageExtensions))
                                {
                                    $this->addError("Sadece PNG, JPG, JPEG ve GIF formatındaki resimler {$rules['name']} resmi için kullanılabilir!");
                                }

                                if ($img["size"] > 5500000) {
                                    $this->addError("Üzgünüm, dosyanız çok büyük.");
                                }
                            break;
                            case 'video': 
                                $file = $item;
                                $allowedVideoExtensions = array(".mp4", ".mov", ".wmv", ".flv", ".avi", ".avchd", ".webm", ".mkv");

                                $img = $_FILES[$file];
                                $original_extension = (false === $pos = strrpos($img["name"], '.')) ? '' : substr($img["name"], $pos);
                                $original_extension = strtolower($original_extension);

                                if (!in_array($original_extension, $allowedVideoExtensions))
                                {
                                    $this->addError("Sadece bu formattaki videoları destekliyoruz.");
                                }

                                if ($img["size"] > 1073741824) {
                                    $this->addError("Üzgünüm, dosyanız çok büyük.");
                                }
                        }
                    }
                }
                $counter++;
            }
        } else {
            foreach($items as $item=>$rules) {
                foreach($rules as $rule => $rule_value) {
                    $value = trim($source[$item]);
                    $item = htmlspecialchars($item);
    
                    if($rule === "required" && $rule_value == true && empty($value)) {
                        $this->addError("{$rules['name']} zorunlu bir alandır");
                    } else if(!empty($value)) {
                        switch($rule) {
                            case 'min':
                                if(strlen($value) < $rule_value) {
                                    $this->addError("{$rules['name']} en az $rule_value karakter olmalıdır.");
                                }
                            break;
                            case 'max':
                                if(strlen($value) > $rule_value) {
                                    $this->addError("{$rules['name']} en fazla $rule_value karakter olmalıdır.");
                                }
                            break;
                            case 'matches':
                                if($value != $source[$rule_value]) {
                                    $this->addError("Şifreler eşleşmiyor.");
                                }
                            break;
                            case 'unique':
                                $this->_db->query("SELECT * from user_info WHERE $item = '$value'");
                                if($this->_db->count()) {
                                    $this->addError("Bu {$rules['name']} zaten kullanılıyor.");
                                }
                            break;
                            case 'email-or-username':
                                $email_or_username = trim($value);
                                $email_or_username = filter_var($email_or_username, FILTER_SANITIZE_EMAIL);
                                if(strpos($email_or_username, '@') == true) {
                                    if(!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email_or_username)) {
                                        $this->addError("Geçersiz e-posta adresi.");
                                    }
                                }
                            break;
                            case 'email':
                                $email = trim($value);
                                $email = filter_var($email, FILTER_SANITIZE_EMAIL);
                                if(strpos($email, '@') == true) {
                                    if(!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)) {
                                        $this->addError("Geçersiz e-posta adresi.");
                                    }
                                } else {
                                    $this->addError("Geçersiz e-posta adresi.");
                                }
                            break;
                            case 'range':
                                if(!in_array($value, $rule_value))
                                    $this->addError("{$rules['name']} alanı ya ayarlanmamış ya da geçersiz!");
                            break;
                        }
                    }
                }
            }   
        }

        if(empty($this->_errors)) {
            $this->_passed = true;
        } else {
            $this->_passed = false;
        }

        
        return $this;
    }

    public function addError($error) {
    
        $this->_errors[] = $error;
    }

    public function errors() {
        return $this->_errors;
    }

    public function passed() {
        return $this->_passed ? true : false;
    }
}