<?php

namespace Drupal\fossee_stats\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fossee_stats\Utility;

class WorkshopDeletePrompt extends FormBase {

	public function getFormId(){
    	return 'WorkshopDeletePrompt';
  	}

  	public function buildForm(array $form, FormStateInterface $form_state, $workshop_id = null){

  		$connection = \Drupal::database();
    	$query = $connection->select('workshop');
	    $query->fields('workshop');
	    $query->condition('w_id', $workshop_id);
	    $result = $query->execute();
	    $row = $result->fetchObject();

	    if(empty($row))
	       return $this->redirect('fossee_stats.workshop_display_form');

	    $form = array();
	    $form['text'] = array(
	        '#type'=>'item',
	        '#markup'=>'Are you sure you want to delete "'.$row->w_name.'"?',
	    );
	    $form['yes'] = array(
	        '#type'=>'submit',
	        '#value'=>'Yes',
	        '#name'=>'yes',
	    );
	    $form['no'] = array(
	        '#type'=>'submit',
	        '#value'=>'No',
	        '#name'=>'no',
	    );
	    $form['w_id'] = array(
	        '#type'=>'hidden',
	        '#value'=>$workshop_id,
	    );
	    return $form;
	}

	public function submitForm(array &$form, FormStateInterface $form_state) {

		$connection = \Drupal::database();
    	$query = $connection->select('workshop');
	    $workshop_id = $form_state->getValue('w_id');
	    if($form_state->getTriggeringElement('triggering_element')['#name'] == 'yes')
	    {
	        $query->fields('workshop');
	        $query->condition('w_id', $workshop_id);
	        $result = $query->execute();
	        $row = $result->fetchObject();
	        $root_path = Utility::events_images_path();
	        $eventfolder = $row->images_folder;
	        $dest_path = $eventfolder;
	        $dir_path = $root_path . $dest_path;
	        if (is_dir($dir_path)) {
	            $res = Utility::delete_directory($dir_path);
	            if (!$res) {
	                drupal_set_message(t('Cannot delete Event directory :') . $dir_path . t(',Please contact administrator.'), 'error');
	                return;
	            }
	        }
	        else {
	            drupal_set_message(t("Event directory not present :") . $dir_path . t(", Skipping deleting directory."), 'status');
	        }

	        $query2 = $connection->delete('workshop_images');
	        $query2->condition('w_id', $workshop_id);
	        $result2 = $query2->execute();
	        $query_t = $connection->delete('testimonials');
	        $query_t->condition('w_id', $workshop_id);
	        $result_t = $query_t->execute();
	        $query3 = $connection->delete('workshop');
	        $query3->condition('w_id', $workshop_id);
	        $result3 = $query3->execute();

	        if (!$result3) {
	            drupal_set_message(t("Something went wrong, please try again."), "error");
	        }
	        else {
	            if (!$result2 && (!$result_t)) {
	                drupal_set_message(t("Workshop Deleted successfully"), "status");
	            }
	            else {
	                drupal_set_message(t("Workshop, testimonials and related photos are Deleted successfully"), "status");
	            }
	        }
	    }
	    return $this->redirect('fossee_stats.workshop_display_form');
	}
	    
}
