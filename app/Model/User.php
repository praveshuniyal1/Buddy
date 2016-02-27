<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 * @property AccessToken $AccessToken
 * @property AuthCode $AuthCode
 * @property Client $Client
 * @property RefreshToken $RefreshToken
 */
class User extends AppModel {

/**
 * Display field
 *
 * @var string
 */
        public function beforeSave($options = array()){
		if(isset($this->data[$this->alias]['password'])){
			$this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
		}
		return true;
	}
	var $useTable = 'users';
	
	public $actsAs = array('Containable');
        
   	
       
/**
 * method called beforeSave
 */	
 var $validate = array(
		'username'=>array(
			'NotEmpty'=>array(
				'rule'=>'notEmpty',
				'message'=>'Please enter user name.'
			),
			'IsUnique'=>array(
				'rule'=>'isUnique',
				'message'=>'User name is already exist.'
			)
		)
	);

	
	/*var $belongsTo = array(
		'Location' => array(
			'className' => 'Location',
			'foreignKey' => 'location'
		),
		
	);*/
      
      
}
?>
