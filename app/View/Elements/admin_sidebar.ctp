<script src="http://maxcdn.bootstrapcdn.com/bootstrap/2.2.0/js/bootstrap.min.js"></script>
<script type="text/javascript" src="http://bootstrapdocs.com/v2.3.2/docs/assets/js/bootstrap-collapse.js"></script>	
<?php echo $this->Html->script(array('scroll')); ?>
<!-- Sidebar begins -->
<div id="sidebar">
    <div class="mainNav" id="scrollbox3">
              
        <!-- Main nav -->
        <ul class="nav accordion" id="accordion2">
           <li class="nav-icon accordion-group">
		   <div class="accordion-heading">
				   <a href="<?php echo $this->Html->url(array('controller'=>'users','action'=>'admin_dashboard')); ?>" class="<?php echo (($this->params['controller'] == 'users' && $this->params['action'] == 'admin_dashboard')?'collapsed':'' ); ?>" >
							<span class="icon icon-dashboard"></span>
								<span>Dashboard</span>
				   </a>
			   </div>
		   </li>
		   
        <!--li class="nav-icon accordion-group">
		   <div class="accordion-heading">
		   <a data-toggle="collapse" data-parent="#accordion2" href="#collapse2" class="<?php // echo (($this->params['controller'] == 'admins')?'collapsed':''); ?>" >
					<span class="icon icon-calendar"></span>
						<span>Admin Management</span>
		   </a>
            </div>
			<div id="collapse2" class="<?php // echo (($this->params['controller'] == 'admins')?'accordion-body in collapse':'accordion-body collapse'); ?>">
			  <div class="accordion-inner">
				<a class="<?php // echo (($this->params['controller'] == 'admins' && $this->params['action']=='admin_add')?'active':''); ?>" href="<?php // echo $this->Html->url(array('controller'=>'admins','action'=>'add')) ?>" title="">
							<span>Add Admin</span>
		      </a>
			  </div>
			  <div class="accordion-inner">
				<a class="<?php // echo (($this->params['controller'] == 'admins' && $this->params['action']=='admin_index')?'active':''); ?>" href="<?php // echo $this->Html->url(array('controller'=>'admins','action'=>'index')) ?>" title="">
							<span>Manage Admin</span>
		      </a>
			  </div>			  	
			</div>		
		</li-->    		  
	 	
		    
		 <li class="nav-icon accordion-group">
		   <div class="accordion-heading">
		   <a data-toggle="collapse" data-parent="#accordion2" href="#collapsetwo" class="<?php echo (($this->params['controller'] == 'users')?'collapsed':''); ?>" >
					<span class="icon icon-calendar"></span>
						<span>Users Management</span>
		   </a>
            </div>
			<div id="collapsetwo" class="<?php echo (($this->params['controller'] == 'users')?'accordion-body in collapse':'accordion-body collapse'); ?>">
			  <div class="accordion-inner">
				<a class="<?php echo (($this->params['controller'] == 'users' && $this->params['action']=='admin_addadmin')?'active':''); ?>" href="<?php echo $this->Html->url(array('controller'=>'users','action'=>'addadmin')) ?>" title="">
							<span>Add Admin</span>
		      </a>
			  </div>
			  <div class="accordion-inner">
				<a class="<?php echo (($this->params['controller'] == 'users' && $this->params['action']=='admin_index')?'active':''); ?>" href="<?php echo $this->Html->url(array('controller'=>'users','action'=>'index')) ?>" title="">
							<span>Manage user</span>
		      </a>
			  </div>	
			  <div class="accordion-inner">
				<a class="<?php echo (($this->params['controller'] == 'users' && $this->params['action']=='admin_admin')?'active':''); ?>" href="<?php echo $this->Html->url(array('controller'=>'users','action'=>'admin')) ?>" title="">
							<span>Manage admin</span>
		      </a>
			  </div>				  
			</div>		
		   </li>  
		   <li class="nav-icon accordion-group">
		   <div class="accordion-heading">
		   <a data-toggle="collapse" data-parent="#accordion3" href="#collapsethree" class="<?php echo (($this->params['controller'] == 'Activities')?'collapsed':''); ?>" >
					<span class="icon icon-calendar"></span>
						<span>Activitiy Management</span>
		   </a>
            </div>
			<div id="collapsethree" class="<?php echo (($this->params['controller'] == 'Activities')?'accordion-body in collapse':'accordion-body collapse'); ?>">
			  <div class="accordion-inner">
				<a class="<?php echo (($this->params['controller'] == 'Activities' && $this->params['action']=='admin_add')?'active':''); ?>" href="<?php echo $this->Html->url(array('controller'=>'Activities','action'=>'add')) ?>" title="">
							<span>Add Activitiy</span>
		      </a>
			  </div>
			  <div class="accordion-inner">
				<a class="<?php echo (($this->params['controller'] == 'Activities' && $this->params['action']=='admin_index')?'active':''); ?>" href="<?php echo $this->Html->url(array('controller'=>'Activities','action'=>'index')) ?>" title="">
							<span>Manage Activities</span>
		      </a>
			  </div>			  	
			</div>		
		   </li>    
		   
		   
		   
		   

        </ul>
    </div>
	</div>
		<script type="text/javascript">
$(function(){
$('#scrollbox3').enscroll({
    showOnHover: true,
    verticalTrackClass: 'track3',
    verticalHandleClass: 'handle3'
});
});
</script>