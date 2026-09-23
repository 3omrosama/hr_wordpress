<?php
/**
 * Admin Employee Form Template (Create & Edit).
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir     = NDS_HR_I18n::get_direction();
$is_rtl  = NDS_HR_I18n::is_rtl();
$is_edit = isset( $employee ) && ! empty( $employee->id );
$title   = $is_edit ? __( 'Edit Employee', 'nds-hr' ) : __( 'Add New Employee', 'nds-hr' );
?>

<div class="nds-hr-wrap" dir="<?php echo esc_attr( $dir ); ?>">

	<!-- Top Header -->
	<header class="nds-hr-header">
		<div class="nds-hr-header-main">
			<div>
				<h1 class="nds-hr-title"><?php echo esc_html( $title ); ?></h1>
				<p class="nds-hr-subtitle">
					<?php echo esc_html( $is_edit ? sprintf( __( 'Editing workforce profile: %s (%s)', 'nds-hr' ), $employee->full_name, $employee->employee_id ) : __( 'Register a new workforce member and configure their corporate account credentials.', 'nds-hr' ) ); ?>
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

	<!-- Main Form -->
	<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-employees' ) ); ?>" class="nds-hr-form" id="nds-hr-employee-form">
		<input type="hidden" name="nds_hr_admin_action" value="save_employee">
		<input type="hidden" name="employee_db_id" value="<?php echo esc_attr( $is_edit ? $employee->id : 0 ); ?>">
		<?php wp_nonce_field( 'nds_hr_save_employee' ); ?>

		<div class="nds-hr-form-layout">

			<!-- Left Column: Primary Employee Profile -->
			<div class="nds-hr-form-main">

				<!-- Card 1: Identification & Personal Info -->
				<div class="nds-hr-card nds-hr-form-section">
					<div class="nds-hr-card-header">
						<h2 class="nds-hr-card-title"><?php esc_html_e( 'Identity & Personal Data', 'nds-hr' ); ?></h2>
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
							<small class="nds-hr-help-text"><?php esc_html_e( 'Unique organization personnel identifier.', 'nds-hr' ); ?></small>
						</div>

						<!-- National ID / Iqama -->
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
								placeholder="e.g. Al-Mansoor"
							>
						</div>
					</div>

					<div class="nds-hr-fields-row">
						<!-- Email -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="email"><?php esc_html_e( 'Corporate Email Address *', 'nds-hr' ); ?></label>
							<input
								type="email"
								id="email"
								name="email"
								value="<?php echo esc_attr( $is_edit ? $employee->email : '' ); ?>"
								required
								class="nds-hr-input"
								placeholder="ahmed@example.com"
							>
						</div>

						<!-- Phone -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="phone"><?php esc_html_e( 'Office Phone', 'nds-hr' ); ?></label>
							<input
								type="text"
								id="phone"
								name="phone"
								value="<?php echo esc_attr( $is_edit ? $employee->phone : '' ); ?>"
								class="nds-hr-input"
								placeholder="+966 11 000 0000"
							>
						</div>
					</div>

					<div class="nds-hr-fields-row">
						<!-- Mobile -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="mobile"><?php esc_html_e( 'Personal Mobile', 'nds-hr' ); ?></label>
							<input
								type="text"
								id="mobile"
								name="mobile"
								value="<?php echo esc_attr( $is_edit ? $employee->mobile : '' ); ?>"
								class="nds-hr-input"
								placeholder="+966 50 000 0000"
							>
						</div>

						<!-- Date of Birth -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="date_of_birth"><?php esc_html_e( 'Date of Birth', 'nds-hr' ); ?></label>
							<input
								type="date"
								id="date_of_birth"
								name="date_of_birth"
								value="<?php echo esc_attr( $is_edit ? $employee->date_of_birth : '' ); ?>"
								class="nds-hr-input"
							>
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

					<!-- Physical Residential Address -->
					<div class="nds-hr-field-group">
						<label class="nds-hr-label" for="address"><?php esc_html_e( 'Residential Address', 'nds-hr' ); ?></label>
						<textarea id="address" name="address" rows="2" class="nds-hr-textarea" placeholder="Street, Building, City, Country"><?php echo esc_textarea( $is_edit ? $employee->address : '' ); ?></textarea>
					</div>

					<!-- Profile Photo URL -->
					<div class="nds-hr-field-group">
						<label class="nds-hr-label" for="profile_photo_url"><?php esc_html_e( 'Profile Photo URL', 'nds-hr' ); ?></label>
						<input
							type="url"
							id="profile_photo_url"
							name="profile_photo_url"
							value="<?php echo esc_attr( $is_edit ? $employee->profile_photo_url : '' ); ?>"
							placeholder="https://example.com/photo.jpg"
							class="nds-hr-input"
						>
					</div>
				</div>

				<!-- Card 2: Employment & Compensation Details -->
				<div class="nds-hr-card nds-hr-form-section">
					<div class="nds-hr-card-header">
						<h2 class="nds-hr-card-title"><?php esc_html_e( 'Employment Details', 'nds-hr' ); ?></h2>
					</div>

					<div class="nds-hr-fields-row">
						<!-- Hire Date -->
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="hire_date"><?php esc_html_e( 'Hire Date *', 'nds-hr' ); ?></label>
							<input
								type="date"
								id="hire_date"
								name="hire_date"
								value="<?php echo esc_attr( $is_edit ? $employee->hire_date : current_time( 'Y-m-d' ) ); ?>"
								required
								class="nds-hr-input"
							>
						</div>

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

					<!-- Basic Salary Placeholder (Phase 1) -->
					<div class="nds-hr-field-group">
						<label class="nds-hr-label" for="basic_salary"><?php esc_html_e( 'Basic Salary Placeholder', 'nds-hr' ); ?></label>
						<input
							type="number"
							step="0.01"
							id="basic_salary"
							name="basic_salary"
							value="<?php echo esc_attr( $is_edit ? $employee->basic_salary : '0.00' ); ?>"
							class="nds-hr-input"
						>
						<small class="nds-hr-help-text"><?php esc_html_e( 'Compensation structure will be expanded in Payroll Phase.', 'nds-hr' ); ?></small>
					</div>
				</div>

				<!-- Card 3: Emergency Contacts -->
				<div class="nds-hr-card nds-hr-form-section">
					<div class="nds-hr-card-header">
						<h2 class="nds-hr-card-title"><?php esc_html_e( 'Emergency Contacts', 'nds-hr' ); ?></h2>
					</div>

					<div class="nds-hr-fields-row">
						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="emergency_contact_name"><?php esc_html_e( 'Emergency Contact Name', 'nds-hr' ); ?></label>
							<input
								type="text"
								id="emergency_contact_name"
								name="emergency_contact_name"
								value="<?php echo esc_attr( $is_edit ? $employee->emergency_contact_name : '' ); ?>"
								class="nds-hr-input"
							>
						</div>

						<div class="nds-hr-field-group">
							<label class="nds-hr-label" for="emergency_contact_phone"><?php esc_html_e( 'Contact Phone Number', 'nds-hr' ); ?></label>
							<input
								type="text"
								id="emergency_contact_phone"
								name="emergency_contact_phone"
								value="<?php echo esc_attr( $is_edit ? $employee->emergency_contact_phone : '' ); ?>"
								class="nds-hr-input"
							>
						</div>
					</div>

					<div class="nds-hr-field-group">
						<label class="nds-hr-label" for="emergency_contact_relationship"><?php esc_html_e( 'Relationship (e.g., Spouse, Parent, Sibling)', 'nds-hr' ); ?></label>
						<input
							type="text"
							id="emergency_contact_relationship"
							name="emergency_contact_relationship"
							value="<?php echo esc_attr( $is_edit ? $employee->emergency_contact_relationship : '' ); ?>"
							class="nds-hr-input"
						>
					</div>
				</div>

			</div>

			<!-- Right Sidebar: WordPress Account & Save Actions -->
			<div class="nds-hr-form-sidebar">

				<!-- Card: Submit Actions -->
				<div class="nds-hr-card nds-hr-form-section">
					<div class="nds-hr-card-header">
						<h2 class="nds-hr-card-title"><?php esc_html_e( 'Publishing', 'nds-hr' ); ?></h2>
					</div>
					<div class="nds-hr-publish-actions">
						<button type="submit" class="nds-hr-btn nds-hr-btn-primary nds-hr-btn-block" id="nds-hr-submit-btn">
							<span class="dashicons dashicons-saved"></span>
							<?php echo esc_html( $is_edit ? __( 'Update Employee', 'nds-hr' ) : __( 'Save Employee', 'nds-hr' ) ); ?>
						</button>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-employees' ) ); ?>" class="nds-hr-btn nds-hr-btn-outline nds-hr-btn-block text-center">
							<?php esc_html_e( 'Cancel', 'nds-hr' ); ?>
						</a>
					</div>
				</div>

				<!-- Card: WordPress User Account Integration -->
				<div class="nds-hr-card nds-hr-form-section">
					<div class="nds-hr-card-header">
						<h2 class="nds-hr-card-title"><?php esc_html_e( 'Employee Account Onboarding', 'nds-hr' ); ?></h2>
					</div>

					<?php if ( $is_edit && ! empty( $employee->user_id ) ) : 
						$wp_user = get_user_by( 'id', $employee->user_id );
					?>
						<div class="nds-hr-account-linked-box">
							<span class="dashicons dashicons-yes-alt nds-hr-icon-success"></span>
							<div>
								<strong><?php esc_html_e( 'Linked to WordPress User:', 'nds-hr' ); ?></strong>
								<div><?php echo esc_html( $wp_user ? $wp_user->user_login : 'User #' . $employee->user_id ); ?></div>
								<small class="nds-hr-muted-text"><?php echo esc_html( $wp_user ? $wp_user->user_email : '' ); ?></small>
							</div>
						</div>
						<input type="hidden" name="user_id" value="<?php echo esc_attr( $employee->user_id ); ?>">
					<?php else : ?>
						<p class="nds-hr-muted-text" style="font-size: 13px; line-height: 1.4; margin-bottom: 12px;">
							<?php esc_html_e( 'Configure system access credentials so this employee can authenticate at /login/ and access the employee portal.', 'nds-hr' ); ?>
						</p>

						<!-- Account Action Options -->
						<div class="nds-hr-radio-group" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
							<label class="nds-hr-radio-label">
								<input type="radio" name="account_action" value="none" checked class="nds-hr-radio js-account-action-toggle">
								<span><?php esc_html_e( 'Do not create account now', 'nds-hr' ); ?></span>
							</label>

							<label class="nds-hr-radio-label">
								<input type="radio" name="account_action" value="create" class="nds-hr-radio js-account-action-toggle">
								<span><strong><?php esc_html_e( 'Create new WordPress login account', 'nds-hr' ); ?></strong></span>
							</label>

							<?php if ( ! empty( $wp_users ) ) : ?>
								<label class="nds-hr-radio-label">
									<input type="radio" name="account_action" value="link" class="nds-hr-radio js-account-action-toggle">
									<span><?php esc_html_e( 'Link existing WordPress user', 'nds-hr' ); ?></span>
								</label>
							<?php endif; ?>
						</div>

						<!-- Create User Subfields -->
						<div id="nds-hr-create-user-fields" class="nds-hr-subfields" style="display: none; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px; border-radius: 8px; margin-top: 10px;">
							
							<!-- Username -->
							<div class="nds-hr-field-group">
								<label class="nds-hr-label" for="account_username">
									<?php esc_html_e( 'Username *', 'nds-hr' ); ?>
								</label>
								<input type="text" id="account_username" name="account_username" class="nds-hr-input" placeholder="e.g. ahmed.almansoor" autocomplete="off">
								<small class="nds-hr-help-text"><?php esc_html_e( 'Employee will use this or email to log in at /login/', 'nds-hr' ); ?></small>
							</div>

							<!-- Account Email Note -->
							<div class="nds-hr-field-group">
								<label class="nds-hr-label">
									<?php esc_html_e( 'Account Email', 'nds-hr' ); ?>
								</label>
								<div id="nds-hr-synced-email" style="font-size: 13px; color: #475569; padding: 6px 10px; background: #FFF; border: 1px dashed #CBD5E1; border-radius: 4px;">
									<span class="dashicons dashicons-email" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle; margin-right: 4px;"></span>
									<span class="js-email-display"><?php esc_html_e( 'Synced with Corporate Email', 'nds-hr' ); ?></span>
								</div>
							</div>

							<!-- Password Mode Selector: Manual vs Generated -->
							<div class="nds-hr-field-group" style="margin-top: 12px;">
								<label class="nds-hr-label">
									<?php esc_html_e( 'Password Method', 'nds-hr' ); ?>
								</label>
								<div style="display: flex; gap: 8px; margin-bottom: 8px;">
									<button type="button" class="nds-hr-btn nds-hr-btn-outline js-pw-mode-btn" data-mode="generate" style="flex: 1; font-size: 12px; padding: 6px 10px;">
										<span class="dashicons dashicons-admin-network" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
										<?php esc_html_e( 'Generate Secure', 'nds-hr' ); ?>
									</button>
									<button type="button" class="nds-hr-btn nds-hr-btn-outline js-pw-mode-btn" data-mode="manual" style="flex: 1; font-size: 12px; padding: 6px 10px;">
										<span class="dashicons dashicons-edit" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
										<?php esc_html_e( 'Enter Manually', 'nds-hr' ); ?>
									</button>
								</div>
							</div>

							<!-- Password Input with Show/Hide & Generate Action -->
							<div class="nds-hr-field-group">
								<div style="display: flex; justify-content: space-between; align-items: center;">
									<label class="nds-hr-label" for="account_password" style="margin-bottom: 0;">
										<?php esc_html_e( 'Password *', 'nds-hr' ); ?>
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
										placeholder="<?php esc_attr_e( 'Leave blank to auto-generate', 'nds-hr' ); ?>"
										autocomplete="new-password"
									>
									<button type="button" class="js-toggle-pw-visibility" style="position: absolute; right: 8px; background: none; border: none; color: #64748B; font-size: 11px; cursor: pointer; padding: 2px 6px;">
										<span class="dashicons dashicons-visibility" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span>
										<span class="js-pw-toggle-text"><?php esc_html_e( 'Show', 'nds-hr' ); ?></span>
									</button>
								</div>
							</div>

							<!-- Confirm Password -->
							<div class="nds-hr-field-group" id="nds-hr-confirm-pw-group">
								<label class="nds-hr-label" for="account_password_confirm">
									<?php esc_html_e( 'Confirm Password', 'nds-hr' ); ?>
								</label>
								<input 
									type="password" 
									id="account_password_confirm" 
									name="account_password_confirm" 
									class="nds-hr-input"
									style="font-family: monospace; font-size: 13px;"
									placeholder="<?php esc_attr_e( 'Repeat password', 'nds-hr' ); ?>"
									autocomplete="new-password"
								>
								<small class="nds-hr-help-text" id="nds-hr-pw-match-indicator" style="display: none;"></small>
							</div>

							<!-- Credential Quick Copy for Admin -->
							<div id="nds-hr-live-credentials-preview" style="display: none; background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 6px; padding: 10px; margin-bottom: 12px;">
								<div style="font-size: 11px; font-weight: bold; color: #166534; text-transform: uppercase; margin-bottom: 4px;">
									<?php esc_html_e( 'Generated Credentials Staged:', 'nds-hr' ); ?>
								</div>
								<div style="font-size: 12px; font-family: monospace; color: #14532D;" id="nds-hr-live-creds-text"></div>
								<button type="button" class="nds-hr-btn nds-hr-btn-outline js-copy-staged-creds-btn" style="font-size: 11px; padding: 3px 8px; margin-top: 6px; background: #FFF;">
									<span class="dashicons dashicons-clipboard" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
									<?php esc_html_e( 'Copy Credentials', 'nds-hr' ); ?>
								</button>
							</div>

							<!-- Force Password Change on First Login -->
							<div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #E2E8F0;">
								<label class="nds-hr-checkbox-label" style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer;">
									<input type="checkbox" name="require_password_change" value="1" checked style="margin-top: 3px;">
									<span style="font-size: 13px; color: #1E293B;">
										<strong><?php esc_html_e( 'Require password change on first login', 'nds-hr' ); ?></strong>
										<br>
										<small style="color: #64748B;"><?php esc_html_e( 'Employee will be prompted to set a private password immediately after authenticating at /login/.', 'nds-hr' ); ?></small>
									</span>
								</label>
							</div>

							<!-- Email Notification -->
							<div style="margin-top: 10px;">
								<label class="nds-hr-checkbox-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
									<input type="checkbox" name="send_account_notification" value="1" checked>
									<span style="font-size: 13px; color: #334155;"><?php esc_html_e( 'Send credentials to employee email', 'nds-hr' ); ?></span>
								</label>
							</div>

						</div>

						<!-- Link Existing User Subfield -->
						<?php if ( ! empty( $wp_users ) ) : ?>
							<div id="nds-hr-link-user-fields" class="nds-hr-subfields" style="display: none; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px; border-radius: 8px; margin-top: 10px;">
								<div class="nds-hr-field-group">
									<label class="nds-hr-label" for="existing_wp_user_id"><?php esc_html_e( 'Select User', 'nds-hr' ); ?></label>
									<select id="existing_wp_user_id" name="existing_wp_user_id" class="nds-hr-select">
										<option value="0"><?php esc_html_e( '-- Choose WP User --', 'nds-hr' ); ?></option>
										<?php foreach ( $wp_users as $u ) : ?>
											<option value="<?php echo esc_attr( $u->ID ); ?>">
												<?php echo esc_html( $u->display_name . ' (' . $u->user_email . ')' ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
						<?php endif; ?>

					<?php endif; ?>
				</div>

			</div>

		</div>
	</form>

</div>
