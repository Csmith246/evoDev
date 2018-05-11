<?php
// Direct calls to this file are Forbidden when core files are not present
if ( !function_exists ('add_action') ) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}

function bulletproof_security_admin_init() {
global $wpdb, $wp_version, $blog_id;
$Stable_name = $wpdb->prefix . "bpspro_seclog_ignore";
$Ltable_name = $wpdb->prefix . "bpspro_login_security";
$DBBtable_name = $wpdb->prefix . "bpspro_db_backup";

	if ( is_multisite() && $blog_id != 1 ) {

	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $Ltable_name ) ) != $Ltable_name ) {
	
	$sql = "CREATE TABLE $Ltable_name (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  status VARCHAR(60) DEFAULT '' NOT NULL,
  user_id VARCHAR(60) DEFAULT '' NOT NULL,
  username VARCHAR(60) DEFAULT '' NOT NULL,
  public_name VARCHAR(250) DEFAULT '' NOT NULL,
  email VARCHAR(100) DEFAULT '' NOT NULL,
  role VARCHAR(15) DEFAULT '' NOT NULL,
  human_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  login_time VARCHAR(10) DEFAULT '' NOT NULL,
  lockout_time VARCHAR(10) DEFAULT '' NOT NULL,
  failed_logins VARCHAR(2) DEFAULT '' NOT NULL,
  ip_address VARCHAR(45) DEFAULT '' NOT NULL,
  hostname VARCHAR(60) DEFAULT '' NOT NULL,
  request_uri VARCHAR(255) DEFAULT '' NOT NULL,
  UNIQUE KEY id (id)
    );";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	}	

	} else {

	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $Stable_name ) ) != $Stable_name ) {	
	
	$sql = "CREATE TABLE $Stable_name (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  user_agent_bot text NOT NULL,
  UNIQUE KEY id (id)
    );";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	}

	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $Ltable_name ) ) != $Ltable_name ) {	
	
	$sql = "CREATE TABLE $Ltable_name (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  status VARCHAR(60) DEFAULT '' NOT NULL,
  user_id VARCHAR(60) DEFAULT '' NOT NULL,
  username VARCHAR(60) DEFAULT '' NOT NULL,
  public_name VARCHAR(250) DEFAULT '' NOT NULL,
  email VARCHAR(100) DEFAULT '' NOT NULL,
  role VARCHAR(15) DEFAULT '' NOT NULL,
  human_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  login_time VARCHAR(10) DEFAULT '' NOT NULL,
  lockout_time VARCHAR(10) DEFAULT '' NOT NULL,
  failed_logins VARCHAR(2) DEFAULT '' NOT NULL,
  ip_address VARCHAR(45) DEFAULT '' NOT NULL,
  hostname VARCHAR(60) DEFAULT '' NOT NULL,
  request_uri VARCHAR(255) DEFAULT '' NOT NULL,
  UNIQUE KEY id (id)
    );";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	}

	// last job, next job is updated by the cron - job size is the total size of all tables selected in that job
	$DBBtable_name = $wpdb->prefix . "bpspro_db_backup";
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $DBBtable_name ) ) != $DBBtable_name ) {	
	
	$sql = "CREATE TABLE $DBBtable_name (
  bps_id bigint(20) NOT NULL auto_increment,
  bps_table_name text default '' NOT NULL,
  bps_desc text default '' NOT NULL,
  bps_job_type varchar(9) default '' NOT NULL,
  bps_frequency varchar(7) default '' NOT NULL,
  bps_last_job varchar(30) default '' NOT NULL,
  bps_next_job varchar(30) default '' NOT NULL,
  bps_next_job_unix varchar(10) default '' NOT NULL,  
  bps_email_zip varchar(10) default '' NOT NULL,
  bps_job_created datetime default '0000-00-00 00:00:00' NOT NULL,
  UNIQUE KEY bps_id (bps_id)
    );";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	}
	}
	
	// whitelist BPS DB options 
	register_setting('bulletproof_security_options', 'bulletproof_security_options', 'bulletproof_security_options_validate');
	register_setting('bulletproof_security_options_DBB_log', 'bulletproof_security_options_DBB_log', 'bulletproof_security_options_validate_DBB_log');
	register_setting('bulletproof_security_options_autolock', 'bulletproof_security_options_autolock', 'bulletproof_security_options_validate_autolock');
	register_setting('bulletproof_security_options_customcode', 'bulletproof_security_options_customcode', 'bulletproof_security_options_validate_customcode');
	//register_setting('bulletproof_security_options_net_correction', 'bulletproof_security_options_net_correction', 'bulletproof_security_options_validate_net_correction');
	register_setting('bulletproof_security_options_customcode_WPA', 'bulletproof_security_options_customcode_WPA', 'bulletproof_security_options_validate_customcode_WPA');
	register_setting('bulletproof_security_options_login_security', 'bulletproof_security_options_login_security', 'bulletproof_security_options_validate_login_security');
	register_setting('bulletproof_security_options_htaccess_res', 'bulletproof_security_options_htaccess_res', 'bulletproof_security_options_validate_htaccess_res');
	register_setting('bulletproof_security_options_maint_mode', 'bulletproof_security_options_maint_mode', 'bulletproof_security_options_validate_maint_mode');
	register_setting('bulletproof_security_options_theme_skin', 'bulletproof_security_options_theme_skin', 'bulletproof_security_options_validate_theme_skin');
	register_setting('bulletproof_security_options_db_backup', 'bulletproof_security_options_db_backup', 'bulletproof_security_options_validate_db_backup');
	register_setting('bulletproof_security_options_mynotes', 'bulletproof_security_options_mynotes', 'bulletproof_security_options_validate_mynotes');
	register_setting('bulletproof_security_options_email', 'bulletproof_security_options_email', 'bulletproof_security_options_validate_email');			

	// Register BPS js
	wp_register_script( 'bps-js', plugins_url('/bulletproof-security/admin/js/bulletproof-security-admin-5.js'));
				
	// Register BPS stylesheet - UI Theme Skins
	$UIoptions = get_option('bulletproof_security_options_theme_skin');
	
	if ( version_compare( $wp_version, '3.8', '>=' ) ) {
		
		switch ( $UIoptions['bps_ui_theme_skin'] ) {
    		case 'blue':
				wp_register_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));
			break;
    		case 'grey':
				wp_register_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-grey.css'));
			break;
    		case 'black':
				wp_register_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-black.css'));
			break;
		default: 		
				wp_register_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));		
		}
		
	} else {
		
		wp_register_style('bps-css', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-blue.css'));
	}

	// Create BPS Backup Folder
	if ( !is_dir( WP_CONTENT_DIR . '/bps-backup' ) ) {
		@mkdir( WP_CONTENT_DIR . '/bps-backup', 0755, true );
		@chmod( WP_CONTENT_DIR . '/bps-backup/', 0755 );
	}
	
	// Create master backups folder
	if ( !is_dir( WP_CONTENT_DIR . '/bps-backup/master-backups' ) ) {
		@mkdir( WP_CONTENT_DIR . '/bps-backup/master-backups', 0755, true );
		@chmod( WP_CONTENT_DIR . '/bps-backup/master-backups/', 0755 );
	}

	// Create Deny all .htaccess files - /bps-backup htaccess file is recursive and will protect all subfolders
	$bps_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/deny-all.htaccess';
	$bps_denyall_htaccess_renamed = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/.htaccess';
	$security_log_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/security-log/.htaccess';
	$system_info_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/system-info/.htaccess';
	$login_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/login/.htaccess';
	$MMode_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/maintenance/.htaccess';
	$DBB_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/db-backup-security/.htaccess';
	$core_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/core/.htaccess';	
	$bps_ARHtaccess = WP_CONTENT_DIR . '/bps-backup/.htaccess';
	
	if ( !file_exists($bps_ARHtaccess) ) {
		@copy($bps_denyall_htaccess, $bps_ARHtaccess);
	}
	if ( !file_exists($bps_denyall_htaccess_renamed) ) {
		@copy($bps_denyall_htaccess, $bps_denyall_htaccess_renamed);	
	}
	if ( !file_exists($security_log_denyall_htaccess) ) {
		@copy($bps_denyall_htaccess, $security_log_denyall_htaccess);	
	}
	if ( !file_exists($system_info_denyall_htaccess) ) {
		@copy($bps_denyall_htaccess, $system_info_denyall_htaccess);
	}
	if ( !file_exists($login_denyall_htaccess) ) {
		@copy($bps_denyall_htaccess, $login_denyall_htaccess);
	}
	if ( !file_exists($MMode_denyall_htaccess) ) {
		@copy($bps_denyall_htaccess, $MMode_denyall_htaccess);			
	}
	if ( !file_exists($DBB_denyall_htaccess) ) {
		@copy($bps_denyall_htaccess, $DBB_denyall_htaccess);
	}
	if ( !file_exists($core_denyall_htaccess) ) {
		@copy($bps_denyall_htaccess, $core_denyall_htaccess);
	}

	// Create logs folder
	if( !is_dir( WP_CONTENT_DIR . '/bps-backup/logs' ) ) {
		@mkdir( WP_CONTENT_DIR . '/bps-backup/logs', 0755, true );
		@chmod( WP_CONTENT_DIR . '/bps-backup/logs/', 0755 );
	}

	// Create backups folder with randomly generated folder name & save the backups folder name to the DB
	bpsPro_create_db_backup_folder();

	// Create the Security / HTTP error log in /logs
	$bpsProLog = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/http_error_log.txt';
	$bpsProLogARQ = WP_CONTENT_DIR . '/bps-backup/logs/http_error_log.txt';
	
	if ( !file_exists($bpsProLogARQ) ) {
		@copy($bpsProLog, $bpsProLogARQ);
	}	

	// Create the DB Backup log in /logs
	$bpsProDBBLog = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/db_backup_log.txt';
	$bpsProDBBLogARQ = WP_CONTENT_DIR . '/bps-backup/logs/db_backup_log.txt';
	
	if ( !file_exists($bpsProDBBLogARQ) ) {
		@copy($bpsProDBBLog, $bpsProDBBLogARQ);
	}

	// Load scripts and styles only on BPS specified pages
	add_action('load-bulletproof-security/admin/core/options.php', 'bulletproof_security_load_settings_page');
	add_action('load-bulletproof-security/admin/login/login.php', 'bulletproof_security_load_settings_page_login');
	add_action('load-bulletproof-security/admin/security-log/security-log.php', 'bulletproof_security_load_settings_page_security_log');
	add_action('load-bulletproof-security/admin/system-info/system-info.php', 'bulletproof_security_load_settings_page_system_info');
	add_action('load-bulletproof-security/admin/maintenance/maintenance.php', 'bulletproof_security_load_settings_page_maintenance');
	add_action('load-bulletproof-security/admin/theme-skin/theme-skin.php', 'bulletproof_security_load_settings_page_theme_skin');
	add_action('load-bulletproof-security/admin/db-backup-security/db-backup-security.php', 'bulletproof_security_load_settings_page_db_backup_security');	
}

