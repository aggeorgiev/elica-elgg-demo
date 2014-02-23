<?php
/**
 * Edit simulation form
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 */

$simulation = get_entity($vars['guid']);
$vars['entity'] = $simulation;

$draft_warning = $vars['draft_warning'];
if ($draft_warning) {
	$draft_warning = '<span class="mbm elgg-text-help">' . $draft_warning . '</span>';
}

$action_buttons = '';
$delete_link = '';
$preview_button = '';
$simulation_type_disabled = false;

if ($vars['guid']) {
	// add a delete button if editing
	$delete_url = "action/elica/simulation/delete?guid={$vars['guid']}";
	$delete_link = elgg_view('output/confirmlink', array(
		'href' => $delete_url,
		'text' => elgg_echo('delete'),
		'class' => 'elgg-button elgg-button-delete float-alt'
	));
	$simulation_type_disabled = true;
}

$save_button = elgg_view('input/submit', array(
	'value' => elgg_echo('save'),
	'name' => 'save',
));
$action_buttons = $save_button . $preview_button . $delete_link;

$title_label = elgg_echo('elica:title');
$title_input = elgg_view('input/text', array(
	'name' => 'title',
	'id' => 'title',
	'value' => $vars['title']
));

/**
$excerpt_label = elgg_echo('elica:excerpt');
$excerpt_input = elgg_view('input/text', array(
	'name' => 'excerpt',
	'id' => 'excerpt',
	'value' => _elgg_html_decode($vars['excerpt'])
));
*/

$body_label = elgg_echo('elica:description');
$body_input = elgg_view('input/longtext', array(
	'name' => 'description',
	'id' => 'description',
	'value' => $vars['description']
));

//TODO: take the available simulations from WADL document
$simulation_type_label = elgg_echo('elica:simulation:type');
$simulation_type_input = elgg_view('input/dropdown', array(
	'disabled' => $simulation_type_disabled,
	'name' => 'simulation_id',
	'id' => 'simulation_id',
	'value' => $vars['simulation_id'],
	'options_values' => array(
		'1' => elgg_echo('elica:simulation:options:flood'),
	)
));

$save_status = elgg_echo('elica:save_status');
if ($vars['guid']) {
	$entity = get_entity($vars['guid']);
	$saved = date('F j, Y @ H:i', $entity->time_created);
} else {
	$saved = elgg_echo('elica:never');
}

$status_label = elgg_echo('elica:status');
$status_input = elgg_view('input/dropdown', array(
	'name' => 'status',
	'id' => 'status',
	'value' => $vars['status'],
	'options_values' => array(
		'draft' => elgg_echo('elica:status:draft'),
		'published' => elgg_echo('elica:status:published')
	)
));

$comments_label = elgg_echo('comments');
$comments_input = elgg_view('input/dropdown', array(
	'name' => 'comments_on',
	'id' => 'comments_on',
	'value' => $vars['comments_on'],
	'options_values' => array('On' => elgg_echo('on'), 'Off' => elgg_echo('off'))
));

$tags_label = elgg_echo('tags');
$tags_input = elgg_view('input/tags', array(
	'name' => 'tags',
	'id' => 'tags',
	'value' => $vars['tags']
));

$access_label = elgg_echo('access');
$access_input = elgg_view('input/access', array(
	'name' => 'access_id',
	'id' => 'access_id',
	'value' => $vars['access_id']
));

$categories_input = elgg_view('input/categories', $vars);

// hidden inputs
$container_guid_input = elgg_view('input/hidden', array('name' => 'container_guid', 'value' => elgg_get_page_owner_guid()));
$guid_input = elgg_view('input/hidden', array('name' => 'guid', 'value' => $vars['guid']));


echo <<<___HTML

$draft_warning

<div>
	<label for="title">$title_label</label>
	$title_input
</div>

<div>
	<label for="excerpt">$excerpt_label</label>
	$excerpt_input
</div>

<div>
	<label for="description">$body_label</label>
	$body_input
</div>

<div>
	<label for="simulation_type">$simulation_type_label</label>
	$simulation_type_input
</div>

<div>
	<label for="tags">$tags_label</label>
	$tags_input
</div>

$categories_input

<div>
	<label for="comments_on">$comments_label</label>
	$comments_input
</div>

<div>
	<label for="access_id">$access_label</label>
	$access_input
</div>

<div>
	<label for="status">$status_label</label>
	$status_input
</div>

<div class="elgg-foot">
	<div class="elgg-subtext mbm">
	$save_status <span class="elica-save-status-time">$saved</span>
	</div>

	$guid_input
	$container_guid_input

	$action_buttons
</div>

___HTML;
