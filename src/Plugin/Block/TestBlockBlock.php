<?php

declare(strict_types=1);

namespace Drupal\reqresimport_users\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\reqresimport\Service\ReqresUtilitiesInterface;
use Drupal\reqresimport_users\Service\TableContentService;
use Drupal\reqresimport_users\Service\UtilitiesInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a test block block.
 *
 * @Block(
 *   id = "reqresimport_users_test_block",
 *   admin_label = @Translation("Test block"),
 *   category = @Translation("Reqres"),
 * )
 */
final class TestBlockBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs the plugin instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly TableContentService $reqresimportUsersTableContentService,
    private readonly ReqresUtilitiesInterface $reqresimportUtilities,
    private readonly UtilitiesInterface $reqresimportUsersUtilities,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('reqresimport_users.table_content_service'),
      $container->get('reqresimport.utilities'),
      $container->get('reqresimport_users.utilities'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'example' => $this->t('Hello world!'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['example'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Example'),
      '#default_value' => $this->configuration['example'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['example'] = $form_state->getValue('example');
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build['content'] = [
      '#markup' => $this->t('It works! ') . $this->configuration['example'],
    ];
    return $build;
  }

}