// BPS Menu
function bulletproof_security_admin_menu() {
global $blog_id;
	
	if ( current_user_can('manage_options') ) {
	
	// Network / Multisite display partial BPS menus
	if ( is_multisite() && $blog_id != 1 ) {

	add_menu_page(__('BulletProof Security Settings', 'bulletproof-security'), __('BPS Security', 'bulletproof-security'), 'manage_options', 'bulletproof-security/admin/login/login.php', '', plugins_url('bulletproof-security/admin/images/bps-icon-small.png'));
	add_submenu_page('bulletproof-security/admin/login/login.php', __('Login Security', 'bulletproof-security'), __('Login Security', 'bulletproof-security'), 'manage_options', 'bulletproof-security/admin/login/login.php' );
	add_submenu_page('bulletproof-security/admin/login/login.php', __('Maintenance Mode', 'bulletproof-security'), __('Maintenance Mode', 'bulletproof-security'), 'manage_options', 'bulletproof-security/admin/maintenance/maintenance.php' );
	add_submenu_page('bulletproof-security/admin/login/login.php', __('System Info', 'bulletproof-security'), __('System Info', 'bulletproof-security'), 'manage_options', 'bulletproof-security/admin/system-info/system-info.php' );
	add_submenu_page('bulletproof-security/admin/login/login.php', __('UI Theme Skin', 'bulletproof-security'), __('UI Theme Skin', 'bulletproof-security'), 'manage_options', 'bulletproof-security/admin/theme-skin/theme-skin.php' );
	
	} else {

	add_menu_page(__('BulletProof Security ~ htaccess Core', 'bulletproof-security'), __('BPS Security', 'bulletproof-security'), 'manage_options', 'bulletproof-security/admin/core/options.php', '', plugins_url('bulletproof-security/admin/images/bps-icon-small.png'));
	add_submenu_page('bulletproof-security/admin/core/options.php', __('BulletProof Security ~ htaccess Core', 'bulletproof-security'), __('htaccess Core', 'bulletproof-security'), 'manage_options', 'bulletproof-security/admin/core/options.php' );
	add_submenu_page('bulletproof-security/admin/core/options.php', __('Login Security ', 'bulletproof-security'), __('Login Security', 'bulletproof-security'), 'manage_options', 'bulletproof-security/admin/login/login.php' );
	add_submenu_page('bulletproof-security/admin/core/options.php', __('DB Backup & Security', 'bulletproof-security'), __('DB Backup', 'bulletproof-security'), 'manage_options', 'bulletproof-security/admin/db-backup-security/db-backup-security.php' );
	add_submenu_page('bulletproof-security/admin/core/options.php', __('Security Log', 'bulletproof-security'), __('Security Log', 'bulletproof-security'), 'manage_options', 'bulletproof-security/admin/security-log/security-log.php' );
	add_submenu_page('bulletproof-security/admin/core/options.php', __('Maintenance Mode', 'bulletproof-security'), __('Maintenance Mode', 'bulletproof-security'), 'manage_options', 'bulletproof-security/admin/maintenance/maintenance.php' );
	add_submenu_page('bulletproof-security/admin/core/options.php', __('System Info', 'bulletproof-security'), __('System Info', 'bulletproof-security'), 'manage_options', 'bulletproof-security/admin/system-info/system-info.php' );
	add_submenu_page('bulletproof-security/admin/core/options.php', __('UI Theme Skin', 'bulletproof-security'), __('UI Theme Skin', 'bulletproof-security'), 'manage_options', 'bulletproof-security/admin/theme-skin/theme-skin.php' );
	}
	}
}

