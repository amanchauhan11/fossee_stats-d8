(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.fossee_stats = {
    attach: function (context, settings) {
      if(typeof drupalSettings.sortby !== undefined)
       	{ $("input[name='"+drupalSettings.sortby+"']").css("background", "lightblue"); }

    }
  };

}(jQuery, Drupal, drupalSettings));