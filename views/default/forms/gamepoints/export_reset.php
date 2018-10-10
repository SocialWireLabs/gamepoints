<?php

	/**
	* Gamepoints form for manually adding points
	*/

        $group_guid = $vars['group_guid'];


        $form .= "<h2><b>" . elgg_echo('gamepoints:export:reset') . "</b></h2><br />";
        $form .= "<b>" . elgg_echo('gamepoints:export:reset:ranking_name') . "</b><br /><br />";
        $form .= elgg_view('input/text', array('name' => "ranking_name", 'value' => ''));
        $form .= "<br><br>";

        if ($group_guid != null) {
            $form .= elgg_view('input/hidden', array('name' => "group_guid", 'value' => $group_guid));
        }

        $form .= elgg_view('input/submit', array('value' => elgg_echo("gamepoints:export:reset:submit_export_reset")));
        $form .= "<br><br><br>";
        
        echo $form;