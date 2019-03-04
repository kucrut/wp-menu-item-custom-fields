<?php
/**
 * Menu item custom fields example
 *
 * Copy this file into your wp-content/mu-plugins directory.
 *
 * @package Menu_Item_Custom_Fields_Checkbox_Example
 * @version 0.2.0
 * @author Dzikri Aziz <kvcrvt@gmail.com>
 *
 *
 * Plugin name: Menu Item Custom Fields Example
 * Plugin URI: https://github.com/kucrut/wp-menu-item-custom-fields
 * Description: Example usage of Menu Item Custom Fields in plugins/themes
 * Version: 0.2.0
 * Author: Dzikri Aziz
 * Author URI: http://kucrut.org/
 * License: GPL v2
 * Text Domain: menu-item-custom-fields-example
 */


/**
 * Sample menu item metadata
 *
 * This class demonstrate the usage of Menu Item Custom Fields in plugins/themes.
 *
 * @since 0.1.0
 */
class Menu_Item_Custom_Fields_Checkbox_Example {

	/**
	 * Holds our custom fields
	 *
	 * @var    array
	 * @access protected
	 * @since  Menu_Item_Custom_Fields_Checkbox_Example 0.2.0
	 */
	protected static $fields = array();


	/**
	 * Initialize plugin
	 */
	public static function init() {
		add_action( 'wp_nav_menu_item_custom_fields', array( __CLASS__, '_fields' ), 10, 4 );
		add_action( 'wp_update_nav_menu_item', array( __CLASS__, '_save' ), 10, 3 );
		add_filter( 'manage_nav-menus_columns', array( __CLASS__, '_columns' ), 99 );

		self::$fields = array(
			'field-01' => __( 'Custom Field #1', 'menu-item-custom-fields-example' ),
			'field-02' => __( 'Custom Field #2', 'menu-item-custom-fields-example' ),
		);
	}


	/**
	 * Save custom field value
	 *
	 * @wp_hook action wp_update_nav_menu_item
	 *
	 * @param int   $menu_id         Nav menu ID
	 * @param int   $menu_item_db_id Menu item ID
	 * @param array $menu_item_args  Menu item data
	 */
	public static function _save( $menu_id, $menu_item_db_id, $menu_item_args ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		check_admin_referer( 'update-nav_menu', 'update-nav-menu-nonce' );
		foreach ( self::$fields as $_key => $label ) {
			$key = sprintf( 'menu-item-%s', $_key );
			// Sanitize
			if ( ! empty( $_POST[ $key ][ $menu_item_db_id ] ) ) {
				// Do some checks here...
				$value = 'yes';
			} else {
				$value = '';
			}

			// Update
			update_post_meta( $menu_item_db_id, $key, $value );
		}
	}

	/**
	 * Print field
	 *
	 * @param object $item  Menu item data object.
	 * @param int    $depth Depth of menu item. Used for padding.
	 * @param array  $args  Menu item args.
	 * @param int    $id    Nav menu ID.
	 *
	 * @return string Form fields
	 */
	public static function _fields( $id, $item, $depth, $args ) {
		foreach ( self::$fields as $_key => $label ) {
			$key   = sprintf( 'menu-item-%s', $_key );
			$id    = sprintf( 'edit-%s-%s', $key, $item->ID );
			$name  = sprintf( '%s[%s]', $key, $item->ID );
			$value = get_post_meta( $item->ID, $key, true );
			$class = sprintf( 'field-%s', $_key );
			$check = empty( $value ) ? '' : $value;
			?>
            <p class="description description-wide <?php echo esc_attr( $class ) ?>"><p>
                <label for="<?php echo esc_attr( $id ); ?>">
                    <input type="checkbox" id="<?php echo esc_attr( $id ); ?>"
                           class="widefat <?php echo esc_attr( $id ); ?>"
                           name="<?php echo esc_attr( $name ); ?>"
						<?php checked( $check, 'yes' ); ?>/>
					<?php echo esc_attr( $label ); ?>
                </label>
            </p></p>
			<?php
		}
	}


	/**
	 * Add our fields to the screen options toggle
	 *
	 * @param array $columns Menu item columns
	 * @return array
	 */
	public static function _columns( $columns ) {
		$columns = array_merge( $columns, self::$fields );

		return $columns;
	}
}

Menu_Item_Custom_Fields_Checkbox_Example::init();
