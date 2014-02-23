<?php
/**
 * Add run river view
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 */

$subject = $vars['item']->getSubjectEntity();
$object = $vars['item']->getObjectEntity();
$simulation = get_entity($object->simulation_guid);
$container = $object->getContainerEntity();

if ($simulation) { // prevent error if simulation has been deleted
	$subject_link = elgg_view('output/url', array(
		'href' => $subject->getURL(),
		'text' => $subject->name,
		'class' => 'elgg-river-subject',
		'is_trusted' => true,
	));

	$object_link = elgg_view('output/url', array(
		'href' => $object->getURL(),
		'text' => elgg_echo('elica:river:run'),
		'class' => 'elgg-river-object',
		'is_trusted' => true,
	));

	$simulation_link = elgg_view('output/url', array(
		'href' => $simulation->getURL(),
		'text' => $simulation->title,
		'class' => 'elgg-river-object',
		'is_trusted' => true,
	));

	$group_link = elgg_view('output/url', array(
		'href' => $container->getURL(),
		'text' => $container->name,
		'is_trusted' => true,
	));
	$group_string = elgg_echo('river:ingroup', array($group_link));

	$excerpt = strip_tags(elgg_get_excerpt($object->description, 100));

	$summary = elgg_echo("elica:river:run", array($subject_link, $object_link, $simulation_link, $group_string));

	echo elgg_view('river/elements/layout', array(
		'item' =>  $vars['item'],
		'summary' => $summary,
		'message' => $excerpt,
	));
}