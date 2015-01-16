<?php
/*
Plugin Name: RDM Ajax Sidebar Menu
Plugin URI: https://github.com/RainyDayMedia/wp-ajax-sidebar
Description: Create simple sidebar menus that load content into the main panel with ajax.
Version: 1.0.0
Author: Todd Miller
Author URI: http://rainydaymedia.net/
License: GPL2

Copyright 2014  Todd Miller  (email : todd@rainydaymedia.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Register the widget with WordPress
 */
add_action( 'widgets_init', 'register_rdm_ajax_sidebar_widget' );
function register_rdm_ajax_sidebar_widget() {
    register_widget( 'RDM_AjaxSidebar_Widget' );
}

/**
 * Build out the page content ajax magic
 */
add_action( 'wp_ajax_load_page_content', 'rdmAjaxPageContent' );
add_action( 'wp_ajax_nopriv_load_page_content', 'rdmAjaxPageContent' );
function rdmAjaxPageContent()
{
	global $wpdb, $post, $id, $withcomments;

	$withcomments = 1;

	$pageID = $_POST['pageID'];
	$post   = get_post( $pageID );
	setup_postdata($post);

	// pull in the basic content template
	get_template_part('content', 'page');

	// if we have comments or comments are open, show the template
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}

	die();
}

/**
 * Widget class extends base WordPress Widget class.
 */
class RDM_AjaxSidebar_Widget extends WP_Widget {

	/**
	 * Constructor that register the widget with wordpress.
	 */
	function __construct() {
		parent::__construct(
			'rdm_widget', // Base ID
			'RDM Ajax Sidebar Menu', // Name
			array( 'description' => 'An RDM Widget to create an Ajax powered Sidebar Menu.' ) // Args
		);
	}

	/**
	 * Echo the widget content.
	 *
	 * Subclasses should over-ride this function to generate their widget code.
	 *
	 * @access public
	 *
	 * @param array $args     Display arguments including before_title, after_title,
	 *                        before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance )
	{
		// Get menu
		$menu_items = ! empty( $instance['nav_menu'] ) ? wp_get_nav_menu_items( $instance['nav_menu'] ) : false;

		// if no menu is selected, just quit
		if ( !$menu_items ) {
			return;
		}

		// enqueue styles
	/* TODO FONT AWESOME SHOULD BE AN OPTION */
		wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' );
		wp_enqueue_style( 'rdm-ajax-sidebar-style', plugins_url( 'rdm-ajax-sidebar.min.css', __FILE__ ) );

