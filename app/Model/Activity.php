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
class Activity extends AppModel {
	
	var $useTable = 'activities';
	
	public $actsAs = array('Containable');	
	
}	
	