// dequeue any rogue plugin or theme scripts & styles from loading in BPS pages and breaking stuff
function bpsPro_kill_rogue_plugin_theme_scripts_styles() {
global $wp_scripts, $wp_styles;
    
	$script_handles = array( 'bps-js', 'admin-bar', 'jquery', 'jquery-ui-core', 'jquery-ui-tabs', 'jquery-ui-dialog', 'jquery-form', 'jquery-ui-accordion', 'jquery-effects-core', 'jquery-effects-blind', 'jquery-effects-explode', 'common', 'utils', 'svg-painter', 'wp-auth-check', 'debug-bar' );

	$style_handles = array( 'bps-css', 'bps-css-38', 'admin-bar', 'colors', 'ie', 'wp-auth-check', 'debug-bar' );

	if ( preg_match( '/page=bulletproof-security/', $_SERVER['REQUEST_URI'], $matches) ) {		

		foreach( $wp_scripts->queue as $handle ) {
		
			if ( !in_array( $handle, $script_handles ) ) {
				wp_dequeue_script( $handle );
        		// uncomment line below to see all the script handles that are being blocked on BPS plugin pages
				//echo $handle . ' | ';
			}
		}
	
		foreach( $wp_styles->queue as $handle ) {
        	
			if ( !in_array( $handle, $style_handles ) ) {
				wp_dequeue_style( $handle );
				// uncomment line below to see all the style handles that are being blocked on BPS plugin pages
				//echo $handle . ' | ';
			}	
		}
	}
}

