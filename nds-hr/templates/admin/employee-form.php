<?php
/**
 * Admin Employee Form Template (Create & Edit).
 * Refined Workforce & Contract Management with Independent NDS HR Accounts.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir     = NDS_HR_I18n::get_direction();
$is_rtl  = NDS_HR_I18n::is_rtl();
$is_edit = isset( $employee ) && ! empty( $employee->id );
$title   = $is_edit ? __( 'Edit Employee Profile', 'nds-hr' ) : __( 'Add New Employee', 'nds-hr' );

// Format Dates for UI Display (DD/MM/YYYY)
$formatted_dob            = $is_edit && ! empty( $employee->date_of_birth ) ? NDS_HR_Security::format_date_for_display( $employee->date_of_birth ) : '';
$formatted_hire_date      = $is_edit && ! empty( $employee->hire_date ) ? NDS_HR_Security::format_date_for_display( $employee->hire_date ) : date( 'd/m/Y' );
$formatted_contract_start = $is_edit && ! empty( $employee->contract_start_date ) ? NDS_HR_Security::format_date_for_display( $employee->contract_start_date ) : $formatted_hire_date;
$formatted_contract_end   = $is_edit && ! empty( $employee->contract_end_date ) ? NDS_HR_Security::format_date_for_display( $employee->contract_end_date ) : '';

// Calculate initial contract duration
$initial_duration = $is_edit && ! empty( $employee->contract_start_date )
	? NDS_HR_Security::calculate_contract_duration( $employee->contract_start_date, $employee->contract_end_date )
	: __( 'Open-ended / Indefinite contract', 'nds-hr' );

// Determine Phone Country Code and Local Number
$phone_country = '+20';
$local_mobile  = '';

if ( $is_edit ) {
	$raw_phone = ! empty( $employee->mobile ) ? (string) $employee->mobile : ( ! empty( $employee->phone ) ? (string) $employee->phone : '' );
	if ( ! empty( $employee->phone_country_code ) ) {
		$phone_country = $employee->phone_country_code;
	} elseif ( 0 === strpos( $raw_phone, '+966' ) ) {
		$phone_country = '+966';
	}

	// Extract digits without prefix
	$digits = preg_replace( '/\D/', '', $raw_phone );
	if ( '+966' === $phone_country && 0 === strpos( $digits, '966' ) ) {
		$local_mobile = substr( $digits, 3 );
	} elseif ( '+20' === $phone_country && 0 === strpos( $digits, '20' ) ) {
		$local_mobile = substr( $digits, 2 );
	} else {
		$local_mobile = $digits;
	}
}

// Current HR Account Role
$current_account_role = 'hr_employee';
if ( ! empty( $hr_user ) && ! empty( $hr_user->role_slug ) ) {
	$current_account_role = $hr_user->role_slug;
}

// Basic Salary & Currency (Optional)
$salary_value = '';
if ( $is_edit && isset( $employee->basic_salary ) && null !== $employee->basic_salary && '' !== $employee->basic_salary ) {
	$salary_num = (float) $employee->basic_salary;
	$salary_value = $salary_num > 0 ? number_format( $salary_num, 2, '.', '' ) : '';
}
$salary_currency = $is_edit && ! empty( $employee->salary_currency ) ? $employee->salary_currency : 'EGP';
?>

<div class="nds-hr-wrap" dir="<?php echo esc_attr( $dir ); ?>">

	<!-- Top Header -->
	<header class="nds-hr-header">
		<div class="nds-hr-header-main">
			<div>
				<h1 class="nds-hr-title"><?php echo esc_html( $title ); ?></h1>
				<p class="nds-hr-subtitle">
					<?php echo esc_html( $is_edit ? sprintf( __( 'Editing workforce profile: %s (%s)', 'nds-hr' ), $employee->full_name, $employee->employee_id ) : __( 'Register a new workforce member, employment contract, and configure independent NDS HR credentials.', 'nds-hr' ) ); ?>
				</p>
			</div>
			<div class="nds-hr-header-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-employees' ) ); ?>" class="nds-hr-btn nds-hr-btn-outline">
					<span class="dashicons dashicons-arrow-left-alt"></span>
					<?php esc_html_e( 'Back to Directory', 'nds-hr' ); ?>
				</a>
			</div>
		</div>
	</header>

	<!-- Main Form with File Upload Support -->
	<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-employees' ) ); ?>" class="nds-hr-form" id="nds-hr-employee-form" enctype="multipart/form-data">
		<input type="hidden" name="nds_hr_admin_action" value="save_employee">
		<input type="hidden" name="employee_db_id" value="<?php echo esc_attr( $is_edit ? $employee->id : 0 ); ?>">
		<?php wp_nonce_field( 'nds_hr_save_employee' ); ?>

		<div class="nds-hr-form-layout">

			<!-- Left Column: Primary Sections -->
			<div class="nds-hr-form-main">

				<!-- SECTION 1: Personal Information -->
				<div class="nds-hr-card nds-hr-form-section">
					<div class="nds-hr-card-header">
						<h2 class="nds-hr-card-title">
							<span class="dashicons dashicons-admin-users nds-hr-card-icon"></span>
							<?php esc_html_e( '1. Personal Information', 'nds-hr' ); ?>
						</h2>
					</div>

					<div class="nds-hr-fields-row">
						<!-- First Name -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="first_name"><?php esc_html_e( 'First Name *', 'nds-hr' ); ?></label>
							<input
								type="text"
								id="first_name"
								name="first_name"
								value="<?php echo esc_attr( $is_edit ? $employee->first_name : '' ); ?>"
								required
								class="nds-hr-input"
								placeholder="e.g. Ahmed"
							>
						</div>

						<!-- Last Name -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="last_name"><?php esc_html_e( 'Last Name *', 'nds-hr' ); ?></label>
							<input
								type="text"
								id="last_name"
								name="last_name"
								value="<?php echo esc_attr( $is_edit ? $employee->last_name : '' ); ?>"
								required
								class="nds-hr-input"
								placeholder="e.g. Mansoor"
							>
						</div>
					</div>

					<div class="nds-hr-fields-row">
						<!-- Corporate Email -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="email"><?php esc_html_e( 'Corporate Email Address *', 'nds-hr' ); ?></label>
							<input
								type="email"
								id="email"
								name="email"
								value="<?php echo esc_attr( $is_edit ? $employee->email : '' ); ?>"
								required
								class="nds-hr-input"
								placeholder="ahmed.mansoor@example.com"
							>
						</div>

						<!-- Personal Mobile with Egypt / Saudi Arabia Selector -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="mobile">
								<?php esc_html_e( 'Personal Mobile', 'nds-hr' ); ?>
								<small class="nds-hr-muted-text" style="font-weight: normal; margin-left: 4px;">(<?php esc_html_e( 'Optional', 'nds-hr' ); ?>)</small>
							</label>
							<div class="nds-hr-phone-input-group" style="display: flex; gap: 6px;">
								<select id="phone_country_code" name="phone_country_code" class="nds-hr-select" style="width: 140px; flex-shrink: 0;">
									<option value="+20" <?php selected( $phone_country, '+20' ); ?>>🇪🇬 +20 (Egypt)</option>
									<option value="+966" <?php selected( $phone_country, '+966' ); ?>>🇸🇦 +966 (Saudi)</option>
								</select>
								<input
									type="tel"
									id="mobile"
									name="mobile"
									value="<?php echo esc_attr( $local_mobile ); ?>"
									class="nds-hr-input"
									placeholder="<?php echo '+966' === $phone_country ? '5xxxxxxxx' : '10xxxxxxxx'; ?>"
									style="flex: 1;"
								>
							</div>
							<small class="nds-hr-help-text" id="nds-hr-phone-hint">
								<?php echo '+966' === $phone_country ? esc_html__( 'Saudi mobile format: 9 digits starting with 5 (e.g. 50 123 4567)', 'nds-hr' ) : esc_html__( 'Egypt mobile format: 10 digits starting with 10, 11, 12, or 15 (e.g. 10 1234 5678)', 'nds-hr' ); ?>
							</small>
						</div>
					</div>

					<div class="nds-hr-fields-row">
						<!-- Date of Birth (DD/MM/YYYY) with Mini Calendar -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="date_of_birth">
								<?php esc_html_e( 'Date of Birth', 'nds-hr' ); ?>
								<small class="nds-hr-muted-text" style="font-weight: normal; margin-left: 4px;">(DD/MM/YYYY)</small>
							</label>
							<div class="nds-hr-datepicker-wrap">
								<input
									type="text"
									id="date_of_birth"
									name="date_of_birth"
									value="<?php echo esc_attr( $formatted_dob ); ?>"
									class="nds-hr-input js-datepicker js-date-field"
									placeholder="DD/MM/YYYY"
									autocomplete="off"
								>
								<button type="button" class="nds-hr-datepicker-btn js-datepicker-toggle" aria-label="<?php esc_attr_e( 'Choose date', 'nds-hr' ); ?>">
									<span class="dashicons dashicons-calendar-alt"></span>
								</button>
							</div>
							<small class="nds-hr-help-text"><?php esc_html_e( 'Click to select date visually from calendar.', 'nds-hr' ); ?></small>
						</div>

						<!-- Gender -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="gender"><?php esc_html_e( 'Gender', 'nds-hr' ); ?></label>
							<select id="gender" name="gender" class="nds-hr-select">
								<option value="male" <?php selected( $is_edit ? $employee->gender : 'male', 'male' ); ?>><?php esc_html_e( 'Male', 'nds-hr' ); ?></option>
								<option value="female" <?php selected( $is_edit ? $employee->gender : '', 'female' ); ?>><?php esc_html_e( 'Female', 'nds-hr' ); ?></option>
								<option value="other" <?php selected( $is_edit ? $employee->gender : '', 'other' ); ?>><?php esc_html_e( 'Other', 'nds-hr' ); ?></option>
							</select>
						</div>
					</div>

					<div class="nds-hr-fields-row">
						<!-- National ID / Iqama / Passport -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="national_id"><?php esc_html_e( 'National ID / Iqama / Passport', 'nds-hr' ); ?></label>
							<input
								type="text"
								id="national_id"
								name="national_id"
								value="<?php echo esc_attr( $is_edit ? $employee->national_id : '' ); ?>"
								class="nds-hr-input"
								placeholder="e.g. 1029384756"
							>
						</div>

						<!-- Residential Address -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="address"><?php esc_html_e( 'Residential Address', 'nds-hr' ); ?></label>
							<input
								type="text"
								id="address"
								name="address"
								value="<?php echo esc_attr( $is_edit ? $employee->address : '' ); ?>"
								class="nds-hr-input"
								placeholder="City, District, Street"
							>
						</div>
					</div>
				</div>

				<!-- SECTION 2: Profile Photo Upload -->
				<div class="nds-hr-card nds-hr-form-section">
					<div class="nds-hr-card-header">
						<h2 class="nds-hr-card-title">
							<span class="dashicons dashicons-format-image nds-hr-card-icon"></span>
							<?php esc_html_e( '2. Profile Photo', 'nds-hr' ); ?>
						</h2>
					</div>

					<div class="nds-hr-photo-upload-container" style="display: flex; gap: 20px; align-items: center;">
						<!-- Image Preview Thumbnail -->
						<div class="nds-hr-photo-preview-box" style="width: 96px; height: 96px; border-radius: 50%; border: 2px dashed #CBD5E1; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #F8FAFC; position: relative; flex-shrink: 0;">
							<?php if ( $is_edit && ! empty( $employee->profile_photo_url ) ) : ?>
								<img id="nds-hr-avatar-preview" src="<?php echo esc_url( $employee->profile_photo_url ); ?>" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
							<?php else : ?>
								<img id="nds-hr-avatar-preview" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
								<span id="nds-hr-avatar-placeholder" class="dashicons dashicons-camera" style="font-size: 36px; width: 36px; height: 36px; color: #94A3B8;"></span>
							<?php endif; ?>
						</div>

						<!-- Upload Controls -->
						<div style="flex: 1;">
							<label class="nds-hr-label" for="profile_photo">
								<?php esc_html_e( 'Upload Employee Photo (JPG or PNG, max 5MB)', 'nds-hr' ); ?>
							</label>
							<input
								type="file"
								id="profile_photo"
								name="profile_photo"
								accept="image/png, image/jpeg, image/jpg"
								class="nds-hr-file-input"
							>
							<small class="nds-hr-help-text" style="display: block; margin-top: 4px;">
								<?php esc_html_e( 'Secure server-side validation. Uploading a new photo replaces the existing photo.', 'nds-hr' ); ?>
							</small>

							<?php if ( $is_edit && ! empty( $employee->profile_photo_url ) ) : ?>
								<div style="margin-top: 8px;">
									<label class="nds-hr-checkbox-label" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #DC2626; cursor: pointer;">
										<input type="checkbox" name="remove_profile_photo" id="remove_profile_photo" value="1">
										<span><?php esc_html_e( 'Remove current photo', 'nds-hr' ); ?></span>
									</label>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<!-- SECTION 3: Employment Information -->
				<div class="nds-hr-card nds-hr-form-section">
					<div class="nds-hr-card-header">
						<h2 class="nds-hr-card-title">
							<span class="dashicons dashicons-businesswoman nds-hr-card-icon"></span>
							<?php esc_html_e( '3. Employment Information', 'nds-hr' ); ?>
						</h2>
					</div>

					<div class="nds-hr-fields-row">
						<!-- Employee ID Code -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="employee_id"><?php esc_html_e( 'Employee ID / Code *', 'nds-hr' ); ?></label>
							<input
								type="text"
								id="employee_id"
								name="employee_id"
								value="<?php echo esc_attr( $is_edit ? $employee->employee_id : $next_code ); ?>"
								required
								class="nds-hr-input"
								placeholder="e.g. NDS-00001"
							>
						</div>

						<!-- Hire Date with Mini Calendar -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="hire_date">
								<?php esc_html_e( 'Hire Date *', 'nds-hr' ); ?>
								<small class="nds-hr-muted-text" style="font-weight: normal; margin-left: 4px;">(DD/MM/YYYY)</small>
							</label>
							<div class="nds-hr-datepicker-wrap">
								<input
									type="text"
									id="hire_date"
									name="hire_date"
									value="<?php echo esc_attr( $formatted_hire_date ); ?>"
									required
									class="nds-hr-input js-datepicker js-date-field"
									placeholder="DD/MM/YYYY"
									autocomplete="off"
								>
								<button type="button" class="nds-hr-datepicker-btn js-datepicker-toggle" aria-label="<?php esc_attr_e( 'Choose date', 'nds-hr' ); ?>">
									<span class="dashicons dashicons-calendar-alt"></span>
								</button>
							</div>
						</div>
					</div>

					<div class="nds-hr-fields-row">
						<!-- Department -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="department_id"><?php esc_html_e( 'Department', 'nds-hr' ); ?></label>
							<select id="department_id" name="department_id" class="nds-hr-select">
								<option value="0"><?php esc_html_e( 'Select Department', 'nds-hr' ); ?></option>
								<?php foreach ( $departments as $dept ) : ?>
									<option value="<?php echo esc_attr( $dept->id ); ?>" <?php selected( $is_edit ? $employee->department_id : 0, $dept->id ); ?>>
										<?php echo esc_html( $dept->name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<!-- Position -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="position_id"><?php esc_html_e( 'Position', 'nds-hr' ); ?></label>
							<select id="position_id" name="position_id" class="nds-hr-select">
								<option value="0"><?php esc_html_e( 'Select Position', 'nds-hr' ); ?></option>
								<?php foreach ( $positions as $pos ) : ?>
									<option value="<?php echo esc_attr( $pos->id ); ?>" <?php selected( $is_edit ? $employee->position_id : 0, $pos->id ); ?>>
										<?php echo esc_html( $pos->title ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="nds-hr-fields-row">
						<!-- Employment Status -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="employment_status"><?php esc_html_e( 'Employment Status *', 'nds-hr' ); ?></label>
							<select id="employment_status" name="employment_status" class="nds-hr-select">
								<option value="active" <?php selected( $is_edit ? $employee->employment_status : 'active', 'active' ); ?>><?php esc_html_e( 'Active', 'nds-hr' ); ?></option>
								<option value="inactive" <?php selected( $is_edit ? $employee->employment_status : '', 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'nds-hr' ); ?></option>
								<option value="terminated" <?php selected( $is_edit ? $employee->employment_status : '', 'terminated' ); ?>><?php esc_html_e( 'Terminated', 'nds-hr' ); ?></option>
								<option value="suspended" <?php selected( $is_edit ? $employee->employment_status : '', 'suspended' ); ?>><?php esc_html_e( 'Suspended', 'nds-hr' ); ?></option>
							</select>
						</div>

						<!-- Basic Salary (Optional + Currency Selector) -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="basic_salary">
								<?php esc_html_e( 'Basic Salary', 'nds-hr' ); ?>
								<small class="nds-hr-muted-text" style="font-weight: normal; margin-left: 4px;">(<?php esc_html_e( 'Optional', 'nds-hr' ); ?>)</small>
							</label>
							<div class="nds-hr-salary-input-group" style="display: flex; gap: 6px;">
								<select id="salary_currency" name="salary_currency" class="nds-hr-select" style="width: 140px; flex-shrink: 0;">
									<option value="EGP" <?php selected( $salary_currency, 'EGP' ); ?>>EGP — <?php esc_html_e( 'Egyptian Pound', 'nds-hr' ); ?> (ج.م)</option>
									<option value="SAR" <?php selected( $salary_currency, 'SAR' ); ?>>SAR — <?php esc_html_e( 'Saudi Riyal', 'nds-hr' ); ?> (ر.س)</option>
								</select>
								<input
									type="number"
									step="0.01"
									min="0"
									id="basic_salary"
									name="basic_salary"
									value="<?php echo esc_attr( $salary_value ); ?>"
									class="nds-hr-input"
									placeholder="0.00"
									style="flex: 1;"
								>
							</div>
							<small class="nds-hr-help-text"><?php esc_html_e( 'Optional compensation amount. ISO currency code stored separately.', 'nds-hr' ); ?></small>
						</div>
					</div>
				</div>

				<!-- SECTION 4: Employment Contract -->
				<div class="nds-hr-card nds-hr-form-section">
					<div class="nds-hr-card-header">
						<h2 class="nds-hr-card-title">
							<span class="dashicons dashicons-media-document nds-hr-card-icon"></span>
							<?php esc_html_e( '4. Employment Contract', 'nds-hr' ); ?>
						</h2>
					</div>

					<div class="nds-hr-fields-row">
						<!-- Contract Type -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="contract_type"><?php esc_html_e( 'Contract Type *', 'nds-hr' ); ?></label>
							<select id="contract_type" name="contract_type" class="nds-hr-select">
								<option value="permanent" <?php selected( $is_edit && isset( $employee->contract_type ) ? $employee->contract_type : 'permanent', 'permanent' ); ?>><?php esc_html_e( 'Permanent', 'nds-hr' ); ?></option>
								<option value="fixed_term" <?php selected( $is_edit && isset( $employee->contract_type ) ? $employee->contract_type : '', 'fixed_term' ); ?>><?php esc_html_e( 'Fixed Term', 'nds-hr' ); ?></option>
								<option value="temporary" <?php selected( $is_edit && isset( $employee->contract_type ) ? $employee->contract_type : '', 'temporary' ); ?>><?php esc_html_e( 'Temporary', 'nds-hr' ); ?></option>
								<option value="probation" <?php selected( $is_edit && isset( $employee->contract_type ) ? $employee->contract_type : '', 'probation' ); ?>><?php esc_html_e( 'Probation', 'nds-hr' ); ?></option>
								<option value="other" <?php selected( $is_edit && isset( $employee->contract_type ) ? $employee->contract_type : '', 'other' ); ?>><?php esc_html_e( 'Other', 'nds-hr' ); ?></option>
							</select>
						</div>

						<!-- Real-Time Duration Badge -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label"><?php esc_html_e( 'Contract Duration', 'nds-hr' ); ?></label>
							<div id="nds-hr-contract-duration-display" style="padding: 9px 12px; background: #F1F5F9; border: 1px solid #CBD5E1; border-radius: 6px; font-weight: 600; color: #0F766E; font-size: 13px;">
								<span class="dashicons dashicons-clock" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle; margin-right: 4px;"></span>
								<span id="nds-hr-duration-text"><?php echo esc_html( $initial_duration ); ?></span>
							</div>
						</div>
					</div>

					<div class="nds-hr-fields-row">
						<!-- Contract Start Date with Mini Calendar -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="contract_start_date">
								<?php esc_html_e( 'Contract Start Date', 'nds-hr' ); ?>
								<small class="nds-hr-muted-text" style="font-weight: normal; margin-left: 4px;">(DD/MM/YYYY)</small>
							</label>
							<div class="nds-hr-datepicker-wrap">
								<input
									type="text"
									id="contract_start_date"
									name="contract_start_date"
									value="<?php echo esc_attr( $formatted_contract_start ); ?>"
									class="nds-hr-input js-datepicker js-contract-date js-date-field"
									placeholder="DD/MM/YYYY"
									autocomplete="off"
								>
								<button type="button" class="nds-hr-datepicker-btn js-datepicker-toggle" aria-label="<?php esc_attr_e( 'Choose date', 'nds-hr' ); ?>">
									<span class="dashicons dashicons-calendar-alt"></span>
								</button>
							</div>
						</div>

						<!-- Contract End Date with Mini Calendar -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="contract_end_date">
								<?php esc_html_e( 'Contract End Date', 'nds-hr' ); ?>
								<small class="nds-hr-muted-text" style="font-weight: normal; margin-left: 4px;">(<?php esc_html_e( 'Leave empty for open-ended', 'nds-hr' ); ?>)</small>
							</label>
							<div class="nds-hr-datepicker-wrap">
								<input
									type="text"
									id="contract_end_date"
									name="contract_end_date"
									value="<?php echo esc_attr( $formatted_contract_end ); ?>"
									class="nds-hr-input js-datepicker js-contract-date js-date-field"
									placeholder="DD/MM/YYYY"
									autocomplete="off"
								>
								<button type="button" class="nds-hr-datepicker-btn js-datepicker-toggle" aria-label="<?php esc_attr_e( 'Choose date', 'nds-hr' ); ?>">
									<span class="dashicons dashicons-calendar-alt"></span>
								</button>
							</div>
						</div>
					</div>

					<!-- Contract Document Upload -->
					<div class="nds-hr-field-group" style="margin-top: 14px; padding-top: 14px; border-top: 1px dashed #E2E8F0;">
						<label class="nds-hr-label" for="contract_document">
							<?php esc_html_e( 'Contract Document (PDF, DOC, DOCX - max 10MB)', 'nds-hr' ); ?>
						</label>
						<input
							type="file"
							id="contract_document"
							name="contract_document"
							accept=".pdf, .doc, .docx, application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document"
							class="nds-hr-file-input"
						>

						<?php if ( $is_edit && ! empty( $employee->contract_document_url ) ) : ?>
							<div class="nds-hr-current-doc-box" style="margin-top: 10px; padding: 10px 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; display: flex; align-items: center; justify-content: space-between;">
								<div style="display: flex; align-items: center; gap: 8px;">
									<span class="dashicons dashicons-media-document" style="color: #0D9488;"></span>
									<a href="<?php echo esc_url( $employee->contract_document_url ); ?>" target="_blank" style="font-weight: 600; font-size: 13px; color: #0F766E;">
										<?php echo esc_html( ! empty( $employee->contract_document_name ) ? $employee->contract_document_name : __( 'View Current Contract Document', 'nds-hr' ) ); ?>
									</a>
								</div>
								<label class="nds-hr-checkbox-label" style="font-size: 12px; color: #DC2626; cursor: pointer; display: flex; align-items: center; gap: 4px;">
									<input type="checkbox" name="remove_contract_document" value="1">
									<span><?php esc_html_e( 'Remove document', 'nds-hr' ); ?></span>
								</label>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- SECTION 5: Emergency Contacts -->
				<div class="nds-hr-card nds-hr-form-section">
					<div class="nds-hr-card-header">
						<h2 class="nds-hr-card-title">
							<span class="dashicons dashicons-phone nds-hr-card-icon"></span>
							<?php esc_html_e( '5. Emergency Contact', 'nds-hr' ); ?>
						</h2>
					</div>

					<div class="nds-hr-fields-row">
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="emergency_contact_name"><?php esc_html_e( 'Contact Person Name', 'nds-hr' ); ?></label>
							<input
								type="text"
								id="emergency_contact_name"
								name="emergency_contact_name"
								value="<?php echo esc_attr( $is_edit ? $employee->emergency_contact_name : '' ); ?>"
								class="nds-hr-input"
								placeholder="e.g. Sara Mansoor"
							>
						</div>

						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="emergency_contact_phone"><?php esc_html_e( 'Emergency Phone Number', 'nds-hr' ); ?></label>
							<input
								type="text"
								id="emergency_contact_phone"
								name="emergency_contact_phone"
								value="<?php echo esc_attr( $is_edit ? $employee->emergency_contact_phone : '' ); ?>"
								class="nds-hr-input"
								placeholder="+20 10xxxxxxxx / +966 5xxxxxxxx"
							>
						</div>
					</div>

					<div class="nds-hr-field-group">
						<label class="nds-hr-label" for="emergency_contact_relationship"><?php esc_html_e( 'Relationship', 'nds-hr' ); ?></label>
						<input
							type="text"
							id="emergency_contact_relationship"
							name="emergency_contact_relationship"
							value="<?php echo esc_attr( $is_edit ? $employee->emergency_contact_relationship : '' ); ?>"
							class="nds-hr-input"
							placeholder="e.g. Spouse, Parent, Sibling"
						>
					</div>
				</div>

				<?php
				// Section: Custom Fields (Phase 2C-1: ONLY on Add Employee; do not render on Edit yet)
				$active_custom_fields = ! $is_edit ? NDS_HR_Custom_Fields::get_active_fields( 'employee' ) : array();
				if ( ! empty( $active_custom_fields ) ) :
				?>
				<!-- SECTION 6: Custom Fields (Add Employee Only) -->
				<div class="nds-hr-card nds-hr-form-section" id="nds-hr-custom-fields-section">
					<div class="nds-hr-card-header">
						<h2 class="nds-hr-card-title">
							<span class="dashicons dashicons-forms nds-hr-card-icon"></span>
							<?php esc_html_e( '6. Custom Fields', 'nds-hr' ); ?>
						</h2>
					</div>

					<div class="nds-hr-custom-fields-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
						<?php foreach ( $active_custom_fields as $cf ) :
							$field_key      = $cf->field_key;
							$field_label    = $cf->field_label;
							$field_type     = $cf->field_type;
							$is_required    = ! empty( $cf->is_required );
							$description    = $cf->description;
							$settings       = is_array( $cf->settings ) ? $cf->settings : array();
							$options        = isset( $settings['options'] ) && is_array( $settings['options'] ) ? $settings['options'] : array();
							$input_name     = 'custom_fields[' . esc_attr( $field_key ) . ']';
							$field_id_attr  = 'cf_' . esc_attr( $field_key );
							$submitted_val  = isset( $_POST['custom_fields'][ $field_key ] ) ? $_POST['custom_fields'][ $field_key ] : null;
						?>
							<div class="nds-hr-field-group" style="<?php echo 'textarea' === $field_type ? 'grid-column: 1 / -1;' : ''; ?>">
								<label class="nds-hr-label" for="<?php echo esc_attr( $field_id_attr ); ?>">
									<?php echo esc_html( $field_label ); ?>
									<?php if ( $is_required ) : ?>
										<span style="color: #DC2626; margin-left: 2px;">*</span>
									<?php endif; ?>
								</label>

								<?php switch ( $field_type ) :
									case 'textarea': ?>
										<textarea
											id="<?php echo esc_attr( $field_id_attr ); ?>"
											name="<?php echo esc_attr( $input_name ); ?>"
											rows="3"
											class="nds-hr-input"
											<?php echo $is_required ? 'required' : ''; ?>
											placeholder="<?php echo esc_attr( $field_label ); ?>"
										><?php echo esc_textarea( null !== $submitted_val ? (string) $submitted_val : '' ); ?></textarea>
										<?php break; ?>

									<?php case 'number': ?>
										<input
											type="number"
											step="any"
											id="<?php echo esc_attr( $field_id_attr ); ?>"
											name="<?php echo esc_attr( $input_name ); ?>"
											value="<?php echo esc_attr( null !== $submitted_val ? (string) $submitted_val : '' ); ?>"
											class="nds-hr-input"
											<?php echo $is_required ? 'required' : ''; ?>
											placeholder="0"
										>
										<?php break; ?>

									<?php case 'email': ?>
										<input
											type="email"
											id="<?php echo esc_attr( $field_id_attr ); ?>"
											name="<?php echo esc_attr( $input_name ); ?>"
											value="<?php echo esc_attr( null !== $submitted_val ? (string) $submitted_val : '' ); ?>"
											class="nds-hr-input"
											<?php echo $is_required ? 'required' : ''; ?>
											placeholder="user@example.com"
										>
										<?php break; ?>

									<?php case 'phone': ?>
										<input
											type="tel"
											id="<?php echo esc_attr( $field_id_attr ); ?>"
											name="<?php echo esc_attr( $input_name ); ?>"
											value="<?php echo esc_attr( null !== $submitted_val ? (string) $submitted_val : '' ); ?>"
											class="nds-hr-input"
											<?php echo $is_required ? 'required' : ''; ?>
											placeholder="+20 10xxxxxxxx / +966 5xxxxxxxx"
										>
										<?php break; ?>

									<?php case 'date': ?>
										<div class="nds-hr-datepicker-wrap">
											<input
												type="text"
												id="<?php echo esc_attr( $field_id_attr ); ?>"
												name="<?php echo esc_attr( $input_name ); ?>"
												value="<?php echo esc_attr( null !== $submitted_val ? (string) $submitted_val : '' ); ?>"
												class="nds-hr-input js-datepicker js-date-field"
												placeholder="DD/MM/YYYY"
												autocomplete="off"
												<?php echo $is_required ? 'required' : ''; ?>
											>
											<button type="button" class="nds-hr-datepicker-btn js-datepicker-toggle" aria-label="<?php esc_attr_e( 'Choose date', 'nds-hr' ); ?>">
												<span class="dashicons dashicons-calendar-alt"></span>
											</button>
										</div>
										<?php break; ?>

									<?php case 'select': ?>
										<select
											id="<?php echo esc_attr( $field_id_attr ); ?>"
											name="<?php echo esc_attr( $input_name ); ?>"
											class="nds-hr-select"
											<?php echo $is_required ? 'required' : ''; ?>
										>
											<option value=""><?php esc_html_e( '-- Select Option --', 'nds-hr' ); ?></option>
											<?php foreach ( $options as $opt ) :
												$opt_val = is_array( $opt ) ? ( $opt['value'] ?? '' ) : ( is_object( $opt ) ? ( $opt->value ?? '' ) : $opt );
												$opt_lbl = is_array( $opt ) ? ( $opt['label'] ?? $opt_val ) : ( is_object( $opt ) ? ( $opt->label ?? $opt_val ) : $opt );
											?>
												<option value="<?php echo esc_attr( $opt_val ); ?>" <?php selected( (string) $submitted_val, (string) $opt_val ); ?>>
													<?php echo esc_html( $opt_lbl ); ?>
												</option>
											<?php endforeach; ?>
										</select>
										<?php break; ?>

									<?php case 'multiselect': ?>
										<select
											id="<?php echo esc_attr( $field_id_attr ); ?>"
											name="<?php echo esc_attr( $input_name ); ?>[]"
											multiple
											class="nds-hr-select"
											style="min-height: 90px;"
											<?php echo $is_required ? 'required' : ''; ?>
										>
											<?php
											$selected_array = is_array( $submitted_val ) ? $submitted_val : ( null !== $submitted_val ? array( $submitted_val ) : array() );
											foreach ( $options as $opt ) :
												$opt_val = is_array( $opt ) ? ( $opt['value'] ?? '' ) : ( is_object( $opt ) ? ( $opt->value ?? '' ) : $opt );
												$opt_lbl = is_array( $opt ) ? ( $opt['label'] ?? $opt_val ) : ( is_object( $opt ) ? ( $opt->label ?? $opt_val ) : $opt );
											?>
												<option value="<?php echo esc_attr( $opt_val ); ?>" <?php echo in_array( (string) $opt_val, array_map( 'strval', $selected_array ), true ) ? 'selected' : ''; ?>>
													<?php echo esc_html( $opt_lbl ); ?>
												</option>
											<?php endforeach; ?>
										</select>
										<?php break; ?>

									<?php case 'radio': ?>
										<div class="nds-hr-radio-group" style="display: flex; flex-direction: column; gap: 6px; margin-top: 4px;">
											<?php foreach ( $options as $opt_idx => $opt ) :
												$opt_val = is_array( $opt ) ? ( $opt['value'] ?? '' ) : ( is_object( $opt ) ? ( $opt->value ?? '' ) : $opt );
												$opt_lbl = is_array( $opt ) ? ( $opt['label'] ?? $opt_val ) : ( is_object( $opt ) ? ( $opt->label ?? $opt_val ) : $opt );
												$radio_id = $field_id_attr . '_' . $opt_idx;
											?>
												<label for="<?php echo esc_attr( $radio_id ); ?>" class="nds-hr-radio-label" style="display: flex; align-items: center; gap: 6px; font-weight: normal; cursor: pointer;">
													<input
														type="radio"
														id="<?php echo esc_attr( $radio_id ); ?>"
														name="<?php echo esc_attr( $input_name ); ?>"
														value="<?php echo esc_attr( $opt_val ); ?>"
														<?php checked( (string) $submitted_val, (string) $opt_val ); ?>
														<?php echo $is_required ? 'required' : ''; ?>
													>
													<span><?php echo esc_html( $opt_lbl ); ?></span>
												</label>
											<?php endforeach; ?>
										</div>
										<?php break; ?>

									<?php case 'checkbox': ?>
										<div class="nds-hr-checkbox-group" style="display: flex; flex-direction: column; gap: 6px; margin-top: 4px;">
											<?php
											$checked_array = is_array( $submitted_val ) ? $submitted_val : ( null !== $submitted_val ? array( $submitted_val ) : array() );
											if ( empty( $options ) ) :
											?>
												<label for="<?php echo esc_attr( $field_id_attr ); ?>" class="nds-hr-checkbox-label" style="display: flex; align-items: center; gap: 6px; font-weight: normal; cursor: pointer;">
													<input
														type="checkbox"
														id="<?php echo esc_attr( $field_id_attr ); ?>"
														name="<?php echo esc_attr( $input_name ); ?>"
														value="1"
														<?php checked( ! empty( $submitted_val ) ); ?>
														<?php echo $is_required ? 'required' : ''; ?>
													>
													<span><?php echo esc_html( $field_label ); ?></span>
												</label>
											<?php else :
												foreach ( $options as $opt_idx => $opt ) :
													$opt_val = is_array( $opt ) ? ( $opt['value'] ?? '' ) : ( is_object( $opt ) ? ( $opt->value ?? '' ) : $opt );
													$opt_lbl = is_array( $opt ) ? ( $opt['label'] ?? $opt_val ) : ( is_object( $opt ) ? ( $opt->label ?? $opt_val ) : $opt );
													$cb_id   = $field_id_attr . '_' . $opt_idx;
												?>
													<label for="<?php echo esc_attr( $cb_id ); ?>" class="nds-hr-checkbox-label" style="display: flex; align-items: center; gap: 6px; font-weight: normal; cursor: pointer;">
														<input
															type="checkbox"
															id="<?php echo esc_attr( $cb_id ); ?>"
															name="<?php echo esc_attr( $input_name ); ?>[]"
															value="<?php echo esc_attr( $opt_val ); ?>"
															<?php echo in_array( (string) $opt_val, array_map( 'strval', $checked_array ), true ) ? 'checked' : ''; ?>
														>
														<span><?php echo esc_html( $opt_lbl ); ?></span>
													</label>
												<?php endforeach;
											endif; ?>
										</div>
										<?php break; ?>

									<?php case 'yes_no': ?>
										<div class="nds-hr-yesno-group" style="display: flex; gap: 16px; margin-top: 4px;">
											<label class="nds-hr-radio-label" style="display: flex; align-items: center; gap: 6px; font-weight: normal; cursor: pointer;">
												<input
													type="radio"
													name="<?php echo esc_attr( $input_name ); ?>"
													value="1"
													<?php checked( (string) $submitted_val, '1' ); ?>
													<?php echo $is_required ? 'required' : ''; ?>
												>
												<span><?php esc_html_e( 'Yes', 'nds-hr' ); ?></span>
											</label>
											<label class="nds-hr-radio-label" style="display: flex; align-items: center; gap: 6px; font-weight: normal; cursor: pointer;">
												<input
													type="radio"
													name="<?php echo esc_attr( $input_name ); ?>"
													value="0"
													<?php checked( (string) $submitted_val, '0' ); ?>
													<?php echo $is_required ? 'required' : ''; ?>
												>
												<span><?php esc_html_e( 'No', 'nds-hr' ); ?></span>
											</label>
										</div>
										<?php break; ?>

									<?php default: // 'text' ?>
										<input
											type="text"
											id="<?php echo esc_attr( $field_id_attr ); ?>"
											name="<?php echo esc_attr( $input_name ); ?>"
											value="<?php echo esc_attr( null !== $submitted_val ? (string) $submitted_val : '' ); ?>"
											class="nds-hr-input"
											<?php echo $is_required ? 'required' : ''; ?>
											placeholder="<?php echo esc_attr( $field_label ); ?>"
										>
										<?php break; ?>
								<?php endswitch; ?>

								<?php if ( ! empty( $description ) ) : ?>
									<small class="nds-hr-help-text" style="margin-top: 4px; display: block; color: #64748B; font-size: 11px;">
										<?php echo esc_html( $description ); ?>
									</small>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

			</div>

			<!-- Right Column: NDS HR Account & Save Action -->
			<div class="nds-hr-form-sidebar">

				<!-- Submit Card -->
				<div class="nds-hr-card nds-hr-form-section">
					<div class="nds-hr-card-header">
						<h2 class="nds-hr-card-title"><?php esc_html_e( 'Actions', 'nds-hr' ); ?></h2>
					</div>
					<div class="nds-hr-publish-actions">
						<button type="submit" class="nds-hr-btn nds-hr-btn-primary nds-hr-btn-block" id="nds-hr-submit-btn">
							<span class="dashicons dashicons-saved"></span>
							<?php echo esc_html( $is_edit ? __( 'Update Employee Profile', 'nds-hr' ) : __( 'Save Employee', 'nds-hr' ) ); ?>
						</button>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-employees' ) ); ?>" class="nds-hr-btn nds-hr-btn-outline nds-hr-btn-block text-center" style="margin-top: 8px;">
							<?php esc_html_e( 'Cancel', 'nds-hr' ); ?>
						</a>
					</div>
				</div>

				<!-- Independent NDS HR Account Configuration Card -->
				<div class="nds-hr-card nds-hr-form-section">
					<div class="nds-hr-card-header">
						<h2 class="nds-hr-card-title">
							<span class="dashicons dashicons-shield-alt nds-hr-card-icon"></span>
							<?php esc_html_e( 'NDS HR Account & Role', 'nds-hr' ); ?>
						</h2>
					</div>

					<p class="nds-hr-muted-text" style="font-size: 13px; line-height: 1.4; margin-bottom: 14px;">
						<?php esc_html_e( 'Manage independent NDS HR application credentials and role permissions. No WordPress users or wp-admin access are created.', 'nds-hr' ); ?>
					</p>

					<!-- Account Role Selection (Employee vs Admin) -->
					<div class="nds-hr-field-group">
						<label class="nds-hr-label" for="hr_account_role">
							<?php esc_html_e( 'NDS HR Role *', 'nds-hr' ); ?>
						</label>
						<select id="hr_account_role" name="hr_account_role" class="nds-hr-select" style="font-weight: 600;">
							<option value="hr_employee" <?php selected( $current_account_role, 'hr_employee' ); ?>><?php esc_html_e( 'Employee (Portal Access)', 'nds-hr' ); ?></option>
							<option value="hr_admin" <?php selected( $current_account_role, 'hr_admin' ); ?>><?php esc_html_e( 'Admin (Full HR Management)', 'nds-hr' ); ?></option>
						</select>
						<small class="nds-hr-help-text">
							<?php esc_html_e( 'Employee: can view self-profile/attendance. Admin: can manage all HR modules.', 'nds-hr' ); ?>
						</small>
					</div>

					<?php if ( $is_edit && ! empty( $hr_user ) ) : ?>
						<!-- Existing HR Account Info -->
						<div class="nds-hr-account-linked-box" style="margin-top: 14px; background: #F0FDF4; border: 1px solid #BBF7D0; padding: 12px; border-radius: 6px;">
							<div style="display: flex; align-items: center; gap: 8px;">
								<span class="dashicons dashicons-yes-alt" style="color: #16A34A; font-size: 20px;"></span>
								<div>
									<strong><?php esc_html_e( 'NDS HR Account Active', 'nds-hr' ); ?></strong>
									<div style="font-size: 13px; font-family: monospace; color: #166534;"><?php echo esc_html( $hr_user->username ); ?></div>
								</div>
							</div>
						</div>

						<!-- Reset Password for Existing HR Account -->
						<div class="nds-hr-field-group" style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed #E2E8F0;">
							<div style="display: flex; justify-content: space-between; align-items: center;">
								<label class="nds-hr-label" for="account_password" style="margin-bottom: 0;">
									<?php esc_html_e( 'Reset Password', 'nds-hr' ); ?>
								</label>
								<button type="button" class="js-generate-password-btn" style="background: none; border: none; color: #0D9488; font-size: 12px; cursor: pointer; text-decoration: underline; padding: 0;">
									<?php esc_html_e( 'Auto-Generate', 'nds-hr' ); ?>
								</button>
							</div>
							<div style="position: relative; display: flex; align-items: center; margin-top: 4px;">
								<input 
									type="password" 
									id="account_password" 
									name="account_password" 
									class="nds-hr-input" 
									style="padding-right: 70px; font-family: monospace; font-size: 13px;"
									placeholder="<?php esc_attr_e( 'Leave blank to keep existing', 'nds-hr' ); ?>"
									autocomplete="new-password"
								>
								<button type="button" class="js-toggle-pw-visibility" style="position: absolute; right: 8px; background: none; border: none; color: #64748B; font-size: 11px; cursor: pointer; padding: 2px 6px;">
									<span class="dashicons dashicons-visibility" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span>
									<span class="js-pw-toggle-text"><?php esc_html_e( 'Show', 'nds-hr' ); ?></span>
								</button>
							</div>
						</div>

						<!-- Force Password Change on Next Login -->
						<div style="margin-top: 10px;">
							<label class="nds-hr-checkbox-label" style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer;">
								<input type="checkbox" name="require_password_change" value="1" <?php checked( ! empty( $hr_user->require_password_change ) ); ?> style="margin-top: 3px;">
								<span style="font-size: 13px; color: #1E293B;">
									<strong><?php esc_html_e( 'Require password change on next login', 'nds-hr' ); ?></strong>
								</span>
							</label>
						</div>

					<?php else : ?>

						<!-- New Account Creation Toggle & Credentials -->
						<div style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed #E2E8F0;">
							<label class="nds-hr-checkbox-label" style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; margin-bottom: 12px;">
								<input type="checkbox" name="create_hr_account" id="create_hr_account" value="1" checked style="margin-top: 3px;" class="js-toggle-account-creation">
								<span style="font-size: 13px; color: #1E293B;">
									<strong><?php esc_html_e( 'Create NDS HR Login Account', 'nds-hr' ); ?></strong>
									<br>
									<small style="color: #64748B;"><?php esc_html_e( 'Allows user to log in at /login/ using email/username and password.', 'nds-hr' ); ?></small>
								</span>
							</label>

							<div id="nds-hr-account-credential-fields" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px; border-radius: 8px;">
								
								<!-- Username -->
								<div class="nds-hr-field-group">
									<label class="nds-hr-label" for="account_username">
										<?php esc_html_e( 'Username', 'nds-hr' ); ?>
									</label>
									<input type="text" id="account_username" name="account_username" class="nds-hr-input" placeholder="e.g. ahmed.mansoor" autocomplete="off">
									<small class="nds-hr-help-text"><?php esc_html_e( 'Auto-suggested from email.', 'nds-hr' ); ?></small>
								</div>

								<!-- Password Methods -->
								<div class="nds-hr-field-group" style="margin-top: 10px;">
									<div style="display: flex; justify-content: space-between; align-items: center;">
										<label class="nds-hr-label" for="account_password" style="margin-bottom: 0;">
											<?php esc_html_e( 'Password', 'nds-hr' ); ?>
										</label>
										<button type="button" class="js-generate-password-btn" style="background: none; border: none; color: #0D9488; font-size: 12px; cursor: pointer; text-decoration: underline; padding: 0;">
											<?php esc_html_e( 'Generate Password', 'nds-hr' ); ?>
										</button>
									</div>
									<div style="position: relative; display: flex; align-items: center; margin-top: 4px;">
										<input 
											type="password" 
											id="account_password" 
											name="account_password" 
											class="nds-hr-input" 
											style="padding-right: 70px; font-family: monospace; font-size: 13px;"
											placeholder="<?php esc_attr_e( 'Auto-generated if left blank', 'nds-hr' ); ?>"
											autocomplete="new-password"
										>
										<button type="button" class="js-toggle-pw-visibility" style="position: absolute; right: 8px; background: none; border: none; color: #64748B; font-size: 11px; cursor: pointer; padding: 2px 6px;">
											<span class="dashicons dashicons-visibility" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span>
											<span class="js-pw-toggle-text"><?php esc_html_e( 'Show', 'nds-hr' ); ?></span>
										</button>
									</div>
								</div>

								<!-- Live Credentials Preview for Admin -->
								<div id="nds-hr-live-credentials-preview" style="display: none; background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 6px; padding: 10px; margin-top: 10px;">
									<div style="font-size: 11px; font-weight: bold; color: #166534; text-transform: uppercase; margin-bottom: 4px;">
										<?php esc_html_e( 'Generated Credentials Staged:', 'nds-hr' ); ?>
									</div>
									<div style="font-size: 12px; font-family: monospace; color: #14532D;" id="nds-hr-live-creds-text"></div>
									<button type="button" class="nds-hr-btn nds-hr-btn-outline js-copy-staged-creds-btn" style="font-size: 11px; padding: 3px 8px; margin-top: 6px; background: #FFF;">
										<span class="dashicons dashicons-clipboard" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
										<?php esc_html_e( 'Copy Credentials', 'nds-hr' ); ?>
									</button>
								</div>

								<!-- Force Password Change -->
								<div style="margin-top: 12px; padding-top: 8px; border-top: 1px solid #E2E8F0;">
									<label class="nds-hr-checkbox-label" style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer;">
										<input type="checkbox" name="require_password_change" value="1" checked style="margin-top: 3px;">
										<span style="font-size: 12px; color: #1E293B;">
											<strong><?php esc_html_e( 'Require password change on first login', 'nds-hr' ); ?></strong>
										</span>
									</label>
								</div>

							</div>
						</div>

					<?php endif; ?>
				</div>

			</div>

		</div>
	</form>

</div>
