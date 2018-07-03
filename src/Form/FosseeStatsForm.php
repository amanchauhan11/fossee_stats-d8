<?php

namespace Drupal\fossee_stats\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Datetime\DrupalDateTime;
use \Drupal\fossee_stats\Utility;
use \Drupal\fossee_stats\Controller\EventActivitiesAll as EA;
use \Drupal\Core\Ajax\AjaxResponse;
use \Drupal\Core\Ajax\HtmlCommand;
use \Drupal\Core\Ajax\RemoveCommand;
use \Drupal\Core\Ajax\ReplaceCommand;
use \Drupal\Core\Database\Database;

class FosseeStatsForm extends FormBase {

	public function getFormId(){
    	return 'FosseeStatsForm';
  	}

  	public function buildForm(array $form, FormStateInterface $form_state) {

  		$options_first = $this->_ajax_example_get_first_dropdown_options();
	    if (null!==$form_state->getValue('foss_sub_project') && null!==$form_state->getValue('foss_type') && null!==$form_state->getValue('foss_sub_project_status')) {
	        $foss_project = (null!==$form_state->getValue('foss_type')) ? $form_state->getValue('foss_type') : key($options_first);
	    }
	    else {
	        $foss_project = '';
	    }
	    $form['foss_type'] = array(
	        '#type' => 'select',
	        '#prefix' => '<div class="content"><div class="row">',
	        '#suffix' => '',
	        '#title' => t('FOSS Type'),
	        '#multiple' => FALSE,
	        '#options' => $options_first,
	        //'#default_value' => $foss_project,
	        '#validated' => TRUE,
	        '#ajax' => array(
	            'callback' => [$this,'ajax_foss_type_dependent_dropdown_callback'],
	            'wrapper' => 'dropdown-second-replace',
	            'progress' => array(
	                'message' => ''
	            )
	        )
	    );
	   /* Second Dropdown for Activities like TBC, LM and other */

	    if (null!==$form_state->getValue('foss_sub_project') && null!==$form_state->getValue('foss_type') && null!==$form_state->getValue('foss_sub_project_status')) {
	        $foss_sub_project = (null!==$form_state->getValue('foss_sub_project')) ? $form_state->getValue('foss_sub_project') : key($this->get_activities_list($foss_project));
	    }
	    else {
	        $foss_sub_project = '';
	    }
	    $form['foss_sub_project'] = array(
	        '#type' => 'select',
	        '#title' => t('Activities'),
	        '#options' => $this->get_activities_list($foss_project),
	        '#prefix' => '<div id="dropdown-second-replace" style="padding-left:25px" >',
	        '#suffix' => '</div>',
	        '#default_value' => "--------------",
	        '#validated' => TRUE,
	        '#ajax' => array(
	            'callback' => [$this,'ajax_foss_sub_project_dependent_dropdown_callback'],
	            'wrapper' => 'dropdown-third-replace',
	            'progress' => array(
	                'message' => ''
	            )
	        ),

	        '#states' => array(
	            'invisible' => array(
	                ':input[name="foss_type"]' => array(
	                    'value' => ""
	                )
	            )
	        )
	    );

	   /* third Dropdown for Status like Completed and In Progress */
	    if (null!==$form_state->getValue('foss_sub_project') && null!==$form_state->getValue('foss_type') && null!==$form_state->getValue('foss_sub_project_status')) {
	        $foss_sub_project_status = (null!==$form_state->getValue('foss_sub_project_status')) ? $form_state->getValue('foss_sub_project_status') : '';
	    }
	    else {
	        $foss_sub_project_status = '';
	    }
	    $form['foss_sub_project_status'] = array(
	        '#type' => 'select',
	        '#title' => t('Status'),
	        '#options' => $this->_ajax_example_get_third_dropdown_options($foss_sub_project),
	        '#prefix' => '<div id="dropdown-third-replace" style="padding-left:25px">',
	        '#suffix' => '</div></div>',
	        '#default_value' => $foss_sub_project_status,
	        '#validated' => TRUE,
	        '#states' => array(
	            'invisible' => array(
	                array(
	                    array(
	                        ':input[name="foss_sub_project"]' => array(
	                            'value' => 0
	                        )
	                    ),
	                    'or',
	                    array(
	                        ':input[name="foss_type"]' => array(
	                            'value' => ""
	                        )

	                    )
	                )
	            )
	        )
	    );
	     /* Start Date for Filter */
	    $form['start_date'] = array(
	        '#type' => 'date',
	        '#title' => t('From Date:'),
	        '#default_value' => new DrupalDateTime(''),
	        '#date_year_range' => '2011:+0',
	        '#prefix' => '<div class="row"><div id="startdate">',
	        '#suffix' => '</div>',
	        '#states' => array(
	            'invisible' => array(
	                ':input[name="foss_type"]' => array(
	                    'value' => ""
	                )
	            )
	        )
	    );
	 /* End Date for Filter */
	    $form['end_date'] = array(
	        '#type' => 'date',
	        '#title' => t('To Date:'),
	        '#default_value' => new DrupalDateTime(''),
	        '#date_year_range' => '2011:+0',
	        '#prefix' => '<div id="enddate" style="padding-left:15px;">',
	        '#suffix' => '</div></div>',
	        '#states' => array(
	            'invisible' => array(
	                ':input[name="foss_type"]' => array(
	                    'value' => ""
	                )
	            )
	        )
	    );
	     /* Country list for Filter */
	    if (null!==$form_state->getValue('countryname') && null!==$form_state->getValue('statename') && null!==$form_state->getValue('cityname')) {
	        $countryname = (null!==$form_state->getValue('countryname')) ? $form_state->getValue('countryname') : key($this->get_country_list($foss_project, $foss_sub_project));
	    }
	    else {
	        $countryname = '';
	    }

	    $form['countryname'] = array(
	        "#type" => "select",
	        "#title" => t("Select Country"),
	        '#prefix' => '<div class="row"><div id="load_country">',
	        '#suffix' => '</div>',
	        '#options' => $this->get_country_list($foss_project, $foss_sub_project),
	        '#states' => array(
	            'invisible' => array(
	                array(
	                    array(
	                        ':input[name="foss_sub_project"]' => array(
	                            'value' => 0
	                        )
	                    ),
	                    'or',
	                    array(
	                        ':input[name="foss_type"]' => array(
	                            'value' => ""
	                        ),
	                        ':input[name="foss_type"]' => array(
	                            'value' => 3
	                        )

	                    )
	                )
	            )
	        ),
	        '#ajax' => array(
	            'callback' => [$this,'ajax_state_list_dependent_dropdown_callback'],
	            'wrapper' => 'load_state',
	            'progress' => array(
	                'message' => ''
	            )
	        )
	    );

	 /* State list for Filter */
	    if (null!==$form_state->getValue('countryname') && null!==$form_state->getValue('statename') && null!==$form_state->getValue('cityname')) {
	        $statename = (null!==$form_state->getValue('statename')) ? $form_state->getValue('statename') : key($this->get_state_list($foss_project, $foss_sub_project, $countryname));
	    }
	    else {
	        $statename = '';
	    }

	    $form['statename'] = array(
	        "#type" => "select",
	        "#title" => t("State Name"),
	        '#validated' => TRUE,
	        '#options' => $this->get_state_list($foss_project, $foss_sub_project, $countryname),
	        '#prefix' => '<div id= "load_state">',
	        '#suffix' => '</div>',
	        '#states' => array(
	            'invisible' => array(
	                array(
	                    array(
	                        ':input[name="foss_sub_project"]' => array(
	                            'value' => 0
	                        )
	                    ),
	                    'or',
	                    array(
	                        ':input[name="foss_type"]' => array(
	                            'value' => ""
	                        ),
	                        ':input[name="foss_type"]' => array(
	                            'value' => 3
	                        )

	                    )
	                )
	            )
	        ),
	        '#ajax' => array(
	            'callback' => [$this,'ajax_city_list_dependent_dropdown_callback'],
	            'wrapper' => 'load_state',
	            'progress' => array(
	                'message' => ''
	            )
	        )
	    );

	/* City list for Filter */
	    if (null!==$form_state->getValue('countryname') && null!==$form_state->getValue('statename') && null!==$form_state->getValue('cityname')) {
	        $cityname = (null!==$form_state->getValue('cityname')) ? $form_state->getValue('cityname') : key($this->get_city_list($foss_project, $foss_sub_project, $countryname, $statename));
	    }
	    else {
	        $cityname = '';
	    }
	    $form['cityname'] = array(
	        "#type" => "select",
	        "#title" => t("City Name"),
	        '#validated' => TRUE,
	        '#options' => $this->get_city_list($foss_project, $foss_sub_project, $countryname, $statename),
	        '#prefix' => '<div id= "load_city">',
	        '#suffix' => '</div></div>',
	        '#states' => array(
	            'invisible' => array(
	                array(
	                    array(
	                        ':input[name="foss_sub_project"]' => array(
	                            'value' => 0
	                        )
	                    ),
	                    'or',
	                    array(
	                        ':input[name="foss_type"]' => array(
	                            'value' => ""
	                        ),
	                        ':input[name="foss_type"]' => array(
	                            'value' => 3
	                        )

	                    )
	                )
	            )
	        )
	    );
	    $form['submit'] = array(
	        '#type' => 'submit',
	        '#ajax' => array(
	            'callback' => [$this,'ajax_example_submit_driven_callback'],
	            'progress' => array(
	                'message' => ''
	            )
	        ),
	        '#value' => t('Filter'),
	        '#prefix' => '',

	        '#suffix' => ''
	    );
	    $form['reset'] = array(
	        '#type' => 'submit',
	        '#value' => t('Reset'),
	        '#prefix' => '',
	        '#suffix' => '</div>'
	    );
	    //This is used for displaying text (tab sta)
	    $form['displaytext'] = array(
	        '#type' => 'markup',
	        '#prefix' => '<div><div id="displaytext" style="font-weight:bold;padding-top:10px">',
	        '#suffix' => '</div></div>',
	        '#markup' => ''
	    );
	    //For displaying count of TBC of all foss
	    $form['tbctable'] = array(
	        '#type' => 'item',
	        '#prefix' => '<div id="default_load" >',
	        '#markup' => '<ul class="nav nav-tabs">
	                        <li class="active"><a data-toggle="tab" href="#tbctabledata">Textbook Companions</a></li>
	                         <li><a data-toggle="tab" href="#lmtabledata">Lab Migrations</a></li>
	                          <li><a data-toggle="tab" href="#workshopdata">Workshops</a></li>
	                            <li><a data-toggle="tab" href="#conferencedata">Conferences </a></li>
	                            <li><a data-toggle="tab" href="#spokentutorialdata">Spoken Tutorials</a></li>
	                            <li><a data-toggle="tab" href="#otheractivities">Other Activities</a></li>
	                      </ul>'
	     );
	    $TBC_getchart = $this->getchart("TBC");
	    $LM_getchart = $this->getchart("LM");
	    $Other_getchart = $this->getchart("Other");
	     $form['tab_content'] = array(
	        '#type' => 'inline_template',
	        '#template' => '<div class="tab-content">

	        <div id="tbctabledata"class="tab-pane fade in active">' . $this->get_tabledata_TBC_or_LM("TBC", "1960/01/01", "")  . \Drupal::service('renderer')->render($TBC_getchart) . '</div>
	        </div>

	        <div id="lmtabledata" class="tab-pane fade ">' . $this->get_tabledata_TBC_or_LM("LM", "1960/01/01", "")  . \Drupal::service('renderer')->render($LM_getchart) . '</div>
	        </div>

	        <div id="workshopdata" class="tab-pane fade ">' . EA::workshop_view_all(0, 1960-01-01, date("Y-m-d")) . '</div>

	        <div id="conferencedata" class="tab-pane fade ">' .EA::conference_seminar_view_all(0, "", "") . '</div>

	        <div id="spokentutorialdata" class="tab-pane fade ">' . $this->spokentutorial_view_all("") . '</div>

	        <div id="otheractivities" class="tab-pane fade ">' . $this->other_activities("","","","") . \Drupal::service('renderer')->render($Other_getchart) . '</div></div>
	 </div>'
	    );
	    $form['lastdiv'] = array(
	        '#type' => 'item',
	        '#markup' => '',
	        '#suffix' => '</div>'
	    );
	    $form['#attached']['library'][] = 'fossee_stats/stats';
	    $form['#attached']['library'][] = 'fossee_stats/piechart';
	    return $form;
  	}

  	public function _ajax_example_get_first_dropdown_options() {
  		$connection = Database::getConnection();
	    $query = $connection->select('foss_type');
	    $query->fields('foss_type', array(
	        'id'
	    ));
	    $query->fields('foss_type', array(
	        'foss_name'
	    ));
	    $result = $query->execute();
	    $options = array();
	    $options[''] = "--------------";
	    while ($foss_detail = $result->fetchObject()) {
	        $options[$foss_detail->id] = $foss_detail->foss_name;
	    }
	    return $options;
	}

	//List of option to fill secong drop down ie. Activities
	public function get_activities_list($foss_project) {
	    if ($foss_project != NULL) {
	    	$connection = Database::getConnection();
	        $query = $connection->select('foss_type');
	        $query->fields('foss_type', array(
	            'tbc',
	            'lab_migration',
	            'workshop',
	            'conference'
	        ));
	        $query->fields('foss_type', array(
	            'spoken_tutorial',
	            'postal_campaigns',
	            'flow_sheet',
	            'circuit_simulation',
	            'case_study'
	        ));
	        $query->condition('id', $foss_project);
	        $result = $query->execute();
	        $subproject_detail = $result->fetchObject();
	        $optiondata = array(
	            "tbc",
	            "lab_migration",
	            "workshop",
	            "conference",
	            "spoken_tutorial",
	            "postal_campaigns",
	            "flow_sheet",
	            "circuit_simulation",
	            "case_study"
	        );
	        $optionvalue = array(
	            " ",
	            "Textbook Companion",
	            "Lab Migration",
	            "Workshop",
	            "Conference",
	            "Spoken Tutorial",
	            "Postal Campaigns",
	            "Flowsheet",
	            "Circuit Simulation",
	            "Case Study"
	        );
	        $options = array();
	        $options[0] = "--------------";
	        $i = 0;
	        foreach ($optiondata as $value) {
	            $i++;
	            if (($subproject_detail->$value) != 0) {
	                $options[$i] = $optionvalue[$i];
	            }
	        }
	        return $options;
	    }
	    else {
	        $options[0] = "--------------";
	        return $options;
	    }
	}

