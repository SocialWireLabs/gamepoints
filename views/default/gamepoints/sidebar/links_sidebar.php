<?php

$owner = $vars['owner'];

$gamepoints_string = elgg_echo('gamepoints:submenu:list');
$add_gamepoints_string = elgg_echo('gamepoints:submenu:add');
$export_gamepoints_string = elgg_echo('gamepoints:submenu:export');

$wwwroot = elgg_get_config('wwwroot');

$links = <<<EOT
<div class="elgg-module elgg-owner-block">
<div class="elgg-head">
    <div class="elgg-image-block clearfix">
        <div class="elgg-body">
    <h3><a href="{$wwwroot}gamepoints/group/{$owner->guid}/all">{$gamepoints_string}</a></h3></div></div>
</div>
<div class="elgg-body">
    <ul class="elgg-menu elgg-menu-owner-block elgg-menu-owner-block-default">
        <li><a href="{$wwwroot}gamepoints/group/{$owner->guid}/all">{$gamepoints_string}</a></li>
EOT;

if (is_admin_or_teacher($owner->guid)) {
    $links .= <<<EOT
        <li><a href="{$wwwroot}gamepoints/add/{$owner->guid}">{$add_gamepoints_string}</a></li>
        <li><a href="{$wwwroot}gamepoints/export/{$owner->guid}">{$export_gamepoints_string}</a></li>
EOT;
}

$links .= <<<EOT
    </ul>
</div>
</div>
EOT;

echo $links;
