<?php echo $this->element("admin_header"); ?>
<?php echo $this->element("admin_topright"); ?>
<?php echo $this->element("admin_nav"); ?>
<?php echo $this->element("admin_sidebar");  ?> 
<div id="content">
    <div class="contentTop">
        <span class="pageTitle"><span class="icon-screen"></span>User Management</span>
        <ul class="quickStats">
            <li>
				<?php echo $this->Html->link($this->Html->Image('../images/icons/quickstats/user.png'),'javascript:void();',array('escape'=>false,'border'=>'0','class'=>'blueImg')); ?>
                <div class="floatR"><strong class="blue"><?php echo $this->Paginator->counter('{:count}');?></strong><span>Users</span></div>
            </li>
        </ul>
    </div>
     <!-- Breadcrumbs line -->
    <div class="breadLine">
        <div class="bc">
            <ul id="breadcrumbs" class="breadcrumbs">
                <li><a href="<?php echo $this->Html->url(array('controller'=>'users','action'=>'admin_dashboard')); ?>">Dashboard</a></li>
                <li class="current"><a href="<?php echo $this->Html->url(array('controller'=>'users','action'=>'admin_index')); ?>">User Management</a></li>
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
			<span class="titleIcon">
			<input title="Select All" class="tool-tip" id="titleCheck" name="titleCheck" type="checkbox"></span>    <h6>User Management</h6>
			<div style="float:right;">
				   <?php echo $this->Form->create('User', array('controller'=>'users','action'=>'index')); ?>	   	  
					<div style="margin-top:5px;">			
						<input type="text" name="keyword" title="Search by username, email, name, city, activities, matches .."placeholder="Search the keywords" class="tipS tool-tip" autocomplete="off">
						 <input value="" type="submit" name="search">
					</div>
					<?php echo $this->Form->end();?>
			</div>			
		</div>
		<?php //echo "<pre>";print_r($customers);exit;?>
       <?php if(!empty($customers)){ ?>
                      
        <div id="dyn" class="hiddenpars">
            <?php  echo $this->Form->create('User',array('id' => 'mbc')); ?>
            <table cellpadding="0" cellspacing="0" class="tDefault checkAll tMedia" id="checkAll" width="100%">
            <thead>
            <tr>
            <th></th>
            <!--th><?php //echo ('Username'); ?></span></th-->
            <th><?php echo ('Email'); ?></th>
			<th><?php echo ('Name'); ?></th>
			<th><?php echo ('Latitude'); ?></th>
			<th><?php echo ('Longitude'); ?></th>
			<th><?php echo ('State'); ?></th>
			<th><?php echo ('City'); ?></th>
			<th><?php echo ('state'); ?></th>
            <th><?php echo('Created'); ?></th>            
            <th>Action</th>
            </tr>
            </thead>
            <tbody>
		
			<?php foreach ($customers as $user): ?>
            <tr class="gradeX">
            <td><?php echo $this->Form->checkbox("use"+$user['User']['id'],array('value' => $user['User']['id'],'class'=>'checkAll')); ?></td>
            <!--td><?php //echo ($user['User']['username']? $user['User']['username'] : 'Not Mentioned'); ?></td-->
			<td><?php echo h($user['User']['email']); ?></td>
			<td><?php echo h($user['User']['name']); ?></td>
			<td><?php echo h(number_format($user['User']['latitude'],4)); ?></td>
			<td><?php echo h(number_format($user['User']['longitude'],4)); ?></td>
			<td><?php echo h($user['User']['state']); ?></td>
			<td><?php if(($user['User']['location'])==''){echo "Not Mentioned";} else { echo h($user['User']['location']); }?></td>
			<td><?php if(($user['User']['status'])==1){echo "Activate";} else { echo "Deactivate"; }?></td>
			
            <td><?php echo date("d M y",strtotime($user['User']['created'])); ?></td>
            
			<td class="center">
             <form></form>

			<?php echo $this->Html->link($this->Html->image('../images/icons/admins/edit.png',array('border'=>'0','class'=>'iconb','width'=>'17')),array('action' => 'admin_edit', $user['User']['id']),array('class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Edit','escape'=>false)); ?>
			<?php echo $this->Html->link($this->Html->image('../images/icons/admins/event.png',array('border'=>'0','class'=>'iconb','width'=>'17')),array('controller'=>'users','action' => 'admin_activities', $user['User']['usr_id']),array('class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Activities Management','escape'=>false)); ?>
			<?php echo $this->Html->link($this->Html->image('../images/icons/admins/14-20.png',array('border'=>'0','class'=>'iconb','width'=>'17')),array('controller'=>'Users','action' => 'admin_matches', $user['User']['usr_id']),array('class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Matches management','escape'=>false)); ?>
            <?php echo $this->Form->postLink($this->Html->image('../images/icons/admins/delete.png',array('border'=>'0','class'=>'iconb','width'=>'17')),array('controller'=>'Users','action'=>'delete',$user['User']['id']),array('escape'=>false,'class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Delete'),__('If you delete this user then all its answer and scores would be deleted. Are you sure you want to delete %s?', $user['User']['name']));?>
			 <?php if ($user['User']['status']=='0'){?>
			<?php echo $this->Form->postLink($this->Html->image('../images/icons/admins/deactivate.png',array('border'=>'0','class'=>'iconb','width'=>'17')), array('action' => 'activate', $user['User']['id']),array('escape'=>false,'class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Active'),__('Are you sure you want to activate #%s?', $user['User']['name']));?><?php }else { ?>
			<?php echo $this->Form->postLink($this->Html->image('../images/icons/admins/activate.png',array('border'=>'0','class'=>'iconb','width'=>'17')), array('action' => 'block', $user['User']['id']), array('escape'=>false,'class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Block'),__('Are you sure you want to block #%s?', $user['User']['name'])); ?><?php }?>			
            </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            </table> 
            <br/><br/>
            <?php 
			echo $this->Html->link('Delete All','javascript:void(0);',array('onclick'=>'return deleteAll();','class'=>'buttonS bRed','style'=>"margin-left:20px")); ?>
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