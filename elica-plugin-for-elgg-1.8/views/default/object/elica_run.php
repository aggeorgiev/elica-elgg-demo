<?php
/**
 * View for simulation's run objects
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 */

$full = elgg_extract('full_view', $vars, FALSE);
$run = elgg_extract('entity', $vars, FALSE);

if (!$run) {
	return TRUE;
}

$owner = $run->getOwnerEntity();
$container = $run->getContainerEntity();
$categories = elgg_view('output/categories', $vars);
$excerpt = $run->excerpt;
if (!$excerpt) {
	$excerpt = elgg_get_excerpt($run->description);
}

$simulation_icon = elgg_view_entity_icon($run, 'small');

$owner_link = elgg_view('output/url', array(
	'href' => "elica/owner/$owner->username",
	'text' => $owner->name,
	'is_trusted' => true,
));
$author_text = elgg_echo('elica:by:author', array($owner_link));
$date = elgg_echo('elica:last:updated', array(elgg_view_friendly_time($run->time_updated)));

// The "on" status changes for comments, so best to check for !Off
if ($run->comments_on != 'Off') {
	$comments_count = $run->countComments();
	//only display if there are commments
	if ($comments_count != 0) {
		$text = elgg_echo("comments") . " ($comments_count)";
		$comments_link = elgg_view('output/url', array(
			'href' => $run->getURL() . '#elica-comments',
			'text' => $text,
			'is_trusted' => true,
		));
	} else {
		$comments_link = '';
	}
} else {
	$comments_link = '';
}

$metadata = elgg_view_menu('entity', array(
	'entity' => $vars['entity'],
	'handler' => 'elica',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
));

//$subtitle = "$date $author_text $comments_link $categories";
$subtitle = "$date $author_text";

// do not show the metadata and controls in widget view
if (elgg_in_context('widgets')) {
	$metadata = '';
}

if ($full) {

	$body = elgg_view('output/longtext', array(
		'value' => $run->description,
		'class' => 'elica-run',
	));

	$params = array(
		'entity' => $run,
		'title' => false,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
	);
	$params = $params + $vars;
	$summary = elgg_view('object/elements/summary', $params);

	echo elgg_view('object/elements/full', array(
		'summary' => $summary,
		'icon' => $simulation_icon,
		'body' => $body,
	));

} else {
	// brief view
	$excerpt = '';

	$params = array(
		'entity' => $run,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
		'content' => $excerpt,
	);
	$params = $params + $vars;

	$list_body = elgg_view('object/elements/summary', $params);
	echo elgg_view_image_block($simulation_icon, $list_body);
}
