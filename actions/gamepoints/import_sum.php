<?php

    $group_guid = (int) get_input('group_guid');
    $ranking_guid = get_input('ranking_guid', null);

    if (!$ranking_guid) {
        register_error(elgg_echo('gamepoints:export:error:need_ranking_guid'));
        forward(REFERER);
    }

    $context = elgg_get_context();
    elgg_set_context('gamepoints');

    //Importamos el ranking
    //Ponemos bien los puntos
    $options = array('type' => 'user', 'relationship' => 'member', 'relationship_guid' => $group_guid,
        'inverse_relationship' => TRUE, 'limit' => null);

    $group_members = new ElggBatch(elgg_get_entities_from_relationship, $options);
    $metadata_name = 'gamepoints_'.$group_guid;

    foreach ($group_members as $user) {
        $sum = $user->$metadata_name;
        //individuales
        $options = array('type' => 'object', 'subtype' => 'gamepoint', 'metadata_case_sensitive' => false, 'limit' => 0,
        'metadata_name_value_pairs' => array(array('name' => 'group_guid', 'value' => $group_guid),
            array('name' => 'ranking_guid', 'value' => $ranking_guid)), 'container_guid' => $user->guid);

        $member_gamepoints = new ElggBatch(elgg_get_entities_from_metadata, $options);

        foreach ($member_gamepoints as $gamepoint) {
            //Solo lo sumamos si no está ya en el ranking actual
            if (!is_array($gamepoint->ranking_guid)) {
                $ranking_guids = array();
                $ranking_guids[] = $gamepoint->ranking_guid;
            }
            else
                $ranking_guids = $gamepoint->ranking_guid;

            if (!array_search((int) 0, $ranking_guids))
                $sum += $gamepoint->points;
        }

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

            foreach ($subgroup_gamepoints as $gamepoint) {
                //Solo lo sumamos si no está ya en el ranking actual
                if (!is_array($gamepoint->ranking_guid)) {
                    $ranking_guids = array();
                    $ranking_guids[] = $gamepoint->ranking_guid;
                }
                else
                    $ranking_guids = $gamepoint->ranking_guid;

                if (!array_search((int) 0, $ranking_guids))
                    $sum += $gamepoint->points;
            }
        }
        $user->$metadata_name = $sum;
    }

    //Quitamos los objetos del ranking
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

    system_message(elgg_echo("gamepoints:export:import_sum:success"));
    forward(REFERER);
?>
