<?php

namespace Drupal\fossee_stats\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Datetime\DrupalDateTime;
use \Drupal\Core\Ajax\AjaxResponse;
use \Drupal\Core\Ajax\RemoveCommand;
use Drupal\fossee_stats\Utility;

class WorkshopAddForm extends FormBase {

	public function getFormId(){
    	return 'WorkshopAddForm';
  	}

    public function buildForm(array $form, FormStateInterface $form_state) {

	    $form = array();
	    $form['#attached']['library'][] = 'fossee_stats/validators';

        $form['foss_select'] = array(
            '#type' => 'select',
            '#title' => t('Select FOSS under which event took place'),
            '#options' => $this->get_first_dropdown_options_foss_name(),
            '#default_value' => '',
            "#required" => TRUE
        );
        $form['type'] = array(
            '#type' => 'select',
            '#title' => t('Selected Type'),
            '#options' => array(
                'Workshop' => t('Workshop'),
                'Conference' => t('Conference'),
                'Events' => t('Events')
            ),
            '#default_value' => '',
            "#required" => TRUE
        );
        $form["name"] = array(
            "#type" => "textfield",
            "#title" => "Name",
            "#default_value" => '',
            "#required" => TRUE,
            "#maxlength" => 100,
        );
        $form['start_date'] = array(
            '#type' => 'datetime',
            '#title' => 'Start Date and Time',
            '#date_year_range' => '2008:+3',
            '#default_value' => '',//new DrupalDateTime('2000-01-01 00:00:00'),
            //'#date_format' => array('month' => 9, 'day' => 6, 'year' => 1962),// h:i A',
            "#required" => TRUE,
        );

        $form['end_date'] = array(
            '#type' => 'datetime',
            '#title' => 'End Date and Time',
            //'#date_increment' => 15,
            '#date_year_range' => '2008:+3',
            '#default_value' => '',
            //'#date_format' => 'Y-m-d h:i A',
            /*'#attributes' => array(
                'autocomplete' => 'off',
                'readonly' => 'readonly'
            ),*/
            "#required" => TRUE,
            '#suffix' => t('<p id="validate_date" style="visibility:hidden; color:red;">Invalid dates! End date can\'t be earlier than start date.</p> <p id="validate_time" style="visibility:hidden; color:red">Invalid time! End\'t time cannot be earlier than start time</p>')
        );
        $form["venue"] = array(
            "#type" => "textfield",
            "#title" => "Venue",
            "#default_value" => '',
            "#required" => TRUE,
            "#maxlength" => 100,
        );
        $form["street"] = array(
            "#type" => "textfield",
            "#title" => "Street",
            "#default_value" => '',
            "#required" => TRUE,
            "#maxlength" => 100,
        );
        $form['country'] = array(
            '#type' => 'select',
            '#title' => t('Country'),
            '#options' => array(
                'India' => 'India',
                'Others' => 'Others'
            ),
            '#required' => TRUE,
            '#tree' => TRUE,
            '#validated' => TRUE
        );
        $form['other_country'] = array(
            '#type' => 'textfield',
            '#title' => t('Other than India'),
            '#size' => 100,
            '#attributes' => array(
                'placeholder' => t('Enter your country name')
            ),
            '#states' => array(
                'visible' => array(
                    ':input[name="country"]' => array(
                        'value' => 'Others'
                    )
                )
            )
        );
        $form['other_state'] = array(
            '#type' => 'textfield',
            '#title' => t('State other than India'),
            '#size' => 100,
            '#attributes' => array(
                'placeholder' => t('Enter your state/region name')
            ),
            '#states' => array(
                'visible' => array(
                    ':input[name="country"]' => array(
                        'value' => 'Others'
                    )
                )
            )
        );
        $form['other_city'] = array(
            '#type' => 'textfield',
            '#title' => t('City other than India'),
            '#size' => 100,
            '#attributes' => array(
                'placeholder' => t('Enter your city name')
            ),
            '#states' => array(
                'visible' => array(
                    ':input[name="country"]' => array(
                        'value' => 'Others'
                    )
                )
            )
        );

        $form['other_pincode'] = array(
            '#type' => 'textfield',
            '#title' => t('Pincode'),
            '#size' => 30,
            '#maxlength' => 6,
            '#attributes' => array(
                'placeholder' => 'Enter pincode....'
            ),
            '#states' => array(
                'visible' => array(
                    ':input[name="country"]' => array(
                        'value' => 'Others'
                    )
                )
            ),
        );

        $form['all_state'] = array(
            '#type' => 'select',
            '#title' => t('State'),
            '#options' => $this->_list_of_states(),
            '#validated' => TRUE,
            '#states' => array(
                'visible' => array(
                    ':input[name="country"]' => array(
                        'value' => 'India'
                    )
                )
            ),
            '#ajax'=> array(
                'callback'=>[$this, 'list_city_for_state'],
                'wrapper'=>'city-wrapper',
            )
        );

        $form['city'] = array(
            '#type' => 'select',
            '#title' => t('City'),
            '#options' => $this->_list_of_cities($form_state->getValue('all_state')),
            '#validated' => TRUE,
            '#states' => array(
                'visible' => array(
                    ':input[name="country"]' => array(
                        'value' => 'India'
                    )
                )
            ),
            '#ajax'=> array(
                'callback'=>[$this, 'list_pincode_for_city'],
                'wrapper'=>'pincode-wrapper',
            ),
            '#prefix'=>'<div id="city-wrapper">',
            '#suffix'=>'</div>',
        );

        $form['pincode'] = array(
            '#type' => 'select',
            '#title' => t('Pincode'),
            '#validated' => TRUE,
            '#options' => $this->_list_of_pincodes($form_state->getValue('city')),
            '#states' => array(
                'visible' => array(
                    ':input[name="country"]' => array(
                        'value' => 'India'
                    )
                )
            ),
            '#prefix'=>'<div id="pincode-wrapper">',
        	'#suffix'=>'</div>',
        );

        $form["no_of_participant"] = array(
            "#type" => "textfield",
            "#title" => "Number Of Participants",
            "#default_value" => '',
            "#required" => TRUE
        );

        $form["event_link"] = array(
            "#type" => "textfield",
            "#title" => "Link to event's website",
            "#default_value" => '',
            '#suffix' => t('<p id="validate_url" style="visibility:hidden; color:red;">Invalid url! Url must start with http:// or https://</p>'),
            '#attributes' => array(
            	'id' => array('edit-event-link'),
            ),
        );
        $form["body"] = array(
            "#type" => "textarea",
            "#title" => "Details (Max characters: 2000)",
            "#default_value" => '',
            "#maxlength" => 2000,
            "#required" => TRUE,
        );
        $form['speakers_fieldset'] = array(
            '#type' => 'fieldset',
            '#tree' => TRUE,
            '#prefix' => '<div id="speakers-fieldset-wrapper"',
            '#suffix' => '</div>',
        );
        if (empty($form_state->get('num_speakers'))) {
            $form_state->set('num_speakers', 1);
        }
        $temp = 0;
        for ($i = 0; $i < $form_state->get('num_speakers'); $i++) {
            $temp1 = $i;
            $form['speakers_fieldset'][$i]["s_text"] = array(
                "#type" => "item",
                "#markup" => "<h4><label>Speaker : " . ($temp1 + 1) . "</label></h4>"
            );
            $form['speakers_fieldset'][$i]["speakername"] = array(
                "#type" => "textfield",
                "#title" => "Name: ",
                "#default_value" => "",
                "#maxlength" => 100,
            );
            $form['speakers_fieldset'][$i]["institute"] = array(
                "#type" => "textfield",
                "#title" => "Institute: ",
                "#default_value" => "",
                "#maxlength" => 100,
            );
            $form['speakers_fieldset'][$i]["place"] = array(
                "#type" => "textfield",
                "#title" => "Place: ",
                "#default_value" => "",
                "#maxlength" => 100,
            );
        }
        $form["speakers_count"] = array(
            "#type" => "hidden",
            "#value" => $temp1
        );
        $form['speakers_fieldset']['add_speakers'] = array(
            '#type' => 'submit',
            '#value' => t('Add Speaker'),
            '#limit_validation_errors' => array(),
            '#submit' => array(
                [$this, 'speakers_add_more_add_one']
            ),
            '#ajax' => array(
                'callback' => [$this, 'speakers_add_more_callback'],
                'wrapper' => 'speakers-fieldset-wrapper'
            ),
        );
        if ($form_state->get('num_speakers') > 1) {
            $form['speakers_fieldset']['remove_speakers'] = array(
                '#type' => 'submit',
                '#value' => t('Remove Speaker'),
                '#limit_validation_errors' => array(),
                '#submit' => array(
                    '::speakers_add_more_remove_one'
                ),
                '#ajax' => array(
                    'callback' => [$this, 'speakers_add_more_callback'],
                    'wrapper' => 'speakers-fieldset-wrapper'
                )
            );
        }
      /*  if ($no_js_use) {
            if (!empty($form['speakers_fieldset']['remove_speakers']['#ajax'])) {
                unset($form['speakers_fieldset']['remove_speakers']['#ajax']);
            }
            unset($form['speakers_fieldset']['add_speakers']['#ajax']);
        }*/


  // adding testimonial fieldset

        $form['testimonial_fieldset'] = array(
            '#type' => 'fieldset',
            '#tree' => TRUE,
            '#prefix' => '<div id="testimonial-fieldset-wrapper"',
            '#suffix' => '</div>',
        );
        if (empty($form_state->get('num_testimonial'))) {
            $form_state->set('num_testimonial', 1);
        }
        $temp = 0;
        for ($i = 0; $i < $form_state->get('num_testimonial'); $i++) {
            $temp = $i;
            $form['testimonial_fieldset'][$i]["t_text"] = array(
                "#type" => "item",
                "#markup" => "<h4><label>Testimonial : " . ($temp + 1) . "</label></h4>",
            );
            $form['testimonial_fieldset'][$i]["t_name"] = array(
                "#type" => "textfield",
                "#title" => "Name: ",
                "#default_value" => "",
                '#maxlength' => 100,
            );
            $form['testimonial_fieldset'][$i]["t_department"] = array(
                "#type" => "textfield",
                "#title" => "Department: ",
                "#default_value" => "",
                "#maxlength" => 100,
            );
            $form['testimonial_fieldset'][$i]["t_university"] = array(
                "#type" => "textfield",
                "#title" => "University: ",
                "#default_value" => "",
                "#maxlength" => 200,
            );
            $form['testimonial_fieldset'][$i]["t_body"] = array(
                "#type" => "textarea",
                "#title" => "Testimonial: ",
                "#maxlength" => 2000,
            );
        }
        $form["testimonial_count"] = array(
            "#type" => "hidden",
            "#value" => $temp
        );
        $form['testimonial_fieldset']['add_testimonial'] = array(
            '#type' => 'submit',
            '#value' => t('Add Testimonial'),
            '#limit_validation_errors' => array(),
            '#submit' => array(
                '::testimonial_add_more_add_one'
            ),
            '#ajax' => array(
                'callback' => [$this, 'testimonial_add_more_callback'],
                'wrapper' => 'testimonial-fieldset-wrapper'
            )
        );
        if ($form_state->get('num_testimonial') > 1) {
            $form['testimonial_fieldset']['remove_testimonial'] = array(
                '#type' => 'submit',
                '#value' => t('Remove Testimonial'),
                '#limit_validation_errors' => array(),
                '#submit' => array(
                    '::testimonial_add_more_remove_one'
                ),
                '#ajax' => array(
                    'callback' => [$this, 'testimonial_add_more_callback'],
                    'wrapper' => 'testimonial-fieldset-wrapper'
                )
            );
        }
      /*  if ($no_js_use) {
            if (!empty($form['testimonial_fieldset']['remove_testimonial']['#ajax'])) {
                unset($form['testimonial_fieldset']['remove_testimonial']['#ajax']);
            }
            unset($form['testimonial_fieldset']['add_testimonial']['#ajax']);
        }*/


  //Adding video link with title

  $form['videolink_fieldset'] = array(
            '#type' => 'fieldset',
            '#tree' => TRUE,
            '#prefix' => '<div id="videolink-fieldset-wrapper"',
            '#suffix' => '</div>',
        );
        if (empty($form_state->get('num_videolink'))) {
            $form_state->set('num_videolink', 1);
        }

        $temp = 0;
        for ($i = 0; $i < $form_state->get('num_videolink'); $i++) {
            $temp1 = $i;
            $form['videolink_fieldset'][$i]["v_text"] = array(
                "#type" => "item",
                "#markup" => "<h4><label>Video : " . ($temp1 + 1) . "</label></h4>"
            );
            $form['videolink_fieldset'][$i]["videolink_title"] = array(
                "#type" => "textfield",
                "#title" => "Title: ",
                "#default_value" => "",
                "#maxlength" => 100,
            );
            $form['videolink_fieldset'][$i]["videolink_link"] = array(
                "#type" => "textfield",
                "#title" => "Link: ",
                "#default_value" => "",
                '#suffix' => t('<p id="validate_url_'.$temp1.'" style="visibility:hidden; color:red;">Invalid url! Url must start with http:// or https://</p>'),
                '#attributes' => array(
                	'id' => 'videolink_url_'.$temp1
                ),
            );

        }

        $form['videolink_fieldset']['#attached']['drupalSettings']['numvidlink'] = $form_state->get('num_videolink');

        $form["videolink_count"] = array(
            "#type" => "hidden",
            "#value" => $temp1
        );
        $form['videolink_fieldset']['add_videolink'] = array(
            '#type' => 'submit',
            '#value' => t('Add Video Link'),
            '#limit_validation_errors' => array(),
            '#submit' => array(
                [$this, 'videolink_add_more_add_one']
            ),
            '#ajax' => array(
                'callback' => [$this, 'videolink_add_more_callback'],
                'wrapper' => 'videolink-fieldset-wrapper'
            )
        );
        if ($form_state->get('num_videolink') > 1) {
            $form['videolink_fieldset']['remove_videolink'] = array(
                '#type' => 'submit',
                '#value' => t('Remove Video Link'),
                '#limit_validation_errors' => array(),
                '#submit' => array(
                    [$this, 'videolink_add_more_remove_one']
                ),
                '#ajax' => array(
                    'callback' => [$this, 'videolink_add_more_callback'],
                    'wrapper' => 'videolink-fieldset-wrapper'
                )
            );
        }
      /*  if ($no_js_use) {
            if (!empty($form['videolink_fieldset']['remove_videolink']['#ajax'])) {
                unset($form['videolink_fieldset']['remove_videolink']['#ajax']);
            }
            unset($form['videolink_fieldset']['add_videolink']['#ajax']);
        }*/


        $form['names_fieldset'] = array(
            '#type' => 'fieldset',
            '#tree' => TRUE,
            '#prefix' => '<div id="names-fieldset-wrapper"',
            '#suffix' => '</div>',
        );
        if (empty($form_state->get('num_names'))) {
            $form_state->set('num_names', 1);
        }

        if($form_state->get('num_names') == 1)
        {
            $form['names_fieldset'][0]['name'] = array(
                '#title' => t('Add Event Image'),
                '#type' => 'file',
                //'#value' => $_FILES['files']['name']['names_fieldset_'.$i.'_name'],
                '#weight' => '5',
                '#description' => t('Upload an image'),
                // We need this to know which file element this is.
                // By default drupal would name all as files[names_fieldset]
                '#name' => 'files[names_fieldset_0_name]',
                '#prefix' => '<div id="names-wrapper"> <div id="image_0">',
                '#suffix' => '</div> </div>',
            );
        }
        else
        {
            $form['names_fieldset'][$form_state->get('num_names')-1]['name'] = array(
                '#title' => t('Add Event Image'),
                '#type' => 'file',
                //'#value' => $_FILES['files']['name']['names_fieldset_'.$i.'_name'],
                '#weight' => '5',
                '#description' => t('Upload an image'),
                // We need this to know which file element this is.
                // By default drupal would name all as files[names_fieldset]
                '#name' => 'files[names_fieldset_'.($form_state->get('num_names')-1).'_name]',
                '#prefix' => '<div id="image_'.($form_state->get('num_names')-1).'">',
                '#suffix' => '</div>'
            );
        }

        $form['names_fieldset']['add_name'] = array(
            '#type' => 'submit',
            '#value' => t('Add Image'),
            '#limit_validation_errors' => array(),
            '#submit' => array(
                [$this, 'workshop_add_more_add_one']
            ),
            '#ajax' => array(
                'callback' => [$this, 'workshop_add_more_add_one_callback'],
                'wrapper' => 'names-wrapper',
                'method' => 'append'
            ),
        );

        $form['names_fieldset']['remove_name'] = array(
            '#type' => 'submit',
            '#value' => t('Remove Image'),
            '#limit_validation_errors' => array(),
            '#submit' => array(
                [$this, 'workshop_add_more_remove_one']
            ),
            '#ajax' => array(
                'callback' => [$this, 'workshop_remove_callback'],
            )
        );
    /*    if ($no_js_use) {
            if (!empty($form['names_fieldset']['remove_name']['#ajax'])) {
                unset($form['names_fieldset']['remove_name']['#ajax']);
            }
            unset($form['names_fieldset']['add_name']['#ajax']);
        }*/
	    $form["submit"] = array(
	        "#type" => "submit",
	        "#value" => "Submit"
	    );
	    return $form;
	}

