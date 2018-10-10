<?php

    $user_guid = (int)get_input('user_guid');
    $group_guid = (int)get_input('group');

    gamepoints_reset_user($user_guid, $group_guid);
    
    system_message(elgg_echo("gamepoints:reset:success"));
    forward(REFERER);
?>
