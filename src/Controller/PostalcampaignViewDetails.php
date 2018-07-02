<?php

namespace Drupal\fossee_stats\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fossee_stats\Utility;

class PostalcampaignViewDetails extends ControllerBase {

	public function content($postalcampaign_id = null){

		$page_content = "";
	    $row = "";
	    $row1 = "";
	    $connection = \Drupal::database();
	    $query = $connection->select('postercampaign');
	    $query->fields('postercampaign');
	    $query->condition('p_id', $postalcampaign_id);
	    $result = $query->execute();
	    $row = $result->fetchObject();

	    if($query->countQuery()->execute()->fetchField()==0)
	    	return ['#type'=>'markup', '#markup'=>'Postalcampaign not found'];

	    //createZipOfPosterMaterial($row->p_name);
	    $startdate = $row->startdate;
	    $eventfolder = $row->poster_folder;

	    $page_content .= "<table class='table table-bordered table-hover' >";
	    $page_content .= "<tr><td><b>Name</b></td><td>{$row->p_name}</td></tr>";
	    $page_content .= "<tr><td><b>Date</b></td><td>$startdate</td></tr>";
	    $page_content .= "<tr><td><b>Details</b></td><td>{$row->body}</td></tr>";
	    $page_content .= "</table>";

	    $page_content .= "<table class='table table-bordered table-hover'><tr>";
	    $page_content .= "<td style='font-size:20px;padding-bottom:20px'>Campaign Posters

	    <a style='float:right' title='Download all material of Campign' href=" . $GLOBALS['base_url'] . "/postalcampaign/download/" . $eventfolder . ">Click to Download</a></td>";
	    $page_content .="</tr>";

	    $query = $connection->select('postercampaign_poster');
	    $query->fields('postercampaign_poster');
	    $query->condition('p_id', $postalcampaign_id);
	    $result = $query->execute();
	    $num_of_results = $query->countQuery()->execute()->fetchField();
	    if ($num_of_results != 0) {
	        $page_content .= "<tr><td>";
	        while ($row1 = $result->fetchObject()) {
	            if(!file_exists(Utility::posters_path().$row1->path))
	                continue;
	            $ext = end(explode(".", $row1->name));
	            if (($ext=="png")||($ext=="PNG")||($ext=="jpeg")||($ext=="JPEG")||($ext=="jpg")||($ext=="JPG")) {
	            $page_content .= "<a class='fancybox' rel='gallery1' href=" . $GLOBALS['base_url'] . "/campaign_posters/"  . $row1->path . "><img title='Click to view' style='width:150px; height:150px; padding-right:20px;padding-bottom:10px'       src=" . $GLOBALS['base_url'] . "/campaign_posters/" . $row1->path . " /></a>";

	            }
	        }
	        $page_content .= "</td></tr></table>";

	        $page_content .= "<h4>Poster's PDF</h4>";
	        $query = $connection->select('postercampaign_poster');
	        $query->fields('postercampaign_poster');
	        $query->condition('p_id', $postalcampaign_id);
	        $result = $query->execute();

	        while ($row1 = $result->fetchObject()) {
	            $ext = end(explode(".", $row1->name));
	            if (($ext=="pdf")||($ext=="PDF")) {
	                $page_content .= "<a href=" . $GLOBALS['base_url'] . "/campaign_posters/" .  $row1->path . " target='_blank' title='Click to view'>" . $row1->name . "</a>";

	            }
	        }
	    }
	    else {
	        $page_content .= "</table>";
	    }

	    return ['#type'=>'inline_template','#template'=>$page_content];
	}
}