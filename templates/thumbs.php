<div class="gallery-thumbs">
<?php foreach ( $attachments as $id => $attachment ) : ?>
    <<?=$itemtag?> class="gallery-item"><?=isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $thumb_size, false, false) : wp_get_attachment_link($id, $thumb_size, true, false)?></<?=$itemtag?>>
<?php endforeach; ?>
    <br style="clear:both" />
</div>