add_action( 'admin_enqueue_scripts', 'bpsPro_kill_rogue_plugin_theme_scripts_styles' );

// Create Backup folder with randomly generated folder name and update DB with folder name
function bpsPro_create_db_backup_folder() {
$options = get_option('bulletproof_security_options_db_backup');

	if ( $options['bps_db_backup_folder'] && $options['bps_db_backup_folder'] != '' ) {
		return;	
	}
	
	$source = WP_CONTENT_DIR . '/bps-backup';

	if ( is_dir($source) ) {
		
		$iterator = new DirectoryIterator($source);
			
		foreach ( $iterator as $folder ) {
			if ( $folder->isDir() && !$folder->isDot() && preg_match( '/backups_[a-zA-Z0-9]/', $folder ) ) {
				return;
			}
		}
				
		$str = '1234567890abcdefghijklmnopqrstuvxyzABCDEFGHIJKLMNOPQRSTUVWXYZU3xt8Eb9Qw422hG0yv1LCT2Pzub7';
		$folder_obs = substr( str_shuffle($str), 0, 15 );
		@mkdir( WP_CONTENT_DIR . '/bps-backup/backups_' . $folder_obs, 0755, true );
		@chmod( WP_CONTENT_DIR . '/bps-backup/backups_' . $folder_obs . '/', 0755 );
				
		//@mkdir( WP_CONTENT_DIR . '/bps-backup/backups_' . $folder_obs . '/db-diff', 0755, true );
		//@chmod( WP_CONTENT_DIR . '/bps-backup/backups_' . $folder_obs . '/db-diff/', 0755 );

		$dbb_options = 'bulletproof_security_options_db_backup';
		$bps_db_backup_folder = addslashes( WP_CONTENT_DIR . '/bps-backup/backups_' . $folder_obs );
		$bps_db_backup_download_link = ( WP_CONTENT_DIR . '/bps-backup/backups_' . $folder_obs );
		$bps_db_backup_download_link = content_url( '/bps-backup/backups_' ) . $folder_obs . '/';
		
		$DBB_Options = array(
		'bps_db_backup' => 'On', 
		'bps_db_backup_description' => '', 
		'bps_db_backup_folder' => $bps_db_backup_folder, 
		'bps_db_backup_download_link' => $bps_db_backup_download_link, 
		'bps_db_backup_job_type' => '', 
		'bps_db_backup_frequency' => '', 		 
		'bps_db_backup_start_time_hour' => '', 
		'bps_db_backup_start_time_weekday' => '', 
		'bps_db_backup_start_time_month_date' => '', 
		'bps_db_backup_email_zip' => '', 
		'bps_db_backup_delete' => '', 
		'bps_db_backup_status_display' => 'No DB Backups' 
		);	
	
		if ( !get_option( $dbb_options ) ) {	
		
			foreach( $DBB_Options as $key => $value ) {
				update_option('bulletproof_security_options_db_backup', $DBB_Options);
			}
			
		} else {

			foreach( $DBB_Options as $key => $value ) {
				update_option('bulletproof_security_options_db_backup', $DBB_Options);
			}	
		}			
	}
}

// Loads Settings for H-Core
function bulletproof_security_load_settings_page() {
global $bulletproof_security, $wp_version;
$UIoptions = get_option('bulletproof_security_options_theme_skin');
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('jquery-form');
	wp_enqueue_script('jquery-ui-accordion');
	wp_enqueue_script('jquery-effects-core');
	wp_enqueue_script('jquery-effects-blind');
	wp_enqueue_script('jquery-effects-explode');
	wp_enqueue_script('bps-js');

	// Enqueue BPS stylesheet
	if ( version_compare( $wp_version, '3.8', '>=' ) ) {
		
		switch ( $UIoptions['bps_ui_theme_skin'] ) {
    		case 'blue':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));
			break;
    		case 'grey':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-grey.css'));;
			break;
    		case 'black':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-black.css'));
			break;
		default: 		
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));		
		}
		
	} else {
		
		wp_enqueue_style('bps-css', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-blue.css'));
	}
}

// Loads Settings for BPS Login Security
function bulletproof_security_load_settings_page_login() {
global $bulletproof_security, $wp_version;
$UIoptions = get_option('bulletproof_security_options_theme_skin');
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('jquery-form');
	wp_enqueue_script('jquery-ui-accordion');
	wp_enqueue_script('jquery-effects-core');
	wp_enqueue_script('jquery-effects-blind');
	wp_enqueue_script('jquery-effects-explode');
	wp_enqueue_script('bps-js');

	// Enqueue BPS stylesheet
	if ( version_compare( $wp_version, '3.8', '>=' ) ) {
		
		switch ( $UIoptions['bps_ui_theme_skin'] ) {
    		case 'blue':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));
			break;
    		case 'grey':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-grey.css'));;
			break;
    		case 'black':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-black.css'));
			break;
		default: 		
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));		
		}
		
	} else {
		
		wp_enqueue_style('bps-css', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-blue.css'));
	}	
}

