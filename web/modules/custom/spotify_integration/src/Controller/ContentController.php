<?php

namespace Drupal\spotify_integration\Controller;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Content controller.
 */
class ContentController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Company manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerBase
   */
  protected $pluginManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CompanyController constructor.
   *
   * @param \Drupal\Component\Plugin\PluginManagerBase $plugin_manager
   *   The plugin manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  final public function __construct(PluginManagerBase $plugin_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->pluginManager = $plugin_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns a render-able array for the home page.
   */
  public function home() {
    return [
      '#theme' => 'home',
    ];
  }

}
