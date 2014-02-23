<?php
/**
 * Simulation runs widget content view
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 */

$content_type = $vars['entity']->content_type;

// Get the current page's owner
$page_owner = page_owner_entity();
if ($page_owner === false || is_null($page_owner)) {
	$page_owner = $_SESSION['user'];
	set_page_owner($page_owner->getGUID());
}

$num = $vars['entity']->num_display;

if ($content_type == 'mine') {
	$objects = $page_owner->getObjects('elica', $num);
	$count = $page_owner->countObjects('elica');
} else if ($content_type == 'friends') {
	$objects = get_user_friends_objects($page_owner->getGUID(), 'elica', $num);
	$count = count_user_friends_objects($page_owner->getGUID(), 'elica');
} else { // site
	$options = array(
		'type' => 'object',
		'subtype' => 'elica_simulation',
		'limit' => $num,
	);
	$objects = elgg_get_entities($options);
	$options['count'] = true;
	$count = elgg_get_entities($options);
}

if (is_array($objects) && sizeof($objects) > 0) {
	foreach ($objects as $object) {
		echo elgg_view_entity($object);
	}
}
