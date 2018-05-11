<?php
if ( !function_exists('add_action') ) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}

if ( !current_user_can('manage_options') ) { 
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}
?>

<div class="wrap" style="margin-top:45px;">

<?php
// Run Job Form: display Backup is Running message
if ( isset( $_POST['Submit-DBB-Run-Job'] ) && current_user_can('manage_options') ) {
	check_admin_referer('bulletproof_security_db_backup_run_job');
	
	$DBBjobsX = $_POST['DBBjobs'];

	switch( $_POST['Submit-DBB-Run-Job'] ) {
		case __('Run Job/Delete Job', 'bulletproof-security'):
		
		if ( !empty( $DBBjobsX ) ) {
			
			echo '<div id="message" class="updated" style="margin-left:70px;background-color:#000;">';
			
			foreach ( $DBBjobsX as $keyX => $valueX ) {
				
				if ( $valueX == 'runjob' ) {
				
				$backup_running = '<p style="border:1px solid #999999;background-color:#ffffe0;margin:-1px;"><strong><font color="green">'.__('Backup is Running...Please Wait...', 'bulletproof-security').'<br>'.__('Backup & Zip Time Estimates: ', 'bulletproof-security').'<br>'.__('10MB DB: 3 Seconds', 'bulletproof-security').'<br>'.__('100MB DB: 30 Seconds', 'bulletproof-security').'</font></strong></p>';
				echo $backup_running;
				}
			}
			echo '</div>';
		}
	}
}

if ( function_exists('get_transient') ) {
require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

	if ( false === ( $bps_api = get_transient('bulletproof-security_info') ) ) {
		$bps_api = plugins_api( 'plugin_information', array( 'slug' => stripslashes( 'bulletproof-security' ) ) );
		
	if ( !is_wp_error( $bps_api ) ) {
		$bps_expire = 60 * 30; // Cache downloads data for 30 minutes
		$bps_downloaded = array( 'downloaded' => $bps_api->downloaded );
		maybe_serialize( $bps_downloaded );
		set_transient( 'bulletproof-security_info', $bps_downloaded, $bps_expire );
	}
	}

		$bps_transient = get_transient( 'bulletproof-security_info' );
    	
		echo '<div class="bps-star-container" style="float:right;position:relative;top:-40px;left:0px;margin:0px 0px -40px 0px;font-weight:bold;">';
		echo '<div class="bps-star"><img src="'.plugins_url('/bulletproof-security/admin/images/star.png').'" /></div>';
		echo '<div class="bps-downloaded">';
		
		foreach ( $bps_transient as $key => $value ) {
			echo number_format_i18n( $value ) .' '. str_replace( 'downloaded', "Downloads", $key );
		}
		
		echo '<div class="bps-star-link"><a href="http://wordpress.org/support/view/plugin-reviews/bulletproof-security" target="_blank" title="Add your own BPS Plugin Review">'.__('Add a Review', 'bulletproof-security').'</a><br><a href="http://affiliates.ait-pro.com/po/" target="_blank" title="Upgrade to BulletProof Security Pro">Upgrade to Pro</a></div>';
		echo '</div>';
		echo '</div>';
}
?>

<h2 style="margin-left:70px;"><?php _e('BulletProof Security ~ DB Backup & Security', 'bulletproof-security'); ?></h2>
<div id="message" class="updated" style="border:1px solid #999999;margin-left:70px;background-color:#000;">

<?php
// HUD - Heads Up Display - Warnings and Error messages
echo bps_check_php_version_error();
echo bps_hud_check_bpsbackup();
echo bps_check_safemode();
echo @bps_w3tc_htaccess_check($plugin_var);
echo @bps_wpsc_htaccess_check($plugin_var);
bps_delete_language_files();

// General all purpose "Settings Saved." message for forms
if ( current_user_can('manage_options') && wp_script_is( 'bps-js', $list = 'queue' ) ) {
if ( @$_GET['settings-updated'] == true) {
	$text = '<p style="background-color:#ffffe0;font-size:1em;font-weight:bold;padding:5px;margin:0px;"><font color="green"><strong>'.__('Settings Saved', 'bulletproof-security').'</strong></font></p>';
	echo $text;
	}
}

$bpsSpacePop = '-------------------------------------------------------------';

// Replace ABSPATH = wp-content/plugins
$bps_plugin_dir = str_replace( ABSPATH, '', WP_PLUGIN_DIR);
// Replace ABSPATH = wp-content
$bps_wpcontent_dir = str_replace( ABSPATH, '', WP_CONTENT_DIR);
// Top div echo & bottom div echo
$bps_topDiv = '<div id="message" class="updated" style="background-color:#ffffe0;font-size:1em;font-weight:bold;border:1px solid #999999; margin-left:70px;"><p>';
$bps_bottomDiv = '</p></div>';

