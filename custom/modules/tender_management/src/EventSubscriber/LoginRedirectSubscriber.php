<?php

namespace Drupal\tender_management\EventSubscriber;

use Drupal\Core\EventSubscriber\RedirectResponseSubscriber;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirects users after login based on their roles.
 */
class LoginRedirectSubscriber implements EventSubscriberInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new LoginRedirectSubscriber.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Priority should be higher than Drupal's RedirectResponseSubscriber.
    $events[KernelEvents::REQUEST][] = ['checkForRedirection', 35];
    return $events;
  }

  /**
   * Redirects users after login based on their roles.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The request event.
   */
  public function checkForRedirection(RequestEvent $event) {
    $request = $event->getRequest();
    
    // Only process GET requests.
    if ($request->getMethod() !== 'GET') {
      return;
    }

    // Check if this is a post-login redirect (user just logged in).
    $current_path = $request->getPathInfo();
    
    // Only redirect from the home page or user pages after login.
    if (!in_array($current_path, ['/', '/user', '/user/login']) && 
        !preg_match('#^/user/\d+/?$#', $current_path)) {
      return;
    }

    // Don't redirect anonymous users.
    if ($this->currentUser->isAnonymous()) {
      return;
    }

    // Get user roles.
    $user_roles = $this->currentUser->getRoles();
    
    // Define role-based redirections.
    $redirections = [
      'ukk' => '/tender-management',
      'jpsd' => '/tender-management', 
      'panel' => '/tender-management',
      'vendor' => '/user-management/dashboard',
      'content_producer' => '/user-management/dashboard',
      'administrator' => '/tender-management',
      'tender_admin' => '/tender-management',
    ];

    // Check for role-based redirection.
    foreach ($redirections as $role => $redirect_path) {
      if (in_array($role, $user_roles)) {
        // Create redirect response.
        $redirect_url = Url::fromUserInput($redirect_path)->toString();
        $response = new RedirectResponse($redirect_url, 302);
        $event->setResponse($response);
        return;
      }
    }
  }
}