	//this is ajax callback method for first dropdown ie FOSS type
	public function ajax_foss_type_dependent_dropdown_callback(array $form, FormStateInterface $form_state) {


		$ajaxResponse = new AjaxResponse(); 
	    $foss_sub_project = $form_state->getValue('foss_type');
	    $foss_sub_project_activities = $form_state->getValue('foss_sub_project');
	    $startdate = $form_state->getValue('start_date');
	    $enddate = $form_state->getValue('end_date');
	    $enddate = trim($enddate);
	    $startdate = trim($startdate);
	    if ($startdate == "") {
	        $startdate = '1960/01/01';
	    }
	    else {
	        $startdate = $startdate;
	    }
	    if ($enddate == "") {
	        $enddate = date("Y-m-d");
	    }
	    else {
	        $enddate = $enddate;
	    }
	    if ($foss_sub_project == "") {
	        $form['default_load']['#markup'] = '<ul class="nav nav-tabs">
	                        <li class="active"><a data-toggle="tab" href="#tbctabledata">Textbook Companion</a></li>
	                         <li><a data-toggle="tab" href="#lmtabledata">Lab Migration</a></li>
	                          <li><a data-toggle="tab" href="#workshopdata">Workshop</a></li>
	                           <li><a data-toggle="tab" href="#selfworkshopdata">Self Workshop</a></li>
	                            <li><a data-toggle="tab" href="#conferencedata">Conference </a></li>
	                            <li><a data-toggle="tab" href="#spokentutorialdata">Spoken Tutorial</a></li>
	                      </ul>


	    <div class="tab-content">

	        <div id="tbctabledata"class="tab-pane fade in active">' . $this->get_tabledata_TBC_or_LM("TBC", $startdate, $enddate) . '
	        <div id="tbcchartdata" style="float:left;width:300px;height:300px;">' . \Drupal::service('renderer')->render(getchart("TBC")) . '</div>
	        </div>

	        <div id="lmtabledata" class="tab-pane fade ">' . $this->get_tabledata_TBC_or_LM("LM", $startdate, $enddate) . '
	        <div id="lmchartdata" style="float:left;width:350px;height:300px;">' . Drupal::service('renderer')->render(getchart("LM")) . '</div>
	        </div>

	        <div id="workshopdata" class="tab-pane fade ">' . EA::workshop_view_all(0, $startdate, $enddate) . '</div>

	        <div id="selfworkshopdata" class="tab-pane fade">'. $this->getselfworkshoplcount(0, $startdate, $enddate, "", "") . '</div>

	        <div id="conferencedata" class="tab-pane fade ">' . $this->EA::conference_seminar_view_all(0, $startdate, $enddate) . '</div>

	        <div id="spokentutorialdata" class="tab-pane fade ">' . $this->spokentutorial_view_all("") . '</div>
	 </div>';
	 		$ajaxResponse->addCommand(new HtmlCommand("#default_load", \Drupal::service('renderer')->render($form['default_load'])));
	        $form['load_city']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#load_city", ""));
	        $form['load_state']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#load_state", ""));
	        $form['load_country']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#load_country", ""));
	        $form['startdate']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#startdate", ""));
	        $form['enddate']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#enddate", ""));
	        $form['dropdown-third-replace']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#dropdown-third-replace", ""));
	    }
	    elseif ($foss_sub_project == '3') {

	        $form['startdate']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#startdate", ""));
	        $form['enddate']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#enddate", ""));
	        $form['load_city']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#load_city", ""));
	        $form['load_state']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#load_state", ""));
	        $form['load_country']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#load_country", ""));
	        $form['dropdown-second-replace']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#dropdown-second-replace", ""));
	        $form['dropdown-third-replace']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#dropdown-third-replace", ""));


	    }

	    else {
	        $form['start_date']['#type'] = "date";
	        $form['start_date']['#title'] = t('From Date:');
	        $form['start_date']['#default_value'] = new DrupalDateTime('');
	        $form['start_date']['#date_year_range'] = '2011:+0';
	        $ajaxResponse->addCommand(new HtmlCommand("#startdate", \Drupal::service('renderer')->render($form['start_date'])));

	        $form['end_date']['#type'] = "date";
	        $form['end_date']['#title'] = t('To Date:');
	        $form['end_date']['#date_year_range'] = '2011:+0';
	        $ajaxResponse->addCommand(new HtmlCommand("#enddate", \Drupal::service('renderer')->render($form['end_date'])));

	  		$form['load_city']['#markup'] = "";
	  		$ajaxResponse->addCommand(new HtmlCommand("#load_city", ""));
	        $form['load_state']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#load_state", ""));
	        $form['load_country']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#load_country", ""));
	    }

	    $form['foss_sub_project']['#default_value'] = "--------------";
	    $form['foss_sub_project']['#options'] = $this->get_activities_list($foss_sub_project);
	    $ajaxResponse->addCommand(new ReplaceCommand("#dropdown-second-replace", $form['foss_sub_project']));


	    if ($foss_sub_project != "" && ($foss_sub_project_activities == 1 || $foss_sub_project_activities == 2 || $foss_sub_project_activities == 7 || $foss_sub_project_activities == 8)) {

	    if ($foss_sub_project == 3) {
	      $foss_sub_project_activities="";
	    $form['dropdown-third-replace']['#markup'] = "";
	    	  $ajaxResponse->addCommand(new HtmlCommand("#dropdown-third-replace", ""));
	    }
	    else{
	      $form['foss_sub_project_status']['#type'] = "select";
	          $form['foss_sub_project_status']['#title'] = t("Status");
	          $form['foss_sub_project_status']['#options'] = $this->_ajax_example_get_third_dropdown_options($foss_sub_project_activities);
	          $ajaxResponse->addCommand(new HtmlCommand("#dropdown-third-replace", \Drupal::service('renderer')->render($form['foss_sub_project_status'])));
	    }

	    }
	    else {
	        $form['dropdown-third-replace']['#markup'] = "";
	        $ajaxResponse->addCommand(new HtmlCommand("#dropdown-third-replace", ""));
	    }
	    return $ajaxResponse;
	}

	//Returns table with count of TBC /LM
	public function get_tabledata_TBC_or_LM($sub_type, $startdate, $enddate) {
	    if ($enddate == "") {
	        $enddate = date("Y-m-d");
	    }
	    $rows = array();
	    $headers = array(
	        " Project",
	        "Completed",
	        "In Progress"
	    );
	    $connection = Database::getConnection();
	    if ($sub_type == 'TBC') {
	        $query = $connection->select('foss_type');
	        $query->fields('foss_type', array(
	            'id'
	        ));
	        $query->fields('foss_type', array(
	            'foss_name'
	        ));
	        $query->fields('foss_type', array(
	            'tbc'
	        ));
	        $query->fields('foss_type', array(
	            'tbc_completed'
	        ));
	        $query->fields('foss_type', array(
	            'tbc_pending'
	        ));
	        $query->condition('tbc', 1);
	        $result = $query->execute();
	        while ($foss_detail = $result->fetchObject()) {
	            $foss_type = $foss_detail->foss_name;
	            Database::setActiveConnection($foss_type);;

	            if ($foss_detail->foss_name != 'Python') {
	                Database::setActiveConnection($foss_detail->foss_name); //Active other database
	                if ($foss_detail->foss_name != 'eSim' && $foss_detail->foss_name != 'OpenFOAM' && $foss_detail->foss_name != 'OpenModelica' && $foss_detail->foss_name != 'OR-Tools') {
 
	                    	$query2 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    	$query2->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    	$result2 = $query2->condition('po.proposal_status',3)->condition('pe.approval_status',1)->condition('pe.category',0,'>')->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :enddate',array(':enddate'=>$enddate))->countQuery()->execute();

	                    	/*
	                        $query2 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND pe.category>0 AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate ", array(
	                            ':startdate' => $startdate,
	                            ':enddate' => $enddate
	                        ));*/
	                    
	                   /* else {
	                        $query2 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND pe.category>0 AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate ", array(
	                            ':startdate' => $startdate,
	                            ':enddate' => $enddate
	                        ));
	                    }*/
	                }
	                else {
	                	$query2 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                	$query2->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                	$result2 = $query2->condition('po.proposal_status',3)->condition('pe.approval_status',1)->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :enddate',array(':enddate'=>$enddate))->countQuery()->execute();

	                  /*  $query2 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate ", array(
	                        ':startdate' => $startdate,
	                        ':enddate' => $enddate
	                    ));*/
	                }
	                $completedbookcount = $result2->fetchField();
	                if ($foss_detail->tbc_completed != "" && $foss_detail->tbc_completed != NULL) {
	                    $clink = "<a href=" . $foss_detail->tbc_completed . " target='_blank'>" . $completedbookcount . "</a>";
	                    $completedbookcount = $clink;
	                }

	                 /* For setting completion date for pending TBC and LM more */
	      $pending_enddate = date('Y-m-d', strtotime("+5 months", strtotime( $enddate)));

	                if ($foss_detail->foss_name != 'eSim' && $foss_detail->foss_name != 'OpenModelica'  && $foss_detail->foss_name != 'OpenFOAM' && $foss_detail->foss_name != 'OR-Tools') {
	                    if ($foss_detail->foss_name != 'DWSIM') {
	                    	$query3 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    	$query3->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    	$result3 = $query3->condition('po.proposal_status',3,'<>')->condition('pe.approval_status',1)->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :pending_date',array(':pending_date'=>$pending_enddate))->countQuery()->execute();

	                        /*$query3 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <>3 AND pe.approval_status =1 AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate ", array(
	                            ':startdate' => $startdate,
	                            ':enddate' => $pending_enddate
	                        ));*/
	                    }
	                    else {
	                    	$query3 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    	$query3->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    	$result3 = $query3->condition('po.proposal_status',3,'<>')->condition('pe.approval_status',1)->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :pending_date',array(':pending_date'=>$pending_enddate))->countQuery()->execute();

	                       /* $query3 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1  AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate ", array(
	                            ':startdate' => $startdate,
	                            ':enddate' => $pending_enddate
	                        ));*/
	                    }
	                }
	                else {
	                    /*$query3 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <>3 AND pe.approval_status =1 AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate ", array(
	                        ':startdate' => $startdate,
	                        ':enddate' => $pending_enddate
	                    ));*/
	                    $query3 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    $query3->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    $result3 = $query3->condition('po.proposal_status',3,'<>')->condition('pe.approval_status',1)->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :pending_date',array(':pending_date'=>$pending_enddate))->countQuery()->execute();
	                }
	                $pendingbookcount = $result3->fetchField();
	                if ($foss_detail->tbc_pending != "" && $foss_detail->tbc_pending != NULL) {
	                    $plink = "<a href=" . $foss_detail->tbc_pending . " target='_blank'>" . $pendingbookcount . "</a>";
	                    $pendingbookcount = $plink;
	                }
	                Database::setActiveConnection(); // We need to call the main (drupal); db back
	                //Database::setActiveConnection(); // without the paramater means set back to the default for the site
	                $item = array(
	                    $foss_detail->foss_name,
	                    $completedbookcount,
	                    $pendingbookcount
	                );
	                array_push($rows, $item);
	            }
	            else {
	                //For Python TBC
	                Database::setActiveConnection($foss_detail->foss_name);; //Active other database
	                $query5 = Database::getConnection()->select('tbc_book');
	                $query5->addExpression('count(*)', 'count');
	                $query5->condition('approved', 1);
	                $result5 = $query5->execute();
	                $completedbookcount = $result5->fetchObject()->count;
	                if ($foss_detail->tbc_completed != "" && $foss_detail->tbc_completed != NULL) {
	                    $clink = "<a href=" . $foss_detail->tbc_completed . " target='_blank'>" . $completedbookcount . "</a>";
	                    $completedbookcount = $clink;
	                }
	                $query6 = Database::getConnection()->select('tbc_book');
	                $query6->addExpression('count(*)', 'count');
	                $query6->condition('approved', 1, '<>');
	                $result6 = $query6->execute();
	                $pendingbookcount = $result6->fetchObject()->count;
	                if ($foss_detail->tbc_pending != "" && $foss_detail->tbc_pending != NULL) {
	                    $plink = "<a href=" . $foss_detail->tbc_pending . " target='_blank'>" . $pendingbookcount . "</a>";
	                    $pendingbookcount = $plink;
	                }
	                Database::setActiveConnection(); // We need to call the main (drupal); db back
	                //Database::setActiveConnection(); // without the paramater means set back to the default for the site
	                $item = array(
	                    $foss_detail->foss_name,
	                    $completedbookcount,
	                    $pendingbookcount
	                );
	                array_push($rows, $item);
	            }
	        }
	    }
	    else {
	        $query = Database::getConnection()->select('foss_type');
	        $query->fields('foss_type', array(
	            'id'
	        ));
	        $query->fields('foss_type', array(
	            'foss_name'
	        ));
	        $query->fields('foss_type', array(
	            'lab_migration'
	        ));
	        $query->fields('foss_type', array(
	            'lm_completed'
	        ));
	        $query->fields('foss_type', array(
	            'lm_pending'
	        ));
	        $query->condition('lab_migration', 1);
	        $result = $query->execute();
	        while ($foss_detail = $result->fetchObject()) {
	            Database::setActiveConnection($foss_detail->foss_name);; //Active other database
	            $query2 = Database::getConnection()->select('lab_migration_proposal');
	            $query2->addExpression('count(*)', 'count');
	            $query2->condition('approval_status', 3);
	            $result2 = $query2->execute();
	            $completedlabcount = $query2->countQuery()->execute()->fetchField();
	            if ($foss_detail->lm_completed != "" && $foss_detail->lm_completed != NULL) {
	                $clink = "<a href=" . $foss_detail->lm_completed . " target='_blank'>" . $completedlabcount . "</a>";
	                $completedlabcount = $clink;
	            }
	            $query3 = Database::getConnection()->select('lab_migration_proposal');
	            $query3->addExpression('count(*)', 'count');
	            $query3->condition('approval_status', 1);
	            $result3 = $query3->execute();
	            $pendinglabcount = $query3->countQuery()->execute()->fetchField();
	            if ($foss_detail->lm_pending != "" && $foss_detail->lm_pending != NULL) {
	                $plink = "<a href=" . $foss_detail->lm_pending . " target='_blank'>" . $pendinglabcount . "</a>";
	                $pendinglabcount = $plink;
	            }
	            $item = array(
	                $foss_detail->foss_name,
	                $completedlabcount,
	                $pendinglabcount
	            );
	            array_push($rows, $item);
	        }
	    }
	    Database::setActiveConnection(); // We need to call the main (drupal); db back
	    //Database::setActiveConnection(); // without the paramater means set back to the default for the site
	    $count = Utility::bootstrap_table_format($headers, $rows);
	    return $count;
	}

	public function _ajax_example_get_third_dropdown_options($foss_sub_project) {
	    $options = array();
	    if ($foss_sub_project != 0) {
	        if ($foss_sub_project == 1) {
	            $options[0] = "--------------";
	            $options[1] = "Books in Progress";
	            $options[2] = "Completed Books";
	        }
	        elseif ($foss_sub_project == 2) {
	            $options[0] = "--------------";
	            $options[1] = "Labs in Progress";
	            $options[2] = "Completed Labs";
	        }elseif ($foss_sub_project == 7) {
	            $options[0] = "--------------";
	            $options[1] = "Flowsheets in Progress";
	            $options[2] = "Completed Flowsheets";
	        }
	        elseif ($foss_sub_project == 8) {
	            $options[0] = "--------------";
	            $options[1] = "Simulations in Progress";
	            $options[2] = "Completed Simulations";
	        }
	    }

	    return $options;
	}

	//Get list of country
	public function get_country_list($foss_type, $foss_sub_project) {

	     if ($foss_type!= 0) {
	        if ($foss_sub_project != 0) {

	        	$connection = Database::getConnection();
	            $query = $connection->select('foss_type');
	            $query->fields('foss_type', array(
	                'id'
	            ));
	            $query->fields('foss_type', array(
	                'foss_name'
	            ));
	            $query->condition('id', $foss_type);
	            $result = $query->execute();
	            $foss_detail = $result->fetchObject();
	            $foss_type_name = $foss_detail->foss_name;
	            $options = array();

	            if ($foss_sub_project == 1 && $foss_type!=3) {

	                $sub_project = "textbook_companion_proposal";

	                Database::setActiveConnection($foss_type_name);; //Active other database
	                $connection = Database::getConnection();
	                $options[0] = '--------------';
	                $query = $connection->select($sub_project);
	                $query->fields($sub_project, ['country'])->distinct();
	                $result = $query->execute();
	                while ($country_detail = $result->fetchObject()) {
	                    $options[$country_detail->country] = $country_detail->country;
	                }


	            }
	            elseif ($foss_sub_project == 2 && $foss_type!=3) {
	                $sub_project = "lab_migration_proposal";

	                Database::setActiveConnection($foss_type_name);; //Active other database
	                $connection = Database::database();
	                // $options    = array();
	                $options[0] = '--------------';
	                $query = $connection->select($sub_project)->fields($sub_project, 'country')->distinct();
	                $result = $query->execute();
	                while ($country_detail = $result->fetchObject()) {
	                    $options[$country_detail->country] = $country_detail->country;
	                }
	            }
	            elseif ($foss_sub_project == 3||$foss_sub_project == 5) {
	                $sub_project = "workshop";
	                $foss_type_db = 'default';

	                Database::setActiveConnection();; //Active other database
	                //$options    = array();
	                $connection = Database::getConnection();
	                $options[0] = '--------------';
	                $query = $connection->select($sub_project)->fields($sub_project, 'country')->distinct()->condition($foss_type_name, 'foss_name');
	                $result = $query->execute();
	                while ($country_detail = $result->fetchObject()) {
	                    $options[$country_detail->country] = $country_detail->country;
	                }

	            }
	            elseif ($foss_sub_project == 4) {

	                $options[0] = '--------------';
	                $options[1] = 'India';

	            }


	            Database::setActiveConnection(); // We need to call the main (drupal); db back


	            return $options;
	        }
	        else {
	            $options = array();
	            $options[0] = '--------------';
	            return $options;
	        }
	    }
	}

