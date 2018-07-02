<?php

namespace Drupal\fossee_stats\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fossee_stats\Utility;

class EventSeminarViewAll extends ControllerBase {

	public function content(){

		$page_content = "";
	    $headers = array(
	        "Name",
	        "Start Date",
	        "Venue",
	        "No of Participants",
	        ""
	    );
	    $rows1 = array();
	    $connection = \Drupal::database();
	    $query1 = $connection->select('workshop');
	    $query1->fields('workshop');
	    $query1->condition('type', "Events");
	    $query1->orderBy('startdate', 'DESC');
	    $result1 = $query1->execute();
	    while ($row = $result1->fetchObject()) {
	        $item = array(
	            $row->w_name,
	            $row->startdate,
	            $row->venue,
	            $row->no_of_participant,

	            "<a href=" . $GLOBALS['base_url'] . "/events/view_details/{$row->w_id} target='_blank' title='Click to view detail'>Details</a>"
	        );
	        array_push($rows1, $item);
	    }
	    $page_content .= Utility::bootstrap_table_format($headers, $rows1);
    	return ['#type' => 'inline_template', '#template' => $page_content];
	}
}