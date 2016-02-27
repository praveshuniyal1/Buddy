<?php
App::uses('AppController', 'Controller');
App::uses('File', 'Utility');
App::uses('CakeEmail', 'Network/Email');

class AdminsController extends AppController {

	public $components= array('RequestHandler');
	var $uses= array('Admin');
	
	public function beforeFilter()
	{
		parent::beforeFilter();
		$this->Auth->allow(array('login'));
	}

	
	
	public function admin_login(){
	
	  	 if ($this->request->is('Post')) {		
            App::Import('Utility', 'Validation');
            if (isset($this->data['Admin']['username']) && Validation::email($this->data['Admin']['username'])){
                $this->request->data['Admin']['email'] = $this->data['Admin']['username'];
                $this->Auth->authenticate['Form']     = array(
                    'fields' => array(
						'userModel' => 'Admin',
                        'username' => 'email'
                    )
                );
                $x = $this->Admin->find('first',array('conditions' => array('email' => $this->data['Admin']['username'])));
            } else {
                $this->Auth->authenticate['Form'] = array(
                    'fields' => array(
						'userModel' => 'Admin',
                        'username' => 'username'
                    )
                ); 
                $x = $this->Admin->find('first',array('conditions' => array('username' => $this->data['Admin']['username'])));
			}
			if(!empty($x)){
            if($x['Admin']['user_type'] == '1' && $x['Admin']['status'] == '1'){
            	if (!$this->Auth->login()) {
            		$this->Session->setFlash('Please check your password.');
            		$this->redirect(array('controller' => 'users', 'action' => 'admin_login'));
            	} else {
            		$this->Session->write('VenueUser',true);
            		$this->Session->setFlash('Successfully signed in');
            		$this->redirect(array('controller' => 'users', 'action' => 'admin_dashboard'));
            	}         
            }else{
            	$this->Session->setFlash("You don't have Administrator authorities or your account is inactive.");
            	$this->redirect(array('controller' => 'users', 'action' => 'admin_login'));
            }
			  } else {
					$this->Session->setFlash("Invalid username or password.");
					$this->redirect(array('controller' => 'users', 'action' => 'admin_login'));
			}
		}
	}//end of func login//

				  
	public function admin_dashboard(){ 
		$this->loadModel("Members");
		$this->Members->recursive = 0;
    	$x = $this->Members->find('all',array(    				
    					"order"=>"Members.id ASC"
    				));
    	$this->set("users", $x);
		$y = $this->Members->read('all');
    	$this->set("use_count", $x);
		

		$this->set(compact('x'));
        
	} //end of func dashboard//
	

	public function admin_index() 
	{
			$this->loadModel('Admin');
			//$this->Customer->recursive = 0;
			$this->set('customers', $this->paginate('Admin'));
	}
	public function admin_logout()
    {
        $this->Auth->logout();
        $this->Session->setFlash('Logged out.');
        $this->redirect(array('controller'=>'Admins','action'=>'admin_login'));
    }
	
