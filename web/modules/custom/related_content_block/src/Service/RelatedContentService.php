<?php

namespace Drupal\related_content_block\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use \Drupal\node\Entity\Node;
use Drupal\Core\Database\Connection;

/**
 * Service for fetching related content.
 */
class RelatedContentService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
   /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new RelatedContentService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $database) {
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
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
    // kint("ggg");die("ggg");
    // Load the current node.
    if ($current_node_id){
    $current_node =$this->entityTypeManager->getStorage('node')->load($current_node_id);
    }
    // Check if the current node is of type "Article".
    if ($current_node && $current_node->getType() == 'article') {
      // Fetch the category and author information of the current node.
      $category = $current_node->get('field_category')->getValue()[0]['target_id'];
      $author = $current_node->getOwner();
      $limit = 5;
      // Define an array to store related nodes.
      $related_nodes = [];

      // Fetch articles in the same category and by the same author.
      $related_nodes += $this->getRelatedNodesByCategoryAndAuthor($current_node, $category, $author, $limit);
      // Fetch articles in the same category but by different authors.
      $related_nodes += $this->getRelatedNodesByCategory($current_node, $category, $limit);

      // Fetch articles in different categories but by the same author.
      $related_nodes += $this->getRelatedNodesByAuthor($current_node, $author);

      // Fetch articles in different categories and by different authors.
      $related_nodes += $this->getRelatedNodes($current_node);

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
   public function getRelatedNodesByCategoryAndAuthor($current_node_id, $category_id, $author_id, $limit = 5) {
    $query = $this->database->select('node_field_data', 'n')
      ->fields('n', ['nid'])
      ->condition('n.status', 1)
      ->condition('n.type', 'article')
      ->condition('n.nid', $current_node_id, '<>')
      ->condition('n.field_category_target_id', $category_id)
      ->condition('n.uid', $author_id)
      ->orderBy('n.title')
      ->orderBy('n.created', 'DESC')
      ->range(0, $limit);
    $nids = $query->execute()->fetchCol();
    return $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
  }

  /**
   * Helper method to fetch articles in the same category but by different authors.
   */
   public function getRelatedNodesByCategory($current_node_id, $category_id, $limit = 5) {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('status', 1)
      ->accessCheck(TRUE)
      ->condition('type', 'article')
      ->condition('nid', $current_node_id, '<>')
      ->condition('field_category', $category_id);
    $query->sort('title');
    $query->sort('created', 'DESC');
    $query->range(0, $limit);
    $nids = $query->execute();
    var_dump("tripti");exit;
    return $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
  }

  /**
   * Helper method to fetch articles in different categories but by the same author.
   */
  public function getRelatedNodesByAuthor($current_node_id, $author_id, $limit = 5) {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('status', 1)
      ->accessCheck(TRUE)
      ->condition('type', 'article')
      ->condition('nid', $current_node_id, '<>')
      ->condition('uid', $author_id);
    $query->sort('title');
    $query->sort('created', 'DESC');
    $query->range(0, $limit);
    $nids = $query->execute();
    return $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
  }

  /**
   * Helper method to fetch articles in different categories and by different authors.
   */
 public function getRelatedNodes($current_node_id, $limit = 5) {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('status', 1)
      ->condition('nid', $current_node_id, '<>')
      ->accessCheck(TRUE)
      ->condition('type', 'article');
    $query->sort('title');
    $query->sort('created', 'DESC');
    $query->range(0, $limit);
    $nids = $query->execute();
    return $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
}


}
