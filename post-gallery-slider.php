<?php
/*
Plugin Name: Post gallery slider
Plugin URI: https://bitbucket.org/kasparsj/post-gallery-slider
Description: Post gallery slider, with thumbnails and with nice animation, and auto height.
Author: Kaspars Jaudzems
Author URI: http://kasparsj.wordpress.com
Version: 1.0
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
    
    wp_register_script( 'jquery.sudoSlider.js', plugins_url('post-gallery-slider') . '/libs/jquery.sudoSlider.js', array('jquery'), '2.1.8' );
    wp_enqueue_script( 'jquery.sudoSlider.js' );
    add_action( 'wp_footer', 'post_gallery_slider_footer' );

	$itemtag = tag_escape($itemtag);
	$captiontag = tag_escape($captiontag);
	$columns = intval($columns);
	$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
	$float = is_rtl() ? 'right' : 'left';

	$selector = "gallery-{$instance}";
    list($src, $width, $height) = wp_get_attachment_image_src(key($attachments), $size, true);
    
    $thumbs_div = "<div class='gallery-thumbs'>
                    <ul>\n";
    $i = 0;
    foreach ( $attachments as $id => $attachment ) {
        $link = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $thumb_size, false, false) : wp_get_attachment_link($id, $thumb_size, true, false);
        $thumbs_div .= "<li>{$link}</li>";
    }
    $thumbs_div .= "</ul>
                <div style='clear:both'></div>\n
            </div>\n";
    
    $size_class = sanitize_html_class( $size );
    $slider_div = "<div class='gallery-slider gallery-size-{$size_class}'>
                    <ul>\n";
	$i = 0;
	foreach ( $attachments as $id => $attachment ) {
		$link = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $size, false, false) : wp_get_attachment_image($id, $size, true, false);
		$slider_div .= "<li>{$link}</li>";
	}
    $slider_div .= "</ul>
            </div>\n";

	$gallery_div = '';
    $gallery_style = "<style type='text/css'>
        .gallery-slider { overflow: hidden; width: {$width}px; height: {$height}px; }
        {$options['gallery_css']}
    </style>";
	$gallery_div = "<div id='$selector' class='gallery galleryid-{$id}'>";
	$output  = apply_filters( 'gallery_style', $gallery_style . "\n\t\t" . $gallery_div );
    $output .= ($options["thumb_pos"] == "before" ? $thumbs_div.$slider_div : $slider_div.$thumbs_div);
	$output .= "</div>\n";
    
	return $output;
}
add_filter( 'post_gallery', 'post_gallery_slider', 10, 2 );

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

// Settings functions
add_action('init', 'post_gallery_slider_init');

function post_gallery_slider_init() {
    $options = get_option('post_gallery_slider');
    
    if (($options['restore'] == 'on') || (!is_array($options))) {
		$defaults = array(
            "size" => "large",
            "width" => 650,
            "height" => 0,
            "thumb_size" => "thumbnail",
            "thumb_width" => 0,
            "thumb_height" => 60,
            "thumb_pos" => "before",
            "gallery_css" => ".gallery { overflow: visible }
.gallery-thumbs ul, .gallery-thumbs ul li { list-style: none; }
.gallery-thumbs ul li { float: left; margin: -3px 7px 7px -3px; line-height: 0 }
.gallery-thumbs ul li a { display: block; padding: 2px; border: 1px solid transparent }
.gallery-thumbs ul li a:hover, .gallery-thumbs ul li a.active { border: 1px solid #CCC; }
.gallery-slider ul, .gallery-slider ul li { margin:0; padding: 0; list-style: none; position: relative; overflow: hidden; display: block; }
.gallery-slider ul li img { border: 0 }",
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
    $options = get_option("post_gallery_slider");
    $sizes = array(
        "large" => "large", 
        "medium" => "medium", 
        "gallery-image" => "custom", 
        "full" => "full (not recommended)", 
        "thumbnail" => "thumbnail (not recommended)");
    $thumb_sizes = array(
        "thumbnail" => "thumbnail",
        "medium" => "medium",
        "gallery-thumb" => "custom",
        "large" => "large (not recommended)",
        "full" => "full (not recommended)");
?>
<div class="wrap">
    <div class="icon32" id="icon-options-general"><br></div>
    <h2>Post Gallery Slider</h2>
    <!--Some optional text here explaining the overall purpose of the options and what they relate to etc.-->
    <form method="post" action="options.php">
        <?php settings_fields('post_gallery_slider'); ?>
        <!--<h3>Main Section</h3>
        <p>Some section description</p>-->
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="post_gallery_slider_size">Image size:</label></th>
                    <td>
                        <select id="post_gallery_slider_size" name="post_gallery_slider[size]">
                            <?php foreach ($sizes as $size => $title): if ($title == "custom" && !function_exists("add_image_size")) continue; ?>
                            <option value="<?php echo $size ?>"<?php echo $size == $options["size"] ? ' selected="selected"' : '' ?>><?php echo $title ?></option>
                            <?php endforeach; ?>
                        </select>
                        &nbsp;
                        <span id="post_gallery_slider_size_custom"<?php echo $options["size"] != "custom" ? ' style="display:none"' : ''?>>
                            <label for="post_gallery_slider_width">Width:</label> <input id="post_gallery_slider_width" name="post_gallery_slider[width]" size="4" type="text" value="<?php echo $options["width"] ?>">
                            <label for="post_gallery_slider_height">Height:</label> <input id="post_gallery_slider_height" name="post_gallery_slider[height]" size="4" type="text" value="<?php echo $options["height"] ?>">
                        </span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="post_gallery_slider_thumb_size">Thumbnails size:</label></th>
                    <td>
                        <select id="post_gallery_slider_thumb_size" name="post_gallery_slider[thumb_size]">
                            <?php foreach ($thumb_sizes as $size => $title): if ($title == "custom" && !function_exists("add_image_size")) continue; ?>
                            <option value="<?php echo $size ?>"<?php echo $size == $options["thumb_size"] ? ' selected="selected"' : '' ?>><?php echo $title ?></option>
                            <?php endforeach; ?>
                        </select>
                        &nbsp;
                        <span id="post_gallery_slider_thumb_size_custom"<?php echo $options["thumb_size"] != "custom" ? ' style="display:none"' : ''?>>
                            <label for="post_gallery_slider_thumb_width">Width:</label> <input id="post_gallery_slider_thumb_width" name="post_gallery_slider[thumb_width]" size="4" type="text" value="<?php echo $options["thumb_width"] ?>">
                            <label for="post_gallery_slider_thumb_height">Height:</label> <input id="post_gallery_slider_thumb_height" name="post_gallery_slider[thumb_height]" size="4" type="text" value="<?php echo $options["thumb_height"] ?>">
                        </span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="post_gallery_slider_thumb_width">Thumbnails position:</label></th>
                    <td>
                        <select id="post_gallery_slider_thumb_pos" name="post_gallery_slider[thumb_pos]">
                            <option value="before"<?php echo $options["thumb_pos"] == "before" ? ' selected="selected"' : '' ?>>Before slider</option>
                            <option value="after"<?php echo $options["thumb_pos"] == "after" ? ' selected="selected"' : '' ?>>After slider</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="post_gallery_slider_gallery_css">Gallery CSS:</label></th>
                    <td><textarea id="post_gallery_slider_gallery_css" name="post_gallery_slider[gallery_css]" rows="7" cols="95"><?php echo $options["gallery_css"] ?></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="post_gallery_slider_restore">Restore defaults:</label></th>
                    <td><input id="post_gallery_slider_restore" name="post_gallery_slider[restore]" type="checkbox" /></td>
                </tr>
            </tbody>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $("#post_gallery_slider_size").change(function() {
            if (this.value == "gallery-image")
                $("#post_gallery_slider_size_custom").show();
            else
                $("#post_gallery_slider_size_custom").hide();
        });
        $("#post_gallery_slider_thumb_size").change(function() {
            if (this.value == "gallery-thumb")
                $("#post_gallery_slider_thumb_size_custom").show();
            else
                $("#post_gallery_slider_thumb_size_custom").hide();
        });
    });
</script>
<?php
}

?>
