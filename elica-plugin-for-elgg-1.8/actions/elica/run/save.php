<?php
/**
 * Save Simulation Run
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 */

elgg_make_sticky_form('elica_simulation_run');

$save = (bool)get_input('save');

// store errors to pass along
$error = FALSE;
$error_forward_url = REFERER;
$user = elgg_get_logged_in_user_entity();

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

// edit or create a new entity
$guid = get_input('guid');

if ($guid) {
	$entity = get_entity($guid);
	if (elgg_instanceof($entity, 'object', 'elica_simulation_run') && $entity->canEdit()) {
		$run = $entity;
	} else {
		register_error(elgg_echo('elica:error:run_not_found'));
		forward(get_input('forward', REFERER));
	}

	// save some data for revisions once we save the new edit
	$revision_text = $run->description;
	$new_post = $run->new_post;
} else {
	$run = new ElggSimulationRun();
	$run->subtype = 'elica_simulation_run';
	$new_post = TRUE;
}

// set defaults and required values.
$values = array(
	'title' => '',
	'description' => '',
	'status' => 'draft',
	'access_id' => ACCESS_DEFAULT,
	'comments_on' => 'On',
	'excerpt' => '',
	'tags' => '',
	'video_url' => get_input('video_url'),
	'image_url' => get_input('image_url'),
        'simulation_param_NumberOfBuildings' => get_input('simulation_param_NumberOfBuildings'),
        'simulation_param_MinimalFloorsOfBuildings' => get_input('simulation_param_MinimalFloorsOfBuildings'),
        'simulation_param_MaximalFloorsOfBuildings' => get_input('simulation_param_MaximalFloorsOfBuildings'),
        'simulation_param_TerrainFlatness' => get_input('simulation_param_TerrainFlatness'),
	'container_guid' => (int)get_input('container_guid'),
	'simulation_guid' => (int)get_input('simulation_guid'),
);

// fail if a required entity isn't set
$required = array('title', 'description', 
    'simulation_param_NumberOfBuildings',
    'simulation_param_MinimalFloorsOfBuildings',
    'simulation_param_MaximalFloorsOfBuildings',
    'simulation_param_TerrainFlatness');

// load from POST and do sanity and access checking
foreach ($values as $name => $default) {
	if ($name === 'title') {
		$value = htmlspecialchars(get_input('title', $default, false), ENT_QUOTES, 'UTF-8');
	} else {
		$value = get_input($name, $default);
	}
	echo $name;
	if (in_array($name, $required) && empty($value)) {
		$error = elgg_echo("elica:error:missing:$name");
	}

	if ($error) {
		break;
	}

	switch ($name) {
		case 'tags':
			$values[$name] = string_to_tag_array($value);
			break;

		case 'excerpt':
			if ($value) {
				$values[$name] = elgg_get_excerpt($value);
			}
			break;

		case 'container_guid':
			// this can't be empty or saving the base entity fails
			if (!empty($value)) {
				if (can_write_to_container($user->getGUID(), $value)) {
					$values[$name] = $value;
				} else {
					$error = elgg_echo("elica:error:cannot_write_to_container");
				}
			} else {
				unset($values[$name]);
			}
			break;

		default:
			$values[$name] = $value;
			break;
	}
}

// assign values to the entity, stopping on error.
if (!$error) {
	foreach ($values as $name => $value) {
		$run->$name = $value;
	}
}

// only try to save base entity if no errors
if (!$error) {
	if ($run->save()) {
    try {
        $elica_api = elica_get_api_object();
        $url = sprintf('runservice/1.0/run/%s', $run->guid);
        
        $parameters =  array(
                          "run" => array (
                            "simulationId" => 1,
                            "title" => $run->title,
                            "description" => $run->description,
                            "simulationParams" => array (
                                "NumberOfBuildings" => $run->simulation_param_NumberOfBuildings,
                                "MinimalFloorsOfBuildings" => $run->simulation_param_MinimalFloorsOfBuildings,
                                "MaximalFloorsOfBuildings" => $run->simulation_param_MaximalFloorsOfBuildings,
                                "TerrainFlatness" => $run->simulation_param_TerrainFlatness
                            ),
                            "encodingOptions" => array (
                                "format" => "mp4",
                                "width" => 640,
                                "height" => 480
                            )
                          )
                        );
                        
        $response = $elica_api->post($url, $parameters);        
        $run->video_url = $response->run->url;
	$run->image_url = $response->run->image;
        $run->save();
    } catch (Exception $e) {
        //TODO:
    }
        
		// remove sticky form entries
		elgg_clear_sticky_form('elica_simulation_run');

		// no longer a brand new post.
		$run->deleteMetadata('new_post');

		// if this was an edit, create a revision annotation
		if (!$new_post && $revision_text) {
			$run->annotate('run_revision', $revision_text);
		}

		system_message(elgg_echo('elica:message:saved'));

		$status = $run->status;

		// add to river if changing status or published, regardless of new post
		// because we remove it for drafts.
		if (($new_post || $old_status == 'draft') && $status == 'published') {
			add_to_river('river/object/elica/create', 'create', $run->owner_guid, $run->getGUID());

			// we only want notifications sent when post published
			register_notification_object('object', 'elica', elgg_echo('elica:newpost'));
			elgg_trigger_event('publish', 'object', $run);

			// reset the creation time for posts that move from draft to published
			if ($guid) {
				$run->time_created = time();
				$run->save();
			}
		} elseif ($old_status == 'published' && $status == 'draft') {
			elgg_delete_river(array(
				'object_guid' => $run->guid,
				'action_type' => 'create',
			));
		}

		if ($new_post && !add_entity_relationship($simulation->getGUID(), "run", $run->getGUID())) {
			register_error(elgg_echo("elica:run:attacherror"));
			forward($simulation->getURL());
		}			
		
		if ($run->status == 'published' || $save == false) {
			forward($run->getURL());
		} else {
			forward("elica/run/edit/$run->guid");
		}
		
		forward($simulation->getURL());
		
	} else {
		register_error(elgg_echo('elica:error:cannot_save'));
		forward($error_forward_url);
	}
} else {
	register_error($error);
	forward($error_forward_url);
}