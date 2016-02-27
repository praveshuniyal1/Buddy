<?php echo $this->element("admin_header"); ?>
<?php echo $this->element("admin_topright"); ?>
<?php echo $this->element("admin_nav"); ?>
<?php echo $this->element("admin_sidebar");  ?> 
<div id="content">
    <div class="contentTop">
        <span class="pageTitle"><span class="icon-screen"></span>User Activity Management</span>
        <ul class="quickStats">
            <li>
				<?php echo $this->Html->link($this->Html->Image('../images/icons/quickstats/user.png'),'javascript:void();',array('escape'=>false,'border'=>'0','class'=>'blueImg')); ?>
                <div class="floatR"><strong class="blue"><?php echo $this->Paginator->counter('{:count}');?></strong><span>User Activities</span></div>
            </li>
        </ul>
    </div>
     <!-- Breadcrumbs line -->
    <div class="breadLine">
        <div class="bc">
            <ul id="breadcrumbs" class="breadcrumbs">
                <li><a href="<?php echo $this->Html->url(array('controller'=>'users','action'=>'admin_dashboard')); ?>">Dashboard</a></li>
                <li><a href="<?php echo $this->Html->url(array('controller'=>'users','action'=>'admin_index')); ?>">User Management</a></li>
				<li class="current"><a href="javascript::void();">User Activity Management</a></li>
            </ul>
        </div>
    </div>
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
       <span class="titleIcon"></span>    <h6>User Activity Management</h6>
		<div style="float:right;">
			   <?php echo $this->Form->create('User', array('controller'=>'users','action'=>'activities/'.$fb_id)); ?>	   	  
				<div style="margin-top:5px;">			
					<input type="text" name="keyword" title="Enter activity name,.."placeholder="Search the keywords" class="tipS tool-tip" autocomplete="off">
					 <input value="" type="submit" name="search">
				</div>
				<?php echo $this->Form->end();?>
		</div>		
		</div>
       <?php if(!empty($activities)){ ?>
                      
        <div id="dyn" class="hiddenpars">
            <?php  echo $this->Form->create('User',array('id' => 'mbc')); ?>
            <table cellpadding="0" cellspacing="0" class="tDefault checkAll tMedia" id="checkAll" width="100%">
            <thead>
            <tr>
            <th><?php echo ('S No.'); ?></span></th>
            <!--th><?php // echo ('User id'); ?></th-->
			<th><?php echo ('Activity Name'); ?></th>
            <th><?php echo('Created'); ?></th>            
            <th>Action</th>
            </tr>
            </thead>
            <tbody>
			<?php  $i=1; foreach ($activities as $act): ?>
            <tr class="gradeX">           
            <td><?php echo h($i); ?></td>
            <!--td><?php // echo h($act['UserActivity']['user_id']); ?></td-->
			<td><?php echo h($act['UserActivity']['activity_name']); ?></td>
            <td><?php echo date("d M y",strtotime($act['UserActivity']['created'])); ?></td>
            
			<td class="center"> 
             <form></form>
            <?php echo $this->Form->postLink($this->Html->image('../images/icons/admins/delete.png',array('border'=>'0','class'=>'iconb','width'=>'17')),array('controller'=>'Users','action'=>'deleteactivities',$act['UserActivity']['id'],$act['UserActivity']['usr_id']),array('escape'=>false,'class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Delete'),__('Are you sure you want to delete %s?', $act['UserActivity']['activity_name']));?>
			 <?php /*if ($act['UserActivity']['status']=='0'){?>
			<?php echo $this->Form->postLink($this->Html->image('../images/icons/admins/deactivate.png',array('border'=>'0','class'=>'iconb','width'=>'17')), array('action' => 'activateactivities', $act['UserActivity']['id']),array('escape'=>false,'class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Active'),__('Are you sure you want to activate #%s?', $act['UserActivity']['activity_name']));?><?php }else { ?>
			<?php echo $this->Form->postLink($this->Html->image('../images/icons/admins/activate.png',array('border'=>'0','class'=>'iconb','width'=>'17')), array('action' => 'blockactivities', $act['UserActivity']['id']), array('escape'=>false,'class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Block'),__('Are you sure you want to block #%s?', $act['UserActivity']['activity_name'])); ?><?php }*/?>			
            </td>
            </tr>
            <?php $i++; endforeach; ?>
            </tbody>
            </table> 
            <br/><br/>
            <?php 
			// echo $this->Html->link('Delete All','javascript:void(0);',array('onclick'=>'return deleteAll();','class'=>'buttonS bRed','style'=>"margin-left:20px")); ?>
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
	$('#mbc input[type="checkbox"]').each(function() {
        if ($(this).is(":checked")) {
			arr.push($(this).val());
			anyBoxesChecked = true;
        }
    });
 
    if (anyBoxesChecked == false) {
		alert('Please select at least one checkbox to delete User.');
		return false;
    } else {				
		if(confirm("If you delete seleted users then all its answer and scores would be deleted. Are you sure you want to delete seleted Users?")){
					
					$.ajax({
						type:'POST',
						dataType: 'json',
						url:'<?php echo Router::url(array('controller'=>'users','action'=>'admin_deleteall')); ?>',
						 data: {'User':arr},
						success:function(result){
                            $('.checkAll').attr("checked", false);
							$('#titleCheck').attr("checked", false);
							window.location.reload();
						}
					});
					
					return false;
		}	
			return false;
	} 
}//end of func deleteAll//

</script>    