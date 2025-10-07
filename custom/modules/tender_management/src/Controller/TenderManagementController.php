<?php

namespace Drupal\tender_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Class TenderManagementController.
 */
class TenderManagementController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * TenderManagementController constructor.
   */
  public function __construct(Connection $database, LoggerChannelFactoryInterface $logger_factory, AccountInterface $current_user) {
    $this->database = $database;
    $this->loggerFactory = $logger_factory;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('logger.factory'),
      $container->get('current_user')
    );
  }

  /**
   * Tender Management Dashboard page.
   */
  public function dashboard() {
    $user_id = $this->currentUser->id();
    
    // Get user role-based data
    $user_roles = $this->currentUser->getRoles();
    $is_admin = in_array('administrator', $user_roles) || in_array('tender_admin', $user_roles);
    $is_evaluator = in_array('tender_evaluator', $user_roles);
    $is_vendor = in_array('vendor', $user_roles) || in_array('content_producer', $user_roles);
    
    $build = [
      '#theme' => 'tender_dashboard',
      '#user_roles' => $user_roles,
      '#is_admin' => $is_admin,
      '#is_evaluator' => $is_evaluator,
      '#is_vendor' => $is_vendor,
      '#attached' => [
        'library' => [
          'tender_management/tender_dashboard',
        ],
      ],
    ];

    return $build;
  }

  /**
   * Vendor Registration page.
   */
  public function vendorRegistration() {
    $build = [
      '#theme' => 'vendor_registration',
      '#attached' => [
        'library' => [
          'tender_management/vendor_registration',
        ],
      ],
    ];

    return $build;
  }

  /**
   * Create Tender page.
   */
  public function createTender() {
    $build = [
      '#theme' => 'create_tender',
      '#attached' => [
        'library' => [
          'tender_management/create_tender',
        ],
      ],
    ];

    return $build;
  }

  /**
   * Tender List page.
   */
  public function tenderList() {
    $user_id = $this->currentUser->id();
    $user_roles = $this->currentUser->getRoles();
    $is_admin = in_array('administrator', $user_roles) || in_array('tender_admin', $user_roles);
    
    // Get tenders based on user role
    $query = $this->database->select('tenders', 't')
      ->fields('t')
      ->orderBy('created_at', 'DESC');
    
    if (!$is_admin) {
      // Vendors can only see published tenders
      $query->condition('status', ['published', 'closed'], 'IN');
    }
    
    $tenders = $query->execute()->fetchAll();
    
    $build = [
      '#theme' => 'tender_list',
      '#tenders' => $tenders,
      '#is_admin' => $is_admin,
      '#attached' => [
        'library' => [
          'tender_management/tender_list',
        ],
      ],
    ];

    return $build;
  }

  /**
   * Tender Details page.
   */
  public function tenderDetails($tender_id) {
    $user_id = $this->currentUser->id();
    $user_roles = $this->currentUser->getRoles();
    $is_admin = in_array('administrator', $user_roles) || in_array('tender_admin', $user_roles);
    $is_vendor = in_array('vendor', $user_roles);
    
    // Get tender details
    $tender = $this->database->select('tenders', 't')
      ->fields('t')
      ->condition('id', $tender_id)
      ->execute()
      ->fetchAssoc();
    
    if (!$tender) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
    
    // Get user's proposal if vendor
    $user_proposal = null;
    if ($is_vendor) {
      $user_proposal = $this->database->select('tender_proposals', 'tp')
        ->fields('tp')
        ->condition('tender_id', $tender_id)
        ->condition('vendor_id', $user_id)
        ->execute()
        ->fetchAssoc();
    }
    
    // Get all proposals if admin
    $proposals = [];
    if ($is_admin) {
      $proposals = $this->database->select('tender_proposals', 'tp')
        ->fields('tp')
        ->condition('tender_id', $tender_id)
        ->execute()
        ->fetchAll();
    }
    
    $build = [
      '#theme' => 'tender_details',
      '#tender' => $tender,
      '#user_proposal' => $user_proposal,
      '#proposals' => $proposals,
      '#is_admin' => $is_admin,
      '#is_vendor' => $is_vendor,
      '#user_id' => $user_id,
      '#attached' => [
        'library' => [
          'tender_management/tender_details',
        ],
      ],
    ];

    return $build;
  }

  /**
   * Evaluation page.
   */
  public function evaluation() {
    $user_roles = $this->currentUser->getRoles();
    $is_evaluator = in_array('tender_evaluator', $user_roles) || in_array('administrator', $user_roles);
    
    if (!$is_evaluator) {
      throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
    }
    
    // Get proposals pending evaluation
    $proposals = $this->database->select('tender_proposals', 'tp')
      ->fields('tp')
      ->condition('status', 'submitted')
      ->execute()
      ->fetchAll();
    
    $build = [
      '#theme' => 'tender_evaluation',
      '#proposals' => $proposals,
      '#attached' => [
        'library' => [
          'tender_management/evaluation',
        ],
      ],
    ];

    return $build;
  }

  /**
   * Results page.
   */
  public function results() {
    $user_roles = $this->currentUser->getRoles();
    $is_admin = in_array('administrator', $user_roles) || in_array('tender_admin', $user_roles);
    
    // Get completed tenders with results
    $query = $this->database->select('tenders', 't')
      ->fields('t')
      ->condition('status', 'evaluated')
      ->orderBy('created_at', 'DESC');
    
    $tenders = $query->execute()->fetchAll();
    
    $build = [
      '#theme' => 'tender_results',
      '#tenders' => $tenders,
      '#is_admin' => $is_admin,
      '#attached' => [
        'library' => [
          'tender_management/results',
        ],
      ],
    ];

    return $build;
  }

  /**
   * AJAX: Save tender.
   */
  public function saveTender(Request $request) {
    $user_id = $this->currentUser->id();
    
    try {
      $tender_data = [
        'tender_number' => $request->request->get('tender_number'),
        'title' => $request->request->get('title'),
        'description' => $request->request->get('description'),
        'type' => $request->request->get('type'),
        'category' => $request->request->get('category'),
        'episode_duration' => $request->request->get('episode_duration'),
        'total_episodes' => (int) $request->request->get('total_episodes'),
        'budget_per_episode' => (float) $request->request->get('budget_per_episode'),
        'total_budget' => (float) $request->request->get('total_budget'),
        'submission_deadline' => $request->request->get('submission_deadline'),
        'evaluation_period' => $request->request->get('evaluation_period'),
        'production_start' => $request->request->get('production_start'),
        'production_end' => $request->request->get('production_end'),
        'technical_requirements' => $request->request->get('technical_requirements'),
        'content_requirements' => $request->request->get('content_requirements'),
        'status' => $request->request->get('status', 'draft'),
        'created_by' => $user_id,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
      ];

      $tender_id = $this->database->insert('tenders')
        ->fields($tender_data)
        ->execute();

      $this->loggerFactory->get('tender_management')
        ->info('Tender created successfully by user @user_id with ID @tender_id', [
          '@user_id' => $user_id,
          '@tender_id' => $tender_id,
        ]);

      return new JsonResponse([
        'success' => TRUE,
        'message' => 'Tender created successfully!',
        'tender_id' => $tender_id,
      ]);

    } catch (\Exception $e) {
      $this->loggerFactory->get('tender_management')
        ->error('Error creating tender: @error', ['@error' => $e->getMessage()]);

      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Error creating tender: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * AJAX: Save proposal.
   */
  public function saveProposal(Request $request) {
    $user_id = $this->currentUser->id();
    
    try {
      // Get company ID for this user
      $company = $this->database->select('content_producers', 'cp')
        ->fields('cp', ['id'])
        ->condition('user_id', $user_id)
        ->execute()
        ->fetchAssoc();
      
      $proposal_data = [
        'tender_id' => (int) $request->request->get('tender_id'),
        'vendor_id' => $user_id,
        'company_id' => $company ? $company['id'] : NULL,
        'proposal_title' => $request->request->get('proposal_title'),
        'proposal_description' => $request->request->get('proposal_description'),
        'proposed_budget' => (float) $request->request->get('proposed_budget'),
        'timeline' => $request->request->get('timeline'),
        'technical_approach' => $request->request->get('technical_approach'),
        'team_details' => $request->request->get('team_details'),
        'status' => $request->request->get('status', 'draft'),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
      ];

      // Check if proposal already exists
      $existing_proposal = $this->database->select('tender_proposals', 'tp')
        ->fields('tp', ['id'])
        ->condition('tender_id', $proposal_data['tender_id'])
        ->condition('vendor_id', $user_id)
        ->execute()
        ->fetchAssoc();

      if ($existing_proposal) {
        // Update existing proposal
        unset($proposal_data['created_at']);
        $this->database->update('tender_proposals')
          ->fields($proposal_data)
          ->condition('id', $existing_proposal['id'])
          ->execute();
        $proposal_id = $existing_proposal['id'];
      } else {
        // Create new proposal
        $proposal_id = $this->database->insert('tender_proposals')
          ->fields($proposal_data)
          ->execute();
      }

      $this->loggerFactory->get('tender_management')
        ->info('Proposal saved successfully by user @user_id with ID @proposal_id', [
          '@user_id' => $user_id,
          '@proposal_id' => $proposal_id,
        ]);

      return new JsonResponse([
        'success' => TRUE,
        'message' => 'Proposal saved successfully!',
        'proposal_id' => $proposal_id,
      ]);

    } catch (\Exception $e) {
      $this->loggerFactory->get('tender_management')
        ->error('Error saving proposal: @error', ['@error' => $e->getMessage()]);

      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Error saving proposal: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * AJAX: Save evaluation.
   */
  public function saveEvaluation(Request $request) {
    $user_id = $this->currentUser->id();
    
    try {
      $evaluation_data = [
        'proposal_id' => (int) $request->request->get('proposal_id'),
        'evaluator_id' => $user_id,
        'criteria_name' => $request->request->get('criteria_name'),
        'criteria_weight' => (float) $request->request->get('criteria_weight'),
        'score' => (float) $request->request->get('score'),
        'max_score' => (float) $request->request->get('max_score', 100),
        'comments' => $request->request->get('comments'),
        'evaluation_date' => date('Y-m-d H:i:s'),
      ];

      $evaluation_id = $this->database->insert('tender_evaluations')
        ->fields($evaluation_data)
        ->execute();

      // Update proposal status and score
      $this->updateProposalScore($evaluation_data['proposal_id']);

      $this->loggerFactory->get('tender_management')
        ->info('Evaluation saved successfully by user @user_id with ID @evaluation_id', [
          '@user_id' => $user_id,
          '@evaluation_id' => $evaluation_id,
        ]);

      return new JsonResponse([
        'success' => TRUE,
        'message' => 'Evaluation saved successfully!',
        'evaluation_id' => $evaluation_id,
      ]);

    } catch (\Exception $e) {
      $this->loggerFactory->get('tender_management')
        ->error('Error saving evaluation: @error', ['@error' => $e->getMessage()]);

      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Error saving evaluation: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * AJAX: Get tender data.
   */
  public function getTender(Request $request) {
    $tender_id = $request->query->get('tender_id');
    
    try {
      $tender = $this->database->select('tenders', 't')
        ->fields('t')
        ->condition('id', $tender_id)
        ->execute()
        ->fetchAssoc();

      if (!$tender) {
        return new JsonResponse([
          'success' => FALSE,
          'message' => 'Tender not found',
        ], 404);
      }

      return new JsonResponse([
        'success' => TRUE,
        'tender' => $tender,
      ]);

    } catch (\Exception $e) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Error retrieving tender: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Helper function to update proposal score.
   */
  private function updateProposalScore($proposal_id) {
    // Calculate weighted average score
    $evaluations = $this->database->select('tender_evaluations', 'te')
      ->fields('te', ['score', 'criteria_weight', 'max_score'])
      ->condition('proposal_id', $proposal_id)
      ->execute()
      ->fetchAll();

    $total_weighted_score = 0;
    $total_weight = 0;

    foreach ($evaluations as $evaluation) {
      $normalized_score = ($evaluation->score / $evaluation->max_score) * 100;
      $weighted_score = $normalized_score * ($evaluation->criteria_weight / 100);
      $total_weighted_score += $weighted_score;
      $total_weight += $evaluation->criteria_weight;
    }

    $final_score = $total_weight > 0 ? $total_weighted_score : 0;

    // Update proposal
    $this->database->update('tender_proposals')
      ->fields([
        'evaluation_score' => $final_score,
        'status' => 'under_review',
        'updated_at' => date('Y-m-d H:i:s'),
      ])
      ->condition('id', $proposal_id)
      ->execute();
  }

  /**
   * AJAX: Get dashboard statistics.
   */
  public function getDashboardStats(Request $request) {
    $user_id = $this->currentUser->id();
    $user_roles = $this->currentUser->getRoles();
    $is_admin = in_array('administrator', $user_roles) || in_array('tender_admin', $user_roles);
    $is_vendor = in_array('vendor', $user_roles) || in_array('content_producer', $user_roles);
    
    try {
      $stats = [];
      
      // Active tenders count
      $query = $this->database->select('tenders', 't');
      if ($is_admin) {
        $stats['active_tenders'] = $query->condition('status', ['draft', 'published'], 'IN')->countQuery()->execute()->fetchField();
      } else {
        $stats['active_tenders'] = $query->condition('status', 'published')->countQuery()->execute()->fetchField();
      }
      
      // User-specific stats
      if ($is_vendor) {
        $stats['my_proposals'] = $this->database->select('tender_proposals', 'tp')
          ->condition('vendor_id', $user_id)
          ->countQuery()
          ->execute()
          ->fetchField();
      }
      
      if ($is_admin) {
        $stats['total_proposals'] = $this->database->select('tender_proposals', 'tp')
          ->countQuery()
          ->execute()
          ->fetchField();
          
        $stats['pending_evaluations'] = $this->database->select('tender_proposals', 'tp')
          ->condition('status', 'submitted')
          ->countQuery()
          ->execute()
          ->fetchField();
      }
      
      return new JsonResponse([
        'success' => TRUE,
        'stats' => $stats,
      ]);
      
    } catch (\Exception $e) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Error loading dashboard stats: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * AJAX: Get recent activities.
   */
  public function getRecentActivities(Request $request) {
    try {
      $activities = [];
      
      // Get recent tender activities
      $recent_tenders = $this->database->select('tenders', 't')
        ->fields('t', ['title', 'created_at', 'status'])
        ->orderBy('created_at', 'DESC')
        ->range(0, 5)
        ->execute()
        ->fetchAll();
      
      foreach ($recent_tenders as $tender) {
        $activities[] = [
          'type' => 'tender',
          'message' => 'Tender "' . $tender->title . '" was ' . $tender->status,
          'time' => date('M j, Y g:i A', strtotime($tender->created_at)),
        ];
      }
      
      return new JsonResponse([
        'success' => TRUE,
        'activities' => $activities,
      ]);
      
    } catch (\Exception $e) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Error loading activities: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * AJAX: Get upcoming deadlines.
   */
  public function getUpcomingDeadlines(Request $request) {
    try {
      $deadlines = [];
      
      // Get tenders with upcoming deadlines
      $upcoming_tenders = $this->database->select('tenders', 't')
        ->fields('t', ['title', 'submission_deadline'])
        ->condition('submission_deadline', date('Y-m-d H:i:s'), '>')
        ->condition('status', 'published')
        ->orderBy('submission_deadline', 'ASC')
        ->range(0, 5)
        ->execute()
        ->fetchAll();
      
      foreach ($upcoming_tenders as $tender) {
        $deadlines[] = [
          'title' => $tender->title,
          'deadline' => date('M j, Y g:i A', strtotime($tender->submission_deadline)),
        ];
      }
      
      return new JsonResponse([
        'success' => TRUE,
        'deadlines' => $deadlines,
      ]);
      
    } catch (\Exception $e) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Error loading deadlines: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * AJAX: Get all tenders.
   */
  public function getTenders(Request $request) {
    try {
      $query = $this->database->select('tenders', 't')
        ->fields('t')
        ->orderBy('created_at', 'DESC');
      
      $tenders = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
      
      // Format the data for display
      $formatted_tenders = [];
      foreach ($tenders as $tender) {
        $formatted_tenders[] = [
          'id' => $tender['id'],
          'tender_number' => $tender['tender_number'],
          'title' => $tender['title'],
          'description' => $tender['description'],
          'type' => $tender['type'],
          'category' => $tender['category'],
          'total_episodes' => $tender['total_episodes'],
          'total_budget' => number_format($tender['total_budget'], 2),
          'submission_deadline' => $tender['submission_deadline'],
          'status' => $tender['status'],
          'created_at' => date('M j, Y', strtotime($tender['created_at'])),
          'created_by' => $tender['created_by'],
        ];
      }
      
      return new JsonResponse([
        'success' => TRUE,
        'tenders' => $formatted_tenders,
        'total' => count($formatted_tenders),
      ]);
      
    } catch (\Exception $e) {
      $this->loggerFactory->get('tender_management')
        ->error('Error loading tenders: @error', ['@error' => $e->getMessage()]);
      
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Error loading tenders: ' . $e->getMessage(),
      ], 500);
    }
  }

}