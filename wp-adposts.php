<?php
defined('ABSPATH') OR exit('No direct script access allowed');
/**
 * Plugin Name: WP AdPosts
 * Plugin URI: https://ianhub.net/products/wordpress/wp-adposts
 * Author: Kader Bouyakoub
 * Author URI: https://github.com/bkader
 * Description: Allows you to create ads using codes or images and provides views and clicks counters, as well as locations management. You can even use the provided shortcode to insert ads into posts and pages.
 * Version: 1.0.0
 * License: GPLv3 or later
 * License URI: https://opensource.org/licenses/GPL-3.0
 * Text Domain: wp-adposts
 * Domain Path: /languages/
 *
 * @package 	wordpress
 * @subpackage 	Plugins
 * @category 	Ads Plugins
 * @author 		Kader Bouyakoub <bkader@mail.com>
 * @link 		https://github.com/bkader
 * @copyright 	Copyright (c) 2018, Kader Bouyakoub (https://github.com/bkader)
 * @license 	https://opensource.org/licenses/GPL-3.0  GNU General Public License
 * @since 		1.0.0
 */

if ( ! function_exists('Ianhub_WP_AdPosts')):

class Ianhub_WP_AdPosts
{
	/**
	 * The plugin version number.
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * The plugin's URL.
	 * @var string
	 */
	private $url;

	/**
	 * Standard ads sizes.
	 * @var array
	 */
	private $_standard_ads = array(
		'88x31'   => array(88,  31),
		'120x60'  => array(120, 60),
		'120x90'  => array(120, 90),
		'120x240' => array(120, 240),
		'120x600' => array(120, 600),
		'125x125' => array(125, 125),
		'160x600' => array(160, 600),
		'180x150' => array(180, 150),
		'234x60'  => array(234, 60),
		'250x250' => array(250, 250),
		'300x100' => array(300, 100),
		'300x250' => array(300, 250),
		'300x600' => array(300, 600),
		'336x280' => array(336, 280),
		'468x60'  => array(468, 60),
		'728x90'  => array(728, 90),
		'728x300' => array(728, 300),
	);

	/**
	 * Ads sizes used by default if the users did
	 * not provided some.
	 * @var array
	 */
	private $_ads_sizes = array('300x250', '468x60', '728x90');

	/**
	 * Ads sizes the users selected.
	 * @var array
	 */
	private $ads_sizes;

	// ------------------------------------------------------------------------
	// Plugin activation, deactivation and loaded hooks.
	// ------------------------------------------------------------------------

	/**
	 * This method is triggered upon plugin's activation.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	none
	 * @return 	void
	 */
	public function activate()
	{
		// We simple create the option.
		delete_option('wp_adposts_sizes');
		add_option('wp_adposts_sizes', $this->_ads_sizes);
	}

	// ------------------------------------------------------------------------

	/**
	 * This method is triggered upon plugin's deactivation.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	none
	 * @return 	void
	 */
	public function deactivate()
	{
		// We simple delete the option.
		delete_option('wp_adposts_sizes');
	}

	// ------------------------------------------------------------------------

