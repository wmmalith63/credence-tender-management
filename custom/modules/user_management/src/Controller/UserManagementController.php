<?php

namespace Drupal\user_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for user management.
 */
class UserManagementController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructor.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Display user profile.
   */
  public function profile() {
    $current_user = $this->currentUser();
    $user = \Drupal\user\Entity\User::load($current_user->id());
    
    $build = [
      '#theme' => 'user_profile_page',
      '#user' => $user,
      '#attached' => [
        'library' => ['user_management/dashboard'],
      ],
    ];
    
    return $build;
  }

  /**
   * Display user dashboard.
   */
  public function dashboard() {
    $current_user = $this->currentUser();
    $user = \Drupal\user\Entity\User::load($current_user->id());
    
    $build = [
      '#theme' => 'user_dashboard',
      '#user' => $user,
      '#attached' => [
        'library' => ['user_management/dashboard'],
      ],
    ];
    
    return $build;
  }

  /**
   * Display registration form.
   */
  public function register() {
    $build = [
      '#markup' => '<h2>Producer Registration</h2><p>Registration form will appear here.</p>',
    ];
    
    return $build;
  }

  /**
   * Save company details via AJAX.
   */
  public function saveCompanyDetails() {
    $request = \Drupal::request();
    $current_user = $this->currentUser();
    $user_id = $current_user->id();
    
    if (!$request->isMethod('POST')) {
      return new \Symfony\Component\HttpFoundation\JsonResponse([
        'success' => false,
        'message' => 'Only POST requests allowed.'
      ], 405);
    }
    
    try {
      \Drupal::logger('user_management')->info('Company details save started for user @uid', ['@uid' => $user_id]);
      
      // Get form data
      $company_name = $request->request->get('company_name');
      $business_registration_number = $request->request->get('business_registration_number');
      $email = $request->request->get('email');
      $phone = $request->request->get('phone');
      $address = $request->request->get('address');
      $postal_code = $request->request->get('postal_code');
      $city = $request->request->get('city');
      $state = $request->request->get('state');
      
      \Drupal::logger('user_management')->info('Form data received: company=@company, email=@email', [
        '@company' => $company_name,
        '@email' => $email
      ]);
      
      // Validate required fields
      if (empty($company_name) || empty($business_registration_number) || empty($email) || empty($phone)) {
        \Drupal::logger('user_management')->error('Required fields validation failed');
        return new \Symfony\Component\HttpFoundation\JsonResponse([
          'success' => false,
          'message' => 'Please fill in all required fields.'
        ], 400);
      }
      
      // Validate email
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        \Drupal::logger('user_management')->error('Email validation failed: @email', ['@email' => $email]);
        return new \Symfony\Component\HttpFoundation\JsonResponse([
          'success' => false,
          'message' => 'Please enter a valid email address.'
        ], 400);
      }
      
      // Validate postal code (Malaysian format)
      if (!empty($postal_code) && !preg_match('/^\d{5}$/', $postal_code)) {
        \Drupal::logger('user_management')->error('Postal code validation failed: @postal', ['@postal' => $postal_code]);
        return new \Symfony\Component\HttpFoundation\JsonResponse([
          'success' => false,
          'message' => 'Please enter a valid 5-digit postal code.'
        ], 400);
      }
      
      // Check if company details already exist
      $existing = $this->database->select('content_producers', 'cp')
        ->fields('cp', ['id'])
        ->condition('uid', $user_id)
        ->execute()
        ->fetchField();

      \Drupal::logger('user_management')->info('Existing record found: @existing', ['@existing' => $existing ? 'Yes' : 'No']);

      $company_data = [
        'uid' => $user_id,
        'company_name' => $company_name,
        'business_registration_number' => $business_registration_number,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'postal_code' => $postal_code,
        'city' => $city,
        'state' => $state,
        'country' => 'Malaysia',
        'contact_person' => $company_name, // Use company name as contact person for now
        'updated_at' => date('Y-m-d H:i:s'),
      ];

      if ($existing) {
        // Update existing record
        \Drupal::logger('user_management')->info('Updating existing company record for user @uid', ['@uid' => $user_id]);
        
        $result = $this->database->update('content_producers')
          ->fields($company_data)
          ->condition('uid', $user_id)
          ->execute();
        
        \Drupal::logger('user_management')->info('Company details updated for user @uid, affected rows: @rows', [
          '@uid' => $user_id,
          '@rows' => $result
        ]);
        
        return new \Symfony\Component\HttpFoundation\JsonResponse([
          'success' => true,
          'message' => 'Company details updated successfully.'
        ]);
      } else {
        // Insert new record
        \Drupal::logger('user_management')->info('Creating new company record for user @uid', ['@uid' => $user_id]);
        
        $company_data['created_at'] = date('Y-m-d H:i:s');
        $company_data['certification_status'] = 'pending';
        
        $result = $this->database->insert('content_producers')
          ->fields($company_data)
          ->execute();
        
        \Drupal::logger('user_management')->info('Company details created for user @uid, insert ID: @id', [
          '@uid' => $user_id,
          '@id' => $result
        ]);
        
        return new \Symfony\Component\HttpFoundation\JsonResponse([
          'success' => true,
          'message' => 'Company details saved successfully.'
        ]);
      }
      
    } catch (\Exception $e) {
      \Drupal::logger('user_management')->error('Company details save failed: @error', ['@error' => $e->getMessage()]);
      
      return new \Symfony\Component\HttpFoundation\JsonResponse([
        'success' => false,
        'message' => 'Failed to save company details. Please try again.'
      ], 500);
    }
  }

  /**
   * Get existing company details via AJAX.
   */
  public function getCompanyDetails() {
    $current_user = $this->currentUser();
    $user_id = $current_user->id();
    
    try {
      // Get existing company details
      $company = $this->database->select('content_producers', 'cp')
        ->fields('cp')
        ->condition('uid', $user_id)
        ->execute()
        ->fetchAssoc();
      
      if ($company) {
        return new \Symfony\Component\HttpFoundation\JsonResponse([
          'success' => true,
          'data' => $company
        ]);
      } else {
        return new \Symfony\Component\HttpFoundation\JsonResponse([
          'success' => false,
          'message' => 'No company details found.'
        ]);
      }
      
    } catch (\Exception $e) {
      \Drupal::logger('user_management')->error('Failed to get company details: @error', ['@error' => $e->getMessage()]);
      
      return new \Symfony\Component\HttpFoundation\JsonResponse([
        'success' => false,
        'message' => 'Failed to load company details.'
      ], 500);
    }
  }

}