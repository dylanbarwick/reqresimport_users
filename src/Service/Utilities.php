<?php

declare(strict_types=1);

namespace Drupal\reqresimport_users\Service;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * @todo Add class description.
 */
final class Utilities implements UtilitiesInterface {

  /**
   * Constructs a Utilities object.
   */
  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getSettings(): array {
    // Get settings.
    $parent_config = $this->configFactory->get('reqresimport.settings');
    $config = $this->configFactory->get('reqresimport_users.settings');

    if (!$config->get('default_url')) {
      $default_url = $parent_config->get('default_url');
    }
    else {
      $default_url = $config->get('default_url');
    }

    return [
      'default_url' => $default_url,
      'default_endpoint' => $config->get('default_endpoint'),
    ];
  }

}
