<?php
    function gamepoints_init() {        
        elgg_register_entity_type('object', 'gamepoint');

        elgg_extend_view('css', 'gamepoints/css');

        elgg_register_page_handler('gamepoints','gamepoints_page_handler');
        
        add_group_tool_option('gamepoints',elgg_echo('gamepoints:group:enable'),false);
        elgg_extend_view('groups/tool_latest', 'gamepoints/group_module');

        elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'gamepoints_owner_block_menu');
    }

    function gamepoints_owner_block_menu($hook, $type, $return, $params) {
        if ($params['entity'] instanceof ElggGroup) {
            if ($params['entity']->gamepoints_enable != "no") {
                    $url = "gamepoints/group/{$params['entity']->guid}/all";
                    $item = new ElggMenuItem('gamepoints', elgg_echo('gamepoints:group'), $url);
                    $return[] = $item;
            }
        }
        return $return;
    }

    function gamepoints_page_handler($page) {
            if (!isset($page[0])) {
                    return false;
            }
            $base_dir = elgg_get_plugins_path() . 'gamepoints/pages/gamepoints';

            switch ($page[0]) {
                    case "group":
                            include "$base_dir/owner.php";
                            break;
                    case "detail":
                            set_input('username', $page[1]);
                            set_input('user_guid', $page[2]);
                            include "$base_dir/detail.php";
                            break;
                    case "add":
                            include "$base_dir/add.php";
                            break;
                    case "export":
                            set_input('group_guid', $page[1]);
                            include "$base_dir/export.php";
                            break;
                    default:
                            return false;
            }
    }

    function gamepoints_add($user_guid, $points, $entity_guid = null, $group_guid = null, $subgroup = false, $description = null) {
        $context = elgg_get_context();
        elgg_set_context('gamepoints');

        //comprobamos si ya fue evaluada, para que lo que haga sea actualizar en ese caso
        if ($entity_guid) {
            $options = array('type' => 'object', 'subtype' => 'gamepoint', 'metadata_case_sensitive' => false,
                        'metadata_name_value_pairs' => array('name' => 'rated_entity', 'value' => $entity_guid), 'limit' => 1);
            $gamepoints = elgg_get_entities_from_metadata($options);
        }
        
        if ($gamepoints){
            gamepoints_update($gamepoints[0]->guid, (int) $points);
        }
        else {
            $gamepoint = new ElggObject();

            // Add the points, type, and guid as metadata to the user object
            $gamepoint->subtype = "gamepoint";
            $gamepoint->owner_guid = elgg_get_logged_in_user_guid();
            $gamepoint->points = (int) $points;
            $gamepoint->container_guid = $user_guid;
            $gamepoint->rated_entity = $entity_guid;
            $gamepoint->group_guid = $group_guid;
            $gamepoint->description = $description;
            $gamepoint->ranking_guid = (int) 0;

            $group = get_entity($group_guid);
            $gamepoint->access_id = 0;
            if ($group_guid) {
                $group = get_entity($group_guid);
                $gamepoint->access_id = $group->group_acl;
                $gamepoint->subgroup = $subgroup;
            }
            else {
                $gamepoint->access_id = 2;
            }

            //Si tuvo éxito actualizamos los metadatos del usuario
            if ($gamepoint->save()) {
                if ($subgroup) {
                    $subgroup_members = get_group_members($user_guid,null);
                    foreach ($subgroup_members as $member){
                        $metadata_name = 'gamepoints_'.$group_guid;
                        if ($member->$metadata_name)
                            $member->$metadata_name = $member->$metadata_name + $points;
                        else
                            $member->$metadata_name = $points;
                    }
                }
                else {

                    $user = get_entity($user_guid);

                    if ($group_guid)
                        $metadata_name = 'gamepoints_'.$group_guid;
                    else
                        $metadata_name = 'gamepoints';

                    if ($user->$metadata_name) {
                        $user->$metadata_name = $user->$metadata_name + $points;
                    } else {
                        $user->$metadata_name = $points;
	 	    }
                }
            }
        }
        elgg_set_context($context);
    }

    function gamepoints_remove($guid) {
        $context = elgg_get_context();
        elgg_set_context('gamepoints');
        $entity = get_entity($guid);
        $user = get_entity($entity->container_guid);
        $group_guid = $entity->group_guid;
        $points = (int) $entity->points;

        $entity->delete();
        
        elgg_set_context($context);
    }

    function gamepoints_get($user_guid, $group_guid = null) {
        $user = get_user($user_guid);
        if ($group_guid){
            $metadata_name = 'gamepoints_'.$group_guid;
            return ($user->$metadata_name);
        }
        else
            return ($user->gamepoints);
    }

    function gamepoints_reset_user($user_guid, $group_guid = null) {
        $context = elgg_get_context();
        elgg_set_context('gamepoints');
        if ($group_guid) {
            //Borramos los objetos del usuario
            $options = array('type' => 'object', 'subtype' => 'gamepoint', 'container_guid' => $user_guid, 'metadata_case_sensitive' => false,'metadata_name_value_pairs' => array('name' => 'group_guid', 'value' => $group_guid), 'limit' => null);
            $gamepoints = elgg_get_entities_from_metadata($options);
            foreach ($gamepoints as $gamepoint) {
                $gamepoint->delete();
            }
            //Borramos los objetos de los subgrupos a los que pertenece el usuario
            $subgroups = elgg_get_entities_from_relationship(array(
                'type_subtype_pairs' => array('group' => 'lbr_subgroup'),
                'container_guids' => $group_guid,
                'relationship' => 'member',
                'inverse_relationship' => false,
                'relationship_guid' => $user_guid,
            ));
	    if ($subgroups) {
               $options = array('type' => 'object', 'subtype' => 'gamepoint', 'container_guid' => $subgroups[0]->guid, 'metadata_case_sensitive' => false,'metadata_name_value_pairs' => array('name' => 'group_guid', 'value' => $group_guid), 'limit' => null);
               $gamepoints = elgg_get_entities_from_metadata($options);
               foreach ($gamepoints as $gamepoint) {
                  $gamepoint->delete();
               }
	    }

            $user = get_entity($user_guid);
            $metadata_name = 'gamepoints_'.$group_guid;
            $user->$metadata_name = "";
        }
        else {
            $options = array('type' => 'object', 'subtype' => 'gamepoint', 'container_guid' => $user_guid, 'metadata_case_sensitive' => false,
                    'metadata_name_value_pairs' => array('name' => 'group_guid', 'value' => null), 'limit' => null);
            $gamepoints = elgg_get_entities_from_metadata($options);
            foreach ($gamepoints as $gamepoint) {
                $gamepoint->delete();
            }
            $user = get_entity($user_guid);
            $user->gamepoints = "";
        }
        elgg_set_context($context);
    }

    function gamepoints_update($gamepoints_guid, $new_points) {
        $context = elgg_get_context();
        elgg_set_context('gamepoints');
        $gamepoints = get_entity($gamepoints_guid);
        $old_points = (int) $gamepoints->points;

	if (empty($new_points) || !(is_numeric($new_points)))
	   $gamepoints->delete();
	else 
           $gamepoints->points = (int) $new_points;
        $user = get_entity($gamepoints->container_guid);
        if ($gamepoints->group_guid){
            $metadata_name = 'gamepoints_'.$gamepoints->group_guid;
            if ($gamepoints->subgroup){
                $subgroup_members = get_group_members($gamepoints->container_guid,null);
                foreach ($subgroup_members as $member){
                        $member->$metadata_name = $member->$metadata_name - $old_points + $new_points;
                }
            }
            else
                $user->$metadata_name = $user->$metadata_name - $old_points + $new_points;
        }
        else {
            $user->gamepoints = $user->gamepoints - $old_points + $new_points;
        }
        elgg_set_context($context);
    }

    function gamepoints_get_entity_points($entity_guid) {
        $options = array('type' => 'object', 'subtype' => 'gamepoint', 'metadata_case_sensitive' => false,
                    'metadata_name_value_pairs' => array('name' => 'rated_entity', 'value' => $entity_guid), 'limit' => 1);
        $gamepoints = elgg_get_entities_from_metadata($options);
        if ($gamepoints)
            return $gamepoints[0]->points;
        else
            return false;
    }
    
    function gamepoints_get_entity($entity_guid) {
        $options = array('type' => 'object', 'subtype' => 'gamepoint', 'metadata_case_sensitive' => false,
                    'metadata_name_value_pairs' => array('name' => 'rated_entity', 'value' => $entity_guid), 'limit' => 1);
        $gamepoints = elgg_get_entities_from_metadata($options);
        if ($gamepoints)
            return $gamepoints[0];
        else
            return false;
    }

    function gamepoints_remove_by_entity($entity_guid) {
        $context = elgg_get_context();
        elgg_set_context('gamepoints');
        
        $options = array('type' => 'object', 'subtype' => 'gamepoint', 'metadata_case_sensitive' => false,
                    'metadata_name_value_pairs' => array('name' => 'rated_entity', 'value' => $entity_guid), 'limit' => 1);
        $gamepoints = elgg_get_entities_from_metadata($options);
        if ($gamepoints)
            gamepoints_remove ($gamepoints[0]->guid);
        
        elgg_set_context($context);
    }

    //Permisos para editar objetos
    function users_can_edit($hook_name, $entity_type, $return_value, $params) {
        if ($params['entity']->type ==  "user" && elgg_get_context() == 'gamepoints')
            return true;
    }
    
    function gamepoints_can_edit($hook_name, $entity_type, $return_value, $params) {
        if ($params['entity']->getSubtype() ==  "gamepoint" && elgg_get_context() == 'gamepoints' && is_admin_or_teacher($params['entity']->group_guid)){
            return true;
        }
    }

    function remove_gamepoints($event, $object_type, $object) {
        if ($object->getSubtype() == 'gamepoint') {
            $context = elgg_get_context();
            elgg_set_context('gamepoints');
            $user = get_entity($object->container_guid);
            $group_guid = $object->group_guid;
            $points = (int) $object->points;

            if ($group_guid)
                $metadata_name = 'gamepoints_'.$group_guid;
            else
                $metadata_name = 'gamepoints';

            if ($object->subgroup) {
                $subgroup_members = get_group_members($object->container_guid,null);
                foreach ($subgroup_members as $member){
                    $member->$metadata_name = $member->$metadata_name - $points;
                }
            }
            else
                $user->$metadata_name = $user->$metadata_name - $points;
            elgg_set_context($context);
        }
    }

    function is_admin_or_teacher($container_guid, $user_guid = null) {
            if ($user_guid)
                $user = get_entity($user_guid);
            else
                $user = elgg_get_logged_in_user_entity();
            if ($user->isAdmin())
                return true;
            $container = get_entity($container_guid);
            if ($container instanceof ElggGroup) {
                // Caso subgrupos: también hemos de tener en cuenta los profesores del grupo
                if ($container->getContainerEntity() instanceof ElggGroup){
                    if ($user->guid == $container->owner_guid
                        || (elgg_is_active_plugin('group_tools') && (check_entity_relationship($user->guid,'group_admin', $container->getContainerGUID() ))))
                            return true;
                    else
                        return false;
                }
                // Caso grupos
                elseif ($user->guid == $container->owner_guid
                    || (elgg_is_active_plugin('group_tools') && (check_entity_relationship($user->guid,'group_admin', $container->guid))))
                        return true;
                else
                    return false;
            }
            else {
                return $user->isAdmin();
            }
    }

    elgg_register_plugin_hook_handler('permissions_check','user','users_can_edit');
    elgg_register_plugin_hook_handler('permissions_check','object','gamepoints_can_edit');

    elgg_register_event_handler('init','system','gamepoints_init');
    //elgg_register_event_handler('pagesetup','system','gamepoints_menu');
    elgg_register_event_handler('delete', 'object', 'remove_gamepoints');


    $action_base = elgg_get_plugins_path() . 'gamepoints/actions/gamepoints';
    elgg_register_action("gamepoints/delete","$action_base/delete.php");
    elgg_register_action("gamepoints/reset","$action_base/reset.php");
    elgg_register_action("gamepoints/add","$action_base/add.php");
    elgg_register_action("gamepoints/export_reset","$action_base/export_reset.php");
    elgg_register_action("gamepoints/export_import","$action_base/export_import.php");
    elgg_register_action("gamepoints/import_sum","$action_base/import_sum.php");
    elgg_register_action("gamepoints/delete_ranking","$action_base/delete_ranking.php");
?>
