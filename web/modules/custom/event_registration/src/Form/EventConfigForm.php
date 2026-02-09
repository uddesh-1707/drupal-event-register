<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Event configuration form for admins.
 */
class EventConfigForm extends FormBase {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructor.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_event_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['event_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#required' => TRUE,
    ];

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category of the event'),
      '#options' => [
        'online_workshop' => $this->t('Online Workshop'),
        'hackathon' => $this->t('Hackathon'),
        'conference' => $this->t('Conference'),
        'one_day_workshop' => $this->t('One-day Workshop'),
      ],
      '#required' => TRUE,
    ];

    $form['registration_start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Registration Start Date'),
      '#required' => TRUE,
    ];

    $form['registration_end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Registration End Date'),
      '#required' => TRUE,
    ];

    $form['event_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Event Date'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Event'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $start = strtotime($form_state->getValue('registration_start_date'));
    $end = strtotime($form_state->getValue('registration_end_date'));
    $event = strtotime($form_state->getValue('event_date'));

    if ($end < $start) {
      $form_state->setErrorByName('registration_end_date',
        $this->t('Registration end date must be after start date.'));
    }

    if ($event < $end) {
      $form_state->setErrorByName('event_date',
        $this->t('Event date must be after registration end date.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  // Save registration.
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

  // Fetch event details.
  $event = $this->database->select('event_registration_event', 'e')
    ->fields('e', ['event_name', 'event_date', 'category'])
    ->condition('id', $form_state->getValue('event_id'))
    ->execute()
    ->fetchObject();

  $params = [
    '@name' => $form_state->getValue('full_name'),
    '@email' => $form_state->getValue('email'),
    '@event' => $event->event_name,
    '@date' => date('Y-m-d', $event->event_date),
    '@category' => ucfirst(str_replace('_', ' ', $event->category)),
  ];

  $mailManager = \Drupal::service('plugin.manager.mail');

  // Send email to user.
  $mailManager->mail(
    'event_registration',
    'user_confirmation',
    $form_state->getValue('email'),
    \Drupal::languageManager()->getDefaultLanguage()->getId(),
    $params
  );

  // Send email to admin if enabled.
  $config = $this->configFactory()->get('event_registration.settings');
  if ($config->get('admin_notification_enabled')) {
    $mailManager->mail(
      'event_registration',
      'admin_notification',
      $config->get('admin_email'),
      \Drupal::languageManager()->getDefaultLanguage()->getId(),
      $params
    );
  }

  $this->messenger()->addStatus(
    $this->t('You have successfully registered for the event.')
  );
}

}
