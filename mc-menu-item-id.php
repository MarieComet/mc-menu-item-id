<?php
/**
 * Plugin Name: Menu Item ID
 * Plugin URI: https://github.com/MarieComet/mc-menu-item-id
 * Description: This WordPress plugin adds an ID field to menu items and displays them.
 * Author: Marie Comet
 * Author URI: https://www.mariecomet.fr
 * Version: 1.0.0
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: mc-menu-item-id
 * Domain Path: /languages/
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class MC_Menu_Item_Id {

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {

		// load the plugin translation files
		add_action( 'init', array( $this, 'textdomain' ) );
		
		// Add custom fields to menu
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'add_custom_fields_meta' ) );
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'add_custom_fields' ), 10, 4 );

		// Save menu custom fields
		add_action( 'wp_update_nav_menu_item', array( $this, 'update_custom_nav_fields' ), 10, 3 );
		
		// Edit menu walker
		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'edit_walker' ), 10, 2 );

		// Add ID to link item
		add_filter( 'nav_menu_link_attributes', array( $this, 'add_id_attributes_link' ), 10, 3 );

	}


	/**
	 * Load the plugin's text domain
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function textdomain() {
		load_plugin_textdomain( 'mc-menu-item-id', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Add custom menu style fields data to the menu.
	 *
	 * @access public
	 * @param object $menu_item A single menu item.
	 * @return object The menu item.
	 */
	public function add_custom_fields_meta( $menu_item ) {

		$menu_item->item_id = get_post_meta( $menu_item->ID, '_menu_item_id', true );

		return $menu_item;
	}

	/**
	 * Add custom ID field to the menu edit screen.
	 *
	 * @access public
	 * @param object $menu_item A single menu item.
	 * @return object The menu item.
	 */
	public function add_custom_fields( $id, $item, $depth, $args ) { ?>

		<p class="field-css-id description description-thin">
			<label for="edit-menu-item-id-<?php echo esc_attr( $item->ID ); ?>">
				<?php _e( 'CSS ID (optional, must be unique)', 'mc-menu-item-id' ); ?><br />
				<input type="text" id="edit-menu-item-id-<?php echo esc_attr( $item->ID ); ?>" class="widefat code edit-menu-item-id" name="menu-item-item_id[<?php echo esc_attr( $item->ID ); ?>]" value="<?php echo esc_attr( $item->item_id ); ?>" />
			</label>
		</p>
	<?php }

	/**
	 * Add the custom menu style fields menu item data to fields in database.
	 *
	 * @access public
	 * @param string|int $menu_id         The menu ID.
	 * @param string|int $menu_item_db_id The menu ID from the db.
	 * @param array      $args            The arguments array.
	 * @return void
	 */
	public function update_custom_nav_fields( $menu_id, $menu_item_db_id, $args ) {

		$check = 'item_id';

		if(!isset($_POST['menu-item-'.$check][$menu_item_db_id])) {
			$_POST['menu-item-'.$check][$menu_item_db_id] = '';
		}

		$value = sanitize_html_class( wp_unslash( $_POST['menu-item-'.$check][$menu_item_db_id] ) );
		update_post_meta( $menu_item_db_id, '_menu_'.$check, $value );
	}


	/**
	 * Function to replace normal edit nav walker.
	 *
	 * @return string Class name of new navwalker
	 */
	public function edit_walker() {
		require_once plugin_dir_path( __FILE__ ) . 'walker/class-walker-edit-item-id.php';
		return 'Walker_Nav_Menu_Item_Id';
	}


	/**
	 * Add ID to link item
	 * $atts - HTML attributes in an associative array
	 * $item - Object containing item details. E.G: If the link is to a page $item will be a WP_Post object
	 * $args - Array containing config with desired markup of nav item
	 */
	public function add_id_attributes_link( $atts, $item, $args ) {
		
		if ( isset( $item->item_id ) && '' ==! $item->item_id ) {
			$atts['id'] = $item->item_id;
		}
		return $atts;
	}

}

new MC_Menu_Item_Id();