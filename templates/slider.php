<div class="gallery-slider gallery-size-<?=sanitize_html_class( $size )?>">
    <ul>
<?php foreach ( $attachments as $id => $attachment ) : ?>
		<li class="gallery-item"><?=isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $size, false, false) : wp_get_attachment_image($id, $size, true, false)?></li>
<?php endforeach; ?>
    </ul>
</div>
