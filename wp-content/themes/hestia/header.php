<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the page header div.
 *
 * @package Hestia
 * @since Hestia 1.0
 */
?><!DOCTYPE html>
<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

?>
<html <?php language_attributes(); ?>>
<head>
<meta charset='<?php bloginfo( 'charset' ); ?>'>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php endif; ?>

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">

<link rel="stylesheet" href="http://localhost:81\evoDev\wp-content\themes\hestia\assets\AOS\aos.css" />
<script src="http://localhost:81\evoDev\wp-content\themes\hestia\assets\AOS\aos.js"></script>


<script>
	AOS.init();
	console.log("AOS INIT");
</script>

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<a name="Home"></a>

	<?php
	$wrapper_div_classes = 'wrapper ';
	if ( is_single() ) {
		$wrapper_div_classes .= join( ' ', get_post_class() );
	}
	?>

	<div class="<?php echo esc_attr( $wrapper_div_classes ); ?>">

	<?php
	$header_class = '';
	$hide_top_bar = get_theme_mod( 'hestia_top_bar_hide', true );
	if ( (bool) $hide_top_bar === false ) {
		$header_class .= 'header-with-topbar';
	}
	?>
		<header class="header <?php echo esc_attr( $header_class ); ?>">
			<?php do_action( 'hestia_do_header' ); ?>
