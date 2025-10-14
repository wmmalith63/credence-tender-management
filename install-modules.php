<?php
/**
 * Script to enable custom modules
 */

// Bootstrap Drupal
use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

// Change to the Drupal root
chdir('/var/www/html');

// Autoloader
require_once 'autoload.php';

try {
  // Create a request
  $request = Request::createFromGlobals();
  
  // Bootstrap Drupal
  $kernel = DrupalKernel::createFromRequest($request, 'dev', false);
  $kernel->boot();
  $kernel->preHandle($request);
  
  // Get the module installer service
  $module_installer = \Drupal::service('module_installer');
  
  // List of custom modules to enable
  $modules_to_enable = [
    'user_management',
    'tender_management'
  ];
  
  echo "Installing custom modules...\n";
  
  foreach ($modules_to_enable as $module) {
    if (!\Drupal::moduleHandler()->moduleExists($module)) {
      echo "Enabling module: $module\n";
      $module_installer->install([$module]);
      echo "✅ Module '$module' enabled successfully!\n";
    } else {
      echo "ℹ️  Module '$module' is already enabled.\n";
    }
  }
  
  echo "\n🎉 All custom modules have been processed!\n";
  echo "\nYou can now access:\n";
  echo "- Tender Management: /tender-management\n";
  echo "- User Dashboard: /user/dashboard\n";
  echo "- Admin Modules: /admin/modules\n";
  
} catch (Exception $e) {
  echo "❌ Error: " . $e->getMessage() . "\n";
  echo "This might mean Drupal is not fully installed yet.\n";
  echo "Please complete the Drupal installation first at: http://localhost:8080/core/install.php\n";
}