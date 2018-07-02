<?php

namespace Drupal\fossee_stats\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fossee_stats\Utility;

class PostalCampaignDelete extends ControllerBase {

	public function content($postercampaign_id = null) {

		if(!$postercampaign_id or !is_numeric($postercampaign_id))
			return $this->redirect('fossee_stats.postalcampaign_view_withoutfilter');

		$page_content = "";
		$connection = \Drupal::database();
	    $query = $connection->select('postercampaign');
	    $query->fields('postercampaign');
	    $query->condition('p_id', $postercampaign_id);
	    $result = $query->execute();
	    $row = $result->fetchObject();
	    if($query->countQuery()->execute()->fetchField() == 0)
	    	return ['#type'=>'markup','#markup'=>'Postal campaign not found'];

	    $root_path = Utility::posters_path();
	    $eventfolder = $row->poster_folder; 
	    $dest_path = $eventfolder;
	    $dir_path = $root_path . $dest_path;

	    $query2 = $connection->delete('postercampaign_poster');
	    $query2->condition('p_id', $postercampaign_id);
	    $result2 = $query2->execute();

	    if (is_dir($dir_path)) {
	        $res = Utility::delete_directory($dir_path);
	        if (!$res) {
	            drupal_set_message(t("Cannot delete Campaign directory :") . $dir_path . t(",Please contact administrator."), 'error');
	            return;
	        }
	    }
	    else {
	        drupal_set_message(t("Event directory not present :") . $dir_path . t(", Skipping deleting directory."), 'status');
	    }

	    $query3 = $connection->delete('postercampaign');
	    $query3->condition('p_id', $postercampaign_id);
	    $result3 = $query3->execute();

	    if (!$result3) {
	        drupal_set_message(t("Something went wrong, please try again."), "error");
	    }
	    else {
	        if (!$result2) {
	            drupal_set_message(t("Campaign Deleted successfully"), "status");
	        }
	        else {
	            drupal_set_message(t("Campaign, related poster material are deleted successfully"), "status");
	        }
	    }
	    return ['#type'=>'inline_template', '#template'=>$page_content];
	}
}