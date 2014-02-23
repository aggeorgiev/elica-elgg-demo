<?php
/**
 * Elica English language file.
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 */

$english = array(
    'elica:simulation:processing' => 'Simulation in processing. Please refresh or try again later.',
    'elica:title:all_simulations' => 'All Simulations',
    'elica:string:between' => 'between',
	'elica' => 'Simulations',
	'elica:simulations' => 'Simulations',
	'elica:revisions' => 'Revisions',
	'elica:runs' => 'Runs',
	'elica:run' => 'Simulation run',
	'elica:run:image:url' => 'Run Image URL',
	'elica:run:video:url' => 'Run Video URL',
	'item:object:elica' => 'Simulations',
	'item:object:inquiry' => 'Inquiry: %s',
	'elica:title:user_simulations' => '%s\'s simulations',
	'elica:title:user_runs' => '%s\'s simulations\' runs',
	'elica:title:all_runs' => 'All site simulations\' runs',
	'elica:title:friends' => 'Friends\' simulations\' runs',

	// Settings
	'elica:settings:host' => 'Elica server URL',
	'elica:settings:host:description' => 'Elica server URL',
	'elica:settings:admin_simulation' => "Allow simulation manage",
	'elica:settings:admin_simulation:description' => 'Who is allowed to manage inquiries\' simulations?',
	'elica:settings:admin_transfer:admin' => "Site admin only",
	'elica:settings:admin_transfer:owner' => "Inquiry owners and site admins",
	
	'elica:group' => 'Inguiry simulations',
	'elica:enablesimulation' => 'Enable inquiry simulations',
	'elica:write' => 'Create a simulation run',

	// Simulations
	'elica:simulation:type' => 'Simulation type',
	'elica:simulation:options:flood' => 'Urban flood',
	'elica:simulation:param:NumberOfBuildings' => 'Number Of Buildings',
	'elica:simulation:param:MinimalFloorsOfBuildings' => 'Minimal Floors Of Buildings',
	'elica:simulation:param:MaximalFloorsOfBuildings' => 'Maximal Floors Of Buildings',
	'elica:simulation:param:TerrainFlatness' => 'Terrain Flatness',
	
	// Editing
	'elica:add' => 'Add simulation',
	'elica:simulation:add' => 'Add simulation',
	'elica:run:add' => 'Add simulation run',
	'elica:simulation:edit' => 'Edit simulation',
	'elica:run:edit' => 'Edit simulation run',
	'elica:excerpt' => 'Excerpt',
	'elica:title' => 'Title',
	'elica:description' => 'Description',
	'elica:save_status' => 'Last saved: ',
	'elica:never' => 'Never',
	'elica:simulation:url' => 'Simulation URL',

	// Statuses
	'elica:status' => 'Status',
	'elica:status:draft' => 'Draft',
	'elica:status:published' => 'Published',
	'elica:status:unsaved_draft' => 'Unsaved Draft',

	'elica:revision' => 'Revision',
	'elica:auto_saved_revision' => 'Auto Saved Revision',

	// messages
	'elica:message:saved' => 'Simulation run saved.',
	'elica:error:cannot_save' => 'Cannot save simulation run.',
	'elica:error:cannot_write_to_container' => 'Insufficient access to save simulation run to group.',
	'elica:messages:warning:draft' => 'There is an unsaved draft of this simulation run!',
	'elica:edit_revision_notice' => '(Old version)',
	'elica:message:deleted_simulation' => 'Simulation deleted.',
	'elica:message:deleted_run' => 'Simulation run deleted.',
	'elica:error:cannot_delete_simulation' => 'Cannot delete simulation.',
	'elica:error:cannot_delete_run' => 'Cannot delete simulation run.',
	'elica:simulation:none' => 'No simulations',
	'elica:run:none' => 'No simulation runs',
	'elica:error:missing:title' => 'Please enter title!',
	'elica:error:missing:description' => 'Please enter description!',
	'elica:error:cannot_edit_simulation' => 'This simulation may not exist or you may not have permissions to edit it.',
	'elica:error:revision_not_found' => 'Cannot find this revision.',
	'elica:simulation:notfound' => 'Cannot find simulation.',
	'elica:action:save:error:access' => 'You\'re not allowed to manage simulations of this inquiry',
	'elica:last:updated' => 'Last updated %s',
	'elica:by:author' => 'by %s',
	
	// river
	'river:create:object:run' => '%s published a simulation run %s',
	'river:comment:object:run' => '%s commented on the simulation run %s',

	// notifications
	'elica:newpost' => 'A new simulation run',
	'elica:notification' =>
    '
    %s made a new simulation run.

    %s
    %s

    View and comment on the new simulation run:
    %s
    ',

	// widget
	'elica:widget:description' => 'Display your latest simulation runs',
	'elica:moreruns' => 'More simulation runs',
	'elica:numbertodisplay' => 'Number of simulation runs to display',
	'elica:noruns' => 'No simulation runs'
);

add_translation('en', $english);
