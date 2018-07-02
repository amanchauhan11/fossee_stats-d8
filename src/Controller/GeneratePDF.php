<?php

namespace Drupal\fossee_stats\Controller;

use Drupal\Core\Controller\ControllerBase;

require_once(DRUPAL_ROOT.'/'.drupal_get_path('module', 'fossee_stats').'/tcpdf_include.php');


// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends \TCPDF {

    //Page header
    /*public function Header() {
        // Logo
           $image_file = K_PATH_IMAGES."fosseelogo.png";
     $this->Image($image_file, 10, 10, 25, 0, 'PNG', 'https://fossee.in');
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        //$this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }*/

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom

        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number

        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, FALSE, 'R', 0, '', 0, FALSE, 'T', 'M');

  		$html = '<a href="https://fossee.in" target="_blank" >https://fossee.in</a>';
  		$this->writeHTML($html, TRUE, FALSE, TRUE, FALSE, '');
    }
}

class GeneratePDF extends ControllerBase {

	public function content($workshop_id = null) {

		$connection = \Drupal::database();
		if(!is_numeric($workshop_id))
			return ['#type'=>'markup', '#markup'=>'<h3>Event not found</h3>'];
	
		// create new PDF document
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, TRUE, 'UTF-8', FALSE);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		//$pdf->SetAuthor('Free and Open Software in Education');
		$pdf->SetTitle('Free and Open Software in Education');
		//$pdf->SetSubject('TCPDF Tutorial');
		//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, "", "");

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
		  require_once(dirname(__FILE__) . '/lang/eng.php');
		  $pdf->setLanguageArray($l);
		}

		// ---------------------------------------------------------

		// set font
		//$pdf->SetFont('dejavusans', '', 10);

		// add a page
		$pdf->AddPage();
	    $page_content = "<h3>Event Detail :</h3><br>";
	    $page_content .= '<div style="justify-content: space-around;">';
	    $row = "";
	    $row1 = "";
	    $query = $connection->select('workshop');
	    $query->fields('workshop');
	    $query->condition('w_id', $workshop_id);
	    $result = $query->execute();
	    $no_of_result = $query->countQuery()->execute()->fetchField();
	    if(!$no_of_result)
	    	return ['#type'=>'markup', '#markup'=>'<h3>Event not found</h3>'];

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
		        $textdisplay .= "" . $var . ". " . $speakerrow->name . " <sub>" . $speakerrow->institute . ", " . $speakerrow->place . "</sub> <br>";
		    }
	    }


		$startdate = $row->startdate;
		$enddate = $row->enddate;
		$date = $row->startdate . " , " . $row->enddate;

		$daylen = 60 * 60 * 24;
		$date1 = $row->enddate;
		$date2 = $row->startdate;

		$dayCount = ((strtotime($date1) - strtotime($date2)) / $daylen) + 1;

		$page_content .= '<table cellpadding="3" width="100%" cellspacing="1" bordercolor="RED" border="1" style="text-align:left;border-collapse: collapse;"><tbody>';
		    $page_content .= '
		   <tr><td width="20%"><b>Name</b></td><td width="200%" >' . $row->w_name . '</td></tr>

		   <tr><td width="20%"><b>Start Date</b></td><td width="200%">' . $startdate . '</td></tr>

		   <tr><td width="20%"><b>End Date</b></td><td width="200%">' . $enddate . '</td></tr>

		   <tr><td width="20%"><b>No. of Participants</b></td><td width="200%">' . $row->no_of_participant . '</td></tr>

		   <tr><td width="20%"><b>Venue</b></td><td width="200%">' . $row->venue . '</td></tr>';

		if ($num_of_speakercount!=0) {
		    $page_content .= '<tr><td><b>Speakers</b></td><td>' . $textdisplay . '</td></tr>';
		    }

		$page_content .= '<tr><td width="20%"><b>Details</b></td><td align="justify"  width="200%">' . $row->body . '</td></tr>';

		if (trim(strlen($row->event_link))!=0) {
		 $page_content .= '<tr><td width="20%"><b>Event website</b></td><td width="200%" style="color:blue"><a href="' . $row->event_link . '" target="_blank">' . $row->event_link . '</a></td></tr>';
		}
		$page_content .= '</tbody></table>';

		/* For adding images to PDF*/

	    $query = $connection->select('workshop_images');
	    $query->fields('workshop_images');
	    $query->condition('w_id', $workshop_id);
	    $result = $query->execute();
	    $num_of_results = $query->countQuery()->execute()->fetchField();
	    if ($num_of_results!=0) {
	        $i=0;
	  		$page_content .= "<h3>Pictures</h3>";
	  		$page_content .= '<table border="" cellspacing="6" cellpadding="4"> <tr>';
        	while ($row1 = $result->fetchObject()) {
  				$i++;
			    if (($i%2)==0) {
				    $page='"' . $GLOBALS['base_url'] . "/events_images/" .  $row1->path . '"';
				    $page_content .='<td><img style="width:320px;height:240px;margin:5px 5px 5px 5px;" src=' . $page . '/></td>';
				    $page_content .= "</tr><tr>";
			  	}
				else {
				    $page = '"' . $GLOBALS['base_url'] . "/events_images/"  . $row1->path . '"';
				    $page_content .= '<td><img style="width:320px;height:240px;margin:5px 5px 5px 5px;" src=' . $page . '/></td>';
				}
			}
			if(($num_of_results%2)==0) {
				$page_content = substr($page_content, 0, -4);
				$page_content .= "</table>";
			}
			else{
	   			$page_content .= "</tr> </table>";
			}
	    }

	    /* For adding Testimonial to the  PDF*/
	    $testimonial_query = $connection->select('testimonials');
	    $testimonial_query->fields('testimonials');
	    $testimonial_query->condition('w_id', $workshop_id);
	    $testimonial_query->orderBy('t_id', 'ASC');
	    $testimonial_result = $testimonial_query->execute();
	    $testimonial_of_results = $testimonial_query->countQuery()->execute()->fetchField();

		if ($testimonial_of_results!=0) {
			$counter = 0;
			$page_content .= "<br><h3>Testimonials</h3>";
			$page_content .= '<ul style="list-style-type:square">';
			while ($testimonial_row = $testimonial_result->fetchObject()) {
				$page_content .= '<li>' . $testimonial_row->body . '   <sub>~' . $testimonial_row->name . ', ' . $testimonial_row->department . ' ' . $testimonial_row->university . '</sub>' . '</li><br>';
				$counter++;
				if ($counter==5)
					break;
			}
		    $page_content .= '</ul>';
		}


	    /* For adding videolinks to pdf*/
	    $videolink_query = $connection->select('workshop_videolinks');
	    $videolink_query->fields('workshop_videolinks');
	    $videolink_query->condition('w_id', $workshop_id);
	    $videolink_query->orderBy('v_id', 'ASC');
	    $videolink_result = $videolink_query->execute();
	    $videolink_of_results = $videolink_query->countQuery()->execute()->fetchField();

		if ($videolink_of_results!=0) {
			$page_content .= "<br><h3>Video Links</h3>";
			$page_content .= '<ul style="list-style-type:square">';
			while ($videolink_row = $videolink_result->fetchObject()) {
				$page_content .='<li><a href="' . $videolink_row->link . '" target="_blank" title="Click to watch video">' . $videolink_row->title . '</a>' . '</li><br>';
			}
		    $page_content .= '</ul>';
		}

		$page_content .= '</div>';

		// output the HTML content
		$pdf->writeHTML($page_content, TRUE, FALSE, TRUE, FALSE, '');


		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

		// reset pointer to the last page
		// $pdf->lastPage();

		// ---------------------------------------------------------

		//Close and output PDF document
		$pdf->Output(str_replace(' ',  '_', $row->w_name), 'I');

		//============================================================+
		// END OF FILE
		//============================================================+
		exit();
	}

}