	public function get_first_dropdown_options_foss_name() {
		$connection = \Drupal::database();
    	$query = $connection->select('foss_type');
	    $query->fields('foss_type', array(
	        'id'
	    ));
	    $query->fields('foss_type', array(
	        'foss_name'
	    ));
	    $result = $query->execute();
	    $options = array();
	    while ($foss_detail = $result->fetchObject()) {
	        $options[$foss_detail->foss_name] = $foss_detail->foss_name;
	    }
	    $options["Others"] = "Others";
	    return $options;
	}

	public function _list_of_states() {
	    $states = array(
	        0 => '-Select-'
	    );
	    $connection = \Drupal::database();
    	$query = $connection->select('ai_pincode');
	    $query->fields('ai_pincode', array('statename'));
	    $states_list = $query->execute();
	    while ($states_list_data = $states_list->fetchObject()) {
	        $states[$states_list_data->statename] = $states_list_data->statename;
	    }
	    return $states;
	}

	public function _list_of_cities($state) {
	    $city = array(
	        0 => '-Select-'
	    );
	    if($state and $state != '')
	    {
	        $connection = \Drupal::database();
    		$query = $connection->select('ai_pincode');
	        $query->fields('ai_pincode', array('districtname'));
	        $query->orderBy('districtname', 'ASC');
	        $query->condition('statename', $state,'=');
	        $district_list = $query->execute();
	        while ($district_list_data = $district_list->fetchObject()) {
	            $city[$district_list_data->districtname] = $district_list_data->districtname;
	        }
	    }
	    return $city;
	}

