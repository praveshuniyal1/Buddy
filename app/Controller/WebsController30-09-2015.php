<?php 
	App::uses('AppController', 'Controller');
	App::uses('File', 'Utility');
	App::uses('CakeEmail', 'Network/Email');
	class WebsController extends AppController {		
	public function beforeFilter(){
			parent::beforeFilter();
			$this->Auth->allow(array('getRecord','registeruser','get_category','delete_usractivity','suggested_activity','access_token','online','my_activity','add_user_activity','delete_user_activity','facebook_post_delete','post_share','post_delete','delete_user'));
	}
	
	
	/*	
	1. Signup Api 

	http://dev414.trigma.us/Buddy/webs/registeruser?access_token=56568686565612312&friend_list=&gender=1&latitude=23564.145445&longitude=21.4512357&online=1&profile_link=test.jpg&token_id=&usr_email=lavkush.ramtipathi@gmail.com&usr_id=1212121212121&usr_name=lavkush&friend_list=[{"name":"Puneet Attri","id":"10200000001","first_name":"Puneet","last_name":"Attri"},{"name":"TRun ShRma","id":"10200000002","first_name":"TRun","last_name":"ShRma"},{"name":"Mandeep Singh","id":"10200000003","first_name":"Mandeep","last_name":"Singh"},{"name":"Jitendra Kumar","id":"10200000003","first_name":"Jitendra","last_name":"Kumar"},{"name":"Noushad Shah","id":"821166534647365","first_name":"Noushad","last_name":"Shah"}]
 
	*/
 
 
 
    public function registeruser() {      
			$this->loadModel('User');
			$this->loadModel('FriendList');
			$this->request->data['User']['name']  =  @$_REQUEST['usr_name'];	
			$this->request->data['User']['email']  =  @$_REQUEST['usr_email'];      
			$this->request->data['User']['status']  = 1;      
			$this->request->data['User']['register_date']  = date('Y-m-d'); 			
            $this->request->data['User']['registertype'] = 'facebook';		
			$this->request->data['User']['gender'] =  @$_REQUEST['gender'];		
			$this->request->data['User']['usr_id']  =  $_REQUEST['usr_id'];	
			$this->request->data['User']['online']  =  $_REQUEST['online'];	
			$this->request->data['User']['latitude']  =  $_REQUEST['latitude'];	
			$this->request->data['User']['longitude']  =  $_REQUEST['longitude'];	
			$this->request->data['User']['access_token']  =  $_REQUEST['access_token'];	
			
			$getFbIDStatus =  $this->User->find('first',array('conditions'=>array('User.usr_id'=>$_REQUEST['usr_id'])));
				if(empty($getFbIDStatus)){			
					$this->request->data['User']['divice_token']  = $_REQUEST['token_id'];
					$this->request->data['User']['password']  =  '';
					$this->request->data['User']['profile_image'] =  @$_REQUEST['profile_link'];
					$this->User->create();               
					if ($this->User->save($this->request->data)) {
						$user_id    = $this->User->getLastInsertID();
						$friendList = $_REQUEST['friend_list'];
						if(isset($friendList) && $friendList!=''){ 						
							$FriendLt = str_replace('\/','/',$friendList);
							$getFriends = json_decode($FriendLt);
							foreach($getFriends as $friends){
								$this->request->data['FriendList']['usr_id']  =  $_REQUEST['usr_id'];	
								$this->request->data['FriendList']['access_token']  =  $_REQUEST['access_token'];	
								$this->request->data['FriendList']['name']  =  $friends->name;	
								$this->request->data['FriendList']['fb_id']  =   $friends->id;	
								$this->request->data['FriendList']['first_name']  =  $friends->first_name;	
								$this->request->data['FriendList']['last_name']  =  $friends->last_name;	
								$this->FriendList->create();
								$this->FriendList->save($this->request->data);
							}
						} 
						$userDetails =  $this->User->find('first',array('conditions'=>array('User.id'=>$user_id)));
						$response = array('status'=>1,'message'=>'You have successfully registered as user','user_id'=>$userDetails['User']['usr_id']); 
						echo json_encode($response);
						exit;
					} else{
						$response = array('status'=>0,'message'=>'Unable to register user'); 
						echo json_encode($response);
						exit;
					}
				} else{
						$access_token = $_REQUEST['access_token'];
						$online = $_REQUEST['online'];
						if(isset($access_token) && $access_token!=''){
							$this->User->query("Update  `users` set `access_token`='".$access_token."',`online`='".$online."' where  `id`='".$getFbIDStatus['User']['id']."' ");
						}
						$response = array('status'=>1,'message'=>'You have already registered with this facebook id','user_id'=>$getFbIDStatus['User']['id']); 
						echo json_encode($response);
						exit;
				} 
				exit(); 
    }
	// http://dev414.trigma.us/Buddy/webs/getRecord
	public function getRecord(){
		$getFbIDStatus =  $this->User->find('first',array('conditions'=>array('User.id'=>91)));
		$getserial = unserialize($getFbIDStatus['User']['serial']); 
		echo "<pre>"; print_r($getserial); exit;
	}

	/*	
	2. Event Category Api 

  	http://dev414.trigma.us/Buddy/webs/get_category
 
	*/
	
	public function get_category(){

			$this->loadModel('Activity');
			$activity= $this->Activity->find('all',array('conditions'=>array('Activity.status'=>1)));
			if(!empty($activity)){
					$data['status'] = 1;
					foreach($activity as $act){
						$youtube_video_id = $this->get_youtube_id($act['Activity']['you_tube_link']);
						
						$data['Activities'][] = array('id'=>$act['Activity']['id'],
																	  'category'=>$act['Activity']['name'],
																	  'video_url'=>FULL_BASE_URL.$this->webroot.'files/activities/video/'.$act['Activity']['video'],
																	  'youtube_link'=>$act['Activity']['you_tube_link'],
																	  'youtube_thumbnails'=> 'http://img.youtube.com/vi/'.$youtube_video_id.'/0.jpg',
																	  'created'=>$act['Activity']['created']
						
													);
					}
				echo json_encode($data); 
				exit;
		    } else {
				$data = array('status'=>0,'msg'=>'activities  does not exist.');
				echo json_encode($data); 
				exit;
			}
	}
	
	
	public function get_youtube_id($url){
		$link = $url;
		$video_id = explode("?v=", $link); // For videos like http://www.youtube.com/watch?v=...
		if (empty($video_id[1]))
			$video_id = explode("/v/", $link); // For videos like http://www.youtube.com/watch/v/..

		@$video_id = explode("&", $video_id[1]); // Deleting any other params
		$video_id = $video_id[0];
		return $video_id;
	}
	/*	
	3. All Users List Api 

  	http://dev414.trigma.us/Buddy/webs/access_token?usr_id=888061494604731
 
	*/	
/*
	public function access_token(){
			$this->loadModel('FriendList');
			$usr_id = $_REQUEST['usr_id'];
			$finalArr = array();
			$friends= $this->FriendList->find('all',array('conditions'=>array('FriendList.usr_id'=>$usr_id)));
			if(!empty($friends)){
				foreach($friends as  $friendlist){
					$finalArr[]= $friendlist['FriendList'];
				}			
				$response = array('data'=>$finalArr,'success'=>1,'msg'=>'data Found');
			} else {
				$response = array('status'=>0,'msg'=>'data not found');			
			}
			echo json_encode($response);
			exit;
	}	*/
	
	
	public function access_token(){
			$this->loadModel('FriendList');
			$this->loadModel('User');
			$usr_id = $_REQUEST['usr_id'];
			$finalArr = array();
		/*	$usersDetail= $this->User->find('first',array('conditions'=>array('User.usr_id'=>$usr_id)));
			if(!empty($usersDetail)){
						$userData[] = 	array('id'=>$usersDetail['User']['id'],'usr_id'=>$usersDetail['User']['usr_id'],'name'=>$usersDetail['User']['name'],'access_token'=>$usersDetail['User']['access_token'],'fb_id'=>$usersDetail['User']['usr_id'],'first_name'=>$usersDetail['User']['name'],'last_name'=>$usersDetail['User']['last_name'],'created'=>$usersDetail['User']['created']);
			} */
			$friends= $this->FriendList->find('all',array('conditions'=>array('FriendList.usr_id'=>$usr_id))); 
			if(!empty($friends)){
				foreach($friends as  $friendlist){
					$fb_id = $friendlist['FriendList']['fb_id'];
					$userDetails= $this->User->find('first',array('conditions'=>array('User.usr_id'=>$fb_id)));
					if(isset($userDetails['User']['access_token']) && $userDetails['User']['access_token']!=''){
						$access_token = $userDetails['User']['access_token'];
						$data[] = array('id'=>$friendlist['FriendList']['id'],
												'usr_id'=>$friendlist['FriendList']['usr_id'],
												'name'=>$friendlist['FriendList']['name'],
												'access_token'=>$access_token,
												'fb_id'=>$friendlist['FriendList']['fb_id'],
												'first_name'=>$friendlist['FriendList']['first_name'],
												'last_name'=>$friendlist['FriendList']['last_name'],
												'created'=>$friendlist['FriendList']['created'],
								 );						
					} 
				}
			//	$getData = array_merge($userData,$data);
				if(!empty($data)){
					$response = array('success'=>1,'msg'=>'data Found','data'=>$data);
				} else {
					$response = array('status'=>0,'msg'=>'data not found');	
				}
			} else {
				$response = array('status'=>0,'msg'=>'data not found');			
			}
			echo json_encode($response);
			exit;
	}		
	
	/*	
	4. What Happening Api 

  	http://dev414.trigma.us/Buddy/webs/activity_timeline
 
	*/		
	
	public function activity_timeline(){
			/*$this->loadModel('Activity');
			
			$this->request->data['Suggestion']['usr_id']  =  @$_REQUEST['usr_id'];	
			$this->request->data['Suggestion']['lattitude']  =  @$_REQUEST['lattitude'];	
			$this->request->data['Suggestion']['longitude']  =  @$_REQUEST['lattitude'];			

			$activity= $this->Activity->find('all',array('conditions'=>array('Activity.status'=>1)));	*/
				
	}		
	
	/*	
	5. Delete my Activity Api 

  	http://dev414.trigma.us/Buddy/webs/delete_usractivity?id=1&usr_id=934407333286322
 
	*/		
	
	public function delete_usractivity(){
		$this->loadModel('UserActivity');
		$id  =  @$_REQUEST['id'];	
		$usr_id  =  @$_REQUEST['usr_id'];	
		$userActivity = $this->UserActivity->find('all',array('conditions'=>array('UserActivity.id'=>$id,'UserActivity.usr_id'=>$usr_id)));
		if(!empty($userActivity)){
			$this->UserActivity->id = $id;
			if ($this->UserActivity->delete($id,true)) {
				$response = array('status'=>1,'msg'=>'Activity Successfully removed.');
				echo json_encode($response); 
				exit;			
			}
		} else {
				$response = array('status'=>0,'msg'=>'Unable to remove Activity.');
				echo json_encode($response); 
				exit;				
		}
		
	}		

	/*	
	6. Suggestion Api 

  	http://dev414.trigma.us/Buddy/webs/suggested_activity?usr_id=934407333286322&usr_name=Mandeep%20Singh&suggest_activity=tea
 
	*/		
	
	public function suggested_activity(){
		$this->loadModel('Suggestion');
		$this->request->data['Suggestion']['usr_id']  =  @$_REQUEST['usr_id'];	
		$this->request->data['Suggestion']['usr_name']  =  @$_REQUEST['usr_name'];	
		$this->request->data['Suggestion']['suggest_activity']  =  @$_REQUEST['suggest_activity'];	
		$this->Suggestion->create();
		if ($this->Suggestion->save($this->request->data)) {
			$response = array('success'=>1,'msg'=>'Thanks for suggestion');
			echo json_encode($response);
			exit;
		} else {
			$response = array('success'=>0,'msg'=>'Unable for suggestion');
			echo json_encode($response);
			exit;		
		}
	}
	
	/*	
	7. My Activity Api 

  	http://dev414.trigma.us/Buddy/webs/my_activity?user_id=934407333286322
 
	*/		
	
	public function my_activity(){
			$this->loadModel('User');
			$this->loadModel('UserActivity');
			$this->request->data['UserActivity']['usr_id']  =  @$_REQUEST['usr_id'];	
			$finalArr = array();
			$userActivity = $this->UserActivity->find('all',array('conditions'=>array('UserActivity.usr_id'=>$_REQUEST['usr_id']),'fields'=>array('id','activity_name as activity')));
			//debug($userActivity);	
			foreach($userActivity as $usrActivity){
					$finalArr[] = $usrActivity['UserActivity'];
			}
			if(!empty($userActivity)){
				$response = array('data'=>$finalArr,'success'=>1,'msg'=>'data Found');
			} else {
				$response = array('status'=>0,'msg'=>'data not found');			
			}
			echo json_encode($response);
			exit;
	}	
	
	/*	
	8. Add User Activity Api 

  	http://dev414.trigma.us/Buddy/webs/add_user_activity
	*/	
	
	public function add_user_activity(){
		$this->loadModel('UserActivity');
		
		$this->request->data['UserActivity']['usr_id']  =  @$_REQUEST['usr_id'];	
		$this->request->data['UserActivity']['activity_name']  =  @$_REQUEST['category_name'];	
		$this->request->data['UserActivity']['activity_id']  =  @$_REQUEST['category_id'];	
		$this->request->data['UserActivity']['created']  =  @$_REQUEST['create_date'];		
		$this->request->data['UserActivity']['latitude']  =  @$_REQUEST['latitude'];	
		$this->request->data['UserActivity']['longitude']  =  @$_REQUEST['longitude'];	
		$this->request->data['UserActivity']['expiry_param']  =  @$_REQUEST['expiry_param'];	
		$this->request->data['UserActivity']['place']  =  @$_REQUEST['place'];	
		if(isset($_REQUEST['expiry_param']) && $_REQUEST['expiry_param']==(int)3){ 
			$this->request->data['UserActivity']['expiry_date']  =  $_REQUEST['expire_date'];
		}
		if(isset($_REQUEST['expiry_param']) && $_REQUEST['expiry_param']==(int)4){ 
			$this->request->data['UserActivity']['expiry_date']  =  $_REQUEST['expire_date'];
		}		
		$usr_activity_data_exist = $this->UserActivity->find('first',array('conditions'=>array('activity_id'=>$_REQUEST['category_id'],'usr_id'=>$_REQUEST['usr_id'])));
		//debug($usr_activity_data_exist);
		if(empty($usr_activity_data_exist)){
			$this->UserActivity->create();
			if($this->UserActivity->save($this->request->data['UserActivity'])){
					$lastInsetActID = $this->UserActivity->getLastInsertId();
					$response = array('category_id'=>$_REQUEST['category_id'],'activity_id'=>$lastInsetActID,'success'=>1,'msg'=>'activity added');
					
			} else {
					$response = array('success'=>0,'msg'=>'Unable for add activity');				
			}
		} else {
				$response = array('category_id'=>$_REQUEST['category_id'],'activity_id'=>$usr_activity_data_exist['UserActivity']['id'],'success'=>1,'msg'=>'category id already exist');
		}
		echo json_encode($response);
		exit;
	}
	
	
	/*	
	9. Add activity Api 

  	http://dev414.trigma.us/Buddy/webs/delete_user_activity?id=24&usr_id=934407333286322
 
	*/		
	
	public function delete_user_activity(){
			$this->loadModel('UserActivity');
			$this->request->data['UserActivity']['usr_id']  =  @$_REQUEST['usr_id'];	
			$this->request->data['UserActivity']['id']  =  @$_REQUEST['id'];	
			
			$this->UserActivity->query("delete from user_activities where id='".$_REQUEST['id']."' AND usr_id='".$_REQUEST['usr_id']."' ");
			$response = array('success'=>1,'msg'=>'Activity Successfully removed');
			echo json_encode($response);
			exit;
	}		

	/*	
	10. Delete Account Api 

  	http://dev414.trigma.us/Buddy/webs/delete_user?usr_id=888061494604731
 
	*/		
	
	public function delete_user(){
		$this->loadModel('FriendList');
		$this->loadModel('User');
		$this->loadModel('UserActivity');
		$this->loadModel('Suggestion');
		$usr_id  =  $_REQUEST['usr_id'];
		$userDetails= $this->User->find('first',array('conditions'=>array('User.usr_id'=>$usr_id)));
		if(!empty($userDetails)){
			$this->User->query("DELETE FROM  `users`  where  `usr_id`='".$usr_id."' "); 
			$friendList= $this->FriendList->find('all',array('conditions'=>array('FriendList.usr_id'=>$usr_id)));
			if(!empty($friendList)){
				foreach($friendList  as $friends){
					$this->FriendList->query("DELETE FROM  `friend_lists`  where  `usr_id`='".$friends['FriendList']['usr_id']."' "); 
				} 
			}
			$userActivity= $this->UserActivity->find('all',array('conditions'=>array('UserActivity.usr_id'=>$usr_id)));
			if(!empty($userActivity)){
				foreach($userActivity  as $activites){
					$this->UserActivity->query("DELETE FROM  `user_activities`  where  `usr_id`='".$activites['UserActivity']['usr_id']."' "); 
				} 
			}			
			$userSuggestion= $this->Suggestion->find('all',array('conditions'=>array('Suggestion.usr_id'=>$usr_id)));
			if(!empty($userSuggestion)){
				foreach($userSuggestion  as $suggestions){
					$this->Suggestion->query("DELETE FROM  `suggestions`  where  `usr_id`='".$suggestions['Suggestion']['usr_id']."' "); 
				} 
			}	
			$response = array('status'=>'1','msg'=>'Successfully deleted.');
		} else {
			$response = array('status'=>'0','msg'=>'Invalid user.');
		}
		echo json_encode($response);
		exit;
	}		
	
	/*	
	11. Logout Api 

  	http://dev414.trigma.us/Buddy/webs/online?usr_id=934407333286322
 
	*/		
	
	public function online(){
		$this->loadModel('User');
		$usr_id  =  @$_REQUEST['usr_id'];
		if($usr_id!=''){
			$status = 0;
			$this->User->query("Update  `users` set `online`='".$status."' where  `usr_id`='".$usr_id."' ");
			$response = array('success'=>1,'msg'=>'User Logout Successfully.');
			echo json_encode($response);
			exit;			
		} else {
			$response = array('success'=>0,'msg'=>'Unable to Logout User');
			echo json_encode($response);
			exit;				
		}
	}
	
	/*	
	6. Facebook post share Api 

  	http://dev414.trigma.us/Buddy/webs/post_share?post_id=821166534647365_835989846498367&access_token=CAAFkhl2H7DABAMPmvHTps7LLmQ89JmRfqs4Jnoz8nz6CYZCEJevTFTND8TYr18hjwW2EuI2ZC7nTMxgJDBOYWS7BjfdOUXzBZCHk1dBAKYfVXvdbEIEWM8taZCYuz8WwOPoAmFioLYwsdZCzqHY8bb0iNNcTSTTm8gSd8H6rgwqbZA593DREFAOsGS4HL8z4Ib5ZBXdP50ygkaCvbwlPMpt
 
	*/		
	
	public function post_share(){
		$this->loadModel('SharePost');
		$this->request->data['SharePost']['post_id']  =  @$_REQUEST['post_id'];	
		$this->request->data['SharePost']['access_token']  =  @$_REQUEST['access_token'];	
		$this->request->data['SharePost']['status']  =  '1';	
		$this->SharePost->create();
		if ($this->SharePost->save($this->request->data)) {
			$response = array('success'=>1,'msg'=>'sucessfully shared facebook post');
			echo json_encode($response);
			exit;
		} else {
			$response = array('success'=>0,'msg'=>'Unable to share facebook post');
			echo json_encode($response);
			exit;		
		}
	}

//  http://dev414.trigma.us/Buddy/webs/facebook_post_delete
	
	public function facebook_post_delete(){
		$this->loadModel('SharePost'); 
		$this->loadModel('User'); 
		$recentSharePosts = $this->SharePost->find('all',array('conditions'=>array('SharePost.status'=>1)));
		if(!empty($recentSharePosts)){
			foreach($recentSharePosts as $post){
					$date = $post['SharePost']['created'];
					$postDate = date('Y-m-d H:i:s', strtotime($date . " +2 hours"));
					$currentDate = date('Y-m-d H:i:s');
					if($currentDate >= $postDate) {
						$postId = $post['SharePost']['post_id'];
						$access_token = $post['SharePost']['access_token'];
						$Curl_Session = curl_init('https://graph.facebook.com/'.$postId);
						curl_setopt ($Curl_Session, CURLOPT_POST, 1);
						curl_setopt ($Curl_Session, CURLOPT_POSTFIELDS, "method=DELETE&access_token=".$access_token);
						curl_setopt ($Curl_Session, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt($Curl_Session, CURLOPT_RETURNTRANSFER, 1);
						echo $a8=curl_exec ($Curl_Session);
						$getStatus = json_decode($a8);
						if(isset($getStatus->success)){
							 $this->SharePost->query("DELETE FROM  `share_posts`  where  `id`='".$post['SharePost']['id']."' "); 
						}
						curl_close ($Curl_Session);						
				    }
			}
		}  
		exit;
	} 	
	
	// http://dev414.trigma.us/Buddy/webs/post_delete?post_id=908512445884818_916431925092870&access_token=CAAFkhl2H7DABAHRjcKerPmHiIZBJWbwZC4weAD24kBTztyW3ZAmGH4ZCbWp3DoyjU9hzBSFh7XUVTOPrAsTxhluUXAur7eUtZB0tlLwZA8DZBJbpMhotAeA6FHrCORjeZAOtGqvU3bmtkJZBfZAmTseuhGchfaRrQDZCRYmwyngZB7bT0hPPrHPBZByR54SDoZBSvP5IFZBwgxGLM5M3C5OwaGGWIvYFDMd4ZAgTf2AZD 

	public function post_delete(){ 
		$postId = $_REQUEST['post_id'];
		$access_token =  $_REQUEST['access_token'];
		
		$Curl_Session = curl_init('https://graph.facebook.com/'.$postId);
		curl_setopt ($Curl_Session, CURLOPT_POST, 1);
		curl_setopt ($Curl_Session, CURLOPT_POSTFIELDS, "method=DELETE&access_token=".$access_token);
		curl_setopt ($Curl_Session, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($Curl_Session, CURLOPT_RETURNTRANSFER, 1);
		echo $a8=curl_exec ($Curl_Session);
		$getStatus = json_decode($a8);
		if(isset($getStatus->success)){
			echo 'test only.';
		}		
		curl_close ($Curl_Session);
		
		exit;	
	} 

}