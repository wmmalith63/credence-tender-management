<?php

namespace Drupal\tender_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * AJAX controller for tender management operations.
 */
class TenderAjaxController extends ControllerBase {

  /**
   * AJAX handler for saving tender data.
   * Handles both UKK and Admin tender submissions.
   */
  public function saveTender(Request $request) {
    // Log the request for debugging
    $this->getLogger('tender_management')->info('AJAX saveTender called. Method: @method', [
      '@method' => $request->getMethod(),
    ]);
    
    $current_user = $this->currentUser();
    
    // Check if user is authenticated
    if ($current_user->isAnonymous()) {
      $this->getLogger('tender_management')->warning('Anonymous user attempted to save tender');
      return new JsonResponse(['success' => false, 'message' => 'Access denied - user not logged in']);
    }
    
    $this->getLogger('tender_management')->info('User @uid attempting to save tender', [
      '@uid' => $current_user->id(),
    ]);
    
    try {
      // Get form data
      $tender_number = $request->request->get('tender_number');
      $tender_title = $request->request->get('tender_title');
      $tender_description = $request->request->get('tender_description');
      $tender_type = $request->request->get('tender_type');
      $tender_category = $request->request->get('tender_category');
      $episode_duration = $request->request->get('episode_duration');
      $total_episodes = $request->request->get('total_episodes');
      $budget_per_episode = $request->request->get('budget_per_episode');
      $total_budget = $request->request->get('total_budget');
      $submission_deadline = $request->request->get('submission_deadline');
      $evaluation_period = $request->request->get('evaluation_period');
      $production_start = $request->request->get('production_start');
      $production_end = $request->request->get('production_end');
      $technical_requirements = $request->request->get('technical_requirements');
      $content_requirements = $request->request->get('content_requirements');
      $status = $request->request->get('status'); // 'draft', 'published', 'ukk_saved'
      
      // Handle required_documents array properly
      $required_documents = [];
      $all_request_data = $request->request->all();
      if (isset($all_request_data['required_documents'])) {
        $required_documents = is_array($all_request_data['required_documents']) 
          ? $all_request_data['required_documents'] 
          : [$all_request_data['required_documents']];
      }
      
      // Validate required fields
      if (empty($tender_number) || empty($tender_title) || empty($tender_type)) {
        return new JsonResponse(['success' => false, 'message' => 'Required fields are missing']);
      }
      
      // Get database connection
      $connection = Database::getConnection();
      
      // Check if tender already exists (for updates)
      $existing_tender = $connection->select('tenders', 't')
        ->fields('t', ['id'])
        ->condition('tender_number', $tender_number)
        ->execute()
        ->fetchField();
      
      // Determine final status based on user role and submitted status
      $user_roles = $current_user->getRoles();
      $final_status = 'draft'; // default
      
      if (in_array('ukk', $user_roles) && $status === 'ukk_saved') {
        $final_status = 'pending_approval';
      } elseif (in_array('administrator', $user_roles)) {
        $final_status = $status; // admin can set any status
      }
      
      // Prepare tender data
      $tender_data = [
        'title' => $tender_title,
        'description' => $tender_description,
        'tender_number' => $tender_number,
        'type' => $tender_type,
        'category' => $tender_category,
        'episode_duration' => $episode_duration,
        'total_episodes' => (int)$total_episodes,
        'budget_per_episode' => (float)$budget_per_episode,
        'total_budget' => (float)$total_budget,
        'submission_deadline' => $submission_deadline ? date('Y-m-d H:i:s', strtotime($submission_deadline)) : null,
        'evaluation_period' => $evaluation_period,
        'production_start' => $production_start ? date('Y-m-d', strtotime($production_start)) : null,
        'production_end' => $production_end ? date('Y-m-d', strtotime($production_end)) : null,
        'technical_requirements' => $technical_requirements,
        'content_requirements' => $content_requirements,
        'status' => $final_status,
        'created_by' => $current_user->id(),
        'updated_at' => date('Y-m-d H:i:s'),
      ];
      
      // Set published_at for published tenders
      if ($final_status === 'published') {
        $tender_data['published_at'] = date('Y-m-d H:i:s');
      }
      
      $tender_id = null;
      
      if ($existing_tender) {
        // Update existing tender
        $connection->update('tenders')
          ->fields($tender_data)
          ->condition('id', $existing_tender)
          ->execute();
        $tender_id = $existing_tender;
      } else {
        // Insert new tender
        $tender_data['created_at'] = date('Y-m-d H:i:s');
        $tender_id = $connection->insert('tenders')
          ->fields($tender_data)
          ->execute();
      }
      
      // Save required documents selection
      if (!empty($required_documents) && $tender_id) {
        // Delete existing required documents for this tender
        $connection->delete('tender_required_documents')
          ->condition('tender_id', $tender_id)
          ->execute();
        
        // Insert new required documents
        $insert = $connection->insert('tender_required_documents')
          ->fields(['tender_id', 'document_type', 'is_required', 'created_at']);
        
        foreach ($required_documents as $doc_type) {
          $insert->values([
            'tender_id' => $tender_id,
            'document_type' => $doc_type,
            'is_required' => 1,
            'created_at' => date('Y-m-d H:i:s'),
          ]);
        }
        $insert->execute();
      }
      
      // Log the action
      $this->getLogger('tender_management')->info('Tender @action: @title (ID: @id, Status: @status) by user @uid', [
        '@action' => $existing_tender ? 'updated' : 'created',
        '@title' => $tender_title,
        '@id' => $tender_id,
        '@status' => $final_status,
        '@uid' => $current_user->id(),
      ]);
      
      return new JsonResponse([
        'success' => true, 
        'message' => 'Tender saved successfully',
        'tender_id' => $tender_id,
        'status' => $final_status
      ]);
      
    } catch (\Exception $e) {
      // Log the error
      $this->getLogger('tender_management')->error('Error saving tender: @message', [
        '@message' => $e->getMessage(),
      ]);
      
      return new JsonResponse([
        'success' => false, 
        'message' => 'An error occurred while saving the tender: ' . $e->getMessage()
      ]);
    }
  }

