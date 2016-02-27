<?php
App::uses('AppModel', 'Model');
/**
 * Activity Model
 *
 * @property AccessToken $AccessToken
 * @property AuthCode $AuthCode
 * @property Client $Client
 * @property RefreshToken $RefreshToken
 */
class FriendList extends AppModel {
	
	var $useTable = 'friend_lists';
	public $actsAs = array('Containable');	
	/* var $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'usr_id'
		), */
		
}	
	 