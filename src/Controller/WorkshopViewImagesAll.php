<?php

namespace Drupal\fossee_stats\Controller;

use Drupal\Core\Controller\ControllerBase;

class WorkshopViewImagesAll extends ControllerBase {

	public function content($wid = null) {
		$element = ['#type' => 'inline_template'];
		$page_content = "";
		$connection = \Drupal::database();
	    $query = $connection->select('workshop_images');
	    $query->fields('workshop_images');
	    $query->condition('w_id', $wid);
	    $result = $query->execute();
	    if($query->countQuery()->execute()->fetchField() == 0)
	        return ["#type"=>"markup", "#markup"=>"No images for this event"];

	    $result1 = $query->execute();
	    $image_urls = array();
	    while($row1 = $result1->fetchObject()){
	        array_push($image_urls, $GLOBALS['base_url'].
	            '/events_images/'.$row1->path);
	    }
	    $page_content .= '<div id="myNav" class="overlay">
	    <div class="overlay-content">
	    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
	    <input id="previous-image" type="image" src="'.$GLOBALS['base_url'].'/'.drupal_get_path("module", "fossee_stats") . "/images/left-arrow.png".'" style="margin-right:5%;"> <img id="overlay-image" src="" style="height:500px;width:500px;"/> <input id="next-image" type="image" src="'.$GLOBALS['base_url'].'/'.drupal_get_path("module", "fossee_stats") . "/images/right-arrow.png".'" style="margin-left:5%;""> </div></div>';

	    $page_content .= "<table class='tablew tablew-bordered tablew-hover' > <tr>";    
	    $i=0;
	    while($row = $result->fetchObject()) {
	        $i++;
	        if(($i%3)==0){
	            $page_content .= '<td>'.$row->name.'<br> <img src="'.$GLOBALS['base_url'] . '/events_images/' .$row->path.'" style="height:200px;width:200px;cursor:pointer;" onclick="openNav('.$i.')"/> </td> </tr><tr>';
	        }
	        else {
	            $page_content .= '<td>'.$row->name.'<br> <img src="'.$GLOBALS['base_url'].
	            '/events_images/'.$row->path.'" style="height:200px;width:200px;cursor:pointer;" onclick="openNav('.$i.')"/> </td>';
	        }
	    }
	    $page_content .= '</tr></table>';
	    $element["#attached"]["library"][] = 'fossee_stats/image_overlay';
	    $element['#attached']['drupalSettings']['image_urls'] = $image_urls;
	    $element['#template'] = $page_content;
		return $element;
	}

}