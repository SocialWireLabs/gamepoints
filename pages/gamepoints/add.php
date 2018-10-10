<?php

		$owner = elgg_get_page_owner_entity();
                if (!$owner) {
                        forward();
                }

                // access check for closed groups
                group_gatekeeper();

                $vars['group_guid'] = $owner->guid;
                $form_vars = array('enctype' => 'multipart/form-data');
                $content = elgg_view_form('gamepoints/add', $form_vars, $vars);

                $title = elgg_echo('gamepoints:title:add', array($user->name));

                // Display them in the page
                $params = array(
                    'content' => $content,
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