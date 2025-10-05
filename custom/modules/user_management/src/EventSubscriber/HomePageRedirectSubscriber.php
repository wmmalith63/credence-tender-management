<?php

namespace Drupal\user_management\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirects anonymous users from home to auth page.
 */
class HomePageRedirectSubscriber implements EventSubscriberInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructor.
   */
  public function __construct(AccountInterface $current_user, RouteMatchInterface $route_match) {
    $this->currentUser = $current_user;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['redirectAnonymousUser', 28];
    return $events;
  }

  /**
   * Redirects anonymous users from homepage to auth page.
   */
  public function redirectAnonymousUser(RequestEvent $event) {
    $request = $event->getRequest();
    $route_name = $this->routeMatch->getRouteName();
    
    // Only redirect on the front page and if user is anonymous
    if ($route_name === 'system.404' || $route_name === '<front>' || $request->getPathInfo() === '/') {
      if ($this->currentUser->isAnonymous()) {
        $auth_url = Url::fromRoute('user_management.login_register')->toString();
        $response = new RedirectResponse($auth_url);
        $event->setResponse($response);
      }
    }
  }

}