	//Get the list of State according to country selection .It is ajax method for state
	public function ajax_state_list_dependent_dropdown_callback(array $form, FormStateInterface $form_state) {
	    $foss_type = $form['foss_type']['#options'][$form_state->getValue('foss_type')];
	    $foss_sub_project = $form['foss_sub_project']['#options'][$form_state->getValue('foss_sub_project')];
	    $country = $form['countryname']['#options'][$form_state->getValue('countryname')];

	    $options = array();
	    if ($country!="--------------") {
	    if ($foss_sub_project == "Textbook Companion") {
	        $sub_project = "textbook_companion_proposal";

	        Database::setActiveConnection($foss_type);; //Active other database

	        $options[0] = '--------------';	        
	        $result = Database::getConnection()->select($sub_project)->fields($sub_project)->condition('country', $country)->execute();

	        while ($state_detail = $result->fetchObject()) {
	            $options[$state_detail->state] = $state_detail->state;
	        }
	    }
	    elseif ($foss_sub_project == "Lab Migration") {
	        $sub_project = "lab_migration_proposal";

	        Database::setActiveConnection($foss_type);; //Active other database
	        //$options    = array();
	        $options[0] = '--------------';
	        $result = Database::getConnection()->select($sub_project)->fields($sub_project)->condition('country', $country)->execute();

	        while ($state_detail = $result->fetchObject()) {
	            $options[$state_detail->state] = $state_detail->state;
	        }

	    }
	    elseif ($foss_sub_project == "Workshop") {
	        $sub_project = "workshop";
	        $foss_type_db = 'default';

	        Database::setActiveConnection();; //Active other database
	        //$options    = array();
	        $options[0] = '--------------';
	        $result = Database::getConnection()->select($sub_project)->fields($sub_project)->condition('country', $country)->condition('foss_name', $foss_type)->execute();

	        while ($state_detail = $result->fetchObject()) {
	            $options[$state_detail->state] = $state_detail->state;
	        }
	    }
	    elseif ($foss_sub_project == "Conference") {
	        $sub_project = "workshop";
	        $foss_type_db = 'default';

	        Database::setActiveConnection();; //Active other database
	        //$options    = array();
	        $options[0] = '--------------';
	        $result = Database::getConnection()->select($sub_project)->fields($sub_project)->condition('country', $country)->condition('foss_name', $foss_type)->execute();

	        while ($state_detail = $result->fetchObject()) {
	            $options[$state_detail->state] = $state_detail->state;
	        }
	    }
	    elseif ($foss_sub_project == "Self Workshop") {


	        //Get self workshop foss number for spoken

	  	$query = Database::getConnection()->select('foss_type')->fields('foss_type', 'id')->fields('foss_type', 'foss_name')->fields('foss_type', 'foss_selfworkshop_no');

	        $query->condition('foss_name', $foss_type);
	        $result = $query->execute();
	        $foss_detail = $result->fetchObject();
	  $foss_selfworkshop_no=$foss_detail->foss_selfworkshop_no;

	      Database::setActiveConnection('selfworkshop');; //Active other database
	            $options[0] = '--------------';
	            $query = Database::getConnection()->select('events_training', 't');
	            $query->addJoin('inner', 'events_academiccenter', 'ac', 't.academic_id=ac.id');
	            $query->addJoin('inner','events_city', 'c', 'ac.city_id=c.id');
	            $query->addJoin('inner', 'events_state', 's', 'c.state_id=s.id');
	            $result = $query->condition('status',4)->condition('foss_id', $foss_selfworkshop_no)->fields('s','name')->distinct()->execute();

	            /*$query = "SELECT distinct (s.name) as state FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id )
	       ";
	            $args = array(
	                ":foss_id" => $foss_selfworkshop_no,
	            );*/

	            while ($state_detail = $result->fetchObject()) {
	                $options[$state_detail->state] = $state_detail->state;

	            }

	    }
	    }
	    else{
	   $options[0] = '--------------';
	    }

		Database::setActiveConnection(); // We need to call the main (drupal); db back
	    $form['statename']['#options'] = $options;
	    $response = new AjaxResponse();
	    $response->addCommand(new ReplaceCommand("#load_state", $form['statename']));

	    $optionscity = array();
	    $optionscity[0] = '--------------';
	    $form['cityname']['#options'] = $optionscity;
	    $response->addCommand(new ReplaceCommand("#load_city", $form['cityname']));

	    return $response;
	}

	public function get_state_list($foss_type, $foss_sub_project, $country) {
	   if (($foss_type != 0) && $foss_sub_project != 0 && $country != "") {

	   		$connection = Database::getConnection();
	        $query = $connection->select('foss_type');
	        $query->fields('foss_type', array(
	            'id'
	        ));
	        $query->fields('foss_type', array(
	            'foss_name'
	        ));
	  $query->fields('foss_type', array(
	            'foss_selfworkshop_no'
	        ));
	        $query->condition('id', $foss_type);
	        $result = $query->execute();
	        $foss_detail = $result->fetchObject();
	        $foss_type_name = $foss_detail->foss_name;
	  $foss_selfworkshop_no=$foss_detail->foss_selfworkshop_no;
	        $options = array();

	        if ($foss_sub_project == 1 && $foss_type!=3) {
	    $options = array();
	            $sub_project = "textbook_companion_proposal";

	            Database::setActiveConnection($foss_type_name);; //Active other database

	            $options[0] = '--------------';
	            $result = Database::getConnection()->select($sub_project)->fields($sub_project)->condition('country',$country)->execute();

	            while ($state_detail = $result->fetchObject()) {
	                $options[$state_detail->state] = $state_detail->state;
	            }

	        }
	        elseif ($foss_sub_project == 2 && $foss_type!=3) {
	            $sub_project = "lab_migration_proposal";
	            $options = array();
	            Database::setActiveConnection($foss_type_name);; //Active other database

	            $options[0] = '--------------';
	            $result = Database::getConnection()->select($sub_project)->condition('country', $country)->execute();

	            while ($state_detail = $result->fetchObject()) {
	                $options[$state_detail->state] = $state_detail->state;
	            }

	        }
	        elseif ($foss_sub_project == 3||$foss_sub_project == 5) {
	            $options = array();
	            $sub_project = "workshop";
	            $foss_type_db = 'default';

	             //Active other database
	            Database::setActiveConnection($foss_type_db);;

	            $options[0] = '--------------';
	            $result = Database::getConnection()->select($sub_project)->condition('country',$country)->execute();

	            while ($state_detail = $result->fetchObject()) {
	                $options[$state_detail->state] = $state_detail->state;
	            }

	        }
	        elseif ($foss_sub_project == 4) {

				 //Active other database
	            Database::setActiveConnection('selfworkshop');;
	            $options[0] = '--------------';


	          /*  $query = "SELECT distinct (s.name) as state FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id )

	       ";
	       		            $args = array(
	                ":foss_id" => $foss_selfworkshop_no,
	            );*/

	       	$result = Database::getConnection()->select('events_training', 't')->addJoin('inner', 'events_academiccenter', 'ac', 't.academic_id=ac.id')->addJoin('inner','events_city', 'c', 'ac.city_id=c.id')->addJoin('inner', 'events_state', 's', 'c.state_id=s.id')->condition('status',4)->condition('foss_id', $foss_selfworkshop_no)->fields('s','name')->distinct()->execute();

	            while ($state_detail = $result->fetchObject()) {
	                $options[$state_detail->state] = $state_detail->state;

	            }

	        }
	        Database::setActiveConnection(); // We need to call the main (drupal); db back

	        return $options;
	    }
	    else {
	        $options = array();
	        $options[0] = '--------------';
	        return $options;
	    }
	}

	//Get the list of City according to country selection .It is ajax method for state
	public function ajax_city_list_dependent_dropdown_callback(array $form, FormStateInterface $form_state) {

	    $foss_type = $form['foss_type']['#options'][$form_state->getValue('foss_type')];
	    $foss_sub_project = $form['foss_sub_project']['#options'][$form_state->getValue('foss_sub_project')];
	    $country = $form['countryname']['#options'][$form_state->getValue('countryname')];
	    $state = $form['statename']['#options'][$form_state->getValue('statename')];
	    if ($foss_sub_project == "Textbook Companion") {
	        $sub_project = "textbook_companion_proposal";

			 //Active other database
	        Database::setActiveConnection($foss_type);;
	        $options = array();
	        $options[0] = '--------------';

	        $result = Database::getConnection()->select($sub_project)->fields($sub_project)->condition('country',$country)->condition('state', $state)->execute();

	        while ($city_detail = $result->fetchObject()) {
	            $options[$city_detail->city] = $city_detail->city;
	        }

	    }
	    elseif ($foss_sub_project == "Lab Migration") {
	        $sub_project = "lab_migration_proposal";

	        //Active other database
	        Database::setActiveConnection($foss_type);;
	        $result = Database::getConnection()->select($foss_type)->fields($sub_project)->condition('country',$country)->condition('state',$state)->execute();

	        while ($city_detail = $result->fetchObject()) {
	            $options[$city_detail->city] = $city_detail->city;
	        }

	    }
	    elseif ($foss_sub_project == "Workshop") {
	        $sub_project = "workshop";
	        $foss_type_db = 'default';

	        Database::setActiveConnection($foss_type_db);; //Active other database
	        Database::setActiveConnection();;
	        $result = Database::getConnection()->select($sub_project)->fields($sub_project)->condition('country',$country)->condition('state',$state)->condition('foss_name',$foss_type)->execute();

	        while ($city_detail = $result->fetchObject()) {
	            $options[$city_detail->city] = $city_detail->city;
	        }
	    }
	    elseif ($foss_sub_project == "Conference") {
	        $sub_project = "workshop";
	        $foss_type_db = 'default';

	        Database::setActiveConnection();; //Active other database
	        $options = array();
	        $options[0] = '--------------';
	        $result = Database::getConnection()->select($sub_project)->fields($sub_project)->condition('country',$country)->condition('state',$state)->condition('foss_name',$foss_type)->execute();

	        while ($city_detail = $result->fetchObject()) {
	            $options[$city_detail->city] = $city_detail->city;
	        }
	    }
	    elseif ($foss_sub_project == "Self Workshop") {

	  //Get self workshop foss number for spoken

	  $query = Database::getConnection()->select('foss_type');
	        $query->fields('foss_type', array(
	            'id'
	        ));
	        $query->fields('foss_type', array(
	            'foss_name'
	        ));
	  $query->fields('foss_type', array(
	            'foss_selfworkshop_no'
	        ));
	        $query->condition('foss_name', $foss_type);
	        $result = $query->execute();
	        $foss_detail = $result->fetchObject();
	  $foss_selfworkshop_no=$foss_detail->foss_selfworkshop_no;
	  Database::setActiveConnection('selfworkshop');; //Active other database
	        $options[0] = '--------------';

	 /* $query="SELECT distinct (c.name) as city FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) and s.name LIKE :statename";

	  $args = array(
	                ":foss_id" => $foss_selfworkshop_no,
	    ":statename" => $state,
	            );
	            $result = db_query($query, $args);*/

	    $query = Database::getConnection()->select('events_training', 't');
	    $query->addJoin('inner', 'events_academiccenter', 'ac', 't.academic_id=ac.id');
	    $query->addJoin('inner','events_city', 'c', 'ac.city_id=c.id');
	    $query->addJoin('inner', 'events_state', 's', 'c.state_id=s.id');
	    $result = $query->condition('status',4)->condition('foss_id', $foss_selfworkshop_no)->condition('s.name',$state,'LIKE')->fields('c','name')->distinct()->execute();

            while ($city_detail = $result->fetchObject()) {
                $options[$city_detail->city] = $city_detail->city;

            }

	     }


	    Database::setActiveConnection(); // We need to call the main (drupal); db back

	    $form['cityname']['#options'] = $options;
	    $response = new AjaxResponse();
	    $response->addCommand(new ReplaceCommand("#load_city", $form['cityname']));
	    return $response;

	}

	//Get List of City
	public function get_city_list($foss_type, $foss_sub_project, $country, $state) {

	     if (($foss_type != 0 ) && $foss_sub_project != 0 && $country != "" && $state != "") {


	        $query = Database::getConnection()->select('foss_type');
	        $query->fields('foss_type', array(
	            'id'
	        ));
	        $query->fields('foss_type', array(
	            'foss_name'
	        ));
	  $query->fields('foss_type', array(
	            'foss_selfworkshop_no'
	        ));
	        $query->condition('id', $foss_type);
	        $result = $query->execute();
	        $foss_detail = $result->fetchObject();
	        $foss_type = $foss_detail->foss_name;
	        $foss_selfworkshop_no=$foss_detail->foss_selfworkshop_no;
	        if ($foss_sub_project == 1  && $foss_type!="Python") {
	            $sub_project = "textbook_companion_proposal";
	            Database::setActiveConnection($foss_type);; //Active other database
	            $options = array();
	            $options[0] = '--------------';
	            $result = Database::getConnection()->select($sub_project)->condition('country',$country)->fields($sub_project)->condition('state',$state)->execute();
	            while ($city_detail = $result->fetchObject()) {
	                $options[$city_detail->city] = $city_detail->city;
	            }

	        }
	        elseif ($foss_sub_project == 2  && $foss_type!="Python") {
	            $sub_project = "lab_migration_proposal";
	           	Database::setActiveConnection($foss_type);; //Active other database
	            $options = array();
	            $options[0] = '--------------';
	            $result = \Drupal::database($sub_project)->condition('country',$country)->condition('state',$state)->execute();

	            while ($city_detail = $result->fetchObject()) {
	                $options[$city_detail->city] = $city_detail->city;
	            }


	        }
	        elseif ($foss_sub_project == 3||$foss_sub_project == 5) {
	            $sub_project = "workshop";
	            $foss_type_db = 'default';

	            Database::setActiveConnection();; //Active other database
	            $options = array();
	            $options[0] = '--------------';
	            $result = \Drupal::database($sub_project)->condition('country',$country)->condition('foss_name',$foss_type)->execute();

	            while ($city_detail = $result->fetchObject()) {
	                $options[$city_detail->city] = $city_detail->city;
	            }
	        }
	        elseif ($foss_sub_project == 4) {

	        	Database::setActiveConnection('selfworkshop');; //Active other database
	        $options[0] = '--------------';

	  /*$query="SELECT distinct (c.name) as city FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) and s.name LIKE :statename";

	  $args = array(
	                ":foss_id" => $foss_selfworkshop_no,
	    ":statename" => $state,
	            );
	            $result = db_query($query, $args);*/

	      	$query = Database::getConnection()->select('events_training', 't');
	      	$query->addJoin('inner', 'events_academiccenter', 'ac', 't.academic_id=ac.id');
	      	$query->addJoin('inner','events_city', 'c', 'ac.city_id=c.id');
	      	$query->addJoin('inner', 'events_state', 's', 'c.state_id=s.id');
	      	$result = $query->condition('status',4)->condition('foss_id', $foss_selfworkshop_no)->condition('s.name',$state,'LIKE')->fields('c','name')->distinct()->execute();

	            while ($city_detail = $result->fetchObject()) {
	                $options[$city_detail->city] = $city_detail->city;

	            }

	  }



	        Database::setActiveConnection(); // We need to call the main (drupal); db back


	        return $options;
	    }
	    else {
	        $options = array();
	        $options[0] = '--------------';
	        return $options;
	    }
	}

