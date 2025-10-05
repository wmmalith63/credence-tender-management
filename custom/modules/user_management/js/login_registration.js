(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.loginRegistration = {
    attach: function (context, settings) {

          console.log('Login Registration JavaScript loaded!');
          console.log('Context:', context);
          console.log('Looking for messages...');
          console.log('jQuery version:', $.fn.jquery);
          console.log('Document ready state:', document.readyState);

          // Check if messages exist at all
          var allMessages = $('.messages');
          console.log('All messages found:', allMessages.length);
          if (allMessages.length > 0) {
              allMessages.each(function (index) {
                  console.log('Message ' + index + ':', this);
              });
          }

          // Check in context
          var contextMessages = $('.messages', context);
          console.log('Messages in context:', contextMessages.length);

          // Handle overlay error messages
          $('.messages:not(.overlay-processed)', context).each(function () {
              console.log('Processing message element:', this);
              const $message = $(this);
              $message.addClass('overlay-processed');

              // Remove from current position and append to body to prevent layout issues
              $message.detach().appendTo('body');
              console.log('Message moved to body');

              // Ensure it's properly positioned
              $message.css({
                  'position': 'fixed',
                  'top': '50%',
                  'left': '50%',
                  'transform': 'translate(-50%, -50%)',
                  'z-index': '10000'
              });
          });

          // Single global event handler for close buttons
          $(document).off('click.messageClose').on('click.messageClose', '.message-close-btn, .messages__close', function (e) {
              console.log('Close button clicked!');
              e.preventDefault();
              e.stopPropagation();
              const $message = $(this).closest('.messages, .alert, .message');
              console.log('Found message to close:', $message);
              if ($message.length) {
                  $message.fadeOut(300, function () {
                      $message.remove();
                  });
              }
              return false;
          });          // Global document ready handler for messages that appear outside Drupal behaviors
          $(document).ready(function () {
              console.log('Document ready - checking for messages globally');

              // Check for any existing messages and process them
              function processUnhandledMessages() {
                  $('.messages:not(.overlay-processed)').each(function () {
                      console.log('Found unprocessed message in DOM:', this);
                      var $message = $(this);
                      $message.addClass('overlay-processed');

                      // Remove from current position and append to body
                      $message.detach().appendTo('body');

                      // Position it as overlay
                      $message.css({
                          'position': 'fixed',
                          'top': '50%',
                          'left': '50%',
                          'transform': 'translate(-50%, -50%)',
                          'z-index': '10000'
                      });

                      // Auto-close after 4 seconds
                      setTimeout(function () {
                          if ($message.length && $message.is(':visible')) {
                              $message.fadeOut(300, function () {
                                  $message.remove();
                              });
                          }
                      }, 4000);
                  });
              }

              // Process immediately
              processUnhandledMessages();

              // Check periodically for new messages
              setInterval(processUnhandledMessages, 200);
          });

          // Form toggle functionality
      window.showRegisterForm = function() {
          $('.login-section').hide();
          $('.registration-section').show();
      };

      window.showLoginForm = function() {
          $('.registration-section').hide();
          $('.login-section').show();
      };

          // Ensure both sections are visible on larger screens
      $('.login-section, .registration-section').show();

          // Responsive behavior
      if ($(window).width() <= 768) {
          $('.registration-section').hide();
      }

      $(window).resize(function() {
          if ($(window).width() > 768) {
              $('.login-section, .registration-section').show();
          } else {
              $('.registration-section').hide();
              $('.login-section').show();
          }
      });

          // Email and password validation
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

          // Password confirmation validation
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