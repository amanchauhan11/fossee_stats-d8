<?php

namespace Drupal\fossee_stats\Controller;

use Drupal\Core\Controller\ControllerBase;

class WorkshopViewDetailsAll extends ControllerBase {

	public function content($workshop_id = null){

		$page_content = "";
	    $row = "";
	    $row1 = "";
	    $connection = \Drupal::database();
	    $query = $connection->select('workshop');
	    $query->fields('workshop');
	    $query->condition('w_id', $workshop_id);
	    $result = $query->execute();
	    $row = $result->fetchObject();
	    $speakerquery = $connection->select('speakers');
	    $speakerquery->fields('speakers');
	    $speakerquery->condition('w_id', $workshop_id);
	    $speakerresult = $speakerquery->execute();
	    $num_of_speakercount = $speakerquery->countQuery()->execute()->fetchField();
	    if ($num_of_speakercount!=0) {
		    $textdisplay = "";
		    $counter = 1;
		    while ($speakerrow = $speakerresult->fetchObject()) {
		        $var = $counter++;
		        $textdisplay .= "" . $var . ". " . $speakerrow->name . "<sub class='speaker-name'>" . $speakerrow->institute . ", " . $speakerrow->place . "</sub><br><br>";
	    	}
	    }
	    //$starttime= strftime('%I:%M %p', strtotime($row->starttime));
	    //$endtime= strftime('%I:%M %p', strtotime($row->endtime));
	    $event_link=$row->event_link;

	    $startdate = $row->startdate;
	    $enddate = $row->enddate;
	    $date = $row->startdate . " , " . $row->enddate;
	    //$date1=date_create($row->startdate);
	    //$date2=date_create($row->enddate);
	    //$diff=date_diff($date1,$date2);
	    //$date1=date_create($row->startdate);
	    //$date2=date_create($row->enddate);
	    //$diff=date_diff($date1,$date2);
	    //$duration= $diff->format("%a");
	    $daylen = 60 * 60 * 24;
	    $date1 = $row->enddate;
	    $date2 = $row->startdate;
	    //echo (strtotime($date1)-strtotime($date2))/$daylen;
	    $dayCount = ((strtotime($date1) - strtotime($date2)) / $daylen) + 1;
	    //$time=$starttime." - ".$endtime;
	    $page_content .= "<table class='table table-bordered table-hover' >";
	    //if($row->foss_name=="FOSSEE-Laptop"){
		$page_content .= "<div class='pdf-generate'><a href=" . $GLOBALS['base_url'] . "/events/pdfgenerate/" . $workshop_id . " target='_blank' title= 'Click to view/download as PDF ' >Download as PDF</a></div>";
	//}
	    $page_content .= "<tr><td><b>Name</b></td><td>{$row->w_name}</td></tr>";
	    $page_content .= "<tr><td><b>Start Date</b></td><td>$startdate</td></tr>";
	    $page_content .= "<tr><td><b>End Date</b></td><td>$enddate</td></tr>";
	    $page_content .= "<tr><td><b>No. of Participants</b></td><td>{$row->no_of_participant}</td></tr>";
	    $page_content .= "<tr><td><b>Venue</b></td><td>{$row->venue}</td></tr>";
	     if ($num_of_speakercount!=0) {
	    $page_content .= "<tr><td><b>Speakers</b></td><td>$textdisplay</td></tr>";
	    }
	    $page_content .= "<tr><td><b>Details</b></td><td>{$row->body}</td></tr>";
	    if (trim(strlen($event_link)!=0)) {
	  	$page_content .= '<tr><td><b>Event website</b></td><td><a href=' . $event_link . ' target="_blank" title="Click to view event site">' . $event_link . '</a></td></tr>';
	    }
	    $query = $connection->select('workshop_images');
	    $query->fields('workshop_images');
	    $query->condition('w_id', $workshop_id);
	    $result = $query->execute();
	    $num_of_results = $query->countQuery()->execute()->fetchField();
	    if ($num_of_results != 0) {
	        $page_content .= "<tr><td><b>Pictures</b></td><td>";
	        while ($row1 = $result->fetchObject()) {
	          $eventfolder = $row1->path;
	            $page_content .= "<a class='fancybox' rel='gallery1' href=" . $GLOBALS['base_url'] . "/events_images/" . $row1->path . "><img class='event-image'   src=" . $GLOBALS['base_url'] . "/events_images/" . $row1->path . " /></a>";
	        }
	        $page_content .= "</td></tr></table>";
	    }
	    else {
	        $page_content .= "</table>";
	    }
	    $query = $connection->select('testimonials');
	    $query->fields('testimonials');
	    $query->condition('w_id', $workshop_id);
	    $query->orderBy('t_id', 'DESC');
	    $result = $query->execute();
	    $num_of_results = $query->countQuery()->execute()->fetchField();
	    if ($num_of_results != 0) {
	        if ($num_of_results > 1) {
			    $page_content .= "<div id='testimonials_front'><div><br>
			    <a href=" . $GLOBALS['base_url'] . "/events/testimonials/{$workshop_id} target='_blank' title='Click to view all testimonials'> <h4>Testimonials</h4></a>
				<br></div><ul>";
			    $page_testimonial = "";
			    while ($row = $result->fetchObject()) {
			        if (strlen($row->body) >= 1) {
			    	    if (strlen($row->body) >= 260) {
			                $cut1 = substr($row->body, 0, 200);
			                $cut = $cut1 . "  ...   ";
			                $page_testimonial = "<li><p class='testimonial-row-body'>{$cut} <a href=" . $GLOBALS['base_url'] . "/events/testimonials/view/" . $row->t_id . " name=" . $row1->t_id . " id=" . $row1->t_id . "  class='autodialog' title='Click to read more'>Read more</a></p><br>    <p class='testimonial-row-name-dept-un'>{$row->name},{$row->department},</p> <p class='testimonial-row-name-dept-un'>{row->university}.</p></li>";
		                }
			            else {
			                $page_testimonial = "<li><p class='testimonial-row-body'>{$row->body}</p> <br> <p class='testimonial-row-name-dept-un'>{$row->name},{$row->department},</p>
		                        <p class='testimonial-row-name-dept-un'>{$row->university}.</p>
		                        </li>";
			            }
			        }
		        	$page_content .= $page_testimonial;
		    	}
	    		$page_content .= "</ul></div>";
			}
	        else {
	            $page_content .= "<div id='testimonials_one'><div><br>
				<a href=" . $GLOBALS['base_url'] . "/events/testimonials/{$workshop_id} target='_blank' title='Click to view all testimonials'><h4>Testimonials</h4></a>
				<br></div><ul>";
	            $page_testimonial = "";
	            while ($row = $result->fetchObject()) {
	                if (strlen($row->body) >= 1) {
	                    if (strlen($row->body) >= 260) {
	                        $cut1 = substr($row->body, 0, 200);
	                        $cut = $cut1 . "  ...   ";
	                        $page_testimonial = "<li><p class='testimonial-row-body'>{$cut}<a href=" . $GLOBALS['base_url'] . "/events/testimonials/view/" . $row->t_id . " name=" . $row1->t_id . " id=" . $row1->t_id . "  class='autodialog' title='Click to read more'>Read more</a></p>
	                                              <br>
	                                                <p class='testimonial-row-name-dept-un'>{$row->name},{$row->department},</p>
	                                                <p class='testimonial-row-name-dept-un'>{$row->university}.</p>
	                                                </li>";
	                    }
	                    else {
	                        $page_testimonial = "<li><p class='testimonial-row-body'>$row->body</p>
	                                              <br>
	                                                <p class='testimonial-row-name-dept-un'>{$row->name},{$row->department},</p>
	                                                <p class='testimonial-row-name-dept-un'>{$row->university}.</p>
	                                                </li>";
	                    }
	                }
	                $page_content .= $page_testimonial;
	            }
	            $page_content .= "</ul></div>";
	        }
	    }

	    $videolink_query = $connection->select('workshop_videolinks');
	    $videolink_query->fields('workshop_videolinks');
	    $videolink_query->condition('w_id', $workshop_id);
	    $videolink_query->orderBy('v_id', 'ASC');
	    $videolink_result = $videolink_query->execute();
	    $videolink_num_of_results = $videolink_query->countQuery()->execute()->fetchField();

	    if ($videolink_num_of_results!=0) {

		  	$page_content .= "<br><h3>Video Links</h3>";
		  	$page_content .= '<ul class="vid-link">';
		   	while ($videolink_row = $videolink_result->fetchObject()) {
		  	$page_content .='<li><a href="' . $videolink_row->link . '" target="_blank" title="Click to watch video">' . $videolink_row->title . '</a>' . '</li><br>';
		    }
		  	$page_content .= '</ul>';
		}

		return [
			'#type' => 'item',
			'#markup' => $page_content,
			'#attached' => [
				'library' => ['fossee_stats/testimonials'],
			],
		];
		return $element;
	}

}
			
	