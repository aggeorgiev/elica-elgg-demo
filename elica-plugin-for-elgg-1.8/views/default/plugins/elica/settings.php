<?php
/**
 * Elica settings
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 */

$plugin = $vars["entity"];

// Set Defaults
if (!isset($plugin->host)) {
	$plugin->host = 'http://62.44.100.105:8080/elica/rest/elicaservices/runservice/1.0/';
}

$admin_simulation_options = array(
	"no" => elgg_echo("option:no"),
	"admin" => elgg_echo("elica:settings:admin_transfer:admin"),
	"owner" => elgg_echo("elica:settings:admin_transfer:owner")
);


echo '<div>';

echo elgg_echo('elica:settings:host');

echo elgg_view('input/text', array(
	'name' => 'params[host]',
	'class' => 'elica-admin-input',
	'value' => $plugin->host,
));

echo '<div class="elgg-subtext">' . elgg_echo('elica:settings:host:description') . '</div>';

echo '</div>';


// simulation management settings
echo '<div>';
echo elgg_echo('elica:settings:admin_simulation');
echo '&nbsp;' . elgg_view('input/dropdown', array('name' => 'params[admin_simulation]', 'options_values' => $admin_simulation_options, 'value' => $plugin->admin_simulation));
echo '<div class="elgg-subtext">' . elgg_echo('elica:settings:admin_simulation:description') . '</div>';
echo '</div>';

echo "<br><br>";