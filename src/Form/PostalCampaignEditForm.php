<?php

namespace Drupal\fossee_stats\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Datetime\DrupalDateTime;
use \Drupal\Core\Ajax\AjaxResponse;
use \Drupal\Core\Ajax\RemoveCommand;
use \Drupal\Core\Ajax\ReplaceCommand;
use \Drupal\fossee_stats\Utility;

class PostalCampaignEditForm extends FormBase {

	public function getFormId(){
    	return 'PostalCampaignEditForm';
  	}

  	public function buildForm(array $form, FormStateInterface $form_state, $postercampaign_id = null) {

  		$connection = \Drupal::database();
	  	$query = $connection->select('postercampaign')->fields('postercampaign')->condition('p_id', $postercampaign_id, '='); 
	    $result = $query->execute();
	    $row = $result->fetchObject();
	    $form = array();

	    $form['#attached']['library'][] = 'fossee_stats/image_styles';
        $form['foss_select'] = array(
            '#type' => 'select',
            '#title' => t('Select FOSS under which event took place'),
            '#options' => Utility::get_first_dropdown_options_foss_name(),
            '#default_value' => $row->foss_name,
            "#required" => TRUE
        );
        $form["name"] = array(
            "#type" => "textfield",
            "#title" => "Name",
            "#default_value" => $row->p_name,
            "#required" => TRUE
        );
        $form['start_date'] = array(
            '#type' => 'date',
            '#title' => t('Event Date:'),
            '#date_year_range' => '2008:+3',
            '#default_value' => $row->startdate,
            "#required" => TRUE,
        );
        $form["body"] = array(
            "#type" => "textarea",
            "#title" => "Details",
            "#default_value" => $row->body,
            "#required" => TRUE
        );
        /*Edit Speakers*/
        /*Edit Testimonial*/

        $form['uploadphotos'] = array(
            '#markup' => '<h5><p> Edit Poster</p></h5>(Select Check box to delete existing poster)',
            '#prefix' => '<div id="uploaded_poster"><table><tr>',
            '#suffix' => ''
        );

        $query = $connection->select('postercampaign_poster');
        $query->fields('postercampaign_poster');
        $query->condition('p_id', $postercampaign_id);
        $result = $query->execute();
        while ($row = $result->fetchObject()) {
            $suffix="";
            $ext = end(explode(".", $row->path));
            if (($ext=="png")||($ext=="PNG")||($ext=="jpeg")||($ext=="JPEG")||($ext=="jpg")||($ext=="JPG")) {
                 $suffix='<img style="width:100px; padding-left :20px;height:100px" src=' . $GLOBALS['base_url'] . "/campaign_posters/" . $row->path . ' />';
            }
            else {
                $suffix='<a href='.$GLOBALS['base_url']."/campaign_posters/".$row->path.'>'.$row->name.'</a>';
            }
            $form['postercheck@' . $row->id] = array(
                '#type' => 'checkbox',
                '#field_suffix' => $suffix,
            );
        }
        $form['enduploadposter'] = array(
            '#markup' => '',
            '#prefix' => '',
            '#suffix' => '</tr></table></div>'
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
        $form["postercampaign_id"] = array(
	        "#type" => "hidden",
	        "#value" => $postercampaign_id
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

    public function submitForm(array &$form, FormStateInterface $form_state) {

    	$v = $form_state->getValues("values");
  		$posterupload = 0;

  		$connection = \Drupal::database();
        $p_id = $v["postercampaign_id"];
        $query = $connection->update('postercampaign');
        $query->fields(array(
            'foss_name' => $v["foss_select"],
            'p_name' => $v["name"],
            'startdate' => $v["start_date"],
            'body' => $v["body"]
        ));
        $query->condition('p_id', $v["postercampaign_id"]);
        $result = $query->execute();

        /* For editing Event images */
        /* For deleting existing images */
        /* For deleting existing images */
        $deletecounter = 0;
        $query_poster = $connection->select('postercampaign_poster');
        $query_poster->fields('postercampaign_poster');
        $query_poster->condition('p_id', $v["postercampaign_id"]);
        $result_poster = $query_poster->execute();
        $root_path=Utility::posters_path();
        while ($row_poster = $result_poster->fetchObject()) {
            if ($form_state->getValue('postercheck@' . $row_poster->id) == 1) {
                $query2 = $connection->delete('postercampaign_poster');
                $query2->condition('id', $row_poster->id);
                $delete_poster = $query2->execute();
                if ($delete_poster != 0) {
                    $deletecounter++;
                }
                else {
                    drupal_set_message(t("Error: Could not delete database record". $row_poster->name));
                }
                if (file_exists($root_path . $row_poster->path)) {
                    unlink($root_path . $row_poster->path);                   
                }
                else {
                    drupal_set_message(t('Notice: File no longer exists "') . $row_poster->name . t('", file does not exist'), 'error');
                }
            }
        }
        /* For adding more images to existing event */
        $root_path = Utility::posters_path();
        $query = $connection->select('postercampaign');
        $query->fields('postercampaign');
        $query->condition('p_id', $p_id); 
        $row = $query->execute()->fetchObject();

        if(!file_exists($root_path.$row->poster_folder)){
            $dest_path = preg_replace('/\s+/', '_', $row->p_name).time();
            $dirresult = mkdir($root_path.$dest_path);
            $result = $connection->update('postercampaign')->fields(array('poster_folder'=>$dest_path))->condition('p_id', $p_id)->execute();
            if(!$result or !$dirresult)
             { drupal_set_message(t('Error: Could not create directory'), 'error'); return; }
        }

        $query = $connection->select('postercampaign');
        $query->fields('postercampaign');
        $query->condition('p_id', $p_id);
        $postercampaign = $query->execute()->fetchObject();
        $dest_path = $postercampaign->poster_folder;
        /* uploading files */
        foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
            if ($file_name) {
                if (file_exists($root_path . $dest_path.'/'. preg_replace('/\s+/', '_',$file_name))) {
                    drupal_set_message(t("Error uploading file. File '".$file_name."' already exists."), 'error');
                    return;
                }
                $posterupload = 0;

                /* uploading file */
                if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path .$dest_path. '/'.preg_replace('/\s+/', '_',$file_name))) {
                    $posterquery = $connection->insert('postercampaign_poster');
                    $posterargs = array(
                        "p_id" => $v["postercampaign_id"],
                        "name" => $_FILES['files']['name'][$file_form_name],
                        "path" => $dest_path . '/'.preg_replace('/\s+/', '_', $file_name),
                    );
                    $posterquery->fields($posterargs);
                    /* storing the row id in $result */
                    $posterresult = $posterquery->execute();
                    if ($posterresult != 0) {
                        $posterupload++;
                    }
                    drupal_set_message($file_name . ' uploaded successfully.', 'status');
                }
                else {
                    drupal_set_message($file_name . $dest_path . t('was having an error while uploading :') , 'error');
                }
            }
        }
        if (!$result && $posterupload == 0 && $deletecounter == 0) {
            drupal_set_message(t("Something went wrong, please try again."), "error");
        }
        else {
            drupal_set_message(t("Postal Campaign updated successfully"), "status");
            if ($posterupload != 0) {
                drupal_set_message(t("Event's Poster are added successfully"), "status");
            }
            if ($deletecounter != 0) {
                drupal_set_message(t("Event's Poster are deleted successfully"), "status");
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