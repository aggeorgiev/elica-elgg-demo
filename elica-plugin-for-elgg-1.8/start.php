<?php
/**
 * Elica Simulations
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 */

elgg_register_event_handler('init', 'system', 'elica_init');

/**
 * Init elica plugin.
 */
function elica_init() {

    $js_url = elgg_get_site_url() . 'mod/elica/vendors/flowplayer/flowplayer.min.js';
    elgg_register_js('elica.flowplayer', $js_url);

	$css_url = elgg_get_site_url() . 'mod/elica/vendors/flowplayer/skin/minimalist.css';
	elgg_register_css('elica.flowplayer.css', $css_url);

    $css_url = elgg_get_site_url() . 'mod/elica/vendors/flowplayer/flowplayer-custom.css';
    elgg_register_css('elica.flowplayer-custom.css', $css_url);

	$base = elgg_get_plugins_path() . 'elica';
	elgg_register_class('ElicaAPI', "$base/vendors/elica/ElicaAPI.php");
	elgg_register_library('elgg:elica', "$base/lib/elica.php");
	elgg_load_library('elgg:elica');

	// register permissions check hook
	elgg_register_plugin_hook_handler("permissions_check", "group", "elica_can_manage_simulation_hook");
	
	// for icon
	$icon_sizes = array(
					'topbar' => array('w' => 16, 'h' => 16, 'square' => TRUE, 'upscale' => TRUE),
					'tiny' => array('w' => 25, 'h' => 25, 'square' => TRUE, 'upscale' => TRUE),
					'small' => array('w' => 48, 'h' => 48, 'square' => TRUE, 'upscale' => TRUE),
					'medium' => array('w' => 100, 'h' => 100, 'square' => TRUE, 'upscale' => TRUE),
					'large' => array('w' => 200, 'h' => 200, 'square' => FALSE, 'upscale' => FALSE),
					'master' => array('w' => 550, 'h' => 550, 'square' => FALSE, 'upscale' => FALSE),
				);
	elgg_set_config('icon_sizes', $icon_sizes);	
	elgg_register_plugin_hook_handler ('entity:icon:url', 'object', 'elica_icon_url_override');
	
	// add to the main css
	elgg_extend_view('css/elgg', 'elica/css');

	// routing of urls
	elgg_register_page_handler('elica', 'elica_page_handler'); //coolstone

	// override the default url to view a simulation run object
	elgg_register_entity_url_handler('object', 'elica_simulation', 'elica_simulation_url_handler');
	elgg_register_entity_url_handler('object', 'elica_simulation_run', 'elica_simulation_run_url_handler');
	
	// notifications - need to register for unique event because of draft/published status
	elgg_register_event_handler('publish', 'object', 'object_notifications');
	elgg_register_plugin_hook_handler('notify:entity:message', 'object', 'elica_notify_message');

	// add simulations link to profile
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'elica_owner_block_menu');	
	
	// Register for search.
	elgg_register_entity_type('object', 'elica_simulation');
	elgg_register_entity_type('object', 'elica_simulation_run');

	// Add group option
	elgg_extend_view('groups/tool_latest', 'elica/group_module');
	add_group_tool_option('elica', elgg_echo('elica:enablesimulation'), true);

	// add a elica widget
	elgg_register_widget_type('elica', elgg_echo('elica'), elgg_echo('elica:widget:description'), "groups");

	// register actions
	$action_path = elgg_get_plugins_path() . 'elica/actions/elica';
	elgg_register_action('elica/simulation/save', "$action_path/simulation/save.php");
	elgg_register_action('elica/simulation/delete', "$action_path/simulation/delete.php");
	elgg_register_action('elica/run/save', "$action_path/run/save.php");
	elgg_register_action('elica/run/delete', "$action_path/run/delete.php");

	// entity menu
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'elica_entity_menu_setup');

	// ecml
	elgg_register_plugin_hook_handler('get_views', 'ecml', 'elica_ecml_views_hook');	
}

