<?php
/**
 * Group widget for Elica
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 */

$group = elgg_get_page_owner_entity();

if ($group->elica_enable == "no") {
	return true;
}

$all_link = elgg_view('output/url', array(
	'href' => "elica/group/$group->guid/all",
	'text' => elgg_echo('link:view:all'),
	'is_trusted' => true,
));

elgg_push_context('widgets');
$options = array(
	'type' => 'object',
	'subtype' => 'elica_simulation',
	'container_guid' => elgg_get_page_owner_guid(),
	'metadata_name_value_pairs' => array('name' => 'status', 'value' => 'published'),
	'limit' => 6,
	'full_view' => false,
	'pagination' => false,
);
$content = elgg_list_entities_from_metadata($options);
elgg_pop_context();

if (!$content) {
	$content = '<p>' . elgg_echo('elica:none') . '</p>';
}

$new_link = elgg_view('output/url', array(
	'href' => "elica/add/$group->guid",
	'text' => elgg_echo('elica:write'),
	'is_trusted' => true,
));

echo elgg_view('groups/profile/module', array(
	'title' => elgg_echo('elica:group'),
	'content' => $content,
	'all_link' => $all_link,
	'add_link' => $new_link,
));
