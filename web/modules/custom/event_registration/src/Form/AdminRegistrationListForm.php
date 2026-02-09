<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin listing of event registrations.
 */
class AdminRegistrationListForm extends FormBase {

  protected Connection $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  public function getFormId() {
    return 'event_registration_admin_list_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    // Fetch distinct event dates.
    $dates = $this->database->select('event_registration_event', 'e')
      ->fields('e', ['event_date'])
      ->distinct()
      ->execute()
      ->fetchCol();

    $date_options = ['' => $this->t('- Select Date -')];
    foreach ($dates as $date) {
      $date_options[$date] = date('Y-m-d', $date);
    }

    $form['event_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Date'),
      '#options' => $date_options,
      '#ajax' => [
        'callback' => '::updateEventNames',
        'wrapper' => 'event-name-wrapper',
      ],
    ];

    $form['event_name_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-name-wrapper'],
    ];

    $form['event_name_wrapper']['event_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Name'),
      '#options' => $this->getEventNames($form_state->getValue('event_date')),
    ];

    $form['actions'] = ['#type' => 'actions'];

    $form['actions']['filter'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
    ];

    $form['actions']['export'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export CSV'),
      '#submit' => ['::exportCsv'],
    ];

    // Display results.
    if ($form_state->getValue('event_id')) {
      $rows = [];
      $query = $this->database->select('event_registration_signup', 'r');
      $query->join('event_registration_event', 'e', 'r.event_id = e.id');
      $query->fields('r', ['full_name', 'email', 'college_name', 'department', 'created']);
      $query->fields('e', ['event_date']);
      $query->condition('r.event_id', $form_state->getValue('event_id'));

      $results = $query->execute()->fetchAll();

      foreach ($results as $row) {
        $rows[] = [
          $row->full_name,
          $row->email,
          date('Y-m-d', $row->event_date),
          $row->college_name,
          $row->department,
          date('Y-m-d H:i', $row->created),
        ];
      }

      $form['count'] = [
        '#markup' => '<p><strong>Total Participants:</strong> ' . count($rows) . '</p>',
      ];

      $form['table'] = [
        '#type' => 'table',
        '#header' => [
          'Name',
          'Email',
          'Event Date',
          'College',
          'Department',
          'Submission Date',
        ],
        '#rows' => $rows,
        '#empty' => $this->t('No registrations found.'),
      ];
    }

    return $form;
  }

  /**
   * AJAX callback for event name dropdown.
   */
  public function updateEventNames(array &$form, FormStateInterface $form_state) {
    return $form['event_name_wrapper'];
  }

  /**
   * Fetch event names by date.
   */
  private function getEventNames($event_date) {
    if (!$event_date) {
      return ['' => $this->t('- Select Event -')];
    }

    return ['' => $this->t('- Select Event -')] +
      $this->database->select('event_registration_event', 'e')
        ->fields('e', ['id', 'event_name'])
        ->condition('event_date', $event_date)
        ->execute()
        ->fetchAllKeyed();
  }

  /**
   * CSV export submit handler.
   */
  public function exportCsv(array &$form, FormStateInterface $form_state) {

    $event_id = $form_state->getValue('event_id');
    if (!$event_id) {
      return;
    }

    $query = $this->database->select('event_registration_signup', 'r');
    $query->join('event_registration_event', 'e', 'r.event_id = e.id');
    $query->fields('r');
    $query->fields('e', ['event_date', 'event_name']);

    $query->condition('r.event_id', $event_id);
    $results = $query->execute()->fetchAll();

    $handle = fopen('php://temp', 'w+');
    fputcsv($handle, [
      'Name',
      'Email',
      'Event Name',
      'Event Date',
      'College',
      'Department',
      'Submitted On',
    ]);

    foreach ($results as $row) {
      fputcsv($handle, [
        $row->full_name,
        $row->email,
        $row->event_name,
        date('Y-m-d', $row->event_date),
        $row->college_name,
        $row->department,
        date('Y-m-d H:i', $row->created),
      ]);
    }

    rewind($handle);
    $csv = stream_get_contents($handle);
    fclose($handle);

    $response = new Response($csv);
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="event_registrations.csv"');

    $form_state->setResponse($response);
  }
  /**
 * {@inheritdoc}
 */
public function submitForm(array &$form, FormStateInterface $form_state) {
  // Default submit handler required by FormInterface.
  // Actual actions are handled by custom submit callbacks.
}


}
