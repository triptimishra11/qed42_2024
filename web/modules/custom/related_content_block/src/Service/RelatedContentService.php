<?php

namespace Drupal\related_content_block\Service;

use \Drupal\node\Entity\Node;

/**
 * Service for fetching related content.
 */
class RelatedContentService {

  /**
   * Constructs a new RelatedContentService object.
   */
  public function __construct() {
    // No need to inject services via constructor.
  }

  /**
   * Fetch related content based on specific criteria.
   *
   * @param int $current_node_id
   *   The ID of the current node.
   *
   * @return array
   *   An array of related content entities.
   */
  public function fetchRelatedContent($current_node_id) {
    // Load the entity type manager and database connection services.
    $entityTypeManager = \Drupal::entityTypeManager();
    $database = \Drupal::database();

    $current_node = $entityTypeManager->getStorage('node')->load($current_node_id);
    // Check if the current node is of type "Article".
    if ($current_node && $current_node->getType() == 'article') {
      // Fetch the category and author information of the current node.
      $category = $current_node->get('field_category')->getValue()[0]['target_id'];
      //\Drupal::logger('related_content_block')->notice('Current node loaded: @node', ['@node' => $category]);
      $author = $current_node->get('uid')->getValue()[0]['target_id'];
      $limit = 5;
      // Define an array to store related nodes.
      $related_nodes = [];

      // Fetch articles in the same category and by the same author.
      $related_nodes += $this->getRelatedNodesByCategoryAndAuthor($current_node_id, $category, $author, $limit);
      // Fetch articles in the same category but by different authors.
      $related_nodes += $this->getRelatedNodesByCategory($current_node_id, $category, $limit);

      // Fetch articles in different categories but by the same author.
      $related_nodes += $this->getRelatedNodesByAuthor($current_node_id, $author);
      // Fetch articles in different categories and by different authors.
      $related_nodes += $this->getRelatedNodes($current_node_id);
      // Sort the related nodes by title in ascending order and creation date in descending order.
      uasort($related_nodes, function($a, $b) {
        if ($a->getTitle() == $b->getTitle()) {
          return $b->getCreatedTime() - $a->getCreatedTime();
        }
        return strcmp($a->getTitle(), $b->getTitle());
      });
      // Limit the number of related nodes to 5.
      $related_nodes = array_slice($related_nodes, 0, 5);
      return $related_nodes;
    }

    return [];
  }

 /**
 * Helper method to fetch articles in the same category and by the same author.
 */
public function getRelatedNodesByCategoryAndAuthor($current_node_id, $category_id = '', $author_id = '', $limit = 5) {
    // Load the necessary services using the global Drupal object.
    $entityTypeManager = \Drupal::entityTypeManager();
    $database = \Drupal::database();
    $query = $database->select('node_field_data', 'n');
    $query->fields('n', ['nid']);
    $query->condition('n.status', 1);
    $query->condition('n.type', 'article');
    $query->condition('n.nid', $current_node_id, '<>');
    $query->leftJoin('node__field_category', 'fc', 'fc.entity_id = n.nid');
    $query->condition('fc.field_category_target_id', $category_id, '=');
    $query->condition('n.uid', $author_id);
    $query->orderBy('n.title');
    $query->orderBy('n.created', 'DESC');
    $query->range(0, $limit);


    $nids = $query->execute()->fetchCol();
    return $entityTypeManager->getStorage('node')->loadMultiple($nids);
}
/**
 * Helper method to fetch articles in the same category but by different authors.
 */
public function getRelatedNodesByCategory($current_node_id, $category_id = '', $limit = 5) {
    // Load the necessary services using the global Drupal object.
    $entityTypeManager = \Drupal::entityTypeManager();
    $database = \Drupal::database();
     $query = $database->select('node_field_data', 'n');
    $query->fields('n', ['nid']);
    $query->condition('n.status', 1);
    $query->condition('n.type', 'article');
    $query->condition('n.nid', $current_node_id, '<>');
    $query->leftJoin('node__field_category', 'fc', 'fc.entity_id = n.nid');
    $query->condition('fc.field_category_target_id', $category_id, '=');
    $query->orderBy('n.title');
    $query->orderBy('n.created', 'DESC');
    $query->range(0, $limit);

    $nids = $query->execute()->fetchCol();
    return $entityTypeManager->getStorage('node')->loadMultiple($nids);
}

/**
 * Helper method to fetch articles in different categories but by the same author.
 */
public function getRelatedNodesByAuthor($current_node_id, $author_id, $limit = 5) {
    $entityTypeManager = \Drupal::entityTypeManager();
    $database = \Drupal::database();

    $query = $database->select('node_field_data', 'n')
    ->fields('n', ['nid'])
    ->condition('n.status', 1)
    ->condition('n.type', 'article')
    ->condition('n.nid', $current_node_id, '<>')
    ->condition('n.uid', $author_id)
    ->orderBy('n.title')
    ->orderBy('n.created', 'DESC')
    ->range(0, $limit);
    $nids = $query->execute()->fetchCol();
    return $entityTypeManager->getStorage('node')->loadMultiple($nids);
}

/**
 * Helper method to fetch articles in different categories and by different authors.
 */
public function getRelatedNodes($current_node_id, $limit = 5) {
    $entityTypeManager = \Drupal::entityTypeManager();
    $database = \Drupal::database();

    $query = $database->select('node_field_data', 'n')
    ->fields('n', ['nid'])
    ->condition('n.status', 1)
    ->condition('n.type', 'article')
    ->condition('n.nid', $current_node_id, '<>')
    ->orderBy('n.title')
    ->orderBy('n.created', 'DESC')
    ->range(0, $limit);
    $nids = $query->execute()->fetchCol();
    return $entityTypeManager->getStorage('node')->loadMultiple($nids);
}



}