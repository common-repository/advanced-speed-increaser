<?php
$send_mail_status = ''; 
global $wpdb;
if(@$_REQUEST['first']==1){
	update_option( 'tw_asi_msg', 1);
}
if(isset($_POST['tw_asi_status_submit'])) {
	$status = '';
	if($_POST['tw_asi_status'] == 1) {
		$status = tw_asi_install();
	} else {
		$status = tw_asi_uninstall();
	}
	if($status) {
		$settings_status = '<div class="notice notice-success is-dismissible"><h3>Settings saved successfully.</h3></div>';
	} else {
		$settings_status = '<div class="notice notice-error is-dismissible"><h3>Error applying settings.</h3></div>';
	}
}
else if(isset($_POST['tw_asi_html_submit'])) {
	if($_POST['tw_asi_html1']==1){
		update_option( 'tw_asi_html1', 1);
	}
	else{
		update_option( 'tw_asi_html1', 0);
	}
	$settings_status = '<div class="notice notice-success is-dismissible"><h3>Settings saved successfully.</h3></div>';
}
else if(isset($_POST['tw_asi_optimize_submit'])) {
	$status = tw_asi_table_optimize($_POST['hdntables']);
	if($status) {
		$settings_status = '<div class="notice notice-success is-dismissible"><h3>Table optimized successfully.</h3></div>';
	} else {
		$settings_status = '<div class="notice notice-error is-dismissible"><h3>Error occured while optimizing.</h3></div>';
	}
}
else if(isset($_POST['tw_asi_rem_opt_submit'])) {
	$status = tw_asi_table_remove($_POST['hdntwsirem']);
	if($status) {
		$msg = '';
		if($_POST['hdntwsirem']==1){$msg = '';}
		$settings_status = '<div class="notice notice-success is-dismissible"><h3>'.$msg.' removed successfully</h3></div>';
	} else {
		$settings_status = '<div class="notice notice-error is-dismissible"><h3>Error occured while removing.</h3></div>';
	}
}

?>
<div class="wrap">
<h2>Advanced Speed Increaser</h2>



	<?php
			echo $settings_status;
	?>
    <div class="tw_asi_full_wr">
    <div class="tw_asi_left">
	<div class="tw_asi_check_label">
	
	<?php 
	$tw_asi_status = get_option( 'tw_asi_status', 0 );
	?> 
    <div class="twasi_notice"><form method="post">
    <h2>GZIP compression and Leverage browser caching</h2>
    <table class="wp-list-table widefat fixed striped twasi">
    <tr><th scope="row">Enable</th><td><input type="checkbox" name="tw_asi_status" value="1" <?php echo ($tw_asi_status==0)?'':'checked="checked"' ?> /></td></tr>
    <tr><td colspan="2"><input type="submit" name="tw_asi_status_submit" value="Update" class="button button-primary" /></td></tr>
    </table>
    </form></div>
    
    <?php 
	$tw_asi_html1 = get_option( 'tw_asi_html1', 0 );
	?> 
    <div class="twasi_notice"><form method="post">
    <h2>HTML Minifier</h2>
    <table class="wp-list-table widefat fixed striped twasi">
    <tr><th scope="row">Enable</th><td><input type="checkbox" name="tw_asi_html1" value="1" <?php echo ($tw_asi_html1==0)?'':'checked="checked"' ?> /></td></tr>
    
    <tr><td colspan="2"><input type="submit" name="tw_asi_html_submit" value="Update" class="button button-primary" /></td></tr>
    </table>
    
    </form></div>
    
    <div class="twasi_notice">
    <h2>Database Cleanup</h2>
    <table class="wp-list-table widefat fixed striped twasi">
    <tr><th>Remove Auto Revisions <strong><a title="Know more" target="_blank" href="http://www.thelogicalcoder.com/increase-speed-of-your-wordpress-website/#remove-auto-revisons">?</a></strong></th><td><form method="post"><input type="hidden" name="hdntwsirem" value="1" />
<input type="submit" name="tw_asi_rem_opt_submit" value="Clean Now" class="button button-primary" />
</form></td></tr>
    <tr><th>Remove Auto Drafts <strong><a title="Know more" target="_blank" href="http://www.thelogicalcoder.com/increase-speed-of-your-wordpress-website/#remove-auto-drafts">?</a></strong></th><td><form method="post"><input type="hidden" name="hdntwsirem" value="2" />
