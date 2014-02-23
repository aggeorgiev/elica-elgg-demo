<?php
/**
 * Delete simulation run entity
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 */
 
$run_guid = get_input('guid');
$run = get_entity($run_guid);

if (elgg_instanceof($run, 'object', 'elica_simulation_run') && $run->canEdit()) {
	$container = get_entity($run->container_guid);
	if ($run->delete()) {
		system_message(elgg_echo('elica:message:deleted_run'));
		if (elgg_instanceof($container, 'group')) {
			forward("elica/group/$container->guid/all");
		} else {
			forward("elica/owner/$container->username");
		}
	} else {
		register_error(elgg_echo('elica:error:cannot_delete_run'));
	}
} else {
	register_error(elgg_echo('elica:error:run_not_found1'));
}

forward(REFERER);