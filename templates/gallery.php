<style type="text/css">
    .gallery-slider { overflow: hidden; width: <?=$width?>px; height: <?=$height?>px; }
<?php if (apply_filters('use_default_gallery_style', true)): ?>
    <?=$css?>
<?php endif; ?>
</style>
<div id="gallery-<?=$instance?>" class="gallery galleryid-<?=$id?>">
<?php
if ($options["show_thumbs"] == "before")
    include("thumbs.php");

include("slider.php");

if ($options["show_thumbs"] == "after")
    include("thumbs.php");
?>
</div>