	//Form Submit
	public function ajax_example_submit_driven_callback(array $form, FormStateInterface $form_state) {

		$response = new AjaxResponse();
	    $foss_type = $form['foss_type']['#options'][$form_state->getValue('foss_type')];
	    $foss_sub_project = $form['foss_sub_project']['#options'][$form_state->getValue('foss_sub_project')];
	    $foss_sub_project_status = $form['foss_sub_project_status']['#options'][$form_state->getValue('foss_sub_project_status')];
	    $startdate = $form_state->getValue('start_date');
	    $startdate = trim($startdate);
	    $enddate = $form_state->getValue('end_date');
	    $enddate = trim($enddate);
	    $countryname = $form['countryname']['#options'][$form_state->getValue('countryname')];
	    $countryname = trim($countryname);
	    $cityname = $form['cityname']['#options'][$form_state->getValue('cityname')];
	    $cityname = trim($cityname);
	    $statename = $form['statename']['#options'][$form_state->getValue('statename')];
	    $statename = trim($statename);
	    $link_flag = 0;

	    if ($cityname == "--------------" || $cityname == "") {
	        $cityname = "%";
	    }
	    else {
	        $cityname = $cityname;
	    }
	    if ($statename == "--------------" || $statename == "") {
	        $statename = "%";
	    }
	    else {
	        $statename = $statename;
	    }
	    if ($countryname == "--------------" || $countryname == "") {
	        $countryname = "%";
	    }
	    else {
	        $countryname = $countryname;
	    }
	    if ($startdate == "") {
	        $startdate = '1960-01-01';
	    }
	    else {
	        $startdate = $startdate;
	    }
	    if ($enddate == "") {
	        $enddate = date("Y-m-d");
	    }
	    else {
	        $enddate = $enddate;
	    }
	    if ($foss_type == '--------------') {
	        $form['default_load']['#markup'] = '<ul class="nav nav-tabs">
	                        <li class="active"><a data-toggle="tab" href="#tbctabledata">Textbook Companion</a></li>
	                         <li><a data-toggle="tab" href="#lmtabledata">Lab Migration</a></li>
	                          <li><a data-toggle="tab" href="#workshopdata">Workshop</a></li>
	                            <li><a data-toggle="tab" href="#conferencedata">Conference </a></li>
	                            <li><a data-toggle="tab" href="#spokentutorialdata">Spoken Tutorial</a></li>
	                            <li><a data-toggle="tab" href="#otheractivities">Other Activities</a></li>
	                      </ul>
	                      <div class="tab-content">

	        <div id="tbctabledata"class="tab-pane fade in active">' . $this->get_tabledata_TBC_or_LM("TBC", $startdate, $enddate) . '
	        <div id="tbcchartdata" style="float:left;width:300px;height:300px;">' . \Drupal::service('renderer')->render(getchart("TBC")) . '</div>
	        </div>

	        <div id="lmtabledata" class="tab-pane fade ">' . $this->get_tabledata_TBC_or_LM("LM", $startdate, $enddate) . '
	        <div id="lmchartdata" style="float:left;width:3100px;height:300px;">' . \Drupal::service('renderer')->render(getchart("LM")) . '</div>
	        </div>

	        <div id="workshopdata" class="tab-pane fade ">' . $this->EA::workshop_view_all(0, $startdate, $enddate) . '</div>

	        <div id="conferencedata" class="tab-pane fade ">' . EA::conference_seminar_view_all(0, "", "") . '</div>

	        <div id="spokentutorialdata" class="tab-pane fade ">' . spokentutorial_view_all("") . '</div>
	        <div id="otheractivities" class="tab-pane fade ">' . other_activities("","",$startdate,$enddate) . '
	        <div id="otherchartdata" style="float:left;width:300px;height:300px;">' . \Drupal::service('renderer')->render(getchart("Other")) . '</div></div>
	 </div>
	          ';
	        $response->addCommand(new HtmlCommand("#default_load", \Drupal::service('renderer')->render($form['default_load'])));
	    }
	    elseif ($foss_type != '--------------' && (($foss_sub_project == "--------------") || ($foss_sub_project == "No Sub-Project Available"))) {

	    $tbcdatacheck="";
	    $tbcchartcheck="";
	    $lmdatacheck="";
	    $lmchartcheck="";
	    $workshopdatacheck="";
	    $self_workshopdatacheck="";
	    $conferencedatacheck="";
	    $spoken_tutorialdatacheck="";




	        $form['displaytext']['#markup'] = "Statistic of project : " . $foss_type;
	        $response->addCommand(new HtmlCommand("#displaytext", \Drupal::service('renderer')->render($form['displaytext'])));
	        Database::setActiveConnection('default'); // We need to call the main (drupal); db back
	        Database::setActiveConnection();; // without the paramater means set back to the default for the site
	        $query = Database::getConnection()->select('foss_type');
	        $query->fields('foss_type', array(
	            'tbc',
	            'lab_migration',
	            'workshop',
	            'self_workshop',
	            'conference'
	        ));
	        $query->fields('foss_type', array(
	            'spoken_tutorial',
	            'postal_campaigns',
	            'flow_sheet',
	            'circuit_simulation',
	            'case_study'
	        ));
	        $query->condition('foss_name', $foss_type);
	        $result = $query->execute();
	        $subproject_detail = $result->fetchObject();
	        $optiondata = array(
	            "tbc",
	            "lab_migration",
	            "workshop",
	            "conference",
	            "spoken_tutorial",
	            "postal_campaigns",
	            'flow_sheet',
	            'circuit_simulation',
	            'case_study'
	        );
	        $optionvalue = array(
	            " ",
	            "Textbook Companion",
	            "Lab Migration",
	            "Workshop",
	            "Conference",
	            "Spoken Tutorial",
	            "Postal Campaigns",
	            "Flowsheet",
	            "Circuit Simulation",
	            "Case Study"
	        );
	        $i = 0;
	        $pagecontent = "";
	     //    foreach ($optiondata as $value) {
	     //        $i++;
	     //        //$options[$i]=$optionvalue[$i];
	     //         if (($subproject_detail->$value) != 0) {
	     //            if ($value == "tbc") {
	     //                $pagecontent .= "<div class='tab-preview'>
	     //            <input type='radio' id='tab-1' name='tab-group-1' checked>
	     //            <label for='tab-1'>Textbook Companion</label>
	     //            <div class='tabcontent'><div id='tbctabledata'
	     //      style='float:left;width:2200px;height:200px'>" . get_tabledata_selectedFoss_TBC_LM($foss_type, 'TBC', 'all', $startdate, $enddate, $countryname, $statename, $cityname, $link_flag) . "</div><div id='tbcchartdata' style='float:left;width:2100px;height:200px;'>" . drupal_render(getchartforselectedProject($foss_type, "TBC", $startdate, $enddate, $countryname, $statename, $cityname)) . "</div></div></div>";
	     //            } else if ($value == "lab_migration") {
	     //                $pagecontent .= "<div class='tab-preview'>
	     //        <input type='radio' id='tab-2' name='tab-group-1'>
	     //              <label for='tab-2'>Lab Migration</label>
	     //              <div class='tabcontent'>
	     //        <div id='lmtabledata' style='float:left;width:2200px;height:200px'>" . get_tabledata_selectedFoss_TBC_LM($foss_type, 'LM', "all", $startdate, $enddate, $countryname, $statename, $cityname, $link_flag) . "</div>
	     //        <div id='lmchartdata' style='float:left;width:2100px;height:200px;'>" . drupal_render(getchartforselectedProject($foss_type, "LM", $startdate, $enddate, $countryname, $statename, $cityname)) . "<div></div></div></div></div>";
	     //            } else if ($value == "workshop") {

	     //                $event_type = "Workshop";
	     //                $pagecontent .= "<div class='tab-preview'>
	     //            <input type='radio' id='tab-3' name='tab-group-1' >
	     //            <label for='tab-3'>Workshop</label>
	     //            <div class='tabcontent'><div>" . events_view_filter($foss_type, $event_type, $startdate, $enddate, $countryname, $statename, $cityname) . "</div></div></div>";
	     //            } else if ($value == "self_workshop") {
	     //                $pagecontent .= "<div class='tab-preview'>
	     //            <input type='radio' id='tab-4' name='tab-group-1' >
	     //            <label for='tab-4'>Self Workshop</label>
	     //            <div class='tabcontent'><div id='selfworkshop'>".getselfworkshoplcount($foss_type,$startdate,$enddate, $statename, $cityname)."</div></div></div>";
	     //            } else if ($value == "conference") {
	     //                $event_type = "Conference";
	     //                $pagecontent .= "<div class='tab-preview'>
	     //            <input type='radio' id='tab-5' name='tab-group-1' >
	     //            <label for='tab-5'>Conference</label>
	     //            <div class='tabcontent'><div id='conference'>" . events_view_filter($foss_type, $event_type, $startdate, $enddate, $countryname, $statename, $cityname) . "</div></div></div>";
	     //            } else if ($value == "spoken_tutorial") {
	     //                $pagecontent .= "<div class='tab-preview'>
	     //            <input type='radio' id='tab-6' name='tab-group-1' >
	     //            <label for='tab-6'>Spoken Tutorial</label>
	     //            <div class='tabcontent'>".spokentutorial_view_all($foss_type)."</div></div>";
	     //            }


	     //        } else {
	     //            if ($value == "tbc") {
	     //                $pagecontent .= "<div class='tab-preview'>
	     //             <input type='radio' id='tab-1' name='tab-group-1' checked>
	     //             <label for='tab-1'>Textbook Companion</label>
	     //           <div class='tabcontent'><div id='tbctabledata'
	          // >" . t("
	          // <h5>TextBook Companion Project Not Present</h5>") . "</div></div></div>";
	     //            } else if ($value == "lab_migration") {
	     //                $pagecontent .= "<div class='tab-preview'>
	          //  <input type='radio' id='tab-2' name='tab-group-1'>
	       //             <label for='tab-2'>Lab Migration</label>
	     //             <div class='tabcontent'>
	          //  <div id='lmtabledata'>" . t("
	          // <h5>Lab Migration Project Not Present</h5>") . "<div></div></div></div></div>";
	     //            } else if ($value == "workshop") {
	     //                $pagecontent .= "<div class='tab-preview'>
	     //             <input type='radio' id='tab-3' name='tab-group-1' >
	     //             <label for='tab-3'>Workshop</label>
	     //           <div class='tabcontent'><div>" . t("
	          // <h5> No Workshop were conducted </h5>") . "</div></div></div>";
	     //            } else if ($value == "self_workshop") {
	     //                $pagecontent .= "<div class='tab-preview'>
	     //             <input type='radio' id='tab-4' name='tab-group-1' >
	     //             <label for='tab-4'>Self Workshop</label>
	     //           <div class='tabcontent'>" . t("
	          // <h5>No Self Workshop were conducted</h5>") . "</div></div>";
	     //            } else if ($value == "conference") {
	     //                $pagecontent .= "<div class='tab-preview'>
	     //             <input type='radio' id='tab-5' name='tab-group-1' >
	     //             <label for='tab-5'>Conference</label>
	     //           <div class='tabcontent'><div id='conference'>" . t("
	          // <h5>No Conference were conducted</h5>") . "</div></div></div>";
	     //            } else if ($value == "spoken_tutorial") {
	     //                $pagecontent .= "<div class='tab-preview'>
	     //             <input type='radio' id='tab-6' name='tab-group-1' >
	     //             <label for='tab-6'>Spoken Tutorial</label>
	     //           <div class='tabcontent'>" . t("
	          // <h5>Spoken Tutorial Not Present</h5>") . "</div></div>";
	     //            }
	     //        }
	     //    }
	     //    $form['default_load']['#markup'] = $pagecontent;


	foreach ($optiondata as $value) {
	             $i++;
	            //$options[$i]=$optionvalue[$i];
	              if (($subproject_detail->$value) != 0) {
	                 if ($value == "tbc") {
	                      $tbcdatacheck=$this->get_tabledata_selectedFoss_TBC_LM($foss_type, 'TBC', 'all', $startdate, $enddate, $countryname, $statename, $cityname, $link_flag);

	 $tbcchartcheck=\Drupal::service('renderer')->render(getchartforselectedProject($foss_type, 'TBC', $startdate, $enddate, $countryname, $statename, $cityname));

	                 }
	                 elseif ($value == "lab_migration") {
	                $lmdatacheck= $this->get_tabledata_selectedFoss_TBC_LM($foss_type, 'LM', 'all', $startdate, $enddate, $countryname, $statename, $cityname, $link_flag) ;
	             $lmchartcheck=\Drupal::service('renderer')->render(getchartforselectedProject($foss_type, 'LM', $startdate, $enddate, $countryname, $statename, $cityname));
	                 }
	                 elseif ($value == "workshop") {
	                     $event_type = "Workshop";
	                    $workshopdatacheck= $this->events_view_filter($foss_type, $event_type, $startdate, $enddate, $countryname, $statename, $cityname);
	                 }
	                 elseif ($value == "self_workshop") {
	                     $self_workshopdatacheck=$this->getselfworkshoplcount($foss_type, $startdate, $enddate, $statename, $cityname);
	                 }
	                 elseif ($value == "conference") {
	                     $event_type = "Conference";
	                    $conferencedatacheck= $this->events_view_filter($foss_type, $event_type, $startdate, $enddate, $countryname, $statename, $cityname);
	                 }
	                 elseif ($value == "spoken_tutorial") {
	                   $spoken_tutorialdatacheck= $this->spokentutorial_view_all($foss_type);
	                 }elseif ($value == "flow_sheet") {
	                   $other_activities = $this->other_activities($foss_type,"",$startdate,$enddate);
	                 }elseif ($value == "circuit_simulation") {
	                   $other_activities = $this->other_activities($foss_type,"",$startdate,$enddate);
	                 }


	             }
	             else {
	                 if ($value == "tbc") {
	                           $tbcdatacheck="<h5>TextBook Companion Project Not Present</h5>";
	          $tbcchartcheck="";
	                 }
	                 elseif ($value == "lab_migration") {
	                          $lmdatacheck="<h5>Lab Migration Project Not Present</h5>";
	          $lmchartcheck="";
	                 }
	                 elseif ($value == "workshop") {
	                          $workshopdatacheck= "<h5> No Workshop were conducted </h5>";
	                 }
	                 elseif ($value == "self_workshop") {
	               $self_workshopdatacheck="<h5>No Self Workshop were conducted</h5>";
	                 }
	                 elseif ($value == "conference") {
	          $conferencedatacheck="<h5>No Conference were conducted</h5>";
	                 }
	                 elseif ($value == "spoken_tutorial") {

	        $spoken_tutorialdatacheck="<h5>Spoken Tutorial Not Present</h5>";
	                 }elseif ($value == "flow_sheet") {
	                     $other_activities = "<h5>No Flowsheet is available</h5>";
	                 }elseif ($value == "circuit_simulation") {
	                     $other_activities = "<h5>No Circuit Simulation is available</h5>";
	                 }
	             }
	         }

	   // var_dump($tbcdatacheck."::::::".$lmdatacheck."::::::".$workshopdatacheck.":::::::".$self_workshopdatacheck.":::::::".$conferencedatacheck."::::::".$spoken_tutorialdatacheck);
	//die;




	         $form['default_load']['#markup'] = '<ul class="nav nav-tabs">
	                        <li class="active"><a data-toggle="tab" href="#tbctabledata">Textbook Companion</a></li>
	                         <li><a data-toggle="tab" href="#lmtabledata">Lab Migration</a></li>
	                          <li><a data-toggle="tab" href="#workshopdata">Workshop</a></li>
	                            <li><a data-toggle="tab" href="#conferencedata">Conference </a></li>
	                            <li><a data-toggle="tab" href="#spokentutorialdata">Spoken Tutorial</a></li>
	                            <li><a data-toggle="tab" href="#otheractivities">Other Activities</a></li>
	                      </ul>
	        <div class="tab-content">

	            <div id="tbctabledata"class="tab-pane fade in active">' . $tbcdatacheck . '
	            <div id="tbcchartdata" style="float:left;width:300px;height:300px;">' . $tbcchartcheck . '</div>
	            </div>
	            <div id="lmtabledata" class="tab-pane fade ">' . $lmdatacheck . '
	        <div id="lmchartdata" style="float:left;width:3100px;height:300px;">' . $lmchartcheck . '</div>
	        </div>
	         <div id="workshopdata" class="tab-pane fade ">
	         '. $workshopdatacheck . '</div>
	         <div id="conferencedata" class="tab-pane fade ">' . $conferencedatacheck . '</div>

	        <div id="spokentutorialdata" class="tab-pane fade ">' . $spoken_tutorialdatacheck . '</div>
	        <div id="otheractivities" class="tab-pane fade ">' . $other_activities . '</div>
	        </div>
	                    ';
	        $response->addCommand(new HtmlCommand("#default_load", \Drupal::service('renderer')->render($form['default_load'])));

	    }
	    elseif ($foss_type != '--------------' && $foss_sub_project == "Textbook Companion" && $foss_sub_project_status == "--------------") {

	        $form['displaytext']['#markup'] = "Statistic of project : " . $foss_type;
	        $response->addCommand(new HtmlCommand("#displaytext", \Drupal::service('renderer')->render($form['displaytext'])));

	        $pagecontent = "";
	        $pagecontent .= '<ul class="nav nav-tabs">
	                        <li class="active"><a data-toggle="tab" href="#tbctabledata">Textbook Companion</a></li></ul>
	                        <div class="tab-content">

	        <div id="tbctabledata"class="tab-pane fade in active">' . $this->get_tabledata_selectedFoss_TBC_LM($foss_type, 'TBC', 'all', $startdate, $enddate, $countryname, $statename, $cityname, $link_flag) . '
	        <div id="tbcchartdata" style="float:left;width:300px;height:300px;">' . \Drupal::service('renderer')->render(getchartforselectedProject($foss_type, "TBC", $startdate, $enddate, $countryname, $statename, $cityname)) . '</div>
	        </div></div>';
	        $form['default_load']['#markup'] = $pagecontent;
	        $response->addCommand(new HtmlCommand("#default_load", \Drupal::service('renderer')->render($form['default_load'])));

	    }
	    elseif ($foss_type != '--------------' && $foss_sub_project == "Lab Migration" && $foss_sub_project_status == "--------------") {
	        $form['displaytext']['#markup'] = "Statistic of project : " . $foss_type;
	        $response->addCommand(new HtmlCommand("#displaytext", \Drupal::service('renderer')->render($form['displaytext'])));
	        $pagecontent = "";
	        $pagecontent .= '<ul class="nav nav-tabs">
	                       <li><a data-toggle="tab" href="#lmtabledata">Lab Migration</a></li></ul>
	                        <div class="tab-content">

	       <div id="lmtabledata" class="tab-pane fade in active ">' . $this->get_tabledata_selectedFoss_TBC_LM($foss_type, 'LM', "all", $startdate, $enddate, $countryname, $statename, $cityname, $link_flag) . '
	        <div id="lmchartdata" style="float:left;width:3100px;height:300px;">' . \Drupal::service('renderer')->render(getchartforselectedProject($foss_type, "LM", $startdate, $enddate, $countryname, $statename, $cityname)) . '</div>
	        </div>   </div>';
	        $form['default_load']['#markup'] = $pagecontent;
	        $response->addCommand(new HtmlCommand("#default_load", \Drupal::service('renderer')->render($form['default_load'])));

	    }
	    elseif ($foss_type != '--------------' && $foss_sub_project == "Workshop") {

	        $form['displaytext']['#markup'] = "Statistic of project : " . $foss_type;
	        $response->addCommand(new HtmlCommand("#displaytext", \Drupal::service('renderer')->render($form['displaytext'])));


	        $pagecontent = "";
	        $pagecontent .= '<ul class="nav nav-tabs">
	                       <li><a data-toggle="tab" href="#workshopdata">Workshop</a></li></ul>
	                        <div class="tab-content">

	        <div id="workshopdata" class="tab-pane fade in active">' . $this->events_view_filter($foss_type, $foss_sub_project, $startdate, $enddate, $countryname, $statename, $cityname) . '</div> </div>';

	        $form['default_load']['#markup'] = $pagecontent;
	        $response->addCommand(new HtmlCommand("#default_load", \Drupal::service('renderer')->render($form['default_load'])));

	    }
	    elseif ($foss_type != '--------------' && $foss_sub_project == "Conference") {

	        $form['displaytext']['#markup'] = "Statistic of project : " . $foss_type;
	        $response->addCommand(new HtmlCommand("#displaytext", \Drupal::service('renderer')->render($form['displaytext'])));


	        $pagecontent = "";
	        $pagecontent .= '<ul class="nav nav-tabs">
	                         <li><a data-toggle="tab" href="#conferencedata">Conference </a></li></ul>
	                        <div class="tab-content">

	      <div id="conferencedata" class="tab-pane fade  in active">' . $this->events_view_filter($foss_type, $foss_sub_project, $startdate, $enddate, $countryname, $statename, $cityname) . '</div></div>';

	        $form['default_load']['#markup'] = $pagecontent;
	        $response->addCommand(new HtmlCommand("#default_load", \Drupal::service('renderer')->render($form['default_load'])));

	    }
	    elseif ($foss_type != '--------------' && $foss_sub_project == "Spoken Tutorial") {

	        $form['displaytext']['#markup'] = "Statistic of project : " . $foss_type;
	        $response->addCommand(new HtmlCommand("#displaytext", \Drupal::service('renderer')->render($form['displaytext'])));


	        $pagecontent = "";
	        $pagecontent .= '<ul class="nav nav-tabs">
	                         <li><a data-toggle="tab" href="#spokentutorialdata">Spoken Tutorial</a></li></ul>
	                        <div class="tab-content">

	       <div id="spokentutorialdata" class="tab-pane fade  in active ">' . $this->spokentutorial_view_all($foss_type) . '</div></div>';

	        $form['default_load']['#markup'] = $pagecontent;
	        $response->addCommand(new HtmlCommand("#default_load", \Drupal::service('renderer')->render($form['default_load'])));
	}elseif ($foss_type != '--------------' && $foss_sub_project == "Flowsheet" && $foss_sub_project_status == "--------------") {

	    $form['displaytext']['#markup'] = "Statistic of project : DWSIM";
	    $response->addCommand(new HtmlCommand("#displaytext", \Drupal::service('renderer')->render($form['displaytext'])));


	    $pagecontent = "";
	    $pagecontent .= '<ul class="nav nav-tabs">
	                     <li><a data-toggle="tab" href="#otheractivities">Other Activities</a></li></ul>
	                    <div class="tab-content">

	   <div id="otheractivities" class="tab-pane fade  in active ">' . $this->other_activities('DWSIM',"",$startdate,$enddate) . '</div></div>';

	    $form['default_load']['#markup'] = $pagecontent;
	    $response->addCommand(new HtmlCommand("#default_load", \Drupal::service('renderer')->render($form['default_load'])));
	}elseif ($foss_type != '--------------' && $foss_sub_project == "Circuit Simulation" && $foss_sub_project_status == "--------------") {

	    $form['displaytext']['#markup'] = "Statistic of project : eSim";
	    $response->addCommand(new HtmlCommand("#displaytext", \Drupal::service('renderer')->render($form['displaytext'])));


	    $pagecontent = "";
	    $pagecontent .= '<ul class="nav nav-tabs">
	                     <li><a data-toggle="tab" href="#otheractivities">Other Activities</a></li></ul>
	                    <div class="tab-content">

	   <div id="otheractivities" class="tab-pane fade  in active ">' . $this->other_activities('eSim',"",$startdate,$enddate) . '</div></div>';

	    $form['default_load']['#markup'] = $pagecontent;
	    $response->addCommand(new HtmlCommand("#default_load", \Drupal::service('renderer')->render($form['default_load'])));
	}

	/* else if ($foss_type != '--------------' && $foss_sub_project == "Postal Campaigns") {

	        $form['displaytext']['#markup'] = "Statistic of project : " . $foss_type;
	        $response->addCommand(new HtmlCommand("#displaytext", \Drupal::service('renderer')->render($form['displaytext'])));


	        $pagecontent = "";
	        $pagecontent .= "<div class='tab-preview'>
	                <input type='radio' id='tab-1' name='tab-group-1' >
	                <label for='tab-1'>Postal Campaigns</label>
	                <div class='tabcontent'>" . postalcampaign_view_all($foss_type, $startdate, $enddate) . "</div></div>";

	        $form['default_load']['#markup'] = $pagecontent;
	        $response->addCommand(new HtmlCommand("#default_load", \Drupal::service('renderer')->render($form['default_load'])));

	    }*/

	  elseif ($foss_type != '--------------' && ($foss_sub_project == "Textbook Companion" || $foss_sub_project == "Lab Migration") && $foss_sub_project_status != "--------------") {
	        if ($foss_sub_project == "Textbook Companion" && $foss_sub_project_status == "Completed Books") {
	            $title = "Textbook Companion";
	            $datatable = $this->get_tabledata_selectedFoss_TBC_LM($foss_type, 'TBC', "completed", $startdate, $enddate, $countryname, $statename, $cityname, $link_flag);
	        }
	        elseif ($foss_sub_project == "Lab Migration" && $foss_sub_project_status == "Completed Labs") {
	            $title = "Lab Migration";
	            $datatable = $this->get_tabledata_selectedFoss_TBC_LM($foss_type, 'LM', "completed", $startdate, $enddate, $countryname, $statename, $cityname, $link_flag);
	        }
	        elseif ($foss_sub_project == "Textbook Companion" && $foss_sub_project_status == "Books in Progress") {
	            $title = "Textbook Companion";
	            $datatable = $this->get_tabledata_selectedFoss_TBC_LM($foss_type, 'TBC', "pending", $startdate, $enddate, $countryname, $statename, $cityname, $link_flag);
	        }
	        elseif ($foss_sub_project == "Lab Migration" && $foss_sub_project_status == "Labs in Progress") {
	            $title = "Lab Migration";
	            $datatable = $this->get_tabledata_selectedFoss_TBC_LM($foss_type, 'LM', "pending", $startdate, $enddate, $countryname, $statename, $cityname, $link_flag);
	        }elseif ($foss_sub_project == "Flowsheet" && $foss_sub_project_status == "Flowsheets in Progress") {
	            $title = "Flowsheet (DWSIM)";
	            $datatable = $this->other_activities("DWSIM","progress",$startdate,$enddate);
	        }elseif ($foss_sub_project == "Flowsheet" && $foss_sub_project_status == "Completed Flowsheets") {
	            $title = "Flowsheet (DWSIM)";
	            $datatable = $this->other_activities("DWSIM","completed",$startdate,$enddate);
	        }elseif ($foss_sub_project == "Circuit Simulation" && $foss_sub_project_status == "Simulations in Progress") {
	            $title = "Circuit Simulation (eSim)";
	            $datatable = $this->other_activities("eSim","progress",$startdate,$enddate);
	        }elseif ($foss_sub_project == "Circuit Simulation" && $foss_sub_project_status == "Completed Simulations") {
	            $title = "Circuit Simulation (eSim)";
	            $datatable = $this->other_activities("eSim","completed",$startdate,$enddate);
	        }
	        $form['displaytext']['#markup'] = "Statistic of project : " . $foss_type;
	        $response->addCommand(new HtmlCommand("#displaytext", \Drupal::service('renderer')->render($form['displaytext'])));
	        $form['default_load']['#markup'] = '<ul class="nav nav-tabs">
	        <li><a data-toggle="tab" href="#tabledata">' . $title . '</a></li></ul>
	        <div class="tab-content">
	        <div id="tabledata"class="tab-pane fade in active">'. $datatable . '</div>
	        </div>';
	        $response->addCommand(new HtmlCommand("#default_load", \Drupal::service('renderer')->render($form['default_load'])));

	    }

	    return $response;
	}

