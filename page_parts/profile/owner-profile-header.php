<?php
    use classes\{Token, Config};

    $user_profile_picture = $profile_user_picture = Config::get("root/path") . (empty($fetched_user->getPropertyValue("picture")) ? "public/assets/images/logos/logo512.png" : $fetched_user->getPropertyValue("picture"));
    $private = $user->getPropertyValue("private");
?>

<div class="flex-space" id="owner-profile-menu-and-profile-edit">
    <div class="row-v-flex">
        <a href="" class="profile-menu-item profile-menu-item-selected" style="border-radius: 0">Timeline</a>
    </div>
</div>