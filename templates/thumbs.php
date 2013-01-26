<div class="gallery-thumbs">
    <ul>
<?php foreach ( $attachments as $id => $attachment ) : ?>
        <li class="gallery-item"><?=isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $thumb_size, false, false) : wp_get_attachment_link($id, $thumb_size, true, false)?></li>
<?php endforeach; ?>
    </ul>
    <div style='clear:both'></div>
</div>
