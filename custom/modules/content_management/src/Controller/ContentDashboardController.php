<?php

namespace Drupal\content_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Content Dashboard Controller.
 */
class ContentDashboardController extends ControllerBase {

  /**
   * Dashboard page.
   */
  public function dashboard() {
    $current_user = \Drupal::currentUser();
    $connection = Database::getConnection();
    
    // Get statistics
    $stats = [];
    
    // Total active procurements
    $stats['active_procurements'] = $connection->select('content_procurements', 'cp')
      ->condition('procurement_status', ['published', 'evaluation'], 'IN')
      ->countQuery()
      ->execute()
      ->fetchField();
    
    // Total certified producers
    $stats['certified_producers'] = $connection->select('content_producers', 'cp')
      ->condition('certification_status', 'approved')
      ->countQuery()
      ->execute()
      ->fetchField();
    
    // Pending proposals (if user is admin)
    if ($current_user->hasPermission('evaluate content proposals')) {
      $stats['pending_proposals'] = $connection->select('content_proposals', 'cp')
        ->condition('proposal_status', 'submitted')
        ->countQuery()
        ->execute()
        ->fetchField();
    }
    
    // Recent procurements
    $recent_procurements = $connection->select('content_procurements', 'cp')
      ->fields('cp')
      ->condition('procurement_status', ['published', 'evaluation'], 'IN')
      ->orderBy('created_at', 'DESC')
      ->range(0, 5)
      ->execute()
      ->fetchAll();
    
    // Check if user is a producer
    $is_producer = content_management_user_is_producer($current_user->id());
    $producer_profile = null;
    $my_proposals = [];
    
    if ($is_producer) {
      $producer_profile = content_management_get_producer_profile($current_user->id());
      
      // Get user's recent proposals
      $query = $connection->select('content_proposals', 'cp');
      $query->join('content_producers', 'pr', 'cp.producer_id = pr.id');
      $query->join('content_procurements', 'proc', 'cp.procurement_id = proc.id');
      $query->fields('cp');
      $query->fields('proc', ['title', 'procurement_number']);
      $query->condition('pr.uid', $current_user->id());
      $query->orderBy('cp.created_at', 'DESC');
      $query->range(0, 5);
      
      $my_proposals = $query->execute()->fetchAll();
    }
    
    $build = [
      '#theme' => 'content_procurement_dashboard',
      '#statistics' => $stats,
      '#recent_procurements' => $recent_procurements,
      '#is_producer' => $is_producer,
      '#producer_profile' => $producer_profile,
      '#my_proposals' => $my_proposals,
      '#attached' => [
        'library' => ['content_management/dashboard'],
      ],
    ];
    
    return $build;
  }
}