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
                        <span id="post_gallery_slider_size_custom"<?php echo $options["size"] != "gallery-image" ? ' style="display:none"' : ''?>>
                            <label for="post_gallery_slider_width">Width:</label> <input id="post_gallery_slider_width" name="post_gallery_slider[width]" size="4" type="text" value="<?php echo $options["width"] ?>">
                            <label for="post_gallery_slider_height">Height:</label> <input id="post_gallery_slider_height" name="post_gallery_slider[height]" size="4" type="text" value="<?php echo $options["height"] ?>">
                        </span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="post_gallery_slider_show_thumbs">Show thumbnails:</label></th>
                    <td>
                        <select id="post_gallery_slider_show_thumbs" name="post_gallery_slider[show_thumbs]">
                            <option value="before"<?php echo $options["show_thumbs"] == "before" ? ' selected="selected"' : '' ?>>Before slider</option>
                            <option value="after"<?php echo $options["show_thumbs"] == "after" ? ' selected="selected"' : '' ?>>After slider</option>
                            <option value=""<?php echo $options["show_thumbs"] == "" ? ' selected="selected"' : '' ?>>Do not show</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top" id="post_gallery_slider_thumb_size_row"<?php echo $options["show_thumbs"] == "" ? ' style="display:none"' : '' ?>>
                    <th scope="row"><label for="post_gallery_slider_thumb_size">Thumbnails size:</label></th>
                    <td>
                        <select id="post_gallery_slider_thumb_size" name="post_gallery_slider[thumb_size]">
                            <?php foreach ($thumb_sizes as $size => $title): if ($title == "custom" && !function_exists("add_image_size")) continue; ?>
                            <option value="<?php echo $size ?>"<?php echo $size == $options["thumb_size"] ? ' selected="selected"' : '' ?>><?php echo $title ?></option>
                            <?php endforeach; ?>
                        </select>
                        &nbsp;
                        <span id="post_gallery_slider_thumb_size_custom"<?php echo $options["thumb_size"] != "gallery-thumb" ? ' style="display:none"' : ''?>>
                            <label for="post_gallery_slider_thumb_width">Width:</label> <input id="post_gallery_slider_thumb_width" name="post_gallery_slider[thumb_width]" size="4" type="text" value="<?php echo $options["thumb_width"] ?>" />
                            <label for="post_gallery_slider_thumb_height">Height:</label> <input id="post_gallery_slider_thumb_height" name="post_gallery_slider[thumb_height]" size="4" type="text" value="<?php echo $options["thumb_height"] ?>" />
                        </span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="post_gallery_slider_restore">Restore defaults:</label></th>
                    <td><input id="post_gallery_slider_restore" name="post_gallery_slider[restore]" type="checkbox" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Customize:</th>
                    <td><a href="<?php echo admin_url("plugin-editor.php?file=post-gallery-slider%2Fcss%2Fgallery.css&plugin=post-gallery-slider%2Fpost-gallery-slider.php")?>">Edit CSS</a></td>
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
        $("#post_gallery_slider_show_thumbs").change(function() {
            if (this.value == "") {
                $("#post_gallery_slider_thumb_size_row").hide();
            }
            else {
                $("#post_gallery_slider_thumb_size_row").show();
            }
        });
        $("#post_gallery_slider_thumb_size").change(function() {
            if (this.value == "gallery-thumb")
                $("#post_gallery_slider_thumb_size_custom").show();
            else
                $("#post_gallery_slider_thumb_size_custom").hide();
        });
    });
</script>