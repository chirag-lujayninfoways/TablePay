<?php
/**
 * Admin Actions
 *
 * @package     RPRESS
 * @subpackage  Admin/Actions
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Processes all RPRESS actions sent via POST and GET by looking for the 'rpress-action'
 * request and running do_action() to call the function
 *
 * @since 1.0.0
 * @return void
 */
function rpress_process_actions() {
	if ( isset( $_POST['rpress-action'] ) ) {
		do_action( 'rpress_' . $_POST['rpress-action'], $_POST );
	}

	if ( isset( $_GET['rpress-action'] ) ) {
		do_action( 'rpress_' . $_GET['rpress-action'], $_GET );
	}
}
add_action( 'admin_init', 'rpress_process_actions' );