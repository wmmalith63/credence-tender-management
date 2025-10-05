<?php

namespace Drupal\user_management\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Company Details Form similar to etvcms.rtm.gov.my.
 */
class CompanyDetailsForm extends FormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructor.
   */
  public function __construct(Connection $database, AccountInterface $current_user) {
    $this->database = $database;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_management_company_details_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
    $form['#attached']['library'][] = 'user_management/company_details';
    
    // Check if this is an AJAX request
    $request = \Drupal::request();
    $is_ajax = $request->isXmlHttpRequest();
    
    // If it's an AJAX request, return minimal form without wrapper
    if ($is_ajax) {
      return $this->buildCompanyForm($form, $form_state);
    }
    
    // Main container for full page view
    $form['company_container'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['company-details-container']],
    ];

    // Header section
    $form['company_container']['header'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['page-header']],
      '#markup' => '
        <div class="page-title-section">
          <h1 class="page-title">COMPANY DETAILS</h1>
          <div class="breadcrumb">
            <span>Account Menu</span> > <span class="current">Company Details</span>
          </div>
        </div>
      ',
    ];

    // Purpose statement
    $form['company_container']['purpose'] = [
      '#markup' => '<div class="purpose-statement">
        <p>• Completing Company Details is necessary to meet tender eligibility requirements</p>
      </div>',
    ];

    // Add the actual form content
    $form['company_container'] = array_merge($form['company_container'], $this->buildCompanyForm($form, $form_state));

    return $form;
  }
  
  /**
   * Build the actual company form content.
   */
  private function buildCompanyForm(array $form, FormStateInterface $form_state) {
    $company_form = [];

    // Company Information Section
    $company_form['company_info'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Company Information'),
      '#attributes' => ['class' => ['company-info-section']],
    ];

    $company_form['company_info']['company_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company Name'),
      '#required' => TRUE,
      '#attributes' => ['class' => ['form-control']],
    ];

    $company_form['company_info']['registration_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Business Registration Number'),
      '#required' => TRUE,
      '#attributes' => ['class' => ['form-control']],
    ];

    $company_form['company_info']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#attributes' => ['class' => ['form-control']],
    ];

    $company_form['company_info']['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#required' => TRUE,
      '#attributes' => ['class' => ['form-control']],
    ];

    $company_form['company_info']['address'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Address'),
      '#required' => TRUE,
      '#rows' => 3,
      '#attributes' => ['class' => ['form-control']],
    ];

    $company_form['company_info']['postal_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Postal Code'),
      '#required' => TRUE,
      '#attributes' => ['class' => ['form-control']],
    ];

    $company_form['company_info']['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#required' => TRUE,
      '#attributes' => ['class' => ['form-control']],
    ];

    $company_form['company_info']['state'] = [
      '#type' => 'select',
      '#title' => $this->t('State'),
      '#required' => TRUE,
      '#options' => [
        '' => '- Select State -',
        'johor' => 'Johor',
        'kedah' => 'Kedah',
        'kelantan' => 'Kelantan',
        'kuala_lumpur' => 'Kuala Lumpur',
        'labuan' => 'Labuan',
        'melaka' => 'Melaka',
        'negeri_sembilan' => 'Negeri Sembilan',
        'pahang' => 'Pahang',
        'penang' => 'Penang',
        'perak' => 'Perak',
        'perlis' => 'Perlis',
        'putrajaya' => 'Putrajaya',
        'sabah' => 'Sabah',
        'sarawak' => 'Sarawak',
        'selangor' => 'Selangor',
        'terengganu' => 'Terengganu',
      ],
      '#attributes' => ['class' => ['form-control']],
    ];

    // Board of Directors Section
    $company_form['directors'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Board of Directors'),
      '#attributes' => ['class' => ['directors-section']],
    ];

    $company_form['directors']['directors_table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Position'),
        $this->t('IC/Passport'),
        $this->t('Actions'),
      ],
      '#attributes' => ['class' => ['directors-table']],
      '#empty' => $this->t('No directors added yet.'),
    ];

    $company_form['directors']['add_director'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Director'),
      '#name' => 'add_director',
      '#attributes' => ['class' => ['btn', 'btn-add-director']],
    ];

    // Submit button
    $company_form['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['class' => ['form-actions']],
    ];

    $company_form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Company Details'),
      '#name' => 'save',
      '#attributes' => ['class' => ['btn', 'btn-primary', 'btn-save']],
    ];

    return $company_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate company name
    $company_name = $form_state->getValue('company_name');
    if (empty($company_name)) {
      $form_state->setErrorByName('company_name', $this->t('Company name is required.'));
    }

    // Validate registration number
    $registration_number = $form_state->getValue('registration_number');
    if (empty($registration_number)) {
      $form_state->setErrorByName('registration_number', $this->t('Business registration number is required.'));
    }

    // Validate email
    $email = $form_state->getValue('email');
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('email', $this->t('Please enter a valid email address.'));
    }

    // Validate phone
    $phone = $form_state->getValue('phone');
    if (empty($phone)) {
      $form_state->setErrorByName('phone', $this->t('Phone number is required.'));
    }

    // Validate postal code (Malaysian format)
    $postal_code = $form_state->getValue('postal_code');
    if (empty($postal_code) || !preg_match('/^\d{5}$/', $postal_code)) {
      $form_state->setErrorByName('postal_code', $this->t('Please enter a valid 5-digit postal code.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    
    if ($triggering_element['#name'] == 'save') {
      $this->saveCompanyDetails($form, $form_state);
    } elseif ($triggering_element['#name'] == 'add_director') {
      $this->addDirector($form, $form_state);
    }
  }

  /**
   * Save company details to database.
   */
  private function saveCompanyDetails(array &$form, FormStateInterface $form_state) {
    $user_id = $this->currentUser->id();
    
    try {
      // Check if company details already exist
      $existing = $this->database->select('content_producers', 'cp')
        ->fields('cp', ['id'])
        ->condition('uid', $user_id)
        ->execute()
        ->fetchField();

      $company_data = [
        'uid' => $user_id,
        'company_name' => $form_state->getValue('company_name'),
        'business_registration_number' => $form_state->getValue('registration_number'),
        'email' => $form_state->getValue('email'),
        'phone' => $form_state->getValue('phone'),
        'address' => $form_state->getValue('address'),
        'postal_code' => $form_state->getValue('postal_code'),
        'city' => $form_state->getValue('city'),
        'state' => $form_state->getValue('state'),
        'country' => 'Malaysia',
        'updated_at' => date('Y-m-d H:i:s'),
      ];

      if ($existing) {
        // Update existing record
        $this->database->update('content_producers')
          ->fields($company_data)
          ->condition('uid', $user_id)
          ->execute();
        $this->messenger()->addMessage($this->t('Company details updated successfully.'));
      } else {
        // Insert new record
        $company_data['created_at'] = date('Y-m-d H:i:s');
        $this->database->insert('content_producers')
          ->fields($company_data)
          ->execute();
        $this->messenger()->addMessage($this->t('Company details saved successfully.'));
      }

      // Redirect to dashboard
      $form_state->setRedirect('user_management.dashboard');
      
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('Failed to save company details: @error', ['@error' => $e->getMessage()]));
      \Drupal::logger('user_management')->error('Company details save failed: @error', ['@error' => $e->getMessage()]);
    }
  }

  /**
   * Add director functionality.
   */
  private function addDirector(array &$form, FormStateInterface $form_state) {
    // Rebuild form to add director fields
    $form_state->setRebuild();
    $this->messenger()->addMessage($this->t('Director form will be added here.'));
  }

}