	public function _list_of_pincodes($city) {
	    $pincode = array(
	        0 => '-Select-'
	    );
	    if($city and $city != '')
	    {
	        $connection = \Drupal::database();
    		$query = $connection->select('ai_pincode');
	        $query->fields('ai_pincode', array('pincode', 'officename'));
	        $query->orderBy('pincode', 'ASC');
	        $query->condition('districtname', $city, '=');
	        $pincode_list = $query->execute();
	        while($pincode_list_data = $pincode_list->fetchObject()){
	            $pincode[$pincode_list_data->pincode] = $pincode_list_data->pincode.' - '.$pincode_list_data->officename;
	        }
	    }
	    return $pincode;
	}

	public function list_city_for_state(array &$form, FormStateInterface $form_state) {
    	return $form['city'];
	}

	public function list_pincode_for_city(array &$form, FormStateInterface $form_state) {
    	return $form['pincode'];
	}

	public function speakers_add_more_callback(array &$form, FormStateInterface $form_state) {
    	return $form['speakers_fieldset'];
	}

	public function testimonial_add_more_callback(array &$form, FormStateInterface $form_state) {
    	return $form['testimonial_fieldset'];
	}

	public function videolink_add_more_callback(array &$form, FormStateInterface $form_state){
	    return $form['videolink_fieldset'];
	}

