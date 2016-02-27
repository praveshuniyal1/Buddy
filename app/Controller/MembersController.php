<?php
App::uses('AppController', 'Controller');
App::uses('File', 'Utility');
App::uses('CakeEmail', 'Network/Email');

class MembersController extends AppController {

	public $components= array('RequestHandler');
	var $uses= array('Member');
	
	public function beforeFilter()
	{
		parent::beforeFilter();
		$this->Auth->allow(array('admin_login','admin_forget','admin_reset','login','reset','register','forgot','dashboard','profile','admin_export_csv'));
	}

	
	
	public function admin_login(){ //start of func login//
	
	  	 if ($this->request->is('Post')) {		
            App::Import('Utility', 'Validation');
            if (isset($this->data['Member']['username']) && Validation::email($this->data['Member']['username'])){
                $this->request->data['Member']['email'] = $this->data['Member']['username'];
                $this->Auth->authenticate['Form']     = array(
                    'fields' => array(
						'userModel' => 'Member',
                        'username' => 'email'
                    )
                );
                $x = $this->Member->find('first',array('conditions' => array('email' => $this->data['Member']['username'])));
            } else {
                $this->Auth->authenticate['Form'] = array(
                    'fields' => array(
						'userModel' => 'Member',
                        'username' => 'username'
                    )
                ); 
                $x = $this->Member->find('first',array('conditions' => array('username' => $this->data['Member']['username'])));
			}
			if(!empty($x)){
            if($x['Member']['user_type'] == '1' && $x['Member']['status'] == '1'){
            	if (!$this->Auth->login()) {
            		$this->Session->setFlash('Please check your password.');
            		$this->redirect(array('controller' => 'members', 'action' => 'admin_login'));
            	} else {
            		$this->Session->write('VenueMember',true);
            		$this->Session->setFlash('Successfully signed in');
            		$this->redirect(array('controller' => 'members', 'action' => 'admin_dashboard'));
            	}         
            }else{
            	$this->Session->setFlash("You don't have Administrator authorities or your account is inactive.");
            	$this->redirect(array('controller' => 'members', 'action' => 'admin_login'));
            }
			  } else {
					$this->Session->setFlash("Invalid username or password.");
					$this->redirect(array('controller' => 'members', 'action' => 'admin_login'));
			}
		}
	}//end of func login//

				  
	public function admin_dashboard(){ 
		$this->Member->recursive = 0;
    	$x = $this->Member->find('all',array(    				
    					"order"=>"Member.id ASC"
    				));
    	$this->set("members", $x);
		$y = $this->Member->read('all');
    	$this->set("use_count", $x);
		$this->loadModel("Category");
		$item= $this->Category->find("all",array("order" => "Category.id ASC"));
		$this->set("inventory_items", $item);
		$item_count = $this->Category->read('all');
		$this->set('item_count',$item);
		$this->loadModel("Blog");
		$contain= $this->Blog->find("all",array("order" => "Blog.id ASC"));
		//echo "<pre>";print_r($contain);
		$this->set("container_types", $contain);
		$contain_count = $this->Blog->read('all');
		//echo "<pre>";print_r($contain);
		$this->set('contain_count',$contain);
		$this->loadModel("Contact");
		$contact= $this->Contact->find("all",array("order" => "Contact.id ASC"));
		$this->set("contact_types", $contact);
		$contact_count = $this->Contact->read('all');
		//echo "<pre>";print_r($contact);
		$this->set('contact_count',$contact);
    	//$this->set("use_count", $xy);
		$this->set(compact('x'));
        
	} //end of func dashboard//
	
	public function admin_index() { 
		if($this->request->is('post')){
			$keyword = trim($this->request->data['keyword']);
					@$type =  $this->request->data['type'];
					if(!empty($keyword)){
						 $records = $this->Member->find('all', array('conditions' => array("OR" => array("Member.email LIKE" => "%$keyword%" , "Member.username LIKE" => "%$keyword%","Member.name LIKE" => "%$keyword%"))));
					} else if(!empty($type)){
						  $records = $this->Member->find('all', array('conditions' =>array("Member.usertype_id" => $type)));
					}
					$this->set("customers",$records,$this->paginate());
				if(count($records) == 0){
					$this->Session->setFlash("No Record found with this keyword please use another one.");
				}
			
			if(empty($keyword)&&empty($type)){
				  $this->Member->recursive = 0;
						  $this->set('customers', $this->paginate());
				  $this->Session->setFlash("Please choose some keywords to search..");
			}
		} else{
			$this->Member->recursive = 0;
			$this->paginate = array('order' => array('Member.id' => 'desc'),'limit' =>10);
			$this->set('customers', $this->paginate());
		}	

	}
	
