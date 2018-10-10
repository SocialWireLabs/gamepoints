<?php

    $group_guid = (int) get_input('group_guid');
    $ranking_name = get_input('ranking_name', null);
    $ranking_guid = get_input('ranking_guid', null);

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

    if (!$ranking_guid) {
        register_error(elgg_echo('gamepoints:export:error:need_ranking_guid'));
        forward(REFERER);
    }



    $ranking = new ElggObject();

    // Add the points, type, and guid as metadata to the user object
    $ranking->subtype = "gamepoints_ranking";
    $ranking->owner_guid = elgg_get_logged_in_user_guid();
    $ranking->container_guid = $group_guid;
    $ranking->ranking_name = $ranking_name;

    if (!$ranking->save()) {
        register_error(elgg_echo('gamepoints:export:reset:error:create_ranking'));
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

    //Importamos el ranking
    //Ponemos bien los puntos
    $options = array('type' => 'user', 'relationship' => 'member', 'relationship_guid' => $group_guid,
        'inverse_relationship' => TRUE, 'limit' => null);

    $group_members = new ElggBatch(elgg_get_entities_from_relationship, $options);

    foreach ($group_members as $user) {
        $sum = 0;
        //individuales
        $options = array('type' => 'object', 'subtype' => 'gamepoint', 'metadata_case_sensitive' => false, 'limit' => 0,
        'metadata_name_value_pairs' => array(array('name' => 'group_guid', 'value' => $group_guid),
            array('name' => 'ranking_guid', 'value' => $ranking_guid)), 'container_guid' => $user->guid);

        $member_gamepoints = new ElggBatch(elgg_get_entities_from_metadata, $options);

        foreach ($member_gamepoints as $gamepoint)
            $sum += $gamepoint->points;

        //De subgrupo
        $subgroups = elgg_get_entities_from_relationship(array(
            'type_subtype_pairs' => array('group' => 'lbr_subgroup'),
            'container_guids' => $group_guid,
            'relationship' => 'member',
            'inverse_relationship' => false,
            'relationship_guid' => $user->guid,
        ));
        if ($subgroups) {
            $options = array('type' => 'object', 'subtype' => 'gamepoint', 'metadata_case_sensitive' => false, 'limit' => 0,
            'metadata_name_value_pairs' => array(array('name' => 'group_guid', 'value' => $group_guid),
                array('name' => 'ranking_guid', 'value' => $ranking_guid)), 'container_guid' => $subgroups[0]->guid);
            $subgroup_gamepoints = new ElggBatch(elgg_get_entities_from_metadata, $options);

            foreach ($subgroup_gamepoints as $gamepoint)
                $sum += $gamepoint->points;
        }

        $metadata_name = 'gamepoints_'.$group_guid;
        $user->$metadata_name = $sum;
    }

    $options = array('type' => 'object', 'subtype' => 'gamepoint', 'metadata_case_sensitive' => false, 'limit' => 0,
        'metadata_name_value_pairs' => array(array('name' => 'group_guid', 'value' => $group_guid),
            array('name' => 'ranking_guid', 'value' => $ranking_guid)));

    $batch = new ElggBatch(elgg_get_entities_from_metadata, $options);

    foreach ($batch as $gamepoint) {
        if (!is_array($gamepoint->ranking_guid)) {
            $ranking_guids = array();
            $ranking_guids[] = $gamepoint->ranking_guid;
        }
        else
            $ranking_guids = $gamepoint->ranking_guid;

        if (!array_search((int) 0, $ranking_guids))
            $ranking_guids[] = (int) 0;

        $gamepoint->ranking_guid = $ranking_guids;
    }
    
    elgg_set_context($context);

    system_message(elgg_echo("gamepoints:export:import:success"));
    forward(REFERER);
?>
