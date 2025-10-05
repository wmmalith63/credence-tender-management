/**
 * Dashboard JavaScript - e-TVCMS Tender Management
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.dashboardEnhancements = {
    attach: function (context, settings) {
      
      // Add hover effects to dashboard cards
      $('.dashboard-card', context).once('dashboard-card-hover').each(function() {
        $(this).hover(
          function() {
            $(this).addClass('card-hover');
          },
          function() {
            $(this).removeClass('card-hover');
          }
        );
      });

      // Add click animations to action buttons
      $('.action-btn', context).once('action-btn-click').click(function(e) {
        $(this).addClass('btn-clicked');
        setTimeout(() => {
          $(this).removeClass('btn-clicked');
        }, 200);
      });

      // Initialize dashboard stats counter animation
      $('.stat-number', context).once('stat-counter').each(function() {
        const $this = $(this);
        const finalValue = parseInt($this.text()) || 0;
        
        if (finalValue > 0) {
          let currentValue = 0;
          const increment = finalValue / 30;
          const timer = setInterval(function() {
            currentValue += increment;
            if (currentValue >= finalValue) {
              currentValue = finalValue;
              clearInterval(timer);
            }
            $this.text(Math.floor(currentValue));
          }, 50);
        }
      });

      // Add welcome message animation
      $('.user-welcome', context).once('welcome-animation').addClass('animate-fade-in');

      // Auto-refresh dashboard every 5 minutes
      if ($('.dashboard-container', context).length > 0) {
        setInterval(function() {
          // You can add AJAX calls here to refresh dashboard data
          console.log('Dashboard auto-refresh triggered');
        }, 300000); // 5 minutes
      }

      // Add smooth scrolling for internal links
      $('a[href^="#"]', context).once('smooth-scroll').click(function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
          $('html, body').animate({
            scrollTop: target.offset().top - 100
          }, 500);
        }
      });

      // Add notification system
      function showNotification(message, type = 'info') {
        const notification = $('<div class="dashboard-notification ' + type + '">' + message + '</div>');
        $('body').append(notification);
        
        setTimeout(function() {
          notification.addClass('show');
        }, 100);
        
        setTimeout(function() {
          notification.removeClass('show');
          setTimeout(function() {
            notification.remove();
          }, 300);
        }, 3000);
      }

      // Make notifications available globally
      window.dashboardNotify = showNotification;

    }
  };

  // Add CSS for animations
  Drupal.behaviors.dashboardAnimations = {
    attach: function (context, settings) {
      if (!$('#dashboard-animations-css').length) {
        $('head').append(`
          <style id="dashboard-animations-css">
            .animate-fade-in {
              animation: fadeIn 0.8s ease-in-out;
            }
            
            @keyframes fadeIn {
              from { opacity: 0; transform: translateY(20px); }
              to { opacity: 1; transform: translateY(0); }
            }
            
            .card-hover {
              transform: translateY(-5px) !important;
              transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }
            
            .btn-clicked {
              transform: scale(0.95) !important;
            }
            
            .dashboard-notification {
              position: fixed;
              top: 20px;
              right: 20px;
              padding: 15px 20px;
              border-radius: 8px;
              color: white;
              font-weight: 600;
              z-index: 9999;
              transform: translateX(100%);
              transition: transform 0.3s ease;
              box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            }
            
            .dashboard-notification.show {
              transform: translateX(0);
            }
            
            .dashboard-notification.info {
              background: linear-gradient(135deg, #3498db, #2980b9);
            }
            
            .dashboard-notification.success {
              background: linear-gradient(135deg, #27ae60, #229954);
            }
            
            .dashboard-notification.warning {
              background: linear-gradient(135deg, #f39c12, #e67e22);
            }
            
            .dashboard-notification.error {
              background: linear-gradient(135deg, #e74c3c, #c0392b);
            }
          </style>
        `);
      }
    }
  };

})(jQuery, Drupal);