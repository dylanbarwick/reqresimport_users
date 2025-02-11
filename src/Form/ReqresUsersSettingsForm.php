<?php

declare(strict_types=1);

namespace Drupal\reqresimport_users\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Reqres import - users settings for this site.
 */
final class ReqresUsersSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'reqresimport_users_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['reqresimport_users.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['default_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default URL for reqres users'),
      '#default_value' => $this->config('reqresimport_users.settings')->get('default_url'),
      '#description' => $this->t('Leave this blank to use the default_url value from the reqresimport basic settings form.'),
    ];
    $form['default_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default endpoint'),
      '#default_value' => $this->config('reqresimport_users.settings')->get('default_endpoint'),
      '#description' => $this->t('The path that specifies an endpoint on the API, start with a forward slash.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if ($form_state->getValue('example') === 'wrong') {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('The value is not correct.'),
    //     );
    //   }
    // @endcode
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('reqresimport_users.settings')
      ->set('default_url', $form_state->getValue('default_url'))
      ->set('default_endpoint', $form_state->getValue('default_endpoint'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