// Get Real IP address - USE EXTREME CAUTION!!!
function bpsPro_get_real_ip_address() {
	
	if ( is_admin() && wp_script_is( 'bps-js', $list = 'queue' ) && current_user_can('manage_options') ) {
	
		if ( isset($_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = esc_html($_SERVER['HTTP_CLIENT_IP']);
			
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = esc_html($_SERVER['HTTP_X_FORWARDED_FOR']);
			
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = esc_html($_SERVER['REMOTE_ADDR']);
			
		}
	return $ip;
	}
}	

// Create a new Deny All .htaccess file on first page load with users current IP address
// Create a new Deny All .htaccess file if IP address is not current
function bpsPro_DBBackup_deny_all() {

	if ( is_admin() && wp_script_is( 'bps-js', $list = 'queue' ) && current_user_can('manage_options') ) {
		
		$DBBoptions = get_option('bulletproof_security_options_db_backup');
		$denyall_content = "Order Deny,Allow\nDeny from all\nAllow from " . bpsPro_get_real_ip_address();
		$create_denyall_htaccess_file = $DBBoptions['bps_db_backup_folder'] .'/.htaccess';
		$check_string = @file_get_contents($create_denyall_htaccess_file);
		
		if ( file_exists($create_denyall_htaccess_file) && strpos( $check_string, bpsPro_get_real_ip_address() ) ) {
			return;
		}

		if ( !file_exists($create_denyall_htaccess_file) ) { 

			$handle = fopen( $create_denyall_htaccess_file, 'w+b' );
    		fwrite( $handle, $denyall_content );
    		fclose( $handle );
		}			
		
		if ( file_exists($create_denyall_htaccess_file) && !strpos( $check_string, bpsPro_get_real_ip_address() ) ) { 
			$handle = fopen( $create_denyall_htaccess_file, 'w+b' );
    		fwrite( $handle, $denyall_content );
    		fclose( $handle );
		}
	}
}
bpsPro_DBBackup_deny_all();

?>
</div>

<!-- jQuery UI Tab Menu -->
<div id="bps-container">
	<div id="bps-tabs" class="bps-menu">
    <div id="bpsHead" style="position:relative; top:0px; left:0px;"><img src="<?php echo plugins_url('/bulletproof-security/admin/images/bps-security-shield.png'); ?>" style="float:left; padding:0px 8px 0px 0px; margin:-72px 0px 0px 0px;" /></div>
		<ul>
            <li><a href="#bps-tabs-1"><?php _e('DB Backup', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-2"><?php _e('DB Backup Log', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-3"><?php _e('DB Table Prefix Changer', 'bulletproof-security'); ?></a></li>
 			<li><a href="#bps-tabs-4"><?php _e('Help &amp; FAQ', 'bulletproof-security'); ?></a></li>
		</ul>
            
<div id="bps-tabs-1" class="bps-tab-page">
<h2><?php _e('DB Backup', 'bulletproof-security'); ?></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">

<h3><?php _e('DB Backup', 'bulletproof-security'); ?>  <button id="bps-open-modal1" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
<div id="bps-modal-content1" title="<?php _e('DB Backup', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable (top) and resizable (bottom right corner)', 'bulletproof-security').'</strong><br><br><strong><font color="blue">'.__('Backup Job settings and other information about backups are logged in the DB backup Log. The sql dump backup file in the DB Backup zip file/archive also contains information about the Backup Job.', 'bulletproof-security').'</font></strong><br><br><strong>'.__('DB Backup & Security Guide & Troubleshooting: http://forum.ait-pro.com/forums/topic/database-backup-security-guide/', 'bulletproof-security').'</strong><br><br><strong>'.__('How To Create a Backup Job, Run a Backup Job, Download a Backup File and Delete a Backup File', 'bulletproof-security').'</strong><br><strong>'.__('NOTE: ', 'bulletproof-security').'</strong>'.__('Before creating a Scheduled Backup Job please read the - ', 'bulletproof-security').'<strong>'.__('Scheduled Backup Jobs General Information and Notes', 'bulletproof-security').'</strong>'.__(' help section.', 'bulletproof-security').'<br><br>'.__('1. Click the Create Backup Jobs accordion tab.', 'bulletproof-security').'<br>'.__('2. Enter a Description/Backup Job Name and select the Form option choices that you want.', 'bulletproof-security').'<br>'.__('3. Click the Create Backup Job button to save your Form option choices and create your Backup Job.', 'bulletproof-security').'<br>'.__('4. Click the Backup Jobs - Manual/Scheduled accordion tab, click on the Run checkbox for the Backup Job that you want to run and click the Run Job/Delete Job button.', 'bulletproof-security').'<br>'.__('5. Your Backup files are displayed under the Backup Files - Download/Delete accordion tab.', 'bulletproof-security').'<br>'.__('6. You can Download Backup files to your computer by clicking the Download link for that Backup file.', 'bulletproof-security').'<br>'.__('7. You can delete Backup files by clicking the checkbox for the Backup file that you want to delete and then click the Delete Files button.', 'bulletproof-security').'<br><br><strong>'.__('Backup Jobs - Manual/Scheduled Accordion Tab', 'bulletproof-security').'</strong><br>'.__('- Displays the Description/Job Name, Delete and Run Checkboxes, Job Type, Frequency, Last Backup, Next Backup, Email Backup and Job Created table columns.', 'bulletproof-security').'<br>'.__('- Job Type displays either Manual or Scheduled.', 'bulletproof-security').'<br>'.__('- Frequency displays either Manual, Hourly, Daily, Weekly or Monthly.', 'bulletproof-security').'<br>'.__('- Last Backup displays either Backup Job Created or a timestamp when the last backup job was run.', 'bulletproof-security').'<br>'.__('- Next Backup displays either Manual, Hourly or a combination of user-friendly next job run times: 5PM, Sunday 5PM, 30th 5PM.', 'bulletproof-security').'<br>'.__('- Email Backup displays either Manual, Yes, Yes & Delete, No or Send Email Only.', 'bulletproof-security').'<br>'.__('- Job Created displays the timestamp for when the Backup Job was created.', 'bulletproof-security').'<br><br><strong>'.__('Backup Files - Download/Delete Accordion Tab', 'bulletproof-security').'</strong><br>'.__('- Displays the Backup Filename, Delete Checkbox, Download Links, Backup Folder, Size and Date/Time table columns.', 'bulletproof-security').'<br>'.__('- Backup Filename displays the name of the backup zip file.', 'bulletproof-security').'<br>'.__('- Backup Folder displays the backup folder path.', 'bulletproof-security').'<br>'.__('- Size displays the size of the backup zip file.', 'bulletproof-security').'<br>'.__('- Date/Time displays the date and time that the backup zip file was created.', 'bulletproof-security').'<br><br><strong>'.__('Create Backup Jobs Accordion Tab', 'bulletproof-security').'</strong><br>'.__('- Displays a dynamic DB Table Name checkbox form used to select the database tables that you want to backup.', 'bulletproof-security').'<br>'.__('- Description/Backup Job Name textbox to enter a description for your Backup Job.', 'bulletproof-security').'<br>'.__('- DB Backup Folder Location textbox with a default Obfuscated & Secure BPS Backup Folder location.', 'bulletproof-security').'<br>'.__('- DB Backup File Download Link/URL textbox with a default download URL path.', 'bulletproof-security').'<br>'.__('- Backup Job Type: Manual or Scheduled select dropdown option to choose either a Manual or Scheduled Backup job type.', 'bulletproof-security').'<br>'.__('- Frequency of Scheduled Backup Job (recurring) select dropdown option to choose either N/A, Hourly, Daily, Weekly or Monthly backup job frequency.', 'bulletproof-security').'<br>'.__('- Hour When Scheduled Backup is Run (recurring) select dropdown option to choose a start time for a scheduled backup job: N/A and 12AM through 11PM.', 'bulletproof-security').'<br>'.__('- Day of Week When Scheduled Backup is Run (recurring) select dropdown option to choose a weekday day when a scheduled backup job is run: N/A and Sunday through Monday.', 'bulletproof-security').'<br>'.__('- Day of Month When Scheduled Backup is Run (recurring) select dropdown option to choose a day of the month for a start time when a backup job is run: N/A and 1st through 30th.', 'bulletproof-security').'<br>'.__('- Send Scheduled Backup Zip File Via Email or Just Email Only select dropdown option to choose either to email a zip backup file, do not email backup zip file, email and delete zip backup file or just send an email that backup job has completed/been run.', 'bulletproof-security').'<br>'.__('- Automatically Delete Old Backup Files select dropdown option to choose Never delete old backup files, delete backup files older than 1 day, 5 days, 10 days, 15 days, 30 days, 60 days, 90 days or 180 days.', 'bulletproof-security').'<br>'.__('- Turn On/Off All Scheduled Backups (override) select dropdown option to choose either turn on all scheduled backups or turn off all scheduled backups. This an override option that prevent any/all scheduled backup jobs from being run.', 'bulletproof-security').'<br><br><strong>'.__('Scheduled Backup Jobs General Information and Notes', 'bulletproof-security').'</strong><br>'.__('- Scheduled Backup Cron Jobs are synchronized to run exactly on the hour: 5:00pm, 6:00pm, 7:00pm. The Backup Cron job actual run times may fluctuate. That is just the normal nature of WordPress Crons. The DB Backup Cron is designed to resynchronize itself to the top of the hour on the hour.', 'bulletproof-security').'<br><br>'.__('- Today is 12AM to 11:59PM. If you want a Daily scheduled backup job to start running for the first time at 12AM tomorrow (which seems like today, but is actually tomorrow) then choose the Day of the Week that is tomorrow. 12AM tomorrow is the start time and the Daily scheduled backup job will continue to be run at 12AM every day after the start time that you choose.', 'bulletproof-security').'<br><br>'.__('- The Create Backup Jobs Form allows for the widest possible combinations of start times for scheduled backup jobs. The start time choices are: Frequency, Hour, Day of Week and Day of Month and have many different possible logical combinations that can be chosen. See this help section before creating any scheduled backup jobs - ', 'bulletproof-security').'<strong>'.__('Best Logical Choices For Start Times When Scheduling Backup Jobs With the Create Backup Jobs Form', 'bulletproof-security').'</strong><br><br>'.__('- You can schedule multiple backup jobs for the same frequency. Example: You can create/schedule a backup job to run Weekly at 8PM on Sunday and can create/schedule a backup job to run Weekly at 10PM on Wednesday. Scheduled backup jobs run based on the time the scheduled backup job is scheduled to run - there are no limitations with scheduling multiple backup jobs.', 'bulletproof-security').'<br><br><strong>'.__('Best Logical Choices For Start Times When Scheduling Backup Jobs With the Create Backup Jobs Form', 'bulletproof-security').'</strong><br>'.__('These are some common logical option choices for Creating/Scheduling Backup Jobs. There are other possible combinations of option settings/start times, but these are intended to be simple examples of common logical option setting choices.', 'bulletproof-security').'<br><br><strong>'.__('Hourly Backup Job', 'bulletproof-security').'</strong><br>'.__('- If you choose Hourly for the Frequency and you do not pick a start Time/Hour when the Backup Job is next run. The next Backup Job will be run at the top of the next hour. Example: If the time now is 4:30PM then the next backup job will be run at 5PM, then 6PM, then 7PM, etc.', 'bulletproof-security').'<br>'.__('- If you choose Hourly for the Frequency and pick a start Time/Hour when the Backup Job is next run. The next Backup Job will be run at the start Time/Hour that you chose. Example: If the time now is 4:30PM and you chose 8PM for the start Time/Hour then the next backup job will be run at 8PM, then 9PM, then 10PM, etc.', 'bulletproof-security').'<br><br><strong>'.__('Daily Backup Job', 'bulletproof-security').'</strong><br>'.__('- If today is Tuesday and you want to schedule a Backup Job to run at 12AM daily/every night. You would choose Daily for the Frequency, start Time/Hour of 12AM (12AM is tomorrow) and Wednesday for the day of the week for the start time when the Backup Job is next run. The next Backup Job will be run at 12AM Wednesday tonight/tomorrow and at 12AM every night/morning.', 'bulletproof-security').'<br><br><strong>'.__('Weekly Backup Job', 'bulletproof-security').'</strong><br>'.__('- If you want to schedule a Backup Job to run Weekly at 12AM every Sunday. You would choose Weekly for the Frequency, start Time/Hour of 12AM and Sunday for the day of the week for the start time when the Backup Job is next run. The next Backup Job will be run at 12AM next Sunday and every Sunday at 12AM.', 'bulletproof-security').'<br><br><strong>'.__('Monthly Backup Job', 'bulletproof-security').'</strong><br>'.__('- If you want to schedule a Backup Job to run Monthly on the 30th of each month at 11PM. You would choose Monthly for the Frequency, start Time/Hour of 11PM and 30th for the day of the month for the start time when the Backup Job is next run. The next Backup Job will be run on the 30th of this month at 11PM and each month on the 30th at 11PM.', 'bulletproof-security').'<br><br><strong>'.__('404 errors when trying to download zip files or if you have changed the DB Backup Folder Location', 'bulletproof-security').'</strong><br>'.__('On some web hosts (Go Daddy) if you have a WordPress subfolder website installation: Example: Main domain is example.com and Subfolder WordPress site is example.com/wordpress-subfolder-website/ then the download link will not work correctly and you will see 404 errors when trying to download zip backup files. Your options are to not change the default backup folder path for your subfolder site and download zip backup files via FTP or you can use/add the backup folder path for your main site instead of the default backup folder path for your subfolder site. You would also change the DB Backup File Download Link/URL to your main site\'s backup folder Link/URL path. What this means is that DB Backups for both your main site and your subfolder site will be saved/stored under your main site\'s backup folder.', 'bulletproof-security').'<br><br>'.__('If you are seeing 404 errors after changing the DB Backup File Download Link/URL and/or the DB Backup Folder Location then make sure that you have entered the correct folder path and also the correct link/URL paths for where your DB backup files are being saved/stored. The DB Backup File Download Link/URL path MUST end with/have a trailing slash. Example: http://www.example.com/wp-content/bps-backup/backups_xxxxxxxxxx/', 'bulletproof-security'); echo $text; ?></p>
</div>

<div id="bps-accordion-1" class="bps-accordian-main-2" style="margin:0px 0px 20px 0px;">
<h3><?php _e('Backup Jobs ~ Manual/Scheduled', 'bulletproof-security'); ?></h3>
<div>

<?php

	if ( is_admin() && wp_script_is( 'bps-js', $list = 'queue' ) && current_user_can('manage_options') ) {	

	// Reusable variables
	$DBBoptions = get_option('bulletproof_security_options_db_backup');	
	$timeNow = time();
	$gmt_offset = get_option( 'gmt_offset' ) * 3600;
	$timestamp = date_i18n(get_option('date_format'), strtotime("11/15-1976")) . ' ' . date_i18n(get_option('time_format'), $timeNow + $gmt_offset);

	// Form: DB Backup Run Jobs/Delete Jobs Form
	echo '<form name="bpsDBBackupRunJob" action="admin.php?page=bulletproof-security/admin/db-backup-security/db-backup-security.php" method="post">';
	wp_nonce_field('bulletproof_security_db_backup_run_job');

	$DBBtable_name = $wpdb->prefix . "bpspro_db_backup";
	$DBBRows = '';
	$DBBTableRows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $DBBtable_name WHERE bps_table_name != %s", $DBBRows ) );	
	
	echo '<div id="DBBJobscheckall">';
	echo '<table class="widefat" style="text-align:left;padding:5px;">';
	echo '<thead>';
	echo '<tr>';
	echo '<th scope="col" style="width:20%;font-size:1.13em;background-color:transparent;"><strong>'.__('Description/Job Name', 'bulletproof-security').'</strong></th>';
	echo '<th scope="col" style="width:5%;font-size:1.13em;"><strong><div style="position:relative; bottom:-9px; left:0px;">'.__('Delete', 'bulletproof-security').'</span></strong><br><input type="checkbox" class="checkallDeleteJobs" style="text-align:left;margin-left:0px;" /></th>';	
	echo '<th scope="col" style="width:5%;font-size:1.13em;background-color:transparent;"><strong>'.__('Run', 'bulletproof-security').'</strong></th>';
	echo '<th scope="col" style="width:10%;font-size:1.13em;background-color:transparent;"><strong>'.__('Job Type', 'bulletproof-security').'</strong></th>';
	echo '<th scope="col" style="width:10%;font-size:1.13em;background-color:transparent;"><strong>'.__('Frequency', 'bulletproof-security').'</strong></th>';
	echo '<th scope="col" style="width:15%;font-size:1.13em;background-color:transparent;"><strong>'.__('Last Backup', 'bulletproof-security').'</strong></th>';
	echo '<th scope="col" style="width:15%;font-size:1.13em;background-color:transparent;"><strong>'.__('Next Backup', 'bulletproof-security').'</strong></th>';
	echo '<th scope="col" style="width:10%;font-size:1.13em;background-color:transparent;"><strong>'.__('Email Backup', 'bulletproof-security').'</strong></th>';
	echo '<th scope="col" style="width:10%;font-size:1.13em;background-color:transparent;"><strong>'.__('Job Created', 'bulletproof-security').'</strong></th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	echo '<tr>';

	if ( $wpdb->num_rows == 0 ) {		
		echo '<th scope="row" style="border-bottom:none;">'.__('No Backup Jobs have been created yet.', 'bulletproof-security').'</th>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '</tr>';
	
	} else {

		foreach ( $DBBTableRows as $row ) {
			
			echo '<th scope="row" style="border-bottom:none;">'.$row->bps_desc.'</th>';
			echo "<td><input type=\"checkbox\" id=\"deletejob\" name=\"DBBjobs[$row->bps_id]\" value=\"deletejob\" class=\"deletejobALL\" /></td>";
			echo "<td><input type=\"checkbox\" id=\"runjob\" name=\"DBBjobs[$row->bps_id]\" value=\"runjob\" /></td>";			
			echo '<td>'.$row->bps_job_type.'</td>';
			echo '<td>'.$row->bps_frequency.'</td>';
			echo '<td>'.$row->bps_last_job.'</td>';

			if ( $row->bps_frequency == 'Hourly' && $row->bps_next_job == '' ) {
				$bps_next_job_visual = 'Hourly';
			
			} else {
			
			$day_numeric = array( '1 ', '2 ', '3 ', '4 ', '5 ', '6 ', '7 ', '8 ', '9 ', '10 ', '11 ', '12 ', '13 ', '14 ', '15 ', '16 ', '17 ', '18 ', '19 ', '20 ', '21 ', '22 ', '23 ', '24 ', '25 ', '26 ', '27 ', '28 ', '29 ', '30 ' );
			$day_ordinal = array( '1st ', '2nd ', '3rd ', '4th ', '5th ', '6th ', '7th ', '8th ', '9th ', '10th ', '11th ', '12th ', '13th ', '14th ', '15th ', '16th ', '17th ', '18th ', '19th ', '20th ', '21st ', '22nd ', '23rd ', '24th ', '25th ', '26th ', '27th ', '28th ', '29th ', '30th ' );		
			$bps_next_job_visual = str_replace( $day_numeric, $day_ordinal, $row->bps_next_job );			
			}
			
			echo '<td>'.$bps_next_job_visual.'</td>';
			
			if ( $row->bps_email_zip == 'Delete' ) {
				echo '<td>'.__('Yes & Delete', 'bulletproof-security').'</td>';
			} elseif ( $row->bps_email_zip == 'EmailOnly' ) {
				echo '<td>'.__('Send Email Only', 'bulletproof-security').'</td>';			
			} else {
				echo '<td>'.$row->bps_email_zip.'</td>';
			}
			
			echo '<td>'.$row->bps_job_created.'</td>';
			echo '</tr>';
		}
	}
	echo '</tbody>';
	echo '</table>';
	echo '</div>';

	echo "<p><input type=\"submit\" name=\"Submit-DBB-Run-Job\" value=\"".__('Run Job/Delete Job', 'bulletproof-security')."\" class=\"bps-blue-button\" onclick=\"return confirm('".__('CAUTION:\n\n-------------------------------------------------------------\n\nThis Form is used to Run Backup Jobs or Delete Backup Jobs depending on which checkbox you selected.\n\n-------------------------------------------------------------\n\nClick OK to either Run a Backup Job or Delete Backup Job(s) or click Cancel', 'bulletproof-security')."')\" /></p></form>";


	} // end if ( is_admin() && wp_script_is( 'bps-js', $list = 'queue' )...
?>

<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
    $('.checkallDeleteJobs').click(function() {
        $(this).parents('#DBBJobscheckall:eq(0)').find('.deletejobALL:checkbox').attr('checked', this.checked);
    });
});
/* ]]> */
</script>

