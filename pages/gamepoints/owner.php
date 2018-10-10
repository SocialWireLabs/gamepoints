<?php

                $owner = elgg_get_page_owner_entity();
                if (!$owner) {
                        forward();
                }

                // access check for closed groups
                group_gatekeeper();

                $area2 = "<div><br><table><tr><th width=\"50%\"><b>".elgg_echo('gamepoints:column:user')."</b></th>";
                $area2 .= "<th width=\"20%\"><b>".elgg_echo('gamepoints:column:points')."</b></th>";
                if (elgg_is_admin_logged_in() || elgg_get_logged_in_user_guid() == $owner->owner_guid || (elgg_is_active_plugin('group_tools') && check_entity_relationship(elgg_get_logged_in_user_guid(),'group_admin', $owner->guid)))
                    $area2 .= "<th width=\"10%\"><b>".elgg_echo('gamepoints:column:action')."</b></tr>";
                $area2 .= "<tr><td colspan=3><hr></td></tr>";

                $offset = (int)get_input('offset', 0);

                $order_by_metadata = array('name' => 'gamepoints_'.  elgg_get_page_owner_guid(), 'direction' => 'desc', 'as' => 'integer');
                $options = array('type' => 'user', 'relationship' => 'member', 'relationship_guid' => elgg_get_page_owner_guid(), 'inverse_relationship' => TRUE,'limit' => null, 'offset' => $offset, 'order_by_metadata' => $order_by_metadata);

                $ordered_members = elgg_get_entities_from_relationship($options);
				$wwwroot = elgg_get_config('wwwroot');

                foreach ($ordered_members as $member) {
                    if (!is_admin_or_teacher($owner->guid, $member->guid)) {
		        $metadata_name = 'gamepoints_'.$owner->guid;
			if (($member->$metadata_name)&&(is_numeric($member->$metadata_name))) {
                           $area2 .= "<tr><td><a href=\"{$wwwroot}gamepoints/detail/group:{$owner->guid}/{$member->guid}\">{$member->name}</a></td>";		
                           $area2 .= "<td>{$member->$metadata_name}</td>";
                           if (is_admin_or_teacher($owner->guid))
                              $area2 .= "<td>" . elgg_view("output/url", array('href' => elgg_add_action_tokens_to_url ($wwwroot. "action/gamepoints/reset?user_guid={$member->guid}&group=$owner->guid"),'text' => elgg_echo('gamepoints:reset'),'confirm' => sprintf(elgg_echo('gamepoints:reset:confirm'), $entity->name)))."</td></tr>";
                       }
		    }

                }
                $area2 .= "</table></div>";


                $title = elgg_echo('gamepoints:title:group',array($owner->name));
                // Display them in the page
                $params = array(
                    'content' => $area2,
                    'title' => $title
                );

                if (elgg_instanceof($owner, 'group')) {
                        $params['filter'] = '';
                }

                if ($owner instanceof ElggGroup)
                    $params['sidebar'] = elgg_view('gamepoints/sidebar/links_sidebar', array('owner' => $owner));

                $body = elgg_view_layout('content', $params);

                echo elgg_view_page($title,$body);
?>
