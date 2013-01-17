<?php
/*
Plugin Name: Post gallery slider
Plugin URI: http://github.com/kasparsj/post-gallery-slider
Description: Post gallery slider, with thumbnails and with nice animation, and auto height.
Author: Kaspars Jaudzems
Author URI: http://kasparsj.wordpress.com
Version: 1.0.4
 */

// Post gallery functions
// 
function post_gallery_slider($null, $attr = array()) {
	global $post;

	static $instance = 0;
	$instance++;
    
    $options = get_option('post_gallery_slider');

	// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] )
			unset( $attr['orderby'] );
	}

	extract(shortcode_atts(array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post->ID,
		'columns'    => 3,
		'size'       => $options["size"],
        'thumb_size' => $options["thumb_size"],
		'include'    => '',
		'exclude'    => ''
	), $attr));

	$id = intval($id);
	if ( 'RAND' == $order )
		$orderby = 'none';

	if ( !empty($include) ) {
		$include = preg_replace( '/[^0-9,]+/', '', $include );
		$_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( !empty($exclude) ) {
		$exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
		$attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	} else {
		$attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	}

	if ( empty($attachments) )
		return '';

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment )
			$output .= wp_get_attachment_link($att_id, $size, true) . "\n";
		return $output;
	}
    
    wp_register_script( 'jquery.sudoSlider.js', plugins_url('post-gallery-slider') . '/js/jquery.sudoSlider.js', array('jquery'), '2.1.8' );
    wp_enqueue_script( 'jquery.sudoSlider.js' );
    add_action( 'wp_footer', 'post_gallery_slider_footer', 10000 );

	$itemtag = tag_escape($itemtag);
	$captiontag = tag_escape($captiontag);
	//$columns = intval($columns);
	//$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
	//$float = is_rtl() ? 'right' : 'left';

    ob_start();
    include("templates/gallery.php");
	return ob_get_clean();
}
add_filter( 'post_gallery', 'post_gallery_slider', 1001, 2 );

function post_gallery_slider_footer() {
    if( wp_script_is( 'jquery', 'done' ) ) {
        include("templates/footer.php");
    }
}

// Settings functions
add_action('init', 'post_gallery_slider_init');

function post_gallery_slider_init() {
    $options = get_option('post_gallery_slider');
    
    if (($options['restore'] == 'on') || (!is_array($options))) {
		$defaults = array(
            "size" => "large",
            "width" => 650,
            "height" => 0,
            "show_thumbs" => "before",
            "thumb_size" => "thumbnail",
            "thumb_width" => 0,
            "thumb_height" => 60,
            "gallery_css" => file_get_contents("css/gallery.css"),
        );
		update_option('post_gallery_slider', $defaults);
	}
    
    if ($options["size"] == "gallery-image")
        add_image_size("gallery-image", $options['width'], $options['height']);
    
    if ($options["thumb_size"] == "gallery-thumb")
        add_image_size("gallery-thumb", $options['thumb_width'], $options['thumb_height']);
}

// Admin settings functions
//
add_action('admin_init', 'post_gallery_slider_admin_init');

function post_gallery_slider_admin_init() {
    register_setting('post_gallery_slider', 'post_gallery_slider', 'post_gallery_slider_options_validate');
}

function post_gallery_slider_options_validate($input) {
	return $input;
}

function post_gallery_slider_menu() {
	add_options_page('Post Gallery Slider', 'Post Gallery Slider', 'manage_options', 'post_gallery_slider', 'post_gallery_slider_settings_page');
}
add_action('admin_menu', 'post_gallery_slider_menu');

function post_gallery_slider_settings_page() {
    include("templates/settings-page.php");
}

?>