	/* This function is used to create chart for TBC/LM */
	public function getchart($sub_type) {

		static $chartno = 0;
	    $title = "";
	    if ($sub_type == "TBC") {
	        $title = "Textbook Companion Statistics";
	    }
	    elseif ($sub_type == "LM") {
	        $title = "Lab Migration Statistics";
	    }elseif ($sub_type == "Other") {
	        $title = "Other Activities";
	    }
	    $id = 'piechart'.$chartno;//'piechart'.time();
	  	$chart_element['chart'] = [
	  		'#type'=>'inline_template',
	  		'#prefix'=>'<div id="'.$id.'" style="float:left;width:300px;height:300px;">',
	    	'#suffix'=>'</div>'
	  	];
	  	//$chart_element['#attached']['library'][] = 'fossee_stats/piechart';
	  	$chart_element['#attached']['drupalSettings']['gc'][$chartno]['title'] = $title;
	  	$chart_element['#attached']['drupalSettings']['gc'][$chartno]['chart_id'] = $id;
	    $chart_element['#attached']['drupalSettings']['gc'][$chartno]['chart_data'] = $this->get_data_for_chart_allproject($sub_type);
	   /* $chart = array(
	        '#type' => 'chart',
	        '#title' => t($title),
	        '#chart_type' => 'pie',
	        '#chart_library' => 'google',
	        '#legend_position' => 'right',
	        '#data_labels' => TRUE,
	        '#tooltips' => TRUE,
	        '#width' => 700,
	        '#height' => 300
	    );
	    $chart['pie_data'] = array(
	        '#type' => 'chart_data',
	        '#title' => t($title),
	        '#data' => $this->get_data_for_chart_allproject($sub_type)
	    );
	    $example['chart'] = $chart;*/
	    $chartno++;
	    return $chart_element;
	}

	public function spokentutorial_view_all($foss_name) {
	  if (strlen($foss_name) == 0 || $foss_name == "NULL") {
	        $foss_name= "%";
	    }
	    else {
	        $foss_name = $foss_name;
	    }
	    $page_content = "";
	    $headers = array(
	        "Foss Type",
	        "Count of Video"

	    );

	    $rows1 = array();
	    $query1 = Database::getConnection()->select('spokentutorial');
	    $query1->fields('spokentutorial');
	    $query1->condition('foss_name', $foss_name, 'LIKE');
	    $result1 = $query1->execute();
	    while ($row = $result1->fetchObject()) {
	        $item = array(
	            $row->foss_name,
	            "<a href='" . $row->link . "' target='_blank' title='Click to view tutorial'>" . $row->count . "</a>",
	        );
	        array_push($rows1, $item);
	    }
	    $page_content .= Utility::bootstrap_table_format($headers, $rows1);
	    return $page_content;
	}

	public function other_activities($foss_name,$status,$startdate = false,$enddate = false) {
	    $page_content = "";
	    if ($startdate == "" || $startdate == false) {
	      $startdate = "1960-01-01";
	    }
	    if ($enddate == "" || $enddate == false) {
	      $enddate = date("Y-m-d");
	    }
	    $rows = array();
	    if ($foss_name == "" || $foss_name == NULL) {
	      $headers = array(
	          "Activities",
	          "Progress",
	          "Completed"
	      );
	      Database::setActiveConnection('DWSIM');
	      $completed = Database::getConnection()->select('dwsim_flowsheet_proposal')
	        ->condition('approval_status',3)
	        ->where("FROM_UNIXTIME(actual_completion_date) >= :startdate", array(':startdate'=>$startdate))
	        ->where("FROM_UNIXTIME(actual_completion_date) <= :enddate", array(':enddate'=>$enddate))
	        ->countQuery()
	        ->execute()
	        ->fetchField();
	      $progress = Database::getConnection()->select('dwsim_flowsheet_proposal')
	        ->condition('approval_status',1)
	        ->where("FROM_UNIXTIME(approval_date) >= :startdate", array(':startdate'=>$startdate))
	        ->where("FROM_UNIXTIME(approval_date) <= :enddate", array(':enddate'=>$enddate))
	        ->countQuery()
	        ->execute()
	        ->fetchField();
	      $item = array(
	        'Flowsheet (DWSIM)',
	        '<a href="https://dwsim.fossee.in/flowsheeting-project/flowsheet-progress" target="_blank">'.$progress.'</a>',
	        '<a href="https://dwsim.fossee.in/flowsheeting-project/completed-flowsheet" target="_blank">'.$completed.'</a>'
	      );
	      array_push($rows, $item);
	      Database::setActiveConnection('eSim');
	      $completed = Database::getConnection()->select('esim_circuit_simulation_proposal')
	        ->condition('approval_status',3)
	        ->where("FROM_UNIXTIME(actual_completion_date) >= :startdate", array(':startdate'=>$startdate))
	        ->where("FROM_UNIXTIME(actual_completion_date) <= :enddate", array(':enddate'=>$enddate))
	        ->countQuery()
	        ->execute()
	        ->fetchField();
	      $progress = Database::getConnection()->select('esim_circuit_simulation_proposal')
	        ->condition('approval_status',1)
	        ->where("FROM_UNIXTIME(approval_date) >= :startdate", array(':startdate'=>$startdate))
	        ->where("FROM_UNIXTIME(approval_date) <= :enddate", array(':enddate'=>$enddate))
	        ->countQuery()
	        ->execute()
	        ->fetchField();
	      $item = array(
	        'Circuit Simulation (eSim)',
	        '<a href="https://esim.fossee.in/circuit-simulation-project/circuit-simulation-progress" target="_blank">'.$progress.'</a>',
	        '<a href="https://esim.fossee.in/circuit-simulation-project/completed-circuit-simulations" target="_blank">'.$completed.'</a>'
	      );
	      array_push($rows, $item);
	    }elseif ($foss_name == "DWSIM") {
	        if ($status == 'progress') {
	          $headers = array(
	              "Activities",
	              "Progress"
	          );
	          Database::setActiveConnection('DWSIM');
	          $progress = Database::getConnection()->select('dwsim_flowsheet_proposal')
	            ->condition('approval_status',1)
	            ->where("FROM_UNIXTIME(approval_date) >= :startdate", array(':startdate'=>$startdate))
	            ->where("FROM_UNIXTIME(approval_date) <= :enddate", array(':enddate'=>$enddate))
	            ->countQuery()
	            ->execute()
	            ->fetchField();
	          $item = array(
	            'Flowsheet (DWSIM)',
	            '<a href="https://dwsim.fossee.in/flowsheeting-project/flowsheet-progress" target="_blank">'.$progress.'</a>'
	          );
	          array_push($rows, $item);
	        }elseif ($status == 'completed') {
	          $headers = array(
	              "Activities",
	              "Completed"
	          );
	          Database::setActiveConnection('DWSIM');
	          $completed = Database::getConnection()->select('dwsim_flowsheet_proposal')
	            ->condition('approval_status',3)
	            ->where("FROM_UNIXTIME(actual_completion_date) >= :startdate", array(':startdate'=>$startdate))
	            ->where("FROM_UNIXTIME(actual_completion_date) <= :enddate", array(':enddate'=>$enddate))
	            ->countQuery()
	            ->execute()
	            ->fetchField();
	          $item = array(
	            'Flowsheet (DWSIM)',
	            '<a href="https://dwsim.fossee.in/flowsheeting-project/completed-flowsheet" target="_blank">'.$completed.'</a>'
	          );
	          array_push($rows, $item);
	        }else {
	          $headers = array(
	              "Activities",
	              "Progress",
	              "Completed"
	          );
	          Database::setActiveConnection('DWSIM');
	          $completed = Database::getConnection()->select('dwsim_flowsheet_proposal')
	            ->condition('approval_status',3)
	            ->where("FROM_UNIXTIME(actual_completion_date) >= :startdate", array(':startdate'=>$startdate))
	            ->where("FROM_UNIXTIME(actual_completion_date) <= :enddate", array(':enddate'=>$enddate))
	            ->countQuery()
	            ->execute()
	            ->fetchField();
	          $progress = Database::getConnection()->select('dwsim_flowsheet_proposal')
	            ->condition('approval_status',1)
	            ->where("FROM_UNIXTIME(approval_date) >= :startdate", array(':startdate'=>$startdate))
	            ->where("FROM_UNIXTIME(approval_date) <= :enddate", array(':enddate'=>$enddate))
	            ->countQuery()
	            ->execute()
	            ->fetchField();
	          $item = array(
	            'Flowsheet (DWSIM)',
	            '<a href="https://dwsim.fossee.in/flowsheeting-project/flowsheet-progress" target="_blank">'.$progress.'</a>',
	            '<a href="https://dwsim.fossee.in/flowsheeting-project/completed-flowsheet" target="_blank">'.$completed.'</a>'
	          );
	          array_push($rows, $item);
	        }
	    }elseif ($foss_name == "eSim") {
	        if ($status == "progress") {
	          $headers = array(
	              "Activities",
	              "Progress"
	          );
	          Database::setActiveConnection('eSim');
	          $progress = Database::getConnection()->select('esim_circuit_simulation_proposal')
	            ->condition('approval_status',1)
	            ->where("FROM_UNIXTIME(approval_date) >= :startdate", array(':startdate'=>$startdate))
	            ->where("FROM_UNIXTIME(approval_date) <= :enddate", array(':enddate'=>$enddate))
	            ->countQuery()
	            ->execute()
	            ->fetchField();
	          $item = array(
	            'Circuit Simulation (eSim)',
	            '<a href="https://esim.fossee.in/circuit-simulation-project/circuit-simulation-progress" target="_blank">'.$progress.'</a>'
	          );
	          array_push($rows, $item);
	        }elseif ($status == "completed") {
	          $headers = array(
	              "Activities",
	              "Completed"
	          );
	          Database::setActiveConnection('eSim');
	          $completed = Database::getConnection()->select('esim_circuit_simulation_proposal')
	            ->condition('approval_status',3)
	            ->where("FROM_UNIXTIME(actual_completion_date) >= :startdate", array(':startdate'=>$startdate))
	            ->where("FROM_UNIXTIME(actual_completion_date) <= :enddate", array(':enddate'=>$enddate))
	            ->countQuery()
	            ->execute()
	            ->fetchField();
	          $item = array(
	            'Circuit Simulation (eSim)',
	            '<a href="https://esim.fossee.in/circuit-simulation-project/completed-circuit-simulations" target="_blank">'.$completed.'</a>'
	          );
	          array_push($rows, $item);
	        }else {
	          $headers = array(
	              "Activities",
	              "Progress",
	              "Completed"
	          );
	          Database::setActiveConnection('eSim');
	          $completed = Database::getConnection()->select('esim_circuit_simulation_proposal')
	            ->condition('approval_status',3)
	            ->where("FROM_UNIXTIME(actual_completion_date) >= :startdate", array(':startdate'=>$startdate))
	            ->where("FROM_UNIXTIME(actual_completion_date) <= :enddate", array(':enddate'=>$enddate))
	            ->countQuery()
	            ->execute()
	            ->fetchField();
	          $progress = Database::getConnection()->select('esim_circuit_simulation_proposal')
	            ->condition('approval_status',1)
	            ->where("FROM_UNIXTIME(approval_date) >= :startdate", array(':startdate'=>$startdate))
	            ->where("FROM_UNIXTIME(approval_date) <= :enddate", array(':enddate'=>$enddate))
	            ->countQuery()
	            ->execute()
	            ->fetchField();
	          $item = array(
	            'Circuit Simulation (eSim)',
	            '<a href="https://esim.fossee.in/circuit-simulation-project/circuit-simulation-progress" target="_blank">'.$progress.'</a>',
	            '<a href="https://esim.fossee.in/circuit-simulation-project/completed-circuit-simulations" target="_blank">'.$completed.'</a>'
	          );
	          array_push($rows, $item);
	        }
	    }
	    Database::setActiveConnection('default');
	    $page_content .= Utility::bootstrap_table_format($headers, $rows);
	    return $page_content;
	}