	public function workshop_add_more_add_one_callback(array &$form, FormStateInterface $form_state) {
	    return $form['names_fieldset'][$form_state->get('num_names')-1];
	}

	public function workshop_remove_callback(array &$form, FormStateInterface $form_state){
		$response = new AjaxResponse();
		$response->addCommand(new RemoveCommand('#image_'.$form_state->get('num_names')));
		return $response;
	}

	public function speakers_add_more_add_one(array &$form, FormStateInterface $form_state) {
	    $form_state->set('num_speakers', $form_state->get('num_speakers')+1);
	    $form_state->setRebuild();
	    //$form_state['no_redirect'] = TRUE;
	}

	public function speakers_add_more_remove_one(array &$form, FormStateInterface $form_state) {
	    if ($form_state->get('num_speakers') > 1) {
	        $form_state->set('num_speakers', $form_state->get('num_speakers')-1);
	    }
	    $form_state->setRebuild();
	}

	public function testimonial_add_more_add_one(array &$form, FormStateInterface $form_state) {
	    $form_state->set('num_testimonial', $form_state->get('num_testimonial')+1);
	    $form_state->setRebuild();
    //$form_state['no_redirect'] = TRUE;
	}

	public function testimonial_add_more_remove_one(array &$form, FormStateInterface $form_state) {
	    if ($form_state->get('num_testimonial') > 1) {
	        $form_state->set('num_testimonial', $form_state->get('num_testimonial'));
	    }
	    $form_state->setRebuild();
	}

