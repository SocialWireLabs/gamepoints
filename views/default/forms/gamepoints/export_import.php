<?php

	/**
	* Gamepoints form for manually adding points
	*/

        $group_guid = $vars['group_guid'];

        $options = array('type' => 'object', 'subtype' => 'gamepoints_ranking', 'limit' => 0, 'container_guid' => $group_guid);

        $rankings = elgg_get_entities_from_metadata($options);
        $rankings_array = array();
        foreach ($rankings as $ranking) {
            $rankings_array[$ranking->guid] = $ranking->ranking_name;
        }

        $form .= "<h2><b>" . elgg_echo('gamepoints:export:import') . "</b></h2><br />";
        $form .= "<b>" . elgg_echo('gamepoints:export:reset:ranking_name') . "</b><br /><br />";
        $form .= elgg_view('input/text', array('name' => "ranking_name", 'value' => '')). "<br /><br />";
        $form .= "<b>" . elgg_echo('gamepoints:export:import:ranking') . "</b><br /><br />";
        $form .= elgg_view('input/dropdown', array('name' => "ranking_guid", 'value' => '', 'options_values' => $rankings_array)). "<br /><br />";
        $form .= "<br />";

        if ($group_guid != null) {
            $form .= elgg_view('input/hidden', array('name' => "group_guid", 'value' => $group_guid));
        }

        $form .= elgg_view('input/submit', array('value' => elgg_echo("gamepoints:export:reset:submit_export_import")));
        $form .= "<br><br><br>";
        
        echo $form;