<?php

    $group_guid = (int) get_input('group_guid');
    $ranking_name = get_input('ranking_name', null);

    if (!$ranking_name) {
        register_error(elgg_echo('gamepoints:export:error:need_name'));
        forward(REFERER);
    }

    $options = array('type' => 'object', 'subtype' => 'gamepoints_ranking', 'limit' => 1, 'container_guid' => $group_guid,
        'metadata_case_sensitive' => false, 'metadata_name_value_pairs' => array('name' => 'ranking_name', 'value' => $ranking_name));

    $rankings = elgg_get_entities_from_metadata($options);
    if ($rankings) {
        register_error(elgg_echo('gamepoints:export:error:used_name'));
        forward(REFERER);
    }

    $ranking = new ElggObject();

    // Add the points, type, and guid as metadata to the user object
    $ranking->subtype = "gamepoints_ranking";
    $ranking->owner_guid = elgg_get_logged_in_user_guid();
    $ranking->container_guid = $group_guid;
    $ranking->ranking_name = $ranking_name;

    if (!$ranking->save()) {
        register_error(elgg_echo('gamepoints:export:error:create_ranking'));
        forward(REFERER);
    }

    $context = elgg_get_context();
    elgg_set_context('gamepoints');

    //Metemos los puntos en el ranking
    $options = array('type' => 'object', 'subtype' => 'gamepoint', 'metadata_case_sensitive' => false, 'limit' => 0,
        'metadata_name_value_pairs' => array(array('name' => 'group_guid', 'value' => $group_guid),
            array('name' => 'ranking_guid', 'value' => 0)));

    $batch = new ElggBatch(elgg_get_entities_from_metadata, $options);

    foreach ($batch as $gamepoint) {
        if (!is_array($gamepoint->ranking_guid)) {
            $ranking_guids = array();
            $ranking_guids[] = $gamepoint->ranking_guid;
        }
        else
            $ranking_guids = $gamepoint->ranking_guid;

        $ranking_guids[] = $ranking->getGUID();
        $pos = array_search((int) 0, $ranking_guids);
        unset($ranking_guids[$pos]);
        $ranking_guids = array_values($ranking_guids);

        $gamepoint->ranking_guid = $ranking_guids;
    }

    //Reseteamos el ranking actual
    $options = array('type' => 'user', 'relationship' => 'member', 'relationship_guid' => $group_guid,
        'inverse_relationship' => TRUE, 'limit' => null, 'offset' => $offset);

    $batch = new ElggBatch(elgg_get_entities_from_relationship, $options);

    foreach ($batch as $user) {
        $metadata_name = 'gamepoints_'.$group_guid;
        $user->$metadata_name = (int) 0;
    }
    
    elgg_set_context($context);

    system_message(elgg_echo("gamepoints:export:reset:success"));
    forward(REFERER);
?>
