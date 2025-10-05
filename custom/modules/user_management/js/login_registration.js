(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.loginRegistration = {
    attach: function (context, settings) {
      // Show registration form and hide login form
      window.showRegisterForm = function() {
        $('.login-section').hide();
        $('.registration-section').show();
      };

      // Show login form and hide registration form
      window.showLoginForm = function() {
        $('.registration-section').hide();
        $('.login-section').show();
      };

      // Initially show both forms side by side
      $('.login-section, .registration-section').show();
      
      // On mobile, hide registration form initially
      if ($(window).width() <= 768) {
        $('.registration-section').hide();
      }

      // Handle window resize
      $(window).resize(function() {
        if ($(window).width() > 768) {
          $('.login-section, .registration-section').show();
        } else {
          $('.registration-section').hide();
          $('.login-section').show();
        }
      });

      // Form validation feedback
      $('input[type="email"], input[type="password"]').on('blur', function() {
        var $this = $(this);
        var value = $this.val();
        
        if ($this.attr('type') === 'email' && value) {
          var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(value)) {
            $this.css('border-color', '#e74c3c');
          } else {
            $this.css('border-color', '#27ae60');
          }
        }
        
        if ($this.hasClass('register-password') && value) {
          if (value.length < 8) {
            $this.css('border-color', '#e74c3c');
          } else if (!/^(?=.*[a-zA-Z])(?=.*\d)/.test(value)) {
            $this.css('border-color', '#f39c12');
          } else {
            $this.css('border-color', '#27ae60');
          }
        }
      });

      // Real-time password confirmation
      $('input[name="register_confirm_password"]').on('input', function() {
        var password = $('input[name="register_password"]').val();
        var confirmPassword = $(this).val();
        
        if (confirmPassword) {
          if (password === confirmPassword) {
            $(this).css('border-color', '#27ae60');
          } else {
            $(this).css('border-color', '#e74c3c');
          }
        }
      });
    }
  };

})(jQuery, Drupal);