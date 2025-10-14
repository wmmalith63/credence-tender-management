<?php
/**
 * Simple Drupal installation script
 */

use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;

// Change to the Drupal root
chdir('/var/www/html');

// Autoloader
require_once 'autoload.php';

// Database configuration
$databases['default']['default'] = [
  'database' => 'tender_management',
  'username' => 'drupal',
  'password' => 'drupal123',
  'host' => 'db',
  'port' => '5432',
  'driver' => 'pgsql',
  'prefix' => '',
  'namespace' => 'Drupal\\pgsql\\Driver\\Database\\pgsql',
];

// Install Drupal
try {
  // Create settings.php content
  $settings_content = '<?php

$databases[\'default\'][\'default\'] = [
  \'database\' => \'tender_management\',
  \'username\' => \'drupal\',
  \'password\' => \'drupal123\',
  \'host\' => \'db\',
  \'port\' => \'5432\',
  \'driver\' => \'pgsql\',
  \'prefix\' => \'\',
  \'namespace\' => \'Drupal\\\\pgsql\\\\Driver\\\\Database\\\\pgsql\',
];

$settings[\'hash_salt\'] = \'' . hash('sha256', 'credence_tender_salt_' . time()) . '\';

$settings[\'trusted_host_patterns\'] = [
  \'^localhost$\',
  \'^127\\.0\\.0\\.1$\',
  \'^\\[::1\\]$\',
];

$settings[\'config_sync_directory\'] = \'../config/sync\';
';

  // Write settings.php
  file_put_contents('/var/www/html/sites/default/settings.php', $settings_content);
  chmod('/var/www/html/sites/default/settings.php', 0666);

  echo "Drupal installation script completed!\n";
  echo "Please visit http://localhost:8080/core/install.php to complete the installation.\n";
} catch (Exception $e) {
  echo "Error: " . $e->getMessage() . "\n";
}