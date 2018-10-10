<?php

    $user_guid = (int) get_input('user_guid');
    $points = (int) get_input('points');
    $group_guid = (int) get_input('group_guid');
    $description = get_input('description');

    gamepoints_add($user_guid, $points, null, $group_guid, false,$description);
    system_message(elgg_echo("gamepoints:add_success"));


    forward(REFERER);
?>
