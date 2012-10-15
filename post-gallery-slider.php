<?php
/*
Plugin Name: Post Gallery slider
Plugin URI: https://bitbucket.org/kasparsj/post-gallery-slider
Description: Post gallery slider, with thumbnailsm and with nice animation, and auto height.
Author: Kaspars Jaudzems
Author URI: http://kasparsj.wordpress.com
Version: 1.0
 */

if (function_exists( 'add_image_size' )) {
    add_image_size( 'gallery-thumb', 0, 60 );
}

function post_gallery_slider_footer() {
    if( wp_script_is( 'jquery', 'done' ) ) {
    ?>
    <script type="text/javascript">
        $(document).ready(function() {
           if (typeof $.fn.sudoSlider != "undefined") {
                $(".gallery").each(function() {
                    var gal = this;
                    var sudoSlider = $(".gallery-slider", gal).show().sudoSlider({
                        prevNext: false,
                        continuos: true,
                        beforeAniFunc: function(t) {
                            $(".gallery-thumbs a", gal).removeClass("active");
                            $(".gallery-thumbs a:eq("+(t-1)+")", gal).addClass("active");
                        },
                        afterAniFunc: function(t) {
                            $(".gallery-thumbs a", gal).removeClass("active");
                            $(".gallery-thumbs a:eq("+(t-1)+")", gal).addClass("active");
                        }
                    });
                    $(".gallery-thumbs a", gal).click(function() {
                        sudoSlider.goToSlide(($(this).parent().index()+1))
                        return false;
                    });
                    $(".gallery-slider img", gal).click(function() {
                        sudoSlider.goToSlide("next");
                    });
                });
            } 
        });
    </script>
    <?php
    }
}

function post_gallery_slider($null, $attr = array()) {
	global $post;

	static $instance = 0;
	$instance++;

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
		'size'       => 'large',
        'thumb_size' => 'gallery-thumb',
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
    
    wp_register_script( 'jquery.sudoSlider.min.js', plugins_url('gallery-slider') . '/libs/jquery.sudoSlider.min.js', array('jquery'), '2.1.8' );
    wp_enqueue_script( 'jquery.sudoSlider.min.js' );
    add_action( 'wp_footer', 'post_gallery_slider_footer' );

	$itemtag = tag_escape($itemtag);
	$captiontag = tag_escape($captiontag);
	$columns = intval($columns);
	$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
	$float = is_rtl() ? 'right' : 'left';

	$selector = "gallery-{$instance}";
    list($src, $width, $height) = wp_get_attachment_image_src(key($attachments), $size, true);

	$gallery_div = '';
    $gallery_style = "<style type='text/css'>
        .gallery { overflow: visible }
        .gallery-thumbs ul, .gallery-thumbs ul li { list-style: none; }
        .gallery-thumbs ul li { float: left; margin: -3px 7px 7px -3px; line-height: 0 }
        .gallery-thumbs ul li a { display: block; padding: 2px; border: 1px solid transparent }
        .gallery-thumbs ul li a:hover, .gallery-thumbs ul li a.active { border: 1px solid #CCC; }
        .gallery-slider { overflow: hidden; width: {$width}px; height: {$height}px; }
        .gallery-slider ul, .gallery-slider ul li { margin:0; padding: 0; list-style: none; position: relative; overflow: hidden; display: block; }
        .gallery-slider ul li img { border: 0 }
    </style>";
	$gallery_div = "<div id='$selector' class='gallery galleryid-{$id}'>";
	$output = apply_filters( 'gallery_style', $gallery_style . "\n\t\t" . $gallery_div );
    
    $output .= "<div class='gallery-thumbs'>
                    <ul>\n";
    $i = 0;
	foreach ( $attachments as $id => $attachment ) {
		$link = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $thumb_size, false, false) : wp_get_attachment_link($id, $thumb_size, true, false);
		$output .= "<li>{$link}</li>";
	}
    $output .= "</ul>
            </div>\n";

    $size_class = sanitize_html_class( $size );
    $output .= "<div class='gallery-slider gallery-size-{$size_class}'>
                    <ul>\n";
	$i = 0;
	foreach ( $attachments as $id => $attachment ) {
		$link = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $size, false, false) : wp_get_attachment_image($id, $size, true, false);
		$output .= "<li>{$link}</li>";
	}
    $output .= "</ul>
            </div>\n";

	$output .= "</div>\n";

	return $output;
}
add_filter( 'post_gallery', 'post_gallery_slider', 10, 2 );

?>
