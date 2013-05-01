<?php

namespace li3_follows\models;

use app\models\Users;
use lithium\data\Model;
use lithium\util\Set;
use lithium\util\Inflector;

class Follows extends Model {
	
	/**
	 *     Follower - 
	 *     Role - a user might follow objects in multiple ways. For example a user might 'bookmark'
	 *            a product for viewing later, or they might add it to a wishlist - you can use role
	 *            to differentiate between multiple following actions.
	 * @var type 
	 */
	protected $_schema = array(
		'_id'         => array('type' => 'id'), // Change if using mysql
		'follower'    => array('type' => 'string'), // model name of follower
		'follower_id' => array('type' => 'id'),
		'followed'    => array('type' => 'string'), // model name of followed
		'followed_id' => array('type' => 'id'),
		'role'        => array('type' => 'string'),
		'created'     => array('type' => 'date'),
	);
	
	protected $_meta = array('locked' => true);


	public static function __init(array $options = array()) {
		static::applyFilter('save', function ($self, $params, $chain) {
			if (empty($params['entity']->created)) {
				$params['entity']->created = new \MongoDate();
			}
			return $chain->next($self, $params, $chain);
		});
	}
	
	public function modelName($entity) {
		$model = explode('\\',  $entity->model());
		return strtolower(Inflector::singularize(end($model)));
	}

    /**
     *
     *
     * @param $follower
     * @param $followed
     * @param array $options
     * @return bool
     */
    public static function isFollowing($follower, $followed, $options = array()) {
		$exists = Follows::first(array(
			'conditions' => array(
				'follower_id' => $follower->_id,
				'followed_id' => $followed->_id
			) + $options
		));
		if ($exists) {
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Sets or updates a following relationship between two entities.
	 *
     * @param $follower
     * @param $followed
     * @param array $options
     * @return bool
     */
    public static function follow($follower, $followed, $options = array()) {
		$data = array(
			'follower' => $follower->modelName(),
			'follower_id' => $follower->_id,
			'followed' =>  $followed->modelName(),
			'followed_id' => $followed->_id
		) + $options;

		$following = static::isFollowing($follower, $followed);
		if (!$following) {
			$follow = Follows::create($data);
			return $follow->save();
		}
		else if ($following) {
			$conditions = array(
				'follower_id' => $follower->_id, 
				'followed_id' => $followed->_id
			);
			return Follows::update($data, $conditions);
		}
		return true;
	}
	
	public static function unfollow($follower, $followed, $options = array()) {
		if (static::isFollowing($follower, $followed)) {
			
			$follow = Follows::first(array(
				'conditions' => array(
					'follower_id' => $follower->_id,
					'followed_id' => $followed->_id
				)
			));
			
			if ($follow->role == 'admin' && $follow->followed == 'group') {
				return false;
			}
			
			return Follows::remove(array(
				'follower_id' => $follower->_id,
				'followed_id' => $followed->_id
			));
		}
		return true;
	}
	
	public static function followers($entity, $options = array()) {
		$defaults = array(
			'page' => 1,
			'limit' => null,
			'order' => null,
			'fields' => null,
			'conditions' => array()
		);
		$options = Set::merge($defaults, $options);

		$conditions = ['followed_id' => $entity->_id] + $options['conditions'];
		
		$model = $entity->model();
		
		$data = Follows::all(compact('conditions','page','limit','order','fields'))->to('array');
		$followers = Set::extract($data, '/follower_id');
		$followers = Users::all(array('conditions' => array('_id' => $followers)));
		$count = Follows::count(compact('conditions'));

		return array($followers, $count);
	}
	
	/**
	 * Get all follows another entity (usually a user) is following. To retrieve the document set, use
	 * `$entity->followed('type')`.
	 *
     * @param $follower
     * @param array $options
     * @return array
     */
    public static function following($follower, $options = array()) {
		$defaults = array(
			'conditions' => array()
		);
		$options = Set::merge($defaults, $options);
		$following = Follows::all(array(
			'conditions' => $options['conditions'] + array('follower_id' => $follower->_id)
		));
		$count = Follows::count(array(
			'conditions' => $options['conditions'] + array('follower_id' => $follower->_id)
		));
		return array($following, $count);
	}
	
	/**
	 * Get a document set of all followed objects by a user (or following object).
	 *
     * @param $follower
     * @param $objectType
     * @return bool
     */
    public static function followed($follower, $objectType) {
		list($objects, $objectCount) = $follower->following(array('conditions' => array('followed' => $objectType)));
		if ($objects->data()) {
			$objects = Set::extract($objects->data(), '/followed_id');
			$modelName = 'app\\models\\'. ucfirst(Inflector::pluralize($objectType));
			$objects = $modelName::all(array('conditions' => array('_id' => array('$in' => $objects))));
		}
		else {
			$objects = false;
		}
		return $objects;
	}
}

?>