<input type="submit" name="tw_asi_rem_opt_submit" value="Clean Now" class="button button-primary" />
</form></td></tr>
<tr><th>Remove Trash Posts <strong><a title="Know more" target="_blank" href="http://www.thelogicalcoder.com/increase-speed-of-your-wordpress-website/#remove-trash-posts">?</a></strong></th><td><form method="post"><input type="hidden" name="hdntwsirem" value="3" />
<input type="submit" name="tw_asi_rem_opt_submit" value="Clean Now" class="button button-primary" />
</form></td></tr>
    <tr><th>Remove Spam Comments <strong><a title="Know more" target="_blank" href="http://www.thelogicalcoder.com/increase-speed-of-your-wordpress-website/#remove-spam-comments">?</a></strong></th><td><form method="post"><input type="hidden" name="hdntwsirem" value="4" />
<input type="submit" name="tw_asi_rem_opt_submit" value="Clean Now" class="button button-primary" />
</form></td></tr>
    <tr><th>Remove Unapproved Comments <strong><a title="Know more" target="_blank" href="http://www.thelogicalcoder.com/increase-speed-of-your-wordpress-website/#remove-unapproved-comments">?</a></strong></th><td><form method="post"><input type="hidden" name="hdntwsirem" value="5" />
<input type="submit" name="tw_asi_rem_opt_submit" value="Clean Now" class="button button-primary" />
</form></td></tr>
    <tr><th>Delete Transient Options <strong><a title="Know more" target="_blank" href="http://www.thelogicalcoder.com/increase-speed-of-your-wordpress-website/#delete-transient-options">?</a></strong></th><td><form method="post"><input type="hidden" name="hdntwsirem" value="6" />
<input type="submit" name="tw_asi_rem_opt_submit" value="Clean Now" class="button button-primary" />
</form></td></tr>
    </table>
    </div>
    
	<div class="twasi_notice">
    <h2>Optimize Database and Tables</h2>
    <p class="submit"><a href="admin.php?page=tw-asi&fetch=1" class="button button-primary" />Fetch Database Tables</a></p>
    <?php if(@$_REQUEST['fetch']==1){ 
	
	$sql = "SELECT TABLE_NAME AS table_name, DATA_FREE as data_overload, (data_length + index_length) as data_size FROM information_schema.TABLES WHERE table_schema = '".DB_NAME."'";
	$results = $wpdb->get_results( $sql, OBJECT );
	$total_size = 0;
	$total_overload_size = 0;
	//print_r($results);
	?>
    <style>.widefat th.asi_notice, .widefat td.asi_notice{color:#093;}.widefat th.asi_error, .widefat td.asi_error{color:#dc3232 !important;}</style>
    <table class="wp-list-table widefat fixed striped twasi">
    <tr><th>Table Name</th><th>Table Size</th><th>Table Overload</th></tr>
    <?php
	$table_str = "";
	foreach($results as $result){
		$table_str .= $result->table_name.',';
	?>
    <tr>
      <td><?php echo $result->table_name;?></td>
      <td><?php $total_size += $result->data_size; echo tw_asi_getSizes($result->data_size);?></td>
      <td class="asi_notice <?php if($result->data_overload>0){ echo 'asi_error';} ?>"><?php $total_overload_size += $result->data_overload; echo tw_asi_getSizes($result->data_overload);?></td>
    </tr>
    <?php } ?>
    <tr><th>Total</th><th><?php echo tw_asi_getSizes($total_size); ?></th><th class="asi_notice <?php if($total_overload_size>0){ echo 'asi_error';} ?>"><?php echo tw_asi_getSizes($total_overload_size); ?></th></tr>
    </table>
    <form method="post">
    <input type="hidden" name="hdntables" value="<?php echo rtrim($table_str,',');?>" />
    <p class="submit"><input type="submit" name="tw_asi_optimize_submit" value="Optimize Tables" class="button button-primary" /></p>
    </form>
    <?php } ?>
    </div>
	
	
	</div>
	</div>
    <div class="tw_asi_right">
    	<div class="twasi_notice">
        
        
        <h3>Advanced Speed Increaser Needs Your Support</h3>
        <p class="message">It is hard to continue development and support for this plugin without contributions from users like you. If you enjoy using Advanced Speed Increaser and find it useful, please consider making a donation.</p>
        <p><a href="http://www.thelogicalcoder.com/manage.php/?plugin=twasi&type=donate" target="_blank" class="button button-primary">Donate</a>&nbsp;&nbsp;<a href="http://www.thelogicalcoder.com/manage.php/?plugin=twasi&type=feedback" target="_blank" class="button button-primary">Feedback</a></p>
			
        <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
    </div>
    <div class="clear"></div>

</div>
</div>