// Loads Settings for Security Log page - Enqueue BPS scripts and styles
function bulletproof_security_load_settings_page_security_log() {
global $bulletproof_security, $wp_version;
$UIoptions = get_option('bulletproof_security_options_theme_skin');
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('jquery-form');
	wp_enqueue_script('jquery-ui-accordion');
	wp_enqueue_script('jquery-effects-core');
	wp_enqueue_script('jquery-effects-blind');
	wp_enqueue_script('jquery-effects-explode');
	wp_enqueue_script('bps-js');

	// Enqueue BPS stylesheet
	if ( version_compare( $wp_version, '3.8', '>=' ) ) {
		
		switch ( $UIoptions['bps_ui_theme_skin'] ) {
    		case 'blue':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));
			break;
    		case 'grey':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-grey.css'));;
			break;
    		case 'black':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-black.css'));
			break;
		default: 		
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));		
		}
		
	} else {
		
		wp_enqueue_style('bps-css', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-blue.css'));
	}
}

// Loads Settings for BPS Pro Maintenance - Enqueue BPS scripts and styles
function bulletproof_security_load_settings_page_maintenance() {
global $bulletproof_security, $wp_version;
$UIoptions = get_option('bulletproof_security_options_theme_skin');
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('jquery-form');
	wp_enqueue_script('jquery-ui-accordion');
	wp_enqueue_script('jquery-effects-core');
	wp_enqueue_script('jquery-effects-blind');
	wp_enqueue_script('jquery-effects-explode');
	wp_enqueue_script('bps-js');

	// Enqueue BPS stylesheet
	if ( version_compare( $wp_version, '3.8', '>=' ) ) {
		
		switch ( $UIoptions['bps_ui_theme_skin'] ) {
    		case 'blue':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));
			break;
    		case 'grey':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-grey.css'));;
			break;
    		case 'black':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-black.css'));
			break;
		default: 		
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));		
		}
		
	} else {
		
		wp_enqueue_style('bps-css', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-blue.css'));
	}
}

// Loads Settings for System Info page - Enqueue BPS scripts and styles
function bulletproof_security_load_settings_page_system_info() {
global $bulletproof_security, $wp_version;
$UIoptions = get_option('bulletproof_security_options_theme_skin');
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('jquery-form');
	wp_enqueue_script('jquery-ui-accordion');
	wp_enqueue_script('jquery-effects-core');
	wp_enqueue_script('jquery-effects-blind');
	wp_enqueue_script('jquery-effects-explode');
	wp_enqueue_script('bps-js');

	// Enqueue BPS stylesheet
	if ( version_compare( $wp_version, '3.8', '>=' ) ) {
		
		switch ( $UIoptions['bps_ui_theme_skin'] ) {
    		case 'blue':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));
			break;
    		case 'grey':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-grey.css'));;
			break;
    		case 'black':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-black.css'));
			break;
		default: 		
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));		
		}
		
	} else {
		
		wp_enqueue_style('bps-css', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-blue.css'));
	}
}

// Loads Settings for BPS Pro UI Theme Skin - Enqueue BPS scripts and styles
function bulletproof_security_load_settings_page_theme_skin() {
global $bulletproof_security, $wp_version;
$UIoptions = get_option('bulletproof_security_options_theme_skin');
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('jquery-form');
	wp_enqueue_script('jquery-ui-accordion');
	wp_enqueue_script('jquery-effects-core');
	wp_enqueue_script('jquery-effects-blind');
	wp_enqueue_script('jquery-effects-explode');
	wp_enqueue_script('bps-js');
	
	// Enqueue BPS stylesheet
	if ( version_compare( $wp_version, '3.8', '>=' ) ) {
		
		switch ( $UIoptions['bps_ui_theme_skin'] ) {
    		case 'blue':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));
			break;
    		case 'grey':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-grey.css'));;
			break;
    		case 'black':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-black.css'));
			break;
		default: 		
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));		
		}
		
	} else {
		
		wp_enqueue_style('bps-css', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-blue.css'));
	}
}

// Loads Settings for BPS Pro DB Backup & Security - Enqueue BPS scripts and styles
function bulletproof_security_load_settings_page_db_backup_security() {
global $bulletproof_security, $wp_version;
$UIoptions = get_option('bulletproof_security_options_theme_skin');
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('jquery-form');
	wp_enqueue_script('jquery-ui-accordion');
	wp_enqueue_script('jquery-effects-core');
	wp_enqueue_script('jquery-effects-blind');
	wp_enqueue_script('jquery-effects-explode');
	wp_enqueue_script('bps-js');
	
	// Enqueue BPS stylesheet
	if ( version_compare( $wp_version, '3.8', '>=' ) ) {
		
		switch ( $UIoptions['bps_ui_theme_skin'] ) {
    		case 'blue':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));
			break;
    		case 'grey':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-grey.css'));;
			break;
    		case 'black':
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-black.css'));
			break;
		default: 		
				wp_enqueue_style('bps-css-38', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-wp3.8-blue.css'));		
		}
		
	} else {
		
		wp_enqueue_style('bps-css', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-blue.css'));
	}
}

function bulletproof_security_install() {
global $bulletproof_security, $bps_version;
$previous_install = get_option('bulletproof_security_options');
	
	if ( $previous_install ) {
	if ( version_compare($previous_install['version'], $bps_version, '<') )
	remove_role('denied');
	}
}

// Deactivation - remove/delete nothing at this point
function bulletproof_security_deactivation() {
// nothing needs to removed on deactivation for now
}