	public function admin_changepass(){
		 if ($this->request->is('post')) {
			
			$password =AuthComponent::password($this->data['Admin']['opass']);
            $em= $this->Auth->user('id');
			
			$pass=$this->Admin->find('first',array('conditions'=>array('AND'=>array('Admin.id' => $em))));
			//debug($pass); exit;
			if($pass['Admin']['password'] == $password){
				if($this->data['Admin']['password'] != $this->data['Admin']['cpass'] ){
					$this->Session->setFlash("New password and Confirm password field do not match");
				} else {
					$this->Admin->data['Admin']['opass'] = $this->data['Admin']['password'];
					$this->Admin->id = $pass['Admin']['id'];
					  if($this->Admin->exists()){
						$pass= array('Admin'=>array('password'=>$this->request->data['Admin']['password']));
						if($this->Admin->save($pass)) {
							$this->Session->setFlash("Password updated successfully.");
							$this->redirect(array('controller'=>'users','action' => 'admin_profile'));
						}
					  }
				}
			} else {
				$this->Session->setFlash("Your old password did not match.");
			}        
		  }
			   
    }//end of func admin_changepass//

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		$this->Admin->id = $id;
		if (!$this->Admin->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$this->set('user', $this->Admin->read(null, $id));
	}

/**
 * admin_add method
 *
 * @return void
 */
	
	
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->request->data['Admin']['user_type'] = 1 ;
			$this->request->data['Admin']['ip'] = $this->RequestHandler->getClientIp();
			$this->request->data['Admin']['status'] = '1' ;
			$this->Admin->create();
			if ($this->Admin->saveAll($this->request->data)) {
				$this->Session->setFlash(__('The new admin has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The admin could not be saved. Please, try again.'));
			}
		}
	}

 
	/**
	 * admin_edit method
	 *
	 * @throws NotFoundException
	 * @param string $id
	 * @return void
	 */
	public function admin_edit($id = null) {
		$this->Admin->id = $id;
              
		if (!$this->Admin->exists()) {
			throw new NotFoundException(__('Invalid admin'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
		        if ($this->Admin->save($this->request->data)) {
					$this->Profile->save($this->request->data);
					$this->Session->setFlash(__('The user has been saved'));
					$this->redirect(array('controller'=>'users','action' => 'index'));
				} 
				else {
					$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		} else {
                        
                        
			$this->request->data = $this->Admin->read(null, $id);
		}
                $this->set('action_type','edit');
	}

/**
 * admin_delete method
 *
 * @throws MethodNotAllowedException
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->loadModel('Admin');
		$this->Admin->id = $id;
		$userData = $this->Admin->find('first',array('conditions'=>array('Admin.id'=>$id)));
		
		if (!$this->Admin->exists()) {
			throw new NotFoundException(__('Invalid Admin'));
		}
		if ($this->Admin->delete()) {			
			$this->Session->setFlash(__('Admin deleted successfully'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Admin was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
	
	public function admin_deleteall($id = null){
            
        if (!$this->request->is('Ajax')) {
            throw new MethodNotAllowedException();
        }
		$this->loadModel('Admin');
		foreach ($this->request['data']['Admin'] as $k) {
		    $this->Admin->id = (int) $k;
			$userData = $this->Admin->find('first',array('conditions'=>array('Admin.id'=>(int) $k)));
			
            if ($this->Admin->exists()) {
				$this->Admin->deleteAll(array('Admin.id'=>$k), $cascade = true);		
            }  
		   
        }        
        $this->Session->setFlash(__('Selected admins were removed.'));
       
        $this->autoRender=false;
    }
	
		public function admin_detail($id = null){
			$this->Admin->id = $id;
			$this->set('detail',$this->Admin->find('first',array('conditions'=>array('Admin.id'=>$id))));
			$this->loadModel('UserEducation');
			$x = $this->UserEducation->find('all',array('conditions'=>array('UserEducation.user_id'=>$id)));
			 $this->set('edu',$x);
			 
			$this->loadModel('UserWorkSince');
			$x1 = $this->UserWorkSince->find('all',array('conditions'=>array('UserWorkSince.user_id'=>$id)));
			 $this->set('exp',$x1);
		  }
	
	
	public function admin_activate($id = null)
    {
        $this->Admin->id = $id;
        if ($this->Admin->exists()) {
            $x = $this->Admin->save(array(
                'Admin' => array(
                    'status' => '1'
                )
            ));
            $this->Session->setFlash("admin activated successfully.");
            $this->redirect(array(
                'action' => 'index'
            ));
        } else {
            $this->Session->setFlash("Unable to activate admin.");
            $this->redirect(array(
                'action' => 'index'
            ));
        }        
    }
    
    
    public function admin_block($id = null)
    {
        $this->Admin->id = $id;
        if ($this->Admin->exists()) {
            $x = $this->Admin->save(array(
                'Admin' => array(
                    'status' => '0'
                )
            ));
            $this->Session->setFlash("admin blocked successfully.");
            $this->redirect(array(
                'action' => 'index'
            ));
        } else {
            $this->Session->setFlash("Unable to block admin.");
            $this->redirect(array(
                'action' => 'index'
            ));
        }
        
    }
	
	public function admin_activateall($id = null){
		if (!$this->request->is('Ajax')) {
            throw new MethodNotAllowedException();
        }        
		
        foreach ($this->request['data']['Admin'] as $v) {		
			$this->Admin->id = $v;
			if ($this->Admin->exists()) {
				$x = $this->Admin->save(array(
					'Admin' => array(
						'status' => "1"
					)					
				));	        
			} 		  	          
        }
		$this->Session->setFlash(__('Selected admins Activated Successfully.', true));
		$this->autoRender = false;
    }
		
		
			public function admin_deactivateall($id = null){
				if (!$this->request->is('Ajax')) {
					throw new MethodNotAllowedException();
                }
                
                foreach ($this->request['data']['Admin'] as $v) {	
                                $this->Admin->id = $v;
                                $x = $this->Admin->save(array(
                                        'Admin' => array(
                                                'status' => "0"
                                        )					
                                ));	          
                }
				$this->Session->setFlash(__('Selected Users were deactivated successfully.', true));
				$this->autoRender = false;		
            }
	
	
	public function admin_profile(){
		$id = $this->Auth->User('id');
		$this->set('profile',$this->Admin->find('first',array('conditions'=>array('Admin.id'=>$id))));
		
	}
	
	public function admin_profileedit($id=null) 
	{
		$id = $this->Auth->User('id');
		$this->set('profile',$this->Admin->find('first',array('conditions'=>array('Admin.id'=>$id))));
        $x= $this->Admin->find('first',array('conditions'=>array('Admin.id'=>$id)));
		$this->Admin->id = $id;
		if (!$this->Admin->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {	
		
			
			$one = $this->request->data['Admin']['profile_image'];
            if($this->request->data['Admin']['profile_image']['name']!=""){
				$imageNAME = $x['Admin']['id'].$x['Admin']['profile_image'];
				$file = new File(WWW_ROOT . 'files/profileimage/'.$imageNAME, false, 0777);
				$file->delete();
				$this->request->data['Admin']['profile_image'] = $x['Admin']['id'].$one['name'];  
              }else{
				$this->request->data['Admin']['profile_image'] = $x['Admin']['profile_image'];
            }   
		
			if ($this->Admin->save($this->request->data)) {
			
			if ($one['error'] == 0) {
                    $pth = 'files' . DS . 'profileimage' . DS .$id.$one['name'];
                    move_uploaded_file($one['tmp_name'], $pth);                   
                }
				$this->Session->setFlash(__('The Profile has been updated'));
				$this->redirect(array('action' => 'admin_profile'));
			} else {
				$this->Session->setFlash(__('The Profile could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->Admin->read(null, $id);
		}
	}
	public function admin_userprofile($id=null) {
		$this->User->id = $id;
		$this->set('profile',$this->User->find('first',array('conditions'=>array('User.id'=>$id))));
	}

		 public function admin_forget() {   
        $this->User->recursive=-1;
		if(!empty($this->data))
		{
			if(empty($this->data['User']['email']))
			{
				$this->Session->setFlash('Please Provide Your Email Address that You used to Register with Us');
			}
			else
			{
				$email=$this->data['User']['email'];
				$fu=$this->User->find('first',array('conditions'=>array('User.email'=>$email)));
				if($fu){
					if($fu['User']['status']=="1"){
						$key = Security::hash(String::uuid(),'sha512',true);
						$hash=sha1($fu['User']['username'].rand(0,100));
						$url = Router::url( array('controller'=>'Users','action'=>'reset'), true ).'/'.$key.'#'.$hash;
						$ms="<p>Hello ,<br/>".$fu['User']['name']."<br/><a href=".$url.">Click Here</a> to reset your password.</p><br /> ";
						$fu['User']['token']=$key;
						$this->User->id=$fu['User']['id'];
						if($this->User->saveField('token',$fu['User']['token'])){
							$l = new CakeEmail('smtp');
							$l->config('smtp')->emailFormat('html')->template('signup', 'fancy')->subject('Reset Your Password')->to($fu['User']['email'])->send($ms);
							$this->set('smtp_errors', "none");
							$this->Session->setFlash(__('Check Your Email To Reset your password', true));
							$this->redirect(array('controller' => 'Users','action' => 'admin_login'));
	                    }
						else{
							$this->Session->setFlash("Error Generating Reset link");
						}
					}
					else{
						$this->Session->setFlash('This Account is Blocked. Please Contact to Administrator...');
					}
				}
				else{
					$this->Session->setFlash('Email does Not Exist');
				}
			}
		}
	}
	public function admin_reset($token=null) {
		$this->User->recursive=-1;
		if(!empty($token)){
			$u=$this->User->findBytoken($token);
			if($u){
				$this->User->id=$u['User']['id'];
				if(!empty($this->data)){
					if($this->data['User']['password'] != $this->data['User']['cpassword']){
							$this->Session->setFlash("Both the passwords are not matching...");
							return;
                    }
					$this->User->data=$this->data;
					$this->User->data['User']['username']=$u['User']['username'];
					$new_hash=sha1($u['User']['username'].rand(0,100));//created token
					$this->User->data['User']['token']=$new_hash;
					if($this->User->validates(array('fieldList'=>array('password','cpassword')))){
						if($this->User->save($this->User->data))
						{
							$this->Session->setFlash('Password Has been Updated');
							$this->redirect(array('controller'=>'Users','action'=>'admin_login'));
						}
					}
					else{
					$this->set('errors',$this->User->invalidFields());
					}
				}
			}
			else
			{
			$this->Session->setFlash('Token Corrupted, Please Retry.the reset link <a style="cursor: pointer; color: rgb(0, 102, 0); text-decoration: none; background: url("http://files.adbrite.com/mb/images/green-double-underline-006600.gif") repeat-x scroll center bottom transparent; margin-bottom: -2px; padding-bottom: 2px;" name="AdBriteInlineAd_work" id="AdBriteInlineAd_work" target="_top">work</a> only for once.');
			}
		}
		else{
		$this->Session->setFlash('Pls try again...');
		$this->redirect(array('controller' => 'Users','action' => 'admin_login'));
		}
	}
	 public function forgot($u = null) {
       //  Configure::write('debug', 2);
		if($this->request->is('post')){
			$this->User->recursive = -1;
                $email = $this->data['username'];
                $fu = $this->User->find('first', array('conditions' => array('User.email' => $email)));
                if ($fu) {
                    if ($fu['User']['status'] == "1") {
                        $key = Security::hash(String::uuid(), 'sha512', true);
                        $hash = sha1($fu['User']['email'] . rand(0, 100));
                        $url = Router::url(array('controller' => 'Users', 'action' => 'reset'), true) . '/' . $key . '#' . $hash;
                        $ms = "<p>Hi <br/>".$fu['User']['name']."&nbsp;".$fu['User']['last_name'].",<br/><a href=".$url.">Click here</a> to reset your password.</p><br /> ";
                        $fu['User']['token'] = $key;
                        $this->User->id = $fu['User']['id'];
                        if ($this->User->saveField('token', $fu['User']['token'])) {
						   
								$l = new CakeEmail();
								$l->emailFormat('html')->template('signup', 'fancy')->subject('Reset Your Password')->from('keithmanek1988@gmail.com')->to($email)->send($ms);
								$this->Session->setFlash('Please Check Your Email To Reset your password','default',  array ('class' => 'successfully'));
								$this->redirect(array('controller'=>'users','action'=>'login'));
							
                        } else {
							$this->Session->setFlash('Please try again', 'default', array ('class' => 'errormsg'));
							$this->redirect(array('controller'=>'users','action'=>'forgot'));                         
                        }
                    } else {
							$this->Session->setFlash('Your account has been blocked by Administrator', 'default', array ('class' => 'errormsg'));
							$this->redirect(array('controller'=>'users','action'=>'forgot'));  
                       
                    }
                } else {
					//$this->Session->setFlash('You have registered successfully.', 'default', array ('class' => 'successfully'));	
					$this->Session->setFlash('Email does not exist','default',  array ('class' => 'errormsg'));
					$this->redirect(array('controller'=>'users','action'=>'forgot'));  
                    
                }
			}	
    }
	
	
	
	public function reset($token = null) {
        $this->User->recursive = -1;
        if (!empty($token)) {
            $u = $this->User->findBytoken($token);
            if ($u) {
                $this->User->id = $u['User']['id'];
                if (!empty($this->data)) {
					$this->request->data['User'] = $this->data;
                    if ($this->data['User']['password'] != $this->data['User']['confirm_password']) {
                        $this->Session->setFlash("Both the passwords are not matching", 'default', array ('class' => 'successfully'));
                        
                    }
                    $this->User->data = $this->data;
                    $this->User->data['User']['username'] = $u['User']['username'];
                    $new_hash = sha1($u['User']['username'] . rand(0, 100)); //created token
                    $this->User->data['User']['token'] = $new_hash;
                 
                        //	if($this->request->data['User']['password'] == $this->request->data['User']['confirm_password'] ){
                        if ($this->User->save($this->User->data)) {
							$this->Session->setFlash('Your password has been updated successfully', 'default', array ('class' => 'successfully'));
                            $this->redirect(array('controller'=>'users','action'=>'login'));
                        }
                  
                }
            } else {
			$this->Session->setFlash('Token Corrupted, Please Retry.the reset link <a style="cursor: pointer; color: rgb(0, 102, 0); text-decoration: none; background: url("http://files.adbrite.com/mb/images/green-double-underline-006600.gif") repeat-x scroll center bottom transparent; margin-bottom: -2px; padding-bottom: 2px;" name="AdBriteInlineAd_work" id="AdBriteInlineAd_work" target="_top">work</a> only for once.', 'default', array ('class' => 'errormsg'));
              
            }
        }
    }
	
	public function login() 
	{
		if($this->Session->check('Auth.User')){
				$this->Session->setFlash('You are already logged in','default',array('class'=>'successfully')); 
					$this->redirect(array('controller' => '/'));				
			}		
			if ($this->request->is('post')) 
			{
			//debug($this->data);
			App::Import('Utility', 'Validation');
			
			if(isset($this->data['User']['username']) && $this->data['User']['username']!='' && isset($this->data['User']['password']) && $this->data['User']['password'] !=''){
			if (isset($this->data['User']['username']) && Validation::email($this->data['User']['username'])){
                $this->request->data['User']['email'] = $this->data['User']['username'];
                $this->Auth->authenticate['Form']     = array(
                    'fields' => array(
						'userModel' => 'User',
                        'username' => 'email'
                    )
                );
                $x = $this->User->find('first',array('conditions' => array('email' => $this->data['User']['username'])));
            } else {
                $this->Auth->authenticate['Form'] = array(
                    'fields' => array(
						'userModel' => 'User',
                        'username' => 'username'
                    )
                ); 
             
				$x = $this->User->find('first',array('conditions' => array('username' => $this->data['User']['username'])));
				//$this->Session->setFlash("Email address not found.", 'default', array ('class' => 'errormsg'));				
            	//$this->redirect(array('controller' => 'Users', 'action' => 'login'));			
				
			}
			if(isset($x['User']['type']) && $x['User']['type'] == '0'){
						if ($this->Auth->login()) 
						{
								$this->User->query("UPDATE users set online=1, last_activity=NOW() where id ='".$this->Session->read('Auth.User.id')."'");
								$this->redirect(array('controller'=>'Users','action'=>'profile'));
								
						}
						else
						{
							$this->Session->setFlash('Invalid username or password, try again', 'default', array ('class' => 'errormsg'));
						}
				} else {
						$this->Session->setFlash("Email address not exist.", 'default', array ('class' => 'errormsg'));
						$this->redirect(array('controller' => 'Users', 'action' => 'login'));
				
				}	
				  } else {
				$this->Session->setFlash("Please enter email or password.", 'default', array ('class' => 'errormsg'));
				
            	$this->redirect(array('controller' => 'Users', 'action' => 'login'));
				
		  }		
			}
			
}

	
	
	public function dashboard()
{
	$this->set('name',$this->Auth->User('name'));
			
}


public function profile_image()
{
			if($this->request->is('post'))
			{				
					//debug($this->request->data['User']['id']);exit;
				
							$x = $this->User->findByid($this->request->data['User']['id']);
							$this->autoRender = false;
							$one = $this->request->data['User']['profile_image'];
								if($this->request->data['User']['profile_image']['name']!="")
								{
										$this->request->data['User']['profile_image'] = $this->request->data['User']['id'].$one['name'];  
										$file = new File(WWW_ROOT . 'files/profileimage/'. $x['User']['profile_image'], false, 0777);
										$file->delete();
										/*$dir = $this->webroot. DS .'files' . DS . 'profileimage';
										$file = new File($dir . DS . $x['User']['profile_image']);
										$file->delete();*/
								}
								else
								{
										$this->request->data['User']['profile_image'] = $x['User']['profile_image'];
									
								}   
								if ($this->User->save($this->request->data)) 
								{			
										if ($one['error'] == 0) 
										{
											$pth = 'files' . DS . 'profileimage' . DS .$this->request->data['User']['id'].$one['name'];
											move_uploaded_file($one['tmp_name'], $pth);                   
										}
									$this->Session->setFlash('The Profile has been updated','default',array('class'=>'successfully'));
									$this->redirect(array('action' => 'profile'));
								} 
								else {
									$this->Session->setFlash('The Profile could not be saved. Please, try again.','default',array('class'=>'errormsg'));
									}							
			}
	

}

/**************** Response the ajax request and handle the old password validatation ****************************/

/**************** EndResponse the ajax request and handle the old password validatation ****************************/


//$this->Session->setFlash('your blog has successfully added.', 'default', array ('class' => 'successfully'));		
//$this->redirect(array('controller'=>'blogs', 'action' => 'index'));	

	
	




}