	//Self Workshop from spoken db
	public function getselfworkshoplcount($foss_name, $startdate, $enddate, $state, $city) {

	  if ($foss_name=="0"||$foss_name=="") {
	  $foss_name="%";
	  }
	  else{
	  $foss_name=$foss_name;
	  }

	  if ($city == "" || $city == "null") {
	        $city = "%";
	    }
	    else {
	        $city = $city;
	    }

	    if ($state == "" || $state == "null") {
	        $state= "%";
	    }
	    else {
	        $state = $state;
	    }
	  if ($startdate == "") {
	        $startdate = '1960-01-01';
	    }
	    else {
	        $startdate = $startdate;
	    }

	    if ($enddate == "") {
	        $enddate = date("Y-m-d");
	        //$enddate = "";
	    }
	    else {
	        $enddate = $enddate;
	    }
	    $rows = array();
	    $page_content = "";
	    $headers = array(
	        "FOSS Name",
	        "No. of Workshops Conducted",

	    );
	    $query = Database::getConnection()->select('foss_type');
	    $query->fields('foss_type', array(
	        'id'
	    ));
	    $query->fields('foss_type', array(
	        'foss_name'
	    ));
	    $query->fields('foss_type', array(
	        'foss_selfworkshop_no'
	    ));
	    $query->condition('foss_name', $foss_name, 'LIKE');
	    $query->condition('foss_selfworkshop_no', "null", '!=');
	    $result = $query->execute();
	    if ($result!=NULL) {
	  $page_content = "";

	if ($city == "%"&&$state!="%") {
	$city="NONE";
	while ($foss_detail = $result->fetchObject()) {

	      Database::setActiveConnection('selfworkshop');
	      $query2 = db_query("SELECT count(t.id) as count FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) and s.name LIKE :state AND t.tdate >= :startdate AND t.tdate <= :enddate ", array(
	                                ':foss_id' => $foss_detail->foss_selfworkshop_no,
	        ':state' => $state,
	        ':startdate' => $startdate,
	        ':enddate'  => $enddate,
	                            ));

	      $count = $query2->fetchObject()->count;

	      Database::setActiveConnection('default'); // We need to call the main (drupal); db back
	            Database::setActiveConnection();
	        $item = array(
	            $foss_detail->foss_name,
	            "<a href=" . $GLOBALS['base_url'] . "/completed-workshops-list/" . $foss_detail->foss_selfworkshop_no . "/" . $city . "/" . $state . "/" . $startdate . "/" . $enddate . " target='_blank' title='Click to view workshop list'>" . $count . "</a>"
	          );

	        array_push($rows, $item);
	    }


	}
	elseif ($state == "%"&&$city!="%") {
	$state="NONE";
	while ($foss_detail = $result->fetchObject()) {

	      Database::setActiveConnection('selfworkshop');
	      $query2 = db_query("SELECT count(t.id) as count FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) and c.name LIKE :city  AND t.tdate >= :startdate AND t.tdate <= :enddate ", array(
	                                ':foss_id' => $foss_detail->foss_selfworkshop_no,
	        ':city' => $city,

	        ':startdate' => $startdate,
	        ':enddate' => $enddate,
	                            ));

	      $count = $query2->fetchObject()->count;

	      Database::setActiveConnection('default'); // We need to call the main (drupal); db back
	            Database::setActiveConnection();
	        $item = array(
	            $foss_detail->foss_name,
	            "<a href=" . $GLOBALS['base_url'] . "/completed-workshops-list/" . $foss_detail->foss_selfworkshop_no . "/" . $city . "/" . $state . "/" . $startdate . "/" . $enddate . " target='_blank' title='Click to view workshop list'>" . $count . "</a>"
	          );

	        array_push($rows, $item);
	    }

	}
	elseif ($city == "%"&&$state == "%")  {
	$city="NONE";
	$state="NONE";
	while ($foss_detail = $result->fetchObject()) {

	      Database::setActiveConnection('selfworkshop');
	      $query2 = db_query("SELECT count(t.id) as count FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) AND t.tdate >= :startdate AND t.tdate <= :enddate ", array(
	                                ':foss_id' => $foss_detail->foss_selfworkshop_no,

	        ':startdate' => $startdate,
	        ':enddate' => $enddate,
	                            ));

	      $count = $query2->fetchObject()->count;

	      Database::setActiveConnection('default'); // We need to call the main (drupal); db back
	            Database::setActiveConnection();
	        $item = array(
	            $foss_detail->foss_name,
	            "<a href=" . $GLOBALS['base_url'] . "/completed-workshops-list/" . $foss_detail->foss_selfworkshop_no . "/" . $city . "/" . $state . "/" . $startdate . "/" . $enddate . " target='_blank' title='Click to view workshop list'>" . $count . "</a>"
	          );

	        array_push($rows, $item);
	    }

	}
	else{

	while ($foss_detail = $result->fetchObject()) {

	      Database::setActiveConnection('selfworkshop');
	      $query2 = db_query("SELECT count(t.id) as count FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) and c.name LIKE :city and s.name LIKE :state AND t.tdate >= :startdate AND t.tdate <= :enddate ", array(
	                                ':foss_id' => $foss_detail->foss_selfworkshop_no,
	        ':city' => $city,
	        ':state' => $state,
	        ':startdate' => $startdate,
	        ':enddate' => $enddate,
	                            ));

	      $count = $query2->fetchObject()->count;

	      Database::setActiveConnection('default'); // We need to call the main (drupal); db back
	            Database::setActiveConnection();
	        $item = array(
	            $foss_detail->foss_name,
	            "<a href=" . $GLOBALS['base_url'] . "/completed-workshops-list/" . $foss_detail->foss_selfworkshop_no . "/" . $city . "/" . $state . "/" . $startdate . "/" . $enddate . " target='_blank' title='Click to view workshop list'>" . $count . "</a>"
	          );

	        array_push($rows, $item);
	    }

	}

	    $page_content .= bootstrap_table_format($headers, $rows);


	   }
	   else{
	  $page_content = "No Record Found";
	  }
	  return $page_content;

	}

	//this is ajax callback method for second dropdown ie Activities type
	public function ajax_foss_sub_project_dependent_dropdown_callback(array $form, FormStateInterface $form_state)  {
	  $foss_project = $form_state->getValue('foss_sub_project');
	      $foss_type = $form_state->getValue('foss_type');


	      $response = new AjaxResponse();
	    if ($foss_type != "" && $foss_project != 0 && $foss_type != 3) {


	  if (in_array($foss_project, [6,7,8])) {
		  $form['load_city']['#markup'] = "";
		        $response->addCommand(new HtmlCommand("#load_city", ""));
		  $form['load_country']['#markup'] = "";
		        $response->addCommand(new HtmlCommand("#load_country", ""));
		  $form['load_state']['#markup'] = "";
		  		$response->addCommand(new HtmlCommand("#load_state", ""));

		  	if($foss_project == 6){      
				  $form['end_date']['#markup'] = "";
				        $response->addCommand(new HtmlCommand("#enddate", ""));
				  $form['start_date']['#markup'] = "";
				  		 $response->addCommand(new HtmlCommand("#startdate", ""));
			}
	  }

	  else{

	        $form['countryname']['#type'] = "select";
	        $form['countryname']['#title'] = t("Select Country");
	        $form['countryname']['#option'] = $this->get_country_list($foss_type, $foss_project);
	        $form['countryname']['#prefix'] = "<div id='load_country'>";
	        $form['countryname']['#suffix'] = "</div>";
	        $response->addCommand(new ReplaceCommand("#load_country", $form['countryname']));

	        $country_value = $form_state->getValue('countryname');

	        $form['statename']['#type'] = "select";
	        $form['statename']['#title'] = t("State Name");
	        $form['statename']['#options'] = $this->get_state_list($foss_type, $foss_project, $country_value);
	        $form['countryname']['#prefix'] = "<div id='load_state'>";
	        $form['countryname']['#suffix'] = "</div>";
	        $response->addCommand(new ReplaceCommand("#load_state", $form['statename']));

	        $state_value = $form_state->getValue('statename');

	        $form['statename']['#type'] = "select";
	        $form['statename']['#title'] = t("City Name");
	        $form['statename']['#options'] = $this->get_city_list($foss_type, $foss_project, $country_value, $state_value);
	        $form['countryname']['#prefix'] = "<div id='load_city'>";
	        $form['countryname']['#suffix'] = "</div>";
	        $response->addCommand(new ReplaceCommand("#load_city", $form['cityname']));
	  }

	    }
	    else {
	   if ($foss_project == 3 || $foss_project == 4 || $foss_project == 5) {

	  		$form['start_date']['#type'] = "date";
	        $form['start_date']['#title'] = t('From Date:');
	        $form['start_date']['#default_value'] = new DrupalDateTime('');
	        $form['start_date']['#date_year_range'] = '2011:+0';
	        $response->addCommand(new HtmlCommand("#startdate", \Drupal::service('renderer')->render($form['start_date'])));

	        $form['end_date']['#type'] = "date_popup";
	        $form['end_date']['#title'] = t('To Date:');
	        $form['end_date']['#default_value'] = new DrupalDateTime('');
	        $form['end_date']['#date_year_range'] = '2011:+0';

	        $response->addCommand(new HtmlCommand("#enddate", \Drupal::service('renderer')->render($form['end_date'])));

			$form['countryname']['#type'] = "select";
	        $form['countryname']['#title'] = t("Select Country");
	        $form['countryname']['#option'] = $this->get_country_list($foss_type, $foss_project);
	        $form['countryname']['#prefix'] = "<div id='load_country'>";
	        $form['countryname']['#suffix'] = "</div>";
	        $response->addCommand(new ReplaceCommand("#load_country", $form['countryname']));

	        $country_value = $form_state->getValue('countryname');

			$form['statename']['#type'] = "select";
	        $form['statename']['#title'] = t("State Name");
	        $form['statename']['#options'] = $this->get_state_list($foss_type, $foss_project, $country_value);
	        $form['countryname']['#prefix'] = "<div id='load_state'>";
	        $form['countryname']['#suffix'] = "</div>";
	        $response->addCommand(new ReplaceCommand("#load_state", $form['statename']));

	        $state_value = $form_state->getValue('statename');

	        $form['statename']['#type'] = "select";
	        $form['statename']['#title'] = t("City Name");
	        $form['statename']['#options'] = $this->get_city_list($foss_type, $foss_project, $country_value, $state_value);
	        $form['countryname']['#prefix'] = "<div id='load_city'>";
	        $form['countryname']['#suffix'] = "</div>";
	        $response->addCommand(new ReplaceCommand("#load_city", $form['cityname']));

	  }
	  elseif ($foss_project == 7) {
	  		$form['start_date']['#type'] = "date";
	        $form['start_date']['#title'] = t('From Date:');
	        $form['start_date']['#default_value'] = new DrupalDateTime('');
	        $form['start_date']['#date_year_range'] = '2011:+0';
	        $response->addCommand(new HtmlCommand("#startdate", \Drupal::service('renderer')->render($form['start_date'])));

	        $form['end_date']['#type'] = "date";
	        $form['end_date']['#title'] = t('To Date:');
	        $form['end_date']['#default_value'] = new DrupalDateTime('');
	        $form['end_date']['#date_year_range'] = '2011:+0';
	  		$response->addCommand(new HtmlCommand("#enddate", \Drupal::service('renderer')->render($form['end_date'])));

	  $form['load_city']['#markup'] = "";
	  		$response->addCommand(new HtmlCommand("#load_city", ""));
	  $form['load_country']['#markup'] = "";
	  		$response->addCommand(new HtmlCommand("#load_country", ""));
	  $form['load_state']['#markup'] = "";
	        $response->addCommand(new HtmlCommand("#load_state", ""));
	  }
	  elseif ($foss_project == 6) {
	  $form['load_city']['#markup'] = "";
	  		$response->addCommand(new HtmlCommand("#load_city", ""));
	  $form['load_country']['#markup'] = "";
	  		$response->addCommand(new HtmlCommand("#load_country", ""));
	  $form['load_state']['#markup'] = "";
	        $response->addCommand(new HtmlCommand("#load_state", ""));
	  $form['end_date']['#markup'] = "";
	         $response->addCommand(new HtmlCommand("#enddate", ""));
	  $form['start_date']['#markup'] = "";
	         $response->addCommand(new HtmlCommand("#startdate", ""));
	  }
	    }
	    if ($foss_project == 1 || $foss_project == 2 || $foss_project == 7 || $foss_project == 8) {
	        $form['foss_sub_project_status']['#type'] = "select";
	        $form['foss_sub_project_status']['#title'] = t("Status");
	        $form['foss_sub_project_status']['#options'] = $this->_ajax_example_get_third_dropdown_options($foss_project);

	        $response->addCommand(new HtmlCommand("#dropdown-third-replace", \Drupal::service('renderer')->render($form['foss_sub_project_status'])));
	    }
	    else {
	        $form['dropdown-third-replace']['#markup'] = "";
	        $response->addCommand(new HtmlCommand("#dropdown-third-replace", ''));
	    }
	    return $response;
	}

	public function get_data_for_chart_allproject($sub_type) {
	    $rows = array();
	    array_push($rows, ['Entity','count']);
	    if ($sub_type == "TBC") {
	        $query = Database::getConnection()->select('foss_type');
	        $query->fields('foss_type', array(
	            'id'
	        ));
	        $query->fields('foss_type', array(
	            'foss_name'
	        ));
	        $query->fields('foss_type', array(
	            'tbc'
	        ));
	        $query->condition('tbc', 1);
	        $result = $query->execute();
	        while ($foss_detail = $result->fetchObject()) {
	            if ($foss_detail->foss_name != NULL) {
	                if ($foss_detail->foss_name != 'Python') {
	                    Database::setActiveConnection($foss_detail->foss_name); //Active other database
	                    //For TBC
	                    if ($foss_detail->foss_name != 'eSim' && $foss_detail->foss_name != 'OpenModelica' && $foss_detail->foss_name != 'OpenFOAM' && $foss_detail->foss_name != 'OR-Tools') {
	                        /*$query2 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND pe.category>0");*/
	                        $query2 = Database::getConnection()->select('textbook_companion_preference', 'pe');
	                        $query2->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                        $query2->addField('pe','book','book_count');
	                        $result = $query2->condition('po.proposal_status',3)->condition('pe.approval_status',1)->condition('pe.category',0,'>')->countQuery()->execute();
	                    }
	                    else {

	                        /*$query2 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1");*/
	                        $result = Database::getConnection()->select('textbook_companion_preference', 'pe')->addField('pe','book','book_count')->countQuery()->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id')->condition('po.proposal_status',3)->condition('pe.approval_status',1)->execute();
	                    }
	                    Database::setActiveConnection('default'); // We need to call the main (drupal); db back
	                    Database::setActiveConnection();
	                    $completedbookcount = $result->fetchField();
	                    $option1 = array(
	                        $foss_detail->foss_name,
	                        (int) $completedbookcount
	                    );
	                    array_push($rows, $option1);
	                }
	                else {
	                    Database::setActiveConnection($foss_detail->foss_name); //Active other database
	                    $query2 = Database::getConnection()->select('tbc_book');
	                    $query2->addExpression('count(*)', 'count');
	                    $query2->condition('approved', 1);
	                    $result2 = $query2->execute();
	                    Database::setActiveConnection('default'); // We need to call the main (drupal); db back
	                    Database::setActiveConnection();
	                    $completedbookcount = $query2->countQuery()->execute()->fetchField();
	                    $option1 = array(
	                        $foss_detail->foss_name,
	                        (int) $completedbookcount
	                    );
	                    array_push($rows, $option1);
	                }
	            }
	        }
	    }
	    elseif ($sub_type == "LM") {
	        $query = Database::getConnection()->select('foss_type');
	        $query->fields('foss_type', array(
	            'id'
	        ));
	        $query->fields('foss_type', array(
	            'foss_name'
	        ));
	        $query->fields('foss_type', array(
	            'lab_migration'
	        ));
	        $query->condition('lab_migration', 1);
	        $result = $query->execute();
	        while ($foss_detail = $result->fetchObject()) {
	            if ($foss_detail->foss_name != NULL) {
	                Database::setActiveConnection($foss_detail->foss_name); //Active other database
	                //For LM
	                $query3 = Database::getConnection()->select('lab_migration_proposal');
	                $query3->addExpression('count(*)', 'count');
	                $query3->condition('approval_status', 3);
	                $result3 = $query3->execute();
	                Database::setActiveConnection('default'); // We need to call the main (drupal); db back
	                Database::setActiveConnection();
	                $completedlabcount = $query3->countQuery()->execute()->fetchField();
	                $option1 = array(
	                    $foss_detail->foss_name,
	                    (int) $completedlabcount
	                );
	                array_push($rows, $option1);
	            }
	        }
	    }elseif ($sub_type == "Other") {
	      Database::setActiveConnection('DWSIM');
	      $completed = Database::getConnection()->select('dwsim_flowsheet_proposal')
	        ->condition('approval_status',3)
	        ->countQuery()
	        ->execute()
	        ->fetchField();
	      $progress = Database::getConnection()->select('dwsim_flowsheet_proposal')
	        ->condition('approval_status',1)
	        ->countQuery()
	        ->execute()
	        ->fetchField();
	      $option = array(
	        'Flowsheet (DWSIM)',
	        $completed + $progress
	      );
	      array_push($rows, $option);
	      Database::setActiveConnection('eSim');
	      $completed = Database::getConnection()->select('esim_circuit_simulation_proposal')
	        ->condition('approval_status',3)
	        ->countQuery()
	        ->execute()
	        ->fetchField();
	      $progress = Database::getConnection()->select('esim_circuit_simulation_proposal')
	        ->condition('approval_status',1)
	        ->countQuery()
	        ->execute()
	        ->fetchField();
	      $option = array(
	        'Circuit Simulation (eSim)',
	        $completed + $progress
	      );
	      array_push($rows, $option);
	      Database::setActiveConnection();
	    }
	    return $rows;
	}

