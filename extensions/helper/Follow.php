<?php

namespace li3_follows\extensions\helper;

use li3_follows\models\Follows;
use lithium\core\Libraries;
use lithium\security\Auth;
use lithium\storage\Session;

class Follow extends \lithium\template\Helper {
	
	protected $_classes = array(
		'users' => 'app\models\Users'
	);

	public function link($followed, $options = array()) {
		$defaults = array(
			'type' => 'element',
			'template' => 'follow',
			'library' => 'li3_follows',
			'data' => array(
				'follow_text' => '',
				'unfollow_text' => '',
				'button_text' => '',
				'class' => 'btn'
			),
			'options' => array(
				'paths' => array(
					'element' => dirname(dirname(__DIR__)).'/views/elements/{:element}.{:type}.php'
				)
			)
		);
		$options += $defaults;
		$options['data'] += $defaults['data'];
		$userData = Auth::check('default');
		$userClass = $this->_classes['users'];
		
		if ($userData && $following = $userClass::first((string) $userData['_id'])) {
			$type = array($options['type'] => $options['template']);
            $user = $userClass::first($userData['_id']);
			$data = $options['data'] + compact('followed','following', 'user');
			$view = $this->_context->view();
			return $view->render($type, $data, $options['options']);
		}
		return false;
	}
	
	public function count($followed) {
		list($followers, $count) = Follows::followers($followed);
		return $count;
	}
	
}
?>
