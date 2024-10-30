<?php
/**
 * @package bookingkit
 * @version 1.0
 */
/*
Plugin Name: bookingkit
Plugin URI: http://wordpress.org/plugins/bookingkit/
Description: Integrate bookingkit easily in your website. Allow your users to book your services. Learn more about bookingkit on <a href="http://info.bookingkit.de/wordpress">bookingkit.de</a>.
Author: bookingkit
Version: 1.0
Author URI: https://bookingkit.de
Text Domain: bookingkit
Domain Path: /languages/
*/

/**
 * Add settings link on plugin page
 */

function bookingkit_links($links) { 
  $links[] = '<a href="options-general.php?page=bookingkit_plugin">'.__('Settings', 'bookingkit').'</a>'; 
  $links[] = '<a href="http://info.bookingkit.de/wordpress">'.__('Info', 'bookingkit').'</a>';
  return $links; 
}

/*
 * Add text domain for internationalization
 */

function load_bookingkit_textdomain() {
  load_plugin_textdomain( 'bookingkit', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

/**
 * Add the settings menu
 */

function add_bookingkit_menu() {
	add_options_page(
		__('bookingkit Options', 'bookingkit'),
		'bookingkit',
		'manage_options',
		'bookingkit_plugin',
		'bookingkit_options_page'
		);
}

/**
 * Only accept valid snippets for the general settings
 */

function bookingkit_sanitize( $input ) {
	if (preg_match('/((?:<script src="https:\/\/(?:eu5|app).bookingkit.de\/bkscript.js.php\?)?(?P<model>e|v)?=?(?P<id>[0-9a-f]{32})(?:&lang=)?(?P<lang>	de|en|es)?(?:&t=)?(?P<t>[0-9A-Za-z+%]*)?(?:"><\/script>)?)/', $input, $matches)){
			return $matches[0];
		}
 	else {
		return "";
	}
}

/**
 * Register the settings
 */

function bookingkit_register_settings() {
	register_setting(
          'bookingkit_options',  // settings section
          'bookingkit_vendor_id', // setting name
          'bookingkit_sanitize'
          );
}


/**
 * Build the options page
 */

function bookingkit_options_page() {
	?>
	<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<div id="poststuff">
		<div id="post-body">
			<div id="post-body-content">
			<p><?php _e('To configure your bookingkit plugin, you need to <a href="https://info.bookingkit.de/wordpress">create an account</a> and copy your bookingkit code from "Marketing" -> "Website" on', 'bookingkit') ?> <a href="https://app.bookingkit.de/marketing/website">bookingkit.de</a>. <?php _e('Afterwards you just need to activate the plugin in the bookingkit box of the posts and pages, where you want to display your events.', 'bookingkit') ?> </p>
				<form method="post" action="options.php">
					<?php settings_fields( 'bookingkit_options' ); ?>
					<?php $options = get_option( 'bookingkit_vendor_id' ) ?: ''; ?>
					<table class="form-table">
						<tr valign="top"><th scope="row"><?php _e('bookingkit Code', 'bookingkit') ?></th>
							<td>
								<textarea name="bookingkit_vendor_id" id="vendor_id" rows="4" cols="65"><?php echo $options; ?></textarea>

								<br />
								<label class="description" for="vendor_id"><?php _e('Please copy your bookingkit code from "Marketing" -> "Website" on', 'bookingkit') ?> <a href="https://app.bookingkit.de/marketing/website">bookingkit.de</a>.</label>

							</td>
						</tr>
					</table>
					<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save changes', 'bookingkit') ?>"  /></p>
				</form>
			</div> <!-- end post-body-content -->
		</div> <!-- end post-body -->
	</div> <!-- end poststuff -->
</div>
<?php
}

/**
 * Add the bookingkit box
 */

function bookingkit_add_custom_box() {
	add_meta_box(
            'bookingkit_box_id',            			// Unique ID
            __('bookingkit Settings', 'bookingkit'),	// Box title
            'bookingkit_inner_custom_box'				// Content callback
            );
}

/**
 * Add the content to the bookingkit box
 */

function bookingkit_inner_custom_box( $post ) {
	$active = get_post_meta( $post->ID, 'bookingkit_active', true ); 
	$snippet = get_post_meta( $post->ID, 'bookingkit_snippet', true ); 
	?>
	<p><?php _e('If you would like to display all of your events, just click Active. If you would like to display a specific event, copy the code for this event from the marketing page on', 'bookingkit') ?> <a href="https://app.bookingkit.de/marketing/website">bookingkit.de</a>.<br/> <?php _e('You can place your events within your page or post by adding [bookingkit] in your content.', 'bookingkit') ?> </p>
	<?php
	if (get_option('bookingkit_vendor_id') == false) {
		$class = 'notice notice-warning is-dismissable';
		$message = __( 'Please configure bookingkit in the', 'bookingkit' );
		$settings = __( 'Settings', 'bookingkit' );

		printf( '<div class="%1$s"><p>%2$s <a href="options-general.php?page=bookingkit_plugin">%3$s</a></p></div>', $class, $message, $settings );
		} ?>

	<textarea name="bookingkit_snippet" id="bookingkit_snippet" rows="4" cols="65"><?php echo $snippet; ?></textarea>
	<br/>
	<input type="hidden" name="bookingkit_active" value="0" />
	<input type="checkbox" name="bookingkit_active" id="bookingkit_active" value="1" <?php if ($active == '1') echo 'checked'; ?>>
	<label for="bookingkit_active"><?php _e('Active', 'bookingkit') ?></label>
	<?php
}

/**
 * Save the meta data for the meta box form - posts with shortcode are automatcally set active
 */

function bookingkit_save_postdata( $post_id ) {
	$post = get_post();
	if ( array_key_exists('bookingkit_snippet', $_POST ) ) {
		update_post_meta( $post_id,
			'bookingkit_snippet',
			$_POST['bookingkit_snippet']
			);
	} 
	if ( array_key_exists('bookingkit_active', $_POST ) && $_POST['bookingkit_active'] == '1' || has_shortcode( $post->post_content, 'bookingkit') ) {
		update_post_meta( $post_id,
			'bookingkit_active',
			'1'
			);
	} else {
		update_post_meta( $post_id,
			'bookingkit_active',
			'0'
			);
	}

}

/**
 * Add the shortcode content
 */

function bookingkit_shortcode( $atts ) {
	return "<div id='bookingKitContainer'></div>";
}

/**
 * Register shortcode
 */

function bookingkit_register_shortcode() {
	add_shortcode( 'bookingkit', 'bookingkit_shortcode' );
}

/**
 * Add the bookingkit div to the end of pages without shortcode
 */

function bookingkit_content( $content ) { 
	if ( is_singular() && ! has_shortcode($content, 'bookingkit')) {
		$bookingkit_div = "<div id='bookingKitContainer'></div>";
		$content = $content . $bookingkit_div;
	}
	return $content;
}

/**
 * Add scripts to pages with active plugin
 */

function bookingkit_add_script() {
	$post_id = get_the_ID();
	if (get_post_meta( $post_id, 'bookingkit_active', true) == '1') {
		$snippet = get_post_meta( $post_id, 'bookingkit_snippet', true) ?: get_option('bookingkit_vendor_id');
		preg_match('/((?:<script src="https:\/\/(?:eu5|app).bookingkit.de\/bkscript.js.php\?)?(?P<model>e|v)?=?(?P<id>[0-9a-f]{32})(?:&lang=)?(?P<lang>de|en|es)?(?:&t=)?(?P<t>[0-9A-Za-z+%]*)?(?:"><\/script>)?)/', $snippet, $script_details);
		$lang = '&lang=' . $script_details['lang'] ?: '';
		$t = $script_details['t'] ?: 'Default';
		$model = ($script_details['model'] == 'e' || $script_details['model'] != 'v') ? 'e' : 'v';
		wp_enqueue_script('bookingkit_script'.$post_id, 'https://app.bookingkit.de/bkscript.js.php?' . $model . '=' . $script_details['id']. $lang . '&t=' . $t, array(), null, true);
	}
}


/**
 * Dequeue scripts, if the plugin is inactive
 */

function bookingkit_dequeue_script() {
	$post_id = get_the_ID();
	if (get_post_meta( $post_id, 'bookingkit_active', true) != '1') {
		wp_dequeue_script( 'bookingkit_script' . $post_id );
	}
}

add_action( 'plugins_loaded', 'load_bookingkit_textdomain' );
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'bookingkit_links' );
add_action( 'init', 'bookingkit_register_shortcode' );
add_action( 'admin_init', 'bookingkit_register_settings' );
add_action( 'admin_menu', 'add_bookingkit_menu' );
add_action( 'add_meta_boxes', 'bookingkit_add_custom_box' );
add_action( 'save_post', 'bookingkit_save_postdata' );
add_filter( 'the_content', 'bookingkit_content' ); 
add_action( 'wp_enqueue_scripts', 'bookingkit_add_script');
add_action( 'wp_print_scripts', 'bookingkit_dequeue_script' );

/*
for deactivation:
((?:<div id="bookingKitContainer"><\/div>\s{0,3})?(?:<script src="https:\/\/(?:eu5|app).bookingkit.de\/bkscript.js.php\?)?(?P<model>e|v)?=?(?P<id>[0-9a-f]{32})(?:&lang=)?(?P<lang>de|en|es)?(?:&t=)?(?P<t>[0-9A-Za-z+%]*)?(?:"><\/script>)?)
*/


?>
