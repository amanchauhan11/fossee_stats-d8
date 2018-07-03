(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.fossee_stats = {
    attach: function (context, settings) {

      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
      	for(let i = 0; i < Object.keys(drupalSettings.gca).length; i++) {
	        let data = google.visualization.arrayToDataTable(drupalSettings.gca[i].chart_data);
	        let options = {
	          title: drupalSettings.gca[i].title
	        };
	        let chart = new google.visualization.PieChart(document.getElementById(drupalSettings.gca[i].chart_id));
	        chart.draw(data, options);
	    }

	    for(let i = 0; i < Object.keys(drupalSettings.gcs).length; i++) {
	        let data = google.visualization.arrayToDataTable(drupalSettings.gcs[i].chart_data);
	        let options = {
	          title: drupalSettings.gcs[i].title
	        };
	        let chart = new google.visualization.PieChart(document.getElementById(drupalSettings.gcs[i].chart_id));
	        chart.draw(data, options);
	    }
      }

 	}
 }
}(jQuery, Drupal, drupalSettings));