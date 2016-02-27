<?php 
	App::uses('AppController', 'Controller');
	App::uses('File', 'Utility');
	App::uses('CakeEmail', 'Network/Email');
	class WebsController extends AppController {		
	public function beforeFilter(){
			parent::beforeFilter();
			$this->Auth->allow(array('getRecord','registeruser','get_category','delete_usractivity','suggested_activity','access_token','online','my_activity','add_user_activity','delete_user_activity','facebook_post_delete','post_share','post_delete','delete_user','activity_timeline','match','deleteUserActivity','updateUserCity','updatefriendlist','distance','update_post','chatting','online_friends','friend_profile','message_recieve'));
	}
	
	
	/*	
	1. Signup Api 
http://dev414.trigma.us/Buddy/webs/registeruser?access_token=56568686565612312&friend_list=&gender=1&latitude=23564.145445&longitude=21.4512357&online=1&profile_link=test.jpg&token_id=&usr_email=lavkush.ramtipathi@gmail.com&usr_id=1212121212121&usr_name=lavkush&friend_list=[{"name":"Puneet Attri","id":"10200000001","first_name":"Puneet","last_name":"Attri"},{"name":"TRun ShRma","id":"10200000002","first_name":"TRun","last_name":"ShRma"},{"name":"Mandeep Singh","id":"10200000003","first_name":"Mandeep","last_name":"Singh"},{"name":"Jitendra Kumar","id":"10200000003","first_name":"Jitendra","last_name":"Kumar"},{"name":"Noushad Shah","id":"821166534647365","first_name":"Noushad","last_name":"Shah"}]
 
	*/
/* 	dev414.trigma.us/Buddy/webs/registeruser? */
 
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
			$lat = $_REQUEST['latitude'];
			$lng = $_REQUEST['longitude'];
			$url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false';
			$json = @file_get_contents($url);
			$data=json_decode($json);  
			$status = $data->status;	
			if($status=="OK"){
				$result = $data->results[0]->formatted_address;
				 $sort = explode(",",$result);
				 krsort($sort);
				foreach ($sort as $key => $val) {
					$check[]=$val;   
				}
				$this->request->data['User']['location']  = trim($check[2]);
				$this->request->data['User']['state']  = trim($check[1]);
				$city_name  = trim($check[2]);	
				$state_name = 	trim($check[1]);
			} else {
				$this->request->data['User']['location']  = ''; 
				$this->request->data['User']['state'] = '';
				$city_name  = '';	
				$state_name	= '';
			}		
			
			$getFbIDStatus =  $this->User->find('first',array('conditions'=>array('User.usr_id'=>$_REQUEST['usr_id'])));
				if(empty($getFbIDStatus)){			
					$this->request->data['User']['divice_token']  = $_REQUEST['token_id'];
					$this->request->data['User']['password']  =  '';
					$this->request->data['User']['profile_image'] =  @$_REQUEST['profile_link'];
					$this->User->create();     			
					if ($this->User->save($this->request->data)) {
						$user_id    = $this->User->getLastInsertID();
						$userDetails =  $this->User->find('first',array('conditions'=>array('User.id'=>$user_id)));
						$friendList = $_REQUEST['friend_list'];
						if(isset($friendList) && $friendList!='') { 						
							$FriendLt = str_replace('\/','/',$friendList);
							$getFriends = json_decode($FriendLt);
							if(!empty($getFriends)){
								foreach($getFriends as $friends){
									$checkFriend =  $this->FriendList->find('first',array('conditions'=>array('FriendList.usr_id'=>$userDetails['User']['usr_id'],'FriendList.fb_id'=>$friends->id)));
									if(empty($checkFriend)){								
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
							}
						} 
						$response = array('status'=>1,'message'=>'You have successfully registered as user','user_id'=>$userDetails['User']['usr_id']); 
						echo json_encode($response);
						exit;
					} else{
						$response = array('status'=>0,'message'=>'Unable to register user'); 
						$response = array('status'=>0,'message'=>'Unable to register user'); 
						echo json_encode($response);
						exit;
					}
				} else{
						$profile_image =  @$_REQUEST['profile_link'];
						$online = $_REQUEST['online'];
						$name = $_REQUEST['usr_name'];	
						if(isset($name) && $name!=''){
							$this->User->query("Update  `users` set `state` = '".$state_name."' , `location`='".$city_name."',`profile_image`='".$profile_image."',`name`='".$name."',`online`='".$online."' where  `id`='".$getFbIDStatus['User']['id']."' ");
						} 
						$friendList = $_REQUEST['friend_list'];
						if(isset($friendList) && $friendList!=''){ 						
							$FriendLt = str_replace('\/','/',$friendList);
							$getFriends = json_decode($FriendLt);
							if(!empty($getFriends)){
								foreach($getFriends as $friends){
									$checkFriend =  $this->FriendList->find('first',array('conditions'=>array('FriendList.usr_id'=>$getFbIDStatus['User']['usr_id'],'FriendList.fb_id'=>$friends->id)));
									if(empty($checkFriend)){
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
							}
						} 						
						$response = array('status'=>1,'message'=>'You have already registered with this facebook id','user_id'=>$getFbIDStatus['User']['id']); 
						echo json_encode($response);
						exit;
				} 
				exit(); 
    }
	
	public function getCity(){
		$url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false';
		$json = @file_get_contents($url);
		$data=json_decode($json);  
		$status = $data->status;	
		if($status=="OK"){
			$result = $data->results[0]->formatted_address;
			 $sort = explode(",",$result);
			 krsort($sort);
			foreach ($sort as $key => $val) {
				$check[]=$val;   
			}
			$this->request->data['User']['location']  = trim($check[2]);;
			$city_name  = $location;			
		} else {
			$this->request->data['User']['location']  = ''; 
			$city_name  = '';		
		}	
	}
	// http://dev414.trigma.us/Buddy/webs/getRecord
	public function getRecord() {
		$getFbIDStatus =  $this->User->find('first',array('conditions'=>array('User.id'=>91)));
		$getserial = unserialize($getFbIDStatus['User']['serial']); 
		//echo "<pre>"; print_r($getserial); exit;
	}

	/*	
	2. Event Category Api 
  	http://dev414.trigma.us/Buddy/webs/get_category
	*/
	public function get_category()
	{
			$this->loadModel('Activity');
			$activity= $this->Activity->find('all',array('conditions'=>array('Activity.status'=>1),'order' => 'rand()'));
			if(!empty($activity)){
					$data['status'] = 1;
					foreach($activity as $act) {
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
	
	// http://dev414.trigma.us/Buddy/webs/access_token?usr_id=888551987887376
	public function access_token()
	{
			$this->loadModel('FriendList');
			$this->loadModel('User');
			$usr_id = $_REQUEST['usr_id'];
			$finalArr = array();
			$friends= $this->FriendList->find('all',array('conditions'=>array('FriendList.usr_id'=>$usr_id))); 
			$seft_loc= $this->User->find('first',array('conditions'=>array('User.usr_id'=>$usr_id))); 
			if(!empty($friends)){
				foreach($friends as  $friendlist){
					$friends_loc= $this->User->find('first',array('conditions'=>array('User.usr_id'=>$friendlist['FriendList']['fb_id']))); 
					
			  $theta = $seft_loc['User']['longitude']  - $friends_loc['User']['longitude'];
				$miles = (sin(deg2rad($seft_loc['User']['latitude'])) * sin(deg2rad($friends_loc['User']['latitude']))) + (cos(deg2rad($seft_loc['User']['latitude'])) * cos(deg2rad($friends_loc['User']['latitude'])) * cos(deg2rad($theta)));
				$miles = acos($miles);
				$miles = rad2deg($miles);
				$miles = $miles * 60 * 1.1515;
				$feet = $miles * 5280;
				$yards = $feet / 3;
				$kilometers = $miles * 1.609344;
					// echo "<br/>";
 					   if($kilometers <= 20)
					   {
						   		$fb_id = $friendlist['FriendList']['fb_id'];
								$userDetails= $this->User->find('first',array('conditions'=>array('User.usr_id'=>$fb_id)));
								//echo "<pre>";print_r($userDetails);
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

					   }else{
						   	$response[] = array('status'=>0,'msg'=>'data not found');
					   } 
			 unset($distance);
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
	
	// miles Record 
	 public function distance($lat1, $lon1, $lat2, $lon2) {
		  $theta = $lon1 - $lon2;
		  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		  $dist1 = acos($dist);
		  $dist2 = rad2deg($dist);
		  //echo $dist2;exit;
		  $miles = $dist * 60 * 1.1515;
		  return ($miles * 1.609344);
	}
	/*	
	4. What Happening Api 
  	http://dev414.trigma.us/Buddy/webs/activity_timeline?user_id=950728635000979
	888551987887376*/		
	public function activity_timeline() {
			 $this->loadModel('Activity');
			 $this->loadModel('UserActivity');
			 $this->loadModel('FriendList');
			 $user_id = $_REQUEST['user_id'];
			 $friends = $this->FriendList->find('all',array('conditions'=>array('FriendList.usr_id'=>$user_id))); 
			 if(!empty($friends)) {		
	
					foreach($friends as $value_user) {	
						$fb_id[] = $value_user['FriendList']['fb_id'];
					}
					$userDetails = $this->User->find('all',array('conditions'=>array('User.usr_id' => $fb_id)));	
                        if(!empty($userDetails))
						{ 
								foreach($userDetails as $key =>$value)
								{
									//date_default_timezone_set('Asia/Kolkata');
									$usr_id = $value['User']['usr_id'];
									 $profile_image = $value['User']['profile_image'];
									$todayDate = date('Y-m-d H:i:s'); 
									$todayTime = time();
									$prvTime = $todayTime+86400;
								    $d = date("Y-m-d H:i:s",$prvTime); 
									//$stop_date = date('Y-m-d H:i:s', strtotime($todayDate . ' +1 day'));	
                                     //echo ' SELECT * from  user_activities as UserActivity   WHERE (created  BETWEEN  "'.$d.'" AND "'.$todayDate.'" ) and usr_id = "'.$usr_id.'" ';
									//$uactivity = 	$this->UserActivity->query(' SELECT * from  user_activities as UserActivity   WHERE (created  BETWEEN  "'.$d.'" AND "'.$todayDate.'" ) and usr_id = "'.$usr_id.'" ');
									$uactivity = 	$this->UserActivity->query(' SELECT * from  user_activities as UserActivity   WHERE  usr_id = "'.$usr_id.'" ORDER BY ID DESC');
									//$uactivity = 	$this->UserActivity->query(' SELECT * from  user_activities as UserActivity   WHERE (created  BETWEEN  "'.$d.'" AND "'.$todayDate.'" )');
									/* echo "<pre>";print_r($uactivity);exit; */
									if(!empty($uactivity))    {      										    
											foreach($uactivity as $key => $userActivity){
												 $dattt = date('Y-m-d', strtotime('+1 day', strtotime($userActivity['UserActivity']['created'])));
													//$new_time = strtotime(date($userActivity['UserActivity']['created'] ,'+24 hours'));
													//$dat_e = date('Y-m-d H:i:s',strtotime('+24 hour',strtotime($userActivity['UserActivity']['created'])));
														if(($dattt < $todayDate) )
														{
															$active_status_expire = '0';
														}
														else{
															$active_status_expire = '1';
														}   
													 $activityID = $userActivity['UserActivity']['activity_id'];
													$date_time = $userActivity['UserActivity']['created'];
													$date_new = $userActivity['UserActivity']['create_date'];
													$expiry_param = $userActivity['UserActivity']['expiry_param'];
													$expiretime_date = $userActivity['UserActivity']['expiretime_date'];
													$expiry_date = $userActivity['UserActivity']['expiry_date'];
													$activity = $this->Activity->find('first',array('conditions'=>array('Activity.id'=>$activityID,'Activity.status' =>'1'),'order' =>'id DESC'));
													$name = $this->User->find('first',array('conditions' =>array('User.usr_id' =>$userActivity['UserActivity']['usr_id'])));
													$tomorrow = date('Y-m-d'); 
													if(!empty($activity))
													{
															$final[$activity['Activity']['id']][] = array(
																'id' => $activity['Activity']['id'],
																'name' => $name['User']['name'],
																'profile_image'=>$name['User']['profile_image'],
																'category' => 	$activity['Activity']['name'],
																'video_url' => FULL_BASE_URL.$this->webroot.'files/activities/video/'.$activity['Activity']['video'],
																'youtube_thumbnails'=>FULL_BASE_URL.$this->webroot.'files/activities/video/video-thumb/'.$activity['Activity']['video_thumb'],
																'youtube_link'=>$activity['Activity']['you_tube_link'],
																'activity_status'=>$activity['Activity']['status'],
																'date_time'=>$date_time,
																'expiry_param'=>$expiry_param,
																'expiry_date'=>$expiry_date,
																'expire' =>$expiretime_date,
																'expire_post' =>$active_status_expire
															);
														 
													}	
											 }
											
											
											} 
									
												
								}
								if(!empty($final)) {//echo "<pre>";print_r($final);exit;
							
							//ksort($final);
								//echo "<pre>";print_r($final);
						//	ksort($final);
							//echo "<pre>";print_r($final); 
						 foreach($final as $key=>$datauser){
									foreach($datauser as $userdata){
										$uname[]=$userdata['name'];
										$uimage[]=	$userdata['profile_image'];
									}
									
									rsort($datauser); 
							/* echo "<pre>";print_r($datauser); 	 
							echo $dataa = date('Y-m-d',$datauser['date_time']); */
						/* 	exit; */
								$name=array_values(array_unique($uname));
								$image = array_values(array_unique($uimage));
								$count = count($name);
									$status = ($count > 1 ? '0' : '1');
									/* echo "<pre>";print_r($datauser); 	
									echo $tomorrow = date('Y-m-d'); */
									$final1[] = array(
											'id' => $datauser[0]['id'],
											'name' => $name,
											'category' => 	$datauser[0]['category'],
											'video_url' => $datauser[0]['video_url'],
											'youtube_thumbnails'=>$datauser[0]['youtube_thumbnails'],
											'youtube_link'=>$datauser[0]['youtube_link'],
											'activity_status'=>$datauser[0]['activity_status'],
											'profile_image'=>$image,
											'status'=>$status,
											'date_time'=>$datauser[0]['date_time'],
											'expire_date'=>$datauser[0]['expiry_date'],
											'expiry_param'=>$datauser[0]['expiry_param'],
											'expire_post'=>$datauser[0]['expire_post']
										);	
									unset($uname);							
									unset($uimage);	
																		
						} 
						/* echo "<pre>";print_r($final1);exit; */
						$this->array_sort_by_column($final1, 'date_time');	
						//echo "<pre>";print_r($final1);exit; 
						
							$response = array('message'=>'Successfull','status'=>1,'data'=>$final1);
							echo json_encode($response);exit;
						
					}else{
						$response = array('message'=>'No activity found today','status'=>0);
											echo json_encode($response);exit;
					}
							
										 /* echo json_encode($final);die;
											exit; */
						}else{
							$response = array('message'=>'No activity found today','status'=>0);
											echo json_encode($response);exit;
						}
						
					
					
			 }exit; 	 	
	}
	
	   	
		function array_sort_by_column(&$arr, $col, $dir = SORT_DESC) {
			$sort_col = array();
			foreach ($arr as $key=> $row) {
				$sort_col[$key] = $row[$col];
			}
			array_multisort($sort_col, $dir, $arr);
		}
		
	/*	
	5. Delete my Activity Api 
  	http://dev414.trigma.us/Buddy/webs/delete_usractivity?id=1&usr_id=934407333286322
	*/		
	
	public function delete_usractivity() {
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
		//date_default_timezone_set('Asia/Kolkata');
		$this->loadModel('UserActivity');
		$this->request->data['UserActivity']['usr_id']  =  @$_REQUEST['usr_id'];	
		$this->request->data['UserActivity']['activity_name']  =  @$_REQUEST['category_name'];	
		$this->request->data['UserActivity']['activity_id']  =  @$_REQUEST['category_id'];	
		$this->request->data['UserActivity']['created']  =  @$_REQUEST['create_date'];		
		$this->request->data['UserActivity']['latitude']  =  @$_REQUEST['latitude'];	
		$this->request->data['UserActivity']['longitude']  =  @$_REQUEST['longitude'];	
		$this->request->data['UserActivity']['expiry_param']  =  @$_REQUEST['expiry_param'];	
		$this->request->data['UserActivity']['place']  =  @$_REQUEST['place'];	
		$this->request->data['UserActivity']['expiry_date']  =  @$_REQUEST['expire_date'];	
		$this->request->data['UserActivity']['create_date']  =  @$_REQUEST['create_date'];	
		$tomorrow = date('Y-m-d',strtotime($_REQUEST['create_date'] . "+1 days"));
		$this->request->data['UserActivity']['expiretime_date'] = $tomorrow;
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
	
	public function post_share()  {
		$this->loadModel('SharePost');
		$this->request->data['SharePost']['post_id']  =  @$_REQUEST['post_id'];	
		$this->request->data['SharePost']['access_token']  =  @$_REQUEST['access_token'];	
		$this->request->data['SharePost']['status']  =  '1';	
		$this->SharePost->create();
		if ($this->SharePost->save($this->request->data)) {
			$response = array('success'=>1,'msg'=>'Sucessfully shared facebook post');
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
		//debug($recentSharePosts);exit;
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
	
	// http://dev414.trigma.us/Buddy/webs/post_delete?post_id=888551987887376_897899946952580&access_token=392003234294832|8b61486e00b73967e154980ece91511b

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
	
	//http://dev414.trigma.us/Buddy/webs/match?usr_id=132&category_name=ss&category_id=12&create_date=13&latitude=22&longitude=12125&expiry_param=12&place=delhi&expire_date=12
	public function match() {
		$this->loadModel('UserActivity');
		$this->request->data['UserActivity']['usr_id']  =  @$_REQUEST['usr_id'];	
		$this->request->data['UserActivity']['activity_name']  =  @$_REQUEST['category_name'];	
		$this->request->data['UserActivity']['activity_id']  =  @$_REQUEST['category_id'];	
		$this->request->data['UserActivity']['created']  =  @$_REQUEST['create_date'];		
		$this->request->data['UserActivity']['latitude']  =  @$_REQUEST['latitude'];	
		$this->request->data['UserActivity']['longitude']  =  @$_REQUEST['longitude'];	
		$this->request->data['UserActivity']['expiry_param']  =  @$_REQUEST['expiry_param'];	
		$this->request->data['UserActivity']['place']  =  @$_REQUEST['place'];
		$this->request->data['UserActivity']['expiry_date']  =  @$_REQUEST['"expire_date"'];
		$checkUser = $this->UserActivity->find('first',array('conditions'=>array('UserActivity.usr_id'=>$_REQUEST['usr_id'],'UserActivity.activity_id'=>$_REQUEST['category_id'])));
		if(empty($checkUser)) {
			$this->UserActivity->create();
			if($this->UserActivity->save($this->request->data)){
				$response = array('message'=>'You have successfull added this activity','status'=>1);
				echo json_encode($response);exit;
			}	
		} else {
		$response = array('message'=>'Allready added this activity','status'=>0);
		echo json_encode($response);exit;
		}
	}
	
// UPDATE USER LOCATION API 	
//http://dev414.trigma.us/Buddy/webs/updateUserCity?user_id=888551987887376&latitude=30.72755119&longitude=76.84685455
public function updateUserCity() {
	$this->loadModel('User');
	$user_id = $_REQUEST['user_id'];
	 $lat = (int)(($_REQUEST['latitude']*100))/100;
      $lng = (int)(($_REQUEST['longitude']*100))/100;
	 // $lat = round($_REQUEST['latitude'], 2);
  	// $lng = round($_REQUEST['longitude'], 2);
	if(!empty($_REQUEST['user_id']))	{
		$check = $this->User->find('first',array('conditions'=>array('User.usr_id'=>$_REQUEST['user_id'])));
		if(!empty($check))  {
			$url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false';
			$json = @file_get_contents($url);
			$data=json_decode($json);
			$status = $data->status;	
				if($status=="OK"){
						$result = $data->results[0]->formatted_address;
						 $sort = explode(",",$result);
						 krsort($sort);
						foreach ($sort as $key => $val) {
							$check[]=$val;   
						}
					$this->request->data['User']['location']  = trim($check[2]);
					$this->request->data['User']['state']  = trim($check[1]);
					$this->User->query(' UPDATE users set state = "'.$this->request->data['User']['state'].'" , location = "'.$this->request->data['User']['location'].'" , latitude = "'.$_REQUEST['latitude'].'" , longitude = "'.$_REQUEST['longitude'].'"  WHERE usr_id = "'.$user_id.'" ');	
					$response = array('message'=>'Successfull','status'=>1);
					echo json_encode($response);exit;
				}		
		} else {
				$response = array('message'=>'Invalid user','status'=>0);
				echo json_encode($response);exit;
		}		
	} 

}

 public function updatefriendlist() {
	$this->loadModel('User');
	$this->loadModel('FriendList');
	$user_id = $_REQUEST['user_id'];
	$friendList = $_REQUEST['friend_list'];
	$access_token = $_REQUEST['access_token'];
	if(!empty($_REQUEST['user_id'])) {
		$userDetails =  $this->User->find('first',array('conditions'=>array('User.usr_id'=>$_REQUEST['user_id'])));
		if(!empty($userDetails)) {
			$this->FriendList->deleteAll(array('FriendList.usr_id'=>$user_id));
					if(isset($friendList) && $friendList!='') { 						
							$FriendLt = str_replace('\/','/',$friendList);
							$getFriends = json_decode($FriendLt);
							if(!empty($getFriends)){
								foreach($getFriends as $friends){								
										$this->request->data['FriendList']['usr_id']  =  $_REQUEST['user_id'];	
										$this->request->data['FriendList']['access_token']  =  $_REQUEST['access_token'];	
										$this->request->data['FriendList']['name']  =  $friends->name;	
										$this->request->data['FriendList']['fb_id']  =   $friends->id;	
										$this->request->data['FriendList']['first_name']  =  $friends->first_name;	
										$this->request->data['FriendList']['last_name']  =  $friends->last_name;	
										$this->FriendList->create();
										$this->FriendList->save($this->request->data);
								}
								$response = array('message'=>'Friend list update successfully ','status'=>1);
												echo json_encode($response);exit;
							}
					} else {
						$response = array('message'=>'Friend list not found ','status'=>0);
						echo json_encode($response);exit;
					}					
		}
	
	}exit;

}

public function update_post()
{
	$this->autoRender = false;
	$this->loadModel('UserActivity');
	$user_act = $this->UserActivity->find('all');
	$date_today = date('Y-m-d H:i:s');
	if(!empty($user_act))
	{
			foreach($user_act as $key =>$val)
			{
				$da_te = date('Y-m-d H:i:s',strtotime('+24 hour',strtotime($val['UserActivity']['created'])));
				if($date_today  > $da_te)
				{
					$this->UserActivity->updateAll(array("UserActivity.expire_post"=>"1"),array("UserActivity.id"=>$val['UserActivity']['id']));	
				}
			}
	}
	
}

/************************************************************************  Chatting Module **************************************************************************************/

//http://dev414.trigma.us/Buddy/Webs/online_friends?usr_id=888551987887376

public function online_friends()
{
	$this->loadModel('FriendList');
	$this->loadModel('User');
	$id = $_REQUEST['usr_id'];
	$online = $this->FriendList->find('all',array('conditions' =>array('FriendList.fb_id' =>$id) ));
	/*  echo "<pre>";print_r($online); */
	if(!empty($online))
	{		$response				=	array ('success'=>1,'message'=>'friend found');
		   foreach($online as $key => $value)
		   {
					$user_profile = $this->User->find('first',array('conditions' =>array('User.usr_id' =>$value['FriendList']['usr_id'])));
				   	
					$response['data'][] = array(
												'id' =>$value['FriendList']['id'],
												'usr_id' =>$value['FriendList']['usr_id'],
												'profile_pic' =>$user_profile['User']['profile_image'],
												'usr_name' =>$user_profile['User']['name'],
												'online_status' =>$user_profile['User']['online']
										);
					//echo json_encode($response);exit;
		   }
		   echo json_encode($response);exit;
			
	}
	else{
			$response[] = array('message'=>'Friend list not found ','status'=>0);
			echo json_encode($response);exit;
	}
}



//http://dev414.trigma.us/Buddy/Webs/chatting?from_usrid=950728635000979&to_usrid=888551987887376&msg=aaaaaa&content_type=aa&date=&latitude=234&longitude=234

public function chatting()
{
	$this->loadModel('Chat');
	$data['Chat']['from_usrid'] = $_REQUEST['from_usrid'];
	$data['Chat']['to_usrid'] = $_REQUEST['to_usrid'];
	$data['Chat']['msg'] = $_REQUEST['msg'];
	$data['Chat']['content_type'] = $_REQUEST['content_type'];
	$data['Chat']['date'] = date('Y-m-d H:i:s');
	$data['Chat']['select_date'] = $_REQUEST['date'];
	$data['Chat']['latitude'] = @$_REQUEST['latitude'];
	$data['Chat']['longitude'] = @$_REQUEST['longitude'];
	/* echo "<pre>";print_r($data);exit; */
	if($this->Chat->save($data))
	{
		                $msg_id = $this->Chat->getLastInsertId();
						if($_REQUEST['content_type'] == '0'){
		                 $response = array(
																'msg_id' =>$msg_id,
																'receice_user' =>$_REQUEST['to_usrid'],
																'sender_user' =>$_REQUEST['from_usrid'],
																'message' =>$_REQUEST['msg'],
																'content_type' =>$_REQUEST['content_type'],
																'status' =>'1',
																'msg' =>'chat found'
															);
						}
						if($_REQUEST['content_type'] == '1'){
		                 $response = array(
																'msg_id' =>$msg_id,
																'receice_user' =>$_REQUEST['to_usrid'],
																'sender_user' =>$_REQUEST['from_usrid'],
																'message' =>$_REQUEST['msg'],
																'content_type' =>$_REQUEST['content_type'],
																'latitude' =>$_REQUEST['latitude'],
																'longitude' =>$_REQUEST['longitude'],
																'status' =>'1',
																'msg' =>'chat found'
															);
						}
						//$response = array('message'=>'chatting save successfully','status'=>1);
						echo json_encode($response);exit;
	}
	else{
						$response = array('message'=>'chatting not save .something error occur','status'=>0);
						echo json_encode($response);exit;
	}
}



//http://dev414.trigma.us/Buddy/Webs/friend_profile?usr_id=950728635000979

public function friend_profile()
{
	$this->loadModel('User');
	$ids = $_REQUEST['usr_id'];
	$userdata = $this->User->find('first',array('conditions' =>array('User.usr_id' =>$ids)));
	if(!empty($userdata))
	{
		$response = array(
												'usr_id' =>$userdata['User']['usr_id'],
												'email' =>$userdata['User']['email'],
												'name' =>$userdata['User']['name'],
												'state' =>$userdata['User']['state'],
												'profile_pic' =>$userdata['User']['profile_image'],
												'location' =>$userdata['User']['location'],
												'status' =>'1',
												'message' =>'user data found'
										);
						echo json_encode($response);exit;
	}
	else{
						$response = array('message'=>'user data not found','status'=>0);
						echo json_encode($response);exit;
	}
}


//http://dev414.trigma.us/Buddy/Webs/message_recieve?from_usrid=950728635000979&to_usrid=888551987887376&msg_id=123
public function message_recieve( )
{
	$this->loadModel('Chat');
	$from_id= $_REQUEST['from_usrid'];
	$to_usr= $_REQUEST['to_usrid'];
	$msg_id= $_REQUEST['msg_id'];
	//$msg_id= $_REQUEST['msg_id'];
	/* $msg = $this->Chat->find('all',array('conditions' =>array('Chat.from_usrid' =>$from_id,'Chat.to_usrid' =>$to_usr))); */
	$msg		=	$this->Chat->find (
			'all',array (
				'conditions' => array (
					'OR' => array (
						array(
							'AND' => array(
								array('Chat.from_usrid' => $from_id),
								array('Chat.to_usrid' => $to_usr),
								array('Chat.id >' => $msg_id)
							)
						),
						array(
							'AND' => array(
								array('Chat.to_usrid' => $from_id),
								array('Chat.from_usrid' => $to_usr),
								array('Chat.id >' => $msg_id)
							)
						)
					)
				),'order'		=> array ('Chat.id ASC')
			)
		);
		/* $msgdata		=	$this->Chat->find (
			'all',array (
				'conditions' => array (
					'OR' => array (
						array(
							'AND' => array(
								array('Chat.from_usrid' => $from_id),
								array('Chat.to_usrid' => $to_usr)
							)
						),
						array(
							'AND' => array(
								array('Chat.to_usrid' => $from_id),
								array('Chat.from_usrid' => $to_usr)
							)
						)
					)
				),'order'		=> array ('Chat.id ASC')
			)
		); */
		//echo "<pre>";print_r($msg);
	/* 	echo "<pre>";print_r($msgdata);exit;  */
	if(!empty($msg))
	{
		 foreach($msg as $key =>$val_msg) {
			 if($val_msg['Chat']['content_type'] =='0'){
			//echo "<pre>";print_r($message);
			if ($val_msg['Chat']['from_usrid'] == $from_id)  {
				$chatUser = 'In';
			}  else  {
				$chatUser = 'Out';
			}
		/* $response = 'status' =>'1'; */
			$response['status'] =  '1' ;
			$response['Chat'][]= array(
												'chat_id' =>$val_msg['Chat']['id'],
												'msg_id' =>$val_msg['Chat']['id'],
												'content_type' =>$val_msg['Chat']['content_type'],
												'receice_user' =>$val_msg['Chat']['to_usrid'],
												'sender_user' =>$val_msg['Chat']['from_usrid'],
												'message' =>$val_msg['Chat']['msg'],
												'date' =>$val_msg['Chat']['date'],
												'chat_user' =>$chatUser
												//'status' =>'1'
										);
		 }
		 else{
			  if($val_msg['Chat']['content_type'] =='1'){
			//echo "<pre>";print_r($message);
			if ($val_msg['Chat']['from_usrid'] == $from_id)  {
				$chatUser = 'In';
			}  else  {
				$chatUser = 'Out';
			}
		/* $response = 'status' =>'1'; */
			$response['status'] =  '1' ;
			$response['Chat'][]= array(
												'chat_id' =>$val_msg['Chat']['id'],
												'msg_id' =>$val_msg['Chat']['id'],
												'content_type' =>$val_msg['Chat']['content_type'],
												'receice_user' =>$val_msg['Chat']['to_usrid'],
												'sender_user' =>$val_msg['Chat']['from_usrid'],
												'message' =>$val_msg['Chat']['msg'],
												'date' =>$val_msg['Chat']['select_date'],
												'latitude' =>$val_msg['Chat']['latitude'],
												'longitude' =>$val_msg['Chat']['longitude'],
												'chat_user' =>$chatUser
												//'status' =>'1'
										);
		 }
		 }
		 }
										echo json_encode($response);exit;
	}
	/* else if(!empty($msgdata))
	{
		 foreach($msgdata as $key =>$val_msgdata) {
			//echo "<pre>";print_r($message);
			if ($val_msgdata['Chat']['from_usrid'] == $from_id)  {
				$chatUser = 'In';
			}  else  {
				$chatUser = 'Out';
			}
		/* $response = 'status' =>'1'; 
			$response['status'] =  '1' ;
			$response['Chat'][]= array(
												'chat_id' =>$val_msgdata['Chat']['id'],
												'msg_id' =>$val_msgdata['Chat']['id'],
												'content_type' =>$val_msgdata['Chat']['content_type'],
												'receice_user' =>$val_msgdata['Chat']['to_usrid'],
												'sender_user' =>$val_msgdata['Chat']['from_usrid'],
												'message' =>$val_msgdata['Chat']['msg'],
												'date' =>$val_msgdata['Chat']['date'],
												'chat_user' =>$chatUser
												//'status' =>'1'
										);
		 }
										echo json_encode($response);exit;
	} */
	else{
						$response = array('message'=>'chat data not found','status'=>0);
						echo json_encode($response);exit;
	}
}


 
	
}