/**
 * Dispatches Elica pages.
 * URLs take the form of
 *  All simulations:       elica/all
 *  User's simulations:    elica/owner/<username>
 *  Friends' simulations:   elica/friends/<username>
 *  Simulation:       elica/view/<guid>/<title>
 *  New simulation:        elica/add/<guid>
 *  Edit simulation:       elica/edit/<guid>/<revision>
 *  Group's simulations:      elica/group/<guid>/all
 *
 * Title is ignored
 *
 *
 * @param array $page
 * @return bool
 */
function elica_page_handler($page) {

	elgg_load_library('elgg:elica');

	// add a site navigation item
	$item = new ElggMenuItem('elica', elgg_echo('elica:simulations'), 'elica/all');
	elgg_register_menu_item('site', $item);	
	
	// push all runs breadcrumb
	elgg_push_breadcrumb(elgg_echo('elica:simulations'), "elica/all");

	if (!isset($page[0])) {
		$page[0] = 'all';
	}

	$page_type = $page[0];
	switch ($page_type) {
		case 'owner':
			$user = get_user_by_username($page[1]);
			$params = elica_simulation_get_page_content_list($user->guid);
			break;
		case 'friends':
			$user = get_user_by_username($page[1]);
			$params = elica_simulation_get_page_content_friends($user->guid);
			break;
		case 'read':
		case 'view':
			$params = elica_simulation_get_page_content_read($page[1]);
			break;
		case 'add':
			gatekeeper();
			$params = elica_simulation_get_page_content_edit($page_type, $page[1]);
			break;
		case 'edit':
			gatekeeper();
			$params = elica_simulation_get_page_content_edit($page_type, $page[1], $page[2]);
			break;
		case 'group':
			if ($page[2] == 'all') {
				$params = elica_simulation_get_page_content_list($page[1]);
			} 
			break;
		case 'all':
			$params = elica_simulation_get_page_content_list();
			break;
		case 'run':
			gatekeeper();
			set_input('simulation_guid', $page[2]);
			$params = elica_simulation_run_get_page_content_edit($page[1], $page[2]);
			break;
		default:
			return false;
	}

	$body = elgg_view_layout('content', $params);

	echo elgg_view_page($params['title'], $body);
	return true;
}

/**
 * Format and return the URL for simulations.
 *
 * @param ElggObject $entity Elica object
 * @return string URL of simulation.
 */
function elica_simulation_url_handler($entity) {
	if (!$entity->getOwnerEntity()) {
		// default to a standard view if no owner.
		return FALSE;
	}
	$friendly_title = elgg_get_friendly_title($entity->title);

	return "elica/view/{$entity->guid}/$friendly_title";
}

/**
 * Format and return the URL for simulations.
 *
 * @param ElggObject $entity Elica object
 * @return string URL of simulation.
 */
function elica_simulation_run_url_handler($run) {
	$simulation = get_simulation_for_run($run);
	if ($simulation) {
		return $simulation->getURL() . "#simulation-run-" . $run->getGUID();
	} else {
		return '';
	}
}

/**
 * Add a menu item to an ownerblock
 */
function elica_owner_block_menu($hook, $type, $return, $params) {
	if (elgg_instanceof($params['entity'], 'user')) {
		$url = "elica/owner/{$params['entity']->username}";
		$item = new ElggMenuItem('elica', elgg_echo('elica'), $url);
		$return[] = $item;
	} else {
		if ($params['entity']->elica_enable != 'no') {
			$url = "elica/group/{$params['entity']->guid}/all";
			$item = new ElggMenuItem('elica', elgg_echo('elica:group'), $url);
			$return[] = $item;
		}
	}

	return $return;
}

/**
 * Add particular run links/info to entity menu
 */
