<?php echo $this->element("admin_header"); ?>
<?php echo $this->element("admin_topright"); ?>
<?php echo $this->element("admin_nav"); ?>
<?php echo $this->element("admin_sidebar");  ?> 
<style>
.export {
    float: right;
}

</style>
<div id="content">
    <div class="contentTop">
        <span class="pageTitle"><span class="icon-screen"></span>Activity Management</span>
        <ul class="quickStats">
            <li>
				<?php echo $this->Html->link($this->Html->Image('../images/icons/quickstats/user.png'),'javascript:void();',array('escape'=>false,'border'=>'0','class'=>'blueImg')); ?>
                <div class="floatR"><strong class="blue"><?php echo $this->Paginator->counter('{:count}');?></strong><span>Activities</span></div>
            </li>
        </ul>
    </div>
     <!-- Breadcrumbs line -->
    <div class="breadLine">
        <div class="bc">
            <ul id="breadcrumbs" class="breadcrumbs">
                <li><a href="<?php echo $this->Html->url(array('controller'=>'users','action'=>'admin_dashboard')); ?>">Dashboard</a></li>
                <li class="current"><a href="javascript:void(0);">Activity Management</a></li>
            </ul>
        </div>
    </div>
		
	<!-- User type search box close -->	
    <!-- Main content -->

	    
    <div class="wrapper">
     <?php $x=$this->Session->flash(); ?>
     <?php if($x){ ?>
     <div class="nNote nSuccess" id="flash">
       <div class="alert alert-success" style="text-align:center" ><?php echo $x; ?></div>
     </div><?php } ?>              
    	<!-- Chart -->
       <div class="widget check grid6">
        <div class="whead">
       <span class="titleIcon">
        <input title="Select All" class="tool-tip" id="titleCheck" name="titleCheck" type="checkbox"></span>    <h6>Activity Management</h6>
		<!--<div style="float:right;">
			   <?php echo $this->Form->create('Activity', array('controller'=>'activities','action'=>'index')); ?>	   	  
				<div style="margin-top:5px;">			
					<input type="text" name="keyword" title="Enter activity name,.."placeholder="Search the keywords" class="tipS tool-tip" autocomplete="off">
					 <input value="" type="submit" name="search">
				</div>
				<?php echo $this->Form->end();?>
		</div>-->
		</div>
       <?php if(!empty($activities)){ ?>
                      
        <div id="dyn" class="hiddenpars">
            <?php  echo $this->Form->create('Activity',array('id' => 'mbc')); ?>
            <table cellpadding="0" cellspacing="0" class="tDefault checkAll tMedia" id="checkAll" width="100%">
            <thead>
            <tr>
            <th></th>
            <th><?php echo ('Activity name'); ?></span></th>
            <th><?php echo ('Video'); ?></th>
			<th><?php echo ('Video Link'); ?></th>
			<th><?php echo ('Status'); ?></th>
			<th><?php echo('Created'); ?></th>            
            <th>Action</th>
            </tr>
            </thead>
            <tbody>
			<?php  foreach ($activities as $activity): ?>
            <tr class="gradeX">
            <td><?php echo $this->Form->checkbox("use"+$activity['Activity']['id'],array('value' => $activity['Activity']['id'],'class'=>'checkAll')); ?></td>
            <td><?php echo h($activity['Activity']['name']); ?></td>
			<td><?php echo $this->Html->image(FULL_BASE_URL.$this->webroot.'files/activities/video/video-thumb/'.$activity['Activity']['video_thumb'],array('width'=>'100')); ?></td>
			<td><?php echo h($activity['Activity']['you_tube_link']); ?></td>
			<td><?php if($activity['Activity']['status']=='1') { echo 'Activate'; } else {  echo 'Deactivate'; } ?></td>
		    <td><?php echo date("d M y",strtotime($activity['Activity']['created'])); ?></td>
            
			<td class="center">
             <form></form>
			<?php echo $this->Html->link($this->Html->image('../images/icons/admins/edit.png',array('border'=>'0','class'=>'iconb','width'=>'17')),array('action' => 'admin_edit', $activity['Activity']['id']),array('class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Edit','escape'=>false)); ?>
			
            <?php echo $this->Form->postLink($this->Html->image('../images/icons/admins/delete.png',array('border'=>'0','class'=>'iconb','width'=>'17')),array('controller'=>'Activities','action'=>'delete',$activity['Activity']['id']),array('escape'=>false,'class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Delete'),__('Are you sure you want to delete %s?', $activity['Activity']['name']));?>
			 <?php if ($activity['Activity']['status']=='0'){?>
			<?php echo $this->Form->postLink($this->Html->image('../images/icons/admins/deactivate.png',array('border'=>'0','class'=>'iconb','width'=>'17')), array('action' => 'activate', $activity['Activity']['id']),array('escape'=>false,'class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Publish'),__('Are you sure you want to activate #%s?', $activity['Activity']['name']));?><?php }else { ?>
			<?php echo $this->Form->postLink($this->Html->image('../images/icons/admins/activate.png',array('border'=>'0','class'=>'iconb','width'=>'17')), array('action' => 'block', $activity['Activity']['id']), array('escape'=>false,'class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Unpublish'),__('Are you sure you want to block #%s?', $activity['Activity']['name'])); ?><?php }?>
            </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            </table> 
            <br/><br/>
                <?php echo $this->Html->link('Delete All','javascript:void(0);',array('onclick'=>'return deleteAll();','class'=>'buttonS bRed','style'=>"margin-left:20px")); ?>
				<?php echo $this->Html->link('Published All','javascript:void(0);',array('onclick'=>'return activateAll();','class'=>'buttonS bGreen','style'=>"margin-left:40px")); ?>
				<?php echo $this->Html->link('Unpublished All','javascript:void(0);',array('onclick'=>'return deactiveAll();','class'=>'buttonS bBlue','style'=>"margin-left:40px")); ?>
		    <?php  }
                        else{?>
                            <div id="dyn" style="text-align:center;">
					No records found.
				</div>
                      <?php  }
                        ?>

          <div class="tPages">
              <ul class="pages">
				<li><?php echo $this->Paginator->first('First'); ?></li>
				<li><?php if($this->Paginator->hasPrev()){ echo $this->Paginator->prev(__('Previous'), array('tag' => false)); } ?></li>
                <li><?php echo @$this->Paginator->numbers(); ?></li>
				<li><?php if($this->Paginator->hasNext()){ echo $this->Paginator->next(__('Next'), array('tag' => false)); } ?></li>   
				<li><?php echo $this->Paginator->last('Last'); ?></li>	
              </ul>
            </div>
		    
              <div style="margin-top:10px;"></div>
           </form>
             </div>  
        </div>        
    </div>
