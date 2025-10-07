/**
 * Tender Dashboard JavaScript functionality
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.tenderDashboard = {
    attach: function (context, settings) {
      // Dashboard functionality is already implemented in the template
      // This file exists to satisfy the library dependency
      
      console.log('Tender Dashboard JavaScript loaded');
      
      // Initialize any additional JavaScript functionality here
      once('tender-dashboard', 'body', context).forEach(function (element) {
        // Additional initialization code can go here
      });
    }
  };

})(jQuery, Drupal);