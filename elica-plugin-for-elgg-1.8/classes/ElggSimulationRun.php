<?php
/**
 * Delete run entity
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 *
 * Extended class to override the time_created
 * 
 * @property string $status      The published status of the simulation run (published, draft)
 * @property string $comments_on Whether commenting is allowed (Off, On)
 * @property string $excerpt     An excerpt of the simulation run used when displaying the run
 */
class ElggSimulationRun extends ElggObject {

	/**
	 * Set subtype to blog.
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();

		$this->attributes['subtype'] = "elica_simulation_run";
	}

	/**
	 * Can a user comment on this run?
	 *
	 * @see ElggObject::canComment()
	 *
	 * @param int $user_guid User guid (default is logged in user)
	 * @return bool
	 * @since 1.8.0
	 */
	public function canComment($user_guid = 0) {
		$result = parent::canComment($user_guid);
		if ($result == false) {
			return $result;
		}

		if ($this->comments_on == 'Off') {
			return false;
		}
		
		return true;
	}

}