<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Admin configuration form for Event Registration module.
 */
class EventRegistrationSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['event_registration.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('event_registration.settings');

    $form['admin_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Admin notification email'),
      '#default_value' => $config->get('admin_email'),
      '#required' => TRUE,
    ];

    $form['admin_notification_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable admin notifications'),
      '#default_value' => $config->get('admin_notification_enabled'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('event_registration.settings')
      ->set('admin_email', $form_state->getValue('admin_email'))
      ->set('admin_notification_enabled', $form_state->getValue('admin_notification_enabled'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
