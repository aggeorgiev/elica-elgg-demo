<?php
/**
 * Common library of functions used by Elica Services.
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 *
 * Get the API wrapper object
 * 
 * @return ElicaAPI|null
 */
function elica_get_api_object() {
	$host = elgg_get_plugin_setting('host', 'elica');
	if (!($host)) {
		return null;
	}

	$api = new ElicaAPI();
	if ($api) {
		$api->host = $host;
	}
	return $api;
}

/**
 * Get page components to list a user's or all simulations.
 *
 * @param int $container_guid The GUID of the page owner or NULL for all simulations
 * @return array
 */
function elica_simulation_get_page_content_list($container_guid = NULL) {

	$return = array();
	$return['filter_context'] = $container_guid ? 'mine' : 'all';

	$options = array(
		'type' => 'object',
		'subtype' => 'elica_simulation',
		'full_view' => false,
	);

	$current_user = elgg_get_logged_in_user_entity();

	if ($container_guid) {
		// access check for closed groups
		group_gatekeeper();

		$options['container_guid'] = $container_guid;
		$container = get_entity($container_guid);
		if (!$container) {

		}
		$return['title'] = elgg_echo('elica:title:user_simulations', array($container->name));

		$crumbs_title = $container->name;
		elgg_push_breadcrumb($crumbs_title);

		if ($current_user && ($container_guid == $current_user->guid)) {
			$return['filter_context'] = 'mine';
		} else if (elgg_instanceof($container, 'group')) {
			$return['filter'] = false;
		} else {
			// do not show button or select a tab when viewing someone else's posts
			$return['filter_context'] = 'none';
		}
	} else {
		$return['filter_context'] = 'all';
		$return['title'] = elgg_echo('elica:title:all_simulations');
		elgg_pop_breadcrumb();
		elgg_push_breadcrumb(elgg_echo('elica:simulations'));
	}

	elgg_register_title_button();

	// show all simulations for admin or users looking at their own simulations
	// show only published posts for other users.
	$show_only_published = true;
	if ($current_user) {
		if (($current_user->guid == $container_guid) || $current_user->isAdmin()) {
			$show_only_published = false;
		}
	}
	if ($show_only_published) {
		$options['metadata_name_value_pairs'] = array(
			array('name' => 'status', 'value' => 'published'),
		);
	}

	$list = elgg_list_entities_from_metadata($options);
	if (!$list) {
		$return['content'] = elgg_echo('elica:simulation:none');
	} else {
		$return['content'] = $list;
	}

	return $return;
}

/**
 * Get page components to list of the user's friends' simulations.
 *
 * @param int $user_guid
 * @return array
 */
function elica_simulation_get_page_content_friends($user_guid) {

	$user = get_user($user_guid);
	if (!$user) {
		forward('elica/all');
	}

	$return = array();

	$return['filter_context'] = 'friends';
	$return['title'] = elgg_echo('elica:title:friends');

	$crumbs_title = $user->name;
	elgg_push_breadcrumb($crumbs_title, "elica/owner/{$user->username}");
	elgg_push_breadcrumb(elgg_echo('friends'));

	elgg_register_title_button();

	if (!$friends = get_user_friends($user_guid, ELGG_ENTITIES_ANY_VALUE, 0)) {
		$return['content'] .= elgg_echo('friends:none:you');
		return $return;
	} else {
		$options = array(
			'type' => 'object',
			'subtype' => 'elica_simulation',
			'full_view' => FALSE,
		);

		foreach ($friends as $friend) {
			$options['container_guids'][] = $friend->getGUID();
		}

		// admin / owners can see any posts
		// everyone else can only see published posts
		$show_only_published = true;
		$current_user = elgg_get_logged_in_user_entity();
		if ($current_user) {
			if (($user_guid == $current_user->guid) || $current_user->isAdmin()) {
				$show_only_published = false;
			}
		}
		if ($show_only_published) {
			$options['metadata_name_value_pairs'][] = array(
				array('name' => 'status', 'value' => 'published')
			);
		}

		$list = elgg_list_entities_from_metadata($options);
		if (!$list) {
			$return['content'] = elgg_echo('elica:simulation:none');
		} else {
			$return['content'] = $list;
		}
	}

	return $return;
}

/**
 * Get page components to view a simulation.
 *
 * @param int $guid GUID of a simulation entity.
 * @return array
 */
