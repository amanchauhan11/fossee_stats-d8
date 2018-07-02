<?php

namespace Drupal\fossee_stats\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Datetime\DrupalDateTime;
use \Drupal\Core\Ajax\AjaxResponse;
use \Drupal\Core\Ajax\RemoveCommand;
use \Drupal\Core\Ajax\ReplaceCommand;
use \Drupal\fossee_stats\Utility;

class PostalCampaignAddForm extends FormBase {

	public function getFormId(){
    	return 'PostalCampaignAddForm';
  	}

    public function buildForm(array $form, FormStateInterface $form_state) {

    	$form['foss_select'] = array(
            '#type' => 'select',
            '#title' => t('Select FOSS under which event took place'),
            '#options' => Utility::get_first_dropdown_options_foss_name(),
            '#default_value' => '',
            "#required" => TRUE
        );

        $form["name"] = array(
            "#type" => "textfield",
            "#title" => "Name",
            "#default_value" => '',
            "#required" => TRUE
        );
        $form['start_date'] = array(
            '#type' => 'date',
            '#title' => t('Event Date:'),
            '#date_year_range' => '2008:+3',
            '#default_value' => new DrupalDateTime(''),
            "#required" => TRUE,
        );

        $form["body"] = array(
            "#type" => "textarea",
            "#title" => "Details",
            "#default_value" => '',
            "#required" => TRUE
        );

        $form['poster_fieldset'] = array(
            '#type' => 'fieldset',
            '#tree' => TRUE,
            '#prefix' => '<div id="poster-fieldset-wrapper">',
            '#suffix' => '</div>'
        );

        if (empty($form_state->get('num_poster'))) {
            $form_state->set('num_poster', 1);
        }
        if($form_state->get('num_poster') == 1)
        {
            $form['poster_fieldset'][0]['name'] = array(
                '#title' => t('Add poster'),
                '#type' => 'file',
                '#weight' => '5',
                '#description' => t('Upload an poster'),
                '#name' => 'files[poster_fieldset_0_name]',
                '#prefix' => '<div id="poster-wrapper"> <div id="poster_0">',
                '#suffix' => '</div> </div>',
            );
        }

        $form['poster_fieldset']['add_name'] = array(
            '#type' => 'submit',
            '#value' => t('Add poster'),
            '#limit_validation_errors' => array(),
            '#submit' => array(
                [$this, 'poster_add_more_add_one']
            ),
            '#ajax' => array(
                'callback' => [$this, 'poster_add_more_add_one_callback'],
                'wrapper' => 'poster-wrapper',
                'method' => 'append'
            ),
        );

        $form['poster_fieldset']['remove_name'] = array(
            '#type' => 'submit',
            '#value' => t('Remove Poster'),
            '#limit_validation_errors' => array(),
            '#submit' => array(
                [$this, 'poster_add_more_remove_one']
            ),
            '#ajax' => array(
                'callback' => [$this, 'poster_remove_callback'],
            ),
        );
        $form["submit"] = array(
            "#type" => "submit",
            "#value" => "Submit"
        );
        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state){

        if (isset($_FILES['files'])) {
        /* check for valid filename extensions */
            foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
                if ($file_name) {
                    if (strstr($file_form_name, 'poster_fieldset'))
                        $file_type = 'I';
                    switch ($file_type) {
                        case 'I':
                            $allowed_extensions_str = \Drupal::state()->get('events_poster_extensions', '');
                            break;
                    }
                    $allowed_extensions = explode(',', $allowed_extensions_str);
                    foreach($allowed_extensions as $i=>$ext) $allowed_extensions[$i] = trim($ext);
                    $allowed_ext= explode('.', strtolower($_FILES['files']['name'][$file_form_name]));
                    $temp_extension = end($allowed_ext);
                    if (!in_array($temp_extension, $allowed_extensions))
                    {
                        //drupal_set_message($temp_extension);
                        $form_state->setError($form['poster_fieldset'], t('Only file with ') . $allowed_extensions_str . t(',extensions can be uploaded.'));
                    }

                }
            }
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state){

        $v = $form_state->getValues("values");
        $connection = \Drupal::database();
        $query = $connection->insert('postercampaign');

        $dest_path = preg_replace('/\s+/', '_', $v["name"]).time();
        $args = array(
            "foss_name" => $v["foss_select"],
            "p_name" => $v["name"],
            "startdate" => $v["start_date"],
            "body" => $v["body"],
            "poster_folder" => $dest_path,
        );
        $query->fields($args);
        /* storing the row id in $result */
        $result = $query->execute();

        /* For adding poster of events*/
        $items = array();
        $root_path = Utility::posters_path();
        mkdir($root_path . $dest_path);
        /* uploading files */
        foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
            if ($file_name) {
                $sfilename = preg_replace('/\s+/', '_', $file_name);
                if (file_exists($root_path . $dest_path .'/'. $sfilename)) {
                    drupal_set_message(t("Error uploading file. File !filename already exists."), array(
                        t('!filename') => $_FILES['files']['name'][$file_form_name]
                    ), 'error');
                    return;
                }
                $posterupload = 0;
                /* uploading file */
                if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path .'/'.$sfilename)) {
                    $posterquery = $connection->insert('postercampaign_poster');
                    $posterargs = array(
                        "p_id" => $result,
                        "name" => $_FILES['files']['name'][$file_form_name],
                        "path" => $dest_path .'/'. $sfilename
                    );
                    $posterquery->fields($posterargs);
                    /* storing the row id in $result */
                    $posterresult = $posterquery->execute();
                    if ($posterresult != 0) {
                        {$posterupload++; }
                    }
                    drupal_set_message($file_name . ' uploaded successfully.', 'status');
                }
                else {
                    drupal_set_message($file_name . $dest_path . t('was having an error while uploading :') , 'error');
                }
            }
        }
        if (!$result) {
            drupal_set_message(t("Something went wrong, please try again."), "error");
        }
        else {
            drupal_set_message(t("Postal Campaign added successfully"), "status");
            if ($posterupload != 0) {
                drupal_set_message(t("Event's Poster are added successfully") , "status");
            }
        }

    }

    public function poster_add_more_add_one_callback(array $form, FormStateInterface $form_state) {
        $form['poster_fieldset'][$form_state->get('num_poster')-1]['name'] = array(
            '#title' => t('Add poster'),
            '#type' => 'file',
            '#weight' => '5',
            '#description' => t('Upload an poster'),
            '#name' => 'files[poster_fieldset_'.($form_state->get('num_poster')-1).'_name]',
            '#prefix' => '<div id="poster_'.($form_state->get('num_poster')-1).'">',
            '#suffix' => '</div>'
        );
        return $form['poster_fieldset'][$form_state->get('num_poster')-1];
    }

    public function poster_add_more_add_one(array $form, FormStateInterface &$form_state) {
        $form_state->set('num_poster', $form_state->get('num_poster')+1);
        $form_state->setRebuild(); //should also work without setrebuild, but for some reason doesn't.
    }

    public function poster_add_more_remove_one(array $form, FormStateInterface &$form_state) {
        if ($form_state->get('num_poster') > 1) {
            $form_state->set('num_poster', $form_state->get('num_poster')-1);
            $form_state->set('poster_0', FALSE);
        }
        else{
            $form_state->set('poster_0', TRUE);
        }
        $form_state->setRebuild(); //should also work without setrebuild, but for some reason doesn't.
    }

    public function poster_remove_callback(array $form, FormStateInterface $form_state){
        $response = new AjaxResponse();
        if($form_state->get('poster_0')){
            $response->addCommand(new ReplaceCommand('#poster-wrapper', $form['poster_fieldset'][0]));
        }
        else
            $response->addCommand(new RemoveCommand('#poster_'.$form_state->get('num_poster')));
        return $response;
    }

}
