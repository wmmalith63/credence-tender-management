<?php

namespace Drupal\user_management\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Password\PasswordGeneratorInterface;
use Drupal\Core\Password\PasswordInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

/**
 * Custom login and registration form.
 */
class LoginRegistrationForm extends FormBase {

  /**
   * The password hasher.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $passwordHasher;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructor.
   */
  public function __construct(PasswordInterface $password_hasher, AccountInterface $current_user) {
    $this->passwordHasher = $password_hasher;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('password'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_management_login_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
    $form['#attached']['library'][] = 'user_management/login_registration';
    
    // Add inline styles to ensure visibility
    $form['#attached']['html_head'][] = [
      [
        '#tag' => 'style',
        '#value' => '
          .login-registration-container { 
            display: flex !important; 
            max-width: 1200px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #ffffff; 
            border-radius: 10px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); 
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
          }
          .login-section, .registration-section { 
            flex: 1; 
            padding: 40px; 
            margin: 0 10px; 
          }
          .login-section { 
            background: #f8f9fa; 
            border-radius: 10px 0 0 10px; 
            border-right: 2px solid #e9ecef; 
          }
          .registration-section { 
            background: #ffffff; 
            border-radius: 0 10px 10px 0; 
          }
          .section-title { 
            color: #2c3e50; 
            font-size: 28px; 
            font-weight: 600; 
            margin-bottom: 20px; 
            text-align: center; 
            border-bottom: 3px solid #3498db; 
            padding-bottom: 10px; 
          }
        ',
      ],
      'inline-login-styles'
    ];
    
    $form['container'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['login-registration-container']],
    ];

