<?php
/**
 * Menu item images example
 *
 * Copy this file into your wp-content/mu-plugins directory.
 *
 * @package Menu_Item_Custom_Fields_Images
 * @version 1.0
 * @author Kodie Grantham <kodie.grantham@gmail.com>
 *
 *
 * Plugin name: Menu Item Custom Fields Images
 * Plugin URI: https://github.com/kucrut/wp-menu-item-custom-fields
 * Description: Example usage of Menu Item Images in plugins/themes
 * Version: 1.0.0
 * Author: Kodie Grantham
 * Author URI: http://kodieg.com/
 * License: GPL v2
 * Text Domain: menu-item-custom-fields-images
 */


/**
 * Sample menu item metadata
 *
 * This class demonstrate the usage of Menu Item Images in plugins/themes.
 */
class Menu_Item_Custom_Fields_Images {

	/**
	 * Holds our custom fields
	 *
	 * @var    array
	 * @access protected
	 */
	protected static $fields = array();


	/**
	 * Initialize plugin
	 */
	public static function init() {
		add_action( 'wp_nav_menu_item_custom_fields', array( __CLASS__, '_fields' ), 10, 4 );
		add_action( 'wp_update_nav_menu_item', array( __CLASS__, '_save' ), 10, 3 );
		add_filter( 'manage_nav-menus_columns', array( __CLASS__, '_columns' ), 99 );

		global $pagenow;
		if ( $pagenow == 'nav-menus.php' ) {
		  wp_enqueue_media();
		  add_action( 'admin_footer', array( __CLASS__, '_admin_js' ) );
		}

		self::$fields = array(
			'image' => __( 'Image', 'menu-item-custom-fields-image' ),
			'image-hover' => __( 'Hover Image', 'menu-item-custom-fields-hover-image' ),
		);
	}

  public static function _admin_js() {
    echo "<script type='text/javascript'>
    jQuery(document).ready(function($){
        var custom_uploader;

        $('.menu_image_upload').click(function(e) {
            e.preventDefault();
            var fieldforid = jQuery(this).data('for-id');

            if (custom_uploader) {
                custom_uploader.open();
                return;
            }

            custom_uploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose Image',
                button: {
                    text: 'Choose Image'
                },
                multiple: false
            });

            custom_uploader.on('select', function() {
                attachment = custom_uploader.state().get('selection').first().toJSON();
                $('#'+fieldforid).val(attachment.url).trigger('input');
            });

            custom_uploader.open();
        });

        $('.menu_image_clear').click(function(e) {
          e.preventDefault();
          var fieldforid = jQuery(this).data('for-id');
          $('#'+fieldforid).val('').trigger('input');
        });

        $('.menu_image').on('input', function(e){
          var imgurl = $(this).val();
          var imgpreview = $(this).siblings('.menu_image_preview');
          var imgreset = $(this).siblings('.menu_image_clear');

          if (imgurl) {
            imgpreview.show().attr('src', imgurl);
            imgreset.show();
          } else {
            imgpreview.hide();
            imgreset.hide();
          }
        }).trigger('input');
    });
    </script>";
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
				$value = $_POST[ $key ][ $menu_item_db_id ];
			}
			else {
				$value = null;
			}

			// Update
			if ( ! is_null( $value ) ) {
				update_post_meta( $menu_item_db_id, $key, $value );
			}
			else {
				delete_post_meta( $menu_item_db_id, $key );
			}
		}
	}


	/**
	 * Print field
	 *
	 * @param object $item  Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args  Menu item args.
	 * @param int    $id    Nav menu ID.
	 *
	 * @return string Form fields
	 */
	public static function _fields( $id, $item, $depth, $args ) {
		foreach ( self::$fields as $_key => $label ) :
			$key   = sprintf( 'menu-item-%s', $_key );
			$id    = sprintf( 'edit-%s-%s', $key, $item->ID );
			$name  = sprintf( '%s[%s]', $key, $item->ID );
			$value = get_post_meta( $item->ID, $key, true );
			$class = sprintf( 'field-%s', $_key );
			?>
				<p class="description description-wide <?php echo esc_attr( $class ) ?>">
					<?php printf(
						'<label for="%1$s">%2$s<br />
            <input type="hidden" id="%1$s" class="menu_image widefat %1$s" name="%3$s" value="%4$s" />
            <img src="" id="menu_image_preview_%1$s" data-for-name="%3$s" data-for-id="%1$s" class="menu_image_preview" style="display:block;width:100px;height:auto;" />
            <input type="button" id="menu_image_upload_%1$s" data-for-name="%3$s" data-for-id="%1$s" class="menu_image_upload" value="Upload Image" />
            <input type="button" id="menu_image_clear_%1$s" data-for-name="%3$s" data-for-id="%1$s" class="menu_image_clear" value="Clear Image" />
            </label>',
						esc_attr( $id ),
						esc_html( $label ),
						esc_attr( $name ),
						esc_attr( $value )
					) ?>
				</p>
			<?php
		endforeach;
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
Menu_Item_Custom_Fields_Images::init();
