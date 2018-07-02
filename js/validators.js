(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.fossee_stats = {
    attach: function (context, settings) {

      if (typeof drupalSettings.numvidlink !== 'undefined')
      {
      	var numvidlink = drupalSettings.numvidlink;
    		for(let i = 0; i < numvidlink; i++)
    		{
          let videoid = '#videolink_url_'+i.toString();
          $(videoid).change(
      	 	function(){
      	 				let link = $(this).val();
      	 				if( link.indexOf("http://") && link.indexOf("https://") && link.length)
      	 					{ $("#validate_url_"+i.toString()).css("visibility", "visible"); $('#edit-submit').prop('disabled', true);}
      	 				else
      	 					{ $("#validate_url_"+i.toString()).css("visibility", "hidden"); $('#edit-submit').prop('disabled', false);}
      	 			   }
     	    );
          let link = $(videoid).val();
          if( link.indexOf("http://") && link.indexOf("https://") && link.length)
            { $("#validate_url_"+i.toString()).css("visibility", "visible"); $('#edit-submit').prop('disabled', true);}
          else
            { $("#validate_url_"+i.toString()).css("visibility", "hidden");  }
    		}
      }

       $("#edit-event-link").change(
       		function(){
       					var link = $(this).val();
       					if( link.indexOf("http://") && link.indexOf("https://") && link.length)
       						{ $("#validate_url").css("visibility", "visible");  $('#edit-submit').prop('disabled', true);}
       					else
                  { $("#validate_url").css("visibility", "hidden"); }
       				}
       );

       $("input[name='end_date[date]']").change(
          function() {
                if( $(this).val() < $("input[name='start_date[date]']").val())
                  { $("#validate_date").css("visibility","visible"); $('#edit-submit').prop('disabled', true);}
                else
                { $("#validate_date").css("visibility","hidden"); }
               }
        );

       $("input[name='start_date[date]']").change(
          function() {
                if( $("input[name='end_date[date]']").val()!="" && $(this).val() > $("input[name='end_date[date]']").val())
                 { $("#validate_date").css("visibility","visible");  $('#edit-submit').prop('disabled', true);}
                else
                  { $("#validate_date").css("visibility","hidden");}
               }
        );

      $("input[name='start_date[time]']").change(
          function() {
            if($("input[name='end_date[date]']").val()==$("input[name='start_date[date]']").val())
              if($(this).val()>$("input[name='end_date[time]']"))
                { $("#validate_time").css("visibility","visible"); $('#edit-submit').prop('disabled', true);}
              else
                { $("#validate_time").css("visibility","hidden"); }
          }

        );

       $("input[name='end_date[time]']").change(
          function() {
            if($("input[name='end_date[date]']").val()==$("input[name='start_date[date]']").val())
              if($(this).val()<$("input[name='start_date[time]']"))
                { $("#validate_time").css("visibility","visible");  $('#edit-submit').prop('disabled', true);}
              else
                { $("#validate_time").css("visibility","hidden");}
          }
        );
    }
  };

}(jQuery, Drupal, drupalSettings));
