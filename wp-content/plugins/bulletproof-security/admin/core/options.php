<?php
// Direct calls to this file are Forbidden when core files are not present
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

<h2 style="margin-left:70px;"><?php _e('BulletProof Security ~ htaccess Core', 'bulletproof-security'); ?></h2>
<div id="message" class="updated" style="border:1px solid #999999;margin-left:70px;background-color:#000;">

<?php
// HUD - Heads Up Display - Warnings and Error messages
echo bps_check_php_version_error();
echo bps_hud_check_bpsbackup();
echo bps_check_safemode();
echo @bps_w3tc_htaccess_check($plugin_var);
echo @bps_wpsc_htaccess_check($plugin_var);
bps_delete_language_files();

// default.htaccess, secure.htaccess, fwrite content for all WP site types
$bps_get_domain_root = bpsGetDomainRoot();
$bps_get_wp_root_default = bps_wp_get_root_folder();
// Replace ABSPATH = wp-content/plugins
$bps_plugin_dir = str_replace( ABSPATH, '', WP_PLUGIN_DIR);
// Replace ABSPATH = wp-content
$bps_wpcontent_dir = str_replace( ABSPATH, '', WP_CONTENT_DIR);
// Replace ABSPATH = wp-content/uploads
$wp_upload_dir = wp_upload_dir();
$bps_uploads_dir = str_replace( ABSPATH, '', $wp_upload_dir['basedir'] );
$bps_topDiv = '<div id="message" class="updated" style="background-color:#ffffe0;font-size:1em;font-weight:bold;border:1px solid #999999; margin-left:70px;"><p>';
$bps_bottomDiv = '</p></div>';

