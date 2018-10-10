<?php

    $guid = (int) get_input('guid');
    gamepoints_remove($guid);

    system_message(elgg_echo("gamepoints:delete_success"));
    forward($_SERVER['HTTP_REFERER']);
?>
