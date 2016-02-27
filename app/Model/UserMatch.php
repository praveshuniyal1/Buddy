<?php
App::uses('AppModel', 'Model');
/**
 * UserMatch Model
 *
 * @property AccessToken $AccessToken
 * @property AuthCode $AuthCode
 * @property Client $Client
 * @property RefreshToken $RefreshToken
 */
class UserMatch extends AppModel {
	
	var $useTable = 'user_matches';
	
	public $actsAs = array('Containable');	
	
}	
	