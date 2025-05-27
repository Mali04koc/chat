-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 24, 2021 at 01:25 PM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chat`
--

-- --------------------------------------------------------

--
--   Kanal tablosu yapısı
--

CREATE TABLE `channel` (
  `id` int(11) NOT NULL,
  `sender` int(11) DEFAULT NULL,
  `receiver` int(11) DEFAULT NULL,
  `group_recipient_id` int(11) DEFAULT NULL,
  `message_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Yorum tablosu yapısı
--

CREATE TABLE `comment` (
  `id` int(11) NOT NULL,
  `comment_owner` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `comment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `comment_edit_date` timestamp NULL DEFAULT NULL,
  `comment_text` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 

/* Engine=InnoDB ile sql de hangi depolama motorunu seçtik, bunu yazmasak varsayılan depolama motorunu
seçicekti.utf8 yerine bu dil paketini seçme sebebimiz mesajlarda emoji gönderebilmek */

-- --------------------------------------------------------

--
-- Beğen tablosu yapısı
--

CREATE TABLE `like` (
  `id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `like_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Mesaj tablosu yapısı
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `message_creator` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `create_date` datetime NOT NULL DEFAULT current_timestamp(),
  `is_reply` int(11) DEFAULT NULL,
  `reply_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- mesaj alıcısı tablosu yapısı

CREATE TABLE `message_recipient` (
  `id` int(11) NOT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* tinyint adı gibi küçük sayısal değerler için kullanılır.*/
-- --------------------------------------------------------

--
-- Gönder tablosu yapısı
--

CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `post_owner` int(11) NOT NULL,
  `post_visibility` int(11) NOT NULL DEFAULT 0,
  `post_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `post_edit_date` timestamp NULL DEFAULT NULL,
  `text_content` text DEFAULT NULL,
  `picture_media` text DEFAULT NULL,
  `video_media` text DEFAULT NULL,
  `post_place` int(11) DEFAULT 1,
  `is_shared` int(11) DEFAULT 0,
  `post_shared_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* timestamp de aynı datetime gibi,aralarındaki fark timestamp ile 
tablonun oluşturulma veya güncellenme zamanını otomatik olarak izlenir.Datetime ise önemli tarihleri
tutar.*/
-- --------------------------------------------------------

--
-- 
-- Gönderi yeri tablosu yapısı
--

CREATE TABLE `post_place` (
  `id` int(11) NOT NULL,
  `post_place` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Gönderi yeri tablosuna veri ekliyoruz
--

INSERT INTO `post_place` (`id`, `post_place`) VALUES
(1, 'timeline'),
(2, 'group');

-- --------------------------------------------------------

--
-- Gönderi görünürlük tablosu yapısı
--

CREATE TABLE `post_visibility` (
  `id` int(11) NOT NULL,
  `visibility` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Gönderi görünürlük tablosuna veri ekliyoruz
--

INSERT INTO `post_visibility` (`id`, `visibility`) VALUES
(1, 'public'),
(2, 'friends'),
(3, 'only me');

-- --------------------------------------------------------

--
-- Kullanıcı oturum tablosu yapısı , bununla kullanıcıların oturum bilgilerini tutacağız
--

CREATE TABLE `users_session` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `hash` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Kullanıcı takip tablosu yapısı
--

CREATE TABLE `user_follow` (
  `id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `followed_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Kullanıcı bilgileri tablosu yapısı
--

CREATE TABLE `user_info` (
  `id` int(11) NOT NULL,
  `username` varchar(20) DEFAULT NULL,
  `password` varchar(64) DEFAULT NULL,
  `salt` varchar(32) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `joined` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_type` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT '',
  `bio` varchar(800) DEFAULT NULL,
  `picture` text DEFAULT NULL,
  `cover` text DEFAULT NULL,
  `private` int(11) NOT NULL DEFAULT -1,
  `last_active_update` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Kullanıcının sistemdeki bilgileri tablosuna veri ekliyoruz
--

CREATE TABLE `user_metadata` (
  `id` int(11) NOT NULL,
  `label` varchar(200) DEFAULT NULL,
  `content` varchar(200) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Kullanıcı ilişkileri tablosu yapısı`
--

CREATE TABLE `user_relation` (
  `from` int(11) NOT NULL,
  `to` int(11) NOT NULL,
  `status` varchar(1) DEFAULT NULL,
  `since` timestamp NOT NULL DEFAULT current_timestamp(),
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Kullanıcı türü tablosu yapısı admin veya normal kullanıcıları ayırmak için
--

CREATE TABLE `user_type` (
  `id` int(11) NOT NULL,
  `type_name` varchar(30) DEFAULT NULL,
  `permission` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Kullanıcı türü tablosuna veri ekliyoruz
--

INSERT INTO `user_type` (`id`, `type_name`, `permission`) VALUES
(1, 'Normal user', NULL),
(2, 'Admin', '{\'Admin\':1}');

-- --------------------------------------------------------

--
-- Kullanıcı mesaj yazma bildirimi tablosu yapısı
--

CREATE TABLE `writing_message_notifier` (
  `message_writer` int(11) DEFAULT NULL,
  `message_waiter` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablolar arası ilişkiler ve keyleri ekliyoruz
--

--
-- Kanal tablosu için indexler
--
ALTER TABLE `channel`
  ADD PRIMARY KEY (`id`);

--
-- Yorum tablosu için indexler
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`id`);

--
-- Beğen tablosu için indexler
--
ALTER TABLE `like`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_index` (`post_id`,`user_id`);

--
-- Mesaj tablosu için indexler
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Mesaj Alıcısı tablosu için indexler`
--
ALTER TABLE `message_recipient`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Gönder tablosu için indexler
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_post_place` (`post_place`);

--
-- Gönderi yeri tablosu için indexler
--
ALTER TABLE `post_place`
  ADD PRIMARY KEY (`id`);

--
-- Gönderi görünürlük tablosu için indexler
--
ALTER TABLE `post_visibility`
  ADD PRIMARY KEY (`id`);

--
-- Kullanıcı oturum tablosu için indexler
--
ALTER TABLE `users_session`
  ADD PRIMARY KEY (`id`);

--
-- Kullanıcı takip tablosu için indexler
--
ALTER TABLE `user_follow`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `follow_unique` (`follower_id`,`followed_id`);

--
-- Kullanıcı bilgileri tablosu için indexler
--
ALTER TABLE `user_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Kullanıcının sistemdeki bilgileri tablosu için indexler
--
ALTER TABLE `user_metadata`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_label_UK` (`label`,`user_id`);

--
-- Kullanıcı ilişkileri tablosu için indexler
--
ALTER TABLE `user_relation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQUE_RELATION` (`from`,`to`,`status`);

--
-- Kullanıcı türü tablosu için indexler
--
ALTER TABLE `user_type`
  ADD PRIMARY KEY (`id`);

--
-- Tablolarda otomatik artış ayarları
--

--
-- Kanal tablosu için otomatik artış ayarları 
--
ALTER TABLE `channel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Yorum tablosu için otomatik artış ayarları
--
ALTER TABLE `comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
--  Beğen tablosu için otomatik artış ayarları
--
ALTER TABLE `like`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Mesaj tablosu için otomatik artış ayarları
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Mesaj alıcı tablosu için otomatik artış ayarları
--
ALTER TABLE `message_recipient`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Gönder tablosu için otomatik artış ayarları
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Gönderi yeri tablosu için otomatik artış ayarları
--
ALTER TABLE `post_place`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Gönderi görünürlük tablosu için otomatik artış ayarları
--
ALTER TABLE `post_visibility`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Kullanıcı oturum tablosu için otomatik artış ayarları
--
ALTER TABLE `users_session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Kullanıcı takip tablosu için otomatik artış ayarları
--
ALTER TABLE `user_follow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Kullanıcı bilgileri tablosu için otomatik artış ayarları
--
ALTER TABLE `user_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Kullanıcının sistemdeki bilgileri tablosu için otomatik artış ayarları
--
ALTER TABLE `user_metadata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Kullanıcı ilişkileri tablosu için otomatik artış ayarları
--
ALTER TABLE `user_relation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Kullanıcı türü tablosu için otomatik artış ayarları
--
ALTER TABLE `user_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- fOREİGN KEY TANIMLADIK
--

--
-- Mesaj alıcısı tablosu için foreign key tanımladık
--
ALTER TABLE `message_recipient`
  ADD CONSTRAINT `message_recipient_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE SET NULL;

--
-- Gönder tablosu için foreign key tanımladık`
--
ALTER TABLE `post`
  ADD CONSTRAINT `fk_post_place` FOREIGN KEY (`post_place`) REFERENCES `post_place` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

/* Mantığı: Script çalışırken yapılan geçici ayar değişikliklerini, işlem tamamlandığında eski haline getirerek "temiz bir çıkış" yapmayı sağlar. */

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_discussions` (IN `user_id` INT)  BEGIN
	SELECT MAX(M.id) AS mid, M.message_creator as message_creator, MR.receiver_id as message_receiver, M.create_date as message_date, MR.is_read as is_read FROM message AS M
	INNER JOIN message_recipient AS MR
	ON M.id = MR.message_id
    WHERE M.message_creator = user_id OR MR.receiver_id = user_id
	GROUP BY M.message_creator, MR.receiver_id
	ORDER BY mid DESC;
END$$

DELIMITER ;

/*Varsayılan olarak MySQL'de komutlar ; ile biter. Ancak, prosedür gibi çok satırlı bir yapıyı yazarken ;
 sıkça kullanıldığı için, MySQL'in prosedür içindeki ; işaretlerini yanlış anlamaması için farklı bir delimiter ($$) belirlenir.*/

/* Busp_get_discussions prosedüründe root kullanıcının user_id ile kullanıcının katıldığı mesajlaşmaları döndürür. */