	public function admin_logout()
    {
        $this->Auth->logout();
        $this->Session->setFlash('Logged out.');
        $this->redirect(array('controller'=>'Members','action'=>'admin_login'));
    }
	
	public function admin_changepass(){
		 if ($this->request->is('post')) {
			
			$password =AuthComponent::password($this->data['Member']['opass']);
            $em= $this->Auth->user('id');
			
			$pass=$this->Member->find('first',array('conditions'=>array('AND'=>array('Member.id' => $em))));
			//debug($pass); exit;
			if($pass['Member']['password'] == $password){
				if($this->data['Member']['password'] != $this->data['Member']['cpass'] ){
					$this->Session->setFlash("New password and Confirm password field do not match");
				} else {
					$this->Member->data['Member']['opass'] = $this->data['Member']['password'];
					$this->Member->id = $pass['Member']['id'];
					  if($this->Member->exists()){
						$pass= array('Member'=>array('password'=>$this->request->data['Member']['password']));
						if($this->Member->save($pass)) {
							$this->Session->setFlash("Password updated successfully.");
							$this->redirect(array('controller'=>'members','action' => 'admin_profile'));
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
		$this->Member->id = $id;
		if (!$this->Member->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$this->set('user', $this->Member->read(null, $id));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->request->data['Member']['ip'] = $this->RequestHandler->getClientIp();
			$this->request->data['Member']['status'] = '1' ;
			$this->Member->create();
			if ($this->Member->save($this->request->data)) {
                                $id=$this->Member->getLastInsertId();
                                    $this->Session->setFlash(__('The member has been saved'));
                                    $this->redirect(array('action' => 'index'));
				
			} else {
				$this->Session->setFlash(__('The member could not be saved. Please, try again.'));
			}
		}
             $this->set('action_type','add');
			$this->viewPath="Members";
			$this->render('admin_edit'); 
	}
	
	
	public function admin_admin() {
		if ($this->request->is('post')) {
			$this->request->data['Member']['type'] = 1 ;
			$this->request->data['Member']['ip'] = $this->RequestHandler->getClientIp();
			$this->request->data['Member']['status'] = '1' ;
			$this->Member->create();
			if ($this->Member->saveAll($this->request->data)) {
				$this->Session->setFlash(__('The new Admin has been saved'));
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
		$this->Member->id = $id;
		if (!$this->Member->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		
		if ($this->request->is('post') || $this->request->is('put')) {
				    if ($this->Member->save($this->request->data)) {
					$this->Session->setFlash(__('The user has been saved'));
					$this->redirect(array('controller'=>'members','action' => 'index'));
				} 
				else {
					$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		} else {
                   $this->request->data = $this->Member->read(null, $id);
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
		$this->loadModel('Member');
		$this->Member->id = $id;
		$userData = $this->Member->find('first',array('conditions'=>array('Member.id'=>$id)));
		
		if (!$this->Member->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->Member->delete()) {			
			$this->Session->setFlash(__('Member deleted successfully'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Member was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
	
	public function admin_deleteall($id = null) {
	
        if (!$this->request->is('Ajax')) {
            throw new MethodNotAllowedException();
        }
		//$this->loadModel('Member');
		//pr($this->request['data']['Member']);exit;
		foreach ($this->request['data']['Member'] as $k) {
		    $this->Member->id = (int) $k;
			$userData = $this->Member->find('first',array('conditions'=>array('Member.id'=>(int) $k)));
			    if ($this->Member->exists()) {
				$this->Member->deleteAll(array('Member.id'=>$k), $cascade = true);
                //$this->Customer->deleteAll();				
            }  
		   
        }        
        $this->Session->setFlash(__('Selected Users were removed.'));
       
        $this->autoRender=false;
    }
	
	public function admin_detail($id = null){
		$this->Member->id = $id;
		$this->set('detail',$this->Member->find('first',array('conditions'=>array('Member.id'=>$id))));
		$this->loadModel('MemberEducation');
	    $x = $this->MemberEducation->find('all',array('conditions'=>array('MemberEducation.user_id'=>$id)));
	     $this->set('edu',$x);
		 
		$this->loadModel('MemberWorkSince');
	    $x1 = $this->MemberWorkSince->find('all',array('conditions'=>array('MemberWorkSince.user_id'=>$id)));
	     $this->set('exp',$x1);
                    }
	
	
	public function admin_activate($id = null)
    {
        $this->Member->id = $id;
        if ($this->Member->exists()) {
            $x = $this->Member->save(array(
                'Member' => array(
                    'status' => '1'
                )
            ));
            $this->Session->setFlash("Member activated successfully.");
            $this->redirect(array(
                'action' => 'index'
            ));
        } else {
            $this->Session->setFlash("Unable to activate user.");
            $this->redirect(array(
                'action' => 'index'
            ));
        }        
    }
    
    
    public function admin_block($id = null)
    {
        $this->Member->id = $id;
        if ($this->Member->exists()) {
            $x = $this->Member->save(array(
                'Member' => array(
                    'status' => '0'
                )
            ));
            $this->Session->setFlash("Member blocked successfully.");
            $this->redirect(array(
                'action' => 'index'
            ));
        } else {
            $this->Session->setFlash("Unable to block user.");
            $this->redirect(array(
                'action' => 'index'
            ));
        }
        
    }
	
	public function admin_activateall($id = null){
		if (!$this->request->is('Ajax')) {
            throw new MethodNotAllowedException();
        }        
		
        foreach ($this->request['data']['Member'] as $v) {		
			$this->Member->id = $v;
			if ($this->Member->exists()) {
				$x = $this->Member->save(array(
					'Member' => array(
						'status' => "1"
					)					
				));	        
			} 		  	          
        }
		$this->Session->setFlash(__('Selected Members Activated Successfully.', true));
		$this->autoRender = false;
    }
		
		
	public function admin_deactivateall($id = null){
            if (!$this->request->is('Ajax')) {
            throw new MethodNotAllowedException();
                }
                
                foreach ($this->request['data']['Member'] as $v) {	
                                $this->Member->id = $v;
                                $x = $this->Member->save(array(
                                        'Member' => array(
                                                'status' => "0"
                                        )					
                                ));	          
                }
                        $this->Session->setFlash(__('Selected Members were deactivated successfully.', true));
                        $this->autoRender = false;		
            }
	
	
	public function admin_profile(){
		$id = $this->Auth->Member('id');
		$this->set('profile',$this->Member->find('first',array('conditions'=>array('Member.id'=>$id))));
		
	}
	
	public function admin_profileedit($id=null) 
	{
		$id = $this->Auth->Member('id');
		$this->set('profile',$this->Member->find('first',array('conditions'=>array('Member.id'=>$id))));
        $x= $this->Member->find('first',array('conditions'=>array('Member.id'=>$id)));
		$this->Member->id = $id;
		if (!$this->Member->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {	
		
			
			$one = $this->request->data['Member']['profile_image'];
            if($this->request->data['Member']['profile_image']['name']!=""){
				$imageNAME = $x['Member']['id'].$x['Member']['profile_image'];
				$file = new File(WWW_ROOT . 'files/profileimage/'.$imageNAME, false, 0777);
				$file->delete();
				$this->request->data['Member']['profile_image'] = $x['Member']['id'].$one['name'];  
              }else{
				$this->request->data['Member']['profile_image'] = $x['Member']['profile_image'];
            }   
		
			if ($this->Member->save($this->request->data)) {
			
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
			$this->request->data = $this->Member->read(null, $id);
		}
	}
	public function admin_userprofile($id=null) {
		$this->Member->id = $id;
		$this->set('profile',$this->Member->find('first',array('conditions'=>array('Member.id'=>$id))));
	}

		 public function admin_forget() {   
        $this->Member->recursive=-1;
		if(!empty($this->data))
		{
			if(empty($this->data['Member']['email']))
			{
				$this->Session->setFlash('Please Provide Your Email Address that You used to Register with Us');
			}
			else
			{
				$email=$this->data['Member']['email'];
				$fu=$this->Member->find('first',array('conditions'=>array('Member.email'=>$email)));
				if($fu){
					if($fu['Member']['status']=="1"){
						$key = Security::hash(String::uuid(),'sha512',true);
						$hash=sha1($fu['Member']['username'].rand(0,100));
						$url = Router::url( array('controller'=>'Members','action'=>'reset'), true ).'/'.$key.'#'.$hash;
						$ms="<p>Hello ,<br/>".$fu['Member']['name']."<br/><a href=".$url.">Click Here</a> to reset your password.</p><br /> ";
						$fu['Member']['token']=$key;
						$this->Member->id=$fu['Member']['id'];
						if($this->Member->saveField('token',$fu['Member']['token'])){
							$l = new CakeEmail('smtp');
							$l->config('smtp')->emailFormat('html')->template('signup', 'fancy')->subject('Reset Your Password')->to($fu['Member']['email'])->send($ms);
							$this->set('smtp_errors', "none");
							$this->Session->setFlash(__('Check Your Email To Reset your password', true));
							$this->redirect(array('controller' => 'Members','action' => 'admin_login'));
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
		$this->Member->recursive=-1;
		if(!empty($token)){
			$u=$this->Member->findBytoken($token);
			if($u){
				$this->Member->id=$u['Member']['id'];
				if(!empty($this->data)){
					if($this->data['Member']['password'] != $this->data['Member']['cpassword']){
							$this->Session->setFlash("Both the passwords are not matching...");
							return;
                    }
					$this->Member->data=$this->data;
					$this->Member->data['Member']['username']=$u['Member']['username'];
					$new_hash=sha1($u['Member']['username'].rand(0,100));//created token
					$this->Member->data['Member']['token']=$new_hash;
					if($this->Member->validates(array('fieldList'=>array('password','cpassword')))){
						if($this->Member->save($this->Member->data))
						{
							$this->Session->setFlash('Password Has been Updated');
							$this->redirect(array('controller'=>'Members','action'=>'admin_login'));
						}
					}
					else{
					$this->set('errors',$this->Member->invalidFields());
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
		$this->redirect(array('controller' => 'Members','action' => 'admin_login'));
		}
	}
	 public function forgot($u = null) {
       //  Configure::write('debug', 2);
		if($this->request->is('post')){
			$this->Member->recursive = -1;
                $email = $this->data['username'];
                $fu = $this->Member->find('first', array('conditions' => array('Member.email' => $email)));
                if ($fu) {
                    if ($fu['Member']['status'] == "1") {
                        $key = Security::hash(String::uuid(), 'sha512', true);
                        $hash = sha1($fu['Member']['email'] . rand(0, 100));
                        $url = Router::url(array('controller' => 'Members', 'action' => 'reset'), true) . '/' . $key . '#' . $hash;
                        $ms = "<p>Hi <br/>".$fu['Member']['name']."&nbsp;".$fu['Member']['last_name'].",<br/><a href=".$url.">Click here</a> to reset your password.</p><br /> ";
                        $fu['Member']['token'] = $key;
                        $this->Member->id = $fu['Member']['id'];
                        if ($this->Member->saveField('token', $fu['Member']['token'])) {
						   
								$l = new CakeEmail();
								$l->emailFormat('html')->template('signup', 'fancy')->subject('Reset Your Password')->from('keithmanek1988@gmail.com')->to($email)->send($ms);
								$this->Session->setFlash('Please Check Your Email To Reset your password','default',  array ('class' => 'successfully'));
								$this->redirect(array('controller'=>'members','action'=>'login'));
							
                        } else {
							$this->Session->setFlash('Please try again', 'default', array ('class' => 'errormsg'));
							$this->redirect(array('controller'=>'members','action'=>'forgot'));                         
                        }
                    } else {
							$this->Session->setFlash('Your account has been blocked by Administrator', 'default', array ('class' => 'errormsg'));
							$this->redirect(array('controller'=>'members','action'=>'forgot'));  
                       
                    }
                } else {
					//$this->Session->setFlash('You have registered successfully.', 'default', array ('class' => 'successfully'));	
					$this->Session->setFlash('Email does not exist','default',  array ('class' => 'errormsg'));
					$this->redirect(array('controller'=>'members','action'=>'forgot'));  
                    
                }
			}	
    }
	
	
	
	public function reset($token = null) {
        $this->Member->recursive = -1;
        if (!empty($token)) {
            $u = $this->Member->findBytoken($token);
            if ($u) {
                $this->Member->id = $u['Member']['id'];
                if (!empty($this->data)) {
					$this->request->data['Member'] = $this->data;
                    if ($this->data['Member']['password'] != $this->data['Member']['confirm_password']) {
                        $this->Session->setFlash("Both the passwords are not matching", 'default', array ('class' => 'successfully'));
                        
                    }
                    $this->Member->data = $this->data;
                    $this->Member->data['Member']['username'] = $u['Member']['username'];
                    $new_hash = sha1($u['Member']['username'] . rand(0, 100)); //created token
                    $this->Member->data['Member']['token'] = $new_hash;
                 
                        //	if($this->request->data['Member']['password'] == $this->request->data['Member']['confirm_password'] ){
                        if ($this->Member->save($this->Member->data)) {
							$this->Session->setFlash('Your password has been updated successfully', 'default', array ('class' => 'successfully'));
                            $this->redirect(array('controller'=>'members','action'=>'login'));
                        }
                  
                }
            } else {
			$this->Session->setFlash('Token Corrupted, Please Retry.the reset link <a style="cursor: pointer; color: rgb(0, 102, 0); text-decoration: none; background: url("http://files.adbrite.com/mb/images/green-double-underline-006600.gif") repeat-x scroll center bottom transparent; margin-bottom: -2px; padding-bottom: 2px;" name="AdBriteInlineAd_work" id="AdBriteInlineAd_work" target="_top">work</a> only for once.', 'default', array ('class' => 'errormsg'));
              
            }
        }
    }
	
	public function login() 
	{
		if($this->Session->check('Auth.Member')){
				$this->Session->setFlash('You are already logged in','default',array('class'=>'successfully')); 
					$this->redirect(array('controller' => '/'));				
			}		
			if ($this->request->is('post')) 
			{
			//debug($this->data);
			App::Import('Utility', 'Validation');
			
			if(isset($this->data['Member']['username']) && $this->data['Member']['username']!='' && isset($this->data['Member']['password']) && $this->data['Member']['password'] !=''){
			if (isset($this->data['Member']['username']) && Validation::email($this->data['Member']['username'])){
                $this->request->data['Member']['email'] = $this->data['Member']['username'];
                $this->Auth->authenticate['Form']     = array(
                    'fields' => array(
						'userModel' => 'Member',
                        'username' => 'email'
                    )
                );
                $x = $this->Member->find('first',array('conditions' => array('email' => $this->data['Member']['username'])));
            } else {
                $this->Auth->authenticate['Form'] = array(
                    'fields' => array(
						'userModel' => 'Member',
                        'username' => 'username'
                    )
                ); 
             
				$x = $this->Member->find('first',array('conditions' => array('username' => $this->data['Member']['username'])));
				//$this->Session->setFlash("Email address not found.", 'default', array ('class' => 'errormsg'));				
            	//$this->redirect(array('controller' => 'Members', 'action' => 'login'));			
				
			}
			if(isset($x['Member']['type']) && $x['Member']['type'] == '0'){
						if ($this->Auth->login()) 
						{
								$this->Member->query("UPDATE members set online=1, last_activity=NOW() where id ='".$this->Session->read('Auth.Member.id')."'");
								$this->redirect(array('controller'=>'Members','action'=>'profile'));
								
						}
						else
						{
							$this->Session->setFlash('Invalid username or password, try again', 'default', array ('class' => 'errormsg'));
						}
				} else {
						$this->Session->setFlash("Email address not exist.", 'default', array ('class' => 'errormsg'));
						$this->redirect(array('controller' => 'Members', 'action' => 'login'));
				
				}	
				  } else {
				$this->Session->setFlash("Please enter email or password.", 'default', array ('class' => 'errormsg'));
				 $this->redirect(array('controller' => 'Members', 'action' => 'login'));
				
		  }		
			}
			
}

	
			/*if($this->Session->check('Auth.Member')){
				$this->Session->setFlash('you are already logged in','success_msg');
            	$this->redirect(array('controller' => 'homes', 'action' => 'index'));
			}
			if ($this->request->is('post')) {
		    App::Import('Utility', 'Validation');
			
			if(isset($this->data['Member']['username']) && $this->data['Member']['username']!='' && isset($this->data['Member']['password']) && $this->data['Member']['password'] !=''){
            if (isset($this->data['Member']['username']) && Validation::email($this->data['Member']['username'])){
                $this->request->data['Member']['email'] = $this->data['Member']['username'];
                $this->Auth->authenticate['Form']     = array(
                    'fields' => array(
						'userModel' => 'Member',
                        'username' => 'email'
                    )
                );
                $x = $this->Member->find('first',array('conditions' => array('email' => $this->data['Member']['username'])));
            } else {
                $this->Auth->authenticate['Form'] = array(
                    'fields' => array(
						'userModel' => 'Member',
                        'username' => 'username'
                    )
                ); 
                $x = array();
				$this->Session->setFlash("Email address not found.",'error_msg');
            	$this->redirect(array('controller' => 'Members', 'action' => 'login'));
			}
			if(isset($x['Member']['type']) && $x['Member']['type'] != '1'){
            	if (!$this->Auth->login()) {
            		$this->Session->setFlash('Please check your password.','error_msg');
            		
            	} else {
            		$this->Session->setFlash('you have logged in Successfuly.','success_msg');
            		$this->redirect(array('controller' => 'homes', 'action' => 'index'));
            	}         
            } else {
            	$this->Session->setFlash("Email address not exist.",'error_msg');
            	$this->redirect(array('controller' => 'Members', 'action' => 'login'));
            }
		  } else {
				$this->Session->setFlash("Please enter email or password.",'error_msg');
            	$this->redirect(array('controller' => 'Members', 'action' => 'login'));
		  }
		 } */
	//}
	
	public function dashboard()
{
	$this->set('name',$this->Auth->Member('name'));
			
}


public function profile_image()
{
			if($this->request->is('post'))
			{				
					//debug($this->request->data['Member']['id']);exit;
				
							$x = $this->Member->findByid($this->request->data['Member']['id']);
							$this->autoRender = false;
							$one = $this->request->data['Member']['profile_image'];
								if($this->request->data['Member']['profile_image']['name']!="")
								{
										$this->request->data['Member']['profile_image'] = $this->request->data['Member']['id'].$one['name'];  
										$file = new File(WWW_ROOT . 'files/profileimage/'. $x['Member']['profile_image'], false, 0777);
										$file->delete();
										/*$dir = $this->webroot. DS .'files' . DS . 'profileimage';
										$file = new File($dir . DS . $x['Member']['profile_image']);
										$file->delete();*/
								}
								else
								{
										$this->request->data['Member']['profile_image'] = $x['Member']['profile_image'];
									
								}   
								if ($this->Member->save($this->request->data)) 
								{			
										if ($one['error'] == 0) 
										{
											$pth = 'files' . DS . 'profileimage' . DS .$this->request->data['Member']['id'].$one['name'];
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

public function admin_export_csv()
{
	$this->autoRender = false;		
	$conditions = array();	
	$this->loadModel("Member");		
	@$info = $this->Member->find('all');
	$data = "Id,Username,Name,Email,Password,Date,Location \n";
	$name = 'User';
	$filename = "Member.csv";
		foreach($info as $info)
		{ //echo "<pre>";print_r($info);
			$data .=$info['Member']['id'].",";
			$data .=$info['Member']['username'].",";
			$data .=$info['Member']['name'].",";
			$data .= $info['Member']['email'].","; 
			$data .=$info['Member']['password'].",";
			$data .=$info['Member']['created'].",";	
			$data .=$info['Location']['title'].","."\n";			
		}
		$fp = fopen('files/memberRecord/'.$filename,"w");
		if($fp)
		{
			fwrite($fp,$data);
		 	header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream'); 
			header('Content-Disposition: attachment; filename='.$name.'_Report_'.date("d/m/Y").".csv");
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public'); 
			header('Content-Length: ' . filesize('files/memberRecord/'.$filename));
			/* ob_clean(); */
			readfile('files/memberRecord/'.$filename);
			unlink('files/memberRecord/'.$filename); 
			fclose($fp);
			flush();
			exit;	
		}
}

}