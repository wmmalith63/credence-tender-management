<?php
/**
 * Script to reset admin password
 */

// Bootstrap Drupal
use Drupal\user\Entity\User;

// Change to the Drupal root
chdir('/var/www/html');

// Autoloader
require_once 'autoload.php';

try {
  // Load Drupal
  $kernel = \Drupal\Core\DrupalKernel::createFromRequest(\Symfony\Component\HttpFoundation\Request::createFromGlobals(), 'prod', false);
  $kernel->boot();
  $kernel->preHandle(\Symfony\Component\HttpFoundation\Request::createFromGlobals());

  // Load admin user (UID 1)
  $admin = User::load(1);
  
  if ($admin) {
    // Set new password
    $new_password = 'admin123';
    $admin->setPassword($new_password);
    $admin->save();
    
    echo "✅ Admin password reset successfully!\n";
    echo "==========================================\n";
    echo "👤 Admin Login Details:\n";
    echo "Username: " . $admin->getAccountName() . "\n";
    echo "Email: " . $admin->getEmail() . "\n";
    echo "Password: " . $new_password . "\n";
    echo "==========================================\n";
    echo "🌐 Login at: http://localhost:8080/user/login\n";
  } else {
    echo "❌ Admin user not found!\n";
  }
  
} catch (Exception $e) {
  echo "❌ Error: " . $e->getMessage() . "\n";
}