function elica_simulation_get_page_content_read($guid = NULL) {

	$return = array();

	$simulation = get_entity($guid);

	// no header or tabs for viewing an individual simulation
	$return['filter'] = '';

	if (!elgg_instanceof($simulation, 'object', 'elica_simulation')) {
		register_error(elgg_echo('noaccess'));
		$_SESSION['last_forward_from'] = current_page_url();
		forward('');
	}

	$return['title'] = $simulation->title;

	$container = $simulation->getContainerEntity();
	$crumbs_title = $container->name;
	if (elgg_instanceof($container, 'group')) {
		elgg_push_breadcrumb($crumbs_title, "elica/group/$container->guid/all");
	} else {
		elgg_push_breadcrumb($crumbs_title, "elica/owner/$container->username");
	}

	elgg_push_breadcrumb($simulation->title);
	$return['content'] = elgg_view_entity($simulation, array('full_view' => true));
	// check to see if we should allow comments
	if ($simulation->comments_on != 'Off' && $simulation->status == 'published') {
		$return['content'] .= elgg_view_comments($simulation);
	}

	return $return;
}

/**
 * Get page components to edit/create a simulation.
 *
 * @param string  $page     'edit' or 'new'
 * @param int     $guid     GUID of simulation or container
 * @param int     $revision Annotation id for revision to edit (optional)
 * @return array
 */
function elica_simulation_get_page_content_edit($page, $guid = 0, $revision = NULL) {

	elgg_load_js('elgg.elica');

	$return = array(
		'filter' => '',
	);

	$vars = array();
	$vars['id'] = 'elica-simulation-edit';
	$vars['class'] = 'elgg-form-alt';

	$sidebar = '';
	if ($page == 'edit') {
		$simulation = get_entity((int)$guid);

		$title = elgg_echo('elica:simulation:edit');

		if (elgg_instanceof($simulation, 'object', 'elica_simulation') && $simulation->canEdit()) {
			$vars['entity'] = $simulation;

			$title .= ": \"$simulation->title\"";

			if ($revision) {
				$revision = elgg_get_annotation_from_id((int)$revision);
				$vars['revision'] = $revision;
				$title .= ' ' . elgg_echo('elica:edit_revision_notice');

				if (!$revision || !($revision->entity_guid == $guid)) {
					$content = elgg_echo('elica:error:revision_not_found');
					$return['content'] = $content;
					$return['title'] = $title;
					return $return;
				}
			}

			$body_vars = elica_simulation_prepare_form_vars($simulation, $revision);

			elgg_push_breadcrumb($simulation->title, $simulation->getURL());
			elgg_push_breadcrumb(elgg_echo('edit'));
			
			elgg_load_js('elgg.elica');

			$content = elgg_view_form('elica/simulation/save', $vars, $body_vars);
			$sidebar = elgg_view('elica/sidebar/revisions', $vars);
		} else {
			$content = elgg_echo('elica:error:cannot_edit_simulation');
		}
	} else {
		elgg_push_breadcrumb(elgg_echo('elica:simulation:add'));
		$body_vars = elica_simulation_prepare_form_vars(null);

		$title = elgg_echo('elica:simulation:add');
		$content = elgg_view_form('elica/simulation/save', $vars, $body_vars);
	}

	$return['title'] = $title;
	$return['content'] = $content;
	$return['sidebar'] = $sidebar;
	return $return;	
}


/**
 * Pull together simulation variables for the save form
 *
 * @param ElggSimulation       $simulation
 * @return array
 */
function elica_simulation_prepare_form_vars($simulation = NULL, $revision = NULL) {

	// input names => defaults
	$values = array(
		'title' => NULL,
		'description' => NULL,
		'status' => 'published',
		'access_id' => ACCESS_DEFAULT,
		'comments_on' => 'On',
		'excerpt' => NULL,
		'tags' => NULL,
		'container_guid' => NULL,
		'guid' => NULL,
		'draft_warning' => '',
	);

	if ($simulation) {
		foreach (array_keys($values) as $field) {
			if (isset($simulation->$field)) {
				$values[$field] = $simulation->$field;
			}
		}

		if ($simulation->status == 'draft') {
			$values['access_id'] = $simulation->future_access;
		}
	}

	if (elgg_is_sticky_form('elica_simulation')) {
		$sticky_values = elgg_get_sticky_values('elica_simulation');
		foreach ($sticky_values as $key => $value) {
			$values[$key] = $value;
		}
	}
	
	elgg_clear_sticky_form('elica_simulation');

	if (!$simulation) {
		return $values;
	}

	// load the revision annotation if requested
	if ($revision instanceof ElggAnnotation && $revision->entity_guid == $simulation->getGUID()) {
		$values['revision'] = $revision;
		$values['description'] = $revision->value;
	}

	return $values;
}