  /**
   * AJAX handler for saving vendor applications.
   */
  public function saveVendorApplication(Request $request) {
    $current_user = $this->currentUser();
    
    // Check if user is authenticated and is a vendor
    if ($current_user->isAnonymous() || !in_array('vendor', $current_user->getRoles())) {
      return new JsonResponse(['success' => false, 'message' => 'Access denied']);
    }
    
    try {
      // Get form data
      $tender_id = $request->request->get('selected_tender');
      $company_name = $request->request->get('company_name');
      $company_reg_no = $request->request->get('company_reg_no');
      $contact_person = $request->request->get('contact_person');
      $contact_email = $request->request->get('contact_email');
      $additional_notes = $request->request->get('additional_notes');
      
      // Validate required fields
      if (empty($tender_id) || empty($company_name) || empty($contact_person)) {
        return new JsonResponse(['success' => false, 'message' => 'Required fields are missing']);
      }
      
      // Get database connection
      $connection = Database::getConnection();
      
      // Check if application already exists
      $existing_application = $connection->select('vendor_applications', 'va')
        ->fields('va', ['id'])
        ->condition('tender_id', $tender_id)
        ->condition('vendor_uid', $current_user->id())
        ->execute()
        ->fetchField();
      
      if ($existing_application) {
        return new JsonResponse(['success' => false, 'message' => 'You have already applied for this tender']);
      }
      
      // Insert vendor application
      $application_data = [
        'tender_id' => $tender_id,
        'vendor_uid' => $current_user->id(),
        'company_name' => $company_name,
        'company_reg_no' => $company_reg_no,
        'contact_person' => $contact_person,
        'contact_email' => $contact_email,
        'additional_notes' => $additional_notes,
        'application_status' => 'submitted',
        'submitted_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
      ];
      
      $application_id = $connection->insert('vendor_applications')
        ->fields($application_data)
        ->execute();
      
      // Log the action
      $this->getLogger('tender_management')->info('Vendor application submitted: Tender @tender_id by user @uid', [
        '@tender_id' => $tender_id,
        '@uid' => $current_user->id(),
      ]);
      
      return new JsonResponse([
        'success' => true, 
        'message' => 'Application submitted successfully',
        'application_id' => $application_id
      ]);
      
    } catch (\Exception $e) {
      // Log the error
      $this->getLogger('tender_management')->error('Error saving vendor application: @message', [
        '@message' => $e->getMessage(),
      ]);
      
      return new JsonResponse([
        'success' => false, 
        'message' => 'An error occurred while submitting your application: ' . $e->getMessage()
      ]);
    }
  }

