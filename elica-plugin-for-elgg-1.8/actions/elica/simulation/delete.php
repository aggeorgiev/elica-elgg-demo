<?php
/**
 * Delete run entity
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 */

$simulation_guid = get_input('guid');
$simulation = get_entity($simulation_guid);

if (elgg_instanceof($simulation, 'object', 'elica_simulation') && $simulation->canEdit()) {
	$container = get_entity($simulation->container_guid);
	
	// delete related simulation runs first
	$runs = get_simulation_runs($simulation);
	if ($runs && is_array($runs)) {
		foreach ($runs as $run) {
			$run->delete();
		}
	}
	
	if ($simulation->delete()) {
		system_message(elgg_echo('elica:message:deleted_simulation'));
		if (elgg_instanceof($container, 'group')) {
			forward("elica/group/$container->guid/all");
		} else {
			forward("elica/owner/$container->username");
		}
	} else {
		register_error(elgg_echo('elica:error:cannot_delete_simulation'));
	}
} else {
	register_error(elgg_echo('elica:error:simulation_not_found'));
}

forward(REFERER);