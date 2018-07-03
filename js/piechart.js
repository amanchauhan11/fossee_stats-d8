(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.fossee_stats = {
    attach: function (context, settings) {

      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
      	for(let i = 0; i < Object.keys(drupalSettings.gc).length; i++) {
	        let data = google.visualization.arrayToDataTable(drupalSettings.gc[i].chart_data);
	        let options = {
	          title: drupalSettings.gc[i].title
	        };
	        let chart = new google.visualization.PieChart(document.getElementById(drupalSettings.gc[i].chart_id));
	        chart.draw(data, options);
	    }
      }

 	}
 }
}(jQuery, Drupal, drupalSettings));