// Partial Uninstall - BPS later version will have the option of complete removal in addition to a BPS Pro upgrade uninstall
// Currently all options and files are not deleted on uninstall as a courtesy to BPS Pro customers
function bulletproof_security_uninstall() {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php');
	delete_option( 'bulletproof_security_options' );
	delete_transient( 'bulletproof-security_info' );
}

/* 
// This uninstall function will completely remove BPS files, DB Options & Tables
// IMPORTANT: either do not delete db backup options or add the Pro Setup Wizard function to get the existing DB backups folder name
function bulletproof_security_complete_uninstall() {
global $wpdb, $current_user;
require_once( ABSPATH . 'wp-admin/includes/plugin.php');

$user_id = $current_user->ID;
$Stable_name = $wpdb->prefix . "bpspro_seclog_ignore";
$Ltable_name = $wpdb->prefix . "bpspro_login_security";
$DBBtable_name = $wpdb->prefix . "bpspro_db_backup";
$RootHtaccess = ABSPATH . '.htaccess';
$RootHtaccessBackup = WP_CONTENT_DIR . '/bps-backup/master-backups/root.htaccess';
$wpadminHtaccess = ABSPATH . 'wp-admin/.htaccess';
$wpadminHtaccessBackup = WP_CONTENT_DIR . '/bps-backup/master-backups/wpadmin.htaccess';
//$options = get_option('bulletproof_security_options');

	if ( file_exists($RootHtaccess) ) {
		copy($RootHtaccess, $RootHtaccessBackup);
	}
	if ( file_exists($wpadminHtaccess) ) {
		copy($wpadminHtaccess, $wpadminHtaccessBackup);
	}

	delete_transient( 'bulletproof-security_info' );
	
	delete_option('bulletproof_security_options');
	delete_option('bulletproof_security_options_customcode');
	delete_option('bulletproof_security_options_customcode_WPA');
	delete_option('bulletproof_security_options_maint');
	delete_option('bulletproof_security_options_maint_mode');
	delete_option('bulletproof_security_options_mynotes');
	delete_option('bulletproof_security_options_email');
	delete_option('bulletproof_security_options_autolock');
	delete_option('bulletproof_security_options_login_security');
	delete_option('bulletproof_security_options_theme_skin');
	delete_option('bulletproof_security_options_db_backup');
	delete_option('bulletproof_security_options_DBB_log');
	delete_option('bulletproof_security_options_htaccess_res');
	delete_option('bulletproof_security_options_net_correction');
	// will be adding this new upgrade notice option later
	// delete_option('bulletproof_security_options_upgrade_notice');	
	$wpdb->query("DROP TABLE IF EXISTS $Stable_name");
	$wpdb->query("DROP TABLE IF EXISTS $Ltable_name");
	$wpdb->query("DROP TABLE IF EXISTS $DBBtable_name");
	delete_user_meta($user_id, 'bps_ignore_iis_notice');
	delete_user_meta($user_id, 'bps_ignore_sucuri_notice');
	delete_user_meta($user_id, 'bps_ignore_BLC_notice');
	delete_user_meta($user_id, 'bps_ignore_PhpiniHandler_notice');
	delete_user_meta($user_id, 'bps_ignore_Permalinks_notice');
	delete_user_meta($user_id, 'bps_brute_force_login_protection_notice');
	delete_user_meta($user_id, 'bps_speed_boost_cache_notice');
	delete_user_meta($user_id, 'bps_xmlrpc_ddos_notice');
	delete_user_meta($user_id, 'bps_author_enumeration_notice');
	delete_user_meta($user_id, 'bps_ignore_wpfirewall2_notice');
	delete_user_meta($user_id, 'bps_hud_NetworkActivationAlert_notice');
	@unlink($RootHtaccess);
	@unlink($wpadminHtaccess);
}
*/

// Validate BPS options 
function bulletproof_security_options_validate($input) {  
	$options = get_option('bulletproof_security_options');  
	$options['bps_blank'] = wp_filter_nohtml_kses($input['bps_blank']);
			
	return $options;  
}

// Validate BPS options - BPS .49.9 New Maintenance Mode Form options
function bulletproof_security_options_validate_maint_mode($input) {  
	$options = get_option('bulletproof_security_options_maint_mode');  
	$options['bps_maint_on_off'] = wp_filter_nohtml_kses($input['bps_maint_on_off']);
	$options['bps_maint_countdown_timer'] = wp_filter_nohtml_kses($input['bps_maint_countdown_timer']);
	$options['bps_maint_countdown_timer_color'] = wp_filter_nohtml_kses($input['bps_maint_countdown_timer_color']);
	$options['bps_maint_time'] = wp_filter_nohtml_kses($input['bps_maint_time']);
	$options['bps_maint_retry_after'] = wp_filter_nohtml_kses($input['bps_maint_retry_after']);
	$options['bps_maint_frontend'] = wp_filter_nohtml_kses($input['bps_maint_frontend']);
	$options['bps_maint_backend'] = wp_filter_nohtml_kses($input['bps_maint_backend']);
	$options['bps_maint_ip_allowed'] = wp_filter_nohtml_kses($input['bps_maint_ip_allowed']);
	$options['bps_maint_text'] = esc_html($input['bps_maint_text']);
	$options['bps_maint_background_images'] = wp_filter_nohtml_kses($input['bps_maint_background_images']);
	$options['bps_maint_center_images'] = wp_filter_nohtml_kses($input['bps_maint_center_images']);
	$options['bps_maint_background_color'] = wp_filter_nohtml_kses($input['bps_maint_background_color']);
	$options['bps_maint_show_visitor_ip'] = wp_filter_nohtml_kses($input['bps_maint_show_visitor_ip']);
	$options['bps_maint_show_login_link'] = wp_filter_nohtml_kses($input['bps_maint_show_login_link']);
	$options['bps_maint_dashboard_reminder'] = wp_filter_nohtml_kses($input['bps_maint_dashboard_reminder']);	
	$options['bps_maint_countdown_email'] = wp_filter_nohtml_kses($input['bps_maint_countdown_email']);
	$options['bps_maint_email_to'] = trim(wp_filter_nohtml_kses($input['bps_maint_email_to']));
	$options['bps_maint_email_from'] = trim(wp_filter_nohtml_kses($input['bps_maint_email_from']));
	$options['bps_maint_email_cc'] = trim(wp_filter_nohtml_kses($input['bps_maint_email_cc']));
	$options['bps_maint_email_bcc'] = trim(wp_filter_nohtml_kses($input['bps_maint_email_bcc']));	
	$options['bps_maint_mu_entire_site'] = wp_filter_nohtml_kses($input['bps_maint_mu_entire_site']);
	$options['bps_maint_mu_subsites_only'] = wp_filter_nohtml_kses($input['bps_maint_mu_subsites_only']);
	
	return $options;  
}

