<?php
App::uses('AppController', 'Controller');
App::uses('File', 'Utility');
App::uses('CakeEmail', 'Network/Email');

class UsersController extends AppController {

	public $components= array('RequestHandler');
	var $uses= array('User');
	
	public function beforeFilter()
	{
		parent::beforeFilter();
		$this->Auth->allow(array());
		$this->loadModel('UserActivity');
	}

	
	
	public function admin_login(){ //start of func login//
	
	  	 if ($this->request->is('Post')) {		
            App::Import('Utility', 'Validation');
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
			}
			if(!empty($x)){
            if($x['User']['user_type'] == '1' && $x['User']['status'] == '1'){
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

				  
	public function admin_dashboard() { 
		$this->loadModel("User");
		$this->User->recursive = 0;
    	$x = $this->User->find('all',array(    				
    					"order"=>"User.id ASC"
    				));
    	$this->set("users", $x);
		$y = $this->User->read('all');
    	$this->set("use_count", $x);
		$this->set(compact('x'));	
		$this->loadModel("Activity");
		$x = $this->Activity->find('all',array(    				
    					"order"=>"Activity.id ASC"
    	));
		$y = $this->Activity->read('all');
    	$this->set("act_count", $x);		
    	// $this->set("users", $x);
        
	} //end of func dashboard//
	
	public function admin_index() { 
		if($this->request->is('post')){
			$keyword = trim($this->request->data['keyword']);
					if(!empty($keyword)) {
						/*  $r = $this->User->query("SELECT User.* from users as User LEFT JOIN user_activities as ua ON(User.usr_id=ua.usr_id) LEFT JOIN user_matches as um ON(User.usr_id=um.user_id) where User.email LIKE '%$keyword%' OR User.username LIKE '%$keyword%' OR User.name LIKE '%$keyword%' OR User.location LIKE '%$keyword%' OR ua.activity_name LIKE '%$keyword%' OR um.matches LIKE '%$keyword%'"); */
						
						 
						 $records = $this->User->find('all', array('conditions' => array("OR" => array("User.email LIKE" => "%$keyword%" , "User.username LIKE" => "%$keyword%","User.name LIKE" => "%$keyword%","User.location LIKE" => "%$keyword%"))));
						 //debug($records); 
					}
					$this->set("customers",$records,$this->paginate());
				if(count($records) == 0){
					$this->Session->setFlash("No Record found with this keyword please use another one.");
				}
			
			if(empty($keyword)){
				  $this->User->recursive = 0;
				  $this->set('customers', $this->paginate());
				  $this->Session->setFlash("Please choose some keywords to search..");
			}
		} else{
			$this->User->recursive = 0;
			$this->paginate = array('conditions'=>array('User.user_type'=>''),'order' => array('User.id' => 'desc'),'limit' =>10);
			$this->set('customers', $this->paginate());
		}	

	}

	public function admin_admin() { 
		if($this->request->is('post')){
			$keyword = trim($this->request->data['keyword']);
					@$type =  $this->request->data['type'];
					if(!empty($keyword)){
						 $records = $this->User->find('all', array('conditions' => array("OR" => array("User.email LIKE" => "%$keyword%" , "User.username LIKE" => "%$keyword%","User.name LIKE" => "%$keyword%"))));
					} else if(!empty($type)){
						  $records = $this->User->find('all', array('conditions' =>array("User.usertype_id" => $type)));
					}
					$this->set("customers",$records,$this->paginate());
				if(count($records) == 0){
					$this->Session->setFlash("No Record found with this keyword please use another one.");
				}
			
			if(empty($keyword)&&empty($type)){
				  $this->User->recursive = 0;
						  $this->set('customers', $this->paginate());
				  $this->Session->setFlash("Please choose some keywords to search..");
			}
		} else{
			$this->User->recursive = 0;
			$this->paginate = array('conditions'=>array('User.user_type'=>'1'),'order' => array('User.id' => 'desc'),'limit' =>10);
			$this->set('customers', $this->paginate());
		}	

	}

	public function admin_activities($fb_id){
	    $this->loadModel('UserActivity');
		$this->set('fb_id',$fb_id);
		if($this->request->is('post')){              
              @$keyword = $this->request->data['keyword'];	
               if($keyword) {
                    $activities = $this->UserActivity->find('all',array('conditions'=>array('OR'=>array('UserActivity.id LIKE'=>"%$keyword%",'UserActivity.activity_name LIKE'=>"%$keyword%"),'AND'=>array('UserActivity.usr_id'=>$fb_id))));				
               } 
               if(empty($activities)){
                         $this->Session->setFlash(__("Please try again,We didn't get your query."));
                 }		
               $this->Set('activities',$activities,$this->paginate());
		} else{
				  $this->paginate = array(
					'conditions' => array('UserActivity.usr_id'=>$fb_id),
					'limit' => 10,
					'order' => array('id' => 'desc')
					);
			 
				 $activities = $this->paginate('UserActivity');
				 $this->set('activities', $activities);			
		}
	} 
	
	public function admin_matches($fb_id){
	    $this->loadModel('UserMatch');
		$this->set('fb_id',$fb_id);
		if($this->request->is('post')){              
              @$keyword = $this->request->data['keyword'];	
               if($keyword) {
                    $matches = $this->UserMatch->find('all',array('conditions'=>array('OR'=>array('UserMatch.id LIKE'=>"%$keyword%",'UserMatch.matches LIKE'=>"%$keyword%"),'AND'=>array('UserMatch.user_id'=>$fb_id))));				
               } 
               if(empty($matches)){
                         $this->Session->setFlash(__("Please try again,We didn't get your query."));
                 }		
               $this->Set('matches',$matches,$this->paginate());
		} else{
				  $this->paginate = array(
					'conditions' => array('UserMatch.user_id'=>$fb_id),
					'limit' => 10,
					'order' => array('id' => 'desc')
					);
			 
				 $matches = $this->paginate('UserMatch');
				 $this->set('matches', $matches);			
		}
	} 
	
	public function admin_logout()
    {
        $this->Auth->logout();
        $this->Session->setFlash('Logged out.');
        $this->redirect(array('controller'=>'Users','action'=>'admin_login'));
    }
	
	public function admin_changepass(){
		 if ($this->request->is('post')) {
			
			$password =AuthComponent::password($this->data['User']['opass']);
            $em= $this->Auth->user('id');
			
			$pass=$this->User->find('first',array('conditions'=>array('AND'=>array('User.id' => $em))));
			//debug($pass); exit;
			if($pass['User']['password'] == $password){
				if($this->data['User']['password'] != $this->data['User']['cpass'] ){
					$this->Session->setFlash("New password and Confirm password field do not match");
				} else {
					$this->User->data['User']['opass'] = $this->data['User']['password'];
					$this->User->id = $pass['User']['id'];
					  if($this->User->exists()){
						$pass= array('User'=>array('password'=>$this->request->data['User']['password']));
						if($this->User->save($pass)) {
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
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$this->set('user', $this->User->read(null, $id));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
            
                //debug($this->request->data);die;
                $userdata=$this->Auth->User();
		if ($this->request->is('post')) {
			$this->request->data['User']['ip'] = $this->RequestHandler->getClientIp();
			$this->request->data['User']['status'] = '1' ;
			$this->User->create();
			if ($this->User->save($this->request->data)) {
                                $id=$this->User->getLastInsertId();
                                    $this->Session->setFlash(__('The user has been saved'));
                                    $this->redirect(array('action' => 'index'));
				
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		}
                
             //   $roles=$this->Role->find('all',  array('conditions'=>  array('Role.status'=>1),'fields'=>array('Role.role_name','Role.id')));
                      
              //  $this->set('roles',$roles);
                $this->set('action_type','add');
		$this->viewPath="Users";
		$this->viewPath="Users";
		$this->render('admin_edit');
	}
	
	
	public function admin_addadmin() {
		if ($this->request->is('post')) {
			$this->request->data['User']['user_type'] = 1 ;
			$this->request->data['User']['ip'] = $this->RequestHandler->getClientIp();
			$this->request->data['User']['status'] = '1' ;
			$this->User->create();
			if ($this->User->saveAll($this->request->data)) {
				$this->Session->setFlash(__('The new Admin has been saved'));
				$this->redirect(array('action' => 'admin'));
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
		$this->User->id = $id;
              
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
		        if ($this->User->save($this->request->data)) {
					$this->Session->setFlash(__('The user has been saved'));
					$this->redirect(array('controller'=>'users','action' => 'index'));
				} 
				else {
					$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		} else {
                        
                        
			$this->request->data = $this->User->read(null, $id);
		}
              //  $roles=$this->Role->find('all',  array('conditions'=>  array('Role.status'=>1),'fields'=>array('Role.role_name','Role.id')));
                        
                        
             //   $this->set('roles',$roles);
                $this->set('action_type','edit');
	}
	
	
	public function admin_editadmin($id = null) {
		$this->User->id = $id;
              
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
		        if ($this->User->save($this->request->data)) {
					$this->Session->setFlash(__('The admin has been saved'));
					$this->redirect(array('controller'=>'users','action' => 'admin'));
				} 
				else {
					$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		} else {
                        
                        
			$this->request->data = $this->User->read(null, $id);
		}
              //  $roles=$this->Role->find('all',  array('conditions'=>  array('Role.status'=>1),'fields'=>array('Role.role_name','Role.id')));
                        
                        
             //   $this->set('roles',$roles);
                $this->set('action_type','edit');
	}	
/*******************************************************************************User Matches ********************************************************************************/

	 public function admin_deletematches($id = null) {
		$this->loadModel('UserMatch');
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->UserMatch->id = $id;
		$userData = $this->UserMatch->find('first',array('conditions'=>array('UserMatch.id'=>$id)));
		
		if (!$this->UserMatch->exists()) {
			throw new NotFoundException(__('Invalid user match'));
		}
		if ($this->UserMatch->delete()) {			
			$this->Session->setFlash(__('user match deleted successfully'));
			$this->redirect(array('action' => 'matches'));
		}
		$this->Session->setFlash(__('user match was not deleted'));
		$this->redirect(array('action' => 'matches'));
	} 

	public function admin_activatematches($id = null)
    {
		$this->loadModel('UserMatch');
        $this->UserMatch->id = $id;
        if ($this->UserMatch->exists()) {
            $x = $this->UserMatch->save(array(
                'UserMatch' => array(
                    'status' => '1'
                )
            ));
            $this->Session->setFlash("user match activated successfully.");
            $this->redirect(array(
                'action' => 'matches'
            ));
        } else {
            $this->Session->setFlash("Unable to activate user match.");
            $this->redirect(array(
                'action' => 'matches'
            ));
        }        
    }
    
    
    public function admin_blockmatches($id = null)
    {
		$this->loadModel('UserMatch');
        $this->UserMatch->id = $id;
        if ($this->UserMatch->exists()) {
            $x = $this->UserMatch->save(array(
                'UserMatch' => array(
                    'status' => '0'
                )
            ));
            $this->Session->setFlash("user match blocked successfully.");
            $this->redirect(array(
                'action' => 'matches'
            ));
        } else {
            $this->Session->setFlash("Unable to block user match.");
            $this->redirect(array(
                'action' => 'matches'
            ));
        }
        
    }
/************************************************************************* End User Matches*********************************************************************************/


/*******************************************************************************User Activities ********************************************************************************/

	 public function admin_deleteactivities($id = null,$fb_id) {
		$this->loadModel('UserActivity');
	
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->UserActivity->id = $id;
		$userData = $this->UserActivity->find('first',array('conditions'=>array('UserActivity.id'=>$id)));
		
		if (!$this->UserActivity->exists()) {
			throw new NotFoundException(__('Invalid user match'));
		}
		if ($this->UserActivity->delete()) {			
			$this->Session->setFlash(__('user activity deleted successfully'));
			$this->redirect(array('action' => 'activities',$fb_id));
		}
		$this->Session->setFlash(__('user activity was not deleted'));
		$this->redirect(array('action' => 'activities',$fb_id));
	} 

	public function admin_activateactivities($id = null)
    {
		$this->loadModel('UserActivity');
        $this->UserActivity->id = $id;
        if ($this->UserActivity->exists()) {
            $x = $this->UserActivity->save(array(
                'UserActivity' => array(
                    'status' => '1'
                )
            ));
            $this->Session->setFlash("user activity activated successfully.");
            $this->redirect(array(
                'action' => 'activities'
            ));
        } else {
            $this->Session->setFlash("Unable to activate user activity.");
            $this->redirect(array(
                'action' => 'activities'
            ));
        }        
    }
    
    
    public function admin_blockactivities($id = null)
    {
		$this->loadModel('UserActivity');
        $this->UserActivity->id = $id;
        if ($this->UserActivity->exists()) {
            $x = $this->UserActivity->save(array(
                'UserActivity' => array(
                    'status' => '0'
                )
            ));
            $this->Session->setFlash("user activity blocked successfully.");
            $this->redirect(array(
                'action' => 'activities'
            ));
        } else {
            $this->Session->setFlash("Unable to block user activity.");
            $this->redirect(array(
                'action' => 'activities'
            ));
        }
        
    }
/************************************************************************* End User Activites*********************************************************************************/


/**
 * admin_delete method
 *
 * @throws MethodNotAllowedException
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
	//echo $id;exit;
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->loadModel('User');
		$this->loadModel('UserActivity');
		$this->loadModel('FriendList');
		$this->loadModel('Activity');
		$this->User->id = $id;
		$userData = $this->User->find('first',array('conditions'=>array('User.id'=>$id)));
		/* echo "<pre>";print_r($userData);exit; */
		$user_id = $userData['User']['usr_id'];
		$this->UserActivity->query(' DELETE from user_activities WHERE usr_id = "'.$user_id.'" ');
		$this->FriendList->query('DELETE from friend_lists WHERE usr_id = "'.$user_id.'" ');
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->User->delete()) {			
			$this->Session->setFlash(__('User deleted successfully'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('User was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
	
	public function admin_deleteadmin($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->loadModel('User');
		$this->User->id = $id;
		$userData = $this->User->find('first',array('conditions'=>array('User.id'=>$id)));
		
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->User->delete()) {			
			$this->Session->setFlash(__('User deleted successfully'));
			$this->redirect(array('action' => 'admin'));
		}
		$this->Session->setFlash(__('User was not deleted'));
		$this->redirect(array('action' => 'admin'));
	}	
	
	public function admin_deleteall($id = null) {
        if (!$this->request->is('Ajax')) {
            throw new MethodNotAllowedException();
        }
		//$this->loadModel('Member');
		//pr($this->request['data']['Member']);exit;
		foreach ($this->request['data']['User'] as $k) {
		    $this->User->id = (int) $k;
			$userData = $this->User->find('first',array('conditions'=>array('User.id'=>(int) $k)));
			    if ($this->User->exists()) {
				$this->User->deleteAll(array('User.id'=>$k), $cascade = true);
                //$this->Customer->deleteAll();				
            }  
		   
        }        
        $this->Session->setFlash(__('Selected Users were removed.'));
       
        $this->autoRender=false;
    }
	
	public function admin_detail($id = null){
		$this->User->id = $id;
		$this->set('detail',$this->User->find('first',array('conditions'=>array('User.id'=>$id))));
		$this->loadModel('UserEducation');
	    $x = $this->UserEducation->find('all',array('conditions'=>array('UserEducation.user_id'=>$id)));
	     $this->set('edu',$x);
		 
		$this->loadModel('UserWorkSince');
	    $x1 = $this->UserWorkSince->find('all',array('conditions'=>array('UserWorkSince.user_id'=>$id)));
	     $this->set('exp',$x1);
                    }
	
	
	public function admin_activate($id = null)
    {
        $this->User->id = $id;
        if ($this->User->exists()) {
            $x = $this->User->save(array(
                'User' => array(
                    'status' => '1'
                )
            ));
            $this->Session->setFlash("User activated successfully.");
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
        $this->User->id = $id;
        if ($this->User->exists()) {
            $x = $this->User->save(array(
                'User' => array(
                    'status' => '0'
                )
            ));
            $this->Session->setFlash("User blocked successfully.");
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

	public function admin_activateadmin($id = null)
    {
        $this->User->id = $id;
        if ($this->User->exists()) {
            $x = $this->User->save(array(
                'User' => array(
                    'status' => '1'
                )
            ));
            $this->Session->setFlash("Admin activated successfully.");
            $this->redirect(array(
                'action' => 'admin'
            ));
        } else {
            $this->Session->setFlash("Unable to activate Admin.");
            $this->redirect(array(
                'action' => 'admin'
            ));
        }        
    }	
	
    public function admin_blockadmin($id = null)
    {
        $this->User->id = $id;
        if ($this->User->exists()) {
            $x = $this->User->save(array(
                'User' => array(
                    'status' => '0'
                )
            ));
            $this->Session->setFlash("Admin blocked successfully.");
            $this->redirect(array(
                'action' => 'admin'
            ));
        } else {
            $this->Session->setFlash("Unable to block admin.");
            $this->redirect(array(
                'action' => 'admin'
            ));
        }
        
    }
	
	public function admin_activateall($id = null){
		if (!$this->request->is('Ajax')) {
            throw new MethodNotAllowedException();
        }        
		
        foreach ($this->request['data']['User'] as $v) {		
			$this->User->id = $v;
			if ($this->User->exists()) {
				$x = $this->User->save(array(
					'User' => array(
						'status' => "1"
					)					
				));	        
			} 		  	          
        }
		$this->Session->setFlash(__('Selected Users Activated Successfully.', true));
		$this->autoRender = false;
    }
		
		
	public function admin_deactivateall($id = null){
            if (!$this->request->is('Ajax')) {
            throw new MethodNotAllowedException();
                }
                
                foreach ($this->request['data']['User'] as $v) {	
                                $this->User->id = $v;
                                $x = $this->User->save(array(
                                        'User' => array(
                                                'status' => "0"
                                        )					
                                ));	          
                }
                        $this->Session->setFlash(__('Selected Users were deactivated successfully.', true));
                        $this->autoRender = false;		
            }
	
	
	public function admin_profile(){
		$id = $this->Auth->User('id');
		$this->set('profile',$this->User->find('first',array('conditions'=>array('User.id'=>$id))));
		
	}
	
	public function admin_profileedit($id=null) 
	{
		$id = $this->Auth->User('id');
		$this->set('profile',$this->User->find('first',array('conditions'=>array('User.id'=>$id))));
        $x= $this->User->find('first',array('conditions'=>array('User.id'=>$id)));
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {	
		
			
			$one = $this->request->data['User']['profile_image'];
            if($this->request->data['User']['profile_image']['name']!=""){
				$imageNAME = $x['User']['id'].$x['User']['profile_image'];
				$file = new File(WWW_ROOT . 'files/profileimage/'.$imageNAME, false, 0777);
				$file->delete();
				$this->request->data['User']['profile_image'] = $x['User']['id'].$one['name'];  
              }else{
				$this->request->data['User']['profile_image'] = $x['User']['profile_image'];
            }   
		
			if ($this->User->save($this->request->data)) {
			
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
			$this->request->data = $this->User->read(null, $id);
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