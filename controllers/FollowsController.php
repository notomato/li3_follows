<?php

namespace li3_follows\controllers;

use li3_follows\models\Follows;
use lithium\action\DispatchException;
use lithium\security\Auth;

class FollowsController extends \lithium\action\Controller {

	/*
	 * User following another object - needs more work and tidy up.
	 */
	public function follow() {
		$this->_render = false;
		$auth = Auth::check('default');
		$follower = \app\models\Users::first((string) $auth['_id']);
		if ($follower && $this->request->data) {
			$followedClass = 'app\models\\' . ucfirst(\lithium\util\Inflector::pluralize($this->request->data['followed']));
			$followed = $followedClass::first($this->request->data['followed_id']);
			if (!$follower || !$followed) {
				echo 'false';
				return false;
			}
			else {
				if (Follows::isFollowing($follower, $followed)) {
					Follows::unfollow($follower, $followed);
				}
				else {
					$result = Follows::follow($follower, $followed);
				}
				echo 'true';
				return true;
			}
		}
		echo 'false';
		return false;
	}
}

?>