(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.fossee_stats = {
    attach: function (context, settings) {

      $(document).ready(function(){

        $('#testimonials_front li').hide().eq(0).show();

        setInterval(function (){

            if($('#testimonials_front li:visible').next().length > 0) {
              $('#testimonials_front li:visible').fadeOut('slow', function(){
               $(this).next().fadeIn('slow'); });

            }
            else {
              console.log('last');
              $('#testimonials_front li:visible').fadeOut('slow', function() {$(this).siblings(':first').fadeIn('slow');});
            }

        }, 12000);

      });
    }
  };

})(jQuery, Drupal, drupalSettings);