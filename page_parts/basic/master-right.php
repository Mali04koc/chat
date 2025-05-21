<?php

use classes\Config;
use models\UserRelation;
use layouts\master_right\Right as MasterRightComponents;

$search_url = Config::get("root/path") . "search.php";

?>

<div id="master-right">
    <div class="flex-space relative">
        <h3 class="title-style-2">Bağlantılar</h3>
        <div>
            <a href="" id="contact-search"></a>
        </div>
        <div class="absolute" id="contact-search-field-container">
            <input type="text" id="contact-search-field" placeholder="Arkadaş ve grup ara">
            <a class="not-link" href=""><img src="public/assets/images/icons/close.png" id="close-contact-search" class="image-style-4" alt=""></a>
        </div>
    </div>
    <div id="contacts-container">
        <?php
            $user_relation = new UserRelation();
            $friends = $user_relation->get_friends($current_user_id);

            if(empty($friends)) {
                echo <<<EMPTY
                    <div class="flex-column" style="margin-top: 40px">
                        <img src="public/assets/images/icons/white-search.png" alt="" style="height: 40px; width: 40px; margin: 0 auto;">
                        <p style="text-align: center">Arkadaş eklemeye veya sevdiğin influencerı takip etmeye başla.</p>
                        <p style="text-align: center">Buraya <a href="$search_url" class="link" style="color: rgb(66, 219, 66)">tıkla</a> ve arama sayfasına git</p>
                    </div>
EMPTY;      
            } else {
                $master_right = new MasterRightComponents();
                foreach($friends as $friend) {
                    $master_right->generateFriendContact($current_user_id, $friend);
                }
            }

        ?>

    </div>
</div>