<?php

// Form Processing: DB Backup Run/Delete Jobs Form
if ( isset( $_POST['Submit-DBB-Run-Job'] ) && current_user_can('manage_options') ) {
	check_admin_referer('bulletproof_security_db_backup_run_job');
	
	$DBBjobs = $_POST['DBBjobs'];
	$DBBtable_name = $wpdb->prefix . "bpspro_db_backup";

	switch( $_POST['Submit-DBB-Run-Job'] ) {
		case __('Run Job/Delete Job', 'bulletproof-security'):
		
		$delete_jobs = array();
		$run_jobs = array();
		
		if ( !empty( $DBBjobs ) ) {
			
			foreach ( $DBBjobs as $key => $value ) {
				
				if ( $value == 'deletejob' ) {
					$delete_jobs[] = $key;
				
				} elseif ( $value == 'runjob' ) {
					$run_jobs[] = $key;
				}
			}
		}
			
		if ( !empty( $delete_jobs ) ) {
			
			echo '<div id="message" class="updated" style="border:1px solid #999999;margin-left:70px;background-color:#ffffe0;"><p>';

			foreach ( $delete_jobs as $delete_job ) {
				
				$DBBackupRows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $DBBtable_name WHERE bps_id = %d", $delete_job ) );
			
				foreach ( $DBBackupRows as $row ) {
					
					$delete_row = $wpdb->query( $wpdb->prepare( "DELETE FROM $DBBtable_name WHERE bps_id = %d", $delete_job ) );
					
					wp_clear_scheduled_hook('bpsPro_DBB_check');
					
					$textDelete = '<strong><font color="green">'.__('Backup Job: ', 'bulletproof-security').$row->bps_desc.__(' has been deleted successfully.', 'bulletproof-security').'</font></strong><br>';
					echo $textDelete;
	
				}
			}
			echo '<div class="bps-message-button" style="width:90px;"><a href="admin.php?page=bulletproof-security/admin/db-backup-security/db-backup-security.php">'.__('Refresh Status', 'bulletproof-security').'</a></div>';
			echo '</p></div>';
		}
		
		if ( !empty( $run_jobs ) ) {
			
			//$DBBoptions = get_option('bulletproof_security_options_db_backup'); 
			$db_backup = $DBBoptions['bps_db_backup_folder'] . '/' . DB_NAME . '.sql';
				
			echo '<div id="message" class="updated" style="border:1px solid #999999;margin-left:70px;background-color:#ffffe0;"><p>';

			foreach ( $run_jobs as $run_job ) {
				
				$DBBackupRows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $DBBtable_name WHERE bps_id = %d", $run_job ) );
			
				foreach ( $DBBackupRows as $row ) {

					$job_type = $row->bps_job_type;
					$email_zip = $row->bps_email_zip;
					
					$build_query_1 = "SHOW TABLES FROM `".DB_NAME."` WHERE `Tables_in_".DB_NAME."` LIKE '";
					$build_query_2 = str_replace( ', ', "' OR `Tables_in_".DB_NAME."` LIKE '", $row->bps_table_name );
					$build_query_3 = "'";
					$tables = $wpdb->get_results( $build_query_1.$build_query_2.$build_query_3, ARRAY_A );
					
					bpsPro_db_backup( $db_backup, $tables, $job_type, $email_zip );
					
					$update_rows = $wpdb->update( $DBBtable_name, array( 'bps_last_job' => $timestamp ), array( 'bps_id' => $row->bps_id ) );

					$textRunJob = '<strong><font color="green">'.__('Backup Job: ', 'bulletproof-security').$row->bps_desc.__(' has completed.', 'bulletproof-security').'<br>'.__('Your DB Backup Log contains the Backup Job Completion Time and other information about this Backup.', 'bulletproof-security').'</font></strong><br>';
					echo $textRunJob;

				}			
			}
			echo '<div class="bps-message-button" style="width:90px;"><a href="admin.php?page=bulletproof-security/admin/db-backup-security/db-backup-security.php">'.__('Refresh Status', 'bulletproof-security').'</a></div>';
			echo '</p></div>';			
		}
		break;
	} // end Switch
}

?>

</div>
<h3><?php _e('Backup Files ~ Download/Delete', 'bulletproof-security'); ?></h3>
<div>

<?php
	if ( is_admin() && wp_script_is( 'bps-js', $list = 'queue' ) && current_user_can('manage_options') ) {	

	// Form: DB Backup File Delete & Download Files Form
	echo '<form name="bpsDBBackupFiles" action="admin.php?page=bulletproof-security/admin/db-backup-security/db-backup-security.php" method="post">';
	wp_nonce_field('bulletproof_security_db_backup_delete_files');

	$source = $DBBoptions['bps_db_backup_folder'];
	$count = 0;	
	
	if ( is_dir($source) ) {
		
		$iterator = new DirectoryIterator($source);

	echo '<div id="DBBFilescheckall">';
	echo '<table class="widefat" style="text-align:left;padding:5px;">';
	echo '<thead>';
	echo '<tr>';
	echo '<th scope="col" style="width:20%;font-size:1.13em;background-color:transparent;"><strong>'.__('Backup Filename', 'bulletproof-security').'</strong></th>';
	echo '<th scope="col" style="width:5%;font-size:1.13em;"><strong><div style="position:relative; bottom:-9px; left:0px;">'.__('Delete', 'bulletproof-security').'</span></strong><br><input type="checkbox" class="checkallDeleteFiles" style="text-align:left;margin-left:0px;" /></th>';	
	echo '<th scope="col" style="width:5%;font-size:1.13em;background-color:transparent;"><strong>'.__('Download', 'bulletproof-security').'</strong></th>';
	echo '<th scope="col" style="width:45%;font-size:1.13em;background-color:transparent;"><strong>'.__('Backup Folder', 'bulletproof-security').'</strong></th>';
	echo '<th scope="col" style="width:10%;font-size:1.13em;background-color:transparent;"><strong>'.__('Size', 'bulletproof-security').'</strong></th>';
	echo '<th scope="col" style="width:15%;font-size:1.13em;background-color:transparent;"><strong>'.__('Date/Time', 'bulletproof-security').'</strong></th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	echo '<tr>';		

		foreach ( $iterator as $file ) {
			
			if ( $file->isFile() && $file->getFilename() != '.htaccess' ) {
				$count++;	
				$fileSize = filesize( $source.DIRECTORY_SEPARATOR.$file->getFilename() );
				$last_modified = filemtime( $source.DIRECTORY_SEPARATOR.$file->getFilename() );  

			echo '<th scope="row" style="border-bottom:none;font-size:1.13em;">'.$file->getFilename().'</th>';
			echo "<td><input type=\"checkbox\" id=\"deletefile\" name=\"DBBfiles[".$file->getFilename()."]\" value=\"deletefile\" class=\"deletefileALL\" /></td>";
			echo '<td><a href="'.$DBBoptions['bps_db_backup_download_link'] . $file->getFilename().'" style="font-size:1em;">'.__('Download', 'bulletproof-security').'</a></td>';			
			echo '<td>'.$DBBoptions['bps_db_backup_folder'].'</td>';
			
			if ( number_format( $fileSize, 2, '.', '' ) >= 1048576 ) {
				echo '<td>'.number_format( $fileSize / ( 1024 * 1024 ), 2 ).' MB</td>';				
			} else {
				echo '<td>'.number_format( $fileSize / 1024, 2 ).' KB</td>';
			}
			echo '<td>'.date( 'Y-m-d g:i a', $last_modified + $gmt_offset ).'</td>';
			echo '</tr>';
			
			} else {	

			if ( !$file->isDot() && $count <= 0 && $file->getFilename() != '.htaccess' ) {
			
			echo '<th scope="row" style="border-bottom:none;">'.__('No Backup Jobs have been Run yet. No Files in Backup.', 'bulletproof-security').'</th>';
			echo '<td></td>';		
			echo '<td></td>'; 
			echo '<td></td>';		
			echo '<td></td>'; 
			echo '<td></td>';
			echo '</tr>';						
			}
			}
		}
	
	echo '</tbody>';
	echo '</table>';
	echo '</div>';		
	}

	echo "<p><input type=\"submit\" name=\"Submit-DBB-Files\" value=\"".__('Delete Files', 'bulletproof-security')."\" class=\"bps-blue-button\" onclick=\"return confirm('".__('Click OK to Delete Backup File(s) or click Cancel', 'bulletproof-security')."')\" /></p></form>";

	} // end if ( is_admin() && wp_script_is( 'bps-js', $list = 'queue' )...
?>  

<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
    $('.checkallDeleteFiles').click(function() {
        $(this).parents('#DBBFilescheckall:eq(0)').find('.deletefileALL:checkbox').attr('checked', this.checked);
    });
});
/* ]]> */
</script>

<?php

// Form Processing: DB Backup File Delete Files Form (downloads are links and not processed)
if ( isset( $_POST['Submit-DBB-Files'] ) && current_user_can('manage_options') ) {
	check_admin_referer('bulletproof_security_db_backup_delete_files');
	
	$DBBFiles = $_POST['DBBfiles'];

	switch( $_POST['Submit-DBB-Files'] ) {
		case __('Delete Files', 'bulletproof-security'):
		
		$delete_files = array();
		$download_files = array();
		
		if ( !empty( $DBBFiles ) ) {
			
			foreach ( $DBBFiles as $key => $value ) {
				
				if ( $value == 'deletefile' ) {
					$delete_files[] = $key;
					
				}
			}
		}
			
		if ( !empty( $delete_files ) ) {
			
			echo '<div id="message" class="updated" style="border:1px solid #999999;margin-left:70px;background-color:#ffffe0;"><p>';

			foreach ( $delete_files as $delete_file ) {
				
				unlink( $DBBoptions['bps_db_backup_folder'] . '/' . $delete_file );
				$textDelete = '<strong><font color="green">'.__('Backup File: ', 'bulletproof-security').$delete_file.__(' has been deleted successfully.', 'bulletproof-security').'</font><strong><br>';
				echo $textDelete;

			}
			echo '<div class="bps-message-button" style="width:90px;"><a href="admin.php?page=bulletproof-security/admin/db-backup-security/db-backup-security.php">'.__('Refresh Status', 'bulletproof-security').'</a></div>';
			echo '</p></div>';	
		}
		break;
	}
}

?>

</div>
<h3><?php _e('Create Backup Jobs', 'bulletproof-security'); ?></h3>
<div>

