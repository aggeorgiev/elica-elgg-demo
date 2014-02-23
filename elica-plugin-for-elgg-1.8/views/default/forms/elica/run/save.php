<?php
/**
 * Edit simulation' run form
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 */

$error_forward_url = REFERER;

$simulation_guid = (int)get_input('simulation_guid');
$simulation = get_entity($simulation_guid);

if (!$simulation) {
	register_error(elgg_echo("elica:simulation:notfound"));
	forward($error_forward_url);
}

$container = get_entity($simulation->container_guid);
if ($container instanceof ElggGroup && !can_write_to_container(0, $container->getGUID())) {
	register_error(sprintf(elgg_echo("elica:run:mustbeingroup"), $container->name));
	forward($simulation->getURL());
}


$run = get_entity($vars['guid']);
$vars['entity'] = $run;

$draft_warning = $vars['draft_warning'];
if ($draft_warning) {
	$draft_warning = '<span class="mbm elgg-text-help">' . $draft_warning . '</span>';
}

$action_buttons = '';
$delete_link = '';
$preview_button = '';
$run_type_disabled = false;

if ($vars['guid']) {
	// add a delete button if editing
	$delete_url = "action/elica/run/delete?guid={$vars['guid']}";
	$delete_link = elgg_view('output/confirmlink', array(
		'href' => $delete_url,
		'text' => elgg_echo('delete'),
		'class' => 'elgg-button elgg-button-delete float-alt'
	));
	$simulation = get_simulation_for_run($run);
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

$video_url_label = elgg_echo('elica:run:video:url');
$video_url_input = elgg_view('input/text', array(
    'disabled' => true,
	'name' => 'video_url',
	'id' => 'video_url',
	'value' => $vars['video_url']
));

$image_url_label = elgg_echo('elica:run:image:url');
$image_url_input = elgg_view('input/text', array(
    'disabled' => true,
        'name' => 'image_url',
        'id' => 'image_url',
        'value' => $vars['image_url']
));


/**
Number Of Buildings 1-60
Minimal Floors Of Buildings 10-30
Maximal Floors Of Buildings 20-80
Terrain Flatness 0.0 - 1
*/
$simulation_param_NumberOfBuildings_label = elgg_echo('elica:simulation:param:NumberOfBuildings');
$simulation_param_NumberOfBuildings_input = elgg_view('input/text', array(
	'name' => 'simulation_param_NumberOfBuildings',
	'id' => 'simulation_param_NumberOfBuildings',
	'value' => $vars['simulation_param_NumberOfBuildings']
));

$simulation_param_MinimalFloorsOfBuildings_label = elgg_echo('elica:simulation:param:MinimalFloorsOfBuildings');
$simulation_param_MinimalFloorsOfBuildings_input = elgg_view('input/text', array(
	'name' => 'simulation_param_MinimalFloorsOfBuildings',
	'id' => 'simulation_param_MinimalFloorsOfBuildings',
	'value' => $vars['simulation_param_MinimalFloorsOfBuildings']
));

$simulation_param_MaximalFloorsOfBuildings_label = elgg_echo('elica:simulation:param:MaximalFloorsOfBuildings');
$simulation_param_MaximalFloorsOfBuildings_input = elgg_view('input/text', array(
	'name' => 'simulation_param_MaximalFloorsOfBuildings',
	'id' => 'simulation_param_MaximalFloorsOfBuildings',
	'value' => $vars['simulation_param_MaximalFloorsOfBuildings']
));

$simulation_param_TerrainFlatness_label = elgg_echo('elica:simulation:param:TerrainFlatness');
$simulation_param_TerrainFlatness_input = elgg_view('input/text', array(
	'name' => 'simulation_param_TerrainFlatness',
	'id' => 'simulation_param_TerrainFlatness',
	'value' => $vars['simulation_param_TerrainFlatness']
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
$container_guid_input = elgg_view('input/hidden', array('name' => 'container_guid', 'value' => $container->getGUID()));
$simulation_guid_input = elgg_view('input/hidden', array('name' => 'simulation_guid', 'value' => $simulation->getGUID()));
$guid_input = elgg_view('input/hidden', array('name' => 'guid', 'value' => $vars['guid']));

$between = elgg_echo('elica:string:between');

echo <<<___HTML

$draft_warning

<div>
	<label for="title">$title_label</label>
	$title_input
</div>

<div>
	<label for="description">$body_label</label>
	$body_input
</div>

<div>
        <label for="title">$image_url_label</label>
        $image_url_input
</div>

<div>
	<label for="title">$video_url_label</label>
	$video_url_input
</div>

<div>
	<label for="title">$simulation_param_NumberOfBuildings_label</label>
	$simulation_param_NumberOfBuildings_input
	<div class="elgg-subtext"> $between 1-60 </div>
</div>

<div>
	<label for="title">$simulation_param_MinimalFloorsOfBuildings_label</label>
	$simulation_param_MinimalFloorsOfBuildings_input
	<div class="elgg-subtext"> $between 10-30 </div>
</div>

<div>
	<label for="title">$simulation_param_MaximalFloorsOfBuildings_label</label>
	$simulation_param_MaximalFloorsOfBuildings_input
	<div class="elgg-subtext"> $between 20-80 </div>
</div>

<div>
	<label for="title">$simulation_param_TerrainFlatness_label</label>
	$simulation_param_TerrainFlatness_input
	<div class="elgg-subtext"> $between 0.1-1.0 </div>
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
	$simulation_guid_input

	$action_buttons
</div>

___HTML;