  /**
   * Get list of tenders for management interface.
   */
  public function getTendersList(Request $request) {
    try {
      $connection = Database::getConnection();
      $current_user = $this->currentUser();
      
      // Build query based on user role
      $query = $connection->select('tenders', 't')
        ->fields('t');
      
      // Filter based on user role
      if ($current_user->hasRole('ukk')) {
        // UKK users can only see their own tenders
        $query->condition('created_by', $current_user->id());
      } elseif ($current_user->hasRole('jpsd') || $current_user->hasRole('panel') || $current_user->hasRole('evaluator')) {
        // JPSD, Panel, and Evaluator users can only see published tenders
        $query->condition('status', ['published', 'closed'], 'IN');
      }
      // Admin users see all tenders (no additional filter needed)
      
      $query->orderBy('created_at', 'DESC');
      $results = $query->execute()->fetchAll();
      
      $tenders = [];
      foreach ($results as $row) {
        $tenders[] = [
          'id' => $row->id,
          'tender_number' => $row->tender_number,
          'title' => $row->title,
          'description' => $row->description,
          'type' => $row->type,
          'category' => $row->category,
          'status' => $row->status,
          'total_episodes' => $row->total_episodes,
          'total_budget' => $row->total_budget,
          'submission_deadline' => $row->submission_deadline,
          'created_at' => date('M j, Y', strtotime($row->created_at)),
        ];
      }
      
      return new JsonResponse([
        'success' => true,
        'tenders' => $tenders
      ]);
      
    } catch (\Exception $e) {
      $this->getLogger('tender_management')->error('Error fetching tenders list: @error', ['@error' => $e->getMessage()]);
      return new JsonResponse([
        'success' => false,
        'message' => 'Error fetching tenders list'
      ]);
    }
  }

  /**
   * Get tender details for viewing.
   */
  public function getTenderDetails($tender_id, Request $request) {
    try {
      $connection = Database::getConnection();
      $current_user = $this->currentUser();
      
      // Get tender by ID or tender_number
      $query = $connection->select('tenders', 't')
        ->fields('t');
      
      // Check if tender_id is numeric (database ID) or alphanumeric (tender_number)
      if (is_numeric($tender_id)) {
        $query->condition('id', $tender_id);
      } else {
        $query->condition('tender_number', $tender_id);
      }
      
      $tender = $query->execute()->fetchObject();
      
      if (!$tender) {
        return new JsonResponse([
          'success' => false,
          'message' => 'Tender not found'
        ]);
      }
      
      // Check access permissions
      $can_edit = FALSE;
      if ($current_user->hasRole('administrator') || 
          ($current_user->hasRole('ukk') && $tender->created_by == $current_user->id())) {
        $can_edit = TRUE;
      }
      
      // Get required documents
      $doc_query = $connection->select('tender_required_documents', 'trd')
        ->fields('trd', ['document_type'])
        ->condition('tender_id', $tender->id);
      $required_docs = $doc_query->execute()->fetchCol();
      
      $tender_data = [
        'id' => $tender->id,
        'tender_number' => $tender->tender_number,
        'tender_title' => $tender->title,
        'tender_description' => $tender->description,
        'tender_type' => $tender->type,
        'tender_category' => $tender->category,
        'episode_duration' => $tender->episode_duration,
        'total_episodes' => $tender->total_episodes,
        'budget_per_episode' => $tender->budget_per_episode,
        'total_budget' => $tender->total_budget,
        'submission_deadline' => $tender->submission_deadline,
        'evaluation_period' => $tender->evaluation_period,
        'production_start' => $tender->production_start,
        'production_end' => $tender->production_end,
        'status' => $tender->status,
        'required_documents' => $required_docs,
        'created_at' => $tender->created_at,
      ];
      
      return new JsonResponse([
        'success' => true,
        'tender' => $tender_data,
        'can_edit' => $can_edit
      ]);
      
    } catch (\Exception $e) {
      $this->getLogger('tender_management')->error('Error fetching tender details: @error', ['@error' => $e->getMessage()]);
      return new JsonResponse([
        'success' => false,
        'message' => 'Error fetching tender details'
      ]);
    }
  }