	public function submitForm(array &$form, FormStateInterface $form_state){
	}

	public function events_view_filter($foss_name, $event_type, $startdate, $enddate,
    $countryname, $statename, $cityname) {

	    $page_content = "";
	    $headers = array(
	        "Name",
	        "Start Date",
	        "Venue",
	        "No of Participants",
	        ""
	    );
	    if ($startdate == "") {
	        $startdate = '1960-01-01';
	    }
	    else {
	        $startdate = $startdate;
	    }

	    if ($enddate == "") {
	        $enddate = date("Y-m-d");
	        //$enddate = "";
	    }
	    else {
	        $enddate = $enddate;
	    }

	    if (trim($countryname) == "0") {
	        $countryname = '%';
	    }
	    else {
	        $countryname = trim($countryname);
	    }

	    if (trim($statename) == "0") {
	        $statename = '%';
	    }
	    else {
	        $statename = trim($statename);
	    }

	    if (trim($cityname) == "0") {
	        $cityname = '%';
	    }
	    else {
	        $cityname = trim($cityname);
	    }


	    $rows = array();
	    $query = Database::getConnection()->select('workshop');
	    $query->fields('workshop');
	    $query->condition('type', $event_type);
	    $query->condition('foss_name', $foss_name);
	    $query->condition('country', $countryname, 'LIKE');
	    $query->condition('state', $statename, 'LIKE');
	    $query->condition('city', $cityname, 'LIKE');
	    $query->condition('startdate', $startdate, '>=');
	    $query->condition('startdate', $enddate, '<=');
	    $query->orderBy('startdate', 'DESC');
	    $result = $query->execute();

	    while ($row = $result->fetchObject()) {
	        $item = array(
	            $row->w_name,
	            $row->startdate,
	            $row->venue,
	            $row->no_of_participant,
	            "<a href=" . $GLOBALS['base_url'] . "/events/view_details/{$row->w_id} target='_blank' title='Click to view detail'>Details</a>"
	        );
	        array_push($rows, $item);
	    }
	    $page_content .= Utility::bootstrap_table_format($headers, $rows);

	    return $page_content;
	}