	public function videolink_add_more_add_one(array &$form, FormStateInterface $form_state) {
	    $form_state->set('num_videolink', $form_state->get('num_videolink')+1);
	    $form_state->setRebuild();
	    //$form_state['no_redirect'] = TRUE;
	}

	public function videolink_add_more_remove_one(array &$form, FormStateInterface $form_state) {
	    if ($form_state->get('num_videolink') > 1) {
	        $form_state->set('num_videolink', $form_state->get('num_videolink')-1);
	    }
	    $form_state->setRebuild();
	}

	public function workshop_add_more_add_one(array &$form, FormStateInterface $form_state) {
	    $form_state->set('num_names', $form_state->get('num_names')+1);
	    $form_state->setRebuild();
	}

	public function workshop_add_more_remove_one(array &$form, FormStateInterface $form_state) {
	    if ($form_state->get('num_names') > 1) {
	        $form_state->set('num_names', $form_state->get('num_names')-1);
	    }
	    $form_state->setRebuild();
	}

	public function validateForm(array &$form, FormStateInterface $form_state) {
	    if ($form_state->getValue('country') == 'Others') {
	        if ($form_state->getValue('other_country') == '') {
	            form_set_error('other_country', t('Enter country name'));
	            // $form_state['values']['country'] = $form_state['values']['other_country'];
	        }
	        else {
	            $form_state->setValue('country', $form_state->getValue('other_country'));
	        }
	        if ($form_state->getValue('other_state')=='') {
	            form_set_error('other_state', t('Enter state name'));
	            // $form_state['values']['country'] = $form_state['values']['other_country'];
	        }
	        else {
	            $form_state->setValue('all_state', $form_state->getValue('other_state'));
	        }
	        if ($form_state->getValue('other_city')=='') {
	            form_set_error('other_city', t('Enter city name'));
	            // $form_state['values']['country'] = $form_state['values']['other_country'];
	        }
	        else {
	            $form_state->setValue('city', $form_state->setValue('other_city'));
	        }
	        if ($form_state->getValue('other_pincode')=='') {
	            form_set_error('other_city', t('Enter pincode'));
	            // $form_state['values']['country'] = $form_state['values']['other_country'];
	        }
	        else {
	            $form_state->setValue('pincode', $form_state->getValue('other_pincode'));
	        }
	    }
	    else {
	        if ($form_state->getValue('country') == '') {
	            form_set_error('country', t('Select country name'));
	            // $form_state['values']['country'] = $form_state['values']['other_country'];
	        }
	        if ($form_state->getValue('all_state') == '') {
	            form_set_error('all_state', t('Select state name'));
	            // $form_state['values']['country'] = $form_state['values']['other_country'];
	        }
	        if ($form_state->getValue('city') == '') {
	            form_set_error('city', t('Select city name'));
	            // $form_state['values']['country'] = $form_state['values']['other_country'];
	        }
	    }
	    if (isset($_FILES['files'])) {
	        /* check for valid filename extensions */
	        foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
	            if ($file_name) {
	                if (strstr($file_form_name, 'names_fieldset'))
	                    $file_type = 'I';
	                switch ($file_type) {
	                    case 'I':
	                        $allowed_extensions_str = \Drupal::state()->get('events_image_extensions', '');
	                        break;
	                }
	                $allowed_extensions = explode(',', $allowed_extensions_str);
	                $temp_extension = end(explode('.', strtolower($_FILES['files']['name'][$file_form_name])));
	                if (!in_array($temp_extension, $allowed_extensions))
	                    form_set_error($file_form_name, t('Only file with') . $allowed_extensions_str . t(',extensions can be uploaded.'));
	                //if ($_FILES['files']['size'][$file_form_name] <= 0)
	                //  form_set_error($file_form_name, t('File size cannot be zero.'));
	            }
	        }
	    }
	}

	public function submitForm(array &$form, FormStateInterface $form_state){

        $connection = \Drupal::database();
        $v = $form_state->getValues("values");
        $start_datetime= explode(" ",$v["start_date"]);
        $end_datetime= explode(" ",$v["end_date"]);
        $eventfolder = str_replace(' ', '_', $v["name"]) . time();

        $query = $connection->insert('workshop');

        $account = \Drupal::currentUser();
        $args = array(
            "foss_name" => $v["foss_select"],
            "type" => $v["type"],
            "w_name" => trim($v["name"]),
            "images_folder" => trim($eventfolder),
            'startdate' => $start_datetime[0],
            'starttime' => $start_datetime[1],
            'enddate' => $end_datetime[0],
            'endtime' => $end_datetime[1],
            "venue" => trim($v["venue"]),
            "street" => trim($v["street"]),
            "country" => trim($v["country"]),
            "state" => trim($v["all_state"]),
            "city" => trim($v["city"]),
            "pincode" => $v["pincode"],
            "event_link" => trim($v["event_link"]),
            "no_of_participant" => $v["no_of_participant"],
            "body" => trim($v["body"]),
            "last_edited_by" => $account->id(),
        );       
        $result = $query->fields($args)->execute();

        /* For adding speakers */
        $speakerupload = 0;
        for ($i = 0; $i <= $v["speakers_count"]; $i++) {
            if ($v['speakers_fieldset'][$i]["speakername"] != "") {
                $speakerquery = $connection->insert('speakers');
                $speakerargs = array(
                    "w_id" => $result,
                    "name" => trim($v['speakers_fieldset'][$i]["speakername"]),
                    "institute" => trim($v['speakers_fieldset'][$i]["institute"]),
                    "place" => trim($v['speakers_fieldset'][$i]["place"])
                );               
                $speakerresult = $speakerquery->fields($speakerargs)->execute();
                if ($speakerresult != 0) {
                    $speakerupload++;
                }
            }
        }
        /* For adding testimonial */
        $testimonialupload = 0;
        for ($i = 0; $i <= $v["testimonial_count"]; $i++) {
            if ($v['testimonial_fieldset'][$i]["t_name"] != "") {
                $testimonialquery = $connection->insert('testimonials');
                    $testimonialargs = array(
                    "w_id" => $result,
                    "body" => trim($v['testimonial_fieldset'][$i]["t_body"]),
                    "name" => trim($v['testimonial_fieldset'][$i]["t_name"]),
                    "department" => trim($v['testimonial_fieldset'][$i]["t_department"]),
                    "university" => trim($v['testimonial_fieldset'][$i]["t_university"])
                );
                /* storing the row id in $result */
                $testimonialresult = $testimonialquery->fields($testimonialargs)->execute();
                if ($testimonialresult != 0) {
                    $testimonialupload++;
                }
            }
        }

        /* Adding video links  */
        $videolinkupload = 0;
        for ($i = 0; $i <= $v["videolink_count"]; $i++) {
            if ($v['videolink_fieldset'][$i]["videolink_title"] != "") {
                $videolinkquery = $connection->insert("workshop_videolinks");
                $videolinkargs = array(
                    "w_id" => $result,
                    "title" => trim($v['videolink_fieldset'][$i]["videolink_title"]),
                    "link" => trim($v['videolink_fieldset'][$i]["videolink_link"])
                );
                /* storing the row id in $result */
                $videolinkresult = $videolinkquery->fields($videolinkargs)->execute();
                if ($videolinkresult != 0) {
                    $videolinkupload++;
                }
            }
        }


        /* For adding images of events*/
        $items = array();
        $root_path = Utility::events_images_path();

        $dest_path = $eventfolder . '/';
        if (!is_dir($root_path . $dest_path)) {
            mkdir($root_path . $dest_path);
        }
        /* uploading files */
        foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
            if ($file_name) {
                if (file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])) {
                    drupal_set_message(t("Error uploading file. File !filename already exists."), array(
                        '!filename' => $_FILES['files']['name'][$file_form_name]
                    ), 'error');
                    return;
                }
                $imageupload = 0;
                /* uploading file */
                $sanitized_file_name = preg_replace('/\s+/', '_', $file_name);
                if(Utility::get_file_size_MB($_FILES['files']['tmp_name'][$file_form_name]) >   \Drupal::state()->get('max_upload_size', 1))
                { drupal_set_message(t('Max file upload size('.\Drupal::state()->get('max_upload_size', 1), 'error')); return; }

                if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path .$sanitized_file_name)) {
                    $imagequery = $connection->insert("workshop_images");
                    $imageargs = array(
                        "w_id" => $result,
                        "name" => $_FILES['files']['name'][$file_form_name],
                        "path" => $dest_path . $sanitized_file_name
                    );
                    /* storing the row id in $result */
                    $imageresult = $imagequery->fields($imageargs)->execute();
                    if ($imageresult != 0) {
                        $imageupload++;
                    }
                    drupal_set_message($file_name . ' uploaded successfully.', 'status');
                }
                else {
                    drupal_set_message($file_name . $dest_path . ' was having an error while uploading :' , 'error');
                }
            }
        }
        if (!$result) {
            drupal_set_message(t("Something went wrong, please try again."), "error");
        }
        else {
            drupal_set_message(t("Workshop added successfully"), "status");
            $mailManager = \Drupal::service('plugin.manager.mail');
            $mailManager->mail('fossee_stats', 'edit_mail', $account->getEmail(), $account->getPreferredLangcode(), array('workshop_name'=>$v['name'],NULL, TRUE));

            if ($imageupload != 0) {
                drupal_set_message(t("Event's Pictures are added successfully"), "status");
            }
            if ($testimonialupload != 0) {
                drupal_set_message(t("Testimonials added successfully"), "status");
            }
            if ($speakerupload != 0) {
                drupal_set_message(t("Speaker's Detail are added successfully"), "status");
            }
      if ($videolinkupload != 0) {
                drupal_set_message(t("Video links are added successfully"), "status");
            }
        }
	}


}