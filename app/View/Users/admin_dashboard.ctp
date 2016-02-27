<?php echo $this->element("admin_header"); ?>
<?php echo $this->element("admin_topright"); ?>
<?php echo $this->element("admin_nav"); ?>
<?php echo $this->element("admin_sidebar"); ?>    
<!-- Content begins -->
<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/modules/exporting.js"></script>
<div id="content">
    <div class="contentTop">
        <span class="pageTitle"><span class="icon-dashboard"></span>Dashboard</span>
    </div>
    <!-- Breadcrumbs line -->
    <div class="breadLine">
        <div class="bc">
            <ul id="breadcrumbs" class="breadcrumbs">
                <li class="current"><a href="">Dashboard</a></li>
            </ul>
        </div>
        <div class="breadLinks">
        </div>
    </div>
    <!-- Main content -->
    <div class="wrapper">
     <?php $x=$this->Session->flash(); ?>
         <?php if($x){ ?>
        <div class="nNote nSuccess" id="flash">
         <div class="alert alert-success" style="text-align:center" > <?php echo $x; ?></div> 
         </div>                
        <?php } ?>
        <div class="row-fluid pull-left">
		<div class="span3">
		<a href="<?php echo $this->Html->url(array('controller'=>'Users','action'=>'admin_index')); ?>" class="info-tiles tiles-inverse has-footer">
			    <div class="tiles-heading">
			        <div class="pull-left">Users</div>
			        <div class="pull-right">
			        	<div class="sparkline-block" id="tileorders"><canvas style="display: inline-block; width: 39px; height: 13px; vertical-align: top;" width="39" height="13"></canvas></div>
			        </div>
			    </div>
			    <div class="tiles-body">
				<?php //echo $use_count;?>
			        <div class="text-center"><?php echo count($use_count); ?></div>
			    </div>
			    <div class="tiles-footer">
			    	<div class="pull-left">Manage Users</div>
			    	<div class="pull-right percent-change"></div>
			    </div>
			</a>
		</div>
		<div class="span3">
		<a href="<?php echo $this->Html->url(array('controller'=>'Activities','action'=>'admin_index')); ?>" class="info-tiles tiles-blue has-footer">
			    <div class="tiles-heading">
			        <div class="pull-left">Activities</div>
			        <div class="pull-right">
			        	<div class="sparkline-block" id="tileorders"><canvas style="display: inline-block; width: 39px; height: 13px; vertical-align: top;" width="39" height="13"></canvas></div>
			        </div>
			    </div>
			    <div class="tiles-body">
			        <div class="text-center"><?php  echo count($act_count); ?></div>
			    </div>
			    <div class="tiles-footer">
			    	<div class="pull-left">manage Location</div>
			    	<div class="pull-right percent-change"></div>
			    </div>
			</a>
		</div>
		<!-- subject-->
		<!--div class="span3">
		<a href="<?php echo $this->Html->url(array('controller'=>'Subjects','action'=>'admin_index')); ?>" class="info-tiles tiles-blue has-footer">
			    <div class="tiles-heading">
			        <div class="pull-left">Subject</div>
			        <div class="pull-right">
			        	<div class="sparkline-block" id="tileorders"><canvas style="display: inline-block; width: 39px; height: 13px; vertical-align: top;" width="39" height="13"></canvas></div>
			        </div>
			    </div>
			    <div class="tiles-body">
			        <div class="text-center"><?php // echo count($item_count); ?></div>
			    </div>
			    <div class="tiles-footer">
			    	<div class="pull-left">manage subject</div>
			    	<div class="pull-right percent-change"></div>
			    </div>
			</a>
		</div-->
		
		

		
		
		
		
		<!-- /span3 -->
		</div> 
<!--div class="widget chartWrapper">
<div class="body">
<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto" ></div>
</div>                
     </div-->
    </div>
</div>
  <script>
  </script>
<?php echo $this->Html->script(array("jquery.form")); ?> 