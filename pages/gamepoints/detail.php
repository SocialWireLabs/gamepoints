<?php

                $owner = elgg_get_page_owner_entity();
                if (!$owner) {
                        forward();
                }

                // access check for closed groups
                group_gatekeeper();

                $user_guid = get_input('user_guid');
                $offset = (int)get_input('offset', 0);

                //De usuario
                $options = array('type' => 'object', 'subtype' => 'gamepoint', 'container_guid' => $user_guid, 'metadata_case_sensitive' => false,
                    'metadata_name_value_pairs' => array(array('name' => 'group_guid', 'value' => $owner->guid),
                        array('name' => 'ranking_guid', 'value' => 0)));

                $count_entities = elgg_get_entities_from_metadata(array_merge($options, array('count' => true)));

                //De subgrupo
                $subgroups = elgg_get_entities_from_relationship(array(
                    'type_subtype_pairs' => array('group' => 'lbr_subgroup'),
                    'container_guids' => $owner->guid,
                    'relationship' => 'member',
                    'inverse_relationship' => false,
                    'relationship_guid' => $user_guid,
                ));
                if ($subgroups) {
                    $options_subgroup = array('type' => 'object', 'subtype' => 'gamepoint', 'container_guid' => $subgroups[0]->guid, 'metadata_case_sensitive' => false,
                    'metadata_name_value_pairs' => array(array('name' => 'group_guid', 'value' => $owner->guid),
                        array('name' => 'ranking_guid', 'value' => 0)));

                    $count_entities_subgroup = elgg_get_entities_from_metadata(array_merge($options_subgroup, array('count' => true)));
                }

                $user = get_entity($user_guid);

                if ($count_entities == 0 && $count_entities_subgroup == 0) {
                    $area2 = "<br>" . elgg_echo('gamepoints:detail_empty');
                }
                else {
                    $area2 = "<h2>".elgg_echo('gamepoints:detail:member')."</h2>";
                    $area2 .= elgg_list_entities_from_metadata(array_merge($options, array('limit' => 10, 'offset' => $offset)));
                    if ($subgroups) {
                        $area2 .= "<h2>".elgg_echo('gamepoints:detail:subgroup')."</h2>";
                        $area2 .= elgg_list_entities_from_metadata(array_merge($options_subgroup, array('limit' => 10, 'offset' => $offset)));
                    }
                }


                $title = elgg_echo('gamepoints:title:detail',array($user->name));
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