/**
 * NDS HR — Admin Interactions JavaScript
 * Enhances Employee Form with visual mini-calendar date pickers (DD/MM/YYYY),
 * live phone country formatting, photo preview, contract duration calculation,
 * and independent NDS HR credential management.
 *
 * @package NDS_HR
 */

(function($) {
	'use strict';

	$(document).ready(function() {

		var isRtl = Boolean(window.ndsHrAdminData && window.ndsHrAdminData.isRtl);

		var monthNamesEn = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		var monthNamesAr = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
		var weekDaysEn   = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
		var weekDaysAr   = ['أحد', 'إثن', 'ثلا', 'أرب', 'خمي', 'جمع', 'سبت'];

		var months   = isRtl ? monthNamesAr : monthNamesEn;
		var weekdays = isRtl ? weekDaysAr : weekDaysEn;

		// =========================================================================
		// 1. Interactive Visual Mini-Calendar Date Picker System
		// =========================================================================

		var $activeDatePickerInput = null;
		var $calendarPopup = null;
		var currentCalYear = new Date().getFullYear();
		var currentCalMonth = new Date().getMonth(); // 0-11

		// Format day and month as two digits (DD/MM/YYYY)
		function pad2(num) {
			return (num < 10 ? '0' : '') + num;
		}

		function formatDateDMY(d, m, y) {
			return pad2(d) + '/' + pad2(m + 1) + '/' + y;
		}

		// Helper: Parse DD/MM/YYYY or YYYY-MM-DD into JS Date object
		function parseDateStr(str) {
			if (!str) return null;
			str = String(str).trim();
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

		function createCalendarDOM() {
			if ($('#nds-hr-calendar-popup').length) {
				return $('#nds-hr-calendar-popup');
			}

			var html = '<div id="nds-hr-calendar-popup" class="nds-hr-mini-calendar" style="display:none;" dir="' + (isRtl ? 'rtl' : 'ltr') + '">' +
				'<div class="nds-hr-cal-header">' +
					'<button type="button" class="nds-hr-cal-nav-btn js-cal-prev" title="Previous Month">' +
						'<span class="dashicons dashicons-arrow-' + (isRtl ? 'right' : 'left') + '-alt2"></span>' +
					'</button>' +
					'<div class="nds-hr-cal-selects">' +
						'<select class="nds-hr-cal-select js-cal-month-select"></select>' +
						'<select class="nds-hr-cal-select js-cal-year-select"></select>' +
					'</div>' +
					'<button type="button" class="nds-hr-cal-nav-btn js-cal-next" title="Next Month">' +
						'<span class="dashicons dashicons-arrow-' + (isRtl ? 'left' : 'right') + '-alt2"></span>' +
					'</button>' +
				'</div>' +
				'<div class="nds-hr-cal-weekdays"></div>' +
				'<div class="nds-hr-cal-days"></div>' +
				'<div class="nds-hr-cal-footer">' +
					'<button type="button" class="nds-hr-cal-action-btn nds-hr-cal-today-btn js-cal-today">' + (isRtl ? 'اليوم' : 'Today') + '</button>' +
					'<button type="button" class="nds-hr-cal-action-btn nds-hr-cal-clear-btn js-cal-clear">' + (isRtl ? 'مسح' : 'Clear') + '</button>' +
				'</div>' +
			'</div>';

			var $popup = $(html).appendTo('body');

			// Populate month options
			var $monthSelect = $popup.find('.js-cal-month-select');
			for (var m = 0; m < 12; m++) {
				$monthSelect.append('<option value="' + m + '">' + months[m] + '</option>');
			}

			// Populate year options (from 1930 to currentYear + 20)
			var $yearSelect = $popup.find('.js-cal-year-select');
			var maxYear = new Date().getFullYear() + 20;
			var minYear = 1930;
			for (var y = maxYear; y >= minYear; y--) {
				$yearSelect.append('<option value="' + y + '">' + y + '</option>');
			}

			// Populate weekday headers
			var $weekdays = $popup.find('.nds-hr-cal-weekdays');
			for (var w = 0; w < 7; w++) {
				$weekdays.append('<div>' + weekdays[w] + '</div>');
			}

			// Bind month & year select change
			$monthSelect.on('change', function() {
				currentCalMonth = parseInt($(this).val(), 10);
				renderCalendarGrid();
			});

			$yearSelect.on('change', function() {
				currentCalYear = parseInt($(this).val(), 10);
				renderCalendarGrid();
			});

			// Bind Prev / Next buttons
			$popup.find('.js-cal-prev').on('click', function(e) {
				e.preventDefault();
				currentCalMonth--;
				if (currentCalMonth < 0) {
					currentCalMonth = 11;
					currentCalYear--;
				}
				renderCalendarGrid();
			});

			$popup.find('.js-cal-next').on('click', function(e) {
				e.preventDefault();
				currentCalMonth++;
				if (currentCalMonth > 11) {
					currentCalMonth = 0;
					currentCalYear++;
				}
				renderCalendarGrid();
			});

			// Bind Today button
			$popup.find('.js-cal-today').on('click', function(e) {
				e.preventDefault();
				if ($activeDatePickerInput && $activeDatePickerInput.length) {
					var today = new Date();
					var formatted = formatDateDMY(today.getDate(), today.getMonth(), today.getFullYear());
					$activeDatePickerInput.val(formatted).trigger('input').trigger('change');
				}
				closeCalendar();
			});

			// Bind Clear button
			$popup.find('.js-cal-clear').on('click', function(e) {
				e.preventDefault();
				if ($activeDatePickerInput && $activeDatePickerInput.length) {
					$activeDatePickerInput.val('').trigger('input').trigger('change');
				}
				closeCalendar();
			});

			// Prevent calendar clicks from closing itself
			$popup.on('click', function(e) {
				e.stopPropagation();
			});

			return $popup;
		}

		function renderCalendarGrid() {
			if (!$calendarPopup) return;

			$calendarPopup.find('.js-cal-month-select').val(currentCalMonth);
			$calendarPopup.find('.js-cal-year-select').val(currentCalYear);

			var $daysGrid = $calendarPopup.find('.nds-hr-cal-days');
			$daysGrid.empty();

			var firstDayIndex = new Date(currentCalYear, currentCalMonth, 1).getDay(); // 0 = Sunday
			var daysInMonth = new Date(currentCalYear, currentCalMonth + 1, 0).getDate();

			// Selected date in active input
			var selectedDate = $activeDatePickerInput ? parseDateStr($activeDatePickerInput.val()) : null;
			var today = new Date();

			// Empty cells before day 1
			for (var i = 0; i < firstDayIndex; i++) {
				$daysGrid.append('<button type="button" class="nds-hr-cal-day empty" tabindex="-1" disabled></button>');
			}

			// Days of current month
			for (var day = 1; day <= daysInMonth; day++) {
				var isToday = (today.getFullYear() === currentCalYear && today.getMonth() === currentCalMonth && today.getDate() === day);
				var isSelected = (selectedDate && selectedDate.getFullYear() === currentCalYear && selectedDate.getMonth() === currentCalMonth && selectedDate.getDate() === day);

				var dayClass = 'nds-hr-cal-day';
				if (isToday) dayClass += ' today';
				if (isSelected) dayClass += ' selected';

				var $btn = $('<button type="button" class="' + dayClass + '" data-day="' + day + '">' + day + '</button>');
				$daysGrid.append($btn);
			}

			// Attach click handler for days
			$daysGrid.find('.nds-hr-cal-day:not(.empty)').on('click', function(e) {
				e.preventDefault();
				var chosenDay = parseInt($(this).data('day'), 10);
				var formattedVal = formatDateDMY(chosenDay, currentCalMonth, currentCalYear);

				if ($activeDatePickerInput && $activeDatePickerInput.length) {
					$activeDatePickerInput.val(formattedVal).trigger('input').trigger('change');
				}
				closeCalendar();
			});
		}

		function openCalendarFor($input) {
			$activeDatePickerInput = $input;
			$calendarPopup = createCalendarDOM();

			// Parse existing value or default to current date
			var existingDate = parseDateStr($input.val());
			if (existingDate && !isNaN(existingDate.getTime())) {
				currentCalYear = existingDate.getFullYear();
				currentCalMonth = existingDate.getMonth();
			} else {
				var now = new Date();
				currentCalYear = now.getFullYear();
				currentCalMonth = now.getMonth();
			}

			renderCalendarGrid();

			// Position calendar under the input
			var inputOffset = $input.offset();
			var inputHeight = $input.outerHeight();
			var inputWidth  = $input.outerWidth();
			var calHeight   = 290;
			var calWidth    = 296;

			var top = inputOffset.top + inputHeight + 4;
			var left = inputOffset.left;

			// Viewport bounds check (vertical)
			var windowScrollTop = $(window).scrollTop();
			var windowHeight = $(window).height();
			if (top + calHeight > windowScrollTop + windowHeight && inputOffset.top - calHeight > windowScrollTop) {
				top = inputOffset.top - calHeight - 4;
			}

			// Viewport bounds check (horizontal for RTL / LTR)
			if (isRtl) {
				left = (inputOffset.left + inputWidth) - calWidth;
			}
			if (left < 10) left = 10;

			$calendarPopup.css({
				top: top + 'px',
				left: left + 'px'
			}).fadeIn(120);
		}

		function closeCalendar() {
			if ($calendarPopup) {
				$calendarPopup.fadeOut(100);
			}
			$activeDatePickerInput = null;
		}

		// Bind Datepicker inputs and calendar icon buttons
		$(document).on('click', '.js-datepicker', function(e) {
			e.stopPropagation();
			openCalendarFor($(this));
		});

		$(document).on('click', '.js-datepicker-toggle', function(e) {
			e.preventDefault();
			e.stopPropagation();
			var $targetInput = $(this).siblings('.js-datepicker');
			if ($targetInput.length) {
				openCalendarFor($targetInput);
			}
		});

		// Close calendar on click outside or ESC key
		$(document).on('click', function() {
			closeCalendar();
		});

		$(document).on('keydown', function(e) {
			if (e.key === 'Escape' || e.keyCode === 27) {
				closeCalendar();
			}
		});

		// =========================================================================
		// 2. Contract Duration Live Calculation
		// =========================================================================

		function calculateDuration(startStr, endStr) {
			var start = parseDateStr(startStr);
			var end   = parseDateStr(endStr);

			if (!start) {
				return isRtl ? 'غير محدد / عقد مفتوح' : 'Indefinite / Open-ended';
			}
			if (!end) {
				return isRtl ? 'عقد مفتوح / غير محدد المدة' : 'Open-ended / Indefinite contract';
			}

			if (end < start) {
				return isRtl ? 'نطاق غير صحيح (تاريخ النهاية يسبق تاريخ البداية)' : 'Invalid range (End date is before start date)';
			}

			var diffTime = Math.abs(end - start);
			var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

			var years = Math.floor(diffDays / 365);
			var remainingDays = diffDays % 365;
			var months = Math.floor(remainingDays / 30);
			var days = remainingDays % 30;

			var parts = [];
			if (years > 0) {
				parts.push(years + (isRtl ? ' سنة' : (years === 1 ? ' Year' : ' Years')));
			}
			if (months > 0) {
				parts.push(months + (isRtl ? ' شهر' : (months === 1 ? ' Month' : ' Months')));
			}
			if (days > 0 || parts.length === 0) {
				parts.push(days + (isRtl ? ' يوم' : (days === 1 ? ' Day' : ' Days')));
			}

			return parts.join(', ');
		}

		$('.js-contract-date').on('input change blur', function() {
			var start = $('#contract_start_date').val();
			var end   = $('#contract_end_date').val();
			var duration = calculateDuration(start, end);
			$('#nds-hr-duration-text').text(duration);
		});

		// =========================================================================
		// 3. Phone Country Code Switcher
		// =========================================================================

		$('#phone_country_code').on('change', function() {
			var country = $(this).val();
			var $mobile = $('#mobile');
			var $hint   = $('#nds-hr-phone-hint');

			if ('+966' === country) {
				$mobile.attr('placeholder', '5xxxxxxxx');
				$hint.text(isRtl ? 'صيغة الجوال السعودي: 9 أرقام تبدأ بالرقم 5 (مثال: 50 123 4567)' : 'Saudi mobile format: 9 digits starting with 5 (e.g. 50 123 4567)');
			} else {
				$mobile.attr('placeholder', '10xxxxxxxx');
				$hint.text(isRtl ? 'صيغة الموبايل المصري: 10 أرقام تبدأ بـ 10 أو 11 أو 12 أو 15 (مثال: 10 1234 5678)' : 'Egypt mobile format: 10 digits starting with 10, 11, 12, or 15 (e.g. 10 1234 5678)');
			}
		});

		// =========================================================================
		// 4. Profile Photo Live Preview
		// =========================================================================

		$('#profile_photo').on('change', function(e) {
			var file = e.target.files && e.target.files[0];
			if (file) {
				if (file.size > 5 * 1024 * 1024) {
					alert(isRtl ? 'حجم الصورة المحددة يتجاوز 5 ميغابايت.' : 'Selected photo exceeds 5MB limit.');
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

		$('#remove_profile_photo').on('change', function() {
			if ($(this).is(':checked')) {
				$('#nds-hr-avatar-preview').hide();
				$('#nds-hr-avatar-placeholder').show();
			}
		});

		// =========================================================================
		// 5. Independent NDS HR Account Credential Management
		// =========================================================================

		function generateSecurePassword(length) {
			length = length || 14;
			var uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
			var lowercase = 'abcdefghijkmnopqrstuvwxyz';
			var numbers = '23456789';
			var symbols = '!@#$%^&*()-_=+[]{}';
			var allChars = uppercase + lowercase + numbers + symbols;

			var password = '';
			password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
			password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
			password += numbers.charAt(Math.floor(Math.random() * numbers.length));
			password += symbols.charAt(Math.floor(Math.random() * symbols.length));

			for (var i = password.length; i < length; i++) {
				password += allChars.charAt(Math.floor(Math.random() * allChars.length));
			}

			return password.split('').sort(function() { return 0.5 - Math.random(); }).join('');
		}

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

		$('.js-toggle-account-creation').on('change', function() {
			if ($(this).is(':checked')) {
				$('#nds-hr-account-credential-fields').slideDown(150);
			} else {
				$('#nds-hr-account-credential-fields').slideUp(150);
			}
		});

		$('#email').on('input blur', function() {
			var emailVal = $(this).val();
			var $usernameField = $('#account_username');

			if (emailVal && $usernameField.length && !$usernameField.val()) {
				var suggested = emailVal.split('@')[0].replace(/[^a-zA-Z0-9._-]/g, '').toLowerCase();
				$usernameField.val(suggested);
			}
			updateLiveCredentialsPreview();
		});

		$('.js-generate-password-btn').on('click', function(e) {
			e.preventDefault();
			var generated = generateSecurePassword(14);
			$('#account_password').val(generated).attr('type', 'text');
			$('.js-pw-toggle-text').text('Hide');
			updateLiveCredentialsPreview();
		});

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

		$('.js-copy-staged-creds-btn').on('click', function(e) {
			e.preventDefault();
			var textToCopy = $('#nds-hr-live-creds-text').text();
			if (navigator.clipboard && textToCopy) {
				navigator.clipboard.writeText(textToCopy).then(function() {
					var $btn = $('.js-copy-staged-creds-btn');
					$btn.text(isRtl ? 'تم النسخ للحافظة!' : 'Copied to Clipboard!');
					setTimeout(function() {
						$btn.html('<span class="dashicons dashicons-clipboard"></span> ' + (isRtl ? 'نسخ بيانات الدخول' : 'Copy Credentials'));
					}, 2500);
				});
			}
		});

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
					$btnText.text(isRtl ? 'تم نسخ بيانات الدخول!' : 'Credentials Copied!');
					setTimeout(function() {
						$btnText.text(isRtl ? 'نسخ بيانات الدخول' : 'Copy Credentials');
					}, 3000);
				});
			}
		});

		// =========================================================================
		// Global Page Navigation Progress Bar
		// =========================================================================
		function initGlobalProgressBar() {
			if (!$('#nds-hr-global-progress').length) {
				$('body').prepend(
					'<div id="nds-hr-global-progress" role="progressbar" aria-label="Loading page..." aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">' +
						'<div class="nds-hr-progress-inner"></div>' +
					'</div>'
				);
			}

			var $bar = $('#nds-hr-global-progress');
			var $inner = $bar.find('.nds-hr-progress-inner');

			function startProgress() {
				$bar.addClass('is-loading');
				$inner.css({ width: '30%', opacity: 1 });
				setTimeout(function() {
					$inner.css({ width: '75%' });
				}, 100);
			}

			function completeProgress() {
				$inner.css({ width: '100%' });
				setTimeout(function() {
					$inner.css({ opacity: 0 });
					setTimeout(function() {
						$bar.removeClass('is-loading');
						$inner.css({ width: '0%' });
					}, 200);
				}, 150);
			}

			// Intercept standard navigation links (excluding hash links, target=_blank, modals)
			$(document).on('click', 'a', function(e) {
				var href = $(this).attr('href');
				var target = $(this).attr('target');
				var isModal = $(this).hasClass('js-open-modal') || $(this).data('toggle') === 'modal';

				if (!href || href.startsWith('#') || href.startsWith('javascript:') || target === '_blank' || isModal) {
					return;
				}

				// Only start progress for internal navigation
				if (href.indexOf(window.location.host) !== -1 || href.startsWith('/') || !href.startsWith('http')) {
					startProgress();
				}
			});

			// Complete progress when DOM is fully loaded or page shows
			$(window).on('pageshow', function() {
				completeProgress();
			});
		}

		initGlobalProgressBar();

	});

})(jQuery);
