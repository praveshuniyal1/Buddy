<?php
App::uses('AppModel', 'Model');
/**
 * Member Model
 *
 * @property AccessToken $AccessToken
 * @property AuthCode $AuthCode
 * @property Client $Client
 * @property RefreshToken $RefreshToken
 */
class Member extends AppModel {

/**
 * Display field
 *
 * @var string
 */
        /*public function beforeSave($options = array()){
		if(isset($this->data[$this->alias]['password'])){
			$this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
		}
		return true;
	}*/
	var $useTable = 'members';
	
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
				'message'=>'Member name is already exist.'
			)
		)
	);

	
      

}
?>
