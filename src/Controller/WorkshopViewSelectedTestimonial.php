<?php

namespace Drupal\fossee_stats\Controller;

use Drupal\Core\Controller\ControllerBase;

class WorkshopViewSelectedTestimonial extends ControllerBase {

	public function content($id = null) {
		$connection = \Drupal::database();
		$page_content = "";
	    $query = $connection->select('testimonials');
	    $query->fields('testimonials');
	    $query->condition('t_id', $id);
	    $result = $query->execute();
	    $row = $result->fetchObject();
	    $page_content .= "<br><ul><li><i>{$row->body}</i>
	                                              <br>
	            <br>
	                                                <p style='text-align:right;'>{$row->name},{$row->department},</p>
	                                                <p style='text-align:right;margin-top:-15px''>{$row->university}.</p>
	                                                </li><ul>";
	    return ['#type'=>'inline_template', '#template'=>$page_content];
	}
}