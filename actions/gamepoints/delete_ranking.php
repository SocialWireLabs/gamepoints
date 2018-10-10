<?php

    $group_guid = (int) get_input('group_guid');
    $ranking_guid = get_input('ranking_guid', null);

    if (!$ranking_guid) {
        register_error(elgg_echo('gamepoints:export:error:need_ranking_guid'));
        forward(REFERER);
    }

    $context = elgg_get_context();
    elgg_set_context('gamepoints');

    //Quitamos los objetos del ranking y borramos ranking
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

        $pos = array_search($ranking_guid, $ranking_guids);
        unset($ranking_guids[$pos]);
        $ranking_guids = array_values($ranking_guids);

        $gamepoint->ranking_guid = $ranking_guids;
    }

    $ranking = get_entity($ranking_guid);
    $ranking->delete();
    
    elgg_set_context($context);

    system_message(elgg_echo('gamepoints:export:delete_ranking:success'));
    forward(REFERER);
?>
