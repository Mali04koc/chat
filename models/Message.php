<?php

namespace models;

use classes\{DB};

class Message {
    private $db,
    $id,
    $message_sender,
    $message_receiver,
    $message,
    $message_date = '',
    $recipient_id,
    $is_read = false,
    $is_reply=null,
    $reply_to=null;
    
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
        $this->message_sender = $data["sender"];
        $this->message_receiver = $data["receiver"];
        $this->message = $data["message"];
        $this->message_date = $data["message_date"];
    }

    public static function exists($message_id) {
        DB::getInstance()->query("SELECT * FROM message WHERE id = ?", array($message_id));

        return DB::getInstance()->count();
    }

    // burada istenen mesaj varsa count>0 olur ve ardından o mesajdaki is_reply gibi db verileri
    // message classındaki değişkenlere atanır
    public function get_message($property, $value) {
        $this->db->query("SELECT * FROM `message` WHERE `$property` = ?", array($value));

        if($this->db->count() > 0) {
            $fetched_message = $this->db->results()[0];

            $this->id = $fetched_message->id;
            $this->message_sender = $fetched_message->message_creator;
            $this->message = $fetched_message->message;
            $this->message_date = $fetched_message->create_date;
            $this->is_reply = $fetched_message->is_reply;
            $this->reply_to = $fetched_message->reply_to;

            return true;
        }

        return false;
    }


    // message_creator kısmını yani mesaj yazanın idsini döndürüyoruz.
    public static function get_creator_by_id($message_id) {
        DB::getInstance()->query("SELECT message_creator FROM `message` WHERE `id` = ?", array($message_id));

        if(DB::getInstance()->count() > 0) {
            $fetched_message = DB::getInstance()->results()[0];
            return $fetched_message;
        }

        return false;
    }


    // burada istenen mesaj varsa count>0 olup o mesajı getirir ama sınıfın değişkenlerini güncellemez
    public static function get_message_obj($property, $value) {
        DB::getInstance()->query("SELECT * FROM `message` WHERE `$property` = ?", array($value));

        if(DB::getInstance()->count() > 0) {
            $fetched_message = DB::getInstance()->results()[0];
            return $fetched_message;
        }

        return false;
    }

    public function add() {
    
        // mesajı ekle
        $this->db->query("INSERT INTO `message` 
        (`message_creator`, `message`, `create_date`, `is_reply`, `reply_to`) 
        VALUES (?, ?, ?, ?, ?)", array(
            $this->message_sender,
            $this->message,
            $this->message_date,
            $this->is_reply,
            $this->reply_to,
        ));

        // varsa error onları al
        $message_row_inserted = $this->db->error();

        // eklenen mesajın ID sini al
        $last_inserted_message_id = $this->db->pdo()->lastInsertId();

       // eklenen mesajı gönderini,alanı,idyi channel tablosuna ekle
        $this->db->query("INSERT INTO `channel` 
        (`sender`, `receiver`, `group_recipient_id`, `message_id`) 
        VALUES (?, ?, ?, ?)", array(
            $this->message_sender,
            $this->message_receiver,
            null,
            $last_inserted_message_id
        ));

        //error varsa al
        $channel_row_inserted = $this->db->error();

        // mesaj ilişkisini message_recipienta ekle
        $this->db->query("INSERT INTO `message_recipient` 
        (`receiver_id`, `message_id`, `is_read`) 
        VALUES (?, ?, ?)", array(
            $this->message_receiver,
            $last_inserted_message_id,
            $this->is_read
        ));
         
        // error varsa al
        $message_recipient_row_inserted = $this->db->error();
        // her şey düzgün giderse Mesaj ID sini döndür
        return $this->db->error() == false ? $last_inserted_message_id : false;
    }


    // Mesaj tablosunda güncelle
    public function update_property($property) {
        $this->db->query("UPDATE `message` SET $property=? WHERE id=?",
        array(
            $this->$property,
            $this->id
        ));

        return ($this->db->error()) ? false : true;
    }





    public function delete_sended_message() {
        // hemen altta açıklaması olan function ile mesajı alıcı tarafından siliyoruz
        $this->delete_received_message();
        // mesajı gönderen kısmından siliyoruz
        $this->db->query("DELETE FROM `message` WHERE id = ?", array(
            $this->id
        ));

        return ($this->db->error()) ? false : true;
    }



    // burada sadece mesajı alıcı tarafından siliyoruz
    public function delete_received_message() {
    

        $this->db->query("DELETE FROM message_recipient WHERE message_id = ?", array(
            $this->id
        ));

        return ($this->db->error()) ? false : true;
    }

    //Belirli bir gönderici ve alıcı arasındaki tüm kanal verilerini siler.
    public static function dump_channel($sender, $receiver) {
        DB::getInstance()->query("DELETE FROM channel WHERE sender = ? AND receiver = ?", array($sender, $receiver));
    }


    // Belirli bir gönderici ve alıcı arasındaki mesajları getirir.
    public static function getMessages($sender, $receiver) {
        DB::getInstance()->query("SELECT * FROM `message_recipient`
        INNER JOIN `message` 
        ON message.id = message_recipient.message_id 
        WHERE message_recipient.receiver_id = ? AND message.message_creator = ?", array(
            $receiver,
            $sender
        ));

        return DB::getInstance()->results();
    }

    public function get_message_recipient_data() {
        DB::getInstance()->query("SELECT * FROM `message_recipient`
        WHERE message_recipient.message_id = ?", array(
            $this->id
        ));

        return DB::getInstance()->results()[0];
    }

    public function add_writing_message_notifier() {
        $this->db->query("INSERT INTO `writing_message_notifier` 
        (`message_writer`, `message_waiter`) 
        VALUES (?, ?)", array(
            $this->message_sender,
            $this->message_receiver,
        ));

        return $this->db->error() == false ? true : false;
    }

    public function delete_writing_message_notifier() {
        $this->db->query("DELETE FROM `writing_message_notifier` WHERE `message_writer` = ? AND `message_waiter` = ?"
        , array(
            $this->message_sender,
            $this->message_receiver,
        ));

        return $this->db->error() == false ? true : false;
    }
    // Kullanıcı ile ilgili tüm tartışmaları (discussions) getirir.

    //Kullanım: Bir stored procedure olan sp_get_discussions çağrılır
    public static function get_discussions($user_id) {
        DB::getInstance()->query("CALL sp_get_discussions(?)", array($user_id));

        return DB::getInstance()->results();
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}
