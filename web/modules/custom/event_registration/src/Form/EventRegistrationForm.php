<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Event registration form.
 */
class EventRegistrationForm extends FormBase {

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
    return 'event_registration_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    // Check if registration is open.
    $now = time();
    $open_events = $this->database->select('event_registration_event', 'e')
      ->fields('e', ['id'])
      ->condition('registration_start_date', $now, '<=')
      ->condition('registration_end_date', $now, '>=')
      ->execute()
      ->fetchCol();

    if (empty($open_events)) {
      $form['message'] = [
        '#markup' => $this->t('Event registration is currently closed.'),
      ];
      return $form;
    }

    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
    ];

    $form['college_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('College Name'),
      '#required' => TRUE,
    ];

    $form['department'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Department'),
      '#required' => TRUE,
    ];

    // Category dropdown.
    $categories = $this->database->select('event_registration_event', 'e')
      ->fields('e', ['category'])
      ->distinct()
      ->execute()
      ->fetchCol();

    $category_options = array_combine($categories, $categories);

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category'),
      '#options' => ['' => $this->t('- Select -')] + $category_options,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updateEventDates',
        'wrapper' => 'event-date-wrapper',
      ],
    ];

    $form['event_date_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-date-wrapper'],
    ];

    $form['event_date_wrapper']['event_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Date'),
      '#options' => $this->getEventDates($form_state->getValue('category')),
      '#required' => TRUE,
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
      '#options' => $this->getEventNames(
        $form_state->getValue('category'),
        $form_state->getValue('event_date')
      ),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
    ];

    return $form;
  }

  /**
   * AJAX callback for event dates.
   */
  public function updateEventDates(array &$form, FormStateInterface $form_state) {
    return $form['event_date_wrapper'];
  }

  /**
   * AJAX callback for event names.
   */
  public function updateEventNames(array &$form, FormStateInterface $form_state) {
    return $form['event_name_wrapper'];
  }

  /**
   * Fetch event dates by category.
   */
  private function getEventDates($category) {
    if (!$category) {
      return ['' => $this->t('- Select -')];
    }

    $dates = $this->database->select('event_registration_event', 'e')
      ->fields('e', ['event_date'])
      ->condition('category', $category)
      ->execute()
      ->fetchCol();

    $options = ['' => $this->t('- Select -')];
    foreach ($dates as $date) {
      $options[$date] = date('Y-m-d', $date);
    }

    return $options;
  }

  /**
   * Fetch event names by category and date.
   */
  private function getEventNames($category, $event_date) {
    if (!$category || !$event_date) {
      return ['' => $this->t('- Select -')];
    }

    return $this->database->select('event_registration_event', 'e')
      ->fields('e', ['id', 'event_name'])
      ->condition('category', $category)
      ->condition('event_date', $event_date)
      ->execute()
      ->fetchAllKeyed();
  }

 public function validateForm(array &$form, FormStateInterface $form_state) {

  // Strict email validation.
  $email = $form_state->getValue('email');
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $form_state->setErrorByName('email',
      $this->t('Please enter a valid email address.'));
  }

  // No special characters in text fields.
  foreach (['full_name', 'college_name', 'department'] as $field) {
    if (!preg_match('/^[a-zA-Z\s]+$/', $form_state->getValue($field))) {
      $form_state->setErrorByName(
        $field,
        $this->t('Special characters are not allowed.')
      );
    }
  }

  // Duplicate check: Email + Event ID.
  $exists = $this->database->select('event_registration_signup', 'r')
    ->fields('r', ['id'])
    ->condition('email', $email)
    ->condition('event_id', $form_state->getValue('event_id'))
    ->execute()
    ->fetchField();

  if ($exists) {
    $form_state->setErrorByName('email',
      $this->t('You have already registered for this event.'));
  }
}

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->database->insert('event_registration_signup')
      ->fields([
        'event_id' => $form_state->getValue('event_id'),
        'full_name' => $form_state->getValue('full_name'),
        'email' => $form_state->getValue('email'),
        'college_name' => $form_state->getValue('college_name'),
        'department' => $form_state->getValue('department'),
        'created' => time(),
      ])
      ->execute();

    $this->messenger()->addStatus(
      $this->t('You have successfully registered for the event.')
    );
  }

}
