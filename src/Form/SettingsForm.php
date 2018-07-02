<?php

namespace Drupal\fossee_stats\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fossee_stats\Utility;

class SettingsForm extends FormBase {

	public function getFormId(){
		return 'SettingsForm';
	}

	public function buildForm(array $form, FormStateInterface $form_state) {
		  $form['extensions']['image'] = array(
		    '#type' => 'textfield',
		    '#title' => t('Allowed image file extensions'),
		    '#description' => t('A comma separated list WITHOUT SPACE of source file extensions that are permitted to be uploaded on the server'),
		    '#size' => 50,
		    '#maxlength' => 255,
		    '#required' => TRUE,
		    '#default_value' => \Drupal::state()->get('events_image_extensions', ''),
		  );
		  $form['extensions']['poster'] = array(
		    '#type' => 'textfield',
		    '#title' => t('Allowed poster file extensions'),
		    '#description' => t('A comma separated list WITHOUT SPACE of source file extensions that are permitted to be uploaded on the server'),
		    '#size' => 50,
		    '#maxlength' => 255,
		    '#required' => TRUE,
		    '#default_value' =>  \Drupal::state()->get('events_poster_extensions', ''),
		  );
		  $form['maxuploadsize'] = array(
		    '#type' => 'textfield',
		    '#title' => t('Maximum file upload size(MB): '),
		    '#description' => t('Maximum allowed size of upload file in MB'),
		    '#maxlength' => 5,
		    '#required' => TRUE,
		    '#default_value' =>  \Drupal::state()->get('max_upload_size', ''),
		  );

		  $form['email_id_bcc'] = array(
		    '#type' => 'textfield',
		    '#title' => t('Email of admin to be notified after addition/change in any workshop details'),
		    '#description' => t('An email will be sent to this id if any user changes/adds any workshop detail along with the email sent to that user'),
		    '#required' => TRUE,
		    '#default_value' =>  \Drupal::state()->get('email_bcc', ''),
		  );

		  $form['submit'] = array(
		    '#type' => 'submit',
		    '#value' => t('Submit')
		  );
		  return $form;
	}

	public function submitForm(array &$form, FormStateInterface $form_state){
	  \Drupal::state()->set('events_image_extensions', $form_state->getValue('image'));
	  \Drupal::state()->set('events_poster_extensions', $form_state->getValue('poster'));
	  \Drupal::state()->set('max_upload_size', $form_state->getValue('maxuploadsize'));
	  \Drupal::state()->set('email_bcc', $form_state->getValue('email_id_bcc'));
	  drupal_set_message(t('Settings updated'), 'status');
	}
}