/**
 * ===============================
 * Simulation run functions
 * ===============================
 */

 /**
 * Get page components to edit/create a simulation run.
 *
 * @param string  $page     'edit' or 'new'
 * @param int     $guid     GUID of simulation or container
 * @param int     $revision Annotation id for revision to edit (optional)
 * @return array
 */
function elica_simulation_run_get_page_content_edit($page, $guid = 0, $revision = NULL) {

	elgg_load_js('elgg.elica');

	$return = array(
		'filter' => '',
	);

	$vars = array();
	$vars['id'] = 'elica-simulation-run-edit';
	$vars['class'] = 'elgg-form-alt';

	$sidebar = '';
	if ($page == 'edit') {
		$run = get_entity((int)$guid);

		$title = elgg_echo('elica:run:edit');

		if (elgg_instanceof($run, 'object', 'elica_simulation_run') && $run->canEdit()) {
			$vars['entity'] = $run;

			$title .= ": \"$run->title\"";

			if ($revision) {
				$revision = elgg_get_annotation_from_id((int)$revision);
				$vars['revision'] = $revision;
				$title .= ' ' . elgg_echo('elica:edit_revision_notice');

				if (!$revision || !($revision->entity_guid == $guid)) {
					$content = elgg_echo('elica:error:revision_not_found');
					$return['content'] = $content;
					$return['title'] = $title;
					return $return;
				}
			}

			$body_vars = elica_simulation_run_prepare_form_vars($run, $revision);

			elgg_push_breadcrumb($run->title, $run->getURL());
			elgg_push_breadcrumb(elgg_echo('edit'));
			
			elgg_load_js('elgg.elica');

			$content = elgg_view_form('elica/run/save', $vars, $body_vars);
			$sidebar = elgg_view('elica/sidebar/revisions', $vars);
		} else {
			$content = elgg_echo('elica:error:cannot_edit_simulation_run');
		}
	} else {
		elgg_push_breadcrumb(elgg_echo('elica:run:add'));
		$body_vars = elica_simulation_run_prepare_form_vars(null);

		$title = elgg_echo('elica:run:add');
		$content = elgg_view_form('elica/run/save', $vars, $body_vars);
	}

	$return['title'] = $title;
	$return['content'] = $content;
	$return['sidebar'] = $sidebar;
	return $return;	
}

/**
 * Pull together simulation run variables for the save form
 *
 * @param ElggSimulationRun       $run
 * @return array 
 */
function elica_simulation_run_prepare_form_vars($run = NULL, $revision = NULL) {

	// input names => defaults
	$values = array(
		'title' => NULL,
		'description' => NULL,
		'status' => 'published',
		'access_id' => ACCESS_DEFAULT,
		'comments_on' => 'On',
		'excerpt' => NULL,
		'tags' => NULL,
		'video_url' => NULL,
		'image_url' => NULL,
		'simulation_param_NumberOfBuildings' => 20,
		'simulation_param_MinimalFloorsOfBuildings' => 20,
		'simulation_param_MaximalFloorsOfBuildings' => 40,
		'simulation_param_TerrainFlatness' => 0.3,        
		'container_guid' => NULL,
		'simulation_guid' => NULL,
		'guid' => NULL,
		'draft_warning' => '',
	);

	if ($run) {
		foreach (array_keys($values) as $field) {
			if (isset($run->$field)) {
				$values[$field] = $run->$field;
			}
		}

		if ($run->status == 'draft') {
			$values['access_id'] = $run->future_access;
		}
	}

	if (elgg_is_sticky_form('elica_simulation_run')) {
		$sticky_values = elgg_get_sticky_values('elica_simulation_run');
		foreach ($sticky_values as $key => $value) {
			$values[$key] = $value;
		}
	}
	
	elgg_clear_sticky_form('elica_simulation_run');

	if (!$run) {
		return $values;
	}

	// load the revision annotation if requested
	if ($revision instanceof ElggAnnotation && $revision->entity_guid == $simulation->getGUID()) {
		$values['revision'] = $revision;
		$values['description'] = $revision->value;
	}

	return $values;
}
