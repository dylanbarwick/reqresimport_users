<?php

declare(strict_types=1);

namespace Drupal\reqresimport_users\Plugin\Block;

use Drupal\reqresimport\Service\TableContentServiceBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a reqres users block.
 *
 * @Block(
 *   id = "reqresimport_users_block",
 *   admin_label = @Translation("Reqres users"),
 *   category = @Translation("Reqres"),
 * )
 */
final class ReqresImportUsersBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The table content service.
   *
   * @var \Drupal\reqresimport\Service\TableContentServiceBase
   */
  protected $tableContentServiceBase;

    /**
   * Constructs a new ReqresImportUsersAjaxBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param $plugin_id
   *   The plugin ID for the plugin instance.
   * @param $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\reqresimport\Service\TableContentServiceBase $tableContentServiceBase
   *   The table content service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MessengerInterface $messenger,
    TableContentServiceBase $tableContentServiceBase,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->tableContentServiceBase = $tableContentServiceBase;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('messenger'),
      $container->get('reqresimport.table_content_service_base'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'per_page' => 6,
      'email_label' => $this->t('Email'),
      'first_name_label' => $this->t('Forename'),
      'last_name_label' => $this->t('Surname'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {

    // How many items per page. An integer field with minimum 1.
    $form['per_page'] = [
      '#type' => 'number',
      '#title' => $this->t('Items per page'),
      '#default_value' => $this->configuration['per_page'],
      '#min' => 1,
    ];

    // Email label.
    $form['email_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email label'),
      '#default_value' => $this->configuration['email_label'],
    ];

    // Forename label.
    $form['first_name_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Forename label'),
      '#default_value' => $this->configuration['first_name_label'],
    ];

    // Surname label.
    $form['last_name_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Surname label'),
      '#default_value' => $this->configuration['last_name_label'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['per_page'] = $form_state->getValue('per_page');
    $this->configuration['email_label'] = $form_state->getValue('email_label');
    $this->configuration['first_name_label'] = $form_state->getValue('first_name_label');
    $this->configuration['last_name_label'] = $form_state->getValue('last_name_label');
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    // Get values from block config.
    $block_config = $this->getConfiguration();
    // Get user utilities.
    $json_user_utils = \Drupal::service('reqresimport_users.utilities');
    $user_settings = $json_user_utils->getSettings();
    $url = $user_settings['default_url'] . $user_settings['default_endpoint'];
    $label_map = $json_user_utils->getRetrievedFieldLabels();
    foreach ($label_map as $key => $field_label_map) {
      // Check if the value is a string.
      if (!is_string($block_config[$field_label_map['our_label']])) {
        $label_map[$key]['our_value'] = $block_config[$field_label_map['our_label']]->__toString();
      }
      else {
        $label_map[$key]['our_value'] = $block_config[$field_label_map['our_label']];
      }

    }

    $page = (int) \Drupal::request()->query->get('page') ?? 0;
    $build = $this->tableContentServiceBase->getTableContent(
      $block_config['id'],
      $block_config['per_page'],
      $url,
      $page,
      $label_map
    );

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account): AccessResult {
    // If the user has the permission `view reqres users block`, allow access.
    if ($account->hasPermission('view reqres users block')) {
      return AccessResult::allowed();
    }
  }

}