    // Login Section (Left Side)
    $form['container']['login_section'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['login-section']],
    ];

    $form['container']['login_section']['login_title'] = [
      '#markup' => '<h2 class="section-title">Login</h2>',
    ];

    $form['container']['login_section']['login_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => FALSE,
      '#attributes' => ['class' => ['login-email'], 'placeholder' => 'Enter your email'],
    ];

    $form['container']['login_section']['login_password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => FALSE,
      '#attributes' => ['class' => ['login-password'], 'placeholder' => 'Enter your password'],
    ];

    $form['container']['login_section']['login_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Login'),
      '#name' => 'login',
      '#attributes' => ['class' => ['btn', 'btn-login']],
    ];

    $form['container']['login_section']['forgot_password'] = [
      '#markup' => '<p class="forgot-password"><a href="/user/password">Forgot Password?</a></p>',
    ];

    $form['container']['login_section']['register_link'] = [
      '#markup' => '<div class="register-link"><button type="button" class="btn btn-register-link" onclick="showRegisterForm()">New User? Register Here</button></div>',
    ];

    // Registration Section (Right Side)
    $form['container']['registration_section'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['registration-section']],
    ];

    $form['container']['registration_section']['register_title'] = [
      '#markup' => '<h2 class="section-title">Registration</h2>',
    ];

    $form['container']['registration_section']['register_instructions'] = [
      '#markup' => '<p class="instructions">Please fill in an active email address and create a password of at least eight (8) characters with a combination of numbers and letters.</p>',
    ];

    $form['container']['registration_section']['register_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => FALSE,
      '#attributes' => ['class' => ['register-email'], 'placeholder' => 'Enter your email'],
    ];

    $form['container']['registration_section']['register_password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => FALSE,
      '#attributes' => ['class' => ['register-password'], 'placeholder' => 'Password (minimum 8 characters with letters and numbers)'],
    ];

    $form['container']['registration_section']['register_confirm_password'] = [
      '#type' => 'password',
      '#title' => $this->t('Confirm Password'),
      '#required' => FALSE,
      '#attributes' => ['class' => ['register-confirm-password'], 'placeholder' => 'Confirm your password'],
    ];

    $form['container']['registration_section']['register_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
      '#name' => 'register',
      '#attributes' => ['class' => ['btn', 'btn-register']],
    ];

    $form['container']['registration_section']['login_link'] = [
      '#markup' => '<p class="login-link">Already have an account? <button type="button" class="link-btn" onclick="showLoginForm()">Sign In Here</button></p>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    
    if ($triggering_element['#name'] == 'login') {
      // Validate login
      $email = $form_state->getValue('login_email');
      $password = $form_state->getValue('login_password');
      
      if (empty($email)) {
        $form_state->setErrorByName('login_email', $this->t('Email is required.'));
      }
      if (empty($password)) {
        $form_state->setErrorByName('login_password', $this->t('Password is required.'));
      }
    }
    
    if ($triggering_element['#name'] == 'register') {
      // Validate registration
      $email = $form_state->getValue('register_email');
      $password = $form_state->getValue('register_password');
      $confirm_password = $form_state->getValue('register_confirm_password');
      
      if (empty($email)) {
        $form_state->setErrorByName('register_email', $this->t('Email is required.'));
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_state->setErrorByName('register_email', $this->t('Please enter a valid email address.'));
      } else {
        // Check if email already exists
        $existing_user = user_load_by_mail($email);
        if ($existing_user) {
          $form_state->setErrorByName('register_email', $this->t('An account with this email already exists.'));
        }
      }
      
      if (empty($password)) {
        $form_state->setErrorByName('register_password', $this->t('Password is required.'));
      } elseif (strlen($password) < 8) {
        $form_state->setErrorByName('register_password', $this->t('Password must be at least 8 characters long.'));
      } elseif (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)/', $password)) {
        $form_state->setErrorByName('register_password', $this->t('Password must contain both letters and numbers.'));
      }
      
      if ($password !== $confirm_password) {
        $form_state->setErrorByName('register_confirm_password', $this->t('Passwords do not match.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    if ($triggering_element['#name'] == 'login') {
      $this->handleLogin($form, $form_state);
    } elseif ($triggering_element['#name'] == 'register') {
      $this->handleRegistration($form, $form_state);
    }
  }

  /**
   * Handle user login.
   */
  private function handleLogin(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('login_email');
    $password = $form_state->getValue('login_password');
    
    // Debug logging
    \Drupal::logger('user_management')->info('Login attempt for email: @email', ['@email' => $email]);
    
    $user = user_load_by_mail($email);
    if ($user) {
      \Drupal::logger('user_management')->info('User found for email: @email', ['@email' => $email]);
      
      if ($this->passwordHasher->check($password, $user->getPassword())) {
        \Drupal::logger('user_management')->info('Password correct for user: @email', ['@email' => $email]);
        user_login_finalize($user);
        $this->messenger()->addMessage($this->t('Login successful. Welcome to e-TVCMS Dashboard!'));
        $form_state->setRedirect('user_management.dashboard');
      } else {
        \Drupal::logger('user_management')->warning('Invalid password for user: @email', ['@email' => $email]);
        $this->messenger()->addError($this->t('Invalid email or password.'));
      }
    } else {
      \Drupal::logger('user_management')->warning('User not found for email: @email', ['@email' => $email]);
      $this->messenger()->addError($this->t('Invalid email or password.'));
    }
  }

  /**
   * Handle user registration.
   */
  private function handleRegistration(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('register_email');
    $password = $form_state->getValue('register_password');
    
    try {
      $user = User::create([
        'name' => $email,
        'mail' => $email,
        'pass' => $password,
        'status' => 1,
        // Don't specify roles - authenticated users get 'authenticated' role automatically
      ]);
      $user->save();
      
      $this->messenger()->addMessage($this->t('Registration successful! You can now log in with your credentials.'));
      
      // Clear form values
      $form_state->setValue('register_email', '');
      $form_state->setValue('register_password', '');
      $form_state->setValue('register_confirm_password', '');
      
      // Optionally auto-login the user after registration
      user_login_finalize($user);
      $form_state->setRedirect('user_management.dashboard');
      
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('Registration failed: @error', ['@error' => $e->getMessage()]));
      \Drupal::logger('user_management')->error('Registration failed: @error', ['@error' => $e->getMessage()]);
    }
  }

}