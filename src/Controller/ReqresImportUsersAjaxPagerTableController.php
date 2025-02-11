<?php

namespace Drupal\reqresimport_users\Controller;

use Drupal\reqresimport_users\Service\TableContentService;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\BeforeCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Drupal\block\Entity\Block;

/**
 * Controller for the AJAX pager table.
 */
class ReqresImportUsersAjaxPagerTableController extends ControllerBase {

  /**
   * The table content service.
   *
   * @var \Drupal\reqresimport_users\Service\TableContentService
   */
  protected $tableContentService;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Request Stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  public function __construct(
    MessengerInterface $messenger,
    TableContentService $tableContentService,
    AccountInterface $current_user,
    RequestStack $requestStack,
  ) {
    $this->tableContentService = $tableContentService;
    $this->messenger = $messenger;
    $this->currentUser = $current_user;
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('messenger'),
      $container->get('reqresimport_users.table_content_service'),
      $container->get('current_user'),
      $container->get('request_stack'),
    );
  }

  /**
   * Refreshes the table content via AJAX.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response.
   */
  public function refreshAjaxBlock() {
    $current_theme_name = \Drupal::theme()->getActiveTheme()->getName();
    $blocks = \Drupal::entityTypeManager()->getStorage('block')
      ->loadByProperties(['plugin' => 'reqresimport_users_block', 'theme' => $current_theme_name]);
    $this_block_id = reset($blocks)->id();

    $this_block = Block::load($this_block_id);
    if ($this_block) {
      $this_block_config = $this_block->get('settings');
    }

    $settings = [
      'id' => $this_block_config['id'],
      'per_page' => $this_block_config['per_page'],
      'email_label' => $this_block_config['email_label'],
      'forename_label' => $this_block_config['forename_label'],
      'surname_label' => $this_block_config['surname_label'],
    ];

    $request = $this->requestStack->getCurrentRequest();
    if (is_null($request) || !$request->isXmlHttpRequest()) {
      throw new HttpException(400, 'This is not an AJAX request.');
    }
    $page_number = (int) $request->query->get('page') + 1;

    $response = new AjaxResponse();
    $command = new ReplaceCommand('#ajax-pager-table-wrapper', $this->tableContentService->getTableContent(
      $settings['id'],
      $settings['per_page'],
      $settings['email_label'],
      $settings['forename_label'],
      $settings['surname_label'],
      $page_number));
    $response->addCommand($command);

    return $response;
  }

}