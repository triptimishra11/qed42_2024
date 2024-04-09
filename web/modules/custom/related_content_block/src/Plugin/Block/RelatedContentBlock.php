<?php

namespace Drupal\related_content_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\related_content_block\Service\RelatedContentService;

/**
 * Provides a 'Related Content Block' block.
 *
 * @Block(
 *   id = "related_content_block",
 *   admin_label = @Translation("Related Content Block"),
 * )
 */
class RelatedContentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The related content service.
   *
   * @var \Drupal\related_content_block\Service\RelatedContentService
   */
  protected $relatedContentService;

  /**
   * Constructs a new RelatedContentBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\related_content_block\Service\RelatedContentService $related_content_service
   *   The related content service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RelatedContentService $related_content_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->relatedContentService = $related_content_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('related_content_block.related_content_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the current node ID
    $current_node_id = \Drupal::routeMatch()->getParameter('node')->id();
    // Fetch related content.
    $related_content = $this->relatedContentService->fetchRelatedContent($current_node_id);
    $items = [];
    foreach ($related_content as $node) {
      $items[] = [
        '#type' => 'link',
        '#title' => $node->getTitle(),
        '#url' => $node->toUrl(),
      ];
    }
    // Build the render array.
    $build = [
      '#theme' => 'related_content_block',
      '#items' => $items,
    ];

    return $build;
  }

}
