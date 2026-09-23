/**
 * NDS HR — Admin Interactions JavaScript
 * Enhances Employee Form with live phone country formatting, photo preview,
 * contract duration calculation, and independent NDS HR credential management.
 *
 * @package NDS_HR
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
			var empCode  = $('#employee_id').val() || '';

			if (username && password) {
				var previewText = 'Employee ID: ' + empCode + '\nUsername: ' + username + '\nTemporary Password: ' + password;
				$('#nds-hr-live-creds-text').text(previewText);
				$('#nds-hr-live-credentials-preview').slideDown(150);
			} else {
				$('#nds-hr-live-credentials-preview').slideUp(150);
			}
		}

		// Helper: Parse DD/MM/YYYY or YYYY-MM-DD into JS Date object
		function parseDateStr(str) {
			if (!str) return null;
			str = str.trim();
			// Match DD/MM/YYYY or DD-MM-YYYY
			var dmyMatch = str.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
			if (dmyMatch) {
				var day = parseInt(dmyMatch[1], 10);
				var month = parseInt(dmyMatch[2], 10) - 1;
				var year = parseInt(dmyMatch[3], 10);
				return new Date(year, month, day);
			}
			// Match YYYY-MM-DD
			var ymdMatch = str.match(/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/);
			if (ymdMatch) {
				var year = parseInt(ymdMatch[1], 10);
				var month = parseInt(ymdMatch[2], 10) - 1;
				var day = parseInt(ymdMatch[3], 10);
				return new Date(year, month, day);
			}
			return null;
		}

		// Helper: Calculate duration between start and end dates
		function calculateDuration(startStr, endStr) {
			var start = parseDateStr(startStr);
			var end   = parseDateStr(endStr);

			if (!start) {
				return 'Indefinite / Open-ended';
			}
			if (!end) {
				return 'Open-ended / Indefinite contract';
			}

			if (end < start) {
				return 'Invalid range (End date is before start date)';
			}

			var diffTime = Math.abs(end - start);
			var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

			var years = Math.floor(diffDays / 365);
			var remainingDays = diffDays % 365;
			var months = Math.floor(remainingDays / 30);
			var days = remainingDays % 30;

			var parts = [];
			if (years > 0) {
				parts.push(years + (years === 1 ? ' Year' : ' Years'));
			}
			if (months > 0) {
				parts.push(months + (months === 1 ? ' Month' : ' Months'));
			}
			if (days > 0 || parts.length === 0) {
				parts.push(days + (days === 1 ? ' Day' : ' Days'));
			}

			return parts.join(', ');
		}

		// 1. Phone Country Code Switcher
		$('#phone_country_code').on('change', function() {
			var country = $(this).val();
			var $mobile = $('#mobile');
			var $hint   = $('#nds-hr-phone-hint');

			if ('+966' === country) {
				$mobile.attr('placeholder', '5xxxxxxxx');
				$hint.text('Saudi mobile format: 9 digits starting with 5 (e.g. 50 123 4567)');
			} else {
				$mobile.attr('placeholder', '10xxxxxxxx');
				$hint.text('Egypt mobile format: 10 digits starting with 10, 11, 12, or 15 (e.g. 10 1234 5678)');
			}
		});

		// 2. Profile Photo Live Preview
		$('#profile_photo').on('change', function(e) {
			var file = e.target.files && e.target.files[0];
			if (file) {
				// Validate client side type & size
				if (file.size > 5 * 1024 * 1024) {
					alert('Selected photo exceeds 5MB limit.');
					$(this).val('');
					return;
				}

				var reader = new FileReader();
				reader.onload = function(evt) {
					$('#nds-hr-avatar-placeholder').hide();
					$('#nds-hr-avatar-preview').attr('src', evt.target.result).show();
				};
				reader.readAsDataURL(file);
			}
		});

		// 3. Remove photo checkbox clears preview
		$('#remove_profile_photo').on('change', function() {
			if ($(this).is(':checked')) {
				$('#nds-hr-avatar-preview').hide();
				$('#nds-hr-avatar-placeholder').show();
			}
		});

		// 4. Live Contract Duration Calculator
		$('.js-contract-date').on('input change blur', function() {
			var start = $('#contract_start_date').val();
			var end   = $('#contract_end_date').val();
			var duration = calculateDuration(start, end);
			$('#nds-hr-duration-text').text(duration);
		});

		// 5. Account Creation Toggle (New Employee)
		$('.js-toggle-account-creation').on('change', function() {
			if ($(this).is(':checked')) {
				$('#nds-hr-account-credential-fields').slideDown(150);
			} else {
				$('#nds-hr-account-credential-fields').slideUp(150);
			}
		});

		// 6. Auto-suggest username from corporate email
		$('#email').on('input blur', function() {
			var emailVal = $(this).val();
			var $usernameField = $('#account_username');

			if (emailVal && $usernameField.length && !$usernameField.val()) {
				var suggested = emailVal.split('@')[0].replace(/[^a-zA-Z0-9._-]/g, '').toLowerCase();
				$usernameField.val(suggested);
			}
			updateLiveCredentialsPreview();
		});

		// 7. Standalone Generate Password Button
		$('.js-generate-password-btn').on('click', function(e) {
			e.preventDefault();
			var generated = generateSecurePassword(14);
			$('#account_password').val(generated).attr('type', 'text');
			$('.js-pw-toggle-text').text('Hide');
			updateLiveCredentialsPreview();
		});

		// 8. Show/Hide Password Toggle
		$('.js-toggle-pw-visibility').on('click', function(e) {
			e.preventDefault();
			var $pw = $('#account_password');
			var currentType = $pw.attr('type');

			if ('password' === currentType) {
				$pw.attr('type', 'text');
				$('.js-pw-toggle-text').text('Hide');
			} else {
				$pw.attr('type', 'password');
				$('.js-pw-toggle-text').text('Show');
			}
		});

		$('#account_username, #employee_id').on('input', updateLiveCredentialsPreview);

		// 9. Copy Staged Credentials Button
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

		// 10. Copy New Created Credentials from List Notice
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
