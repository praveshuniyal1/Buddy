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
        <span class="pageTitle"><span class="icon-screen"></span>Category Management</span>
        <ul class="quickStats">
            <li>
				<?php echo $this->Html->link($this->Html->Image('../images/icons/quickstats/user.png'),'javascript:void();',array('escape'=>false,'border'=>'0','class'=>'blueImg')); ?>
                <div class="floatR"><strong class="blue"><?php echo $this->Paginator->counter('{:count}');?></strong><span>Categories</span></div>
            </li>
        </ul>
    </div>
     <!-- Breadcrumbs line -->
    <div class="breadLine">
        <div class="bc">
            <ul id="breadcrumbs" class="breadcrumbs">
                <li><a href="<?php echo $this->Html->url(array('controller'=>'members','action'=>'admin_dashboard')); ?>">Dashboard</a></li>
                <li class="current"><a href="<?php echo $this->Html->url(array('controller'=>'Categories','action'=>'admin_index')); ?>">Category Management</a></li>
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
        <input title="Select All" class="tool-tip" id="titleCheck" name="titleCheck" type="checkbox"></span>    <h6>Category Management</h6>
		<div style="float:right;">
			   <?php echo $this->Form->create('Category', array('controller'=>'Categories','action'=>'index')); ?>	   	  
				<div style="margin-top:5px;">			
					<input type="text" name="keyword" title="Enter username,id.."placeholder="Search.." class="tipS tool-tip" autocomplete="off">
					 <input value="" type="submit" name="search">
				</div>
				<?php echo $this->Form->end();?>
		</div>		
		</div>
       <?php if(!empty($categories)){ ?>
                      
        <div id="dyn" class="hiddenpars">
            <?php  echo $this->Form->create('Activity',array('id' => 'mbc')); ?>
            <table cellpadding="0" cellspacing="0" class="tDefault checkAll tMedia" id="checkAll" width="100%">
            <thead>
            <tr>
            <th></th>
            <th><?php echo ('Category name'); ?></span></th>
			<th><?php echo ('Status'); ?></th>        
            <th>Action</th>
            </tr>
            </thead>
            <tbody>
			<?php  foreach ($categories as $category): ?>
            <tr class="gradeX">
            <td><?php echo $this->Form->checkbox("use"+$category['Category']['id'],array('value' => $category['Category']['id'],'class'=>'checkAll')); ?></td>
            <td><?php echo h($category['Category']['name']); ?></td>
			<td><?php if($category['Category']['status']=='1') { echo 'Activate'; } else {  echo 'Deactivate'; } ?></td>
            
			<td class="center">
             <form></form>
			<?php echo $this->Html->link($this->Html->image('../images/icons/admins/edit.png',array('border'=>'0','class'=>'iconb','width'=>'17')),array('action' => 'admin_edit', $category['Category']['id']),array('class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Edit','escape'=>false)); ?>
			
            <?php echo $this->Form->postLink($this->Html->image('../images/icons/admins/delete.png',array('border'=>'0','class'=>'iconb','width'=>'17')),array('controller'=>'Categories','action'=>'delete',$category['Category']['id']),array('escape'=>false,'class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Delete'),__('Are you sure you want to delete %s?', $category['Category']['name']));?>
			 <?php if ($category['Category']['status']=='0'){?>
			<?php echo $this->Form->postLink($this->Html->image('../images/icons/admins/deactivate.png',array('border'=>'0','class'=>'iconb','width'=>'17')), array('action' => 'activate', $category['Category']['id']),array('escape'=>false,'class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Active'),__('Are you sure you want to activate #%s?', $category['Category']['name']));?><?php }else { ?>
			<?php echo $this->Form->postLink($this->Html->image('../images/icons/admins/activate.png',array('border'=>'0','class'=>'iconb','width'=>'17')), array('action' => 'block', $category['Category']['id']), array('escape'=>false,'class'=>'tablectrl_small bDefault tipS tool-tip','title'=>'Block'),__('Are you sure you want to block #%s?', $category['Category']['name'])); ?><?php }?>
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
		alert('Please select at least one checkbox to delete Member.');
		return false;
    } else {				
		if(confirm("Are you sure you want to delete?")){
					
					$.ajax({
						type:'POST',
						dataType: 'json',
						url:'<?php echo Router::url(array('controller'=>'Categories','action'=>'admin_deleteall')); ?>',
						 data: {'Member':arr},
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
$(document).ready(function()
{
	$("#filter").click(function()
	{
		$("#filter_form").submit();	
	});
});

</script>    