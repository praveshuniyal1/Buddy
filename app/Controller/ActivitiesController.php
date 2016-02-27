<?php
App::uses('AppController', 'Controller');
/**
 * Albums Controller
 *
 * @property Album $Album
 */
class  ActivitiesController extends AppController {  
// spublic $components = array('ImageResize');
/**
 * index method
 *
 * @return void
 */
   
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('imagelist','categoryimage','like','popularimage','winnerlist','add','userLike','userUploadedImage','userReport'));
	}
	
	public function admin_index() { 
		if($this->request->is('post')){
			$keyword = trim($this->request->data['keyword']);
					@$type =  $this->request->data['type'];
					if(!empty($keyword)){
							$activity = $this->Activity->find('all',array('conditions'=>array('OR'=>array('Activity.name LIKE'=>"%$keyword%",'Activity.video LIKE'=>"%$keyword%",'Activity.you_tube_link LIKE'=>"%$keyword%"))));
					}
				$this->set("activities",$this->paginate());
			
			if(empty($keyword)&&empty($type)){
				  $this->Activity->recursive = 0;
					$this->set('activities', $this->paginate());
				  $this->Session->setFlash("Please choose some keywords to search..");
			}
		} else{
			$this->Activity->recursive = 0;
			$this->paginate = array('order' => array('Activity.id' => 'desc'),'limit' =>10);
			$this->set('activities', $this->paginate());
		}	

	}	

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
public function admin_view($id = null) {
        $this->Image->id = $id;
        if (!$this->Image->exists()) {
                throw new NotFoundException(__('Invalid Image'));
        }
        $this->set('songs', $this->Image->read(null, $id));

}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
        if ($this->request->is('post')) {
			   $this->request->data['Activity']['status']=1;
                  $once = $this->request->data['Activity']['video'];
                  $this->request->data['Activity']['video'] = str_replace("?", "@", $once['name']);  
				  $videoName = str_replace(' ','_',$once['name']);
                  $this->Activity->create();
                  $this->Activity->save($this->request->data);
                  $id = $this->Activity->getLastInsertId();
				  $this->request->data['Activity']['video'] = $id.str_replace(' ','_',$this->request->data['Activity']['video']);
                  if($this->Activity->save($this->request->data)){		   
					if ($once['error'] == 0) {
						$pth = 'files/activities/video/' . DS .$id.$videoName;
						move_uploaded_file($once['tmp_name'], $pth);      
					
						$t = time();
						$nameImg= $t.'.png';
						$flvfile = WWW_ROOT.'files' . DS . 'activities/video/'.$id.$videoName;
						$imagefile = WWW_ROOT.'files' . DS . 'activities/video/video-thumb/'.$nameImg;
						exec('ffmpeg  -i ' . $flvfile . ' -f mjpeg -vframes 1 -s 320x270 -an ' . $imagefile . '');
						$this->Activity->query("update activities set video_thumb='".$nameImg."' where id = '".$id."' ");
					}	
					$this->Session->setFlash(__('The activities has been saved'));
					$this->redirect(array('action' => 'index'));  
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
			$this->Activity->id = $id;
			if (!$this->Activity->exists()) {
					throw new NotFoundException(__('Invalid Activity'));
			}
			@$activity = $this->Activity->find('first',array('conditions'=>array('Activity.id'=>$id)));
			if ($this->request->is('post') || $this->request->is('put')) {
				 $once = $this->request->data['Activity']['video'];   
				if($once['name']){
					$this->request->data['Activity']['video'] = $id.str_replace(' ','_',$once['name']);
				}else{
					$this->request->data['Activity']['video'] = $activity['Activity']['video']; 
				}
					$videoName = str_replace(' ','_',$once['name']);
					if ($this->Activity->save($this->request->data)) {
						  if($this->request->data['Activity']['video']){
								if ($once['error'] == 0) {
									$pth = 'files/activities/video/' . DS .$id.$videoName;
									move_uploaded_file($once['tmp_name'], $pth);      
								
									$t = time();
									$nameImg= $t.'.png';
									$flvfile = WWW_ROOT.'files' . DS . 'activities/video/'.$id.$videoName;
									$imagefile = WWW_ROOT.'files' . DS . 'activities/video/video-thumb/'.$nameImg;
									exec('ffmpeg  -i ' . $flvfile . ' -f mjpeg -vframes 1 -s 320x270 -an ' . $imagefile . '');
									$this->Activity->query("update activities set video_thumb='".$nameImg."' where id = '".$id."' ");
								}	                    
						  }
						$this->Session->setFlash(__('The activity has been saved'));
						$this->redirect(array('action' => 'index'));
					} else {
							$this->Session->setFlash(__('The activity could not be saved. Please, try again.'));
					}
			} else {
					$this->request->data = $this->Activity->read(null, $id);
			}
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
			$this->Activity->id = $id;
			if (!$this->Activity->exists()) {
					throw new NotFoundException(__('Invalid activity'));
			}
			if ($this->Activity->delete($id,true)) {
				$this->Session->setFlash(__('Activity deleted'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Session->setFlash(__('Activity was not deleted'));
			$this->redirect(array('action' => 'index'));
	}
	
	
	public function admin_activate($id = null) {  
		$this->Activity->id = $id;
		if ($this->Activity->exists()) {
			$x = $this->Activity->save(array(
				'Activity' => array(
					'status' => '1'
				)
			));
			$this->Session->setFlash("Activity activated successfully.");
			$this->redirect(array(
				'action' => 'index'
			));
		} else {
			$this->Session->setFlash("Unable to activate Activity.");
			$this->redirect(array(
				'action' => 'index'
			));
		}        
	}
    
    
public function admin_block($id = null) {
    $this->Activity->id = $id;
    if ($this->Activity->exists()) {
        $x = $this->Activity->save(array(
            'Activity' => array(
                'status' => '0'
            )
        ));
        $this->Session->setFlash("Activity blocked successfully.");
        $this->redirect(array(
            'action' => 'index'
        ));
    } else {
        $this->Session->setFlash("Unable to block Image.");
        $this->redirect(array(
            'action' => 'index'
        ));
    }        
}
	
	
	
	
	
	public function admin_deleteall($id = null){
        if (!$this->request->is('Ajax')) {
            throw new MethodNotAllowedException();
        }
        foreach ($this->request['data']['Activity'] as $k) {
            $this->Activity->id = (int) $k;
            if ($this->Activity->exists()) {
                $this->Activity->delete();
            }            
        }        
        $this->Session->setFlash(__('Selected activity were removed successfully.'));
        $this->autoRender = false;
    } 
	
	public function admin_activateall($id = null){
		if (!$this->request->is('Ajax')) {
            throw new MethodNotAllowedException();
        }        
        foreach ($this->request['data']['Activity'] as $v) {	
			$this->Activity->id = $v;
			if ($this->Activity->exists()) {
				$x = $this->Activity->save(array(
					'Activity' => array(
						'status' => "1"
					)
					
				));	        					
			}		           
        }
		$this->Session->setFlash(__('Selected activity were activated successfully.', true));
		$this->autoRender = false;
    }
		
	public function admin_deactivateall($id = null){
		if (!$this->request->is('Ajax')) {
            throw new MethodNotAllowedException();
        }        
        foreach ($this->request['data']['Activity'] as $v) {
			$this->Activity->id = $v;
			if ($this->Activity->exists()) {
				$x = $this->Activity->save(array(
					'Activity' => array(
						'status' => "0"
					)					
				));	        
			}        
        }
		$this->Session->setFlash(__('Selected activity were deactivated successfully.', true));					
		$this->autoRender = false;
    }
	
}?>
