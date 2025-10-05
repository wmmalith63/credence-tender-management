/**
 * Dashboard Dynamic Content Loading
 * Handles loading different sections within the dashboard
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.dashboardTabs = {
    attach: function (context, settings) {
      
      // Handle all dashboard navigation links with data attributes
      $('[data-dashboard-link]', context).once('dashboard-nav').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $link = $(this);
        var linkType = $link.data('dashboard-link');
        var href = $link.attr('href');
        var linkText = $link.text().trim();
        
        // Remove active class from all navigation items
        $('.dropdown-item, .nav-item, .action-btn').removeClass('active');
        $link.addClass('active');
        
        // Load content based on link type
        switch(linkType) {
          case 'company-details':
            loadCompanyDetailsForm();
            break;
          case 'proposals':
            loadProposalsContent();
            break;
          case 'profile':
            loadProfileContent();
            break;
          default:
            loadDashboardHome();
        }
        
        console.log('Dashboard link clicked:', linkType);
        return false;
      });
      
      // Fallback: Prevent all navigation to company/details pages
      $('a[href*="/company"]', context).once('company-nav').on('click', function(e) {
        var $link = $(this);
        var href = $link.attr('href');
        
        if (href && href.includes('/company')) {
          e.preventDefault();
          e.stopPropagation();
          console.log('Company link intercepted:', href);
          
          $('.dropdown-item, .nav-item, .action-btn').removeClass('active');
          $link.addClass('active');
          
          loadCompanyDetailsForm();
          return false;
        }
      });
      
      // Handle navigation items
      $('.nav-item', context).once('nav-tabs').on('click', function(e) {
        var $link = $(this);
        var href = $link.attr('href');
        
        if (href && (href.includes('/dashboard') || href.includes('/proposals') || href.includes('/user/profile'))) {
          e.preventDefault();
          e.stopPropagation();
          
          $('.nav-item').removeClass('active');
          $link.addClass('active');
          
          var linkText = $link.text().trim();
          if (href.includes('/proposals')) {
            loadProposalsContent();
          } else if (href.includes('/user/profile')) {
            loadProfileContent();
          } else {
            loadDashboardHome();
          }
          
          return false;
        }
      });
    }
  };
  
  /**
   * Load dashboard content dynamically
   */
  function loadDashboardContent(url, title) {
    // Show loading state
    showLoadingState();
    
    // Update page title
    updatePageTitle(title);
    
    // Determine what content to load
    if (url.includes('/company/details') || url.includes('/company')) {
      loadCompanyDetailsForm();
    } else if (url.includes('/proposals')) {
      loadProposalsContent();
    } else if (url.includes('/user/profile')) {
      loadProfileContent();
    } else {
      loadDashboardHome();
    }
  }
  
  /**
   * Load company details form
   */
  function loadCompanyDetailsForm() {
    $.ajax({
      url: '/company/details',
      type: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      success: function(response) {
        // Extract form content from response
        var $response = $(response);
        var $form = $response.find('form').length ? $response.find('form').parent() : $response.find('.company-details-container');
        
        if ($form.length) {
          updateDashboardContent($form.html());
        } else {
          // Fallback: create the form content directly
          createCompanyDetailsForm();
        }
        hideLoadingState();
      },
      error: function() {
        // Fallback: create the form content directly
        createCompanyDetailsForm();
        hideLoadingState();
      }
    });
  }
  
  /**
   * Create company details form directly
   */
  function createCompanyDetailsForm() {
    var formHtml = `
      <div class="dashboard-content company-details-section">
        <div class="content-header">
          <h2 class="content-title">COMPANY DETAILS</h2>
          <div class="breadcrumb">
            <span>Account Menu</span> > <span class="current">Company Details</span>
          </div>
        </div>
        
        <div class="purpose-statement">
          <p>• Completing Company Details is necessary to meet tender eligibility requirements</p>
        </div>
        
        <form id="company-details-form" class="company-form">
          <div class="form-section">
            <h3 class="section-title">Company Information</h3>
            
            <div class="form-grid">
              <div class="form-group full-width">
                <label for="company_name">Company Name <span class="required">*</span></label>
                <input type="text" id="company_name" name="company_name" class="form-control" required>
              </div>
              
              <div class="form-group">
                <label for="registration_number">Business Registration Number <span class="required">*</span></label>
                <input type="text" id="registration_number" name="registration_number" class="form-control" required>
              </div>
              
              <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" class="form-control" required>
              </div>
              
              <div class="form-group">
                <label for="phone">Phone Number <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" class="form-control" required>
              </div>
              
              <div class="form-group">
                <label for="postal_code">Postal Code <span class="required">*</span></label>
                <input type="text" id="postal_code" name="postal_code" class="form-control" pattern="[0-9]{5}" required>
              </div>
              
              <div class="form-group full-width">
                <label for="address">Address <span class="required">*</span></label>
                <textarea id="address" name="address" class="form-control" rows="3" required></textarea>
              </div>
              
              <div class="form-group">
                <label for="city">City <span class="required">*</span></label>
                <input type="text" id="city" name="city" class="form-control" required>
              </div>
              
              <div class="form-group">
                <label for="state">State <span class="required">*</span></label>
                <select id="state" name="state" class="form-control" required>
                  <option value="">- Select State -</option>
                  <option value="johor">Johor</option>
                  <option value="kedah">Kedah</option>
                  <option value="kelantan">Kelantan</option>
                  <option value="kuala_lumpur">Kuala Lumpur</option>
                  <option value="labuan">Labuan</option>
                  <option value="melaka">Melaka</option>
                  <option value="negeri_sembilan">Negeri Sembilan</option>
                  <option value="pahang">Pahang</option>
                  <option value="penang">Penang</option>
                  <option value="perak">Perak</option>
                  <option value="perlis">Perlis</option>
                  <option value="putrajaya">Putrajaya</option>
                  <option value="sabah">Sabah</option>
                  <option value="sarawak">Sarawak</option>
                  <option value="selangor">Selangor</option>
                  <option value="terengganu">Terengganu</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="form-section">
            <h3 class="section-title">Board of Directors</h3>
            <div class="directors-section">
              <table class="directors-table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Position</th>
                    <th>IC/Passport</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="directors-tbody">
                  <tr class="empty-row">
                    <td colspan="4" class="text-center">No directors added yet.</td>
                  </tr>
                </tbody>
              </table>
              <button type="button" class="btn btn-add-director" onclick="addDirector()">Add Director</button>
            </div>
          </div>
          
          <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-save">Save Company Details</button>
          </div>
        </form>
      </div>
    `;
    
    updateDashboardContent(formHtml);
    
    // Bind form submission
    bindCompanyFormSubmission();
  }
  
  /**
   * Load proposals content
   */
  function loadProposalsContent() {
    var content = `
      <div class="dashboard-content proposals-section">
        <div class="content-header">
          <h2 class="content-title">MY PROPOSALS</h2>
          <div class="breadcrumb">
            <span>Account Menu</span> > <span class="current">Proposals</span>
          </div>
        </div>
        
        <div class="proposals-grid">
          <div class="proposal-card">
            <h3>Proposal Management</h3>
            <p>View and manage your submitted proposals</p>
            <button class="btn btn-primary">View Proposals</button>
          </div>
        </div>
      </div>
    `;
    updateDashboardContent(content);
  }
  
  /**
   * Load profile content
   */
  function loadProfileContent() {
    var content = `
      <div class="dashboard-content profile-section">
        <div class="content-header">
          <h2 class="content-title">MY PROFILE</h2>
          <div class="breadcrumb">
            <span>Account Menu</span> > <span class="current">My Profile</span>
          </div>
        </div>
        
        <div class="profile-info">
          <h3>Profile Information</h3>
          <p>Manage your personal account information</p>
          <button class="btn btn-primary">Edit Profile</button>
        </div>
      </div>
    `;
    updateDashboardContent(content);
  }
  
  /**
   * Load dashboard home
   */
  function loadDashboardHome() {
    var content = `
      <div class="dashboard-content home-section">
        
        <div class="dashboard-grid">
          <div class="dashboard-card">
            <div class="card-icon">🏢</div>
            <h3>Company Details</h3>
            <p>Complete your company profile to participate in tenders</p>
            <a href="/company/details" class="btn btn-primary tab-link">Complete Details</a>
          </div>
          
          <div class="dashboard-card">
            <div class="card-icon">📋</div>
            <h3>Active Tenders</h3>
            <p>View and apply for available tenders</p>
            <button class="btn btn-primary">View Tenders</button>
          </div>
          
          <div class="dashboard-card">
            <div class="card-icon">📝</div>
            <h3>My Proposals</h3>
            <p>Track your submitted proposals</p>
            <a href="/proposals" class="btn btn-primary tab-link">View Proposals</a>
          </div>
          
          <div class="dashboard-card">
            <div class="card-icon">👤</div>
            <h3>Profile Settings</h3>
            <p>Manage your account settings</p>
            <a href="/user/profile" class="btn btn-primary tab-link">Edit Profile</a>
          </div>
        </div>
      </div>
    `;
    updateDashboardContent(content);
    
    // Rebind tab links
    $('.tab-link').once('tab-links').on('click', function(e) {
      e.preventDefault();
      var href = $(this).attr('href');
      var title = $(this).closest('.dashboard-card').find('h3').text();
      loadDashboardContent(href, title);
    });
  }
  
  /**
   * Update dashboard content area
   */
  function updateDashboardContent(html) {
    var $contentArea = $('.dashboard-main-content');
    if ($contentArea.length === 0) {
      // Create content area if it doesn't exist
      $('.dashboard-nav').after('<div class="dashboard-main-content"></div>');
      $contentArea = $('.dashboard-main-content');
    }
    
    $contentArea.html(html);
    
    // Reattach Drupal behaviors to new content
    Drupal.attachBehaviors($contentArea[0]);
  }
  
  /**
   * Update page title
   */
  function updatePageTitle(title) {
    $('.content-title').text(title.toUpperCase());
  }
  
  /**
   * Show loading state
   */
  function showLoadingState() {
    var $contentArea = $('.dashboard-main-content');
    if ($contentArea.length === 0) {
      $('.dashboard-nav').after('<div class="dashboard-main-content"></div>');
      $contentArea = $('.dashboard-main-content');
    }
    
    $contentArea.html(`
      <div class="loading-state">
        <div class="spinner"></div>
        <p>Loading...</p>
      </div>
    `);
  }
  
  /**
   * Hide loading state
   */
  function hideLoadingState() {
    $('.loading-state').remove();
  }
  
  /**
   * Bind company form submission
   */
  function bindCompanyFormSubmission() {
    $('#company-details-form').once('company-form').on('submit', function(e) {
      e.preventDefault();
      
      var formData = new FormData(this);
      
      $.ajax({
        url: '/company/details',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
          // Show success message
          showMessage('Company details saved successfully!', 'success');
        },
        error: function() {
          showMessage('Failed to save company details. Please try again.', 'error');
        }
      });
    });
  }
  
  /**
   * Show success/error messages
   */
  function showMessage(message, type) {
    var messageClass = type === 'success' ? 'messages--status' : 'messages--error';
    var messageHtml = `<div class="messages ${messageClass}">${message}</div>`;
    
    $('.dashboard-content').prepend(messageHtml);
    
    // Auto-remove message after 5 seconds
    setTimeout(function() {
      $('.messages').fadeOut();
    }, 5000);
  }
  
  // Global function for adding directors
  window.addDirector = function() {
    var directorHtml = `
      <tr class="director-row">
        <td><input type="text" name="director_name[]" class="form-control" placeholder="Director Name"></td>
        <td><input type="text" name="director_position[]" class="form-control" placeholder="Position"></td>
        <td><input type="text" name="director_ic[]" class="form-control" placeholder="IC/Passport"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeDirector(this)">Remove</button></td>
      </tr>
    `;
    
    var $tbody = $('#directors-tbody');
    $tbody.find('.empty-row').remove();
    $tbody.append(directorHtml);
  };
  
  // Global function for removing directors
  window.removeDirector = function(button) {
    $(button).closest('tr').remove();
    
    // Show empty message if no directors
    if ($('#directors-tbody tr').length === 0) {
      $('#directors-tbody').html('<tr class="empty-row"><td colspan="4" class="text-center">No directors added yet.</td></tr>');
    }
  };
  
  // Initialize dashboard home on page load and handle browser navigation
  $(document).ready(function() {
    // Only load dashboard home if we're on the dashboard page
    if (window.location.pathname === '/dashboard') {
      loadDashboardHome();
      
      // Override all company details links globally
      $(document).on('click', 'a[href="/company/details"], a[href*="company/details"]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Company details link intercepted');
        loadCompanyDetailsForm();
        return false;
      });
      
      // Override form submissions to stay on dashboard
      $(document).on('submit', '#company-details-form', function(e) {
        e.preventDefault();
        handleCompanyFormSubmission(this);
        return false;
      });
    }
  });
  
  /**
   * Handle company form submission via AJAX
   */
  function handleCompanyFormSubmission(form) {
    var $form = $(form);
    var formData = new FormData(form);
    
    // Add form token if available
    var token = $form.find('input[name="form_token"]').val();
    if (token) {
      formData.append('form_token', token);
    }
    
    // Show loading state
    $form.find('.btn-save').prop('disabled', true).text('Saving...');
    
    $.ajax({
      url: '/company/details',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      success: function(response) {
        showMessage('Company details saved successfully!', 'success');
        $form.find('.btn-save').prop('disabled', false).text('Save Company Details');
      },
      error: function(xhr, status, error) {
        showMessage('Failed to save company details. Please try again.', 'error');
        $form.find('.btn-save').prop('disabled', false).text('Save Company Details');
        console.error('Form submission error:', error);
      }
    });
  }

})(jQuery, Drupal);