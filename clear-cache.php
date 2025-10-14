<?php
/**
 * Cache clearing script for Drupal
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
  $kernel = DrupalKernel::createFromRequest($request, 'prod', false);
  $kernel->boot();
  $kernel->preHandle($request);
  
  // Clear all caches
  drupal_flush_all_caches();
  
  echo "✅ Drupal cache cleared successfully!\n";
  echo "🔄 All caches have been rebuilt.\n";
  echo "🌐 Your changes should now be visible.\n";
  
} catch (Exception $e) {
  echo "❌ Error clearing cache: " . $e->getMessage() . "\n";
  echo "ℹ️  You can also try clearing cache through the admin interface at /admin/config/development/performance\n";
}