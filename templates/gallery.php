<style type="text/css">
    .gallery-slider { width: <?=$width?>px; height: <?=$height?>px; }
    <?=$options['gallery_css']?>
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
