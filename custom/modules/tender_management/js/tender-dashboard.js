/**
 * Tender Dashboard JavaScript functionality
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.tenderDashboard = {
    attach: function (context, settings) {
      console.log('Tender Dashboard JavaScript loaded');
      
      // Initialize form submission handlers
      once('tender-dashboard', 'body', context).forEach(function (element) {
        initializeTenderForm();
        initializeVendorApplicationForm();
      });
    }
  };

  /**
   * Initialize tender creation/editing form
   */
  function initializeTenderForm() {
    // Handle Save as Draft button
    $(document).off('click', '#save-draft-btn').on('click', '#save-draft-btn', function (e) {
      e.preventDefault();
      saveTenderData('draft');
    });

    // Handle Save for UKK button (UKK users only)
    $(document).off('click', '#save-ukk-btn').on('click', '#save-ukk-btn', function (e) {
      e.preventDefault();
      saveTenderData('ukk_saved');
    });

    // Handle Publish button (Admin only)
    $(document).off('click', '#publish-btn').on('click', '#publish-btn', function (e) {
      e.preventDefault();
      if (confirm('Are you sure you want to publish this tender? This will make it visible to all vendors.')) {
        saveTenderData('published');
      }
    });
  }

  /**
   * Initialize vendor application form
   */
  function initializeVendorApplicationForm() {
    // Handle vendor application submission
    $(document).off('click', '#submit-application-btn').on('click', '#submit-application-btn', function (e) {
      e.preventDefault();
      saveVendorApplication();
    });
  }

  /**
   * Save tender data via AJAX
   */
  function saveTenderData(status) {
    // Show loading indicator
    var $btn = status === 'draft' ? $('#save-draft-btn') :
      status === 'ukk_saved' ? $('#save-ukk-btn') : $('#publish-btn');
    var originalText = $btn.text();
    $btn.prop('disabled', true).text('Saving...');

    // Collect form data
    var formData = {
      tender_number: $('#tender-number').val(),
      tender_title: $('#tender-title').val(),
      tender_description: $('#tender-description').val(),
      tender_type: $('#tender-type').val(),
      tender_category: $('#tender-category').val(),
      episode_duration: $('#episode-duration').val(),
      total_episodes: $('#total-episodes').val(),
      budget_per_episode: $('#budget-per-episode').val(),
      total_budget: $('#total-budget').val(),
      submission_deadline: $('#submission-deadline').val(),
      evaluation_period: $('#evaluation-period').val(),
      production_start: $('#production-start').val(),
      production_end: $('#production-end').val(),
      technical_requirements: $('#technical-requirements').val(),
      content_requirements: $('#content-requirements').val(),
      status: status,
      required_documents: []
    };

    // Collect selected required documents
    $('input[name="required_documents[]"]:checked').each(function () {
      formData.required_documents.push($(this).val());
    });

    // Validate required fields
    if (!formData.tender_number || !formData.tender_title || !formData.tender_type) {
      alert('Please fill in all required fields: Tender Number, Title, and Type.');
      $btn.prop('disabled', false).text(originalText);
      return;
    }

    // Make AJAX request
    $.ajax({
      url: '/ajax/tender/save',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          showSuccessMessage('Tender saved successfully!');

          // Update UI based on status
          if (response.status === 'published') {
            showSuccessMessage('Tender has been published and is now visible to vendors.');
          } else if (response.status === 'pending_approval') {
            showSuccessMessage('Tender saved and submitted for approval.');
          }

          // Optionally reload the page or update UI
          setTimeout(function () {
            location.reload();
          }, 2000);
        } else {
          showErrorMessage('Error: ' + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX Error:', error);
        showErrorMessage('An error occurred while saving the tender. Please try again.');
      },
      complete: function () {
        $btn.prop('disabled', false).text(originalText);
      }
    });
  }

  /**
   * Save vendor application via AJAX
   */
  function saveVendorApplication() {
    var $btn = $('#submit-application-btn');
    var originalText = $btn.text();
    $btn.prop('disabled', true).text('Submitting...');

    // Collect form data
    var formData = {
      selected_tender: $('#selected-tender').val(),
      company_name: $('#company-name').val(),
      company_reg_no: $('#company-reg-no').val(),
      contact_person: $('#contact-person').val(),
      contact_email: $('#contact-email').val(),
      additional_notes: $('#additional-notes').val()
    };

    // Validate required fields
    if (!formData.selected_tender || !formData.company_name || !formData.contact_person) {
      alert('Please fill in all required fields.');
      $btn.prop('disabled', false).text(originalText);
      return;
    }

    // Make AJAX request
    $.ajax({
      url: '/ajax/vendor/application/save',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          showSuccessMessage('Application submitted successfully!');

          // Clear the form
          $('#vendor-application-form')[0].reset();

          // Optionally redirect or reload
          setTimeout(function () {
            location.reload();
          }, 2000);
        } else {
          showErrorMessage('Error: ' + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX Error:', error);
        showErrorMessage('An error occurred while submitting your application. Please try again.');
      },
      complete: function () {
        $btn.prop('disabled', false).text(originalText);
      }
    });
  }

  /**
   * Show success message
   */
  function showSuccessMessage(message) {
    // Remove existing messages
    $('.alert').remove();

    // Add success message
    var $alert = $('<div class="alert alert-success" style="position: fixed; top: 20px; right: 20px; z-index: 9999; padding: 15px; background: #dff0d8; border: 1px solid #d6e9c6; color: #3c763d; border-radius: 4px;">' + message + '</div>');
    $('body').append($alert);

    // Auto-remove after 5 seconds
    setTimeout(function () {
      $alert.fadeOut();
    }, 5000);
  }

  /**
   * Show error message
   */
  function showErrorMessage(message) {
    // Remove existing messages
    $('.alert').remove();

    // Add error message
    var $alert = $('<div class="alert alert-danger" style="position: fixed; top: 20px; right: 20px; z-index: 9999; padding: 15px; background: #f2dede; border: 1px solid #ebccd1; color: #a94442; border-radius: 4px;">' + message + '</div>');
    $('body').append($alert);

    // Auto-remove after 5 seconds
    setTimeout(function () {
      $alert.fadeOut();
    }, 5000);
  }

})(jQuery, Drupal);