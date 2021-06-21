<?php

/**
 * @file
 * Contains Drupal\event_brite\Form\SettingsForm.
 */

namespace Drupal\event_brite\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 *
 * @package Drupal\event_brite\Form
 */
class AuthTokenForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'event_brite.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'auth_token_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('event_brite.settings');
    $form['auth_token'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Enter Private Token'),
      '#default_value' => $config->get('auth_token'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('event_brite.settings')
      ->set('auth_token', $form_state->getValue('auth_token'))
      ->save();
  }

}