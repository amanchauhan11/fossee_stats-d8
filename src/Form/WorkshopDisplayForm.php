<?php

namespace Drupal\fossee_stats\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fossee_stats\Utility;
use Drupal\Core\Url;
use Drupal\Core\Link;

class WorkshopDisplayForm extends FormBase {

  public function getFormId(){
    return 'sort_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['sort_fieldset'] = [
                '#type' => 'fieldset',
                '#tree' => TRUE,
                '#attributes' => [
                    'id' => ['sort-fieldset-wrapper'],
                ],
    ];

    $form['sort_fieldset']['#attached']['library'][] = 'fossee_stats/highlighter';

    $form['sort_fieldset']['sort_by_date'] = array(
        '#type' => 'submit',
        '#value' => t('Sort by date'),
        '#limit_validation_errors' => array(),
        '#submit' => array(
            '::sort_by_submit',
        ),
        '#ajax' => array(
            'callback' => [$this, 'workshop_display_form_callback'],
            'wrapper' => 'sort-fieldset-wrapper',
        ),
        '#name' => 'startdate',
    );

    $form['sort_fieldset']['last_added'] = array(
        '#type' => 'submit',
        '#value' => t('Sort by time of adding events'),
        '#limit_validation_errors' => array(),
        '#submit' => array(
            '::sort_by_submit',
        ),
        '#ajax' => array(
            'callback' => [$this, 'workshop_display_form_callback'],
            'wrapper' => 'sort-fieldset-wrapper',
        ),
        '#name' => 'w_id',
    );

    $form['sort_fieldset']['sort_by_name'] = array(
        '#type' => 'submit',
        '#value' => t('Sort by name'),
        '#limit_validation_errors' => array(),
        '#submit' => array(
            '::sort_by_submit',
        ),
        '#ajax' => array(
            'callback' => [$this, 'workshop_display_form_callback'],
            'wrapper' => 'sort-fieldset-wrapper',
        ),
        '#name'=>'w_name',
    );

    $form['sort_fieldset']['sort_by_venue'] = array(
        '#type' => 'submit',
        '#value' => t('Sort by venue'),
        '#limit_validation_errors' => array(),
        '#submit' => array(
            '::sort_by_submit'
        ),
        '#ajax' => array(
            'callback' => [$this, 'workshop_display_form_callback'],
            'wrapper' => 'sort-fieldset-wrapper'
        ),
        '#name'=>'venue',
    );

    $form['sort_fieldset']['sort_by_participant'] = array(
        '#type' => 'submit',
        '#value' => t('Sort by participants'),
        '#limit_validation_errors' => array(),
        '#submit' => array(
            '::sort_by_submit'
        ),
        '#ajax' => array(
            'callback' => [$this, 'workshop_display_form_callback'],
            'wrapper' => 'sort-fieldset-wrapper'
        ),
        '#name'=>'no_of_participant',
    );

    $events_table_content = '';
    if(!isset($form_state->getTriggeringElement()['#name']))
    {
        $events_table_content = $this->getWorkshopTableContent('w_id');
        $form['sort_fieldset']['#attached']['drupalSettings']['sortby'] = 'w_id';
    }
    else
    {
        $events_table_content = $this->getWorkshopTableContent($form_state->getTriggeringElement()['#name']);
        $form['sort_fieldset']['#attached']['drupalSettings']['sortby'] = $form_state->getTriggeringElement()['#name'];
    }
    $form['sort_fieldset']['events_table'] = array(
        '#type' => 'item',
        '#markup' => $events_table_content,
    );
    return $form;
  }


  public function workshop_display_form_callback(array &$form, FormStateInterface $form_state) {
    return $form['sort_fieldset'];
  }

  public function sort_by_submit(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

  public function getWorkshopTableContent($criteria) {
    $criteria_order = array('w_id'=>'DESC', 'startdate'=>'DESC', 'w_name'=>'INC', 'venue'=>'INC', 'no_of_participant'=>'INC');
    $connection = \Drupal::database();
    $query = $connection->select('workshop');
    $query->fields('workshop');
    $query->orderBy($criteria, $criteria_order[$criteria]);
    $result = $query->execute();
    $headers = array(
        "S No.",
        "Name",
        "Start Date",
        "Venue",
        "No of Participant",
        ""
    );
    $rows = array();
    $options = array(
        'attributes' => array(
            'class' => 'delete',
        )
    );
    $num = 1;
    while ($row = $result->fetchObject()) {
        $item = array(
            $num,
            $row->w_name,
            $row->startdate,
            $row->venue,
            $row->no_of_participant,
            Link::fromTextAndUrl(t('Edit'), Url::fromUri('internal:/events/edit/'.$row->w_id))->toString() . " | " . Link::fromTextAndUrl(t('Delete'), Url::fromUri('internal:/events/delete/'.$row->w_id, $options))->toString(),
        );
        array_push($rows, $item);
        $num++;
    }
    return Utility::bootstrap_table_format($headers, $rows);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {}
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