  /**
   * Get tender data for editing.
   */
  public function getTenderForEdit($tender_id, Request $request) {
    try {
      $connection = Database::getConnection();
      $current_user = $this->currentUser();
      
      // Get tender by ID or tender_number
      $query = $connection->select('tenders', 't')
        ->fields('t');
      
      // Check if tender_id is numeric (database ID) or alphanumeric (tender_number)
      if (is_numeric($tender_id)) {
        $query->condition('id', $tender_id);
      } else {
        $query->condition('tender_number', $tender_id);
      }
      
      $tender = $query->execute()->fetchObject();
      
      if (!$tender) {
        return new JsonResponse([
          'success' => false,
          'message' => 'Tender not found'
        ]);
      }
      
      // Check edit permissions
      if (!$current_user->hasRole('administrator') && 
          !($current_user->hasRole('ukk') && $tender->created_by == $current_user->id())) {
        return new JsonResponse([
          'success' => false,
          'message' => 'Access denied - you cannot edit this tender'
        ]);
      }
      
      // Get required documents
      $doc_query = $connection->select('tender_required_documents', 'trd')
        ->fields('trd', ['document_type'])
        ->condition('tender_id', $tender->id);
      $required_docs = $doc_query->execute()->fetchCol();
      
      $tender_data = [
        'id' => $tender->id,
        'tender_number' => $tender->tender_number,
        'tender_title' => $tender->title,
        'tender_description' => $tender->description,
        'tender_type' => $tender->type,
        'tender_category' => $tender->category,
        'episode_duration' => $tender->episode_duration,
        'total_episodes' => $tender->total_episodes,
        'budget_per_episode' => $tender->budget_per_episode,
        'total_budget' => $tender->total_budget,
        'submission_deadline' => $tender->submission_deadline,
        'evaluation_period' => $tender->evaluation_period,
        'production_start' => $tender->production_start,
        'production_end' => $tender->production_end,
        'status' => $tender->status,
        'required_documents' => $required_docs,
      ];
      
      return new JsonResponse([
        'success' => true,
        'tender' => $tender_data
      ]);
      
    } catch (\Exception $e) {
      $this->getLogger('tender_management')->error('Error fetching tender for edit: @error', ['@error' => $e->getMessage()]);
      return new JsonResponse([
        'success' => false,
        'message' => 'Error fetching tender for editing'
      ]);
    }
  }

  /**
   * Update existing tender.
   */
  public function updateTender(Request $request) {
    try {
      $connection = Database::getConnection();
      $current_user = $this->currentUser();
      
      $tender_id = $request->request->get('tender_id');
      $is_edit_mode = $request->request->get('edit_mode');
      
      if (!$tender_id || !$is_edit_mode) {
        return new JsonResponse([
          'success' => false,
          'message' => 'Missing tender ID or edit mode flag'
        ]);
      }
      
      // Get existing tender to check permissions
      $query = $connection->select('tenders', 't')
        ->fields('t', ['id', 'created_by'])
        ->condition('tender_number', $tender_id);
      $existing_tender = $query->execute()->fetchObject();
      
      if (!$existing_tender) {
        return new JsonResponse([
          'success' => false,
          'message' => 'Tender not found'
        ]);
      }
      
      // Check edit permissions
      if (!$current_user->hasRole('administrator') && 
          !($current_user->hasRole('ukk') && $existing_tender->created_by == $current_user->id())) {
        return new JsonResponse([
          'success' => false,
          'message' => 'Access denied - you cannot edit this tender'
        ]);
      }
      
      // Get form data
      $tender_data = [
        'title' => $request->request->get('tender_title'),
        'description' => $request->request->get('tender_description'),
        'type' => $request->request->get('tender_type'),
        'category' => $request->request->get('tender_category'),
        'episode_duration' => $request->request->get('episode_duration'),
        'total_episodes' => $request->request->get('total_episodes'),
        'budget_per_episode' => $request->request->get('budget_per_episode'),
        'total_budget' => $request->request->get('total_budget'),
        'submission_deadline' => $request->request->get('submission_deadline'),
        'evaluation_period' => $request->request->get('evaluation_period'),
        'production_start' => $request->request->get('production_start'),
        'production_end' => $request->request->get('production_end'),
        'updated_at' => date('Y-m-d H:i:s'),
      ];
      
      // Update tender
      $connection->update('tenders')
        ->fields($tender_data)
        ->condition('id', $existing_tender->id)
        ->execute();
      
      // Update required documents
      $required_documents = $request->request->all()['required_documents'] ?? [];
      
      // Delete existing required documents
      $connection->delete('tender_required_documents')
        ->condition('tender_id', $existing_tender->id)
        ->execute();
      
      // Insert new required documents
      if (!empty($required_documents)) {
        foreach ($required_documents as $doc_type) {
          $connection->insert('tender_required_documents')
            ->fields([
              'tender_id' => $existing_tender->id,
              'document_type' => $doc_type,
              'created_at' => date('Y-m-d H:i:s'),
            ])
            ->execute();
        }
      }
      
      $this->getLogger('tender_management')->info('Tender @tender_id updated by user @uid', [
        '@tender_id' => $tender_id,
        '@uid' => $current_user->id(),
      ]);
      
      return new JsonResponse([
        'success' => true,
        'message' => 'Tender updated successfully',
        'tender_id' => $tender_id
      ]);
      
    } catch (\Exception $e) {
      $this->getLogger('tender_management')->error('Error updating tender: @error', ['@error' => $e->getMessage()]);
      return new JsonResponse([
        'success' => false,
        'message' => 'Error updating tender: ' . $e->getMessage()
      ]);
    }
  }

