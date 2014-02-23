<?php
/**
 * Save simulation entity
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 */

elgg_make_sticky_form('elica_simulation');

$save = (bool)get_input('save');

// store errors to pass along
$error = FALSE;
$error_forward_url = REFERER;
$user = elgg_get_logged_in_user_entity();

// edit or create a new entity
$guid = get_input('guid');

if ($guid) {
	$entity = get_entity($guid);
	if (elgg_instanceof($entity, 'object', 'elica_simulation') && $entity->canEdit()) {
		$simulation = $entity;
	} else {
		register_error(elgg_echo('elica:error:simulation_not_found'));
		forward(get_input('forward', REFERER));
	}

	// save some data for revisions once we save the new edit
	$revision_text = $simulation->description;
	$new_post = $simulation->new_post;
} else {
	$simulation = new ElggSimulation();
	$simulation->subtype = 'elica_simulation';
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
	'container_guid' => (int)get_input('container_guid'),
);

// fail if a required entity isn't set
$required = array('title', 'description');


// load from POST and do sanity and access checking
foreach ($values as $name => $default) {
	if ($name === 'title') {
		$value = htmlspecialchars(get_input('title', $default, false), ENT_QUOTES, 'UTF-8');
	} else {
		$value = get_input($name, $default);
	}

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
		$simulation->$name = $value;
	}
}

// only try to save base entity if no errors
if (!$error) {
	if ($simulation->save()) {
		// remove sticky form entries
		elgg_clear_sticky_form('elica_simulation');

		// no longer a brand new post.
		$simulation->deleteMetadata('new_post');

		// if this was an edit, create a revision annotation
		if (!$new_post && $revision_text) {
			$simulation->annotate('run_revision', $revision_text);
		}

		system_message(elgg_echo('elica:message:simulation:saved'));

		$status = $simulation->status;

		// add to river if changing status or published, regardless of new post
		// because we remove it for drafts.
		if (($new_post || $old_status == 'draft') && $status == 'published') {
			add_to_river('river/object/elica/simulation/create', 'create', $simulation->owner_guid, $simulation->getGUID());

			// we only want notifications sent when post published
			register_notification_object('object', 'elica_simulation', elgg_echo('elica:newpost'));
			elgg_trigger_event('publish', 'object', $simulation);

			// reset the creation time for posts that move from draft to published
			if ($guid) {
				$simulation->time_created = time();
				$simulation->save();
			}
		} elseif ($old_status == 'published' && $status == 'draft') {
			elgg_delete_river(array(
				'object_guid' => $simulation->guid,
				'action_type' => 'create',
			));
		}

		if ($simulation->status == 'published' || $save == false) {
			forward($simulation->getURL());
		} else {
			forward("elica/simulation/edit/$simulation->guid");
		}
	} else {
		register_error(elgg_echo('elica:error:simulation:cannot_save'));
		forward($error_forward_url);
	}
} else {
	register_error($error);
	forward($error_forward_url);
}