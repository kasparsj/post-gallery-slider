<script type="text/javascript">
    jQuery(document).ready(function($) {
       if (typeof $.fn.sudoSlider != "undefined") {
            $(".gallery").each(function() {
                var gal = this,
                    touch = $("html.touch").size() > 0;
                var sudoSlider = $(".gallery-slider", gal).show().sudoSlider({
                    prevNext: false,
                    continuos: true,
                    touch: touch,
                    effect: "slide",
                    initCallback: function() {
                        $(".gallery-thumbs a", gal).removeClass("active");
                        $(".gallery-thumbs a:first", gal).addClass("active");
                    },
                    afteranimation: function(t) {
                        $(".gallery-thumbs a", gal).removeClass("active");
                        $(".gallery-thumbs a:eq("+(t-1)+")", gal).addClass("active");
                    }
                });
                $(".gallery-thumbs a", gal).click(function(event) {
                    event.preventDefault();
                    sudoSlider.goToSlide(($(this).parent().index()+1))
                    return false;
                });
                if (!touch) {
                    $(".gallery-slider img", gal).click(function(event) {
                        event.preventDefault();
                        sudoSlider.goToSlide("next");
                        return false;
                    });
                }
            });
        } 
    });
</script>