// Validate BPS options - Options.php - Edit/Uploads/Downloads page - Root .htaccess file AutoLock 
function bulletproof_security_options_validate_autolock($input) {  
	$options = get_option('bulletproof_security_options_autolock');  
	$options['bps_root_htaccess_autolock'] = wp_filter_nohtml_kses($input['bps_root_htaccess_autolock']);
		
	return $options;  
}

// Validate BPS options - BPS Custom Code - Root .htaccess
function bulletproof_security_options_validate_customcode($input) {  
	$options = get_option('bulletproof_security_options_customcode');  
	// TOP PHP/PHP.INI HANDLER/CACHE CODE
	$options['bps_customcode_one'] = esc_html($input['bps_customcode_one']);
	$options['bps_customcode_server_signature'] = esc_html($input['bps_customcode_server_signature']);
	$options['bps_customcode_directory_index'] = esc_html($input['bps_customcode_directory_index']);
	// BRUTE FORCE LOGIN PAGE PROTECTION
	$options['bps_customcode_server_protocol'] = esc_html($input['bps_customcode_server_protocol']);	
	$options['bps_customcode_error_logging'] = esc_html($input['bps_customcode_error_logging']);
	$options['bps_customcode_deny_dot_folders'] = esc_html($input['bps_customcode_deny_dot_folders']);	
	$options['bps_customcode_admin_includes'] = esc_html($input['bps_customcode_admin_includes']);
	$options['bps_customcode_wp_rewrite_start'] = esc_html($input['bps_customcode_wp_rewrite_start']);
	$options['bps_customcode_request_methods'] = esc_html($input['bps_customcode_request_methods']);
	// PLUGIN/THEME SKIP/BYPASS RULES
	$options['bps_customcode_two'] = esc_html($input['bps_customcode_two']);
	$options['bps_customcode_timthumb_misc'] = esc_html($input['bps_customcode_timthumb_misc']);
	$options['bps_customcode_bpsqse'] = esc_html($input['bps_customcode_bpsqse']);
	if ( is_multisite() ) {
	$options['bps_customcode_wp_rewrite_end'] = esc_html($input['bps_customcode_wp_rewrite_end']);
	}
	$options['bps_customcode_deny_files'] = esc_html($input['bps_customcode_deny_files']);
	// BOTTOM HOTLINKING/FORBID COMMENT SPAMMERS/BLOCK BOTS/BLOCK IP/REDIRECT CODE
	$options['bps_customcode_three'] = esc_html($input['bps_customcode_three']);

	return $options;  
}


// Validate BPS options - BPS Custom Code - WP-admin .htaccess
function bulletproof_security_options_validate_customcode_WPA($input) {  
	$options = get_option('bulletproof_security_options_customcode_WPA');  
	$options['bps_customcode_deny_files_wpa'] = esc_html($input['bps_customcode_deny_files_wpa']);
	$options['bps_customcode_one_wpa'] = esc_html($input['bps_customcode_one_wpa']);
	$options['bps_customcode_two_wpa'] = esc_html($input['bps_customcode_two_wpa']);
	$options['bps_customcode_bpsqse_wpa'] = esc_html($input['bps_customcode_bpsqse_wpa']);		
	
	return $options;  
}

// Validate BPS options - BPS "My Notes" settings 
function bulletproof_security_options_validate_mynotes($input) {  
	$options = get_option('bulletproof_security_options_mynotes');  
	$options['bps_my_notes'] = esc_html($input['bps_my_notes']);
		
	return $options;  
}