		// enqueue our script here, so its not included on pages that don't have the widget
		wp_enqueue_script( 'rdm-ajax-sidebar-script', plugins_url( 'rdm-ajax-sidebar.min.js', __FILE__ ), array( 'jquery' ), '', true );
		wp_localize_script( 'rdm-ajax-sidebar-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )));
		add_action( 'wp_footer', array( $this, 'initialize_scripts' ), 99 );

		// build the parent-child menu arrays
		$sorted_menu_items = $items_with_children = array();
		foreach ( $menu_items as $item ) {
			if ( $item->menu_item_parent ) {
				if ( !array_key_exists( $item->menu_item_parent, $items_with_children ) ) {
					$items_with_children[$item->menu_item_parent] = array();
				}
				$items_with_children[$item->menu_item_parent][] = $item;
			} else {
				$sorted_menu_items[$item->menu_order] = $item;
			}
		}

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		echo $args['before_widget'];

		echo $args['before_title'] . $title . $args['after_title'];
		?>

		<div id="" class="">
			<ul id="rdm-ajax-sidebar-menu-items" class="rdm-ajax-sidebar-menu-items">

			<?php foreach ( $sorted_menu_items as $item ) : ?>
				<?php $hasChildren = array_key_exists( $item->ID, $items_with_children ); ?>

				<?php if ( $hasChildren ) : ?>
					<li data-child="<?php echo $item->ID; ?>" class="rdm-ajax-sidebar-parent"><?php echo $item->title; ?> <span><i class="fa fa-caret-down"></i></span></li>
				<?php else : ?>
					<?php if ( $item->type == "custom" ) : ?>
						<li class="rdm-ajax-sidebar-parent"><a href="<?php echo $item->url; ?>" target="_blank"><?php echo $item->title; ?></a></li>
					<?php else : ?>
						<li data-page-id="<?php echo $item->object_id; ?>" class="rdm-ajax-sidebar-parent"><a href="<?php echo $item->url; ?>"><?php echo $item->title; ?></a></li>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( $hasChildren ) : ?>
					<ul id="<?php echo $item->ID; ?>" class="rdm-ajax-sidebar-child-menu-items">

					<?php foreach ( $items_with_children[$item->ID] as $child_item ) : ?>
						<?php if ( $child_item->type == "custom" ) : ?>
							<li class="rdm-ajax-sidebar-child"><a href="<?php echo $child_item->url; ?>" target="_blank"><?php echo $child_item->title; ?></a></li>
						<?php else : ?>
							<li data-page-id="<?php echo $child_item->object_id; ?>" class="rdm-ajax-sidebar-child"><a href="<?php echo $child_item->url; ?>"><?php echo $child_item->title; ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>

					</ul>
				<?php endif; ?>

			<?php endforeach; ?>

			</ul>
		</div>

		<?php

		echo $args['after_widget'];
	}

	/**
	 * Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly. The newly-calculated
	 * value of `$instance` should be returned. If false is returned, the instance won't be
	 * saved/updated.
	 *
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            {@see WP_Widget::form()}.
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = array();
		if ( ! empty( $new_instance['title'] ) ) {
			$instance['title'] = strip_tags( stripslashes($new_instance['title']) );
		}
		if ( ! empty( $new_instance['nav_menu'] ) ) {
			$instance['nav_menu'] = (int) $new_instance['nav_menu'];
		}
		return $instance;
	}

	/**
	 * Output the settings update form.
	 *
	 * @access public
	 *
	 * @param array $instance Current settings.
	 * @return string Default return is 'noform'.
	 */
	public function form( $instance )
	{
		// enqueue admin scripts
		//wp_enqueue_script( 'rdm-ajax-sidebar-script', plugins_url( 'rdm-ajax-sidebar.min.js', __FILE__ ), array( 'jquery' ), '', true );
		//add_action( 'wp_footer', function() {
			//echo '<script>rdmAjaxSidebarAdmin.init();</script>';
		//}, 99 );
		//add_action( 'wp_footer', array( $this, 'init_admin_script' ), 99 );

		// get our current options
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$nav_menu = isset( $instance['nav_menu'] ) ? $instance['nav_menu'] : '';

		// Get menus
		$menus = wp_get_nav_menus();

		// If no menus exists, direct the user to go and create some.
		if ( !$menus ) {
			echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.'), admin_url('nav-menus.php') ) .'</p>';
			return;
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('nav_menu'); ?>"><?php _e('Select Menu:'); ?></label>
			<select id="<?php echo $this->get_field_id('nav_menu'); ?>" name="<?php echo $this->get_field_name('nav_menu'); ?>">
				<option value="0"><?php _e( '&mdash; Select &mdash;' ) ?></option>
		<?php
			foreach ( $menus as $menu ) {
				echo '<option value="' . $menu->term_id . '"'
					. selected( $nav_menu, $menu->term_id, false )
					. '>'. esc_html( $menu->name ) . '</option>';
			}
		?>
			</select>
		</p>
		<?php
	}

	/**
	 * Initialize the JQuery class
	 *
	 * @access public
	 */
	public function initialize_scripts()
	{
		echo '<script>rdmAjaxSidebar.init( "rdm-ajax-sidebar-menu-items", "main" );</script>';
	}
}