<?php
	if ( is_admin() && wp_script_is( 'bps-js', $list = 'queue' ) && current_user_can('manage_options') ) {	
	
	// Form: DB Backup Create Job Form
	echo '<form name="bpsDBBackupCreateJob" action="admin.php?page=bulletproof-security/admin/db-backup-security/db-backup-security.php" method="post">';
	wp_nonce_field('bulletproof_security_db_backup_create_job');

	//$DBBoptions = get_option('bulletproof_security_options_db_backup');
	$DBTables = 0;
	$size = 0;
	$getDBTables = $wpdb->get_results( $wpdb->prepare( "SHOW TABLE STATUS WHERE Rows >= %d", $DBTables ) );

	echo '<div id="DBBcheckall" style="max-height:400px;">';
	echo '<table style="text-align:left;border-right:1px solid black;padding:5px;">';
	echo '<thead>';
	echo '<tr>';
	echo '<th scope="col" style="width:20px;font-size:1em;border-bottom:1px solid black;background-color:transparent;"><strong>'.__('All', 'bulletproof-security').'</strong><br><input type="checkbox" class="checkallDBB" /></th>';
	echo '<th scope="col" style="width:400px;font-size:1.13em;padding-top:20px;margin-right:20px;border-bottom:1px solid black;background-color:transparent;"><strong>'.__('DB Table Name', 'bulletproof-security').'</strong></th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	echo '<tr>';
	
	$checked = ( isset( $_POST['dbb[$Tabledata->Name]'] ) ) ? $_POST['dbb[$Tabledata->Name]'] : 'checked';
	
	foreach ( $getDBTables as $Tabledata ) {

		echo "<td><input type=\"checkbox\" id=\"dbbtables\" name=\"dbb[$Tabledata->Name]\" value=\"dbbtables\" class=\"dbbtablesALL\" $checked /></td>";
		echo '<td>'.$Tabledata->Name.'</td>';
		echo '</tr>';
	}

	echo '</tbody>';
	echo '</table>';
	echo '</div>'; // jQuery div parent
	
	echo '<div id="DBBOptions" style="margin:0px 0px 0px 0px;float:left;">';

	$DBBDescription = ( isset( $_POST['DBBDescription'] ) ) ? $_POST['DBBDescription'] : $DBBoptions['bps_db_backup_description'];	
	$DBBFolder = ( isset( $_POST['DBBFolder'] ) ) ? $_POST['DBBFolder'] : $DBBoptions['bps_db_backup_folder'];
	$DBBDownloadLink = ( isset( $_POST['DBBDownloadLink'] ) ) ? $_POST['DBBDownloadLink'] : $DBBoptions['bps_db_backup_download_link'];	
	
	echo '<label for="bps-dbb">'.__('Description/Backup Job Name:', 'bulletproof-security').'</label><br>';
	echo '<input type="text" name="DBBDescription" class="regular-text-short-fixed" style="width:500px;margin:0px 0px 10px 0px;" value="" /><br>';

	echo '<label for="bps-dbb">'.__('DB Backup Folder Location:', 'bulletproof-security').'</label><br>';
	echo '<label for="bps-dbb"><font color="blue"><strong>'.__('Recommended: Use The Default Obfuscated & Secure BPS Backup Folder', 'bulletproof-security').'</strong></font></label><br>';
	echo '<input type="text" name="DBBFolder" class="regular-text-short-fixed" style="width:500px;margin:0px 0px 10px 0px;" value="'.trim(stripslashes($DBBFolder)).'" /><br>';
	
	echo '<label for="bps-dbb">'.__('DB Backup File Download Link/URL:', 'bulletproof-security').'</label><br>';
	echo '<label for="bps-dbb"><font color="blue"><strong>'.__('Note: If you are seeing 404 errors when trying to download zip files or if you have changed', 'bulletproof-security').'</strong></font></label><br>';
	echo '<label for="bps-dbb"><font color="blue"><strong>'.__('the DB Backup Folder Location above, click the DB Backup Read Me help button', 'bulletproof-security').'</strong></font></label><br>';
	echo '<input type="text" name="DBBDownloadLink" class="regular-text-short-fixed" style="width:500px;margin:0px 0px 10px 0px;" value="'.trim($DBBDownloadLink).'" /><br>';

	echo '<label for="bps-dbb">'.__('Backup Job Type: Manual or Scheduled', 'bulletproof-security').'</label><br>';
	echo '<select name="dbb_backup_job_type" style="width:340px;">';
	echo '<option value="Manual"'. selected('Manual', $DBBoptions['bps_db_backup_job_type']).'>'.__('Manual DB Backup Job', 'bulletproof-security').'</option>';
	echo '<option value="Scheduled"'. selected('Scheduled', $DBBoptions['bps_db_backup_job_type']).'>'.__('Scheduled DB Backup Job', 'bulletproof-security').'</option>';
	echo '</select><br><br>';

	echo '<label for="bps-dbb">'.__('Frequency of Scheduled Backup Job (recurring)', 'bulletproof-security').'</label><br>';
	echo '<select name="dbb_backup_job_frequency" style="width:340px;">';
	echo '<option value="NA"'. selected('NA', $DBBoptions['bps_db_backup_frequency']).'>'.__('N/A', 'bulletproof-security').'</option>';
	echo '<option value="Hourly"'. selected('Hourly', $DBBoptions['bps_db_backup_frequency']).'>'.__('Hourly Scheduled DB Backup Job', 'bulletproof-security').'</option>';
	echo '<option value="Daily"'. selected('Daily', $DBBoptions['bps_db_backup_frequency']).'>'.__('Daily Scheduled DB Backup Job', 'bulletproof-security').'</option>';
	echo '<option value="Weekly"'. selected('Weekly', $DBBoptions['bps_db_backup_frequency']).'>'.__('Weekly Scheduled DB Backup Job', 'bulletproof-security').'</option>';
	echo '<option value="Monthly"'. selected('Monthly', $DBBoptions['bps_db_backup_frequency']).'>'.__('Monthly Scheduled DB Backup Job', 'bulletproof-security').'</option>';
	echo '</select><br><br>';
	
	echo '<label for="bps-dbb">'.__('Hour When Scheduled Backup is Run (recurring)', 'bulletproof-security').'</label><br>';
	echo '<select name="dbb_backup_job_start_time_hour" style="width:340px;">';
	echo '<option value="NA"'. selected('NA', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('N/A', 'bulletproof-security').'</option>';
	echo '<option value="12AM"'. selected('12AM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('12AM', 'bulletproof-security').'</option>';
	echo '<option value="1AM"'. selected('1AM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('1AM', 'bulletproof-security').'</option>';
	echo '<option value="2AM"'. selected('2AM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('2AM', 'bulletproof-security').'</option>';
	echo '<option value="3AM"'. selected('3AM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('3AM', 'bulletproof-security').'</option>';
	echo '<option value="4AM"'. selected('4AM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('4AM', 'bulletproof-security').'</option>';
	echo '<option value="5AM"'. selected('5AM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('5AM', 'bulletproof-security').'</option>';
	echo '<option value="6AM"'. selected('6AM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('6AM', 'bulletproof-security').'</option>';
	echo '<option value="7AM"'. selected('7AM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('7AM', 'bulletproof-security').'</option>';
	echo '<option value="8AM"'. selected('8AM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('8AM', 'bulletproof-security').'</option>';
	echo '<option value="9AM"'. selected('9AM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('9AM', 'bulletproof-security').'</option>';
	echo '<option value="10AM"'. selected('10AM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('10AM', 'bulletproof-security').'</option>';
	echo '<option value="11AM"'. selected('11AM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('11AM', 'bulletproof-security').'</option>';
	echo '<option value="12PM"'. selected('12PM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('12PM', 'bulletproof-security').'</option>';
	echo '<option value="1PM"'. selected('1PM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('1PM', 'bulletproof-security').'</option>';
	echo '<option value="2PM"'. selected('2PM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('2PM', 'bulletproof-security').'</option>';
	echo '<option value="3PM"'. selected('3PM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('3PM', 'bulletproof-security').'</option>';
	echo '<option value="4PM"'. selected('4PM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('4PM', 'bulletproof-security').'</option>';
	echo '<option value="5PM"'. selected('5PM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('5PM', 'bulletproof-security').'</option>';
	echo '<option value="6PM"'. selected('6PM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('6PM', 'bulletproof-security').'</option>';
	echo '<option value="7PM"'. selected('7PM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('7PM', 'bulletproof-security').'</option>';
	echo '<option value="8PM"'. selected('8PM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('8PM', 'bulletproof-security').'</option>';
	echo '<option value="9PM"'. selected('9PM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('9PM', 'bulletproof-security').'</option>';
	echo '<option value="10PM"'. selected('10PM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('10PM', 'bulletproof-security').'</option>';
	echo '<option value="11PM"'. selected('11PM', $DBBoptions['bps_db_backup_start_time_hour']).'>'.__('11PM', 'bulletproof-security').'</option>';
	echo '</select><br><br>';	

	echo '<label for="bps-dbb">'.__('Day of Week When Scheduled Backup is Run (recurring)', 'bulletproof-security').'</label><br>';
	echo '<select name="dbb_backup_job_start_time_weekday" style="width:340px;">';
	echo '<option value="NA"'. selected('NA', $DBBoptions['bps_db_backup_start_time_weekday']).'>'.__('N/A', 'bulletproof-security').'</option>';
	echo '<option value="Sunday"'. selected('Sunday', $DBBoptions['bps_db_backup_start_time_weekday']).'>'.__('Sunday', 'bulletproof-security').'</option>';
	echo '<option value="Monday"'. selected('Monday', $DBBoptions['bps_db_backup_start_time_weekday']).'>'.__('Monday', 'bulletproof-security').'</option>';
	echo '<option value="Tuesday"'. selected('Tuesday', $DBBoptions['bps_db_backup_start_time_weekday']).'>'.__('Tuesday', 'bulletproof-security').'</option>';
	echo '<option value="Wednesday"'. selected('Wednesday', $DBBoptions['bps_db_backup_start_time_weekday']).'>'.__('Wednesday', 'bulletproof-security').'</option>';
	echo '<option value="Thursday"'. selected('Thursday', $DBBoptions['bps_db_backup_start_time_weekday']).'>'.__('Thursday', 'bulletproof-security').'</option>';
	echo '<option value="Friday"'. selected('Friday', $DBBoptions['bps_db_backup_start_time_weekday']).'>'.__('Friday', 'bulletproof-security').'</option>';
	echo '<option value="Saturday"'. selected('Saturday', $DBBoptions['bps_db_backup_start_time_weekday']).'>'.__('Saturday', 'bulletproof-security').'</option>';
	echo '</select><br><br>';

	echo '<label for="bps-dbb">'.__('Day of Month When Scheduled Backup is Run (recurring)', 'bulletproof-security').'</label><br>';
	echo '<select name="dbb_backup_job_start_time_month_date" style="width:340px;">';
	echo '<option value="NA"'. selected('NA', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('N/A', 'bulletproof-security').'</option>';
	echo '<option value="1"'. selected('1', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('1st', 'bulletproof-security').'</option>';
	echo '<option value="2"'. selected('2', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('2nd', 'bulletproof-security').'</option>';
	echo '<option value="3"'. selected('3', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('3rd', 'bulletproof-security').'</option>';
	echo '<option value="4"'. selected('4', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('4th', 'bulletproof-security').'</option>';
	echo '<option value="5"'. selected('5', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('5th', 'bulletproof-security').'</option>';
	echo '<option value="6"'. selected('6', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('6th', 'bulletproof-security').'</option>';
	echo '<option value="7"'. selected('7', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('7th', 'bulletproof-security').'</option>';
	echo '<option value="8"'. selected('8', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('8th', 'bulletproof-security').'</option>';
	echo '<option value="9"'. selected('9', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('9th', 'bulletproof-security').'</option>';
	echo '<option value="10"'. selected('10', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('10th', 'bulletproof-security').'</option>';
	echo '<option value="11"'. selected('11', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('11th', 'bulletproof-security').'</option>';
	echo '<option value="12"'. selected('12', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('12th', 'bulletproof-security').'</option>';
	echo '<option value="13"'. selected('13', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('13th', 'bulletproof-security').'</option>';
	echo '<option value="14"'. selected('14', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('14th', 'bulletproof-security').'</option>';
	echo '<option value="15"'. selected('15', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('15th', 'bulletproof-security').'</option>';
	echo '<option value="16"'. selected('16', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('16th', 'bulletproof-security').'</option>';
	echo '<option value="17"'. selected('17', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('17th', 'bulletproof-security').'</option>';
	echo '<option value="18"'. selected('18', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('18th', 'bulletproof-security').'</option>';
	echo '<option value="19"'. selected('19', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('19th', 'bulletproof-security').'</option>';
	echo '<option value="20"'. selected('20', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('20th', 'bulletproof-security').'</option>';
	echo '<option value="21"'. selected('21', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('21st', 'bulletproof-security').'</option>';
	echo '<option value="22"'. selected('22', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('22nd', 'bulletproof-security').'</option>';
	echo '<option value="23"'. selected('23', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('23rd', 'bulletproof-security').'</option>';
	echo '<option value="24"'. selected('24', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('24th', 'bulletproof-security').'</option>';
	echo '<option value="25"'. selected('25', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('25th', 'bulletproof-security').'</option>';
	echo '<option value="26"'. selected('26', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('26th', 'bulletproof-security').'</option>';
	echo '<option value="27"'. selected('27', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('27th', 'bulletproof-security').'</option>';
	echo '<option value="28"'. selected('28', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('28th', 'bulletproof-security').'</option>';
	echo '<option value="29"'. selected('29', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('29th', 'bulletproof-security').'</option>';
	echo '<option value="30"'. selected('30', $DBBoptions['bps_db_backup_start_time_month_date']).'>'.__('30th', 'bulletproof-security').'</option>';
	echo '</select><br><br>';	

	echo '<label for="bps-dbb">'.__('Send Scheduled Backup Zip File Via Email or Just Email Only:', 'bulletproof-security').'</label><br>';
	echo '<label for="bps-dbb"><font color="blue"><strong>'.__('Note: Check with your email provider for the maximum<br>file attachment size limit that is allowed by your Mail Server', 'bulletproof-security').'</strong></font></label><br>';
	echo '<select name="dbb_backup_email_zip" style="width:340px;">';
	echo '<option value="No"'. selected('No', $DBBoptions['bps_db_backup_email_zip']).'>'.__('Do Not Email Zip Backup File', 'bulletproof-security').'</option>';
	echo '<option value="Delete"'. selected('Delete', $DBBoptions['bps_db_backup_email_zip']).'>'.__('Email & Delete Zip Backup File', 'bulletproof-security').'</option>';
	echo '<option value="Yes"'. selected('Yes', $DBBoptions['bps_db_backup_email_zip']).'>'.__('Email Zip Backup File', 'bulletproof-security').'</option>';
	echo '<option value="EmailOnly"'. selected('EmailOnly', $DBBoptions['bps_db_backup_email_zip']).'>'.__('Send Email Only & Not Zip Backup File', 'bulletproof-security').'</option>';
	echo '</select><br><br>';

	echo '<label for="bps-dbb">'.__('Automatically Delete Old Backup Files', 'bulletproof-security').'</label><br>';
	echo '<select name="dbb_backup_delete" style="width:340px;">';
	echo '<option value="Never"'. selected('Never', $DBBoptions['bps_db_backup_delete']).'>'.__('Never Delete Old Backup Files', 'bulletproof-security').'</option>';
	echo '<option value="1"'. selected('1', $DBBoptions['bps_db_backup_delete']).'>'.__('Delete Backup Files Older Than 1 Day', 'bulletproof-security').'</option>';
	echo '<option value="5"'. selected('5', $DBBoptions['bps_db_backup_delete']).'>'.__('Delete Backup Files Older Than 5 Days', 'bulletproof-security').'</option>';
	echo '<option value="10"'. selected('10', $DBBoptions['bps_db_backup_delete']).'>'.__('Delete Backup Files Older Than 10 Days', 'bulletproof-security').'</option>';
	echo '<option value="15"'. selected('15', $DBBoptions['bps_db_backup_delete']).'>'.__('Delete Backup Files Older Than 15 Days', 'bulletproof-security').'</option>';
	echo '<option value="30"'. selected('30', $DBBoptions['bps_db_backup_delete']).'>'.__('Delete Backup Files Older Than 30 Days', 'bulletproof-security').'</option>';
	echo '<option value="60"'. selected('60', $DBBoptions['bps_db_backup_delete']).'>'.__('Delete Backup Files Older Than 60 Days', 'bulletproof-security').'</option>';
	echo '<option value="90"'. selected('90', $DBBoptions['bps_db_backup_delete']).'>'.__('Delete Backup Files Older Than 90 Days', 'bulletproof-security').'</option>';
	echo '<option value="180"'. selected('180', $DBBoptions['bps_db_backup_delete']).'>'.__('Delete Backup Files Older Than 180 Days', 'bulletproof-security').'</option>';
	echo '</select><br><br>';

	echo '<label for="bps-dbb">'.__('Turn On/Off All Scheduled Backups (override):', 'bulletproof-security').'</label><br>';
	echo '<select name="dbb_backup_on_off" style="width:340px;">';
	echo '<option value="On"'. selected('On', $DBBoptions['bps_db_backup']).'>'.__('Turn On All Scheduled Backups', 'bulletproof-security').'</option>';
	echo '<option value="Off"'. selected('Off', $DBBoptions['bps_db_backup']).'>'.__('Turn Off All Scheduled Backups', 'bulletproof-security').'</option>';
	echo '</select><br><br>';

	echo "<p><input type=\"submit\" name=\"Submit-DBB-Create-Job\" value=\"".__('Create Backup Job', 'bulletproof-security')."\" class=\"bps-blue-button\" onclick=\"return confirm('".__('Click OK to Create this Backup Job or click Cancel', 'bulletproof-security')."')\" /></p></form>";

	echo '</div>';
	}

?>

<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
    $('.checkallDBB').click(function() {
		$(this).parents('#DBBcheckall:eq(0)').find('.dbbtablesALL:checkbox').attr('checked', this.checked);
    });
});
/* ]]> */
</script>

<?php

// Form Processing: DB Backup Create Job Form
if ( isset($_POST['Submit-DBB-Create-Job']) && current_user_can('manage_options') ) {
	check_admin_referer('bulletproof_security_db_backup_create_job');
	
	if ( $DBBoptions['bps_db_backup_status_display'] == 'No DB Backups' ) {
		$bps_db_backup_status_display = 'Backup Job Created';
	} else {
		$bps_db_backup_status_display = $DBBoptions['bps_db_backup_status_display'];
	}

	if ( $_POST['dbb_backup_on_off'] == 'Off' ) {
		wp_clear_scheduled_hook('bpsPro_DBB_check');
	}
	
	// some of these options are "one-shot" options
	$DBB_Create_Job_Options = array( 
	'bps_db_backup' => $_POST['dbb_backup_on_off'], 
	'bps_db_backup_description' => $_POST['DBBDescription'], 
	'bps_db_backup_folder' => $_POST['DBBFolder'], 
	'bps_db_backup_download_link' => $_POST['DBBDownloadLink'], 
	'bps_db_backup_job_type' => $_POST['dbb_backup_job_type'], 
	'bps_db_backup_frequency' => $_POST['dbb_backup_job_frequency'], 		 
	'bps_db_backup_start_time_hour' => $_POST['dbb_backup_job_start_time_hour'], 
	'bps_db_backup_start_time_weekday' => $_POST['dbb_backup_job_start_time_weekday'], 
	'bps_db_backup_start_time_month_date' => $_POST['dbb_backup_job_start_time_month_date'], 
	'bps_db_backup_email_zip' => $_POST['dbb_backup_email_zip'], 
	'bps_db_backup_delete' => $_POST['dbb_backup_delete'], 
	'bps_db_backup_status_display' => $bps_db_backup_status_display // one-shot/one-time option - used for one-time Dashboard status display
	);
	
		foreach( $DBB_Create_Job_Options as $key => $value ) {
			update_option('bulletproof_security_options_db_backup', $DBB_Create_Job_Options);
		}
	
	$DBB_Create_Job = $_POST['dbb'];
	$DBBtable_name = $wpdb->prefix . "bpspro_db_backup";
	$timeNow = time();
	$gmt_offset = get_option( 'gmt_offset' ) * 3600;
	$timestamp = date_i18n(get_option('date_format'), strtotime("11/15-1976")) . ' ' . date_i18n(get_option('time_format'), $timeNow + $gmt_offset);
	$bpsDBBLog = WP_CONTENT_DIR . '/bps-backup/logs/db_backup_log.txt';
	
		if ( $_POST['dbb_backup_job_type'] == 'Manual' ) {
			$bps_frequency = 'Manual';
			$bps_last_job = 'Backup Job Created';
			$bps_next_job = 'Manual';
			$bps_email_zip = 'Manual';
			$bps_email_zip_log = 'Manual';
		}
	
		if ( $_POST['dbb_backup_job_type'] == 'Scheduled' ) {
			$bps_frequency = $_POST['dbb_backup_job_frequency'];
			$bps_last_job = 'Backup Job Created';
			$bps_next_job = $_POST['dbb_backup_job_start_time_weekday'] . ' ' .  $_POST['dbb_backup_job_start_time_month_date'] . ' ' .  $_POST['dbb_backup_job_start_time_hour'];
			$bps_next_job = trim( str_replace( 'NA', "", $bps_next_job ) );	
			
			if ( $_POST['dbb_backup_email_zip'] == 'Delete' ) {
				$bps_email_zip_log = 'Yes & Delete';
				$bps_email_zip = 'Delete';	
			} else {
				$bps_email_zip_log = $_POST['dbb_backup_email_zip'];
				$bps_email_zip = $_POST['dbb_backup_email_zip'];
			}

			if ( $_POST['dbb_backup_email_zip'] == 'EmailOnly' ) {
				$bps_email_zip_log = 'Send Email Only';
				$bps_email_zip = 'EmailOnly';	
			} else {
				$bps_email_zip_log = $_POST['dbb_backup_email_zip'];
				$bps_email_zip = $_POST['dbb_backup_email_zip'];
			}
		}

	$log_title = "\r\n" . '[Create Backup Job Settings Logged: ' . $timestamp . ']' . "\r\n" . 'Description/Backup Job Name: ' . $_POST['DBBDescription'] . "\r\n" . 'DB Backup Folder Location: ' . $_POST['DBBFolder'] . "\r\n" . 'DB Backup File Download Link/URL: ' . $_POST['DBBDownloadLink'] . "\r\n" . 'Backup Job Type: ' . $_POST['dbb_backup_job_type'] . "\r\n" . 'Frequency: ' . $_POST['dbb_backup_job_frequency'] . "\r\n" . 'Time When Scheduled Backup is Run: ' . $bps_next_job . "\r\n" . 'Send Scheduled Backup Zip Files Via Email: ' . $bps_email_zip_log . "\r\n" . 'Automatically Delete Old Backup Files Older Than: ' . $_POST['dbb_backup_delete'] .' day(s) old'. "\r\n" . 'Scheduled Backups (override): ' . $_POST['dbb_backup_on_off'] . "\r\n";
	
	if ( empty( $DBB_Create_Job ) ) {
		echo '<div id="message" class="updated" style="border:1px solid #999999;margin-left:70px;background-color:#ffffe0;"><p>';	
		echo '<strong><font color="red">'.__('Error: You did not select any DB Tables to backup. Backup Job was not created.', 'bulletproof-security').'</font></strong><br>';
		echo '</p></div>';
	}
	
	if ( !empty( $DBB_Create_Job ) ) {
		
		if ( is_writable( $bpsDBBLog ) ) {
		if ( !$handle = fopen( $bpsDBBLog, 'a' ) ) {
        	exit;
    	}
    	if ( fwrite( $handle, $log_title ) === FALSE ) {
        	exit;
    	}
    	fclose($handle);
		}

		echo '<div id="message" class="updated" style="border:1px solid #999999;margin-left:70px;background-color:#ffffe0;"><p>';
		
		$Table_array = array();
		
		foreach ( $DBB_Create_Job as $key => $value ) {

			$Table_array[] = $key;
			$comma_separated = implode(', ', $Table_array);	
			$NoDupes = implode(', ', array_unique(explode(', ', $comma_separated)));
			
			$log_contents = 'Table Name: ' . $key . "\r\n";
					
			if ( is_writable( $bpsDBBLog ) ) {
			if ( !$handle = fopen( $bpsDBBLog, 'a' ) ) {
         		exit;
    		}
    		if ( fwrite( $handle, $log_contents ) === FALSE ) {
        		exit;
    		}
    		fclose($handle);
			}
		}

		/** Date & Time Calculations **/
		if ( $_POST['dbb_backup_job_start_time_hour'] == 'NA' ) {
			
			$hour = date( "H", time() );
		
		} else {
			
			$form_hours = array( '12AM', '1AM', '2AM', '3AM', '4AM', '5AM', '6AM', '7AM', '8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM', '9PM', '10PM', '11PM' );
			$military_hours = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23' );		
			$hour = str_replace( $form_hours, $military_hours, $_POST['dbb_backup_job_start_time_hour'] );			
		}

		if ( $_POST['dbb_backup_job_start_time_month_date'] == 'NA' ) {
			$day = date( "j", time() );	
		
		} else {
			
			$day = $_POST['dbb_backup_job_start_time_month_date'];		
		}
		
		$clock = mktime( $hour, 0, 0, date( "n", time() ), $day, date( "Y", time() ) );

		$form_weekday = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
		$form_numeric = array( '0', '1', '2', '3', '4', '5', '6' );		
		$weekday_numeric = str_replace( $form_weekday, $form_numeric, $_POST['dbb_backup_job_start_time_weekday'] );	
		
		$day_of_week_now_numeric = date( "w", time() );
		
		if ( $day_of_week_now_numeric == $weekday_numeric || $_POST['dbb_backup_job_start_time_weekday'] == 'NA' ) {
   			$weekday_days = 0;
		
		} else {

			$dwn = $day_of_week_now_numeric;
			$dwf = $weekday_numeric;
	
			// Bulky stuff, but the "for" loops code was overly complex, problematic and limiting for some scenarios
			// sometimes simpler is better - minimal finite data so no big deal
			if ( $dwn == '0' && $dwf == '1' || $dwn == '1' && $dwf == '2' || $dwn == '2' && $dwf == '3' || $dwn == '3' && $dwf == '4' || $dwn == '4' && $dwf == '5' || $dwn == '5' && $dwf == '6' || $dwn == '6' && $dwf == '0' ) {
				$weekday_days = 1;
			}

			if ( $dwn == '0' && $dwf == '2' || $dwn == '1' && $dwf == '3' || $dwn == '2' && $dwf == '4' || $dwn == '3' && $dwf == '5' || $dwn == '4' && $dwf == '6' || $dwn == '5' && $dwf == '0' || $dwn == '6' && $dwf == '1' ) {
				$weekday_days = 2;
			}

			if ( $dwn == '0' && $dwf == '3' || $dwn == '1' && $dwf == '4' || $dwn == '2' && $dwf == '5' || $dwn == '3' && $dwf == '6' || $dwn == '4' && $dwf == '0' || $dwn == '5' && $dwf == '1' || $dwn == '6' && $dwf == '2' ) {
				$weekday_days = 3;
			}

			if ( $dwn == '0' && $dwf == '4' || $dwn == '1' && $dwf == '5' || $dwn == '2' && $dwf == '6' || $dwn == '3' && $dwf == '0' || $dwn == '4' && $dwf == '1' || $dwn == '5' && $dwf == '2' || $dwn == '6' && $dwf == '3' ) {
				$weekday_days = 4;
			}

			if ( $dwn == '0' && $dwf == '5' || $dwn == '1' && $dwf == '6' || $dwn == '2' && $dwf == '0' || $dwn == '3' && $dwf == '1' || $dwn == '4' && $dwf == '2' || $dwn == '5' && $dwf == '3' || $dwn == '6' && $dwf == '4' ) {
				$weekday_days = 5;
			}
	
			if ( $dwn == '0' && $dwf == '6' || $dwn == '1' && $dwf == '0' || $dwn == '2' && $dwf == '1' || $dwn == '3' && $dwf == '2' || $dwn == '4' && $dwf == '3' || $dwn == '5' && $dwf == '4' || $dwn == '6' && $dwf == '5' ) {
				$weekday_days = 6;
			}
		}
		
		$bps_next_job_unix = $clock + ($weekday_days * 24 * 60 * 60); 

		$DBBInsertRows = $wpdb->insert( $DBBtable_name, array( 'bps_table_name' => $NoDupes, 'bps_desc' => $_POST['DBBDescription'], 'bps_job_type' => $_POST['dbb_backup_job_type'], 'bps_frequency' => $bps_frequency, 'bps_last_job' => $bps_last_job, 'bps_next_job' => $bps_next_job, 'bps_next_job_unix' => $bps_next_job_unix, 'bps_email_zip' => $bps_email_zip, 'bps_job_created' => current_time('mysql') ) );
		
		$text = '<strong><font color="green">'.__('Backup Job ', 'bulletproof-security').$_POST['DBBDescription'].__(' Created Successfully.', 'bulletproof-security').'</font></strong><br>';
		echo $text;
		echo '<strong>'.__('Backup Job Settings Logged successfully in the DB Backup Log', 'bulletproof-security').'</strong><br>';
		echo '<div class="bps-message-button" style="width:90px;"><a href="admin.php?page=bulletproof-security/admin/db-backup-security/db-backup-security.php">'.__('Refresh Status', 'bulletproof-security').'</a></div>';	
		echo '</p></div>';
			
		$DBBLog_Options = array( 'bps_dbb_log_date_mod' => bpsPro_DBB_Log_LastMod() );
	
		foreach( $DBBLog_Options as $key => $value ) {
			update_option('bulletproof_security_options_DBB_log', $DBBLog_Options);
		}
	}
}

?>
</div>
</div>
 </td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>

</div>

<div id="bps-tabs-2" class="bps-tab-page">
<h2><?php _e('DB Backup Log', 'bulletproof-security'); ?></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">

<h3><?php _e('DB Backup Log', 'bulletproof-security'); ?>  <button id="bps-open-modal2" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
<div id="bps-modal-content2" title="<?php _e('DB Backup Log', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable (top) and resizable (bottom right corner)', 'bulletproof-security').'</strong><br><br><strong>'.__('DB Backup Log General Information', 'bulletproof-security').'</strong><br>'.__('Your DB Backup Log file is a plain text static file and not a dynamic file or dynamic display to keep your website resource usage at a bare minimum and keep your website performance at a maximum. Log entries are logged in descending order by Date and Time. You can copy, edit and delete this plain text file.', 'bulletproof-security').'<br><br><strong>'.__('NOTE: ', 'bulletproof-security').'</strong>'.__('Email Alerting and Log file options are located in S-Monitor in BPS Pro instead of being on the Login Security page, Security Log & DB Backup Log pages. The Email Alerting & Log File Options Form is identical on the Login Security, Security Log & DB Backup Log pages in BPS free. You can change and save your email alerting and log file options on any of these pages.', 'bulletproof-security').'<strong><br><br>'.__('What is Logged in The DB Backup Log?', 'bulletproof-security').'</strong><br>'.__('Depending on your DB Backup settings, log entries will be logged anytime you run a Manual Backup Job or whenever a Scheduled Cron Backup Job is run. The Backup Job Completion Time, Zip Backup File Name, timestamp and other information is logged. If you have chosen the option to automatically delete old zip backup files then the zip backup file name and timestamp will be logged when old zip backup files are automatically deleted. When you create a new Backup Job your Backup Job Settings are logged/saved in the DB Backup Log.', 'bulletproof-security').'<strong><br><br>'.__('DB Backup Log File Size', 'bulletproof-security').'</strong><br>'.__('Displays the size of your DB Backup Log file. If your log file is larger than 2MB then you will see a Red warning message displayed: The Email Alerting & Log File Options will only send log files up to 2MB in size. Copy and paste the DB Backup Log file contents into a Notepad text file on your computer and save it. Then click the Delete Log button to delete the contents of this Log file.', 'bulletproof-security').'<br><br><strong>'.__('DB Backup Log Last Modified Time', 'bulletproof-security').'</strong><br>'.__('Displays the time when the last log entry was created in your DB Backup Log file.', 'bulletproof-security').'<br><br><strong>'.__('Delete Log Button', 'bulletproof-security').'</strong><br>'.__('Clicking the Delete Log button will delete the entire contents of your DB Backup Log File.', 'bulletproof-security'); echo $text; ?></p>
</div>

<?php

// Get File Size of the DB Backup Log File
function bpsPro_DBB_LogSize() {
$filename = WP_CONTENT_DIR . '/bps-backup/logs/db_backup_log.txt';

if ( file_exists($filename) ) {
	$logSize = filesize($filename);
	
	if ( $logSize < 2097152 ) {
 		$text = '<strong>'. __('DB Backup Log File Size: ', 'bulletproof-security').'<font color="blue">'. round($logSize / 1024, 2) .' KB</font></strong><br>';
		echo $text;
	} else {
 		$text = '<strong>'. __('DB Backup Log File Size: ', 'bulletproof-security').'<font color="red">'. round($logSize / 1024, 2) .' KB<br>'.__('The Email Logging options will only send log files up to 2MB in size.', 'bulletproof-security').'</font></strong><br>'.__('Copy and paste the DB Backup Log file contents into a Notepad text file on your computer and save it.', 'bulletproof-security').'<br>'.__('Then click the Delete Log button to delete the contents of this Log file.', 'bulletproof-security').'<br>';		
		echo $text;
	}
	}
}
bpsPro_DBB_LogSize();

// Get the Current / Last Modifed Date of the DB Backup Log File
function bpsPro_DBB_Log_LastMod() {
$filename = WP_CONTENT_DIR . '/bps-backup/logs/db_backup_log.txt';

	if ( file_exists($filename) ) {
		$gmt_offset = get_option( 'gmt_offset' ) * 3600;
		$timestamp = date_i18n(get_option('date_format').' - '.get_option('time_format'), @filemtime($filename) + $gmt_offset);

	$text = '<strong>'. __('DB Backup Log Last Modified Time: ', 'bulletproof-security').'<font color="blue">'.$timestamp.'</font></strong><br><br>';
	echo $text;
	}
}
bpsPro_DBB_Log_LastMod();
?>

	<div id="LoginSecurityEmailOptions" style="width:100%;">   

<form name="bpsEmailAlerts" action="options.php" method="post">
    <?php settings_fields('bulletproof_security_options_email'); ?>
	<?php $options = get_option('bulletproof_security_options_email'); ?>
	<?php $admin_email = get_option('admin_email'); ?>

<table border="0">
  <tr>
    <td><label for="bps-monitor-email"><?php _e('Send Email Alerts & Log Files To:', 'bulletproof-security'); ?> </label></td>
    <td><input type="text" name="bulletproof_security_options_email[bps_send_email_to]" class="regular-text-long-fixed" style="width:200px;" value="<?php if ( $options['bps_send_email_to'] != '' ) { echo $options['bps_send_email_to']; } else { echo $admin_email; } ?>" /></td>
  </tr>
  <tr>
    <td><label for="bps-monitor-email"><?php _e('Send Email Alerts & Log Files From:', 'bulletproof-security'); ?> </label></td>
    <td><input type="text" name="bulletproof_security_options_email[bps_send_email_from]" class="regular-text-long-fixed" style="width:200px;" value="<?php if ( $options['bps_send_email_from'] != '' ) { echo $options['bps_send_email_from']; } else { echo $admin_email; } ?>" /></td>
  </tr>
  <tr>
    <td><label for="bps-monitor-email"><?php _e('Send Email Alerts & Log Files Cc:', 'bulletproof-security'); ?> </label></td>
    <td><input type="text" name="bulletproof_security_options_email[bps_send_email_cc]" class="regular-text-long-fixed" style="width:200px;" value="<?php echo $options['bps_send_email_cc']; ?>" /></td>
  </tr>
  <tr>
    <td><label for="bps-monitor-email"><?php _e('Send Email Alerts & Log Files Bcc:', 'bulletproof-security'); ?> </label></td>
    <td><input type="text" name="bulletproof_security_options_email[bps_send_email_bcc]" class="regular-text-long-fixed" style="width:200px;" value="<?php echo $options['bps_send_email_bcc']; ?>" /></td>
  </tr>
</table>
<br />

<table border="0">
  <tr>
    <td><strong><label for="bps-monitor-email"><?php _e('Login Security: Send Login Security Email Alert When...', 'bulletproof-security'); ?></label></strong><br />
<select name="bulletproof_security_options_email[bps_login_security_email]" style="width:340px;">
<option value="lockoutOnly" <?php selected( $options['bps_login_security_email'], 'lockoutOnly'); ?>><?php _e('A User Account Is Locked Out', 'bulletproof-security'); ?></option>
<option value="adminLoginOnly" <?php selected( $options['bps_login_security_email'], 'adminLoginOnly'); ?>><?php _e('An Administrator Logs In', 'bulletproof-security'); ?></option>
<option value="adminLoginLock" <?php selected( $options['bps_login_security_email'], 'adminLoginLock'); ?>><?php _e('An Administrator Logs In & A User Account is Locked Out', 'bulletproof-security'); ?></option>
<option value="anyUserLoginLock" <?php selected( $options['bps_login_security_email'], 'anyUserLoginLock'); ?>><?php _e('Any User Logs In & A User Account is Locked Out', 'bulletproof-security'); ?></option>
<option value="no" <?php selected( $options['bps_login_security_email'], 'no'); ?>><?php _e('Do Not Send Email Alerts', 'bulletproof-security'); ?></option>
</select></td>
  </tr>
  <tr>
    <td style="padding-top:5px;"><strong><label for="bps-monitor-email-log"><?php _e('Security Log: Email/Delete Security Log File When...', 'bulletproof-security'); ?></label></strong><br />
<select name="bulletproof_security_options_email[bps_security_log_size]" style="width:80px;">
<option value="500KB" <?php selected( $options['bps_security_log_size'], '500KB' ); ?>><?php _e('500KB', 'bulletproof-security'); ?></option>
<option value="256KB" <?php selected( $options['bps_security_log_size'], '256KB'); ?>><?php _e('256KB', 'bulletproof-security'); ?></option>
<option value="1MB" <?php selected( $options['bps_security_log_size'], '1MB' ); ?>><?php _e('1MB', 'bulletproof-security'); ?></option>
</select>
<select name="bulletproof_security_options_email[bps_security_log_emailL]" style="width:255px;">
<option value="email" <?php selected( $options['bps_security_log_emailL'], 'email' ); ?>><?php _e('Email Log & Then Delete Log File', 'bulletproof-security'); ?></option>
<option value="delete" <?php selected( $options['bps_security_log_emailL'], 'delete' ); ?>><?php _e('Delete Log File', 'bulletproof-security'); ?></option>
</select></td>
  </tr>
  <tr>
    <td style="padding-top:5px;"><strong><label for="bps-monitor-email-log"><?php _e('DB Backup Log: Email/Delete DB Backup Log File When...', 'bulletproof-security'); ?></label></strong><br />
<select name="bulletproof_security_options_email[bps_dbb_log_size]" style="width:80px;">
<option value="500KB" <?php selected( $options['bps_dbb_log_size'], '500KB' ); ?>><?php _e('500KB', 'bulletproof-security'); ?></option>
<option value="256KB" <?php selected( $options['bps_dbb_log_size'], '256KB'); ?>><?php _e('256KB', 'bulletproof-security'); ?></option>
<option value="1MB" <?php selected( $options['bps_dbb_log_size'], '1MB' ); ?>><?php _e('1MB', 'bulletproof-security'); ?></option>
</select>
<select name="bulletproof_security_options_email[bps_dbb_log_email]" style="width:255px;">
<option value="email" <?php selected( $options['bps_dbb_log_email'], 'email' ); ?>><?php _e('Email Log & Then Delete Log File', 'bulletproof-security'); ?></option>
<option value="delete" <?php selected( $options['bps_dbb_log_email'], 'delete' ); ?>><?php _e('Delete Log File', 'bulletproof-security'); ?></option>
</select></td>
  </tr>
</table>

<!-- <strong><label for="bps-monitor-email" style="margin:0px 0px 0px 0px;"><?php //_e('BPS Plugin Upgrade Email Notification', 'bulletproof-security'); ?></label></strong><br />
<select name="bulletproof_security_options_email[bps_upgrade_email]" style="width:340px;">
<option value="yes" <?php //selected( @$options['bps_upgrade_email'], 'yes'); ?>><?php //_e('Send Email Alerts', 'bulletproof-security'); ?></option>
<option value="no" <?php //selected( @$options['bps_upgrade_email'], 'no'); ?>><?php //_e('Do Not Send Email Alerts', 'bulletproof-security'); ?></option>
</select><br /><br /> -->

<input type="hidden" name="bpsEMA" value="bps-EMA" />
<input type="submit" name="bpsEmailAlertSubmit" class="bps-blue-button" style="margin:15px 0px 0px 0px;" value="<?php esc_attr_e('Save Options', 'bulletproof-security') ?>" />
</form>
</div>

<?php
if ( isset( $_POST['Submit-Delete-DBB-Log'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_delete_dbb_log' );

	$DBBLog = WP_CONTENT_DIR . '/bps-backup/logs/db_backup_log.txt';
	$DBBLogMaster = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/db_backup_log.txt';
	
	copy($DBBLogMaster, $DBBLog);
		echo $bps_topDiv;
		$text = '<font color="green"><strong>'.__('Success! Your DB Backup Log file has been deleted and replaced with a new blank DB Backup Log file.', 'bulletproof-security').'</strong></font>';
		echo $text;	
		echo $bps_bottomDiv;
}
?>

<div id="DBBLogDelete" style="margin:0px 0px 10px 600px;">
<form name="DeleteDBBLogForm" action="admin.php?page=bulletproof-security/admin/db-backup-security/db-backup-security.php#bps-tabs-2" method="post">
<?php wp_nonce_field('bulletproof_security_delete_dbb_log'); ?>
<input type="submit" name="Submit-Delete-DBB-Log" value="<?php esc_attr_e('Delete Log', 'bulletproof-security') ?>" class="bps-blue-button" onclick="return confirm('<?php $text = __('Clicking OK will delete the contents of your DB Backup Log file.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Click OK to Delete the Log file contents or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
</form>
</div>

<div id="messageinner" class="updatedinner" style="width:690px;">
<?php

// Get DB Backup log file contents
function bpsPro_DBB_get_contents() {
	
	if ( current_user_can('manage_options') ) {
$dbb_log = WP_CONTENT_DIR . '/bps-backup/logs/db_backup_log.txt';
$bps_wpcontent_dir = str_replace( ABSPATH, '', WP_CONTENT_DIR );

	if ( file_exists($dbb_log) ) {
		$dbb_log = file_get_contents($dbb_log);
		return htmlspecialchars($dbb_log);
	
	} else {
	
	_e('The DB Backup Log File Was Not Found! Check that the file really exists here - /', 'bulletproof-security').$bps_wpcontent_dir.__('/bps-backup/logs/db_backup_log.txt and is named correctly.', 'bulletproof-security');
	}
	}
}

// Form: DB Backup Log editor
if ( current_user_can('manage_options') ) {
$dbb_log = WP_CONTENT_DIR . '/bps-backup/logs/db_backup_log.txt';
$write_test = "";
	
	if ( is_writable($dbb_log) ) {
    if ( !$handle = fopen($dbb_log, 'a+b' ) ) {
    exit;
    }
    
	if ( fwrite($handle, $write_test) === FALSE ) {
	exit;
    }
	
	$text = '<font color="green"><strong>'.__('File Open and Write test successful! Your DB Backup Log file is writable.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
	}
	
	if ( isset( $_POST['Submit-DBB-Log'] ) && current_user_can('manage_options') ) {
		check_admin_referer( 'bulletproof_security_save_dbb_log' );
		$newcontentdbb = stripslashes( $_POST['newcontentdbb'] );
	
	if ( is_writable($dbb_log) ) {
		$handle = fopen($dbb_log, 'w+b');
		fwrite($handle, $newcontentdbb);
	$text = '<font color="green"><strong>'.__('Success! Your DB Backup Log file has been updated.', 'bulletproof-security').'</strong></font><br>';
	echo $text;	
    fclose($handle);
	}
}

$scrolltodbblog = isset($_REQUEST['scrolltodbblog']) ? (int) $_REQUEST['scrolltodbblog'] : 0;
?>
</div>

<div id="QLogEditor">
<form name="DBBLog" id="DBBLog" action="admin.php?page=bulletproof-security/admin/db-backup-security/db-backup-security.php#bps-tabs-2" method="post">
<?php wp_nonce_field('bulletproof_security_save_dbb_log'); ?>
<div id="DBBLog">
    <textarea class="bps-text-area-600x700" name="newcontentdbb" id="newcontentdbb" tabindex="1"><?php echo bpsPro_DBB_get_contents(); ?></textarea>
	<input type="hidden" name="scrolltodbblog" id="scrolltodbblog" value="<?php echo esc_html($scrolltodbblog); ?>" />
    <p class="submit">
	<input type="submit" name="Submit-DBB-Log" class="bps-blue-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
</div>
</form>

<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#DBBLog').submit(function(){ $('#scrolltodbblog').val( $('#newcontentdbb').scrollTop() ); });
	$('#newcontentdbb').scrollTop( $('#scrolltodbblog').val() ); 
});
/* ]]> */
</script>
</div>

</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>

</div>

<div id="bps-tabs-3" class="bps-tab-page">
<h2><?php _e('DB Table Prefix Changer', 'bulletproof-security'); ?></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td colspan="2" class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td width="50%" valign="top" class="bps-table_cell_help">

<h3><?php _e('DB Table Prefix Changer', 'bulletproof-security'); ?>  <button id="bps-open-modal3" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
<div id="bps-modal-content3" title="<?php _e('DB Table Prefix Changer', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable (top) and resizable (bottom right corner)', 'bulletproof-security').'</strong><br><br><strong>'.__('Safety Precautions & Procedures', 'bulletproof-security').'</strong><br>'.__('Changing the DB Table Prefix name is a very simple thing to automate. This tool has been extensively tested and is safe and reliable, but anytime you are modifying your database you should ALWAYS perform a database backup as a safety precaution.', 'bulletproof-security').'<br><br><strong>'.__('Compatibility', 'bulletproof-security').'</strong><br>'.__('Works on all WordPress, BuddyPress and bbPress site types: Single standard WordPress installations and Network/Multisite installations.', 'bulletproof-security').'<br><br><strong><font color="blue">'.__('Note: The DB Table Names & Character Length Table needs to be a clickable Form button and is not displayed permanently open because that would cause the entire DB Backup & Security page (all Tab pages) to perform poorly/sluggishly on large websites.', 'bulletproof-security').'</font></strong><br><br><strong>'.__('Other Prefix Changes Explained', 'bulletproof-security').'</strong><br>'.__('In your WordPress xxxxxx_options DB Table there is 1 value that will be changed in the option_name Column: xxxxxx_user_roles. In your WordPress xxxxxx_usermeta DB Table there are several values that will be changed in the meta_key Column. These are user/user ID specific values based on individual user\'s Metadata stored in the xxxxxx_usermeta DB Table. Metadata is user specific saved settings, such as individual user\'s capabilities, permissions, saved screen options settings, etc.', 'bulletproof-security').'<br><br><strong>'.__('Security measure vs Anti-nuisance measure', 'bulletproof-security').'</strong><br>'.__('By changing your Database Table Prefix name you will probably stop a lot of random Bot probes from doing any further reconnaissance against your website and causing unnecessary slowness from those random Bot probes. Changing the DB Table Prefix name is not really a security measure since if a hacker wants to find/get your DB Table Prefix name he/she will be able to find/get that information. The Anti-nuisance benefits alone are worth changing your DB Table Prefix name.', 'bulletproof-security').'<br><br><strong>'.__('Correct Usage & Technical Info.', 'bulletproof-security').'</strong><br>'.__('If you want to create your own DB Table Prefix name or add additional characters to the randomly generated DB Table Prefix name then ONLY use lowercase letters, numbers and underscores in your DB Table Prefix name. The standard MySQL DB Table naming convention is xxxxxx_ where the x\'s should be ONLY lowercase letters and/or numbers and the DB Table Prefix name should end with an underscore.', 'bulletproof-security').'<br><br>'.__('The maximum length limitation of a DB Table name, including the table prefix is 64 characters. See the DB Table Names & Character Length Table for character lengths of your database table names.', 'bulletproof-security').'<br><br>'.__('If a plugin or theme is using "wp_" in its DB Table naming conventions, example: wp_wp_some_plugin_table_name, then the DB Table Prefix Changer tool will NOT change anything besides the first wp_ in the DB Table name - The DB Table Prefix Change will ONLY change the actual start/prefix of a DB Table name.', 'bulletproof-security').'<br><br>'.__('To change your DB Table Prefix name back to the WordPress default DB Table Prefix name, enter wp_ for the DB Table Prefix name.', 'bulletproof-security'); echo $text; ?></p>
</div>

<?php
	if ( is_admin() && wp_script_is( 'bps-js', $list = 'queue' ) && current_user_can('manage_options') && preg_match( '/page=bulletproof-security/', $_SERVER['REQUEST_URI'], $matches) ) {	

echo '<div id="DBPrefixText" style="width:90%;padding-bottom:20px;">';
$text = '<span style="font-size:1.13em;">'.__('Your current WordPress Database Table Prefix is: ', 'bulletproof-security').'<strong><font color="blue">'.$wpdb->base_prefix .'</span><br><br>'.__('NOTES: ', 'bulletproof-security').'<br>'.__('1. It is recommended that you backup your database before using this tool.', 'bulletproof-security').'<br>'.__('2. If you want to create your own DB Table Prefix name or add additional characters to the randomly generated DB Table Prefix name below then ONLY use lowercase letters, numbers and underscores in your DB Table Prefix name.', 'bulletproof-security').'<br>'.__('3. The maximum length limitation of a DB Table name, including the table prefix is 64 characters. See the DB Table Names & Character Length Table to the right.', 'bulletproof-security').'<br>'.__('4. To change your DB Table Prefix name back to the WordPress default DB Table Prefix name, enter wp_ for the DB Table Prefix name.', 'bulletproof-security').'</font></strong>';
echo $text;
echo '</div>';

if ( isset( $_POST['Submit-DB-Table-Prefix'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_table_prefix_changer' );
	set_time_limit(300);

	$DBTablePrefix = $_POST['DBTablePrefix'];
	$wpconfig_file = ABSPATH . 'wp-config.php';
	
	if ( !file_exists($wpconfig_file) ) {
		echo '<div id="message" class="updated" style="border:1px solid #999999;margin-left:70px;background-color:#ffffe0;"><p>';
		$text = '<strong><font color="red">'.__('A wp-config.php file was NOT found in your website root folder.', 'bulletproof-security').'</font><br>'.__('Your DB Table Prefix was not changed. If you have moved your wp-config.php file to a another Server folder then you can use this tool to change your DB Table Prefix, but first you will need to temporarily move your wp-config.php file back to the default location: your WordPress website root folder.', 'bulletproof-security').'</strong>';
		echo $text;
		echo '</p></div>';
	}

	if ( file_exists($wpconfig_file) ) {

		$permswpconfig = @substr(sprintf('%o', fileperms($wpconfig_file)), -4);
		$sapi_type = php_sapi_name();
	
		if 	( @$permswpconfig == '0400') {
			$lock = '0400';			
		}

		if ( @substr( $sapi_type, 0, 6 ) != 'apache' || @$permswpconfig != '0666' || @$permswpconfig != '0777' ) { // Windows IIS, XAMPP, etc
			@chmod($wpconfig_file, 0644);
		}

	if ( !is_writable($wpconfig_file) ) {
		echo '<div id="message" class="updated" style="border:1px solid #999999;margin-left:70px;background-color:#ffffe0;"><p>';
		$text = '<strong><font color="red">'.__('Error: The wp-config.php file is not writable. Unable to write to the wp-config.php file.', 'bulletproof-security').'</font><br>'.__('Your DB Table Prefix was not changed. You will need to make the wp-config.php file writable first by changing either the file permissions or Ownership of the wp-config.php file (if you have a DSO Server) before you can use the DB Table Prefix Changer tool to change your DB Table Prefix.', 'bulletproof-security').'</strong>';
		echo $text;
		echo '</p></div>';
	return;
	}

	$base_prefix = $wpdb->base_prefix;
	$MetaKeys = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->usermeta WHERE meta_key LIKE %s", "$base_prefix%" ) );
	$userRoles = '_user_roles';
	$UserRolesRows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s", "%$userRoles" ) );
	$DBTables = 0;
	$getDBTables = $wpdb->get_results( $wpdb->prepare( "SHOW TABLE STATUS WHERE Rows >= %d", $DBTables ) );
	
	foreach ( $getDBTables as $Table ) {

		$new_table_name = preg_replace( "/^$wpdb->base_prefix/", $DBTablePrefix, $Table->Name );
		$rename_table = $wpdb->query( "RENAME TABLE $Table->Name TO $new_table_name" );
	}
			
	foreach ( $UserRolesRows as $data ) {

		$update_user_roles = $wpdb->update( $DBTablePrefix . 'options', array( 'option_name' => $DBTablePrefix . 'user_roles' ), array( 'option_name' => $data->option_name ) );
	}

	if ( is_multisite() ) {
		
		$network_ids = wp_get_sites();

		foreach ( $network_ids as $key => $value ) {
			
			$net_id = $value['blog_id'];
			
			if ( $net_id != '1' ) {
			
			$network_update_user_roles = $wpdb->update( $DBTablePrefix . $net_id . '_options', array( 'option_name' => $DBTablePrefix . $net_id . '_user_roles' ), array( 'option_name' => $wpdb->base_prefix . $net_id . '_user_roles' ) );	
	
			}
		}
	}	
	
	foreach ( $MetaKeys as $mdata ) {

		$new_meta_key = preg_replace( "/^$wpdb->base_prefix/", $DBTablePrefix, $mdata->meta_key );
		$update_meta_keys = $wpdb->update( $DBTablePrefix . 'usermeta', array( 'meta_key' => $new_meta_key ), array( 'meta_key' => $mdata->meta_key ) );
	}

	$contents = file_get_contents($wpconfig_file);
	$pattern = '/\$table_prefix(.*)=(.*);/';
	$wpconfigARQ = WP_CONTENT_DIR . '/bps-backup/autorestore/root-files/wp-config.php';

		$stringReplace = @file_get_contents($wpconfig_file);
		
		if ( preg_match( $pattern, $contents, $matches ) ) {
			
			$stringReplace = preg_replace('/\$table_prefix(.*)=(.*);/', "\$table_prefix = '$DBTablePrefix';", $stringReplace);
		
		}	
	
		if ( file_put_contents( $wpconfig_file, $stringReplace ) ) {
		
			if ( $lock == '0400' ) {	
				@chmod($wpconfig_file, 0400);
			}
		
		@copy($wpconfig_file, $wpconfigARQ);	
		}
			
			echo '<div id="message" class="updated" style="border:1px solid #999999;margin-left:70px;background-color:#ffffe0;"><p>';
			$text = '<font color="green"><strong>'.__('DB Table Prefix Name change completed. Click the Load/Refresh Table button to load/refresh the DB Table Names & Character Length Table.', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo '</p></div>';
	
	} // end if ( file_exists($filename) ) {
}

	// Random DB Table Prefix Name generator
	$str = '1234567890abcdefghijklmnopqrstuvxyz';
	$prefix_obs = substr( str_shuffle($str), 0, 6 ).'_';
	$DBTablePrefix = ( isset( $_POST['DBTablePrefix'] ) ) ? $_POST['DBTablePrefix'] : $prefix_obs;

?>

<form name="bpsTablePrefixChanger" action="admin.php?page=bulletproof-security/admin/db-backup-security/db-backup-security.php#bps-tabs-3" method="post">
<?php wp_nonce_field('bulletproof_security_table_prefix_changer'); ?>
<div>
<strong><label for="bpsTablePrefix"><?php _e('Randomly Generated DB Table Prefix', 'bulletproof-security'); ?></label></strong><br />  
<input type="text" style="width:215px;" name="DBTablePrefix" value="<?php echo $DBTablePrefix; ?>" class="regular-text-short-fixed" /> <br />
<p class="submit">
<input type="submit" name="Submit-DB-Table-Prefix" value="<?php esc_attr_e('Change DB Table Prefix', 'bulletproof-security') ?>" class="bps-blue-button" onclick="return confirm('<?php $text = __('Clicking OK will change your DB Table Prefix name.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Click OK to Change your DB Table Prefix name or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
</p>
</div>
</form>

</td>
    <td width="50%" valign="top" class="bps-table_cell_help">

<?php

function bpsPro_table_status_length() {
global $wpdb;
	
	if ( isset( $_POST['Submit-DB-Prefix-Table-Refresh'] ) && current_user_can('manage_options') ) {
		check_admin_referer( 'bulletproof_security_db_prefix_refresh' );

	$base_prefix = $wpdb->base_prefix;
	$DBTables = 0;
	$getDBTables = $wpdb->get_results( $wpdb->prepare( "SHOW TABLE STATUS WHERE Rows >= %d", $DBTables ) );

	echo '<div id="DBPrefixStatus1" style="margin:0px 0px 20px 0px;overflow:auto;width:100%;height:200px;border:1px solid black;">';
	echo '<table style="text-align:left;border-right:1px solid black;padding:5px;">';
	echo '<thead>';
	echo '<tr>';
	echo '<th scope="col" style="width:250px;font-size:1.13em;border-bottom:1px solid black;background-color:transparent;"><strong>'.__('DB Table Name', 'bulletproof-security').'</strong></th>';
	echo '<th scope="col" style="width:400px;font-size:1.13em;border-bottom:1px solid black;background-color:transparent;"><strong>'.__('Length', 'bulletproof-security').'</strong></th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	echo '<tr>';

	foreach ( $getDBTables as $Tabledata ) {

	echo '<td>'.$Tabledata->Name.'</td>';
	echo '<td>'.strlen($Tabledata->Name).'</td>';
	echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';
	echo '</div>';

	$userRoles = '_user_roles';
	$UserRolesRows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s", "%$userRoles" ) );
	$MetaKeys = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->usermeta WHERE meta_key LIKE %s", "$base_prefix%" ) );	

	echo '<div id="DBPrefixStatus2" style="margin:0px 0px 20px 0px;overflow:auto;width:100%;height:200px;border:1px solid black;">';
	echo '<table style="text-align:left;border-right:1px solid black;padding:5px;">';
	echo '<thead>';
	echo '<tr>';
	echo '<th scope="col" style="width:250px;font-size:1.13em;border-bottom:1px solid black;background-color:transparent;"><strong>'.__('DB Table Name / Column', 'bulletproof-security').'</strong></th>';
	echo '<th scope="col" style="width:400px;font-size:1.13em;border-bottom:1px solid black;background-color:transparent;"><strong>'.__('Other Prefix Changes', 'bulletproof-security').'</strong></th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	echo '<tr>';

	foreach ( $UserRolesRows as $data ) {

	echo '<td>'.$wpdb->options.' / option_name</td>';
	echo '<td>'.$data->option_name.'</td>';
	echo '</tr>';
	}
	
	if ( is_multisite() ) {
		
	echo '<tr>';
	
	$network_ids = wp_get_sites();

	foreach ( $network_ids as $key => $value ) {
			
		$net_id = $value['blog_id'];
			
		if ( $net_id != '1' ) {
			
			$network_options_tables = $base_prefix . $net_id . '_options';
			
			$NetUserRolesRows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $network_options_tables WHERE option_name LIKE %s", "%$userRoles" ) );

	echo '<td>'.$network_options_tables.' / option_name</td>';
	echo '<td>'.$base_prefix . $net_id . '_user_roles'.'</td>';
	echo '</tr>';
			
			}
		}
	}

	echo '<tr>';
	
	foreach ( $MetaKeys as $mdata ) {
	
	if ( preg_match( "/^$wpdb->base_prefix/", $mdata->meta_key, $matches ) ) {
	
	echo '<td>'.$wpdb->usermeta.' / meta_key</td>';
	echo '<td>'.'User ID: '.$mdata->user_id.' '.$mdata->meta_key.'</td>';
	echo '</tr>';
	}
	}
	
	echo '</tbody>';
	echo '</table>';
	echo '</div>';
	}
}

	// Form: DB Table Names & Character Length Table - needs to be a clickable form otherwise causes slowness on large websites if query is running
	echo '<div id="DB-Prefix-Table-Refresh-Button" style="margin:0px 0px 20px 0px;">';
	echo '<h3>'.__('DB Table Names & Character Length Table', 'bulletproof-security').'</h3>';
	echo '<h4><font color="blue">'.__('Displays your Current DB Table Names & Length Including The DB Table Prefix', 'bulletproof-security').'</font></h4>';
	echo '<form name="DB-Prefix-Table-Refresh" action="admin.php?page=bulletproof-security/admin/db-backup-security/db-backup-security.php#bps-tabs-3" method="post">';
	wp_nonce_field('bulletproof_security_db_prefix_refresh');
	echo "<p><input type=\"submit\" name=\"Submit-DB-Prefix-Table-Refresh\" value=\"".__('Load/Refresh Table', 'bulletproof-security')."\" class=\"bps-blue-button\" /></p>";
	bpsPro_table_status_length();
	echo "</form>";
	echo '</div>';
	
}// end if ( is_admin() && wp_script_is( 'bps-js', $list = 'queue' )...	
?>

    </td>
  </tr>
  <tr>
    <td colspan="2" class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>

</div>

<div id="bps-tabs-4" class="bps-tab-page">
<h2><?php _e('Help &amp; FAQ', 'bulletproof-security'); ?></h2>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td colspan="2" class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help"><a href="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-10" target="_blank"><?php _e('Whats New in ', 'bulletproof-security'); echo BULLETPROOF_VERSION; ?></a></td>
    <td class="bps-table_cell_help"><a href="http://forum.ait-pro.com/forums/topic/bulletproof-security-pro-version-release-dates/" target="_blank"><?php _e('BPS Pro Features & Version Release Dates', 'bulletproof-security'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help"><a href="http://forum.ait-pro.com/video-tutorials/" target="_blank"><?php _e('Video Tutorials', 'bulletproof-security'); ?></a></td>
    <td class="bps-table_cell_help"><a href="http://forum.ait-pro.com/forums/topic/database-backup-security-guide/" target="_blank"><?php _e('DB Backup & Security Guide & Troubleshooting', 'bulletproof-security'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help"><a href="http://forum.ait-pro.com/forums/topic/plugin-conflicts-actively-blocked-plugins-plugin-compatibility/" target="_blank"><?php _e('Forum: Search, Troubleshooting Steps & Post Questions For Assistance', 'bulletproof-security'); ?></a></td>
    <td class="bps-table_cell_help">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">&nbsp;</td>
    <td class="bps-table_cell_help">&nbsp;</td>
  </tr>
   <tr>
    <td colspan="2" class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
</div>
            
<div id="AITpro-link">BulletProof Security Pro <?php echo BULLETPROOF_VERSION; ?> Plugin by <a href="http://forum.ait-pro.com/" target="_blank" title="AITpro Website Security">AITpro Website Security</a>
</div>
</div>
</div>
</div>