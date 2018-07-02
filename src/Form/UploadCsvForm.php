<?php

namespace Drupal\fossee_stats\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fossee_stats\Utility;
use Drupal\Core\Url;
use Drupal\Core\Link;

require_once(DRUPAL_ROOT.'/'.drupal_get_path('module', 'fossee_stats').'/parsecsv-for-php/parsecsv.lib.php');

class UploadCsvForm extends FormBase {

	public function getFormId(){
    	return 'UploadCsvForm';
  	}

	public function buildForm(array $form, FormStateInterface $form_state) {
	    $form['csv_file'] = array(
	    '#name' => 'files[csv_file]',
	    '#type' => 'file', //you can find a list of available types in the form api
	    '#title' => 'Upload csv file form events',
	    );

	    $form['submit_button'] = array(
	    '#type' => 'submit',
	    '#value' => t('Submit'),
	    );

	    $form['edit'] = array(
	        '#type' => 'item',
	        '#markup' => Link::fromTextAndUrl(t('Click here to edit event and add images'), Url::fromUri('internal:/events/edit'))->toString(),
	        '#weight' => 100,
	    );
	    return $form;
	}

	public function validateForm(array &$form, FormStateInterface $form_state) {

	    if(!isset($_FILES['files']['name']['csv_file']))
	        drupal_set_message(t("!Error uploading csv file"), 'error');

	    $file_path = $_FILES['files']['tmp_name']['csv_file'];
	    if(Utility::get_file_size_MB($file_path) > \Drupal::state()->get('max_upload_size', 1))
	        { drupal_set_message(t('Max file upload size('.\Drupal::state()->get('max_upload_size', 1).'MB) exceeded'), 'error'); $form_state->set('csv_error_flag', TRUE); return; }

	    $csv = new \ParseCsv\Csv($file_path);

	    //validating csv header
	    $std_header = array('foss_name'=>0, 'type'=>0, 'w_name'=>0, 'body'=>0, 'no_of_participant'=>0, 'event_link'=>0, 'startdate'=>0, 'starttime'=>0, 'enddate'=>0, 'endtime'=>0, 'venue'=>0, 'street'=>0, 'country'=>0, 'state'=>0, 'city'=>0, 'pincode'=>0);

	    $user_header = array_keys($csv->data[0]);
	    foreach($user_header as $i=>$value)
	    {
	        if(!in_array($value, array_keys($std_header)))
	            { drupal_set_message(t('Field '.'"'.$value.'"'.' does not match any expected field'), 'error'); $form_state->set('csv_error_flag', TRUE); }
	        else
	            $std_header[$value]++;
	    }
	    foreach($std_header as $key=>$value)
	    {
	        if($value > 1)
	           { drupal_set_message(t("Multiple values for field ".$key), 'error'); $form_state->set('csv_error_flag', TRUE); }
	        else if($value == 0 and !in_array($key, array('event_link', 'starttime', 'endtime')))
	           { drupal_set_message(t("Field ".$key." is missing"), 'error'); $form_state->set('csv_error_flag', TRUE); }
	    }

	    if(null !== $form_state->get('csv_error_flag'))
	        return;

	    //validating csv data
	    foreach($csv->data as $rno=>$row)
	    {
	        foreach($row as $key=>$value)
	        {
	            if( !in_array($key, array('event_link', 'starttime', 'endtime')) and ($value == '' or $value == NULL))
	                { drupal_set_message(t("Missing value for field '".$key."' in row ".($rno+1)), 'error'); $form_state->set('csv_error_flag', TRUE); }
	        }
	    }
	}

	public function submitForm(array &$form, FormStateInterface $form_state) {

		$connection = \Drupal::database();
        $account = \Drupal::currentUser();

	    if(null !== $form_state->get('csv_error_flag'))
	        return;

	    $file_path = $_FILES['files']['tmp_name']['csv_file'];
	    $csv = new \ParseCsv\Csv($file_path);

	    $fields = array_keys($csv->data[0]);
	    array_push($fields, 'images_folder');
	    $insertquery = $connection->insert('workshop')->fields($fields);

	    $eventsfolder = array();
	    foreach($csv->data as $i=>$rows)
	    {
	        $foldername = str_replace(' ', '_', $rows["w_name"]) . time();
	        $root_path = Utility::events_images_path();
	        $dest_path = $foldername . '/';
	        if (!is_dir($root_path . $dest_path)) {
	            mkdir($root_path . $dest_path);
	        }
	        array_push($eventsfolder, $foldername);
	    }

	    foreach($csv->data as $i=>$record)
	    {
	        if($record['starttime'] == NULL or $record['starttime'] == '')
	            $record['starttime'] = "00:00";
	        else if($record['endtime'] == NULL or $record['endtime'] == '')
	            $record['endtime'] = "00:00";
	        $record['images_folder'] = $eventsfolder[$i];
	        $record['last_edited_by'] = $account->id();
	        $insertquery->values($record);
	    }
	    $insertquery->execute();
	    drupal_set_message(t("Successfully submitted"));

	    $mailManager = \Drupal::service('plugin.manager.mail');
	    $mailManager->mail('fossee_stats', 'upload_csv_mail', $account->getEmail(), $account->getPreferredLanguage(), array('csv_filename'=>$_FILES['files']['name']['csv_file'],NULL, TRUE));
	}

}