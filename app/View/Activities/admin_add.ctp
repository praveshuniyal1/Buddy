<?php echo $this->element("admin_header"); ?>
<?php echo $this->element("admin_topright"); ?>
<?php echo $this->element("admin_nav"); ?>
<?php echo $this->element("admin_sidebar"); ?> 
<!--------------------------->
<div class="overlay" id="overlay" style="display:none;"></div>
<div id="content">
    <div class="contentTop">
        <span class="pageTitle"><span class="icon-screen"></span>Activity Management</span>
    </div>
     <!-- Breadcrumbs line -->
    <div class="breadLine">
        <div class="bc">
            <ul id="breadcrumbs" class="breadcrumbs">
                <li><a href="<?php echo $this->Html->url(array('controller'=>'users','action'=>'admin_dashboard')); ?>">Dashboard</a></li>
                <li><a href="<?php echo $this->Html->url(array('controller'=>'activities','action'=>'admin_index')); ?>">Activity Management</a></li>
                 <li class="current"><a href="javascript:void:(0)"><?php if(!empty($this->data)){ ?>Edit Activity <?php } else { ?> Add Activity<?php } ?></a></li>
            </ul>
        </div>
    </div>
    <!-- Main content -->
    <div class="wrapper">
         <div class="widget fluid">
        <div class="whead"><h6>Add Activity</h6></div>
        <div id="dyn" class="hiddenpars">
             <?php echo $this->Form->create('Activity',array('action'=>'admin_add','id'=>'validate','type'=>'file','onsubmit'=>'return loaderChecking();')); ?>
                <div class="formRow">
                    <div class="grid3"><label>Activity Name:</label></div>
                    <div class="grid9">
						<?php echo $this->Form->input('name', array('label'=>"",'type'=>'text','required'));?>
                    </div>
                </div>
                <div class="formRow">
                    <div class="grid3"><label>Activity Video:</label></div>
                    <div class="grid9">
                    <?php echo $this->Form->input('video', array('label'=>"",'type'=>'file','required'));?>
                   </div>
                </div>      
                <div class="formRow">
                    <div class="grid3"><label>You Tube Link:</label></div>
                    <div class="grid9">
                    <?php echo $this->Form->input('you_tube_link', array('label'=>"",'type'=>'url','required'));?>
                    </div>
                </div>
                <div class="formRow">
                    <div class="grid3"><label></label></div>
                    <div class="grid9">
                    <button type="submit" name="Save" id="update" class="buttonS bLightBlue" >Save</button>
                    </div>
                </div>
           </form>     
        </div>            
        </div>        
    </div>
</div>
<script>
(function($) {
    $.fn.extend( {
        limiter: function(limit, elem) {
            $(this).on("keyup focus", function() {
                setCount(this, elem);
            });
            function setCount(src, elem) {
                var chars = src.value.length;
                if (chars > limit) {
                    src.value = src.value.substr(0, limit);
                    chars = limit;
                }
                elem.html( limit - chars );
            }
            setCount($(this)[0], elem);
        }
    });
})(jQuery);

var elem = $("#chars");
$("#profile_desc").limiter(500, elem);
</script> 
<script>
function loaderChecking(){
		$("#overlay").show();
		return true;	
}
</script>

      