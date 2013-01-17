<?php
/*
Plugin Name: Post gallery slider
Plugin URI: http://github.com/kasparsj/post-gallery-slider
Description: Post gallery slider, with thumbnails and with nice animation, and auto height.
Author: Kaspars Jaudzems
Author URI: http://kasparsj.wordpress.com
Version: 1.0.4
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class PostGallerySlider {
    
    static protected $instance = 0;
    
    protected $options;
    protected $sizes = array(
        "large" => "large", 
        "medium" => "medium", 
        "gallery-image" => "custom", 
        "full" => "full (not recommended)", 
        "thumbnail" => "thumbnail (not recommended)");
    protected $thumb_sizes = array(
        "thumbnail" => "thumbnail",
        "medium" => "medium",
        "gallery-thumb" => "custom",
        "large" => "large (not recommended)",
        "full" => "full (not recommended)");
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_filter('post_gallery', array($this, 'post_gallery'), 1001, 2);
    }
    
    public function init() {
        $this->options = get_option('post_gallery_slider');
    
        if (($this->options['restore'] == 'on') || (!is_array($this->options))) {
            $this->options = array(
                "size" => "large",
                "width" => 650,
                "height" => 0,
                "show_thumbs" => "before",
                "thumb_size" => "thumbnail",
                "thumb_width" => 0,
                "thumb_height" => 60,
                "gallery_css" => file_get_contents("css/gallery.css"),
            );
            update_option('post_gallery_slider', $this->options);
        }
        else {
            if ($this->options["size"] == "gallery-image")
                add_image_size("gallery-image", $this->options['width'], $this->options['height']);

            if ($this->options["thumb_size"] == "gallery-thumb")
                add_image_size("gallery-thumb", $this->options['thumb_width'], $this->options['thumb_height']);
        }
    }
    
    public function admin_init() {
        register_setting('post_gallery_slider', 'post_gallery_slider', array($this, 'validate_options'));
    }
    
    public function admin_menu() {
        add_options_page('Post Gallery Slider', 'Post Gallery Slider', 'manage_options', 'post_gallery_slider', array($this, 'options_page'));
    }
    
    public function post_gallery($null, $attr = array()) {
        global $post;

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
            'size'       => $this->options["size"],
            'thumb_size' => $this->options["thumb_size"],
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
                $output .= wp_get_attachment_link($att_id, $this->options["size"], true) . "\n";
            return $output;
        }

        wp_register_script( 'jquery.sudoSlider.js', plugins_url('post-gallery-slider') . '/js/jquery.sudoSlider.js', array('jquery'), '2.1.8' );
        wp_enqueue_script( 'jquery.sudoSlider.js' );
        add_action( 'wp_footer', array($this, 'footer'), 10000 );

        // NOT USED ATM
        //$itemtag = tag_escape($itemtag);
        //$captiontag = tag_escape($captiontag);
        //$columns = intval($columns);
        //$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
        //$float = is_rtl() ? 'right' : 'left';

        $first_image = wp_get_attachment_image_src(key($attachments), $size, true);
        return $this->include_template("gallery.php", array(
            'id' => $id,
            'size' => $size,
            'thumb_size' => $thumb_size,
            'attachments' => $attachments,
            'width' => $first_image[1],
            'height' => $first_image[2],
            'instance' => ++self::$instance,
            'options' => $this->options
        ), true);
    }
    
    public function footer() {
        if( wp_script_is( 'jquery', 'done' ) ) {
            $this->include_template("footer.php");
        }
    }
    
    public function validate_options($input) {
        return $input;
    }
    
    public function options_page() {
        $this->include_template("options-page.php", array(
            'options' => $this->options,
            'sizes' => $this->sizes,
            'thumb_sizes' => $this->thumb_sizes
        ));
    }
    
    protected function include_template($template, $vars, $return = false) {
        if ($return) ob_start();
        extract($vars);
        include("templates/".$template);
        if ($return) return ob_get_clean();
    }
}

new PostGallerySlider;

?>
