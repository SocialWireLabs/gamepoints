<?php

$owner = get_entity($vars['entity']->owner_guid);
$icon = elgg_view_entity_icon($owner, 'small');

$points = sprintf(elgg_echo('gamepoints:object:points'),$vars['entity']->points);

if ($vars['entity']->rated_entity) {
    $rated_entity = get_entity($vars['entity']->rated_entity);
    if ($rated_entity instanceof ElggEntity) {
        if ($rated_entity->getSubtype() == 'answer'){
            //como no tienen título ni vista, mostramos la pregunta a la que responde
            $rated_entity = get_entity($rated_entity->container_guid);
        }
        elseif ($rated_entity->getSubtype() == 'answer_contest'){
            //como no tienen título ni vista, mostramos el concurso a la que responde
            $rated_entity = elgg_get_entities_from_relationship(array(
                'relationship'=> 'answer_contest',
                'relationship_guid'=> $rated_entity->guid,
                'inverse_relationship'=> true,
            ));
            $rated_entity = $rated_entity[0];
        }
        elseif ($rated_entity->getSubtype() == 'cmr_answer'){
            $rated_entity = elgg_get_entities_from_relationship(array(
                'relationship'=> 'answer',
                'relationship_guid'=> $rated_entity->guid,
                'inverse_relationship'=> true,
            ));
            $rated_entity = $rated_entity[0];
        }
        elseif ($rated_entity->getSubtype() == 'test_answer'){
            $rated_entity = elgg_get_entities_from_relationship(array(
                'relationship'=> 'test_answer',
                'relationship_guid'=> $rated_entity->guid,
                'inverse_relationship'=> true,
            ));
            $rated_entity = $rated_entity[0];
        }
        $url = $rated_entity->getURL();
        $title = $rated_entity->title;
        if (!$title)
           $title = $vars['entity']->description;

        $link = "<a href=\"{$url}\">{$title}</a>";

        $info = "<p>$points $link</p>";
    } else{
        $info = "<p>$points {$vars['entity']->description}</p>";
    }
}
else {
    $info = "<p>$points {$vars['entity']->description}</p>";
}

//Identidad y fecha
$container = get_entity($vars['entity']->container_guid);
$friendlytime = elgg_view_friendly_time($vars['entity']->time_created);
$info .= "<p class=\"owner_timestamp\">{$container->name} {$friendlytime}";

//Controles para borrar
$url_vars = elgg_get_site_url();
if (is_admin_or_teacher($vars['entity']->group_guid))
    $info .= " (<a href=\"" . elgg_add_action_tokens_to_url($url_vars . "action/gamepoints/delete?guid={$vars['entity']->guid}")."\">".elgg_echo('gamepoints:delete')."</a>)";

$html .= elgg_view_image_block($icon,$info);

echo $html;
?>
