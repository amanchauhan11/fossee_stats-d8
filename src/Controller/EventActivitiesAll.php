<?php

namespace Drupal\fossee_stats\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fossee_stats\Utility;

class EventActivitiesAll extends ControllerBase {

	public function content() {

		$form['displaytext'] = array(
	        '#type' => 'inline_template',
	        '#prefix' => '<div><div id="displaytext" style="font-weight:bold;padding-top:10px">',
	        '#suffix' => '</div></div>',
	        '#template' => ''
	    );
	    //For displaying count of TBC of all foss
	    $form['tbctable'] = array(
	        '#type' => 'inline_template',
	        '#prefix' => '<div id="default_load" >',
	        '#template' => '<ul class="nav nav-tabs">

	                          <li class="active"><a data-toggle="tab" href="#workshopdata">Workshop</a></li>

	                            <li><a data-toggle="tab" href="#conferencedata">Conference </a></li>

	                      </ul>'
	     );

	     $form['tab_content'] = array(
	        '#type' => 'inline_template',
	        '#template' => '<div class="tab-content">



	        <div id="workshopdata" class="tab-pane fade  in active">' . $this->workshop_view_all(0, 1960-01-01, date("Y-m-d")) . '</div>


	        <div id="conferencedata" class="tab-pane fade ">' . $this->conference_seminar_view_all(0, "", "") . '</div>

	 		</div>'
	    );
	    $form['lastdiv'] = array(
	        '#type' => 'inline_template',
	        '#template' => '',
	        '#suffix' => '</div></div>'
	    );
	    return $form;
	}

	public static function workshop_view_all($workshop_id, $startdate, $enddate) {
	    if ($startdate == "") {
	        $startdate = "2015-08-01";
	    }
	    else {
	        $startdate = $startdate;
	    }
	    if ($enddate == "") {
	        $enddate = date("Y-m-d");
	        //$enddate="";
	    }
	    else {
	        $enddate = $enddate;
	    }
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
	    $query1->condition('type', "Workshop");
	    $query1->condition('startdate', $startdate, '>=');
	    $query1->condition('startdate', $enddate, '<=');
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
	    return $page_content;
	}

	public static function conference_seminar_view_all($workshop_id, $startdate, $enddate) {
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
	    $query1->condition('type', "Conference");
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
	    return $page_content;
	}
}