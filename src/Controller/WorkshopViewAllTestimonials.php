<?php

namespace Drupal\fossee_stats\Controller;

use Drupal\Core\Controller\ControllerBase;

class WorkshopViewAllTestimonials extends ControllerBase {

	public function content($workshop_id = null) {

		$connection = \Drupal::database();
		$page_content = "";
	    $query = $connection->select('testimonials');
	    $query->fields('testimonials');
	    $query->condition('w_id', $workshop_id);
	    $query->orderBy('t_id', 'DESC');
	    $result = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(4)->execute();

	    $query1 = $connection->select('workshop');
	    $query1->fields('workshop');
	    $query1->condition('w_id', $workshop_id);
	    $result1 = $query1->execute();
	    $row1 = $result1->fetchObject();
	    $page_content .= "<h2>Testimonials for " . $row1->w_name . " </h2>";
	    $page_content .= "<div id='testimonials-wrapper' style='margin-top:20px;'>";
	    while ($row = $result->fetchObject()) {
	        $page_content .= "
                <div class='testimonial' style='font-size:17px;'>
                    <i style='font-style:italic;'>{$row->body}</i><br>
                    <p style='margin-left:5%;'>- {$row->name}, {$row->department}, {$row->university}</p>
                </div>";
	    }
	    $page_content .= "</div> <!-- /#testimonials-wrapper -->";
	    
	    $render = [];
	    $render[] = [
	    	'#type' => 'inline_template',
	    	'#template' => $page_content
	    ];
	    $render[] = [
	    	'#type' => 'pager'
	    ];
	    return $render;
	}
}