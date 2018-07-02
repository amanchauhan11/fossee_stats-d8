<?php

namespace Drupal\fossee_stats\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Datetime\DrupalDateTime;
use \Drupal\Core\Ajax\AjaxResponse;
use \Drupal\Core\Ajax\RemoveCommand;
use Drupal\fossee_stats\Utility;

class WorkshopEditForm extends FormBase {

	public function getFormId(){
    	return 'WorkshopEditForm';
  	}

    public function buildForm(array $form, FormStateInterface $form_state, $workshop_id = null) {

    	$connection = \Drupal::database();
    	$query = $connection->select('workshop');
    	$query->fields('workshop');
    	$query->condition('w_id', $workshop_id, '=');
		$result = $query->execute();
		$rowcount = $query->countQuery()->execute()->fetchField();
	    $row = $result->fetchObject();
	    $form = array();

	    if($workshop_id and $rowcount) {

	    	$form['#attached']['library'][] = 'fossee_stats/validators';
	        $form['foss_select'] = array(
	            '#type' => 'select',
	            '#title' => t('Select FOSS under which event took place'),
	            '#options' => $this->get_first_dropdown_options_foss_name(),
	            '#default_value' => $row->foss_name,
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
	            '#default_value' => $row->type,
	            "#required" => TRUE
	        );
	        $form["name"] = array(
	            "#type" => "textfield",
	            "#title" => "Name",
	            "#default_value" => $row->w_name,
	            "#required" => TRUE,
	            "#maxlength" => 100,
	        );
	        $form['start_date'] = array(
	            '#type' => 'datetime',
	            '#title' => 'Start Date and Time',
	            '#date_year_range' => '2008:+3',
	            '#default_value' => new DrupalDateTime($row->startdate.' '.$row->starttime),
	            //'#date_format' => array('month' => 9, 'day' => 6, 'year' => 1962),// h:i A',
	            "#required" => TRUE,
	        );
	        $form['end_date'] = array(
	        	'#type' => 'datetime',
	            '#title' => 'End Date and Time',
	            '#date_year_range' => '2008:+3',
	            '#default_value' => new DrupalDateTime($row->enddate.' '.$row->endtime),
	            //'#date_format' => 'Y-m-d h:i A',
	            "#required" => TRUE,
	            '#suffix' => t('<p id="validate_date" style="visibility:hidden; color:red;">Invalid dates! End date can\'t be earlier than start date.</p> <p id="validate_time" style="visibility:hidden; color:red">Invalid time! End\'t time cannot be earlier than start time</p>')
	        );

	        $form["venue"] = array(
	            "#type" => "textfield",
	            "#title" => "Venue",
	            "#default_value" => $row->venue,
	            "#required" => TRUE,
	            "#maxlength" => 100,
	        );
	        $form["street"] = array(
	            "#type" => "textfield",
	            "#title" => "Street",
	            "#default_value" => $row->street,
	            "#required" => TRUE,
	            "#maxlength" => 100
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
	            '#validated' => TRUE,
	            "#default_value" => $row->country
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
	            ),
	            "#default_value" => $row->country
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
	            ),
	            "#default_value" => $row->state
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
	            ),
	            "#default_value" => $row->city
	        );
	        $form['other_pincode'] = array(
	            '#type' => 'textfield',
	            '#title' => t('Pincode'),
	            '#size' => 30,
	            '#maxlength' => 6,
	            '#attributes' => array(
	                'placeholder' => 'Enter pincode....'
	            ),
	            "#default_value" => $row->pincode,
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
	            ),
	            "#default_value" => $row->state
	        );

	        $form['city'] = array(
	            '#type' => 'select',
	            '#title' => t('City'),
	            '#validated' => TRUE,
	            '#options' => $this->_list_of_cities(null !== $form_state->getValue('all_state')? $form_state->getValue('all_state'): $row->state),
	            '#states' => array(
	                'visible' => array(
	                    ':input[name="country"]' => array(
	                        'value' => 'India'
	                    )
	                )
	            ),
	            '#prefix'=> '<div id="city-wrapper">',
	            '#suffix'=>'</div>',
	            "#default_value" => $row->city,
	            '#ajax'=> array(
	                'callback'=>[$this,'list_pincode_for_city'],
	                'wrapper'=>'pincode-wrapper',
	            ),
	        );
	        $form['pincode'] = array(
	            '#type' => 'select',
	            '#title' => t('Pincode'),
	            '#validated' => TRUE,
	            '#options' => $this->_list_of_pincodes((null!==$form_state->getValue('all_state'))? $form_state->getValue('city'): $row->city),
	            '#states' => array(
	                'visible' => array(
	                    ':input[name="country"]' => array(
	                        'value' => 'India'
	                    )
	                )
	            ),
	            '#prefix'=> '<div id="pincode-wrapper">',
	            '#suffix'=> '</div>',
	            "#default_value" => $row->pincode,
	        );

	        $form["no_of_participant"] = array(
	            "#type" => "textfield",
	            "#title" => "Number Of Participants",
	            "#default_value" => $row->no_of_participant,
	            "#required" => TRUE
	        );

	  		$form["event_link"] = array(
	            "#type" => "textfield",
	            "#title" => "Link to event's website",
	            "#default_value" => $row->event_link,
	            '#suffix' => t('<p id="validate_url" style="visibility:hidden; color:red;">Invalid url! Url must start with http:// or https://</p>')

	        );

	        $form["body"] = array(
	            "#type" => "textarea",
	            "#title" => "Details (Max characters: 2000)",
	            "#default_value" => $row->body,
	            '#maxlength' => 2000,
	            "#required" => TRUE,
	        );
	        /*Edit Speakers*/

	  		$connection = \Drupal::database();
    		$query_s = $connection->select('speakers');
	        $query_s->fields('speakers');
	        $query_s->condition('w_id', $workshop_id);
	        $result_s = $query_s->execute();
	        $num_of_speakerresults = $query_s->countQuery()->execute()->fetchField();

	  $form['speakers_fieldset'] = array(
	            '#type' => 'fieldset',
	            '#tree' => TRUE,
	            '#prefix' => '<div id="speakers-fieldset-wrapper">',
	            '#suffix' => '</div>'
	        );

	    if ($num_of_speakerresults != 0) {

	            $form_state->set('num_speakers', $num_of_speakerresults);
	            $temp = 0;
	            $i = 0;
	            while ($row_s = $result_s->fetchObject()) {
	                $temp = $i;
	                $form['speakers_fieldset'][$i]["s_text"] = array(
	                    "#type" => "item",
	                    "#markup" => "<h4><label>Speakers : " . ($temp + 1) . "</label></h4>"
	                );
	                $form['speakers_fieldset'][$i]["s_id"] = array(
	                    "#type" => "hidden",
	                    "#default_value" => $row_s->s_id
	                );
	                $form['speakers_fieldset'][$i]["speakername"] = array(
	                    "#type" => "textfield",
	                    "#title" => "Name",
	                    "#default_value" => $row_s->name,
	                    "#maxlength" => 100
	                );
	                $form['speakers_fieldset'][$i]["institute"] = array(
	                    "#type" => "textfield",
	                    "#title" => "Institute",
	                    "#default_value" => $row_s->institute,
	                    "#maxlength" => 200,
	                );
	                $form['speakers_fieldset'][$i]["place"] = array(
	                    "#type" => "textfield",
	                    "#title" => "Place",
	                    "#default_value" => $row_s->place,
	                    "#maxlength" => 100,
	                );

	                $i++;
	            }


	            $form["speakers_count"] = array(
	                "#type" => "hidden",
	                "#value" => $temp
	            );

	        }
	        else {
	            if (empty($form_state->get('num_speakers'))) {
	                $form_state->set('num_speakers', 1);
	            }
	            $temp = 0;
	            for ($i = 0; $i < $form_state->get('num_speakers'); $i++) {
	                $temp = $i;
	                $form['speakers_fieldset'][$i]["s_text"] = array(
	                    "#type" => "item",
	                    "#markup" => "<h4><label>Speakers : " . ($temp + 1) . "</label></h4>"
	                );
	                $form['speakers_fieldset'][$i]["speakername"] = array(
	                    "#type" => "textfield",
	                    "#title" => "Name",
	                    "#default_value" => "",
	                    "#maxlength" => 100
	                );
	                $form['speakers_fieldset'][$i]["institute"] = array(
	                    "#type" => "textfield",
	                    "#title" => "Department",
	                    "#default_value" => "",
	                    "#maxlength" => 200,
	                );
	                $form['speakers_fieldset'][$i]["place"] = array(
	                    "#type" => "textfield",
	                    "#title" => "University",
	                    "#default_value" => "",
	                    "#maxlength" => 100,
	                );
	            }
	            $form["speakers_count"] = array(
	                "#type" => "hidden",
	                "#value" => $temp
	            );
	            $form['speakers_fieldset']['add_speakers'] = array(
	                '#type' => 'submit',
	                '#value' => t('Add Speaker'),
	                '#limit_validation_errors' => array(),
	                '#submit' => array(
	                    'speakers_add_more_add_one'
	                ),
	                '#ajax' => array(
	                    'callback' => [$this, 'speakers_add_more_callback'],
	                    'wrapper' => 'speakers-fieldset-wrapper'
	                )
	            );
	            if ($form_state->get('num_speakers') > 1) {
	                $form['speakers_fieldset']['remove_speakers'] = array(
	                    '#type' => 'submit',
	                    '#value' => t('Remove'),
	                    '#limit_validation_errors' => array(),
	                    '#submit' => array(
	                        [$this, 'speakers_add_more_remove_one']
	                    ),
	                    '#ajax' => array(
	                        'callback' => [$this, 'speakers_add_more_callback'],
	                        'wrapper' => 'speakers-fieldset-wrapper'
	                    )
	                );
	            }
	           /* if ($no_js_use) {
	                if (!empty($form['speakers_fieldset']['remove_speakers']['#ajax'])) {
	                    unset($form['speakers_fieldset']['remove_speakers']['#ajax']);
	                }
	                unset($form['speakers_fieldset']['add_speakers']['#ajax']);
	            }*/
	        }


	  /* Edit Video Links */

			$connection = \Drupal::database();
    		$query_v = $connection->select('workshop_videolinks');
	        $query_v->fields('workshop_videolinks');
	        $query_v->condition('w_id', $workshop_id);
	        $result_v = $query_v->execute();
	        $num_of_videoresults = $query_v->countQuery()->execute()->fetchField();

	  		$form['videolink_fieldset'] = array(
	            '#type' => 'fieldset',
	            '#tree' => TRUE,
	            '#prefix' => '<div id="videolink-fieldset-wrapper">',
	            '#suffix' => '</div>'
	        );

	    	if ($num_of_videoresults != 0) {

	            $form_state->set('num_videolink', $num_of_videoresults);
	            $temp = 0;
	            $i = 0;
	            while ($row_v = $result_v->fetchObject()) {
	                $temp = $i;
	                $form['videolink_fieldset'][$i]["v_text"] = array(
	                    "#type" => "item",
	                    "#markup" => "<h4><label>Video : " . ($temp + 1) . "</label></h4>"
	                );
	                $form['videolink_fieldset'][$i]["v_id"] = array(
	                    "#type" => "hidden",
	                    "#default_value" => $row_v->v_id
	                );
	                $form['videolink_fieldset'][$i]["videolink_title"] = array(
	                    "#type" => "textfield",
	                    "#title" => "Title: ",
	                    "#default_value" => $row_v->title,
	                    "#maxlength" => 100,
	                );
	                $form['videolink_fieldset'][$i]["videolink_link"] = array(
	                    "#type" => "textfield",
	                    "#title" => "Institute",
	                    "#default_value" => $row_v->link,
	                    '#suffix' => t('<p id="validate_url_'.$temp.'" style="visibility:hidden; color:red;">Invalid url! Url must start with http:// or https://</p>'),
	                    '#attributes' => array(
	                    	'id' => 'videolink_url_'.$temp
	                    ),
	                );

	                $i++;
	            }

	            $form['videolink_fieldset']['#attached']['drupalSettings']['numvidlink'] = $form_state->get('num_videolink');

	            $form["videolink_count"] = array(
	                "#type" => "hidden",
	                "#value" => $temp
	            );

	        }
	        else {
	            if (empty($form_state->get('num_videolink'))) {
	                $form_state->set('num_videolink', 1);
	            }
	            $temp = 0;
	            for ($i = 0; $i < $form_state->get('num_videolink'); $i++) {
	                $temp = $i;
	                $form['videolink_fieldset'][$i]["v_text"] = array(
	                    "#type" => "item",
	                    "#markup" => "<h4><label>Video : " . ($temp + 1) . "</label></h4>"
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
	                    '#suffix' => t('<p id="validate_url_'.$temp.'" style="visibility:hidden; color:red;">Invalid url! Url must start with http:// or https://</p>'),
	                    '#attributes' => array(
                			'id' => 'videolink_url_'.$temp
                		 ),

	                );


	            }
	            $form['videolink_fieldset']['#attached']['drupalSettings']['numvidlink'] = $form_state->get('num_videolink');

	            $form["videolink_count"] = array(
	                "#type" => "hidden",
	                "#value" => $temp
	            );
	            $form['videolink_fieldset']['add_videolink'] = array(
	                '#type' => 'submit',
	                '#value' => t('Add Video'),
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
	                    '#value' => t('Remove'),
	                    '#limit_validation_errors' => array(),
	                    '#submit' => array(
	                        [$this, 'videolink_add_more_remove_one'],
	                    ),
	                    '#ajax' => array(
	                        'callback' => [$this, 'videolink_add_more_callback'],
	                        'wrapper' => 'videolink-fieldset-wrapper'
	                    )
	                );
	            }
	        /*    if ($no_js_use) {
	                if (!empty($form['videolink_fieldset']['remove_videolink']['#ajax'])) {
	                    unset($form['videolink_fieldset']['remove_videolink']['#ajax']);
	                }
	                unset($form['videolink_fieldset']['add_videolink']['#ajax']);
	            }*/
	        }

	        /*Edit Testimonial*/
	        $connection = \Drupal::database();
    		$query_t = $connection->select('testimonials');
	        $query_t->fields('testimonials');
	        $query_t->condition('w_id', $workshop_id);
	        $result_t = $query_t->execute();
	        $num_of_results = $query_t->countQuery()->execute()->fetchField();

	        $form['testimonial_fieldset'] = array(
	            '#type' => 'fieldset',
	            '#tree' => TRUE,
	            //'#title' => t('Add Testimonial'),
	            '#prefix' => '<div id="testimonial-fieldset-wrapper">',
	            '#suffix' => '</div>'
	        );
	        if ($num_of_results != 0) {
	            $form_state->set('num_testimonial', $num_of_results);
	            $temp = 0;
	            $i = 0;
	            while ($row_t = $result_t->fetchObject()) {
	                $temp = $i;
	                $form['testimonial_fieldset'][$i]["t_text"] = array(
	                    "#type" => "item",
	                    "#markup" => "<h4><label>Testimonial : " . ($temp + 1) . "</label></h4>"
	                );
	                $form['testimonial_fieldset'][$i]["t_id"] = array(
	                    "#type" => "hidden",
	                    "#default_value" => $row_t->t_id
	                );
	                $form['testimonial_fieldset'][$i]["t_name"] = array(
	                    "#type" => "textfield",
	                    "#title" => "Name",
	                    "#default_value" => $row_t->name,
	                    "#maxlength" => 100,
	                );
	                $form['testimonial_fieldset'][$i]["t_department"] = array(
	                    "#type" => "textfield",
	                    "#title" => "Department",
	                    "#default_value" => $row_t->department,
	                    "#maxlength" => 100,
	                );
	                $form['testimonial_fieldset'][$i]["t_university"] = array(
	                    "#type" => "textfield",
	                    "#title" => "University",
	                    "#default_value" => $row_t->university,
	                    "#maxlength" => 200,
	                );
	                $form['testimonial_fieldset'][$i]["t_body"] = array(
	                    "#type" => "textarea",
	                    "#title" => "Testimonial",
	                    "#default_value" => $row_t->body,
	                    "#maxlength" => 2000,
	                );
	                $i++;
	            }
	            $form["testimonial_count"] = array(
	                "#type" => "hidden",
	                "#value" => $temp
	            );
	        }
	        else {
	            if (empty($form_state->get('num_testimonial'))) {
	                $form_state->set('num_testimonial', 1);
	            }
	            $temp = 0;
	            for ($i = 0; $i < $form_state->get('num_testimonial'); $i++) {
	                $temp = $i;
	                $form['testimonial_fieldset'][$i]["t_text"] = array(
	                    "#type" => "item",
	                    "#markup" => "<h4><label>Testimonial : " . ($temp + 1) . "</label></h4>"
	                );
	                $form['testimonial_fieldset'][$i]["t_name"] = array(
	                    "#type" => "textfield",
	                    "#title" => "Name",
	                    "#default_value" => "",
	                    "#maxlength" => 100,
	                );
	                $form['testimonial_fieldset'][$i]["t_department"] = array(
	                    "#type" => "textfield",
	                    "#title" => "Department",
	                    "#default_value" => "",
	                    "#maxlength" => 100,
	                );
	                $form['testimonial_fieldset'][$i]["t_university"] = array(
	                    "#type" => "textfield",
	                    "#title" => "University",
	                    "#default_value" => "",
	                    "#maxlength" => 200,
	                );
	                $form['testimonial_fieldset'][$i]["t_body"] = array(
	                    "#type" => "textarea",
	                    "#title" => "Testimonial",
	                    "#maxlength" => 2000,
	                );
	            }
	            $form["testimonial_count"] = array(
	                "#type" => "hidden",
	                "#value" => $temp
	            );
	            $form['testimonial_fieldset']['add_testimonial'] = array(
	                '#type' => 'submit',
	                '#value' => t('Add testimonial'),
	                '#limit_validation_errors' => array(),
	                '#submit' => array(
	                    [$this, 'testimonial_add_more_add_one']
	                ),
	                '#ajax' => array(
	                    'callback' => [$this, 'testimonial_add_more_callback'],
	                    'wrapper' => 'testimonial-fieldset-wrapper'
	                )
	            );
	            if ($form_state->get('num_testimonial') > 1) {
	                $form['testimonial_fieldset']['remove_testimonial'] = array(
	                    '#type' => 'submit',
	                    '#value' => t('Remove'),
	                    '#limit_validation_errors' => array(),
	                    '#submit' => array(
	                        [$this, 'testimonial_add_more_remove_one']
	                    ),
	                    '#ajax' => array(
	                        'callback' => [$this, 'testimonial_add_more_callback'],
	                        'wrapper' => 'testimonial-fieldset-wrapper'
	                    )
	                );
	            }
	        /*   if ($no_js_use) {
	                if (!empty($form['testimonial_fieldset']['remove_testimonial']['#ajax'])) {
	                    unset($form['testimonial_fieldset']['remove_testimonial']['#ajax']);
	                }
	                unset($form['testimonial_fieldset']['add_testimonial']['#ajax']);
	            }*/
	        }
	        $form['uploadphotos'] = array(
	            '#markup' => '<h5><p> Edit Photos</p></h5>(Select Check box to delete existing photos)',
	            '#prefix' => '<div id="uploaded_images"><table><tr>',
	            '#suffix' => ''
	        );
	        $connection = \Drupal::database();
    		$query = $connection->select('workshop');
	        $query->fields('workshop');
	        $query->condition('w_id', $workshop_id);
	        $result = $query->execute();
	        $row = $result->fetchObject();

	        if($row->images_folder == '')
	        {
	            $form['noimagestillnow'] = array(
	                '#markup' => '<p>No Images uploaded till now</p>'
	            );
	        }
	        else
	        {
	            $connection = \Drupal::database();
    			$query1 = $connection->select('workshop_images');
	            $query1->fields('workshop_images');
	            $query1->condition('w_id', $workshop_id);
	            $result1 = $query1->execute();
	            if($query1->countQuery()->execute()->fetchField() == 0){
	                 $form['noimagestillnow'] = array(
	                '#markup' => '<p>No Images uploaded till now</p>'
	                );
	            }
	            else {
	                while ($row1 = $result1->fetchObject()) {
	                    $form['imagecheck@' . $row1->id] = array(
	                        '#type' => 'checkbox',
	                        '#field_suffix' => '<img style="width:100px; padding-left :20px;height:100px" src=' . $GLOBALS['base_url'] . "/events_images/" .$row1->path . ' />',
	                        '#attributes' => array(
	                        	'style' => array('margin-bottom:20px; margin-right:10px; vertical-align:top;')
	                        )
	                    );
						$form['imagecheck@' . $row1->id]['#attached']['library'][] = 'fossee_stats/image_styles';
	                }
	            }
	        }

	        $form['enduploadphotos'] = array(
	            '#markup' => '',
	            '#prefix' => '',
	            '#suffix' => '</tr></table></div>'
	        );

	        $form['names_fieldset'] = array(
	            '#type' => 'fieldset',
	            '#tree' => TRUE,
	            // '#title' => t('Add images'),
	            '#prefix' => '<div id="names-fieldset-wrapper">',
	            '#suffix' => '</div>'
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

	        /*if ($no_js_use) {
	            if (!empty($form['names_fieldset']['remove_name']['#ajax'])) {
	                unset($form['names_fieldset']['remove_name']['#ajax']);
	            }
	            unset($form['names_fieldset']['add_name']['#ajax']);
	        }*/
		    $form["workshop_id"] = array(
		        "#type" => "hidden",
		        "#value" => $workshop_id
		    );
		    $form["submit"] = array(
		        "#type" => "submit",
		        "#value" => "Submit"
		    );
		}
		else
		{
			drupal_set_message('!Invalid workshop id', 'error');
		}

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
	    if($state != null and $state != '')
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
	    if($city != null and $city != '')
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

		$v = $form_state->getValues("values");
	    $start_datetime= explode(" ",$v["start_date"]);
	    $end_datetime= explode(" ",$v["end_date"]);

	    $account = \Drupal::currentUser();;
	    //drupal_mail('fossee_stats', 'edit_mail', $user->mail, language_default(), array('workshop_name'=>trim($v['name'])));
	    $connection = \Drupal::database();
    	$query = $connection->update('workshop');
        $query->fields(array(
            'foss_name' => trim($v["foss_select"]),
            'w_name' => trim($v["name"]),
            'type' => $v["type"],
            'startdate' => $start_datetime[0],
            'starttime' => $start_datetime[1],
            'enddate' => $end_datetime[0],
            'endtime' => $end_datetime[1],
            'venue' => trim($v["venue"]),
            'street' => trim($v["street"]),
            'country' => trim($v["country"]),
            'state' => trim($v["all_state"]),
            'city' => trim($v["city"]),
            'pincode' => trim($v["pincode"]),
            'event_link' => trim($v["event_link"]),
            'no_of_participant' => $v["no_of_participant"],
            'body' => trim($v["body"]),
            'last_edited_by' => $account->id(),
        ));
        $query->condition('w_id', $v["workshop_id"]);
        $result = $query->execute();

          /*For editing speakers */

        $speakersupload = 0;
        for ($i = 0; $i <= $v["speakers_count"]; $i++) {
            if (isset($v['speakers_fieldset'][$i]["s_id"])) {

                if ($v['speakers_fieldset'][$i]["speakername"] != "") {
                    $query = $connection->update('speakers');
                    $query->fields(array(
                        'name' => trim($v['speakers_fieldset'][$i]["speakername"]),
                        'institute' => trim($v['speakers_fieldset'][$i]["institute"]),
                        'place' => trim($v['speakers_fieldset'][$i]["place"])
                    ));
                    $query->condition('s_id', $v['speakers_fieldset'][$i]["s_id"]);
                    $result = $query->execute();
                    if ($result != 0) {
                        $speakersupload++;
                    }
                }
            }
             else {
                if ($v['speakers_fieldset'][$i]["speakername"] != "") {
                    $speakersquery = $connection->insert('speakers');
                    $speakersargs = array(
                        "w_id" => $v["workshop_id"],
                        "name" => trim($v['speakers_fieldset'][$i]["speakername"]),
                        "institute" => trim($v['speakers_fieldset'][$i]["institute"]),
                        "place" => trim($v['speakers_fieldset'][$i]["place"])
                    );
                    $speakersresult = $speakersquery->fields($speakersargs)->execute();
                    if ($speakersresult != 0) {
                        $speakersupload++;
                    }
                }
            }
        }

          /*For editing video */

        $videolink_upload = 0;
        for ($i = 0; $i <= $v["videolink_count"]; $i++) {
   			$v_id=isset($v['videolink_fieldset'][$i]["v_id"])? $v['videolink_fieldset'][$i]["v_id"]:"";
            if ($v_id != "") {
                if ($v['videolink_fieldset'][$i]["videolink_title"] != "") {
                    $query = $connection->update('workshop_videolinks');
                    $query->fields(array(
                        'title' => trim($v['videolink_fieldset'][$i]["videolink_title"]),
                        'link' => trim($v['videolink_fieldset'][$i]["videolink_link"])

                    ));
                    $query->condition('v_id', $v['videolink_fieldset'][$i]["v_id"]);
                    $result = $query->execute();
                    if ($result != 0) {
                        $videolink_upload++;
                    }
                }
            }
            else {
                if ($v['videolink_fieldset'][$i]["videolink_title"] != "") {
                    $videolinkquery = $connection->insert("workshop_videolinks");
                    $videolinkargs = array(
                        "w_id" => $v["workshop_id"],
                        "title" => trim($v['videolink_fieldset'][$i]["videolink_title"]),
                        "link" => trim($v['videolink_fieldset'][$i]["videolink_link"])

                    );
                    $videolinkresult = $videolinkquery->fields($videolinkargs)->execute();
                    if ($videolinkresult != 0) {
                        $videolink_upload++;
                    }
                }
            }
        }

                /* For editing Testimonials */
        $testimonialupload = 0;
        for ($i = 0; $i <= $v["testimonial_count"]; $i++) {
   			$t_id=isset($v['testimonial_fieldset'][$i]["t_id"])?$v['testimonial_fieldset'][$i]["t_id"]:"";
            if ($t_id != "") {
                if ($v['testimonial_fieldset'][$i]["t_name"] != "") {
                    $query = $connection->update('testimonials');
                    $query->fields(array(
                        'body' => trim($v['testimonial_fieldset'][$i]["t_body"]),
                        'name' => trim($v['testimonial_fieldset'][$i]["t_name"]),
                        'department' => trim($v['testimonial_fieldset'][$i]["t_department"]),
                        'university' => trim($v['testimonial_fieldset'][$i]["t_university"])
                    ));
                    $query->condition('t_id', $v['testimonial_fieldset'][$i]["t_id"]);
                    $result = $query->execute();
                    if ($result != 0) {
                        $testimonialupload++;
                    }
                }
            }
            else {
                if ($v['testimonial_fieldset'][$i]["t_name"] != "") {
                    $testimonialquery = $connection->insert('testimonials');
                    $testimonialargs = array(
                        "w_id" => $v["workshop_id"],
                        "body" => trim($v['testimonial_fieldset'][$i]["t_body"]),
                        "name" => trim($v['testimonial_fieldset'][$i]["t_name"]),
                        "department" => trim($v['testimonial_fieldset'][$i]["t_department"]),
                        "university" => trim($v['testimonial_fieldset'][$i]["t_university"])
                    );
                    $testimonialresult = $testimonialquery->fields($testimonialargs)->execute();
                    if ($testimonialresult != 0) {
                        $testimonialupload++;
                    }
                }
            }
        }

        /* For editing Event images */
        /* For deleting existing images */
        $deletecounter = 0;
        $query_img = $connection->select('workshop_images');
        $query_img->fields('workshop_images');
        $query_img->condition('w_id', $v["workshop_id"]);
        $result_img = $query_img->execute();
  		$root_path=Utility::events_images_path();
        while ($row_img = $result_img->fetchObject()) {
            if ($form_state->getValue('imagecheck@' . $row_img->id) == 1) {
                if (file_exists($root_path . $row_img->path)) {
                    unlink($root_path . $row_img->path);
                    $query2 = $connection->delete('workshop_images');
                    $query2->condition('id', $row_img->id);
                    $delete_img = $query2->execute();
                    if ($delete_img != 0) {
                        $deletecounter++;
                    }
                }
                else {
                    drupal_set_message(t('Error Could not delete :') . $filename . t(', file does not exist'), 'error');
                }
            }
        }
        /* For adding more images to existing event */
        $items = array();
        $root_path = Utility::events_images_path();
        $query = $connection->select('workshop');
        $query->fields('workshop');
        $query->condition('w_id', $v["workshop_id"]);
        $result = $query->execute();
        $row_folder = $result->fetchObject();
        $eventfolder = $row_folder -> images_folder;
        $dest_path = $eventfolder . '/';
        if (!is_dir($root_path . $dest_path)) {
            mkdir($root_path . $dest_path);
        }
  		$imageupload = 0;
        /* uploading files */
        foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
            if ($file_name) {
                if (file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])) {
                    drupal_set_message(t("Error uploading file. File !filename already exists."), array(
                        '!filename' => $_FILES['files']['name'][$file_form_name]
                    ), 'error');
                    return;
                }

                /* uploading file */
                if(Utility::get_file_size_MB($_FILES['files']['tmp_name'][$file_form_name]) > \Drupal::state()->get('max_upload_size', 1))
                { drupal_set_message(t('Max file upload size('.\Drupal::state()->get('max_upload_size', 1).'MB) exceeded'), 'error'); return; }

                $sanitized_file_name = preg_replace('/\s+/', '_', $file_name);
                if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $sanitized_file_name)) {
                    $imagequery = $connection->insert('workshop_images');
                    $imageargs = array(
                        "w_id" => $v["workshop_id"],
                        "name" => $file_name,//$_FILES['files']['name'][$file_form_name],
                        "path" => $dest_path . $sanitized_file_name,//$_FILES['files']['name'][$file_form_name]
                    );
                    $imageresult = $imagequery->fields($imageargs)->execute();
                    if ($imageresult != 0) {
                        $imageupload++;
                    }
                    drupal_set_message($file_name . ' uploaded successfully.', 'status');
                }
                else {
                    drupal_set_message($file_name . $dest_path . t('was having an error while uploading :') , 'error');
                }
            }
        }
        if (!$result && $imageupload == 0 && $deletecounter == 0 &&$videolink_upload==0) {
            drupal_set_message(t("Something went wrong, please try again."), "error");
        }
        else {
            drupal_set_message(t("Workshop updated successfully"), "status");
            $mailManager = \Drupal::service('plugin.manager.mail');
            $mailManager->mail('fossee_stats', 'edit_mail', $account->getEmail(), $account->getPreferredLangcode(), array('workshop_name'=>$v['name'],NULL, TRUE));
            if ($imageupload != 0) {
                drupal_set_message(t("Event's Pictures are added successfully"), "status");
            }
            if ($deletecounter != 0) {
                drupal_set_message(t("Event's Pictures are deleted successfully"), "status");
            }
        }
	}

}