// Validate BPS options - Login Security & Monitoring
function bulletproof_security_options_validate_login_security($input) {  
	$BPSoptions = get_option('bulletproof_security_options_login_security');  
	$BPSoptions['bps_max_logins'] = trim(wp_filter_nohtml_kses($input['bps_max_logins']));
	$BPSoptions['bps_lockout_duration'] = trim(wp_filter_nohtml_kses($input['bps_lockout_duration']));
	$BPSoptions['bps_manual_lockout_duration'] = trim(wp_filter_nohtml_kses($input['bps_manual_lockout_duration']));
	$BPSoptions['bps_max_db_rows_display'] = trim(wp_filter_nohtml_kses($input['bps_max_db_rows_display']));
	$BPSoptions['bps_login_security_OnOff'] = wp_filter_nohtml_kses($input['bps_login_security_OnOff']);
	$BPSoptions['bps_login_security_logging'] = wp_filter_nohtml_kses($input['bps_login_security_logging']);
	$BPSoptions['bps_login_security_errors'] = wp_filter_nohtml_kses($input['bps_login_security_errors']);
	$BPSoptions['bps_login_security_pw_reset'] = wp_filter_nohtml_kses($input['bps_login_security_pw_reset']);		
	$BPSoptions['bps_login_security_sort'] = wp_filter_nohtml_kses($input['bps_login_security_sort']);	
	
	return $BPSoptions;  
}

// Validate BPS options - BPS Free Email Alerts
function bulletproof_security_options_validate_email($input) {  
	$options = get_option('bulletproof_security_options_email');  
	$options['bps_send_email_to'] = trim(wp_filter_nohtml_kses($input['bps_send_email_to']));
	$options['bps_send_email_from'] = trim(wp_filter_nohtml_kses($input['bps_send_email_from']));
	$options['bps_send_email_cc'] = trim(wp_filter_nohtml_kses($input['bps_send_email_cc']));
	$options['bps_send_email_bcc'] = trim(wp_filter_nohtml_kses($input['bps_send_email_bcc']));
	$options['bps_login_security_email'] = wp_filter_nohtml_kses($input['bps_login_security_email']);
	//$options['bps_upgrade_email'] = wp_filter_nohtml_kses($input['bps_upgrade_email']);		
	$options['bps_security_log_size'] = wp_filter_nohtml_kses($input['bps_security_log_size']);
	$options['bps_security_log_emailL'] = wp_filter_nohtml_kses($input['bps_security_log_emailL']);	
	$options['bps_dbb_log_email'] = wp_filter_nohtml_kses($input['bps_dbb_log_email']);	
	$options['bps_dbb_log_size'] = wp_filter_nohtml_kses($input['bps_dbb_log_size']);		
	
	return $options;  
}

// Validate BPS options - UI Theme Skin 
function bulletproof_security_options_validate_theme_skin($input) {  
	$options = get_option('bulletproof_security_options_theme_skin');  
	$options['bps_ui_theme_skin'] = wp_filter_nohtml_kses($input['bps_ui_theme_skin']);
			
	return $options;  
}

// Validate BPS options - DB Backup
function bulletproof_security_options_validate_db_backup($input) {  
	$options = get_option('bulletproof_security_options_db_backup');  
	$options['bps_db_backup'] = wp_filter_nohtml_kses($input['bps_db_backup']);
	$options['bps_db_backup_description'] = trim(wp_filter_nohtml_kses($input['bps_db_backup_description']));		
	$options['bps_db_backup_folder'] = trim(wp_filter_nohtml_kses($input['bps_db_backup_folder']));
	$options['bps_db_backup_download_link'] = trim(wp_filter_nohtml_kses($input['bps_db_backup_download_link']));				
	$options['bps_db_backup_job_type'] = wp_filter_nohtml_kses($input['bps_db_backup_job_type']);	
	$options['bps_db_backup_frequency'] = wp_filter_nohtml_kses($input['bps_db_backup_frequency']);	
	$options['bps_db_backup_start_time_hour'] = wp_filter_nohtml_kses($input['bps_db_backup_start_time_hour']);
	$options['bps_db_backup_start_time_weekday'] = wp_filter_nohtml_kses($input['bps_db_backup_start_time_weekday']);
	$options['bps_db_backup_start_time_month_date'] = wp_filter_nohtml_kses($input['bps_db_backup_start_time_month_date']);
	$options['bps_db_backup_email_zip'] = wp_filter_nohtml_kses($input['bps_db_backup_email_zip']);		
	$options['bps_db_backup_delete'] = wp_filter_nohtml_kses($input['bps_db_backup_delete']);		
	$options['bps_db_backup_status_display'] = wp_filter_nohtml_kses($input['bps_db_backup_status_display']); // hidden form option
	
	return $options;  
}

// Validate BPS options - DB Backup Log Last Modified Time DB
function bulletproof_security_options_validate_DBB_log($input) {  
	$options = get_option('bulletproof_security_options_DBB_log');  
	$options['bps_dbb_log_date_mod'] = wp_filter_nohtml_kses($input['bps_dbb_log_date_mod']);
		
	return $options;  
}

// Validate BPS options - Hosting that does not allow wp-admin .htaccess files - Go Daddy Managed WordPress hosting
function bulletproof_security_options_validate_htaccess_res($input) {  
	$options = get_option('bulletproof_security_options_htaccess_res');  
	$options['bps_wpadmin_restriction'] = wp_filter_nohtml_kses($input['bps_wpadmin_restriction']);
		
	return $options;  
}

/*
// Validate BPS options - Network/Multisite root .htaccess file code correction checks
function bulletproof_security_options_validate_net_correction($input) {  
	$options = get_option('bulletproof_security_options_net_correction');  
	$options['bps_net_automagic'] = wp_filter_nohtml_kses($input['bps_net_automagic']);
	$options['bps_net_activated'] = wp_filter_nohtml_kses($input['bps_net_activated']);		
	
	return $options;  
}
*/

?>