	public function get_tabledata_selectedFoss_TBC_LM($foss_type, $sub_type, $status, $startdate, $enddate, $countryname, $statename, $cityname, $link_flag) {

	    //This is for sending values through URL s '%' is not allow through url

	    if ($cityname == "%") {
	        $city = "null";
	    }
	    else {
	        $city = $cityname;
	    }
	    if ($statename == "%") {
	        $state = "null";
	    }
	    else {
	        $state = $statename;
	    }
	    if ($countryname == "%") {
	        $country = "null";
	    }
	    else {
	        $country = $countryname;
	    }

	    if ($status == "all") {
	        $rows = array();
	        if ($sub_type == 'TBC') {
	            $headers = array(
	                "Completed Book",
	                "Book In Progress"
	            );
	            $query = Database::getConnection()->select('foss_type');
	            $query->fields('foss_type', array(
	                'id'
	            ));
	            $query->fields('foss_type', array(
	                'foss_name'
	            ));
	            $query->fields('foss_type', array(
	                'tbc'
	            ));
	            $query->fields('foss_type', array(
	                'tbc_completed'
	            ));
	            $query->fields('foss_type', array(
	                'tbc_pending'
	            ));
	            $query->condition('foss_name', $foss_type);
	            $query->condition('tbc', 1);
	            $result = $query->execute();
	            $foss_detail = $result->fetchObject();
	            $foss_type = $foss_detail->foss_name;
	            Database::setActiveConnection($foss_type);
	            if ($foss_detail->foss_name != 'Python') {
	                Database::setActiveConnection($foss_detail->foss_name); //Active other database
	                if ($foss_detail->foss_name != 'eSim' && $foss_detail->foss_name != 'OpenModelica' && $foss_detail->foss_name != 'OpenFOAM' && $foss_detail->foss_name != 'OR-Tools') {
	                    if ($foss_detail->foss_name != 'DWSIM') {

	                    	$query2 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    	$query2->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    	$result2 = $query2->condition('po.proposal_status',3)->condition('pe.approval_status',1)->condition('pe.category',0,'>')->condition('po.city',$cityname,'LIKE')->condition('po.state',$statename,'LIKE')->condition('po.country',$countryname,'LIKE')->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :enddate',array(':enddate'=>$enddate))->countQuery()->execute();

	                        /*$query2 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND pe.category>0 AND po.city LIKE :city AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
	                            ':city' => $cityname,
	                            ':state' => $statename,
	                            ':country' => $countryname,
	                            ':startdate' => $startdate,
	                            ':enddate' => $enddate
	                        ));*/
	                    }
	                    else {
	                    	$query2 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    	$query2->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    	$result2 = $query2->condition('po.proposal_status',3)->condition('pe.approval_status',1)->condition('pe.category',0,'>')->condition('po.city',$cityname,'LIKE')->condition('po.state',$statename,'LIKE')->condition('po.country',$countryname,'LIKE')->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :enddate',array(':enddate'=>$enddate))->countQuery()->execute();

	                        /*$query2 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND pe.category>0 AND po.city LIKE :city AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
	                            ':city' => $cityname,
	                            ':state' => $statename,
	                            ':country' => $countryname,
	                            ':startdate' => $startdate,
	                            ':enddate' => $enddate
	                        ));*/
	                    }
	                }
	                else {
	                	$query2 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    	$query2->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    	$result2 = $query2->condition('po.proposal_status',3)->condition('pe.approval_status',1)->condition('po.city',$cityname,'LIKE')->condition('po.state',$statename,'LIKE')->condition('po.country',$countryname,'LIKE')->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :enddate',array(':enddate'=>$enddate))->countQuery()->execute();

	                   /* $query2 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND po.city LIKE :city AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
	                        ':city' => $cityname,
	                        ':state' => $statename,
	                        ':country' => $countryname,
	                        ':startdate' => $startdate,
	                        ':enddate' => $enddate
	                    ));*/
	                }
	                $completedbookcount = $result2->fetchField();
	                if ($foss_detail->tbc_completed != "" && $foss_detail->tbc_completed != NULL && $link_flag != 1) {

	                    //$clink = "<a href=" . $foss_detail->tbc_completed . " target='_blank'>" . $completedbookcount . "</a>";
	                    if ($completedbookcount != 0 || $completedbookcount != "0") {
	                        $clink = "<a href=" . $GLOBALS['base_url'] . "/get-list/" . $foss_type . "/" . $sub_type . "/" . "completed" . "/" . $startdate . "/" . $enddate . "/" . $country . "/" . $state . "/" . $city . " target='_blank'>" . $completedbookcount . "</a>";
	                        $completedbookcount = $clink;
	                    }
	                    else {
	                        $completedbookcount = $completedbookcount;
	                    }

	                }

	                /* For setting completion date for pending TBC and LM more */
	                        $pending_enddate = date('Y-m-d', strtotime("+5 months", strtotime( $enddate)));


	                if ($foss_detail->foss_name != 'eSim'  && $foss_detail->foss_name != 'OpenModelica' && $foss_detail->foss_name != 'OpenFOAM' && $foss_detail->foss_name != 'OR-Tools') {
	                    if ($foss_detail->foss_name != 'DWSIM') {
	                       /* $query3 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND pe.category>0 AND po.city LIKE :city AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate ", array(
	                            ':city' => $cityname,
	                            ':state' => $statename,
	                            ':country' => $countryname,
	                            ':startdate' => $startdate,
	                            ':enddate' => $pending_enddate
	                        ));*/
	                        $query3 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    	$query3->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    	$result3 = $query2->condition('po.proposal_status',3,'<>')->condition('pe.approval_status',1)->condition('po.category',0,'>')->condition('po.city',$cityname,'LIKE')->condition('po.state',$statename,'LIKE')->condition('po.country',$countryname,'LIKE')->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :enddate',array(':enddate'=>$pending_enddate))->countQuery()->execute();
	                    }
	                    else {
	                        /*$query3 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND pe.category>0 AND po.city LIKE :city AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
	                            ':city' => $cityname,
	                            ':state' => $statename,
	                            ':country' => $countryname,
	                            ':startdate' => $startdate,
	                            ':enddate' => $pending_enddate
	                        ));*/
	                        $query3 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    	$query3->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    	$result3 = $query2->condition('po.proposal_status',3,'<>')->condition('pe.approval_status',1)->condition('po.category',0,'>')->condition('po.city',$cityname,'LIKE')->condition('po.state',$statename,'LIKE')->condition('po.country',$countryname,'LIKE')->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :enddate',array(':enddate'=>$pending_enddate))->countQuery()->execute();
	                    }
	                }
	                else {
	                   /* $query3 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.city LIKE :city   AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
	                        ':city' => $cityname,
	                        ':state' => $statename,
	                        ':country' => $countryname,
	                        ':startdate' => $startdate,
	                        ':enddate' => $pending_enddate
	                    ));*/
	                    $query3 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    	$query3->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    	$result3 = $query2->condition('po.proposal_status',3,'<>')->condition('pe.approval_status',1)->condition('po.city',$cityname,'LIKE')->condition('po.state',$statename,'LIKE')->condition('po.country',$countryname,'LIKE')->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :enddate',array(':enddate'=>$pending_enddate))->countQuery()->execute();
	                }
	                $pendingbookcount = $result3->fetchField();
	                if ($foss_detail->tbc_pending != "" && $foss_detail->tbc_pending != NULL && $link_flag != 1) {
	                    //$plink = "<a href=" . $foss_detail->tbc_pending . " target='_blank'>" . $pendingbookcount . "</a>";
	                    if ($pendingbookcount != 0 || $pendingbookcount != "0") {
	                        $plink = "<a href=" . $GLOBALS['base_url'] . "/get-list/" . $foss_type . "/" . $sub_type . "/" . "pending" . "/" . $startdate . "/" . $enddate . "/" . $country . "/" . $state . "/" . $city . " target='_blank'>" . $pendingbookcount . "</a>";
	                        $pendingbookcount = $plink;
	                    }
	                    else {
	                        $pendingbookcount = $pendingbookcount;
	                    }

	                }
	                //Database::setActiveConnection('default'); // We need to call the main (drupal) db back
	                //Database::setActiveConnection(); // without the paramater means set back to the default for the site
	                $item = array(
	                    $completedbookcount,
	                    $pendingbookcount
	                );
	                array_push($rows, $item);
	            }
	            else {
	                //For Python TBC
	                Database::setActiveConnection($foss_detail->foss_name); //Active other database
	                $query5 = Database::getConnection()->select('tbc_book');
	                $query5->addExpression('count(*)', 'count');
	                $query5->condition('approved', 1);
	                //$query5->condition('author', '%'.$extrafilter.'%', 'LIKE');
	                $result5 = $query5->execute();
	                $completedbookcount = $result5->fetchObject()->count;
	                if ($foss_detail->tbc_completed != "" && $foss_detail->tbc_completed != NULL && $link_flag != 1) {
	                    $clink = "<a href=" . $foss_detail->tbc_completed . " target='_blank'>" . $completedbookcount . "</a>";
	                    $completedbookcount = $clink;
	                }
	                $query6 = Database::getConnection()->select('tbc_book');
	                $query6->addExpression('count(*)', 'count');
	                $query6->condition('approved', 1, '<>');
	                //$query6->condition('author', '%'.$extrafilter.'%', 'LIKE');
	                $result6 = $query6->execute();
	                $pendingbookcount = $result6->fetchObject()->count;
	                if ($foss_detail->tbc_pending != "" && $foss_detail->tbc_pending != NULL && $link_flag != 1) {
	                    $plink = "<a href=" . $foss_detail->tbc_pending . " target='_blank'>" . $pendingbookcount . "</a>";
	                    $pendingbookcount = $plink;
	                }
	                //Database::setActiveConnection('default'); // We need to call the main (drupal) db back
	                //Database::setActiveConnection(); // without the paramater means set back to the default for the site
	                $item = array(
	                    $completedbookcount,
	                    $pendingbookcount
	                );
	                array_push($rows, $item);
	            }
	        }
	        else {
	            $headers = array(
	                "Completed Labs",
	                "Labs In Progress"
	            );
	            $query = Database::getConnection()->select('foss_type');
	            $query->fields('foss_type', array(
	                'id'
	            ));
	            $query->fields('foss_type', array(
	                'foss_name'
	            ));
	            $query->fields('foss_type', array(
	                'lab_migration'
	            ));
	            $query->fields('foss_type', array(
	                'lm_completed'
	            ));
	            $query->fields('foss_type', array(
	                'lm_pending'
	            ));
	            $query->condition('foss_name', $foss_type);
	            $query->condition('lab_migration', 1);
	            $result = $query->execute();
	            $foss_detail = $result->fetchObject();
	            Database::setActiveConnection($foss_detail->foss_name); //Active other database
	            /*$query2 = db_query("SELECT COUNT(*) AS count from {lab_migration_proposal} WHERE approval_status=3 AND city LIKE :city AND state LIKE :state AND country LIKE :country AND FROM_UNIXTIME(approval_date) >=:startdate AND FROM_UNIXTIME(approval_date) <=:enddate", array(
	                ':city' => $cityname,
	                ':state' => $statename,
	                ':country' => $countryname,
	                ':startdate' => $startdate,
	                ':enddate' => $enddate
	            ));*/
	            $result2 = Database::getConnection()->select('lab_migration_proposal')->fields('lab_migration_proposal')->condition('approval_status',3)->condition('city',$cityname,'LIKE')->condition('state',$statename,'LIKE')->condition('country',$countryname,'LIKE')->where('FROM_UNIXTIME(approval_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(appproval_date) <= :enddate',array(':enddate'=>$enddate))->countQuery()->execute();

	            $completedlabcount = $result2->fetchField();
	            if ($foss_detail->lm_completed != "" && $foss_detail->lm_completed != NULL && $link_flag != 1) {
	                //$clink = "<a href=" . $foss_detail->lm_completed . " target='_blank'>" . $completedlabcount . "</a>";
	                if ($completedlabcount != 0 || $completedlabcount != "0") {
	                    $clink = "<a href=" . $GLOBALS['base_url'] . "/get-list/" . $foss_type . "/" . $sub_type . "/" . "completed" . "/" . $startdate . "/" . $enddate . "/" . $country . "/" . $state . "/" . $city . " target='_blank'>" . $completedlabcount . "</a>";
	                    $completedlabcount = $clink;
	                }
	                else {
	                    $completedlabcount = $completedlabcount;
	                }
	            }
	            /*$query3 = db_query("SELECT COUNT(*) AS count from {lab_migration_proposal} WHERE approval_status=1 AND city LIKE :city AND state LIKE :state AND country LIKE :country AND FROM_UNIXTIME(approval_date) >=:startdate AND FROM_UNIXTIME(approval_date) <=:enddate", array(
	                ':city' => $cityname,
	                ':state' => $statename,
	                ':country' => $countryname,
	                ':startdate' => $startdate,
	                ':enddate' => $enddate
	            ));*/
	            $result3 = Database::getConnection()->select('lab_migration_proposal')->fields('lab_migration_proposal')->condition('approval_status',1)->condition('city',$cityname,'LIKE')->condition('state',$statename,'LIKE')->condition('country',$countryname,'LIKE')->where('FROM_UNIXTIME(approval_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(appproval_date) <= :enddate',array(':enddate'=>$enddate))->countQuery()->execute();

	            $pendinglabcount = $result3->fetchField();
	            if ($foss_detail->lm_pending != "" && $foss_detail->lm_pending != NULL && $link_flag != 1) {
	                //$plink = "<a href=" . $foss_detail->lm_pending . " target='_blank'>" . $pendinglabcount . "</a>";
	                if ($pendinglabcount != 0 || $pendinglabcount != "0") {
	                    $plink = "<a href=" . $GLOBALS['base_url'] . "/get-list/" . $foss_type . "/" . $sub_type . "/" . "pending" . "/" . $startdate . "/" . $enddate . "/" . $country . "/" . $state . "/" . $city . " target='_blank'>" . $pendinglabcount . "</a>";
	                    $pendinglabcount = $plink;
	                }
	                else {
	                    $pendinglabcount = $pendinglabcount;
	                }
	            }
	            $item = array(
	                $completedlabcount,
	                $pendinglabcount
	            );
	            array_push($rows, $item);
	        }
	        Database::setActiveConnection('default'); // We need to call the main (drupal) db back
	        Database::setActiveConnection(); // without the paramater means set back to the default for the site
	        $count = Utility::bootstrap_table_format($headers, $rows);
	        return $count;
	    }
	    elseif ($status == "completed") {
	        $rows = array();
	        if ($sub_type == 'TBC') {
	            $headers = array(
	                "Completed Book"
	            );
	            $query = Database::getConnection()->select('foss_type');
	            $query->fields('foss_type', array(
	                'id'
	            ));
	            $query->fields('foss_type', array(
	                'foss_name'
	            ));
	            $query->fields('foss_type', array(
	                'tbc'
	            ));
	            $query->fields('foss_type', array(
	                'tbc_completed'
	            ));
	            $query->condition('foss_name', $foss_type);
	            $query->condition('tbc', 1);
	            $result = $query->execute();
	            $foss_detail = $result->fetchObject();
	            $foss_type = $foss_detail->foss_name;
	            Database::setActiveConnection($foss_type);
	            if ($foss_detail->foss_name != 'Python') {
	                Database::setActiveConnection($foss_detail->foss_name); //Active other database
	                if ($foss_detail->foss_name != 'eSim'  && $foss_detail->foss_name != 'OpenModelica' && $foss_detail->foss_name != 'OpenFOAM' && $foss_detail->foss_name != 'OR-Tools') {
	                    if ($foss_detail->foss_name != 'DWSIM') {
	                        /*$query2 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND pe.category>0 AND po.city LIKE :city AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
	                            ':city' => $cityname,
	                            ':state' => $statename,
	                            ':country' => $countryname,
	                            ':startdate' => $startdate,
	                            ':enddate' => $enddate
	                        ));*/
	                        $query2 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    	$query2->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    	$result2 = $query2->condition('po.proposal_status',3)->condition('pe.approval_status',1)->condition('pe.category',0,'>')->condition('po.city',$cityname,'LIKE')->condition('po.state',$statename,'LIKE')->condition('po.country',$countryname,'LIKE')->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :enddate',array(':enddate'=>$enddate))->countQuery()->execute();
	                    }
	                    else {
	                        /*$query2 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND pe.category>0 AND po.city LIKE :city AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate ", array(
	                            ':city' => $cityname,
	                            ':state' => $statename,
	                            ':country' => $countryname,
	                            ':startdate' => $startdate,
	                            ':enddate' => $enddate
	                        ));*/
	                        $query2 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    	$query2->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    	$result2 = $query2->condition('po.proposal_status',3)->condition('pe.approval_status',1)->condition('pe.category',0,'>')->condition('po.city',$cityname,'LIKE')->condition('po.state',$statename,'LIKE')->condition('po.country',$countryname,'LIKE')->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :enddate',array(':enddate'=>$enddate))->countQuery()->execute();
	                    }
	                }
	                else {
	                    /*$query2 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND po.city LIKE :city AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
	                        ':city' => $cityname,
	                        ':state' => $statename,
	                        ':country' => $countryname,
	                        ':startdate' => $startdate,
	                        ':enddate' => $enddate
	                    ));*/
	                    $query2 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    	$query2->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    	$result2 = $query2->condition('po.proposal_status',3)->condition('pe.approval_status',1)->condition('po.city',$cityname,'LIKE')->condition('po.state',$statename,'LIKE')->condition('po.country',$countryname,'LIKE')->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :enddate',array(':enddate'=>$enddate))->countQuery()->execute();
	                }
	                $completedbookcount = $result2->fetchField();
	                if ($foss_detail->tbc_completed != "" && $foss_detail->tbc_completed != NULL && $link_flag != 1) {
	                    //$clink = "<a href=" . $foss_detail->tbc_completed . " target='_blank'>" . $completedbookcount . "</a>";
	                    if ($completedbookcount != 0 || $completedbookcount != "0") {
	                        $clink = "<a href=" . $GLOBALS['base_url'] . "/get-list/" . $foss_type . "/" . $sub_type . "/" . "completed" . "/" . $startdate . "/" . $enddate . "/" . $country . "/" . $state . "/" . $city . " target='_blank'>" . $completedbookcount . "</a>";
	                        $completedbookcount = $clink;
	                    }
	                    else {
	                        $completedbookcount = $completedbookcount;
	                    }

	                }
	                $item = array(
	                    $completedbookcount
	                );
	                array_push($rows, $item);
	            }
	            else {
	                //For Python TBC
	                Database::setActiveConnection($foss_detail->foss_name); //Active other database
	                $query5 = Database::getConnection()->select('tbc_book');
	                $query5->addExpression('count(*)', 'count');
	                $query5->condition('approved', 1);
	                //$query5->condition('author', '%'.$extrafilter.'%', 'LIKE');
	                $result5 = $query5->execute();
	                $completedbookcount = $result5->fetchObject()->count;
	                if ($foss_detail->tbc_completed != "" && $foss_detail->tbc_completed != NULL && $link_flag != 1) {
	                    $clink = "<a href=" . $foss_detail->tbc_completed . " target='_blank'>" . $completedbookcount . "</a>";
	                    $completedbookcount = $clink;
	                }
	                $item = array(
	                    $completedbookcount
	                );
	                array_push($rows, $item);
	            }
	        }
	        else {
	            $headers = array(
	                "Completed Labs"
	            );
	            $query = Database::getConnection()->select('foss_type');
	            $query->fields('foss_type', array(
	                'id'
	            ));
	            $query->fields('foss_type', array(
	                'foss_name'
	            ));
	            $query->fields('foss_type', array(
	                'lab_migration'
	            ));
	            $query->fields('foss_type', array(
	                'lm_completed'
	            ));
	            $query->condition('foss_name', $foss_type);
	            $query->condition('lab_migration', 1);
	            $result = $query->execute();
	            $foss_detail = $result->fetchObject();
	            Database::setActiveConnection($foss_detail->foss_name); //Active other database
	            /*$query2 = db_query("SELECT COUNT(*) AS count from {lab_migration_proposal} WHERE approval_status=3 AND city       LIKE :city AND state LIKE :state AND country LIKE :country AND FROM_UNIXTIME(approval_date) >=:startdate AND FROM_UNIXTIME(approval_date) <=:enddate", array(
	                ':city' => $cityname,
	                ':state' => $statename,
	                ':country' => $countryname,
	                ':startdate' => $startdate,
	                ':enddate' => $enddate
	            ));*/
	            $result2 = Database::getConnection()->select('lab_migration_proposal')->fields('lab_migration_proposal')->condition('approval_status',3)->condition('city',$cityname,'LIKE')->condition('state',$statename,'LIKE')->condition('country',$countryname,'LIKE')->where('FROM_UNIXTIME(approval_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(appproval_date) <= :enddate',array(':enddate'=>$enddate))->countQuery()->execute();
	            $completedlabcount = $result2->fetchField();
	            if ($foss_detail->lm_completed != "" && $foss_detail->lm_completed != NULL && $link_flag != 1) {
	                //$clink = "<a href=" . $foss_detail->lm_completed . " target='_blank'>" . $completedlabcount . "</a>";
	                if ($completedlabcount != "0" || $completedlabcount != 0) {
	                    $clink = "<a href=" . $GLOBALS['base_url'] . "/get-list/" . $foss_type . "/" . $sub_type . "/" . "completed" . "/" . $startdate . "/" . $enddate . "/" . $country . "/" . $state . "/" . $city . " target='_blank'>" . $completedlabcount . "</a>";
	                    $completedlabcount = $clink;
	                }
	                else {
	                    $completedlabcount = $completedlabcount;
	                }

	            }
	            $item = array(
	                $completedlabcount
	            );
	            array_push($rows, $item);
	        }
	        Database::setActiveConnection('default'); // We need to call the main (drupal) db back
	        Database::setActiveConnection(); // without the paramater means set back to the default for the site
	        $count = Utility::bootstrap_table_format($headers, $rows);
	    }
	    elseif ($status == "pending") {
	        $rows = array();
	        if ($sub_type == 'TBC') {
	            $headers = array(
	                "Books in Progress"
	            );
	            $query = Database::getConnection()->select('foss_type');
	            $query->fields('foss_type', array(
	                'id'
	            ));
	            $query->fields('foss_type', array(
	                'foss_name'
	            ));
	            $query->fields('foss_type', array(
	                'tbc'
	            ));
	            $query->fields('foss_type', array(
	                'tbc_pending'
	            ));
	            $query->condition('foss_name', $foss_type);
	            $query->condition('tbc', 1);
	            $result = $query->execute();
	            $foss_detail = $result->fetchObject();
	            $foss_type = $foss_detail->foss_name;
	            Database::setActiveConnection($foss_type);
	            if ($foss_detail->foss_name != 'Python') {
	                Database::setActiveConnection($foss_detail->foss_name); //Active other database
	                if ($foss_detail->foss_name != 'eSim' && $foss_detail->foss_name != 'OpenModelica' && $foss_detail->foss_name != 'OpenFOAM' && $foss_detail->foss_name != 'OR-Tools') {
	                    if ($foss_detail->foss_name != 'DWSIM') {
	                        /*$query2 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND pe.category>0 AND po.city LIKE :city AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
	                            ':city' => $cityname,
	                            ':state' => $statename,
	                            ':country' => $countryname,
	                            ':startdate' => $startdate,
	                            ':enddate' => $enddate
	                        ));*/
	                        $query2 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    	$query2->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    	$result2 = $query2->condition('po.proposal_status',3,'<>')->condition('pe.approval_status',1)->condition('pe.category',0,'>')->condition('po.city',$cityname,'LIKE')->condition('po.state',$statename,'LIKE')->condition('po.country',$countryname,'LIKE')->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :enddate',array(':enddate'=>$enddate))->countQuery()->execute();
	                    }
	                    else {
	                        /*$query2 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND pe.category>0 AND po.city LIKE :city AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate ", array(
	                            ':city' => $cityname,
	                            ':state' => $statename,
	                            ':country' => $countryname,
	                            ':startdate' => $startdate,
	                            ':enddate' => $enddate
	                        ));*/
	                        $query2 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    	$query2->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    	$result2 = $query2->condition('po.proposal_status',3,'<>')->condition('pe.approval_status',1)->condition('pe.category',0,'>')->condition('po.city',$cityname,'LIKE')->condition('po.state',$statename,'LIKE')->condition('po.country',$countryname,'LIKE')->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :enddate',array(':enddate'=>$enddate))->countQuery()->execute();
	                    }
	                }
	                else {
	                    /*$query2 = db_query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.city LIKE :city  AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
	                        ':city' => $cityname,
	                        ':state' => $statename,
	                        ':country' => $countryname,
	                        ':startdate' => $startdate,
	                        ':enddate' => $enddate
	                    ));*/
	                    $query2 = Database::getConnection()->select('textbook_companion_preference', 'pe')->fields('pe',['book']);
	                    	$query2->addJoin('left','textbook_companion_proposal', 'po', 'pe.proposal_id = po.id');
	                    	$result2 = $query2->condition('po.proposal_status',3,'<>')->condition('pe.approval_status',1)->condition('po.city',$cityname,'LIKE')->condition('po.state',$statename,'LIKE')->condition('po.country',$countryname,'LIKE')->where('FROM_UNIXTIME(po.completion_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(po.completion_date) <= :enddate',array(':enddate'=>$enddate))->countQuery()->execute();
	                }
	                $pendingbookcount = $result2->fetchField();
	                if ($foss_detail->tbc_pending != "" && $foss_detail->tbc_pending != NULL && $link_flag != 1) {
	                    //$plink = "<a href=" . $foss_detail->tbc_pending . " target='_blank'>" . $pendingbookcount . "</a>";
	                    if ($pendingbookcount != "0" || $pendingbookcount != 0) {
	                        $plink = "<a href=" . $GLOBALS['base_url'] . "/get-list/" . $foss_type . "/" . $sub_type . "/" . "pending" . "/" . $startdate . "/" . $enddate . "/" . $country . "/" . $state . "/" . $city . " target='_blank'>" . $pendingbookcount . "</a>";
	                        $pendingbookcount = $plink;
	                    }
	                    else {
	                        $pendingbookcount = $pendingbookcount;
	                    }
	                }
	                $item = array(
	                    $pendingbookcount
	                );
	                array_push($rows, $item);
	            }
	            else {
	                //For Python TBC
	                Database::setActiveConnection($foss_detail->foss_name); //Active other database
	                $query5 = Database::getConnection()->select('tbc_book');
	                $query5->addExpression('count(*)', 'count');
	                $query5->condition('approved', 1, '<>');
	                //$query5->condition('author', '%'.$extrafilter.'%', 'LIKE');
	                $result5 = $query5->execute();
	                $pendingbookcount = $result5->fetchObject()->count;
	                if ($foss_detail->tbc_pending != "" && $foss_detail->tbc_pending != NULL && $link_flag != 1) {
	                    $plink = "<a href=" . $foss_detail->tbc_pending . " target='_blank'>" . $pendingbookcount . "</a>";
	                    $pendingbookcount = $plink;
	                }
	                $item = array(
	                    $pendingbookcount
	                );
	                array_push($rows, $item);
	            }
	        }
	        else {
	            $headers = array(
	                "Labs in Progress"
	            );
	            $query = Database::getConnection()->select('foss_type');
	            $query->fields('foss_type', array(
	                'id'
	            ));
	            $query->fields('foss_type', array(
	                'foss_name'
	            ));
	            $query->fields('foss_type', array(
	                'lab_migration'
	            ));
	            $query->fields('foss_type', array(
	                'lm_pending'
	            ));
	            $query->condition('foss_name', $foss_type);
	            $query->condition('lab_migration', 1);
	            $result = $query->execute();
	            $foss_detail = $result->fetchObject();
	            Database::setActiveConnection($foss_detail->foss_name); //Active other database
	            /*$query2 = db_query("SELECT COUNT(*) AS count from {lab_migration_proposal} WHERE approval_status=1 AND city       LIKE :city AND state LIKE :state AND country LIKE :country AND FROM_UNIXTIME(approval_date) >=:startdate AND FROM_UNIXTIME(approval_date) <=:enddate", array(
	                ':city' => $cityname,
	                ':state' => $statename,
	                ':country' => $countryname,
	                ':startdate' => $startdate,
	                ':enddate' => $enddate
	            ));*/
	            $result2 = Database::getConnection()->select('lab_migration_proposal')->fields('lab_migration_proposal')->condition('approval_status',1)->condition('city',$cityname,'LIKE')->condition('state',$statename,'LIKE')->condition('country',$countryname,'LIKE')->where('FROM_UNIXTIME(approval_date) >= :startdate',array(':startdate'=>$startdate))->where('FROM_UNIXTIME(appproval_date) <= :enddate',array(':enddate'=>$enddate))->countQuery()->execute();


	            $pendinglabcount = $result2->fetchField();
	            if ($foss_detail->lm_pending != "" && $foss_detail->lm_pending != NULL && $link_flag != 1) {
	                //$plink = "<a href=" . $foss_detail->lm_pending . " target='_blank'>" . $pendinglabcount . "</a>";
	                if ($pendinglabcount != "0" || $pendinglabcount != 0) {
	                    $plink = "<a href=" . $GLOBALS['base_url'] . "/get-list/" . $foss_type . "/" . $sub_type . "/" . "pending" . "/" . $startdate . "/" . $enddate . "/" . $country . "/" . $state . "/" . $city . " target='_blank'>" . $pendinglabcount . "</a>";
	                    $pendinglabcount = $plink;
	                }
	                else {
	                    $pendinglabcount = $pendinglabcount;
	                }

	            }
	            $item = array(
	                $pendinglabcount
	            );
	            array_push($rows, $item);
	        }
	        Database::setActiveConnection('default'); // We need to call the main (drupal) db back
	        Database::setActiveConnection(); // without the paramater means set back to the default for the site
	        $count = Utility::bootstrap_table_format($headers, $rows);
	    }
	    return $count;
	}
}