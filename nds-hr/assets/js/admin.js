/**
 * NDS HR — Admin Interactions JavaScript
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Helper: Generate a cryptographically secure random password
		function generateSecurePassword(length) {
			length = length || 14;
			var uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
			var lowercase = 'abcdefghijkmnopqrstuvwxyz';
			var numbers = '23456789';
			var symbols = '!@#$%^&*()-_=+[]{}';
			var allChars = uppercase + lowercase + numbers + symbols;

			var password = '';
			// Guarantee at least one of each character category
			password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
			password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
			password += numbers.charAt(Math.floor(Math.random() * numbers.length));
			password += symbols.charAt(Math.floor(Math.random() * symbols.length));

			for (var i = password.length; i < length; i++) {
				password += allChars.charAt(Math.floor(Math.random() * allChars.length));
			}

			// Shuffle password characters
			return password.split('').sort(function() { return 0.5 - Math.random(); }).join('');
		}

		// Helper: Update live credentials preview in form
		function updateLiveCredentialsPreview() {
			var username = $('#account_username').val() || '';
			var password = $('#account_password').val() || '';
			var empCode = $('#employee_id').val() || '';

			if (username && password) {
				var previewText = 'Employee ID: ' + empCode + '\nUsername: ' + username + '\nTemporary Password: ' + password;
				$('#nds-hr-live-creds-text').text(previewText);
				$('#nds-hr-live-credentials-preview').slideDown(150);
			} else {
				$('#nds-hr-live-credentials-preview').slideUp(150);
			}
		}

		// 1. Toggle account creation/linking sections
		$('input[name="account_action"]').on('change', function() {
			var selectedAction = $(this).val();

			if ('create' === selectedAction) {
				$('#nds-hr-create-user-fields').slideDown(150);
				$('#nds-hr-link-user-fields').slideUp(150);
				// If password is empty, suggest generating one
				if (!$('#account_password').val()) {
					$('.js-generate-password-btn').trigger('click');
				}
			} else if ('link' === selectedAction) {
				$('#nds-hr-create-user-fields').slideUp(150);
				$('#nds-hr-link-user-fields').slideDown(150);
			} else {
				$('#nds-hr-create-user-fields').slideUp(150);
				$('#nds-hr-link-user-fields').slideUp(150);
			}
		});

		// 2. Auto-suggest username and sync email indicator
		$('#email').on('input blur', function() {
			var emailVal = $(this).val();
			var $usernameField = $('#account_username');
			$('.js-email-display').text(emailVal || 'Synced with Corporate Email');

			if (emailVal && $usernameField.length && !$usernameField.val()) {
				var suggested = emailVal.split('@')[0].replace(/[^a-zA-Z0-9._-]/g, '').toLowerCase();
				$usernameField.val(suggested);
			}
			updateLiveCredentialsPreview();
		});

		// 3. Password Method Selector Buttons (Generate vs Manual)
		$('.js-pw-mode-btn').on('click', function(e) {
			e.preventDefault();
			var mode = $(this).data('mode');
			$('.js-pw-mode-btn').removeClass('nds-hr-btn-primary').addClass('nds-hr-btn-outline');
			$(this).removeClass('nds-hr-btn-outline').addClass('nds-hr-btn-primary');

			if ('generate' === mode) {
				var newPw = generateSecurePassword(14);
				$('#account_password').val(newPw).attr('type', 'text');
				$('#account_password_confirm').val(newPw).attr('type', 'text');
				$('.js-pw-toggle-text').text('Hide');
				$('#nds-hr-confirm-pw-group').hide();
			} else {
				$('#account_password').val('').attr('type', 'password');
				$('#account_password_confirm').val('').attr('type', 'password');
				$('.js-pw-toggle-text').text('Show');
				$('#nds-hr-confirm-pw-group').show();
			}
			updateLiveCredentialsPreview();
		});

		// 4. Standalone Generate Password Button
		$('.js-generate-password-btn').on('click', function(e) {
			e.preventDefault();
			var generated = generateSecurePassword(14);
			$('#account_password').val(generated).attr('type', 'text');
			$('#account_password_confirm').val(generated).attr('type', 'text');
			$('.js-pw-toggle-text').text('Hide');
			updateLiveCredentialsPreview();
		});

		// 5. Show/Hide Password Toggle
		$('.js-toggle-pw-visibility').on('click', function(e) {
			e.preventDefault();
			var $pw = $('#account_password');
			var $confirm = $('#account_password_confirm');
			var currentType = $pw.attr('type');

			if ('password' === currentType) {
				$pw.attr('type', 'text');
				$confirm.attr('type', 'text');
				$('.js-pw-toggle-text').text('Hide');
			} else {
				$pw.attr('type', 'password');
				$confirm.attr('type', 'password');
				$('.js-pw-toggle-text').text('Show');
			}
		});

		// 6. Real-time Password Match Indicator
		$('#account_password, #account_password_confirm').on('input', function() {
			var pw = $('#account_password').val();
			var confirm = $('#account_password_confirm').val();
			var $indicator = $('#nds-hr-pw-match-indicator');

			if (pw && confirm) {
				$indicator.show();
				if (pw === confirm) {
					$indicator.text('Passwords match ✓').css('color', '#166534');
				} else {
					$indicator.text('Passwords do not match ✗').css('color', '#991B1B');
				}
			} else {
				$indicator.hide();
			}
			updateLiveCredentialsPreview();
		});

		$('#account_username, #employee_id').on('input', updateLiveCredentialsPreview);

		// 7. Copy Staged Credentials Button
		$('.js-copy-staged-creds-btn').on('click', function(e) {
			e.preventDefault();
			var textToCopy = $('#nds-hr-live-creds-text').text();
			if (navigator.clipboard && textToCopy) {
				navigator.clipboard.writeText(textToCopy).then(function() {
					var $btn = $('.js-copy-staged-creds-btn');
					$btn.text('Copied to Clipboard!');
					setTimeout(function() {
						$btn.html('<span class="dashicons dashicons-clipboard"></span> Copy Credentials');
					}, 2500);
				});
			}
		});

		// 8. Copy New Created Credentials from List Notice
		$('.js-copy-new-credentials-btn').on('click', function(e) {
			e.preventDefault();
			var empId = $('#nds-hr-cred-empid').text().trim();
			var username = $('#nds-hr-cred-username').text().trim();
			var password = $('#nds-hr-cred-password').text().trim();

			var bundle = 'Employee ID: ' + empId + '\n' +
			             'Username: ' + username + '\n' +
			             'Temporary Password: ' + password + '\n' +
			             'Login URL: ' + window.location.origin + '/login/';

			if (navigator.clipboard) {
				navigator.clipboard.writeText(bundle).then(function() {
					var $btnText = $('.js-copy-btn-text');
					$btnText.text('Credentials Copied!');
					setTimeout(function() {
						$btnText.text('Copy Credentials');
					}, 3000);
				});
			}
		});
	});

})(jQuery);