  /**
   * Publish a tender.
   */
  public function publishTender(Request $request) {
    try {
      $connection = Database::getConnection();
      $current_user = $this->currentUser();
      
      $tender_id = $request->request->get('tender_id');
      
      if (!$tender_id) {
        return new JsonResponse([
          'success' => false,
          'message' => 'Missing tender ID'
        ]);
      }
      
      // Check permissions - only admin can publish
      if (!$current_user->hasRole('administrator')) {
        return new JsonResponse([
          'success' => false,
          'message' => 'Access denied - only administrators can publish tenders'
        ]);
      }
      
      // Update tender status
      $connection->update('tenders')
        ->fields([
          'status' => 'published',
          'published_at' => date('Y-m-d H:i:s'),
          'published_by' => $current_user->id(),
        ])
        ->condition('tender_number', $tender_id)
        ->execute();
      
      return new JsonResponse([
        'success' => true,
        'message' => 'Tender published successfully'
      ]);
      
    } catch (\Exception $e) {
      return new JsonResponse([
        'success' => false,
        'message' => 'Error publishing tender: ' . $e->getMessage()
      ]);
    }
  }

  /**
   * Delete a tender.
   */
  public function deleteTender(Request $request) {
    try {
      $connection = Database::getConnection();
      $current_user = $this->currentUser();
      
      $tender_id = $request->request->get('tender_id');
      
      if (!$tender_id) {
        return new JsonResponse([
          'success' => false,
          'message' => 'Missing tender ID'
        ]);
      }
      
      // Get tender to check permissions
      $query = $connection->select('tenders', 't')
        ->fields('t', ['id', 'created_by'])
        ->condition('tender_number', $tender_id);
      $tender = $query->execute()->fetchObject();
      
      if (!$tender) {
        return new JsonResponse([
          'success' => false,
          'message' => 'Tender not found'
        ]);
      }
      
      // Check permissions
      if (!$current_user->hasRole('administrator') && 
          !($current_user->hasRole('ukk') && $tender->created_by == $current_user->id())) {
        return new JsonResponse([
          'success' => false,
          'message' => 'Access denied - you cannot delete this tender'
        ]);
      }
      
      // Delete related records first
      $connection->delete('tender_required_documents')
        ->condition('tender_id', $tender->id)
        ->execute();
      
      $connection->delete('tender_optional_documents')
        ->condition('tender_id', $tender->id)
        ->execute();
      
      // Delete tender
      $connection->delete('tenders')
        ->condition('id', $tender->id)
        ->execute();
      
      return new JsonResponse([
        'success' => true,
        'message' => 'Tender deleted successfully'
      ]);
      
    } catch (\Exception $e) {
      return new JsonResponse([
        'success' => false,
        'message' => 'Error deleting tender: ' . $e->getMessage()
      ]);
    }
  }

  /**
   * Close a tender.
   */
  public function closeTender(Request $request) {
    try {
      $connection = Database::getConnection();
      $current_user = $this->currentUser();
      
      $tender_id = $request->request->get('tender_id');
      
      if (!$tender_id) {
        return new JsonResponse([
          'success' => false,
          'message' => 'Missing tender ID'
        ]);
      }
      
      // Check permissions - only admin can close
      if (!$current_user->hasRole('administrator')) {
        return new JsonResponse([
          'success' => false,
          'message' => 'Access denied - only administrators can close tenders'
        ]);
      }
      
      // Update tender status
      $connection->update('tenders')
        ->fields([
          'status' => 'closed',
          'closed_at' => date('Y-m-d H:i:s'),
          'closed_by' => $current_user->id(),
        ])
        ->condition('tender_number', $tender_id)
        ->execute();
      
      return new JsonResponse([
        'success' => true,
        'message' => 'Tender closed successfully'
      ]);
      
    } catch (\Exception $e) {
      return new JsonResponse([
        'success' => false,
        'message' => 'Error closing tender: ' . $e->getMessage()
      ]);
    }
  }

}