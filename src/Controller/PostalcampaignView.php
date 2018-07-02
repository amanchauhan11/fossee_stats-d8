<?php

namespace Drupal\fossee_stats\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fossee_stats\Utility;

class PostalcampaignView extends ControllerBase {

	public function content(){
		$foss_name="";
		$startdate="1960-01-01";
		$enddate=date("Y-m-d");
		$pagecontent=$this->postalcampaign_view($foss_name, $startdate, $enddate);
		return ['#type'=>'inline_template', '#template'=>$pagecontent];
	}

	private function postalcampaign_view($foss_name, $startdate, $enddate){

	    if ($startdate == "") {
	        $startdate = "1960-01-01";
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

	    if (strlen($foss_name) == 0 || $foss_name == "NULL") {
	        $foss_name= "%";
	    }
	    else {
	        $foss_name = $foss_name;
	    }
	    $page_content = "";
	    $headers = array(
	        "Name",
	        "Date",
	        ""
	    );

	    $rows1 = array();
	    $connection = \Drupal::database();
	    $query1 = $connection->select('postercampaign');
	    $query1->fields('postercampaign');
	    $query1->condition('foss_name', $foss_name, 'LIKE');
	    $query1->condition('startdate', $startdate, '>=');
	    $query1->condition('startdate', $enddate, '<=');
	    $query1->orderBy('startdate', 'DESC');
	    $result1 = $query1->execute();
	    while ($row = $result1->fetchObject()) {
	        $item = array(
	            $row->p_name,
	            $row->startdate,

	            "<a href=" . $GLOBALS['base_url'] . "/postalcampaign/view_details/{$row->p_id} target='_blank' title='Click to view detail'>Details</a>"
	        );
	        array_push($rows1, $item);
	    }
	    $page_content .= Utility::bootstrap_table_format($headers, $rows1);
	    return $page_content;
	}
}