// Form - copy and rename htaccess files to root folder
// Root BulletProof Mode and Default Mode
$bpsecureroot = 'unchecked';
$bpdefaultroot = 'unchecked';
if ( isset( $_POST['submit12'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_root_copy' );
	
	$options = get_option('bulletproof_security_options_autolock');
	$DefaultHtaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/default.htaccess';
	$RootHtaccess = ABSPATH . '.htaccess';
	$SecureHtaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/secure.htaccess';
	$permsRootHtaccess = @substr(sprintf('%o', fileperms($RootHtaccess)), -4);
	$sapi_type = php_sapi_name();	

	$selected_radio = $_POST['selection12'];
	
	if ( $selected_radio == 'bpsecureroot' ) {
		$bpsecureroot = 'checked';
		
		if ( @substr($sapi_type, 0, 6) != 'apache' && @$permsRootHtaccess != '0666' || @$permsRootHtaccess != '0777') { // Windows IIS, XAMPP, etc
			@chmod($RootHtaccess, 0644);
		}		
		
		if ( !copy($SecureHtaccess, $RootHtaccess) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to Activate BulletProof Security Root Folder Protection! Your Website is NOT protected with BulletProof Security!', 'bulletproof-security').'</strong></font>';
			echo $text;
   			echo $bps_bottomDiv;
		
		} else {
			
			if ( @$permsRootHtaccess == '0644' && @substr($sapi_type, 0, 6) != 'apache' && $options['bps_root_htaccess_autolock'] != 'Off') {			
				@chmod($RootHtaccess, 0404);
			}
			
			echo $bps_topDiv;
			$text = '<font color="green"><strong>'.__('BulletProof Security Root Folder Protection Activated. Your website Root folder is now protected with BulletProof Security.', 'bulletproof-security').'</strong></font><br><font color="red"><strong>'.__('IMPORTANT!', 'bulletproof-security').'</strong></font><strong> '.__('BulletProof Mode for the wp-admin folder MUST also be activated when you have BulletProof Mode activated for the Root folder.', 'bulletproof-security').'</strong>';
			echo $text;
    		echo $bps_bottomDiv;

			/*
			if ( is_multisite() ) {
			
			$Net_options = get_option('bulletproof_security_options_net_correction');  
			$bps_netcorrect_options = 'bulletproof_security_options_net_correction';
			
			$bps_net_automagic = ! $Net_options['bps_net_automagic'] ? '' : 'automagic';
			$bps_net_activated = ! $Net_options['bps_net_activated'] ? 'activated' : 'activated';

			$NC_Options = array( 
			'bps_net_automagic' => $bps_net_automagic, 
			'bps_net_activated' => $bps_net_activated
			);
	
				foreach( $NC_Options as $key => $value ) {
					update_option('bulletproof_security_options_net_correction', $NC_Options);
				}	
			}
			*/
		}
	}
	elseif ( $selected_radio == 'bpdefaultroot' ) {
		$bpdefaultroot = 'checked';

		if ( @substr($sapi_type, 0, 6) != 'apache' || @$permsRootHtaccess != '0666' || @$permsRootHtaccess != '0777') { // Windows IIS, XAMPP, etc
			@chmod($RootHtaccess, 0644);
		}

		if ( !copy($DefaultHtaccess, $RootHtaccess) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to Activate Default htaccess Mode!', 'bulletproof-security').'</strong></font>';
			echo $text;
   			echo $bps_bottomDiv;
		
		} else {

		if ( @$permsRootHtaccess == '0644' && @substr($sapi_type, 0, 6) != 'apache' && $options['bps_root_htaccess_autolock'] != 'Off') {
				@chmod($RootHtaccess, 0404);
			}
			
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Warning: Default htaccess Mode Is Activated In Your Website Root Folder. Your Website Is Not Protected With BulletProof Security.', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo $bps_bottomDiv;
		}
	}
}

// Form - copy and rename htaccess file to wp-admin folder
// Do String Replacements for Custom Code AFTER new .htaccess file has been copied to wp-admin
$bpsecurewpadmin = 'unchecked';
$Removebpsecurewpadmin = 'unchecked';
if ( isset( $_POST['submit13'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_wpadmin_copy' );
	
	$BPS_wpadmin_Options = get_option('bulletproof_security_options_htaccess_res');

	if ( $BPS_wpadmin_Options['bps_wpadmin_restriction'] == 'disabled' ) {
		echo $bps_topDiv;
		$text = '<font color="red"><strong>'.__('wp-admin BulletProof Mode was not activated. wp-admin BulletProof Mode is disabled on the Security Modes page.', 'bulletproof-security').'</strong></font>';
		echo $text;
   		echo $bps_bottomDiv;		
	return;
	}

	$options = get_option('bulletproof_security_options_customcode_WPA');  
	
	$HtaccessMaster = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
	$wpadminHtaccess = ABSPATH . 'wp-admin/.htaccess';
	$permsHtaccess = @substr(sprintf('%o', fileperms($wpadminHtaccess)), -4);
	$sapi_type = php_sapi_name();	
	$bpsString1 = "# CCWTOP";
	$bpsString2 = "# CCWPF";
	$bpsString3 = '/#\sBEGIN\sBPS\sWPADMIN\sDENY\sACCESS\sTO\sFILES(.*)#\sEND\sBPS\sWPADMIN\sDENY\sACCESS\sTO\sFILES/s';
	$bpsString4 = '/#\sBEGIN\sBPSQSE-check\sBPS\sQUERY\sSTRING\sEXPLOITS\sAND\sFILTERS(.*)#\sEND\sBPSQSE-check\sBPS\sQUERY\sSTRING\sEXPLOITS\sAND\sFILTERS/s';
	$bpsReplace1 = htmlspecialchars_decode($options['bps_customcode_one_wpa'], ENT_QUOTES);
	$bpsReplace2 = htmlspecialchars_decode($options['bps_customcode_two_wpa'], ENT_QUOTES);
	$bpsReplace3 = htmlspecialchars_decode($options['bps_customcode_deny_files_wpa'], ENT_QUOTES);	
	$bpsReplace4 = htmlspecialchars_decode($options['bps_customcode_bpsqse_wpa'], ENT_QUOTES);	
	
	$selected_radio = $_POST['selection13'];
	
	if ( $selected_radio == 'bpsecurewpadmin' ) {
		$bpsecurewpadmin = 'checked';

		if ( @substr($sapi_type, 0, 6) != 'apache' || @$permsHtaccess != '0666' || @$permsHtaccess != '0777' ) { // Windows IIS, XAMPP, etc
			@chmod($wpadminHtaccess, 0644);
		}		

		if ( !copy($HtaccessMaster, $wpadminHtaccess) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to Activate BulletProof Security wp-admin Folder Protection! Your wp-admin folder is NOT protected with BulletProof Security!', 'bulletproof-security').'</strong></font>';
			echo $text;
   			echo $bps_bottomDiv;
		
		} else {
	
			if ( file_exists($wpadminHtaccess) ) {
				
				if ( @$permsHtaccess != '0666' || @$permsHtaccess != '0777') { // Windows IIS, XAMPP, etc
					@chmod($wpadminHtaccess, 0644);
				}				
				
				$bpsBaseContent = @file_get_contents($wpadminHtaccess);
		
			if ( $options['bps_customcode_deny_files_wpa'] != '') {        
				$bpsBaseContent = preg_replace('/#\sBEGIN\sBPS\sWPADMIN\sDENY\sACCESS\sTO\sFILES(.*)#\sEND\sBPS\sWPADMIN\sDENY\sACCESS\sTO\sFILES/s', $bpsReplace3, $bpsBaseContent);
			}
			
			if ( $options['bps_customcode_bpsqse_wpa'] != '') {        
				$bpsBaseContent = preg_replace('/#\sBEGIN\sBPSQSE-check\sBPS\sQUERY\sSTRING\sEXPLOITS\sAND\sFILTERS(.*)#\sEND\sBPSQSE-check\sBPS\sQUERY\sSTRING\sEXPLOITS\sAND\sFILTERS/s', $bpsReplace4, $bpsBaseContent);
			}
				
				$bpsBaseContent = str_replace($bpsString1, $bpsReplace1, $bpsBaseContent);
				$bpsBaseContent = str_replace($bpsString2, $bpsReplace2, $bpsBaseContent);
				@file_put_contents($wpadminHtaccess, $bpsBaseContent);

				echo $bps_topDiv;
				$text = '<font color="green"><strong>'.__('BulletProof Security wp-admin Folder Protection Activated. Your wp-admin folder is now protected with BulletProof Security.', 'bulletproof-security').'</strong></font>';
				echo $text;
				echo $bps_bottomDiv;
			}
		}
	}
	elseif ( $selected_radio == 'Removebpsecurewpadmin' ) {
		$Removebpsecurewpadmin = 'checked';
		$fh = fopen($wpadminHtaccess, 'a');
		fwrite($fh, 'delete');
		fclose($fh);
		@unlink($wpadminHtaccess);
	
	if ( file_exists($wpadminHtaccess) ) {
		echo $bps_topDiv;
		$text = '<font color="red"><strong>'.__('Failed to Delete the wp-admin htaccess file! The file does not exist. It may have been deleted or renamed already.', 'bulletproof-security').'</strong></font>';
		echo $text;
   		echo $bps_bottomDiv;
	
	} else {
		
		echo $bps_topDiv;
		$text = '<font color="green"><strong>'.__('The wp-admin htaccess file has been Deleted. ', 'bulletproof-security').'</strong></font><font color="red"><strong>'.__('Your wp-admin folder is no longer htaccess protected. ', 'bulletproof-security').'</strong></font>'.__('If you are testing then be sure to reactivate BulletProof Mode for your wp-admin folder when you are done testing. If you are removing BPS from your website then be sure to also Activate Default Mode for your Root folder. The Root and wp-admin BulletProof Modes must be activated together or removed together.', 'bulletproof-security').'</strong></font>';
		echo $text;
		echo $bps_bottomDiv;
	}
	}
}

// Form rename Deny All htaccess file to .htaccess for the BPS Master htaccess folder
$bps_rename_htaccess_files = 'unchecked';
if ( isset( $_POST['submit8'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_denyall_master' );
	
	$bps_rename_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/deny-all.htaccess';
	$bps_rename_htaccess_renamed = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/.htaccess';
	
	$selected_radio = $_POST['selection8'];
	
	if ( $selected_radio == 'bps_rename_htaccess_files' ) {
		$bps_rename_htaccess_files = 'checked';

		if ( !copy($bps_rename_htaccess, $bps_rename_htaccess_renamed) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to Activate BulletProof Security Deny All Folder Protection! Your BPS Master htaccess folder is NOT Protected with Deny All htaccess folder protection!', 'bulletproof-security').'</strong></font>';
			echo $text;
   			echo $bps_bottomDiv;
			
		} else {
			
			echo $bps_topDiv;
			$text = __('BulletProof Security Deny All Folder Protection', 'bulletproof-security').'<font color="green"><strong> '.__('Activated.', 'bulletproof-security').' </strong></font>'.__('Your BPS Master htaccess folder is Now Protected with Deny All htaccess folder protection.', 'bulletproof-security');
			echo $text;
			echo $bps_bottomDiv;
		}
	}
}

// Form copy and rename the Deny All htaccess file to the BPS backup folder
$bps_rename_htaccess_files_backup = 'unchecked';
if ( isset( $_POST['submit14'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_denyall_bpsbackup' );
	
	$bps_rename_htaccess_backup = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/deny-all.htaccess';
	$bps_rename_htaccess_backup_online = WP_CONTENT_DIR . '/bps-backup/.htaccess';
	
	$selected_radio = $_POST['selection14'];
	
	if ( $selected_radio == 'bps_rename_htaccess_files_backup' ) {
		$bps_rename_htaccess_files_backup = 'checked';
		
		if ( !copy($bps_rename_htaccess_backup, $bps_rename_htaccess_backup_online) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to Activate BulletProof Security Deny All Folder Protection! Your BPS /', 'bulletproof-security').$bps_wpcontent_dir.__('/bps-backup folder is NOT Protected with Deny All htaccess folder protection!', 'bulletproof-security').'</strong></font>';
			echo $text;
   			echo $bps_bottomDiv;
			
		} else {
			
			echo $bps_topDiv;
			$text = __('BulletProof Security Deny All Folder Protection', 'bulletproof-security').'<font color="green"><strong> '.__('Activated.', 'bulletproof-security').' </strong></font>'.__('Your BPS /', 'bulletproof-security').$bps_wpcontent_dir.__('/bps-backup folder is Now Protected with Deny All htaccess folder protection.', 'bulletproof-security');
			echo $text;
			echo $bps_bottomDiv;
		}
	}
}

// Form - Backup and rename existing and / or currently active htaccess files from 
// the root and wpadmin folders to /wp-content/bps-backup
$backup_htaccess = 'unchecked';
if ( isset( $_POST['submit9'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_backup_active_htaccess_files' );
	
	$old_backroot = ABSPATH . '.htaccess';
	$new_backroot = WP_CONTENT_DIR . '/bps-backup/master-backups/root.htaccess';
	$old_backwpadmin = ABSPATH . 'wp-admin/.htaccess';
	$new_backwpadmin = WP_CONTENT_DIR . '/bps-backup/master-backups/wpadmin.htaccess';
	
	$selected_radio = $_POST['selection9'];
	
	if ( $selected_radio == 'backup_htaccess' ) {
		$backup_htaccess = 'checked';
		
		if ( !file_exists($old_backroot) ) { 
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('You do not currently have an .htaccess file in your Root folder to backup.', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo $bps_bottomDiv;
		
		} else {	
		
		if ( !copy($old_backroot, $new_backroot) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to Backup Your Root .htaccess File! File copy function failed. Check the folder permissions for the /', 'bulletproof-security').$bps_wpcontent_dir.__('/bps-backup folder. Folder permissions should be set to 755.', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo $bps_bottomDiv;
		
		} else {
			
			echo $bps_topDiv;
			$text = '<font color="green"><strong>'.__('Your currently active Root .htaccess file has been backed up successfully!', 'bulletproof-security').' </strong></font><br>'.__('Use the Restore feature to restore your htaccess files if you run into a problem at any time. If you make additional changes or install a plugin that writes to the htaccess files then back them up again. This will overwrite the currently backed up htaccess files. Please read the', 'bulletproof-security').' <font color="red"><strong> '.__('CAUTION:', 'bulletproof-security').' </strong></font>'.__('Read Me button on the Backup & Restore Page for more detailed information.', 'bulletproof-security');
			echo $text;
			echo $bps_bottomDiv;
		}
		}
		
		if ( !file_exists($old_backwpadmin)) { 
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('You do not currently have an htaccess file in your wp-admin folder to backup.', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo $bps_bottomDiv;
		
		} else {
		
		if ( !copy($old_backwpadmin, $new_backwpadmin) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to Backup Your wp-admin htaccess File! File copy function failed. Check the folder permissions for the /', 'bulletproof-security').$bps_wpcontent_dir.__('/bps-backup folder. Folder permissions should be set to 755.', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo $bps_bottomDiv;
		
		} else {
			
			echo $bps_topDiv;
			$text = '<font color="green"><strong>'.__('Your currently active wp-admin htaccess file has been backed up successfully!', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo $bps_bottomDiv;
		}
		}
	}
}

// Form - Restore backed up htaccess files
$restore_htaccess = 'unchecked';
if ( isset( $_POST['submit10'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_restore_active_htaccess_files' );
	
	$old_restoreroot = WP_CONTENT_DIR . '/bps-backup/master-backups/root.htaccess';
	$new_restoreroot = ABSPATH . '.htaccess';
	$old_restorewpadmin = WP_CONTENT_DIR . '/bps-backup/master-backups/wpadmin.htaccess';
	$new_restorewpadmin = ABSPATH . 'wp-admin/.htaccess';
	
	$selected_radio = $_POST['selection10'];
	
	if ( $selected_radio == 'restore_htaccess' ) {
		$restore_htaccess = 'checked';
		
		if ( file_exists($old_restoreroot) ) { 
		if ( !copy($old_restoreroot, $new_restoreroot) ) {
			echo $bps_topDiv;
			echo '<font color="red"><strong>'.__('Failed to Restore Your Root htaccess File! This is most likely because you DO NOT currently have a Backed up Root htaccess file.', 'bulletproof-security').'</strong></font>';
   			echo $bps_bottomDiv;
			
		} else {
			
			echo $bps_topDiv;
			$text = '<font color="green"><strong>'.__('Your Root htaccess file has been Restored successfully!', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo $bps_bottomDiv;
		}
		}
		
		if ( file_exists($old_restorewpadmin) ) { 	
		if ( !copy($old_restorewpadmin, $new_restorewpadmin) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to Restore Your wp-admin htaccess File! This is most likely because you DO NOT currently have a Backed up wp-admin htaccess file.', 'bulletproof-security').'</strong></font>';
			echo $text;
   			echo $bps_bottomDiv;
		
		} else {
			
			echo $bps_topDiv;
			$text = '<font color="green"><strong>'.__('Your wp-admin htaccess file has been Restored successfully!', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo $bps_bottomDiv;
		}
		}
	}
}

// General all purpose "Settings Saved." message for forms
if ( current_user_can('manage_options') && wp_script_is( 'bps-js', $list = 'queue' ) ) {
if ( @$_GET['settings-updated'] == true) {
	$text = '<p style="background-color:#ffffe0;font-size:1em;font-weight:bold;padding:5px;margin:0px;"><font color="green"><strong>'.__('Settings Saved', 'bulletproof-security').'</strong></font></p>';
	echo $text;
	}
}

require_once( WP_PLUGIN_DIR . '/bulletproof-security/admin/core/core-help-text.php' );
require_once( WP_PLUGIN_DIR . '/bulletproof-security/admin/core/core-htaccess-code.php' );

$bpsSpacePop = '-------------------------------------------------------------';

?>
</div>

<!-- jQuery UI Tabs Menu -->
<div id="bps-container">
	<div id="bps-tabs" class="bps-menu">
    <div id="bpsHead" style="position:relative; top:0px; left:0px;"><img src="<?php echo plugins_url('/bulletproof-security/admin/images/bps-security-shield.png'); ?>" style="float:left; padding:0px 8px 0px 0px; margin:-72px 0px 0px 0px;" /></div>
   
		<ul>
			<li><a href="#bps-tabs-1"><?php _e('Security Modes', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-2"><?php _e('Security Status', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-tabs-5"><?php _e('Backup &amp; Restore', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-6"><?php _e('htaccess File Editor', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-7"><?php _e('Custom Code', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-tabs-9"><?php _e('Help &amp; FAQ', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-tabs-10"><?php _e('Whats New', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-11"><?php _e('My Notes', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-12"><?php _e('BPS Pro Features', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-13"><?php _e('Website Scanner', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-14"><?php _e('Website SEO', 'bulletproof-security'); ?></a></li>
		</ul>
            
<div id="bps-tabs-1" class="bps-tab-page">
<h2><?php _e('BulletProof Security Modes', 'bulletproof-security'); ?></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">

<h3><?php _e('Setup Steps & AutoMagic - Create Your htaccess Master Files', 'bulletproof-security'); ?>  <button id="bps-open-modal1" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>

<div id="bps-modal-content1" title="<?php _e('Setup Steps & AutoMagic', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content1; ?></p>
</div>

<?php if ( !current_user_can('manage_options') ) { _e('Permission Denied', 'bulletproof-security'); } else { ?>

<table width="100%" border="0">
  <tr>
    <td>
    
    <?php echo bpsPro_site_type_automagic(); ?>
    
<?php if ( ! is_multisite() ) { ?>

    <form name="bps-auto-write-default" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_default'); ?>
<input type="hidden" name="filename" value="bps-auto-write-default_write" />
<div id="AutoMagic-buttons" style="margin:10px;">
<input type="submit" name="bps-auto-write-default" value="<?php _e('Create default.htaccess File', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Clicking OK will create a new customized default.htaccess Master file for your website.', 'bulletproof-security').'\n\n'.__('This is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.', 'bulletproof-security').'\n\n'.__('NOTE: Default Mode should ONLY be activated for Testing and Troubleshooting.', 'bulletproof-security').'\n\n'.__('Click OK to Create your new default.htaccess Master file or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
</div>
</form>

<form name="bps-auto-write-secure-root" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_secure_root'); ?>
<input type="hidden" name="filename" value="bps-auto-write-secure_write" />
<div id="AutoMagic-buttons" style="margin:10px;">
<input type="submit" name="bps-auto-write-secure-root" value="<?php _e('Create secure.htaccess File', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Clicking OK will create a new customized secure.htaccess Master file for your website.', 'bulletproof-security').'\n\n'.__('This is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.', 'bulletproof-security').'\n\n'.__('Click OK to Create your new secure.htaccess Master file or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
</div>
</form>

<?php } else { ?>

<form name="bps-auto-write-default-MUSDir" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_default_MUSDir'); ?>
<input type="hidden" name="filename" value="bps-auto-write-default_write-MUSDir" />
<div id="AutoMagic-buttons" style="margin:10px;">
<input type="submit" name="bps-auto-write-default-MUSDir" value="<?php _e('Create default.htaccess File', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Clicking OK will create a new customized default.htaccess Master file for your Network / Multisite website.', 'bulletproof-security').'\n\n'.__('This is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.', 'bulletproof-security').'\n\n'.__('NOTE: Default Mode should ONLY be activated for Testing and Troubleshooting.', 'bulletproof-security').'\n\n'.__('Click OK to Create your new default.htaccess Master file or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
</div>
</form>

<form name="bps-auto-write-secure-root-MUSDir" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_secure_root_MUSDir'); ?>
<input type="hidden" name="filename" value="bps-auto-write-secure_write_MUSDir" />
<div id="AutoMagic-buttons" style="margin:10px;">
<input type="submit" name="bps-auto-write-secure-root-MUSDir" value="<?php _e('Create secure.htaccess File', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Clicking OK will create a new customized secure.htaccess Master file for your Network / Multisite website.', 'bulletproof-security').'\n\n'.__('This is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.', 'bulletproof-security').'\n\n'.__('Click OK to Create your new secure.htaccess Master file or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
</div>
</form>

<?php } ?>

</td>
  </tr>
</table>

<?php } ?>

<?php if ( !current_user_can('manage_options') ) { _e('Permission Denied', 'bulletproof-security'); } else { ?>

    <h2><?php _e('Activate Security Modes', 'bulletproof-security'); ?></h2>
    <h3><?php _e('Activate Website Root Folder .htaccess Security Mode', 'bulletproof-security'); ?>  <button id="bps-open-modal2" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
    
<div id="bps-modal-content2" title="<?php _e('Activate Root Folder BulletProof Mode', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content2; ?></p>
</div>

<form name="BulletProof-Root" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_root_copy'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection12" type="radio" value="bpsecureroot" class="tog" <?php checked('', $bpsecureroot); ?> /> <?php $text = __('Root Folder', 'bulletproof-security').'<br>'.__('BulletProof Mode', 'bulletproof-security'); echo $text; ?></label></th>
	<td class="url-path"><?php echo get_site_url(); ?>/.htaccess<br /><?php $text = '<font color="green">'.__('Copies the file secure.htaccess to your root folder and renames the file name to just .htaccess', 'bulletproof-security').'</font>'; echo $text; ?></td>
   </tr>
   <tr>   
   <th><label><input name="selection12" type="radio" value="bpdefaultroot" class="tog" <?php checked('', $bpdefaultroot); ?> /><?php $text = '<font color="red">'.__('Default Mode', 'bulletproof-security').'<br>'.__('WP Default htaccess File', 'bulletproof-security').'</font>'; echo $text; ?></label></th>
	<td class="url-path"><?php echo get_site_url(); ?>/.htaccess<br /><?php $text = '<font color="red">'.__(' CAUTION: ', 'bulletproof-security').'</font>'.__('Your site will not be protected if you activate Default Mode. ONLY activate Default Mode for Testing and Troubleshooting.', 'bulletproof-security'); echo $text; ?></td>
   </tr>
</table>
<p class="submit">
<input type="submit" name="submit12" value="<?php esc_attr_e('Activate', 'bulletproof-security') ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Did you create your Master .htaccess files using the AutoMagic buttons?', 'bulletproof-security').'\n\n'.__('Did you backup your existing .htaccess files?', 'bulletproof-security').'\n\n'.__('Do you have any custom .htaccess code in your Root .htaccess file that you want to save before Activating BulletProof Mode?', 'bulletproof-security').'\n\n'.__('Clicking OK will overwrite your existing Root .htaccess file.', 'bulletproof-security').'\n\n'.__('Click OK to Activate BulletProof Mode for your Root folder or click Cancel.', 'bulletproof-security'); echo $text; ?>')" /></p>
</form>

<h3><?php _e('Activate Website wp-admin Folder .htaccess Security Mode', 'bulletproof-security'); ?>  <button id="bps-open-modal3" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>

<div id="bps-modal-content3" title="<?php _e('Activate wp-admin BulletProof Mode', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content3; ?></p>
</div>

<form name="wpadminEnableDisable" action="options.php" method="post">
	<?php settings_fields('bulletproof_security_options_htaccess_res'); ?> 
	<?php $BPS_wpadmin_Options = get_option('bulletproof_security_options_htaccess_res'); ?>
	<strong><label for="wpadmin-res"><?php _e('Enable/Disable wp-admin BulletProof Mode:', 'bulletproof-security'); ?></label></strong><br />
	<strong><label for="wpadmin-res"><?php _e('Note: See Read Me help button above', 'bulletproof-security'); ?></label></strong><br />
<select name="bulletproof_security_options_htaccess_res[bps_wpadmin_restriction]" style="width:280px;">
<option value="enabled" <?php selected('enabled', $BPS_wpadmin_Options['bps_wpadmin_restriction']); ?>><?php _e('Enable wp-admin BulletProof Mode', 'bulletproof-security'); ?></option>
<option value="disabled" <?php selected('disabled', $BPS_wpadmin_Options['bps_wpadmin_restriction']); ?>><?php _e('Disable wp-admin BulletProof Mode', 'bulletproof-security'); ?></option>
</select>
<input type="submit" name="Submit-Enable-Disable-wpadmin" class="bps-blue-button" style="margin:0px 0px 0px 0px;" value="<?php esc_attr_e('Enable/Disable', 'bulletproof-security') ?>" />
</form>

<form name="BulletProof-WPadmin" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_wpadmin_copy'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection13" type="radio" value="bpsecurewpadmin" class="tog" <?php checked('', $bpsecurewpadmin); ?> /> <?php $text = __('wp-admin Folder', 'bulletproof-security').'<br>'.__('BulletProof Mode', 'bulletproof-security'); echo $text; ?></label></th>
	<td class="url-path"><?php echo get_site_url(); ?>/wp-admin/.htaccess<br /><?php $text = '<font color="green">'.__(' Copies the file wpadmin-secure.htaccess to your /wp-admin folder and renames the file name to just .htaccess', 'bulletproof-security').'</font>'; echo $text; ?></td>
   </tr>
   <tr>
	<th><label><input name="selection13" type="radio" value="Removebpsecurewpadmin" class="tog" <?php checked('', $Removebpsecurewpadmin); ?> /> <?php $text = '<font color="red">'.__('Delete wp-admin', 'bulletproof-security').'<br>'.__('htaccess File', 'bulletproof-security').'</font>'; echo $text; ?></label></th>
	<td class="url-path"><?php echo get_site_url(); ?>/wp-admin/.htaccess<br /><?php $text = '<font color="red">'.__(' CAUTION: ', 'bulletproof-security').'</font>'.__('Deletes the .htaccess file in your /wp-admin folder. ONLY delete For testing or BPS removal.', 'bulletproof-security'); echo $text; ?></td>
   </tr>
</table>
<p class="submit"><input type="submit" name="submit13" class="bps-blue-button" value="<?php esc_attr_e('Activate', 'bulletproof-security') ?>" />
</p>
</form>

<h3><?php _e('The Deny All htaccess BulletProof Modes/files below are activated/created automatically. If your Server does not allow this then manually activate the Deny All htaccess files below.', 'bulletproof-security'); ?> 
<h3><?php _e('Activate Deny All htaccess Folder Protection For The BPS Master htaccess Folder', 'bulletproof-security'); ?>  <button id="bps-open-modal4" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>

<div id="bps-modal-content4" title="<?php _e('BPS Master htaccess Folder', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content4; ?></p>
</div>

<form name="BulletProof-deny-all-htaccess" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_denyall_master'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection8" type="radio" value="bps_rename_htaccess_files" class="tog" <?php checked('', $bps_rename_htaccess_files); ?> /> <?php $text = __('Master htaccess', 'bulletproof-security').'<br>'.__('BulletProof Mode', 'bulletproof-security'); echo $text; ?></label></th>
	<td class="url-path"><?php echo plugins_url('/bulletproof-security/admin/htaccess/'); ?><br /><?php $text = '<font color="green">'.__(' Copies the file deny-all.htaccess to the BPS Master htaccess folder and renames the file name to just .htaccess', 'bulletproof-security').'</font>'; echo $text; ?></td>
   </tr>
</table>
<p class="submit"><input type="submit" name="submit8" class="bps-blue-button" value="<?php esc_attr_e('Activate', 'bulletproof-security') ?>" />
</p>
</form>

<h3><?php _e('Activate Deny All htaccess Folder Protection For The BPS Backup Folder', 'bulletproof-security'); ?>  <button id="bps-open-modal5" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>

<div id="bps-modal-content5" title="<?php _e('BPS Backup Folder', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content5; ?></p>
</div>

<form name="BulletProof-deny-all-backup" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_denyall_bpsbackup'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection14" type="radio" value="bps_rename_htaccess_files_backup" class="tog" <?php checked('', $bps_rename_htaccess_files_backup); ?> /> <?php $text = __('BPS Backup', 'bulletproof-security').'<br>'.__('BulletProof Mode', 'bulletproof-security'); echo $text; ?></label></th>
	<td class="url-path"><?php echo get_site_url().'/'.$bps_wpcontent_dir.'/bps-backup/'; ?><br /><?php $text = '<font color="green">'.__(' Copies the file deny-all.htaccess to the BPS Backup folder and renames the file name to just .htaccess', 'bulletproof-security').'</font>'; echo $text; ?></td>
   </tr>
</table>
<p class="submit"><input type="submit" name="submit14" class="bps-blue-button" value="<?php esc_attr_e('Activate', 'bulletproof-security') ?>" />
</p>
</form>
</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
<?php } ?>
</div>
            
<div id="bps-tabs-2" class="bps-tab-page">
<h2><?php _e('BulletProof Security Status', 'bulletproof-security'); ?></h2>

<?php if ( !current_user_can('manage_options') ) { _e('Permission Denied', 'bulletproof-security'); } else { ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-status_table">
  <tr>
    <td width="49%" class="bps-table_title_SS">
	
	<?php _e('Activated BulletProof Security .htaccess Files', 'bulletproof-security'); ?>  <button id="bps-open-modal6" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button>
    
<div id="bps-modal-content6" title="<?php _e('Activated htaccess Files', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content6; ?></p>
</div>

</td>
    <td width="2%">&nbsp;</td>
    <td width="49%" class="bps-table_title"><?php _e('Additional Website Security Measures', 'bulletproof-security'); ?></td>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
    <td>&nbsp;</td>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell">
<?php 
	echo bps_root_htaccess_status();
	echo bps_denyall_htaccess_status_master();
	echo bps_denyall_htaccess_status_backup();
	echo bps_wpadmin_htaccess_status();
?>
    <td>&nbsp;</td>
    <td class="bps-table_cell">
<?php 
	echo bps_wpdb_errors_off();
	echo bps_wp_remove_version();
	echo bps_check_admin_username();
	echo bps_filesmatch_check_readmehtml();
	echo bps_filesmatch_check_installphp();

// Reset/Recheck Dismiss Notices
function bpsDeleteUserMetaDismiss() {
	if ( isset( $_POST['bpsResetDismissSubmit'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_reset_dismiss_notices' );	  

	global $current_user;
	$user_id = $current_user->ID;

	if ( !delete_user_meta($user_id, 'bps_ignore_iis_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The Windows IIS Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Windows IIS check is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( !delete_user_meta($user_id, 'bps_brute_force_login_protection_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The Bonus Custom Code: Brute Force Login Protection Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Bonus Custom Code: Brute Force Login Protection Notice is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( !delete_user_meta($user_id, 'bps_speed_boost_cache_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The Bonus Custom Code: Speed Boost Cache Code Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Bonus Custom Code: Speed Boost Cache Code Notice is reset.', 'bulletproof-security').'<br>'.__('Note: The Speed Boost Cache Code Notice will ONLY be displayed after you dismiss the Brute Force Login Protection Notice.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( !delete_user_meta($user_id, 'bps_author_enumeration_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The Bonus Custom Code: Author Enumeration BOT Probe Code Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Bonus Custom Code: Author Enumeration BOT Probe Code Notice is reset.', 'bulletproof-security').'<br>'.__('Note: The Author Enumeration BOT Probe Code Notice will ONLY be displayed after you dismiss the Speed Boost Cache Code Notice.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( !delete_user_meta($user_id, 'bps_xmlrpc_ddos_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('The Bonus Custom Code: XML-RPC DDoS Protection Code Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Bonus Custom Code: XML-RPC DDoS Protection Code Notice is reset.', 'bulletproof-security').'<br>'.__('Note: The XML-RPC DDoS Protection Code Notice will ONLY be displayed after you dismiss the Author Enumeration BOT Probe Code Notice.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( !delete_user_meta($user_id, 'bps_ignore_PhpiniHandler_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The PHP/php.ini handler htaccess code check Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The PHP/php.ini handler htaccess code check Notice is reset.', 'bulletproof-security').'<br>'.__('Note: The PHP/php.ini handler htaccess code check Notice will ONLY be displayed after you dismiss the Speed Boost Cache Code Notice.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( !delete_user_meta($user_id, 'bps_ignore_Permalinks_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The Custom Permalinks HUD Check Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Custom Permalinks HUD Check is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( !delete_user_meta($user_id, 'bps_ignore_sucuri_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The Sucuri 1-click Hardening wp-content HUD Check Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Sucuri 1-click Hardening wp-content HUD Check is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( !delete_user_meta($user_id, 'bps_ignore_wpfirewall2_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('The WordPress Firewall 2 Plugin Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The WordPress Firewall 2 Plugin check is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}	

	if ( !delete_user_meta($user_id, 'bps_ignore_BLC_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The Broken Link Checker plugin HEAD Request Method filter HUD Check Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Broken Link Checker plugin HEAD Request Method filter HUD Check is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

/* maybe later version - not now
	if ( !delete_user_meta($user_id, 'bps_ignore_public_username_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The username/user account Public Display Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The username/user account Public Display check is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/monitor/monitor.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}
*/
	}
}

?>

<div id="ResetDismissNotices" style="position:relative;top:0px;left:0px;">
<form name="bpsResetDismissNotices" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-2" method="post">
<?php wp_nonce_field('bulletproof_security_reset_dismiss_notices'); ?>
    <p><strong><?php _e('Reset / Recheck Dismiss Notices: ', 'bulletproof-security'); ?>
<input type="hidden" name="bpsRDN" value="bps-RDN" />
<input type="submit" name="bpsResetDismissSubmit" class="bps-blue-button" value="<?php esc_attr_e('Reset / Recheck', 'bulletproof-security') ?>" />
</strong></p>
<?php echo bpsDeleteUserMetaDismiss(); ?>
</form>
</div>

  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
    <td>&nbsp;</td>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
</table>
<?php } ?>

<?php if ( !current_user_can('manage_options') ) { _e('Permission Denied', 'bulletproof-security'); } else { ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-perms_table">
  <tr>
    <td class="bps-table_title_SS">
	
	<?php _e('File and Folder Permissions - CGI or DSO', 'bulletproof-security'); ?>  <button id="bps-open-modal7" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button>
    
<div id="bps-modal-content7" title="<?php _e('File and Folder Permissions', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content7; ?></p>
</div>

</td>
    <td width="2%">&nbsp;</td>
    <td width="49%" class="bps-table_title_SS">
	
	<?php _e('General BulletProof Security File Checks', 'bulletproof-security'); ?>  <button id="bps-open-modal8" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button>
    
<div id="bps-modal-content8" title="<?php _e('General File Checks', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content8; ?></p>
</div>

</td>
  </tr>
  <tr>
  	<td height="100%" class="bps-table_cell_perms_blank">
	
	<?php 
	$sapi_type = php_sapi_name();
	if ( @substr($sapi_type, 0, 6) != 'apache') {	
	
	echo '<div style=\'padding:5px 0px 5px 5px;\'><strong>'; _e('CGI File and Folder Permissions / Recommendations', 'bulletproof-security'); echo '</strong></div>';
	echo '<table style="width:100%;background-color:#A9F5A0;border-bottom:1px solid black;border-top:1px solid black;">';
	echo '<tr>';
	echo '<td style="padding:2px;width:35%;font-weight:bold;">'; _e('File Name', 'bulletproof-security'); echo '<br>'; _e('Folder Name', 'bulletproof-security'); echo '</td>';
    echo '<td style="padding:2px;width:35%;font-weight:bold;">'; _e('File Path', 'bulletproof-security'); echo '<br>'; _e('Folder Path', 'bulletproof-security'); echo '</td>';
    echo '<td style="padding:2px;width:15%;font-weight:bold;">'; _e('Recommended', 'bulletproof-security'); echo '<br>'; _e('Permissions', 'bulletproof-security'); echo '</td>';
    echo '<td style="padding:2px;width:15%;font-weight:bold;">'; _e('Current', 'bulletproof-security'); echo '<br>'; _e('Permissions', 'bulletproof-security'); echo '</td>';
	echo '</tr>';
    echo '</table>';

	bps_check_perms(".htaccess","../.htaccess","404");
	bps_check_perms("wp-config.php","../wp-config.php","400");
	bps_check_perms("index.php","../index.php","400");
	bps_check_perms("wp-blog-header.php","../wp-blog-header.php","400");
	bps_check_perms("root folder","../","705");
	bps_check_perms("wp-admin/","../wp-admin","705");
	bps_check_perms("wp-includes/","../wp-includes","705");
	bps_check_perms("$bps_wpcontent_dir/","../$bps_wpcontent_dir","705");
	bps_check_perms("$bps_wpcontent_dir/bps-backup/","../$bps_wpcontent_dir/bps-backup","755");
	echo '<div style=\'padding-bottom:15px;\'></div>';
	
	} else {
	
	echo '<div style=\'padding:5px 0px 5px 5px;\'><strong>'; _e('DSO File and Folder Permissions / Recommendations', 'bulletproof-security'); echo '</strong></div>';
	echo '<table style="width:100%;background-color:#A9F5A0;border-bottom:1px solid black;border-top:1px solid black;">';
	echo '<tr>';
	echo '<td style="padding:2px;width:35%;font-weight:bold;">'; _e('File Name', 'bulletproof-security'); echo '<br>'; _e('Folder Name', 'bulletproof-security'); echo '</td>';
    echo '<td style="padding:2px;width:35%;font-weight:bold;">'; _e('File Path', 'bulletproof-security'); echo '<br>'; _e('Folder Path', 'bulletproof-security'); echo '</td>';
    echo '<td style="padding:2px;width:15%;font-weight:bold;">'; _e('Recommended', 'bulletproof-security'); echo '<br>'; _e('Permissions', 'bulletproof-security'); echo '</td>';
    echo '<td style="padding:2px;width:15%;font-weight:bold;">'; _e('Current', 'bulletproof-security'); echo '<br>'; _e('Permissions', 'bulletproof-security'); echo '</td>';
	echo '</tr>';
    echo '</table>';
	
	bps_check_perms(".htaccess","../.htaccess","644");
	bps_check_perms("wp-config.php","../wp-config.php","644");
	bps_check_perms("index.php","../index.php","644");
	bps_check_perms("wp-blog-header.php","../wp-blog-header.php","644");
	bps_check_perms("root folder","../","755");
	bps_check_perms("wp-admin/","../wp-admin","755");
	bps_check_perms("wp-includes/","../wp-includes","755");
	bps_check_perms("$bps_wpcontent_dir/","../$bps_wpcontent_dir","755");
	bps_check_perms("$bps_wpcontent_dir/bps-backup/","../$bps_wpcontent_dir/bps-backup","755");
	echo '<div style=\'padding-bottom:15px;\'></div>';
	}
?>

</td>
    <td>&nbsp;</td>
    <td rowspan="4" class="bps-table_cell_file_checks" style="border-bottom:1px solid black; padding:5px;">
    <?php echo bps_general_file_checks(); ?>
   </td>
  </tr>
 <tr>
    <td class="bps-table_cell_file_checks" style="border-bottom:1px solid black;">
    </td>
    <td>&nbsp;</td>
    </tr>
</table>
<br />
<?php } ?>
</div>
            
<div id="bps-tabs-5" class="bps-tab-page">
<h2><?php _e('BulletProof Security Backup &amp; Restore', 'bulletproof-security'); ?></h2>

<?php if ( !current_user_can('manage_options') ) { _e('Permission Denied', 'bulletproof-security'); } else { ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">

<h3><?php echo '<font color="red"><strong>'; _e('CAUTION: ', 'bulletproof-security'); echo '</strong></font>'; ?>  <button id="bps-open-modal10" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>

<div id="bps-modal-content10" title="<?php _e('htaccess File Backup', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content10; ?></p>
</div>

<form name="BulletProof-Backup" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_backup_active_htaccess_files'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection9" type="radio" value="backup_htaccess" class="tog" <?php echo checked('', $backup_htaccess); ?> />
<?php _e('Backup .htaccess Files', 'bulletproof-security'); ?></label></th>
	<td><?php $text = '<font color="green"><strong>'.__('Backs up your currently active .htaccess files in your root and /wp-admin folders.', 'bulletproof-security').'</strong></font><br><strong>'.__('Backup your htaccess files for first time installations of BPS or whenever new modifications have been made to your htaccess files. Read the ', 'bulletproof-security').'<font color="red"><strong>'.__('CAUTION: ', 'bulletproof-security').'</strong></font>'.__('Read Me button.', 'bulletproof-security').'</strong>'; echo $text; ?></td>
	<td>
    </td>
   </tr>
</table>
<p class="submit">
<input type="submit" name="submit9" class="bps-blue-button" value="<?php esc_attr_e('Backup Files', 'bulletproof-security') ?>" />
</p>
</form>

<h3><?php _e('Restore Your .htaccess Files From Backup', 'bulletproof-security'); ?>  <button id="bps-open-modal11" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>

<div id="bps-modal-content11" title="<?php _e('htaccess File Restore', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content11; ?></p>
</div>

<form name="BulletProof-Restore" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_restore_active_htaccess_files'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection10" type="radio" value="restore_htaccess" class="tog" <?php checked('', $restore_htaccess); ?> />
<?php _e('Restore .htaccess Files', 'bulletproof-security'); ?></label></th>
	<td><?php $text = '<font color="green"><strong>'.__('Restores your backed up .htaccess files to your root and /wp-admin folders.', 'bulletproof-security').'</strong></font><br><strong>'.__('Restore your backed up .htaccess files if you have any problems or for use between BPS ugrades.', 'bulletproof-security').'</strong>'; echo $text; ?></td>
	<td>
    </td>
   </tr>
</table>
<p class="submit">
<input type="submit" name="submit10" class="bps-blue-button" value="<?php esc_attr_e('Restore Files', 'bulletproof-security') ?>" />
</p>
</form>

</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
<?php } ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-backup_restore_table">
  <tr>
    <td class="bps-table_title_SS">
	
	<?php _e('Current Backed Up .htaccess Files Status', 'bulletproof-security'); ?>  <button id="bps-open-modal13" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button>
    
<div id="bps-modal-content13" title="<?php _e('Backup .htaccess File', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content13; ?></p>
</div>

</td>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell"><strong><?php bps_general_file_checks_backup_restore(); ?></strong></td>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell"><?php echo bps_backup_restore_checks(); ?></td>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
<br />
</div>
        
<div id="bps-tabs-6" class="bps-tab-page">
<h2><?php _e('BulletProof Security File Editing', 'bulletproof-security'); ?></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell">    

	<h3><?php _e('File Editing', 'bulletproof-security'); ?>  <button id="bps-open-modal14" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
    
<div id="bps-modal-content14" title="<?php _e('File Editing', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content14; ?></p>
</div>

<?php if ( !current_user_can('manage_options') ) { _e('Permission Denied', 'bulletproof-security'); } else { ?>

<table width="100%" border="0">
  <tr>
    <td colspan="2">
    <div id="bps_file_editor" class="bps_file_editor_update">

<?php
echo bps_secure_htaccess_file_check();
echo bps_default_htaccess_file_check();
echo bps_wpadmin_htaccess_file_check();

// Perform File Open and Write test first by appending a literal blank space
// or nothing at all to end of the htaccess files.
// If append write test is successful file is writable on submit
if ( current_user_can('manage_options') ) {
$secure_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/secure.htaccess';
$write_test = "";
	
	if ( is_writable($secure_htaccess_file) ) {
    if ( !$handle = fopen($secure_htaccess_file, 'a+b') ) {
    	exit;
    }
    if ( fwrite($handle, $write_test) === FALSE ) {
    	exit;
    }
		$text = '<strong>'.__('File Open and Write test successful! The secure.htaccess file is writable.', 'bulletproof-security').'</strong><br>';
		echo $text;
	} else {
	
	if ( file_exists($secure_htaccess_file) ) {
		$text = '<font color="blue"><strong>'.__('Cannot write to file: ', 'bulletproof-security').$secure_htaccess_file.'</strong></font><br>';
		echo $text;
	}
	}
	}
	
	if ( isset( $_POST['submit1'] ) && current_user_can('manage_options') ) {
		check_admin_referer( 'bulletproof_security_save_settings_1' );
		$newcontent1 = stripslashes($_POST['newcontent1']);
	
	if ( is_writable($secure_htaccess_file) ) {
		$handle = fopen($secure_htaccess_file, 'w+b');
		fwrite($handle, $newcontent1);
		$text = '<font color="green"><strong>'.__('Success! The secure.htaccess file has been updated.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
    fclose($handle);
	}
}

if ( current_user_can('manage_options') ) {
$default_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/default.htaccess';
$write_test = "";
	
	if ( is_writable($default_htaccess_file) ) {
    if ( !$handle = fopen($default_htaccess_file, 'a+b') ) {
    	exit;
    }
    if ( fwrite($handle, $write_test) === FALSE ) {
    	exit;
    }
		$text = '<strong>'.__('File Open and Write test successful! The default.htaccess file is writable.', 'bulletproof-security').'</strong><br>';
		echo $text;
	} else {
	
	if ( file_exists($default_htaccess_file) ) {
		$text = '<font color="blue"><strong>'.__('Cannot write to file: ', 'bulletproof-security').$default_htaccess_file.'</strong></font><br>';
		echo $text;
	}
	}
	}
	
	if ( isset( $_POST['submit2'] ) && current_user_can('manage_options') ) {
		check_admin_referer( 'bulletproof_security_save_settings_2' );
		$newcontent2 = stripslashes($_POST['newcontent2']);
	
	if ( is_writable($default_htaccess_file) ) {
		$handle = fopen($default_htaccess_file, 'w+b');
		fwrite($handle, $newcontent2);
		$text = '<font color="green"><strong>'.__('Success! The default.htaccess file has been updated.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
    fclose($handle);
	}
}

if ( current_user_can('manage_options') ) {
$wpadmin_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
$write_test = "";
	
	$BPS_wpadmin_Options = get_option('bulletproof_security_options_htaccess_res');
	
	if ( $BPS_wpadmin_Options['bps_wpadmin_restriction'] == 'disabled' ) {
		$text = '<strong>'.__('wpadmin-secure.htaccess file writing is disabled.', 'bulletproof-security').'</strong><br>';
		echo $text;
	
	} else {

	if ( is_writable($wpadmin_htaccess_file) ) {
    if ( !$handle = fopen($wpadmin_htaccess_file, 'a+b') ) {
	    exit;
    }
    if ( fwrite($handle, $write_test) === FALSE ) {
	    exit;
    }
		$text = '<strong>'.__('File Open and Write test successful! The wpadmin-secure.htaccess file is writable.', 'bulletproof-security').'</strong><br>';
		echo $text;
	} else {
	
	if ( file_exists($wpadmin_htaccess_file) ) {
		$text = '<font color="blue"><strong>'.__('Cannot write to file: ', 'bulletproof-security').$wpadmin_htaccess_file.'</strong></font><br>';
		echo $text;
	}
	}
	}
	}
	
	if ( isset( $_POST['submit4'] ) && current_user_can('manage_options') ) {
		check_admin_referer( 'bulletproof_security_save_settings_4' );
		$newcontent4 = stripslashes($_POST['newcontent4']);
	
	if ( is_writable($wpadmin_htaccess_file) ) {
		$handle = fopen($wpadmin_htaccess_file, 'w+b');
		fwrite($handle, $newcontent4);
		$text = '<font color="green"><strong>'.__('Success! The wpadmin-secure.htaccess file has been updated.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
    fclose($handle);
	}
}

if ( current_user_can('manage_options') ) {
$root_htaccess_file = ABSPATH . '.htaccess';
$write_test = "";
	
	if ( is_writable($root_htaccess_file) ) {
    if ( !$handle = fopen($root_htaccess_file, 'a+b') ) {
	    exit;
    }
    if ( fwrite($handle, $write_test) === FALSE ) {
	    exit;
    }
		$text = '<strong>'.__('File Open and Write test successful! Your currently active root .htaccess file is writable.', 'bulletproof-security').'</strong><br>';
		echo $text;
	} else {
	
	if ( file_exists($root_htaccess_file) ) {
		$text = '<font color="blue"><strong>'.__('Your root .htaccess file is Locked with Read Only Permissions.', 'bulletproof-security').'<br>'.__('Use the Lock and Unlock buttons below to Lock or Unlock your root .htaccess file for editing.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	} else {
		$text = '<font color="black"><strong>'.__('Cannot write to file: ', 'bulletproof-security').$root_htaccess_file.'</strong></font><br>';
		echo $text;
	}
	}
	}
	
	if ( isset( $_POST['submit5'] ) && current_user_can('manage_options') ) {
		check_admin_referer( 'bulletproof_security_save_settings_5' );
		$newcontent5 = stripslashes($_POST['newcontent5']);
	
	if ( !is_writable($root_htaccess_file) ) {
		$text = '<font color="red"><strong>'.__('Error: Unable to write to the Root .htaccess file. If your Root .htaccess file is locked you must unlock first.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	}	
	
	if ( is_writable($root_htaccess_file) ) {
		$handle = fopen($root_htaccess_file, 'w+b');
		fwrite($handle, $newcontent5);
		$text = '<font color="green"><strong>'.__('Success! Your currently active root .htaccess file has been updated.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
    fclose($handle);
	}
}

if ( current_user_can('manage_options') ) {
$current_wpadmin_htaccess_file = ABSPATH . 'wp-admin/.htaccess';
$write_test = "";
	
	$BPS_wpadmin_Options = get_option('bulletproof_security_options_htaccess_res');
	
	if ( $BPS_wpadmin_Options['bps_wpadmin_restriction'] == 'disabled' ) {
		$text = '<strong>'.__('wp-admin active htaccess file writing is disabled.', 'bulletproof-security').'</strong><br>';
		echo $text;
	
	} else {

	if ( is_writable($current_wpadmin_htaccess_file) ) {
    if ( !$handle = fopen($current_wpadmin_htaccess_file, 'a+b') ) {
	    exit;
    }
    if ( fwrite($handle, $write_test) === FALSE ) {
	    exit;
    }
		$text = '<strong>'.__('File Open and Write test successful! Your currently active wp-admin .htaccess file is writable.', 'bulletproof-security').'</strong><br>';
		echo $text;
	} else {
	
	if ( file_exists($current_wpadmin_htaccess_file) ) {
		$text = '<font color="blue"><strong>'.__('Cannot write to file: ', 'bulletproof-security').$current_wpadmin_htaccess_file.'</strong></font><br>';
		echo $text;
	}
	}
	}
	}

	if ( isset( $_POST['submit6'] ) && current_user_can('manage_options') ) {
		check_admin_referer( 'bulletproof_security_save_settings_6' );
		$newcontent6 = stripslashes($_POST['newcontent6']);
	
	if ( is_writable($current_wpadmin_htaccess_file) ) {
		$handle = fopen($current_wpadmin_htaccess_file, 'w+b');
		fwrite($handle, $newcontent6);
		$text = '<font color="green"><strong>'.__('Success! Your currently active wp-admin .htaccess file has been updated.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
    fclose($handle);
	}
}

// Lock and Unlock Root .htaccess file 
if ( isset( $_POST['submit-ProFlockLock'] ) && current_user_can('manage_options' )) {
	check_admin_referer( 'bulletproof_security_flock_lock' );
	$bpsRootHtaccessOL = ABSPATH . '.htaccess';
	
	if ( file_exists($bpsRootHtaccessOL) ) {
		chmod($bpsRootHtaccessOL, 0404);
		$text = '<font color="blue"><strong><br>'.__('Your Root .htaccess file has been Locked.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	} else {
		$text = '<font color="red"><strong><br>'.__('Unable to Lock your Root .htaccess file.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	}
}
	
if ( isset( $_POST['submit-ProFlockUnLock'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_flock_unlock' );
	$bpsRootHtaccessOL = ABSPATH . '.htaccess';
		
	if ( file_exists($bpsRootHtaccessOL) ) {
		chmod($bpsRootHtaccessOL, 0644);
		$text = '<font color="blue"><strong><br>'.__('Your Root .htaccess file has been Unlocked.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	} else {
		$text = '<font color="red"><strong><br>'.__('Unable to Unlock your Root .htaccess file.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	}
}
?>
</div>
</td>
<td width="33%" align="center" valign="top"></td>
  </tr>
  <tr>
    <td width="22%">

<?php // Detect the SAPI - display form submit button only if sapi is cgi
	$sapi_type = php_sapi_name();
	if ( @substr($sapi_type, 0, 6) != 'apache' ) {	
?>    
 
 	<div style="margin: 5px;">  
    <form name="bpsFlockLockForm" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_flock_lock'); ?>
	<input type="submit" name="submit-ProFlockLock" value="<?php _e('Lock htaccess File', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Click OK to Lock your Root htaccess file or click Cancel.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Note: The File Open and Write Test window will still display the last status of the file as Unlocked. To see the current status refresh your browser.', 'bulletproof-security'); echo $text; ?>')" />
</form>
<br />
	
    <form name="bpsRootAutoLock-On" action="options.php#bps-tabs-6" method="post">
    <?php settings_fields('bulletproof_security_options_autolock'); ?>
	<?php $options = get_option('bulletproof_security_options_autolock'); ?>
<input type="hidden" name="bulletproof_security_options_autolock[bps_root_htaccess_autolock]" value="On" />
<input type="submit" name="submit-RootHtaccessAutoLock-On" value="<?php _e('Turn On AutoLock', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Turning AutoLock On will allow BPS Pro to automatically lock your Root .htaccess file. For some folks this causes a problem because their Web Hosts do not allow the Root .htaccess file to be locked. For most folks allowing BPS Pro to AutoLock the Root .htaccess file works fine.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Click OK to Turn AutoLock On or click Cancel.', 'bulletproof-security'); echo $text; ?>')" /><?php if ($options['bps_root_htaccess_autolock'] == '' || $options['bps_root_htaccess_autolock'] == 'On') { $text = '<font color="blue" style="font-size:14px;border:2px solid gray;padding:2px;margin-left:5px;background-color:#5cf1f9;position:relative;top:1px;"><strong>'.__('On', 'bulletproof-security').'</strong></font>'; echo $text; } ?>
</form>
</div>

<?php } else { echo ''; } ?>

</td>
    <td width="45%">

<?php // Detect the SAPI - display form submit button only if sapi is cgi
	$sapi_type = php_sapi_name();
	if ( @substr($sapi_type, 0, 6) != 'apache' ) {	
?>        

	<div style="margin: 5px;">    
    <form name="bpsFlockUnLockForm" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_flock_unlock'); ?>

	<input type="submit" name="submit-ProFlockUnLock" value="<?php _e('Unlock htaccess File', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Click OK to Unlock your Root htaccess file or click Cancel.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Note: The File Open and Write Test window will still display the last status of the file as Locked. To see the current status refresh your browser.', 'bulletproof-security'); echo $text; ?>')" />
</form>
<br />
    
    <form name="bpsRootAutoLock-Off" action="options.php#bps-tabs-6" method="post">
    <?php settings_fields('bulletproof_security_options_autolock'); ?>
	<?php $options = get_option('bulletproof_security_options_autolock'); ?>
<input type="hidden" name="bulletproof_security_options_autolock[bps_root_htaccess_autolock]" value="Off" />
<input type="submit" name="submit-RootHtaccessAutoLock-Off" value="<?php _e('Turn Off AutoLock', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Turning AutoLock Off will prevent BPS Pro from automatically locking your Root .htaccess file. For some folks this is necessary because their Web Hosts do not allow the Root .htaccess file to be locked. For most folks allowing BPS Pro to AutoLock the Root .htaccess file works fine.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Click OK to Turn AutoLock Off or click Cancel.', 'bulletproof-security'); echo $text; ?>')" /><?php if ($options['bps_root_htaccess_autolock'] == 'Off') { $text = '<font color="blue" style="font-size:14px;border:2px solid gray;padding:2px;margin-left:5px;background-color:#5cf1f9;position:relative;top:1px;"><strong>'.__('Off', 'bulletproof-security').'</strong></font>'; echo $text; } ?>
</form>
</div>

<?php } else { echo ''; } ?>

</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2">
    
    <!-- jQuery UI File Editor Tab Menu -->
<div id="bps-edittabs" class="bps-edittabs-class">
		<ul>
			<li><a href="#bps-edittabs-1"><?php _e('secure.htaccess', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-edittabs-2"><?php _e('default.htaccess', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-edittabs-4"><?php _e('wpadmin-secure.htaccess', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-edittabs-5"><?php _e('Your Current Root htaccess File', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-edittabs-6"><?php _e('Your Current wp-admin htaccess File', 'bulletproof-security'); ?></a></li>
        </ul>
       
<?php 
$scrollto1 = isset($_REQUEST['scrollto1']) ? (int) $_REQUEST['scrollto1'] : 0; 
$scrollto2 = isset($_REQUEST['scrollto2']) ? (int) $_REQUEST['scrollto2'] : 0;
$scrollto4 = isset($_REQUEST['scrollto4']) ? (int) $_REQUEST['scrollto4'] : 0;
$scrollto5 = isset($_REQUEST['scrollto5']) ? (int) $_REQUEST['scrollto5'] : 0;
$scrollto6 = isset($_REQUEST['scrollto6']) ? (int) $_REQUEST['scrollto6'] : 0;
?>

<div id="bps-edittabs-1" class="bps-edittabs-page-class">
<form name="template1" id="template1" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_1'); ?>
    <div>
    <textarea class="bps-text-area-600x700" name="newcontent1" id="newcontent1" tabindex="1"><?php echo bps_get_secure_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($secure_htaccess_file) ?>" />
	<input type="hidden" name="scrollto1" id="scrollto1" value="<?php echo $scrollto1; ?>" />
    <p class="submit">
	<input type="submit" name="submit1" class="bps-blue-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template1').submit(function(){ $('#scrollto1').val( $('#newcontent1').scrollTop() ); });
	$('#newcontent1').scrollTop( $('#scrollto1').val() ); 
});
/* ]]> */
</script>     
</div>

<div id="bps-edittabs-2" class="bps-edittabs-page-class">
<form name="template2" id="template2" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_2'); ?>
	<div>
    <textarea class="bps-text-area-600x700" name="newcontent2" id="newcontent2" tabindex="2"><?php echo bps_get_default_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($default_htaccess_file) ?>" />
	<input type="hidden" name="scrollto2" id="scrollto2" value="<?php echo $scrollto2; ?>" />
    <p class="submit">
	<input type="submit" name="submit2" class="bps-blue-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template2').submit(function(){ $('#scrollto2').val( $('#newcontent2').scrollTop() ); });
	$('#newcontent2').scrollTop( $('#scrollto2').val() );
});
/* ]]> */
</script>     
</div>

<div id="bps-edittabs-4" class="bps-edittabs-page-class">
<form name="template4" id="template4" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_4'); ?>
	<div>
    <textarea class="bps-text-area-600x700" name="newcontent4" id="newcontent4" tabindex="4"><?php echo bps_get_wpadmin_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($wpadmin_htaccess_file) ?>" />
	<input type="hidden" name="scrollto4" id="scrollto4" value="<?php echo $scrollto4; ?>" />
    <p class="submit">
	<input type="submit" name="submit4" class="bps-blue-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template4').submit(function(){ $('#scrollto4').val( $('#newcontent4').scrollTop() ); });
	$('#newcontent4').scrollTop( $('#scrollto4').val() );
});
/* ]]> */
</script>     
</div>

<?php
// File Editor Root .htaccess file Lock check with pop up Confirm message
function bpsStatusRHE() {
clearstatcache();
$file = ABSPATH . '.htaccess';
$perms = @substr(sprintf('%o', fileperms($file)), -4);
$sapi_type = php_sapi_name();
	
	if ( file_exists($file) && @substr( $sapi_type, 0, 6) != 'apache' ) {		
	return $perms;
	}
}
?>

<div id="bps-edittabs-5" class="bps-edittabs-page-class">
<form name="template5" id="template5" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_5'); ?>
	<div>
    <textarea class="bps-text-area-600x700" name="newcontent5" id="newcontent5" tabindex="5"><?php echo bps_get_root_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($root_htaccess_file) ?>" />
	<input type="hidden" name="scrollto5" id="scrollto5" value="<?php echo $scrollto5; ?>" />
    <p class="submit">
    
	<?php if ( @bpsStatusRHE($perms) == '0404' ) { ?>
	<input type="submit" name="submit5" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('YOUR ROOT HTACCESS FILE IS LOCKED.', 'bulletproof-security').'\n\n'.__('YOUR FILE EDITS / CHANGES CANNOT BE SAVED.', 'bulletproof-security').'\n\n'.__('Click Cancel, copy the file editing changes you made to save them and then click the Unlock .htaccess File button to unlock your Root .htaccess file. After your Root .htaccess file is unlocked paste your file editing changes back into your Root .htaccess file and click this Update File button again to save your file edits / changes.', 'bulletproof-security'); echo $text; ?>')" />
	<?php } else { ?>
	<input type="submit" name="submit5" class="bps-blue-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
<?php } ?>

</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template5').submit(function(){ $('#scrollto5').val( $('#newcontent5').scrollTop() ); });
	$('#newcontent5').scrollTop( $('#scrollto5').val() );
});
/* ]]> */
</script>     
</div>

<div id="bps-edittabs-6" class="bps-edittabs-page-class">
<form name="template6" id="template6" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_6'); ?>
	<div>
    <textarea class="bps-text-area-600x700" name="newcontent6" id="newcontent6" tabindex="6"><?php echo bps_get_current_wpadmin_htaccess_file(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($current_wpadmin_htaccess_file) ?>" />
	<input type="hidden" name="scrollto6" id="scrollto6" value="<?php echo $scrollto6; ?>" />
    <p class="submit">
	<input type="submit" name="submit6" class="bps-blue-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template6').submit(function(){ $('#scrollto6').val( $('#newcontent6').scrollTop() ); });
	$('#newcontent6').scrollTop( $('#scrollto6').val() );
});
/* ]]> */
</script>     
</div>
</div>

</td>
  </tr>
</table>

<?php } ?>

</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
</div>

<div id="bps-tabs-7" class="bps-tab-page">
<h2><?php _e('Custom Code', 'bulletproof-security'); ?></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">

<h3><?php _e('Add Custom htaccess Code To Root and wp-admin htaccess Files', 'bulletproof-security'); ?>  <button id="bps-open-modal16" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>

<div id="bps-modal-content16" title="<?php _e('Custom Code', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content16; ?></p>
</div>

<h3><?php $text = '<strong><a href="http://forum.ait-pro.com/video-tutorials/" target="_blank" title="Link opens in a new Browser window">'.__('Custom Code Video Tutorial', 'bulletproof-security').'</a></strong>'; echo $text; ?></h3>
<h3><?php $text = '<strong><a href="http://forum.ait-pro.com/read-me-first/" target="_blank" title="Link opens in a new Browser window">'.__('BulletProof Security Forum', 'bulletproof-security').'</a></strong>'; echo $text; ?></h3>

<?php 
if ( !current_user_can('manage_options') ) { 
	_e('Permission Denied', 'bulletproof-security'); 
	
	} else { 

	require_once( WP_PLUGIN_DIR . '/bulletproof-security/admin/core/core-custom-code.php' );
}
?>
<br />

</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>

</div>

<div id="bps-tabs-9">
<h2><?php _e('BulletProof Security Help &amp; FAQ', 'bulletproof-security'); ?></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
   <tr>
    <td colspan="2" class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td width="50%" class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/category/bulletproof-security-contributors/" target="_blank"><?php _e('Contributors Page', 'bulletproof-security'); ?></a></td>
    <td width="50%" class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/2304/wordpress-tips-tricks-fixes/permalinks-wordpress-custom-permalinks-wordpress-best-wordpress-permalinks-structure/" target="_blank"><?php _e('WP Permalinks - Custom Permalink Structure Help Info', 'bulletproof-security'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help"><a href="http://forum.ait-pro.com/forums/topic/security-log-event-codes/" target="_blank"><?php _e('Security Log Event Codes', 'bulletproof-security'); ?></a></td>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/2239/bulletproof-security-plugin-support/adding-a-custom-403-forbidden-page-htaccess-403-errordocument-directive-examples/" target="_blank"><?php _e('Adding a Custom 403 Forbidden Page For Your Website', 'bulletproof-security'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help"><a href="http://forum.ait-pro.com/forums/topic/plugin-conflicts-actively-blocked-plugins-plugin-compatibility/" target="_blank"><?php _e('Forum: Search, Troubleshooting Steps & Post Questions For Assistance', 'bulletproof-security'); ?></a></td>
    <td class="bps-table_cell_help"><a href="http://forum.ait-pro.com/video-tutorials/" target="_blank"><?php _e('Custom Code Video Tutorial', 'bulletproof-security'); ?></a></td>
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

<div id="bps-tabs-10">
<h2><?php _e('Whats New in ~ ', 'bulletproof-security'); ?><?php echo $bps_version; ?></h2>
<h3><?php _e('The Whats New page lists new changes made in each new version release of BulletProof Security', 'bulletproof-security'); ?></h3>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-whats_new_table">
  <tr>
   <td width="1%" class="bps-table_title_no_border">&nbsp;</td>
   <td width="99%" class="bps-table_title_no_border">&nbsp;</td>
  </tr>
   <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('Maintenance Mode Network/Multisite Subdomain Completion:', 'bulletproof-security').'</strong></h3>'.__('Maintenance Mode coding work has been completed for Network/Multisite subdomain site types. Maintenance Mode now works for every/all WordPress site types, BuddyPress and bbPress site types.', 'bulletproof-security'); echo $text; ?>
</td>
  </tr>
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('BugFixes/Code Corrections/Misc/CSS/Visual/Other:', 'bulletproof-security').'</strong></h3>'.__('&bull; master-backups folder creation fix for unusual scenarios.<br>&bull; Automatic correction during upgrade for any existing timthumb RFI filter duplicate Referer lines.', 'bulletproof-security'); echo $text; ?>
     </td>
  </tr> 
   <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h2><strong>'.__('Whats New in BPS .51.3', 'bulletproof-security').'</strong></h2>'; echo $text; ?>
</td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('WordPress 4.1 jQuery UI Compatibility Code Correction:', 'bulletproof-security').'</strong></h3>'.__('<strong>Bug:</strong> BPS jQuery UI Dialog Read Me help window position not centered in WordPress 4.1.', 'bulletproof-security').'<br>'.__('<strong>Fix:</strong> Corrected the BPS jQuery UI Dialog Position Method code by adding the appropriate "my" and "at" options.', 'bulletproof-security').'<br>'.__('<strong>Note:</strong> For anyone else experiencing this issue see this Forum Topic for the solution: ', 'bulletproof-security').'<strong><a href="http://forum.ait-pro.com/forums/topic/wordpress-4-1-jquery-ui-dialog-window-position-not-centered/" target="_blank" title="jQuery UI Dialog window position not centered">'.__('jQuery UI Dialog window position not centered', 'bulletproof-security').'</a></strong>'; echo $text; ?>
</td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('Help Link Corrections:', 'bulletproof-security').'</strong></h3>'.__('Special thanks to WordPress Member: ', 'bulletproof-security').'<strong><a href="https://wordpress.org/support/profile/mrppp" target="_blank" title="WordPress Member mrppp">'.__('mrppp', 'bulletproof-security').'</a></strong>'.__(' for finding and reporting invalid help links in BPS.', 'bulletproof-security'); echo $text; ?>
</td>
  </tr> 
   <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h2><strong>'.__('Whats New in BPS .51.2', 'bulletproof-security').'</strong></h2>'; echo $text; ?>
</td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('Significant Root and wp-admin htaccess File Changes:', 'bulletproof-security').'</strong></h3>'.__('Most significant changes/fixes/improvements to the BPS root and wp-admin htaccess files.<br>For additional information about these changes click this Forum link: ', 'bulletproof-security').'<strong><a href="http://forum.ait-pro.com/forums/topic/root-and-wp-admin-htaccess-file-significant-changes/" target="_blank" title="Significant Root and wp-admin htaccess File Changes">'.__('Significant Root and wp-admin htaccess File Changes', 'bulletproof-security').'</a></strong><br><br><strong>&bull; Root htaccess File/Code Fix:</strong> Removal of additional instances of "BEGIN WordPress" and "END WordPress" text from the root htaccess file which caused multiple instances of the default wp htaccess code to be created in the root htaccess file when the WP flush_rewrite_rules function was executed by other plugins and themes.<br><br><strong>&bull; htaccess Help Text Improvement Overall:</strong> The help text throughout both the root and wp-admin htaccess files was very dated and was in need of updating. Better/clearer examples have been created in the help text. Overall the htaccess files are more streamlined and less cluttered looking visually.<br><br><strong>&bull; Structure/Order Code Changes:</strong> Several blocks of htaccess code has been structured differently as far as the general order/sequence of code goes in the root htaccess file and more importantly what code will remain in the root htaccess file in the event that the WP flush_rewrite_rules function is executed by another plugin or theme. There are several technical reasons for making these structure/order changes, which I will not bore you with. Basically things are structured/ordered much better for any/every possible scenario that may occur.<br><br><strong>Note:</strong> This is a one-time BPS Update that requires manual steps to be performed. All future versions of BPS will do the normal/typical automatic update of the BPS htaccess files. Overall we felt that creating a Notice about these significant changes vs just doing a normal automatic update was the best route to take for the primary reasons stated above and some additional reasons not stated here.'; echo $text; ?>
</td>
  </tr>
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('New Custom Code Text Boxes Added:', 'bulletproof-security').'</strong></h3>'.__('&bull; CUSTOM CODE TURN OFF YOUR SERVER SIGNATURE<br>&bull; CUSTOM CODE DENY ACCESS TO PROTECTED SERVER FILES AND FOLDERS', 'bulletproof-security'); echo $text; ?>
</td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('BugFixes/Code Corrections/Misc/CSS/Visual/Other:', 'bulletproof-security').'</strong></h3>'.__('&bull; Custom Code accordion is now using tables vs CSS divs for cross Browser visual compatibility and obsolete CSS code has been removed for the CSS divs.<br>&bull; Overall inpage Custom Code help text information/example improvements.<br>&bull; Network/Multisite Net Correction code/check removed. No longer needed and is now obsolete.<br>&bull; Remote Address IP check added in the 403.php Security logging template. Will display current IP address for troubleshooting purposes.', 'bulletproof-security'); echo $text; ?>
     </td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom_no_border">&nbsp;</td>
    <td class="bps-table_cell_bottom_no_border">&nbsp;</td>
  </tr>
</table>
</div>

<div id="bps-tabs-11" class="bps-tab-page">
<h2><?php _e('My Notes', 'bulletproof-security'); ?></h2>
<div id="bpsMyNotesborder" style="border-top:1px solid #999999;">
<h3><?php _e('Save any personal notes or htaccess code to your WordPress Database', 'bulletproof-security'); ?></h3>
</div>

<?php if ( !current_user_can('manage_options') ) { _e('Permission Denied', 'bulletproof-security'); } else { 
	$scrolltoNotes = isset($_REQUEST['scrolltoNotes']) ? (int) $_REQUEST['scrolltoNotes'] : 0;
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">

<form name="myNotes" action="options.php#bps-tabs-11" method="post">
	<?php settings_fields('bulletproof_security_options_mynotes'); ?>
	<?php $options = get_option('bulletproof_security_options_mynotes'); ?>
<div>
    <textarea class="bps-text-area-600x700" name="bulletproof_security_options_mynotes[bps_my_notes]" tabindex="1"><?php echo $options['bps_my_notes']; ?></textarea>
    <input type="hidden" name="scrolltoNotes" value="<?php echo $scrolltoNotes; ?>" />
    <p class="submit">
	<input type="submit" name="myNotes_submit" class="bps-blue-button" value="<?php esc_attr_e('Save My Notes', 'bulletproof-security') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#myNotes').submit(function(){ $('#scrolltoNotes').val( $('#bulletproof_security_options_mynotes[bps_my_notes]').scrollTop() ); });
	$('#bulletproof_security_options_mynotes[bps_my_notes]').scrollTop( $('#scrolltoNotes').val() ); 
});
/* ]]> */
</script>
</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
<?php } ?>
</div>

<div id="bps-tabs-12">
<h2><?php _e('BulletProof Security Pro Feature Highlights', 'bulletproof-security'); ?></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td colspan="2" class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td width="62%" valign="top" class="bps-table_cell_help">

<div id="bpsProLogo" style="position:relative; top:0px; left:0px;"><a href="http://affiliates.ait-pro.com/po/" target="_blank" title="Get BulletProof Security Pro">
<img src="<?php echo plugins_url('/bulletproof-security/admin/images/bps-pro-logo.png'); ?>" style="float:left;width:361px;-moz-box-shadow:4px 4px 4px #888888;-webkit-box-shadow:4px 4px 4px #888888;box-shadow:4px 4px 4px #888888;" /></a>
</div>

<div id="bpsProText" style="margin:0px 0px 15px 0px;font-size:22px;color:#000066;font-weight:bold;font-style:italic;line-height:22px;text-align:center;">
<?php echo _e('The Most Effective & Comprehensive<br>WordPress Security Plugin', 'bulletproof-security'); ?>

<div id="bpsProLinks" style="margin:15px 0px 10px 0px;font-size:12px;font-weight:bold;font-style:normal;line-height:12px;">
    <a href="http://forum.ait-pro.com/video-tutorials/" target="_blank" title="Link Opens in New Browser Window"><?php _e('BPS Pro 1 Click Setup Wizard & Demo Video Tutorial', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://affiliates.ait-pro.com/" target="_blank" title="Link Opens in New Browser Window"><?php _e('BPS Pro Affiliate Program', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/bulletproof-security-pro-flash/bulletproof.html" target="_blank" title="Link Opens in New Browser Window"><?php _e('View All BPS Pro Features', 'bulletproof-security'); ?></a>
</div>
</div>

<div id="bpsProFeatures" style="position:relative;top:20px;left:0px;font-size:14px;">

<?php $text = '<h4><strong>'.__('BulletProof Security Pro is The Complete Website Security Package for Hacker and Spammer Protection', 'bulletproof-security').'</strong></h4>'; echo $text; ?>

<?php echo '<strong>'; _e('1 Click Setup Wizard: ', 'bulletproof-security'); echo '</strong>'; _e('All BPS Pro security features are setup by the BPS Pro Setup Wizard.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('1 Click Upgrades: ', 'bulletproof-security'); echo '</strong>'; _e('BPS Pro Plugin upgrade notifications are displayed in your WordPress Dashboard exactly the same way as all other WordPress plugins. All BPS Pro files are automatically updated during the upgrade process and no additional setup steps are required when upgrading. When new features and options are added to new BPS Pro versions those new features and options are automatically setup during BPS Pro upgrades and do not require any additional setup or configuration by you.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('AutoRestore / Quarantine Intrusion Detection and Prevention System (IDPS): ', 'bulletproof-security'); echo '</strong>'; _e('ARQ is a real-time file monitor that automatically AutoRestores and/or Quarantines files. ARQ utilizes countermeasure website security that has the capability to protect all of your website files, both WordPress and non-WordPress files, even if your Web Host Server is hacked or if your FTP password is cracked or stolen. Quarantine Options: Restore File, Delete File and View File. AutoRestore/Quarantine includes Displayed Alerts, Email Alerts and Logging. AutoRestore/Quarantine works seamlessly with WordPress Automatic Updates. The BPS Pro Security Log logs all WP files that were installed and backed up automatically during WordPress Automatic Update installations.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('New Concept in Website Security: DB Monitor Intrusion Detection System (IDS): ', 'bulletproof-security'); echo '</strong>'; _e('The DB Monitor (DBM) is an Intrusion Detection System (IDS) that alerts you via email anytime a change/modification occurs in your WordPress database or a new database table is created in your WordPress database. The DB Monitor email alert contains information about what database change/modification occurred and other relevant help info. Your DB Monitor Log also logs any changes/modifications to your WordPress database and other relevant help info. The DBM IDS is similar to the ARQ IDPS where it is the most powerful last line of website security protection defense. If all other outer and inner layers of security protection are penetrated then the most powerful DBM IDS and ARQ IDPS systems kick in and protect your website from attacks/hackers. Even if these powerful security measures are never utilized the most significant benefit is that you know for sure that neither your website files or your WordPress database have been tampered with.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('DB Diff Tool: ', 'bulletproof-security'); echo '</strong>'; _e('The DB Diff Tool compares old database tables from DB backups to current database tables and displays any differences in the data/content of those 2 database tables. The DB Diff Tool allows you to check your WordPress Database if you receive a DB Monitor email alert and do not recognize the database table name change/modification.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('Plugin Firewall: ', 'bulletproof-security'); echo '</strong>'; _e('The Plugin Firewall / Plugins BulletProof Mode prevents/blocks/forbids Remote Access to the plugins folder from external sources (remote script execution, hacker recon, remote scanning, remote accessibility, etc.) and only allows internal access to the plugins folder based on this criteria: Domain name, Server IP Address and Public IP / Your Computer IP Address. True IP based Firewall that updates your IP addres in real-time when it changes. AutoPilot Mode automatically creates plugin whitelist rules in real-time.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('Uploads Folder Anti-Exploit Guard: ', 'bulletproof-security'); echo '</strong>'; _e('The Uploads Folder Anti-Exploit Guard / Uploads BulletProof Mode allows ONLY safe image files with valid image file extensions such as jpg, gif, png, etc. to be accessed, opened or viewed from the uploads folder. The Uploads Anti-Exploit Guard prevents/blocks/forbids files by file extension names in the uploads folder from being accessed, opened, viewed, processed or executed.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('JTC Anti-Spam / Anti-Hacker: ', 'bulletproof-security'); echo '</strong>'; _e('Hacker Protection ~ Spammer Protection ~ DoS/DDoS Attack Protection ~ Brute Force Login Attack Protection ~ SpamBot Trap. JTC Anti-Spam provides website security protection as well as website Anti-Spam protection. JTC Anti-Spam is user friendly Anti-Spam / Anti-Hacker Protection. You can customize and personalize your JTC ToolTip message and CAPTCHA to match your website concept. JTC Anti-Spam / Anti-Hacker protects these website pages/Forms: Login page/Form, Registration page/Form, Lost Password page/Form, Comment page/Form, BuddyPress Register page/Form and the BuddyPress Sidebar Login Form with a user friendly & customizable jQuery ToolTip CAPTCHA.', 'bulletproof-security'); ?><br /><br />

<?php  echo '<strong>'; _e('S-Monitor Displayed Alerts, Email Alerting & Log File Options: ', 'bulletproof-security'); echo '</strong>'; _e('S-Monitor displayed alerting options allow you to choose how you want real-time alerts displayed to you: WP Dashboard, BPS Pro pages only or turned off. Choose whether or not to have email alerts sent when Log files log events. Choose to either automatically Zip and Email Log files to you when they reach the maximum size limit option that you choose or just automatically delete log files when they reach the the maximum size limit option that you choose.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('F-Lock: ', 'bulletproof-security'); echo '</strong>'; _e('Lock and Unlock WordPress Mission Critical files from within your WordPress Dashboard.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('Custom php.ini / ini_set Options: ', 'bulletproof-security'); echo '</strong>'; _e('Quickly create a custom php.ini file for your website or use ini_set Options to increase security and performance with just a few clicks. Additional P-Security Features: All-purpose File Manager, All-purpose File Editor, Protected PHP Error Log, PHP Error Alerts, Secure phpinfo Viewer...', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('Advanced Real-Time Alerting & Heads Up Dashboard Status Display: ', 'bulletproof-security'); echo '</strong>';  _e('BPS Pro checks and displays error, warning, notifications and alert messages in real time. You can choose how you want these messages displayed to you with S-Monitor Monitoring &amp; Alerting Options - Display in your WP Dashboard, BPS Pro pages only, Turned off, Email Alerts, Logging...', 'bulletproof-security'); echo '<br><br><img src="'.plugins_url('/bulletproof-security/admin/images/bps-pro-dashboard-status-display.jpg').'" style="-moz-box-shadow:4px 4px 4px #888888;-webkit-box-shadow:4px 4px 4px #888888;box-shadow:4px 4px 4px #888888;" />'; ?><br /><br />

<?php echo '<strong>'; _e('Pro-Tools: ', 'bulletproof-security'); echo '</strong>'; _e('Pro-Tools is a set of versatile 16 website tools (16 mini-plugins): Online Base64 Decoder, Offline Base64 Decode/Encode, Mcrypt Decrypt/Encrypt, Crypt Encryption, Scheduled Crons, String Finder, String Replacer/Remover, DB String Finder, DB Table Cleaner/Remover, DNS Finder, Ping Website, cURL Scan, Website Headers, WP Automatic Update, Plugin Update Check, XML-RPC Exploit Checker', 'bulletproof-security'); ?><br /><br />
</div>	

    </td>
    <td width="38%" valign="top" class="bps-table_cell_help">

<div id="bpsProVersions">
<a href="http://forum.ait-pro.com/forums/topic/bulletproof-security-pro-version-release-dates/" target="_blank" title="Link Opens in New Browser Window" style="font-size:22px;"><?php _e('BPS Pro Version Release Dates', 'bulletproof-security'); ?></a><br /><br />
<a href="http://www.ait-pro.com/aitpro-blog/5080/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-9/" target="_blank" title="Link Opens in New Browser Window">
	 <?php _e('Whats New in BPS Pro 9.9', 'bulletproof-security'); ?></a><br /><br />
<a href="http://www.ait-pro.com/aitpro-blog/5075/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-8/" target="_blank" title="Link Opens in New Browser Window">
	 <?php _e('Whats New in BPS Pro 9.8', 'bulletproof-security'); ?></a><br /><br />
<a href="http://www.ait-pro.com/aitpro-blog/5066/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-7/" target="_blank" title="Link Opens in New Browser Window">
	 <?php _e('Whats New in BPS Pro 9.7', 'bulletproof-security'); ?></a><br /><br />
<a href="http://www.ait-pro.com/aitpro-blog/5062/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-6/" target="_blank" title="Link Opens in New Browser Window">
	 <?php _e('Whats New in BPS Pro 9.6', 'bulletproof-security'); ?></a><br /><br />
<a href="http://www.ait-pro.com/aitpro-blog/5056/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-5/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 9.5', 'bulletproof-security'); ?></a><br /><br />
     <a href="http://www.ait-pro.com/aitpro-blog/5046/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-3/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 9.3/9.4', 'bulletproof-security'); ?></a><br /><br />
     <a href="http://www.ait-pro.com/aitpro-blog/5039/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-2/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 9.2', 'bulletproof-security'); ?></a><br /><br />
     <a href="http://www.ait-pro.com/aitpro-blog/5027/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-1/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 9.1', 'bulletproof-security'); ?></a><br /><br />
     <a href="http://www.ait-pro.com/aitpro-blog/5009/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-0/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 9.0', 'bulletproof-security'); ?></a><br /><br />
     <a href="http://www.ait-pro.com/aitpro-blog/4994/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-8-3/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 8.3', 'bulletproof-security'); ?></a><br /><br />
      <a href="http://www.ait-pro.com/aitpro-blog/4953/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-8-2/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 8.2', 'bulletproof-security'); ?></a><br /><br />
      <a href="http://www.ait-pro.com/aitpro-blog/4940/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-8-1/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 8.1', 'bulletproof-security'); ?></a><br /><br />  
     <a href="http://www.ait-pro.com/aitpro-blog/4926/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-8-0/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 8.0', 'bulletproof-security'); ?></a><br /><br />  
    <a href="http://www.ait-pro.com/aitpro-blog/4916/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-7-9/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 7.9', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4905/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-7-8/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 7.8', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4900/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-7-7/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 7.7', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4895/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-7-6/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 7.6', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4889/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-7-5/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 7.5', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4876/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-7-0/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 7.0', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4845/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-6-5/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 6.5', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4827/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-6-0/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 6.0', 'bulletproof-security'); ?></a><br /><br />
	<a href="http://www.ait-pro.com/aitpro-blog/4811/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-9/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.9', 'bulletproof-security'); ?></a><br /><br />
	<a href="http://www.ait-pro.com/aitpro-blog/4780/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-8/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.8/5.8.1/5.8.2', 'bulletproof-security'); ?></a><br /><br />
	<a href="http://www.ait-pro.com/aitpro-blog/4744/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-7/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.7/5.7.1/5.7.2', 'bulletproof-security'); ?></a><br /><br />
	<a href="http://www.ait-pro.com/aitpro-blog/4709/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-6/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.6/5.6.1', 'bulletproof-security'); ?></a><br /><br />	
    <a href="http://www.ait-pro.com/aitpro-blog/4683/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-5/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.5', 'bulletproof-security'); ?></a><br /><br />	
    <a href="http://www.ait-pro.com/aitpro-blog/4653/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-4/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.4/5.4.1', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4628/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-3/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.3/5.3.1/5.3.2/5.3.3', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4563/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-2/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.2/5.2.1/5.2.2', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4442/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-9/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.9', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4197/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-8/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.8/5.1.8.1/5.1.8.2/5.1.8.3/5.1.8.4', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4144/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-7/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.7', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4029/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-6/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.6', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3845/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-5/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.5', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3732/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-4/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.4', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3605/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-3" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.3', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3529/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-2/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.2', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3510/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-1/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.1', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3510/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-1/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/2835/bulletproof-security-pro/bulletproof-security-pro-features/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.0', 'bulletproof-security'); ?></a>
 </div>  
    
    </td>
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

<div id="bps-tabs-13">
<h2><?php _e('Sucuri SiteCheck - Free website malware & blacklist scan', 'bulletproof-security'); ?></h2>
<h3><?php _e('BPS is designed to protect your website from being hacked. If your website was already hacked prior to installing BPS then BPS will not automatically clean it up. Sucuri offers hacked website cleanup services.', 'bulletproof-security'); ?></h3>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">
    
    <div id="SucuriLogo" style="position:relative; top:0px; left:0px;"><img src="<?php echo plugins_url('/bulletproof-security/admin/images/sucuri-logo.png'); ?>" style="float:left; padding:0px 10px 0px 0px; margin:0px;" /><h3 style="font-size:14px;"><?php echo '<em>'.'"'.'...'; _e('the sheer nature of malware makes it very challenging to give you 100% certainty you will not get infected. The good news though is that we are doing everything in our power to ensure that 1 - you do not get infected, but 2 - if you do, we have the best solution to get you back on your feet.', 'bulletproof-security'); echo '"'.'</em><br> -- '; _e('Tony Perez, CFO Sucuri, LLC', 'bulletproof-security'); ?></h3><a href="http://sitecheck.sucuri.net/" target="_blank" title="Link opens in new browser window">Sucuri SiteCheck Scanner</a>
    </div>    
    
    </td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
</div>
        
<div id="bps-tabs-14">
<h2><?php _e('Website SEO', 'bulletproof-security'); ?></h2>
<h3><?php $text = __('Free, Premium Plugin and Theme testing, rating and review.', 'bulletproof-security').'<br><br><font color="blue"><strong>'.__('SPECIAL OFFER!!! ', 'bulletproof-security').'</strong></font>'.__('Search Engine Optimization (SEO) eBook for Beginners to Experienced Website Owners.', 'bulletproof-security'); echo $text; ?></h3>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">
    
    <div id="SucuriLogo" style="position:relative; top:0px; left:0px;"><img src="<?php echo plugins_url('/bulletproof-security/admin/images/themes-plugins-logo.png'); ?>" style="float:left; padding:0px 10px 30px 0px; margin:0px;" />
    <h3 style="font-size:14px;"><?php echo '<em>'.'"'.'...'; _e('We Test, Review & Rate Premium, Free and Paid WordPress Themes, Templates & Plugins Daily. 494 themes and 193 plugins have been tested to date....', 'bulletproof-security'); echo '"'.'</em><br> -- '; _e('Reza Shadpay, founder of themesplugins.com', 'bulletproof-security'); ?>
    <br /><br /><a href="http://www.themesplugins.com/" target="_blank" title="Link opens in new browser window">ThemesPlugins.com</a>
    </h3>
	
    <div id="ThemesPlugins" style="position:relative; top:0px; left:0px; margin-bottom:-30px;">
    <h3 style="font-size:14px;"><?php echo '<em>'.'"'.'...'; _e('SEO explained for Beginners to Experienced website owners. Simple and fully explained WhiteHat SEO techniques and methods that will get your website top Google page ranking positions.', 'bulletproof-security'); echo '"'.'</em><br> -- '; _e('Reza Shadpay, founder of themesplugins.com', 'bulletproof-security'); ?>
    <br /><br /><a href="http://www.themesplugins.com/downloads/seo-ebook-wordpress-book-seo/" target="_blank" title="Link opens in new browser window">SEO eBook</a>
    </h3>
    </div>    
    </div>

    </td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
</div>

<div id="AITpro-link">BulletProof Security <?php echo BULLETPROOF_VERSION; ?> Plugin by <a href="http://www.ait-pro.com/" target="_blank" title="AITpro Website Security">AITpro Website Security</a>
</div>
</div>
</div>
</div>