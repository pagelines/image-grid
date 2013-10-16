<?php
/*
	Plugin Name: Image Grid
	Demo: http://imagegrid.ahansson.com
	Description: A responsive Image Grid.
	Version: 2.1
	Author: Aleksander Hansson
	Author URI: http://ahansson.com
	v3: true
	PageLines: true
*/

add_action( 'init', 'image_grid_updater_init' );

/**
 * Load and Activate Plugin Updater Class.
 * @since 0.1.0
 */
function image_grid_updater_init() {

	/* Load Plugin Updater */
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/plugin-updater.php' );

	/* Updater Config */
	$config = array(
		'base'      => plugin_basename( __FILE__ ), //required
		'repo_uri'  => 'http://shop.ahansson.com/',  //required
		'repo_slug' => 'image-grid',  //required
	);

	/* Load Updater Class */
	new AHANSSON_Updater( $config );
}