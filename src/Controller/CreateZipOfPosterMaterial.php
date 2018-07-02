<?php

namespace Drupal\fossee_stats\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fossee_stats\Utility;

class CreateZipOfPosterMaterial extends ControllerBase {

	public function content($name = null){

		$root_path = Utility::posters_path();
		$eventfolder = $name;
		$zip_filename = $root_path . $eventfolder . ".zip";

		$zip = new \ZipArchive();
		// open archive
		if ($zip->open($root_path . $eventfolder . ".zip", \ZIPARCHIVE::CREATE) !== TRUE) {
		die ("Could not open archive");
		}

		try{
		    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root_path . $eventfolder . "/"));
		}
		catch(Exception $e){
		    return drupal_set_message('Failed to open directory: Invalid postalcampaign name or no posters added to postalcampaign yet.', 'error');
		}
		// iterate over the directory
		// add each file found to the archive
		foreach ($iterator as $key => $value) {

		//Check for valid file
		if (strpos(basename($key), '.') != 0) {
		$zip->addFile(realpath($key), basename($key)) or die("ERROR: Could not add file:$key");
		}
		}
		// close and save archive
		$zip->close();

		 /* download zip file */
	    header('Content-Type: application/zip');
	    header('Content-disposition: attachment; filename="' . $eventfolder . '.zip"');
	    header('Content-Length: ' . filesize($zip_filename));
	    ob_clean();
	    //flush();
	    readfile($zip_filename);
	    unlink($zip_filename);
	    //return ['#type'=>'markup','#markup'=>''];
	}
}