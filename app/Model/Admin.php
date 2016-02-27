<?php
App::uses('AppModel', 'Model');

class Admin extends AppModel {

    public function beforeSave($options = array()){
		if(isset($this->data[$this->alias]['password'])){
			$this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
		}
		return true;
	}
	
	var $useTable = 'admins';	
	public $actsAs = array('Containable');     
	
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
}
?>