	/**
	 * Class Constructor.
	 *
	 * This method will simply construct all necessary actions, filters
	 * and functions for this plugin to work.
	 *
	 * @since 	1.0.1
	 *
	 * @access 	public
	 * @param 	none
	 * @return 	void
	 */
	public function __construct()
	{
		// Store the plugin's directory URL.
		$this->url = plugin_dir_url(__FILE__);

		// Load plugin's text domain.
		load_plugin_textdomain(
			'wp-adposts',
			false,
			basename(dirname(__FILE__)).'/languages'
		);

		// Register the custom post type.
		add_action('init', array($this, 'register_ads_and_locations'));

		// For front-end.
		if ( ! is_admin())
		{
			// We enqueue our StyleSheet and JavaScript file.
			$this->enqueue_assets();

			// Add shortcode.
			add_shortcode('wp-adposts', array($this, 'shortcode'));
		}
		// For back-end.
		else
		{
			// Add plugin settings link.
			add_filter(
				'plugin_action_links_'.plugin_basename( __FILE__ ),
				array($this, 'settings_link')
			);

			// Add settings page.
			add_action('admin_menu', array($this, 'settings'));

			// Dashboard ads table columns.
			$this->dashboard_ads();

			// Dashboard ads location.
			$this->dashboard_locations();
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * register_ads_and_locations
	 *
	 * This method handles the registration of the custom post
	 * type "ad" as well as custom ad location taxonomy.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @return 	void
	 */
	public function register_ads_and_locations()
	{
		// We start by registering the post type.
		register_post_type('wpap_ad', array(
			'labels'             => array(
				'name'               => esc_html__('Ads', 'wp-adposts'),
				'singular_name'      => esc_html__('Ad', 'wp-adposts'),
				'menu_name'          => 'WP AdPosts',
				'name_admin_bar'     => 'WP AdPosts',
				'add_new'            => esc_html__('Add'),
				'add_new_item'       => esc_html__('Add'),
				'new_item'           => esc_html__('Add'),
				'edit_item'          => esc_html__('Edit'),
				'view_item'          => esc_html__('View'),
				'all_items'          => esc_html__('All Ads', 'wp-adposts'),
				'search_items'       => esc_html__('Search Ads', 'wp-adposts'),
				'not_found'          => esc_html__('No ads were found.', 'wp-adposts'),
				'not_found_in_trash' => esc_html__('No ads were found in trash.', 'wp-adposts')
			),
			'capability_type'    => 'post',
			'hierarchical'       => false,
			'menu_icon'          => 'dashicons-schedule',
			'menu_position'      => 5,
			'public'             => false,
			'publicly_queryable' => true,
			'query_var'          => false,
			'show_in_admin_bar'  => false,
			'show_in_menu'       => true,
			'show_ui'            => true,
			'supports'           => array('title', 'editor', 'author', 'thumbnail'),
		) );

		// Register ads locations taxonomy.
		register_taxonomy('wpap_location', 'wpap_ad', array(
			'labels'            => array(
				'name'          => esc_html__('Locations', 'wp-adposts'),
				'singular_name' => esc_html__('Location', 'wp-adposts'),
				'menu_name'     => esc_html__('Locations', 'wp-adposts'),
				'all_items'     => esc_html__('All ad locations', 'wp-adposts'),
				'edit_item'     => esc_html__('Edit ad location', 'wp-adposts'),
				'update_item'   => esc_html__('Update'),
				'add_new_item'  => esc_html__('Add'),
				'new_item_name' => esc_html__('Location Name', 'wp-adposts'),
				'search_items'  => esc_html__('Search location', 'wp-adposts'),
				'not_found'     => esc_html__('No location found.', 'wp-adposts'),
			),
			'hierarchical'      => false,
			'public'            => false,
			'show_admin_column' => true,
			'show_ui'           => true,
			'meta_box_cb' => array($this, 'locations_meta_box'),
		) );

		// We make sure to add thumbnail support.
		add_theme_support('post-thumbnails', array('wpap_ad'));

		// We add different ads banners sizes.
		foreach ($this->ads_sizes() as $id)
		{
			add_image_size(
				'ad-'.$id,
				$this->_standard_ads[$id][0],
				$this->_standard_ads[$id][1],
				true
			);
		}
		// add_image_size('ad-300x250', 300, 250, true);
		// add_image_size('ad-300x600', 300, 600, true);
		// add_image_size('ad-468x60', 468, 60, true);
		// add_image_size('ad-728x90', 728, 90, true);
	}

	// ------------------------------------------------------------------------

	/**
	 * Locations meta box callback.
	 *
	 * @since 	1.0.0
	 *
	 * @param 	none
	 * @return 	void
	 */
	public function locations_meta_box()
	{
		// Retrieve all ads locations.
		$locations = get_terms(array(
			'taxonomy'   => 'wpap_location',
			'hide_empty' => false,
		));

		// Display a message if no locations are found.
		if ( ! $locations)
		{
			echo esc_html_('No location found.', 'wp-adposts');
		}
		// Otherwise, display locations radio buttons.
		else
		{
			// Get the post ID to check the selected location.
			foreach ($locations as $term) {
?>
<label>
	<input type="radio" name="tax_input[wpap_location][]" id="in-wpap_location-<?php echo $term->term_id; ?>" value="<?php echo $term->term_id; ?>"<?php if ( has_term( $term->term_id, 'wpap_location', $post_id ) ): ?> checked<?php endif; ?>>
	<?php echo $term->name; ?>
</label><br />
<?php
			}
		}
	}

	// ------------------------------------------------------------------------
	// Plugin's settings link, page and form.
	// ------------------------------------------------------------------------

	/**
	 * Add the settings link to plugins page.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	array 	$links 	All plugins links.
	 * @return 	array
	 */
	public function settings_link( $links )
	{
		array_unshift(
			$links,
			// Settings link.
			'<a href="options-general.php?page=wp-adposts">'.__('Settings').'</a>',
			// Documentation link.
			'<a href="https://github.com/bkader/wp-adposts" target="_blank">Docs</a>'
		);

		return $links;
	}

	// ------------------------------------------------------------------------

	/**
	 * This method simply created the settings menu item.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	none
	 * @return 	void
	 */
	public function settings()
	{
		// We first add the options page and menu item.
		add_options_page(
			esc_html__('WP AdPosts Settings', 'wp-adposts'),
			'WP AdPosts',
			'manage_options',
			'wp-adposts',
			array($this, 'settings_page')
		);

		// Register the form update process.
		add_action('admin_init', array($this, 'settings_form'));
	}

	// ------------------------------------------------------------------------

	/**
	 * Plugin's settings page handler.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	none
	 * @return 	void
	 */
	public function settings_page()
	{
		// Get registered sizes from database.
		$option = get_option('wp_adposts_sizes', null);

		// If none are set, we use default ones.
		if ( ! $option) {
			$option = $this->_ads_sizes;
		}
?>
<div class="wrap">
	<h1><?php esc_html_e('WP AdPosts Settings', 'wp-adposts'); ?></h1>
	<p><?php esc_html_e('Ad banners are kind of standardized. There are several sizes from which you can choose. If none are selected, default ones will be used (300x250, 468x60 and 728x90). Note that for each selected size, and image size will be done to WordPress media manager.', 'wp-adposts'); ?></p>
	<form action="options.php" method="post"><?php

		settings_fields('wp-adposts-settings');
		do_settings_sections('wp-adposts-settings');

		foreach ($this->_standard_ads as $name => $sizes)
		{
?>
		<label for="ad_size_<?php echo $name; ?>"><input type="checkbox" name="wp_adposts_sizes[]" id="ad_size_<?php echo $name; ?>" value="<?php echo $name; ?>"<?php if (is_array($option) && in_array($name, $option)): ?> checked<?php endif; ?>> <?php echo $name; ?></label><br />
<?php
		}
		submit_button();
	?></form>
</div><!--/.wrap-->
<?php
	}

	// ------------------------------------------------------------------------

	/**
	 * We make sure to add the settings fields so it get stored into database.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	none
	 * @return 	void
	 */
	public function settings_form()
	{
		register_setting('wp-adposts-settings', 'wp_adposts_sizes');
	}

	// ------------------------------------------------------------------------

	/**
	 * This method is used to reduce access to database when getting selected
	 * ads sizes. In fact, it simply cache the stored sizes before returning
	 * them so that if we call it again, it will simply return cached ones.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	none
	 * @return 	array
	 */
	public function ads_sizes()
	{
		if ( ! isset($this->ads_sizes))
		{
			$this->ads_sizes = get_option('wp_adposts_sizes');
			if ( ! $this->ads_sizes)
			{
				$this->ads_sizes = $this->_ads_sizes;
			}
		}

		return $this->ads_sizes;
	}

	// ------------------------------------------------------------------------
	// Assets handler.
	// ------------------------------------------------------------------------

	/**
	 * Register plugins assets.
	 *
	 * @since 	1.0.0
	 *
	 * @param 	none
	 * @return 	void
	 */
	public function enqueue_assets()
	{
		// Enqueue the StyleSheet.
		wp_enqueue_style('wp-adposts', $this->url.'css/wp-adposts.min.css');

		/**
		 * Before queuing the script, we make sure to add the config
		 * object thats holds the AJAX URL and more.
		 */
		wp_register_script('wp-adposts', $this->url.'js/wp-adposts.min.js', array('jquery'));
		wp_localize_script('wp-adposts', 'AdPosts', array('ajaxUrl' => admin_url('admin-ajax.php')));
		wp_enqueue_script( 'wp-adposts' );
	}

	// ------------------------------------------------------------------------
	// Dashboard ads table.
	// ------------------------------------------------------------------------

	/**
	 * Alter ads dashboard table.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	void
	 * @return 	void
	 */
	public function dashboard_ads()
	{
		// Add table columns.
		add_filter('manage_edit-wpap_ad_columns', array($this, 'ads_table_columns'));

		// Fill table columns.
		add_filter('manage_wpap_ad_posts_custom_column', array($this, 'ads_table_content'), 10, 2);

		// We make ads table sortable.
		add_filter('manage_edit-wpap_ad_sortable_columns', array($this, 'ads_table_sortable'));

		// Add ads link (custom field) to ads.
		add_action('admin_init', array($this, 'ad_link_meta_box'));
		add_action('save_post', array($this, 'ad_link_save'));
	}

	// ------------------------------------------------------------------------

	/**
	 * Add needed ads table columns and remove unnecessary ones.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	array 	$columns 	The table columns.
	 * @return 	array
	 */
	public function ads_table_columns($columns)
	{
		// Remove unnecessary columns.
		if (function_exists('thumbs_rating_getlink'))
		{
			unset(
				$columns['thumbs_rating_up_count'],
				$columns['thumbs_rating_down_count']
			);
		}

		// Add columns.
		$columns['ad_views']  = esc_html__('Views', 'wp-adposts');
		$columns['ad_clicks'] = esc_html__('Clicks', 'wp-adposts');

		return $columns;
	}

	// ------------------------------------------------------------------------

	/**
	 * Fill ads table columns.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	string 	$column 	The column's ID.
	 * @param 	int 	$post_id 	The post's ID.
	 * @return 	void
	 */
	public function ads_table_content($column, $post_id)
	{
		switch ($column)
		{
			// Display ad views count.
			case 'ad_views':
				$views = get_post_meta($post_id, 'ad_view_count', true);
				if ($views == '')
				{
					$views = 0;
				}
				echo intval($views);
				break;

			// Display ad views count.
			case 'ad_clicks':
				$clicks = get_post_meta($post_id, 'ad_click_count', true);
				if ($clicks == '')
				{
					$clicks = 0;
				}
				echo intval($clicks);
				break;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Allows users to sort ads by views and/or clicks.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	array 	$columns 	Table columns.
	 * @return 	array
	 */
	public function ads_table_sortable($columns)
	{
		return wp_parse_args(array(
			'ad_views' => 'ad_view_count' ,
			'ad_clicks' => 'ad_click_count' ,
		), $columns);
	}

	// ------------------------------------------------------------------------

	/**
	 * Add the ad link custom field to post edit.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	none
	 * @return 	void
	 */
	public function ad_link_meta_box()
	{
		add_meta_box(
			'ad_link',
			esc_html__('Ad Link', 'wp-adposts'),
			array($this, 'ad_link_field'),
			'wpap_ad',
			'side',
			'high'
		);
	}

	// ------------------------------------------------------------------------

	/**
	 * Store ad link to post meta.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	none
	 * @return 	void
	 */
	public function ad_link_field()
	{
		global $post;
		$ad_link = get_post_meta($post->ID, 'ad_link', true);
?>
<input size="35" id="ad_link" name="ad_link" type="text" value="<?php echo $ad_link; ?>" />
<?php
	}

	// ------------------------------------------------------------------------

	/**
	 * Ad link save form.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	none
	 * @return 	void
	 */
	public function ad_link_save()
	{
		global $post;
		update_post_meta($post->ID, 'ad_link', $_POST['ad_link']);
	}

	// ------------------------------------------------------------------------
	// Dashboard locations.
	// ------------------------------------------------------------------------

	/**
	 * Add all necessary actions and filters for dashboard ads
	 * locations management.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	none
	 * @return 	void
	 */
	public function dashboard_locations()
	{
		// Add custom fields to locations creation form.
		add_action('wpap_location_add_form_fields', array($this, 'locations_add_fields'));

		// Add custom fields to locations edit form.
		add_action('wpap_location_edit_form_fields', array($this, 'locations_edit_fields'));

		// Add location form handler.
		add_action('created_wpap_location', array($this, 'locations_add_form'), 10, 2);

		// Edit location form handler.
		add_action('edited_wpap_location', array($this, 'locations_edit_form'), 10, 2);
	}

	// ------------------------------------------------------------------------

	/**
	 * This method adds new fields to locations creation form.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	none
	 * @return 	void
	 */
	public function locations_add_fields()
	{
?><div class="form-field term-group">
	<label for="ad_width"><?php esc_html_e( 'Ads Width', 'wp-adposts' ); ?></label>
	<input type="text" name="ad_width" id="ad_width" value="">
	<p><?php printf(
		esc_html__( 'This determines the maximum ads %s.', 'wp-adposts' ),
		esc_html__( 'width', 'wp-adposts' )
		); ?></p>
</div>
<div class="form-field term-group">
	<label for="ad_height"><?php esc_html_e( 'Ads Height', 'wp-adposts' ); ?></label>
	<input type="text" name="ad_height" id="ad_width" value="">
	<p><?php printf(
		esc_html__( 'This determines the maximum ads %s.', 'wp-adposts' ),
		esc_html__( 'height', 'wp-adposts' )
		); ?></p>
</div><?php
	}

	/**
	 * This method adds new fields to locations edit form.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	object 	$location 	The location object.
	 * @return 	void
	 */
	public function locations_edit_fields($location)
	{
		$ad_width = get_term_meta($location->term_id, 'ad_width', true);
		$ad_height = get_term_meta($location->term_id, 'ad_height', true);
?>
<tr class="form-field">
	<th scope="row"><label for="ad_width"><?php esc_html_e( 'Ads Width', 'wp-adposts' ); ?></label></th>
	<td>
		<input type="text" name="ad_width" id="ad_width" size="3" value="<?php echo $ad_width; ?>">
		<p class="description"><?php printf(
		esc_html__( 'This determines the maximum ads %s.', 'wp-adposts' ),
		esc_html__( 'width', 'wp-adposts' )
		); ?></p>
	</td>
</tr>
<tr class="form-field">
	<th scope="row"><label for="ad_height"><?php esc_html_e( 'Ads Height', 'wp-adposts' ); ?></label></th>
	<td>
		<input type="text" name="ad_height" id="ad_height" size="3" value="<?php echo $ad_height; ?>">
		<p class="description"><?php printf(
		esc_html__( 'This determines the maximum ads %s.', 'wp-adposts' ),
		esc_html__( 'height', 'wp-adposts' )
		); ?></p>
	</td>
</tr><?php
	}

	// ------------------------------------------------------------------------

	/**
	 * Handles location creation form.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param
	 * @param
	 * @return 	void
	 */
	public function locations_add_form()
	{
		$keys = array('ad_width', 'ad_height');
		foreach ($keys as $key)
		{
			if (isset($_POST[$key]))
			{
				add_term_meta(
					$term_id,
					$key,
					str_replace(
						array('px', 'pt', 'em', 'rem'),
						'',
						$_POST[$key]
					)
				);
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Handles location update form.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param
	 * @param
	 * @return 	void
	 */
	public function locations_edit_form()
	{
		$keys = array('ad_width', 'ad_height');
		foreach ($keys as $key)
		{
			if (isset($_POST[$key]))
			{
				update_term_meta(
					$term_id,
					$key,
					str_replace(
						array( 'px', 'pt', 'em', 'rem' ),
						'',
						$_POST[$key]
					)
				);
			}
		}
	}

	// ------------------------------------------------------------------------
	// Ads click.
	// ------------------------------------------------------------------------

	/**
	 * Handles ads clicks.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	none
	 * @return 	void.
	 */
	public function ad_click()
	{
		// Get the post ID first.
		$post_id = absint($_POST['ad_id']);
		if ( ! $post_id)
		{
			echo 'error';
			die();
		}

		// Get click counts.
		$counter = get_post_meta($post_id, 'ad_click_count', true);

		// Process initial status.
		$status = false;

		// If it's not found, we make sure to add it.
		if ( ! $counter)
		{
			$counter = 1;
			delete_post_meta('ad_click_count', $post_id);
			$status = update_post_meta($post_id, 'ad_click_count', $counter);
		}
		// Otherwise, we make sure to increment clicks.
		else
		{
			$counter = absint($counter);
			$counter++;
			$status = update_post_meta($post_id, 'ad_click_count', $counter);
		}

		/**
		 * Because the "update_post_meta" will return the meta id
		 * if newly created or TRUE if the meta was updated, we only
		 * check that it did not return FALSE because that's what's
		 * returned if something went wrong.
		 */
		echo ($status === false) ? 'error' : 'success';
		die();
	}

	// ------------------------------------------------------------------------
	// Shortcode and ad display.
	// ------------------------------------------------------------------------

	/**
	 * The shortcode used to display ads within posts and pages.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	array 	$attrs 	Shortcode attributes.
	 * @return 	string
	 */
	public function shortcode($attrs)
	{
		if (empty($attrs) OR ! isset($attrs['location']))
		{
			return null;
		}

		return $this->ad_display($attrs['location']);
	}

	// ------------------------------------------------------------------------

	/**
	 * Display random ads at the selected location.
	 *
	 * @since 	1.0.0
	 *
	 * @access 	public
	 * @param 	string 	$ad_location 	The location where the ad is displayed.
	 * @return 	string
	 */
	public function ad_display($ad_location = null)
	{
		// If no ad_location is provided, nothing to do.
		if ( ! $ad_location)
		{
			return null;
		}

		// Get the location by it's slug and make sure it exists.
		$location = get_term_by('slug', $ad_location, 'wpap_location');
		if ( ! $location)
		{
			return null;
		}

		// Get location dimensions.
		$width = get_term_meta($location->term_id, 'ad_width', true);
		if ($width == '')
		{
			$width = null;
		}

		$height = get_term_meta($location->term_id, 'ad_height', true);
		if ($height == '')
		{
			$height = 'auto';
		}

		// Let's now retrieve ads.
		$ads = get_posts(array(
			'posts_per_page' => 1,
			'orderby'        => 'rand',
			'post_type'      => 'wpap_ad',
			'wpap_location'  => $location->slug,
		));

		// If there are no ads, nothing to do.
		if ( ! $ads)
		{
			return null;
		}

		// Let's prepare the ad post.
		$ad = $ads[0];
		setup_postdata($ad);

		// Get the ad URL.
		$url = get_post_meta($ad->ID, 'ad_link', true);

		// Get the ad thumbnail.
		$image_id = get_post_thumbnail_id($ad->ID);

		// If found, we prepare the content.
		if ($image_id) {
			// Prepare the width.
			if ( null == $width OR false !== strpos( $width, '%' ) )
			{
				$image_src = wp_get_attachment_image_src($image_id);
			}
			elseif ( ! is_numeric($height))
			{
				$image_src = wp_get_attachment_image_src($image_id, "ad-{$width}");
			}
			else
			{
				$image_src = wp_get_attachment_image_src($image_id, "ad-{$width}x{$height}");
			}

			$content = '<img src="'. $image_src[0] .'" alt="'.$ad->post_title.'" />';
		}
		else
		{
			$content = $ad->post_content;
		}

		// Now we make sure to update view counter.
		$views = get_post_meta( $ad->ID, 'ad_view_count', true );
		if ( ! $views ) {
			$views = 1;
			delete_post_meta( $ad->ID, 'ad_view_count' );
			add_post_meta( $ad->ID, 'ad_view_count', $views );
		} else {
			$views++;
			update_post_meta( $ad->ID, 'ad_view_count', $views );
		}

		// Now we prepare the final output.
		$output = '<div id="wp-adpost-'.$ad->ID.'" class="wp-adpost wp-adpost-'.$ad->ID;

		if ( null !== $width OR false === strpos( $width, '%' ) ) {
			$output .= ' wp-adpost-'.$width;
			if ( $height !== '' && $height !== 'auto') {
				$output .= 'x'.$height;
			}
		}

		$output .= ' wp-adpost-'.$ad->post_name.'">';
		$output .= '<a class="wp-adpost-link" data-ad="'.$ad->ID.'" href="' . $url . '" target="_blank">';
		$output .= $content;
		$output .= '</a>';
		$output .= '</div>'; // end of ad-above-header

		wp_reset_postdata();
		return $output;
	}

}

// Activation and deactivation hooks.
register_activation_hook(__FILE__, array('Ianhub_WP_AdPosts', 'activate'));
register_deactivation_hook(__FILE__, array('Ianhub_WP_AdPosts', 'deactivate'));

/**
 * This function returns the true Ianhub_WP_AdPosts class
 * instance. Use it like you would use a global variable.
 *
 * @example <?php $wpwp = ianhub_wpap(); ?>
 * @since 	1.0.0
 *
 * @param 	none
 * @return 	object
 */
function ianhub_wpap()
{
	global $ianhub_wpap;

	if ( ! isset($ianhub_wpap))
	{
		$ianhub_wpap = new Ianhub_WP_AdPosts();
	}

	return $ianhub_wpap;
}

// Initialize.
ianhub_wpap();

// Adding the AJAX handler.
add_action('wp_ajax_nopriv_ad_click', array('Ianhub_WP_AdPosts', 'ad_click'));
add_action('wp_ajax_ad_click', array('Ianhub_WP_AdPosts', 'ad_click'));

/**
 * Used to display ads at the selected location.
 * @param 	string 	$location
 * @return 	string
 */
function wp_adposts($location = null)
{
	echo ianhub_wpap()->ad_display($location);
}

endif; // En of the class: Ianhub_WP_AdPosts.