</div>
</div>
<script type="text/javascript">
function deleteAll() {
    var anyBoxesChecked = false;
	var arr = new Array();
	var finalArr = new Array();
	
	$('#mbc input[type="checkbox"]').each(function() {
        if ($(this).is(":checked")) {
			arr.push($(this).val());
			anyBoxesChecked = true;
        }
    });
 
    if (anyBoxesChecked == false) {
		alert('Please select at least one checkbox to delete post.');
		return false;
    } else {
	
		$.each(arr, function( index, value ) {
			var res = value.split("-"); 
				finalArr.push(res[0]);
		});
		if(finalArr.length > 0){
			if(confirm("Are you sure you want to delete?")){				
				$.ajax({
					type:'POST',
					dataType: 'json',
					url:'<?php echo Router::url(array('controller'=>'activities','action'=>'admin_deleteall')); ?>',
					 data: {'Activity':finalArr},
					success:function(result){
						$('.checkAll').attr("checked", false);
						$('#titleCheck').attr("checked", false);
						window.location.reload();
					}					
				});				
				return false;
			}	
		}
		return false;
	}	
}//end of func deleteAll//

function activateAll() {
    var anyBoxesChecked = false;
	var arr = new Array();
	var finalArr = new Array();
	var error = '';
	$('#mbc input[type="checkbox"]').each(function() {
        if ($(this).is(":checked")) {
			arr.push($(this).val());
			anyBoxesChecked = true;
        }
    });
 
    if (anyBoxesChecked == false) {
		alert('Please select at least one checkbox to activate activities.');
		return false;
    } else {	
		$.each(arr, function( index, value ) {
			var res = value.split("-"); 
			if(res[1] == 1){	
				if(error.length > 0){	
					error = error+', '+res[2];
				} else {
					error = res[2];
				}
				delete arr[index];				
			} else {
				finalArr.push(res[0]);
			}
		});
		if(finalArr.length > 0){
		if(error.length > 0){
				alert('You cannot activate "'+error+'" post/s as its category status is already blocked So you have to first activate its category status.');
			}
		if(confirm("Are you sure you want to activate selected activities?")){
			$.ajax({
				type:'POST',
				dataType: 'json',
				url:'<?php echo Router::url(array('controller'=>'activities','action'=>'admin_activateall')); ?>',
				 data: {'Activity':finalArr},
				success:function(result){
					$('.checkAll').attr("checked", false);
					$('#titleCheck').attr("checked", false);
					window.location.reload();
				}					
			});					
			return true;
		}	
		}
	}	
}//end of func activateAll//

function deactiveAll() {
    var anyBoxesChecked = false;
	var arr = new Array();
	var finalArr = new Array();
	$('#mbc input[type="checkbox"]').each(function() {
        if ($(this).is(":checked")) {
			arr.push($(this).val());
			anyBoxesChecked = true;
        }
    });
 
    if (anyBoxesChecked == false) {
		alert('Please select at least one checkbox to deactivate post.');
		return false;
    } else {

		$.each(arr, function( index, value ) {
			var res = value.split("-"); 
				finalArr.push(res[0]);			
			});
		if(finalArr.length > 0){
		if(confirm("Are you sure you want to deactivate the seleted activities?")){
				
				$.ajax({
					type:'POST',
					dataType: 'json',
					url:'<?php echo Router::url(array('controller'=>'activities','action'=>'admin_deactivateall')); ?>',
					 data: {'Activity':finalArr},
					success:function(result){
						$('.checkAll').attr("checked", false);
						$('#titleCheck').attr("checked", false);
						window.location.reload();
					}					
				});
				
				return false;
		}	
		}
		return false;
	}		
}//end of func deactiveAll//
</script>