<?php

	/**
	* Gamepoints form for manually adding points
	*/

        $group_guid = $vars['group_guid'];

        //$members = get_group_members($group_guid,null);
		$group = elgg_get_page_owner_entity();
    $members = $group->getMembers(array("limit" => false));

        foreach ($members as $a_member) {
            if (!is_admin_or_teacher($group_guid, $a_member->guid))
                $map[] = array('name' => $a_member->name, 'member' => $a_member->guid);
        }
        sort($map);
        if (!empty($map)) {
            foreach ($map as $a_member) {
                $keyname = $a_member['member'];
                $content_members_filters[$keyname] = $a_member['name'];
            }
        }


        $form .= "<b>" . elgg_echo('gamepoints:add:user') . "</b>";
        $form .= elgg_view('input/dropdown', array('name' => "user_guid", 'value' => '', 'options_values' => $content_members_filters));
        $form .= "<br><br>";

        $form .= "<b>" . elgg_echo('gamepoints:add:points') . "</b>";
        $form .= elgg_view('input/text', array('name' => "points", 'value' => ''));
        $form .= "<br><br>";

        $form .= "<b>" . elgg_echo('gamepoints:add:description') . "</b>";
        $form .= elgg_view('input/text', array('name' => "description", 'value' => ''));
        $form .= "<br><br>";

        if ($group_guid != null) {
            $form .= elgg_view('input/hidden', array('name' => "group_guid", 'value' => $group_guid));
        }

        $form .= elgg_view('input/submit', array('value' => elgg_echo("save")));

        echo $form;