function elica_entity_menu_setup($hook, $type, $return, $params) {
	if (elgg_in_context('widgets')) {
		return $return;
	}

	$entity = $params['entity'];
	$handler = elgg_extract('handler', $params, false);
	if ($handler != 'elica') {
		return $return;
	}

	if ($entity->status != 'published') {
		// draft status replaces access
		foreach ($return as $index => $item) {
			if ($item->getName() == 'access') {
				unset($return[$index]);
			}
		}
		//TODO may be this is to add Inquiry name
		$status_text = elgg_echo("elica:status:{$entity->status}");
		$options = array(
			'name' => 'published_status',
			'text' => "<span>$status_text 1</span>",
			'href' => false,
			'priority' => 150,
		);
		$return[] = ElggMenuItem::factory($options);
	}

	foreach ($return as $index => $item) {
		if ($item->getName() == 'access') {
			unset($return[$index]);
		} 		

	}
	
	$container = $entity->getContainerEntity();
	if (elgg_instanceof($container, 'group')) {
		$group_name = elgg_echo("item:object:inquiry", array($container->name));
		$options = array(
			'name' => 'group_name',
			'text' => "<span>$group_name</span>",
			'href' => false,
			'priority' => 150,
		);
		$return[] = ElggMenuItem::factory($options);	
	}
	
	$options = array(
	    'title' => elgg_echo('elica:run:add'),
		'name' => 'run_add',
		'text' => '<span class="elgg-icon elgg-icon-elica-run"></span>',
		'href' => "elica/run/add/$entity->guid",
		'priority' => 150,
	);
	$return[] = ElggMenuItem::factory($options);	
	
	return $return;
}

/**
 * Set the notification message body
 * 
 * @param string $hook    Hook name
 * @param string $type    Hook type
 * @param string $message The current message body
 * @param array  $params  Parameters about the simulation run
 * @return string
 */
function elica_notify_message($hook, $type, $message, $params) {
	$entity = $params['entity'];
	$to_entity = $params['to_entity'];
	$method = $params['method'];
	if (elgg_instanceof($entity, 'object', 'elica')) {
		$descr = $entity->excerpt;
		$title = $entity->title;
		$owner = $entity->getOwnerEntity();
		return elgg_echo('elica:notification', array(
			$owner->name,
			$title,
			$descr,
			$entity->getURL()
		));
	}
	return null;
}

/**
 * Register simulation runs with ECML.
 */
function elica_ecml_views_hook($hook, $entity_type, $return_value, $params) {
	$return_value['object/elica'] = elgg_echo('elica:simulations');

	return $return_value;
}

/**
 * Override the default entity icon for files
 *
 * Plugins can override or extend the icons using the plugin hook: 'file:icon:url', 'override'
 *
 * @return string Relative URL
 */
function elica_icon_url_override($hook, $type, $returnvalue, $params) {
	$simulation = $params['entity'];
	$size = $params['size'];
	if (elgg_instanceof($simulation, 'object', 'elica_simulation')) {	
		$url = "mod/elica/graphics/icons/{$type}/{$size}.png";
		return $url;
	}
}

function count_simulation_runs($simulation) {
	$options = array(
		'relationship' => 'run',
		'relationship_guid' => $simulation->getGUID(),
		'count' => true,
	);
	return elgg_get_entities_from_relationship($options);
}

function get_simulation_runs($simulation) {
	$options = array(
		'relationship' => 'run',
		'relationship_guid' => $simulation->getGUID(),
		'limit' => 0,
	);
	return elgg_get_entities_from_relationship($options);
}

function get_simulation_for_run($run) {
	if ($run->simulation_guid) {
		$simulation = get_entity($run->simulation_guid);
		if ($simulation) {
			return $simulation;
		}
	}

	$simulations = get_entities_from_relationship("run", $run->getGUID(), true);
	if (count($simulations) > 0) {
		return $simulations[0];
	} else {
		return false;
	}
}
