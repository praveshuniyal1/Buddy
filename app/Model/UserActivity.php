<?php
App::uses('AppModel', 'Model');
/**
 * UserActivity Model
 *
 * @property AccessToken $AccessToken
 * @property AuthCode $AuthCode
 * @property Client $Client
 * @property RefreshToken $RefreshToken
 */
class UserActivity extends AppModel {
	
	var $useTable = 'user_activities';
	
	public $actsAs = array('Containable');	
	
}	
	