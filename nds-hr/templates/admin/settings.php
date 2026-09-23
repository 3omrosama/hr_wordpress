<?php
/**
 * Settings Interface Template (Phase 1).
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_section = isset( $section ) ? $section : 'general';
$is_rtl         = NDS_HR_I18n::is_rtl();

$nav_items = array(
	'general'       => array(
		'label' => __( 'General', 'nds-hr' ),
		'icon'  => 'dashicons-admin-generic',
		'desc'  => __( 'Company profile & contact details', 'nds-hr' ),
	),
	'employees'     => array(
		'label' => __( 'Employees', 'nds-hr' ),
		'icon'  => 'dashicons-groups',
		'desc'  => __( 'Employee policies & configurations', 'nds-hr' ),
	),
	'attendance'    => array(
		'label' => __( 'Attendance', 'nds-hr' ),
		'icon'  => 'dashicons-clock',
		'desc'  => __( 'Shifts, timings & tracking', 'nds-hr' ),
	),
	'leave'         => array(
		'label' => __( 'Leave', 'nds-hr' ),
		'icon'  => 'dashicons-calendar-alt',
		'desc'  => __( 'Leave types, allowances & approvals', 'nds-hr' ),
	),
	'payroll'       => array(
		'label' => __( 'Payroll', 'nds-hr' ),
		'icon'  => 'dashicons-money-alt',
		'desc'  => __( 'Salary structures, taxes & payslips', 'nds-hr' ),
	),
	'notifications' => array(
		'label' => __( 'Notifications', 'nds-hr' ),
		'icon'  => 'dashicons-email-alt',
		'desc'  => __( 'Email alerts & system events', 'nds-hr' ),
	),
	'localization'  => array(
		'label' => __( 'Localization', 'nds-hr' ),
		'icon'  => 'dashicons-translation',
		'desc'  => __( 'Default language & date conventions', 'nds-hr' ),
	),
	'security'      => array(
		'label' => __( 'Security', 'nds-hr' ),
		'icon'  => 'dashicons-shield',
		'desc'  => __( 'Access controls & security policies', 'nds-hr' ),
	),
);
?>

<div class="nds-hr-wrap nds-hr-settings-workspace">
	<!-- Top Header Breadcrumb -->
	<div class="nds-hr-header" style="margin-bottom: 24px;">
		<div class="nds-hr-header-title">
			<h1 style="display: flex; align-items: center; gap: 10px; margin: 0; font-size: 24px; font-weight: 700; color: #0F172A;">
				<span class="dashicons dashicons-admin-settings" style="font-size: 28px; width: 28px; height: 28px; color: #0D9488;"></span>
				<?php esc_html_e( 'NDS HR Settings', 'nds-hr' ); ?>
			</h1>
			<p style="margin: 4px 0 0 0; color: #64748B; font-size: 14px;">
				<?php esc_html_e( 'Configure your organization profile, localization, and system preferences.', 'nds-hr' ); ?>
			</p>
		</div>
	</div>

	<?php if ( ! empty( $is_saved ) ) : ?>
		<div class="nds-hr-alert nds-hr-alert-success" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
			<div style="display: flex; align-items: center; gap: 10px;">
				<span class="dashicons dashicons-yes-alt" style="font-size: 20px; width: 20px; height: 20px; color: #059669;"></span>
				<span style="font-weight: 600; font-size: 14px;"><?php esc_html_e( 'Settings updated successfully.', 'nds-hr' ); ?></span>
			</div>
			<a href="<?php echo esc_url( NDS_HR_Router::url( 'settings', array( 'section' => $active_section ) ) ); ?>" style="color: #065F46; text-decoration: none; font-size: 18px; line-height: 1;" title="<?php esc_attr_e( 'Dismiss', 'nds-hr' ); ?>">&times;</a>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $cf_notice ) ) : ?>
		<?php
		$cf_msg = __( 'Custom field operation completed.', 'nds-hr' );
		if ( 'created' === $cf_notice ) {
			$cf_msg = __( 'Custom field created successfully.', 'nds-hr' );
		} elseif ( 'updated' === $cf_notice ) {
			$cf_msg = __( 'Custom field updated successfully.', 'nds-hr' );
		} elseif ( 'activated' === $cf_notice ) {
			$cf_msg = __( 'Custom field activated successfully.', 'nds-hr' );
		} elseif ( 'deactivated' === $cf_notice ) {
			$cf_msg = __( 'Custom field deactivated successfully.', 'nds-hr' );
		} elseif ( 'deleted' === $cf_notice ) {
			$cf_msg = __( 'Custom field permanently deleted.', 'nds-hr' );
		}
		?>
		<div class="nds-hr-alert nds-hr-alert-success" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
			<div style="display: flex; align-items: center; gap: 10px;">
				<span class="dashicons dashicons-yes-alt" style="font-size: 20px; width: 20px; height: 20px; color: #059669;"></span>
				<span style="font-weight: 600; font-size: 14px;"><?php echo esc_html( $cf_msg ); ?></span>
			</div>
			<a href="<?php echo esc_url( NDS_HR_Router::url( 'settings', array( 'section' => 'employees' ) ) ); ?>" style="color: #065F46; text-decoration: none; font-size: 18px; line-height: 1;" title="<?php esc_attr_e( 'Dismiss', 'nds-hr' ); ?>">&times;</a>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $cf_error ) ) : ?>
		<div class="nds-hr-alert nds-hr-alert-error" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
			<div style="display: flex; align-items: center; gap: 10px;">
				<span class="dashicons dashicons-warning" style="font-size: 20px; width: 20px; height: 20px; color: #DC2626;"></span>
				<span style="font-weight: 600; font-size: 14px;"><?php echo esc_html( $cf_error ); ?></span>
			</div>
			<a href="<?php echo esc_url( NDS_HR_Router::url( 'settings', array( 'section' => 'employees' ) ) ); ?>" style="color: #991B1B; text-decoration: none; font-size: 18px; line-height: 1;" title="<?php esc_attr_e( 'Dismiss', 'nds-hr' ); ?>">&times;</a>
		</div>
	<?php endif; ?>

	<!-- SaaS Settings Grid Layout -->
	<div class="nds-hr-settings-grid" style="display: grid; grid-template-columns: 280px 1fr; gap: 24px; align-items: start;">
		
		<!-- Left Settings Navigation Panel -->
		<aside class="nds-hr-card nds-hr-settings-nav-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
			<div style="padding: 8px 12px 12px 12px; border-bottom: 1px solid #F1F5F9; margin-bottom: 8px;">
				<span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #94A3B8;">
					<?php esc_html_e( 'Configuration Modules', 'nds-hr' ); ?>
				</span>
			</div>
			<nav class="nds-hr-settings-nav-menu" style="display: flex; flex-direction: column; gap: 4px;">
				<?php foreach ( $nav_items as $key => $item ) : ?>
					<?php
					$is_current = ( $key === $active_section );
					$item_url   = NDS_HR_Router::url( 'settings', array( 'section' => $key ) );
					?>
					<a href="<?php echo esc_url( $item_url ); ?>" 
					   class="nds-hr-settings-nav-item <?php echo $is_current ? 'active' : ''; ?>"
					   style="display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: <?php echo $is_current ? '600' : '500'; ?>; color: <?php echo $is_current ? '#0D9488' : '#334155'; ?>; background: <?php echo $is_current ? '#F0FDFA' : 'transparent'; ?>; border: 1px solid <?php echo $is_current ? '#CCFBF1' : 'transparent'; ?>; transition: all 0.15s ease;">
						<span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>" style="font-size: 18px; width: 18px; height: 18px; color: <?php echo $is_current ? '#0D9488' : '#64748B'; ?>;"></span>
						<span><?php echo esc_html( $item['label'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		</aside>

		<!-- Right Settings Content Workspace -->
		<section class="nds-hr-card nds-hr-settings-content-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">

			<!-- 1. GENERAL SETTINGS -->
			<?php if ( 'general' === $active_section ) : ?>
				<div class="nds-hr-settings-section">
					<div style="border-bottom: 1px solid #E2E8F0; padding-bottom: 16px; margin-bottom: 24px;">
						<h2 style="margin: 0 0 6px 0; font-size: 18px; font-weight: 700; color: #0F172A;">
							<?php esc_html_e( 'General Settings', 'nds-hr' ); ?>
						</h2>
						<p style="margin: 0; color: #64748B; font-size: 13px;">
							<?php esc_html_e( 'Manage your primary organization details and official contact information.', 'nds-hr' ); ?>
						</p>
					</div>

					<form method="POST" action="<?php echo esc_url( NDS_HR_Router::url( 'settings', array( 'section' => 'general' ) ) ); ?>">
						<?php wp_nonce_field( NDS_HR_Admin_Settings::NONCE_ACTION, '_nds_hr_settings_nonce' ); ?>
						<input type="hidden" name="nds_hr_settings_action" value="save_general">

						<div style="display: flex; flex-direction: column; gap: 20px; max-width: 640px;">
							<!-- Company Name -->
							<div class="nds-hr-form-group">
								<label for="company_name" style="display: block; font-size: 13px; font-weight: 600; color: #1E293B; margin-bottom: 6px;">
									<?php esc_html_e( 'Company Name', 'nds-hr' ); ?>
								</label>
								<input type="text" 
								       id="company_name" 
								       name="company_name" 
								       value="<?php echo esc_attr( $general_settings['company_name'] ); ?>" 
								       class="nds-hr-input"
								       placeholder="<?php esc_attr_e( 'e.g. Acme Corporation', 'nds-hr' ); ?>"
								       style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 14px; box-sizing: border-box;"
								       <?php disabled( ! $can_manage ); ?>>
							</div>

							<!-- Company Email -->
							<div class="nds-hr-form-group">
								<label for="company_email" style="display: block; font-size: 13px; font-weight: 600; color: #1E293B; margin-bottom: 6px;">
									<?php esc_html_e( 'Company Email', 'nds-hr' ); ?>
								</label>
								<input type="email" 
								       id="company_email" 
								       name="company_email" 
								       value="<?php echo esc_attr( $general_settings['company_email'] ); ?>" 
								       class="nds-hr-input"
								       placeholder="<?php esc_attr_e( 'e.g. hr@company.com', 'nds-hr' ); ?>"
								       style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 14px; box-sizing: border-box;"
								       <?php disabled( ! $can_manage ); ?>>
							</div>

							<!-- Company Phone -->
							<div class="nds-hr-form-group">
								<label for="company_phone" style="display: block; font-size: 13px; font-weight: 600; color: #1E293B; margin-bottom: 6px;">
									<?php esc_html_e( 'Company Phone', 'nds-hr' ); ?>
								</label>
								<input type="text" 
								       id="company_phone" 
								       name="company_phone" 
								       value="<?php echo esc_attr( $general_settings['company_phone'] ); ?>" 
								       class="nds-hr-input"
								       placeholder="<?php esc_attr_e( 'e.g. +20 100 000 0000', 'nds-hr' ); ?>"
								       style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 14px; box-sizing: border-box;"
								       <?php disabled( ! $can_manage ); ?>>
							</div>

							<!-- Company Address -->
							<div class="nds-hr-form-group">
								<label for="company_address" style="display: block; font-size: 13px; font-weight: 600; color: #1E293B; margin-bottom: 6px;">
									<?php esc_html_e( 'Company Address', 'nds-hr' ); ?>
								</label>
								<textarea id="company_address" 
								          name="company_address" 
								          rows="3" 
								          class="nds-hr-input"
								          placeholder="<?php esc_attr_e( 'e.g. Building 12, Tech Park, Cairo, Egypt', 'nds-hr' ); ?>"
								          style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 14px; resize: vertical; box-sizing: border-box;"
								          <?php disabled( ! $can_manage ); ?>><?php echo esc_textarea( $general_settings['company_address'] ); ?></textarea>
							</div>

							<!-- Submit Button -->
							<?php if ( $can_manage ) : ?>
								<div style="margin-top: 12px; padding-top: 16px; border-top: 1px solid #F1F5F9; display: flex; justify-content: flex-end;">
									<button type="submit" class="nds-hr-btn nds-hr-btn-primary" style="background: #0D9488; color: #FFF; border: none; padding: 10px 22px; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
										<span class="dashicons dashicons-saved" style="font-size: 16px; width: 16px; height: 16px;"></span>
										<?php esc_html_e( 'Save Changes', 'nds-hr' ); ?>
									</button>
								</div>
							<?php endif; ?>
						</div>
					</form>
				</div>

			<!-- 2. EMPLOYEE SETTINGS (CUSTOM FIELDS) -->
			<?php elseif ( 'employees' === $active_section ) : ?>
				<?php
				$field_type_labels = array(
					'text'        => __( 'Text', 'nds-hr' ),
					'textarea'    => __( 'Long Text (Textarea)', 'nds-hr' ),
					'number'      => __( 'Number', 'nds-hr' ),
					'email'       => __( 'Email Address', 'nds-hr' ),
					'phone'       => __( 'Phone Number', 'nds-hr' ),
					'date'        => __( 'Date', 'nds-hr' ),
					'select'      => __( 'Dropdown (Select)', 'nds-hr' ),
					'multiselect' => __( 'Multi Select', 'nds-hr' ),
					'checkbox'    => __( 'Checkbox (Multi Choice)', 'nds-hr' ),
					'radio'       => __( 'Radio Buttons', 'nds-hr' ),
					'yes_no'      => __( 'Yes / No Toggle', 'nds-hr' ),
				);
				?>
				<div class="nds-hr-settings-section">
					<!-- Section Header -->
					<div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #E2E8F0; padding-bottom: 16px; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
						<div>
							<h2 style="margin: 0 0 6px 0; font-size: 18px; font-weight: 700; color: #0F172A;">
								<?php esc_html_e( 'Employee Custom Fields', 'nds-hr' ); ?>
							</h2>
							<p style="margin: 0; color: #64748B; font-size: 13px;">
								<?php esc_html_e( 'Define and manage custom employee attributes without modifying database tables or code.', 'nds-hr' ); ?>
							</p>
						</div>
						<?php if ( $can_manage ) : ?>
							<button type="button" 
							        class="nds-hr-btn nds-hr-btn-primary" 
							        onclick="openCustomFieldModal('create')"
							        style="background: #0D9488; color: #FFF; border: none; padding: 9px 18px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
								<span class="dashicons dashicons-plus-alt2" style="font-size: 16px; width: 16px; height: 16px;"></span>
								<?php esc_html_e( '+ Add Custom Field', 'nds-hr' ); ?>
							</button>
						<?php endif; ?>
					</div>

					<!-- Custom Fields List -->
					<?php if ( empty( $employee_custom_fields ) ) : ?>
						<!-- Empty State -->
						<div class="nds-hr-empty-state" style="text-align: center; padding: 48px 24px; border: 2px dashed #E2E8F0; border-radius: 12px; background: #F8FAFC;">
							<div style="width: 56px; height: 56px; border-radius: 50%; background: #E0F2FE; color: #0284C7; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
								<span class="dashicons dashicons-list-view" style="font-size: 28px; width: 28px; height: 28px;"></span>
							</div>
							<h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 700; color: #1E293B;">
								<?php esc_html_e( 'No custom employee fields yet.', 'nds-hr' ); ?>
							</h3>
							<p style="margin: 0 auto 20px auto; max-width: 440px; color: #64748B; font-size: 13px; line-height: 1.5;">
								<?php esc_html_e( 'Create custom fields to capture additional employee information without changing the core employee structure.', 'nds-hr' ); ?>
							</p>
							<?php if ( $can_manage ) : ?>
								<button type="button" 
								        class="nds-hr-btn nds-hr-btn-primary" 
								        onclick="openCustomFieldModal('create')"
								        style="background: #0D9488; color: #FFF; border: none; padding: 9px 20px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
									<span class="dashicons dashicons-plus-alt2" style="font-size: 16px; width: 16px; height: 16px;"></span>
									<?php esc_html_e( '+ Add Custom Field', 'nds-hr' ); ?>
								</button>
							<?php endif; ?>
						</div>
					<?php else : ?>
						<!-- Fields Table -->
						<div style="overflow-x: auto; border: 1px solid #E2E8F0; border-radius: 8px;">
							<table class="nds-hr-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
								<thead>
									<tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; color: #475569; font-weight: 600;">
										<th style="padding: 12px 16px;"><?php esc_html_e( 'Field Label', 'nds-hr' ); ?></th>
										<th style="padding: 12px 16px;"><?php esc_html_e( 'Field Key', 'nds-hr' ); ?></th>
										<th style="padding: 12px 16px;"><?php esc_html_e( 'Field Type', 'nds-hr' ); ?></th>
										<th style="padding: 12px 16px; text-align: center;"><?php esc_html_e( 'Required', 'nds-hr' ); ?></th>
										<th style="padding: 12px 16px; text-align: center;"><?php esc_html_e( 'Status', 'nds-hr' ); ?></th>
										<th style="padding: 12px 16px; text-align: center;"><?php esc_html_e( 'Display Order', 'nds-hr' ); ?></th>
										<?php if ( $can_manage ) : ?>
											<th style="padding: 12px 16px; text-align: right;"><?php esc_html_e( 'Actions', 'nds-hr' ); ?></th>
										<?php endif; ?>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $employee_custom_fields as $cf ) : ?>
										<?php
										$type_label = isset( $field_type_labels[ $cf->field_type ] ) ? $field_type_labels[ $cf->field_type ] : ucfirst( $cf->field_type );
										$json_data  = wp_json_encode( $cf );
										?>
										<tr style="border-bottom: 1px solid #F1F5F9; transition: background-color 0.15s ease;" onmouseover="this.style.backgroundColor='#F8FAFC'" onmouseout="this.style.backgroundColor='transparent'">
											<td style="padding: 12px 16px; font-weight: 600; color: #0F172A;">
												<div><?php echo esc_html( $cf->field_label ); ?></div>
												<?php if ( ! empty( $cf->description ) ) : ?>
													<div style="font-size: 11px; font-weight: 400; color: #64748B; margin-top: 2px;">
														<?php echo esc_html( $cf->description ); ?>
													</div>
												<?php endif; ?>
											</td>
											<td style="padding: 12px 16px;">
												<code style="background: #F1F5F9; color: #0F172A; padding: 2px 6px; border-radius: 4px; font-size: 12px; font-family: monospace;">
													<?php echo esc_html( $cf->field_key ); ?>
												</code>
											</td>
											<td style="padding: 12px 16px; color: #334155;">
												<span style="display: inline-flex; align-items: center; gap: 4px;">
													<?php echo esc_html( $type_label ); ?>
												</span>
											</td>
											<td style="padding: 12px 16px; text-align: center;">
												<?php if ( $cf->is_required ) : ?>
													<span style="background: #FEE2E2; color: #991B1B; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 12px;">
														<?php esc_html_e( 'Yes', 'nds-hr' ); ?>
													</span>
												<?php else : ?>
													<span style="background: #F1F5F9; color: #64748B; font-size: 11px; font-weight: 500; padding: 3px 8px; border-radius: 12px;">
														<?php esc_html_e( 'No', 'nds-hr' ); ?>
													</span>
												<?php endif; ?>
											</td>
											<td style="padding: 12px 16px; text-align: center;">
												<?php if ( $cf->is_active ) : ?>
													<span style="background: #DCFCE7; color: #166534; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 12px; display: inline-flex; align-items: center; gap: 4px;">
														<span style="width: 6px; height: 6px; border-radius: 50%; background: #22C55E;"></span>
														<?php esc_html_e( 'Active', 'nds-hr' ); ?>
													</span>
												<?php else : ?>
													<span style="background: #F1F5F9; color: #64748B; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 12px; display: inline-flex; align-items: center; gap: 4px;">
														<span style="width: 6px; height: 6px; border-radius: 50%; background: #94A3B8;"></span>
														<?php esc_html_e( 'Inactive', 'nds-hr' ); ?>
													</span>
												<?php endif; ?>
											</td>
											<td style="padding: 12px 16px; text-align: center; color: #64748B; font-weight: 500;">
												<?php echo esc_html( (string) $cf->sort_order ); ?>
											</td>
											<?php if ( $can_manage ) : ?>
												<td style="padding: 12px 16px; text-align: right;">
													<div style="display: inline-flex; align-items: center; gap: 6px;">
														<!-- Edit Button -->
														<button type="button" 
														        class="nds-hr-action-btn" 
														        onclick='openCustomFieldModal("edit", <?php echo esc_attr( $json_data ); ?>)'
														        title="<?php esc_attr_e( 'Edit Field', 'nds-hr' ); ?>"
														        style="background: transparent; border: 1px solid #CBD5E1; color: #334155; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; cursor: pointer;">
															<?php esc_html_e( 'Edit', 'nds-hr' ); ?>
														</button>

														<!-- Activate / Deactivate Toggle Form -->
														<form method="POST" action="<?php echo esc_url( NDS_HR_Router::url( 'settings', array( 'section' => 'employees' ) ) ); ?>" style="display: inline;">
															<?php wp_nonce_field( NDS_HR_Admin_Settings::NONCE_ACTION, '_nds_hr_settings_nonce' ); ?>
															<input type="hidden" name="nds_hr_settings_action" value="toggle_custom_field_status">
															<input type="hidden" name="field_id" value="<?php echo esc_attr( (string) $cf->id ); ?>">
															<button type="submit" 
															        class="nds-hr-action-btn" 
															        style="background: transparent; border: 1px solid #CBD5E1; color: <?php echo $cf->is_active ? '#D97706' : '#0D9488'; ?>; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; cursor: pointer;">
																<?php echo $cf->is_active ? esc_html__( 'Deactivate', 'nds-hr' ) : esc_html__( 'Activate', 'nds-hr' ); ?>
															</button>
														</form>

														<!-- Delete Button -->
														<button type="button" 
														        class="nds-hr-action-btn" 
														        onclick='openDeleteFieldModal(<?php echo esc_attr( (string) $cf->id ); ?>, "<?php echo esc_js( $cf->field_label ); ?>", "<?php echo esc_js( $cf->field_key ); ?>")'
														        title="<?php esc_attr_e( 'Delete Field', 'nds-hr' ); ?>"
														        style="background: transparent; border: 1px solid #FCA5A5; color: #DC2626; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; cursor: pointer;">
															<?php esc_html_e( 'Delete', 'nds-hr' ); ?>
														</button>
													</div>
												</td>
											<?php endif; ?>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php endif; ?>
				</div>

				<!-- ADD / EDIT CUSTOM FIELD MODAL -->
				<?php if ( $can_manage ) : ?>
					<div id="nds-hr-cf-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
						<div class="nds-hr-modal-dialog" style="background: #FFFFFF; border-radius: 12px; max-width: 580px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border: 1px solid #E2E8F0;">
							<div style="display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; border-bottom: 1px solid #E2E8F0;">
								<h3 id="cf-modal-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #0F172A;">
									<?php esc_html_e( 'Add Custom Field', 'nds-hr' ); ?>
								</h3>
								<button type="button" onclick="closeCustomFieldModal()" style="background: transparent; border: none; font-size: 20px; color: #94A3B8; cursor: pointer; line-height: 1;">&times;</button>
							</div>

							<form id="cf-modal-form" method="POST" action="<?php echo esc_url( NDS_HR_Router::url( 'settings', array( 'section' => 'employees' ) ) ); ?>" style="padding: 24px;">
								<?php wp_nonce_field( NDS_HR_Admin_Settings::NONCE_ACTION, '_nds_hr_settings_nonce' ); ?>
								<input type="hidden" id="cf_action" name="nds_hr_settings_action" value="create_custom_field">
								<input type="hidden" id="cf_id" name="field_id" value="">
								<input type="hidden" id="cf_original_type" value="">

								<div style="display: flex; flex-direction: column; gap: 16px;">
									<!-- Field Label -->
									<div class="nds-hr-form-group">
										<label for="cf_label" style="display: block; font-size: 13px; font-weight: 600; color: #1E293B; margin-bottom: 6px;">
											<?php esc_html_e( 'Field Label', 'nds-hr' ); ?> <span style="color: #DC2626;">*</span>
										</label>
										<input type="text" 
										       id="cf_label" 
										       name="field_label" 
										       required 
										       placeholder="<?php esc_attr_e( 'e.g. Fingerprint Code, Work Shift', 'nds-hr' ); ?>"
										       oninput="onFieldLabelInput(this.value)"
										       style="width: 100%; padding: 9px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
									</div>

									<!-- Field Key -->
									<div class="nds-hr-form-group">
										<label for="cf_key" style="display: block; font-size: 13px; font-weight: 600; color: #1E293B; margin-bottom: 6px;">
											<?php esc_html_e( 'Field Key (Programmatic ID)', 'nds-hr' ); ?> <span style="color: #DC2626;">*</span>
										</label>
										<input type="text" 
										       id="cf_key" 
										       name="field_key" 
										       required 
										       pattern="^[a-z0-9][a-z0-9_]{1,99}$"
										       placeholder="<?php esc_attr_e( 'e.g. fingerprint_code', 'nds-hr' ); ?>"
										       style="width: 100%; padding: 9px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 13px; font-family: monospace; box-sizing: border-box;">
										<p style="margin: 4px 0 0 0; font-size: 11px; color: #64748B;">
											<?php esc_html_e( 'Must be lowercase alphanumeric with underscores (e.g. work_shift).', 'nds-hr' ); ?>
										</p>
									</div>

									<!-- Field Type -->
									<div class="nds-hr-form-group">
										<label for="cf_type" style="display: block; font-size: 13px; font-weight: 600; color: #1E293B; margin-bottom: 6px;">
											<?php esc_html_e( 'Field Type', 'nds-hr' ); ?> <span style="color: #DC2626;">*</span>
										</label>
										<select id="cf_type" 
										        name="field_type" 
										        onchange="onFieldTypeChange(this.value)"
										        style="width: 100%; padding: 9px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 13px; background: #FFF; box-sizing: border-box;">
											<?php foreach ( $supported_field_types as $type ) : ?>
												<option value="<?php echo esc_attr( $type ); ?>">
													<?php echo esc_html( isset( $field_type_labels[ $type ] ) ? $field_type_labels[ $type ] : ucfirst( $type ) ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>

									<!-- Type Change Incompatibility Warning (shown only in Edit mode when type is changed) -->
									<div id="cf-type-warning" style="display: none; background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; padding: 12px 14px; border-radius: 6px; font-size: 12px; line-height: 1.5;">
										<strong><?php esc_html_e( 'Notice:', 'nds-hr' ); ?></strong>
										<?php esc_html_e( 'Changing field type may affect how existing employee values are interpreted. Stored values will not be automatically deleted.', 'nds-hr' ); ?>
									</div>

									<!-- Dynamic Options Configuration (shown for select, multiselect, checkbox, radio) -->
									<div id="cf-options-group" style="display: none; border: 1px solid #E2E8F0; background: #F8FAFC; border-radius: 8px; padding: 14px;">
										<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
											<span style="font-size: 13px; font-weight: 600; color: #1E293B;">
												<?php esc_html_e( 'Field Options', 'nds-hr' ); ?>
											</span>
											<button type="button" 
											        onclick="addOptionRow()" 
											        style="background: #F1F5F9; border: 1px solid #CBD5E1; color: #0F172A; font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 4px; cursor: pointer;">
												<?php esc_html_e( '+ Add Option', 'nds-hr' ); ?>
											</button>
										</div>
										<div id="cf-options-list" style="display: flex; flex-direction: column; gap: 8px;">
											<!-- Options dynamically inserted via JS -->
										</div>
									</div>

									<!-- Description -->
									<div class="nds-hr-form-group">
										<label for="cf_description" style="display: block; font-size: 13px; font-weight: 600; color: #1E293B; margin-bottom: 6px;">
											<?php esc_html_e( 'Description / Help Text', 'nds-hr' ); ?>
										</label>
										<textarea id="cf_description" 
										          name="description" 
										          rows="2" 
										          placeholder="<?php esc_attr_e( 'Optional guidance for HR managers entering employee records...', 'nds-hr' ); ?>"
										          style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 13px; box-sizing: border-box;"></textarea>
									</div>

									<!-- Display Order, Required & Active Toggles -->
									<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; align-items: center;">
										<div class="nds-hr-form-group">
											<label for="cf_sort_order" style="display: block; font-size: 12px; font-weight: 600; color: #1E293B; margin-bottom: 4px;">
												<?php esc_html_e( 'Display Order', 'nds-hr' ); ?>
											</label>
											<input type="number" 
											       id="cf_sort_order" 
											       name="sort_order" 
											       value="10" 
											       min="0" 
											       style="width: 100%; padding: 8px 10px; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
										</div>

										<div style="display: flex; align-items: center; gap: 8px; margin-top: 18px;">
											<input type="checkbox" id="cf_is_required" name="is_required" value="1">
											<label for="cf_is_required" style="font-size: 13px; font-weight: 500; color: #1E293B; cursor: pointer;">
												<?php esc_html_e( 'Required', 'nds-hr' ); ?>
											</label>
										</div>

										<div style="display: flex; align-items: center; gap: 8px; margin-top: 18px;">
											<input type="checkbox" id="cf_is_active" name="is_active" value="1" checked>
											<label for="cf_is_active" style="font-size: 13px; font-weight: 500; color: #1E293B; cursor: pointer;">
												<?php esc_html_e( 'Active', 'nds-hr' ); ?>
											</label>
										</div>
									</div>
								</div>

								<!-- Modal Footer -->
								<div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; padding-top: 16px; border-top: 1px solid #E2E8F0;">
									<button type="button" 
									        onclick="closeCustomFieldModal()" 
									        style="background: #F1F5F9; border: 1px solid #CBD5E1; color: #475569; padding: 9px 18px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer;">
										<?php esc_html_e( 'Cancel', 'nds-hr' ); ?>
									</button>
									<button type="submit" 
									        id="cf-submit-btn" 
									        style="background: #0D9488; border: none; color: #FFF; padding: 9px 20px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer;">
										<?php esc_html_e( 'Save Custom Field', 'nds-hr' ); ?>
									</button>
								</div>
							</form>
						</div>
					</div>

					<!-- DELETE CONFIRMATION MODAL -->
					<div id="nds-hr-cf-delete-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
						<div style="background: #FFFFFF; border-radius: 12px; max-width: 460px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid #E2E8F0;">
							<div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px;">
								<div style="width: 40px; height: 40px; border-radius: 50%; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
									<span class="dashicons dashicons-warning" style="font-size: 24px; width: 24px; height: 24px;"></span>
								</div>
								<div>
									<h3 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 700; color: #0F172A;">
										<?php esc_html_e( 'Delete Custom Field?', 'nds-hr' ); ?>
									</h3>
									<p style="margin: 0; color: #64748B; font-size: 13px; line-height: 1.5;">
										<?php esc_html_e( 'Are you sure you want to permanently delete custom field', 'nds-hr' ); ?> 
										<strong id="delete-cf-name" style="color: #0F172A;"></strong> (<code id="delete-cf-key" style="font-size: 11px;"></code>)?
									</p>
								</div>
							</div>

							<div style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 12px; border-radius: 6px; font-size: 12px; margin-bottom: 20px; line-height: 1.5;">
								<strong><?php esc_html_e( 'Warning:', 'nds-hr' ); ?></strong>
								<?php esc_html_e( 'This action cannot be undone. All values recorded under this custom field across all employees will be permanently deleted.', 'nds-hr' ); ?>
							</div>

							<form method="POST" action="<?php echo esc_url( NDS_HR_Router::url( 'settings', array( 'section' => 'employees' ) ) ); ?>">
								<?php wp_nonce_field( NDS_HR_Admin_Settings::NONCE_ACTION, '_nds_hr_settings_nonce' ); ?>
								<input type="hidden" name="nds_hr_settings_action" value="delete_custom_field">
								<input type="hidden" id="delete-cf-id" name="field_id" value="">

								<div style="display: flex; justify-content: flex-end; gap: 10px;">
									<button type="button" 
									        onclick="closeDeleteFieldModal()" 
									        style="background: #F1F5F9; border: 1px solid #CBD5E1; color: #475569; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer;">
										<?php esc_html_e( 'Cancel', 'nds-hr' ); ?>
									</button>
									<button type="submit" 
									        style="background: #DC2626; border: none; color: #FFF; padding: 8px 18px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer;">
										<?php esc_html_e( 'Permanently Delete Field', 'nds-hr' ); ?>
									</button>
								</div>
							</form>
						</div>
					</div>

					<script>
					var optionTypes = ['select', 'multiselect', 'checkbox', 'radio'];
					var isManualKeyEdit = false;

					function openCustomFieldModal(mode, fieldData) {
						var modal = document.getElementById('nds-hr-cf-modal');
						var title = document.getElementById('cf-modal-title');
						var actionInput = document.getElementById('cf_action');
						var idInput = document.getElementById('cf_id');
						var labelInput = document.getElementById('cf_label');
						var keyInput = document.getElementById('cf_key');
						var typeInput = document.getElementById('cf_type');
						var descInput = document.getElementById('cf_description');
						var reqInput = document.getElementById('cf_is_required');
						var activeInput = document.getElementById('cf_is_active');
						var orderInput = document.getElementById('cf_sort_order');
						var originalTypeInput = document.getElementById('cf_original_type');
						var warningBox = document.getElementById('cf-type-warning');

						warningBox.style.display = 'none';
						document.getElementById('cf-options-list').innerHTML = '';

						if (mode === 'edit' && fieldData) {
							title.textContent = '<?php echo esc_js( __( 'Edit Custom Field', 'nds-hr' ) ); ?>';
							actionInput.value = 'update_custom_field';
							idInput.value = fieldData.id;
							labelInput.value = fieldData.field_label || '';
							keyInput.value = fieldData.field_key || '';
							typeInput.value = fieldData.field_type || 'text';
							descInput.value = fieldData.description || '';
							reqInput.checked = Boolean(fieldData.is_required);
							activeInput.checked = Boolean(fieldData.is_active);
							orderInput.value = fieldData.sort_order || 10;
							originalTypeInput.value = fieldData.field_type || 'text';
							isManualKeyEdit = true;

							// Render options if select/multiselect/checkbox/radio
							if (fieldData.settings && fieldData.settings.options && Array.isArray(fieldData.settings.options)) {
								fieldData.settings.options.forEach(function(opt) {
									addOptionRow(opt.label, opt.value);
								});
							}
						} else {
							title.textContent = '<?php echo esc_js( __( 'Add Custom Field', 'nds-hr' ) ); ?>';
							actionInput.value = 'create_custom_field';
							idInput.value = '';
							labelInput.value = '';
							keyInput.value = '';
							typeInput.value = 'text';
							descInput.value = '';
							reqInput.checked = false;
							activeInput.checked = true;
							orderInput.value = 10;
							originalTypeInput.value = '';
							isManualKeyEdit = false;
						}

						onFieldTypeChange(typeInput.value);
						modal.style.display = 'flex';
					}

					function closeCustomFieldModal() {
						document.getElementById('nds-hr-cf-modal').style.display = 'none';
					}

					function onFieldLabelInput(val) {
						if (!isManualKeyEdit) {
							var slug = val.toLowerCase()
								.replace(/[^a-z0-9\s]/g, '')
								.replace(/\s+/g, '_')
								.substring(0, 50);
							document.getElementById('cf_key').value = slug;
						}
					}

					document.getElementById('cf_key').addEventListener('input', function() {
						isManualKeyEdit = true;
					});

					function onFieldTypeChange(newType) {
						var optionsGroup = document.getElementById('cf-options-group');
						var warningBox = document.getElementById('cf-type-warning');
						var originalType = document.getElementById('cf_original_type').value;

						if (optionTypes.indexOf(newType) !== -1) {
							optionsGroup.style.display = 'block';
							var list = document.getElementById('cf-options-list');
							if (list.children.length === 0) {
								addOptionRow();
							}
						} else {
							optionsGroup.style.display = 'none';
						}

						if (originalType && originalType !== newType) {
							warningBox.style.display = 'block';
						} else {
							warningBox.style.display = 'none';
						}
					}

					function addOptionRow(label, value) {
						label = label || '';
						value = value || '';
						var list = document.getElementById('cf-options-list');
						var row = document.createElement('div');
						row.style.display = 'flex';
						row.style.gap = '8px';
						row.style.alignItems = 'center';

						row.innerHTML = 
							'<input type="text" name="option_labels[]" value="' + escapeHtml(label) + '" placeholder="<?php echo esc_js( __( 'Option label (e.g. Morning Shift)', 'nds-hr' ) ); ?>" style="flex: 1; padding: 6px 10px; border: 1px solid #CBD5E1; border-radius: 4px; font-size: 12px;">' +
							'<input type="text" name="option_values[]" value="' + escapeHtml(value) + '" placeholder="<?php echo esc_js( __( 'Value (e.g. morning_shift)', 'nds-hr' ) ); ?>" style="flex: 1; padding: 6px 10px; border: 1px solid #CBD5E1; border-radius: 4px; font-size: 12px; font-family: monospace;">' +
							'<button type="button" onclick="this.parentElement.remove()" style="background: #FEE2E2; border: 1px solid #FECACA; color: #DC2626; border-radius: 4px; padding: 6px 8px; font-size: 11px; cursor: pointer;" title="<?php echo esc_js( __( 'Remove Option', 'nds-hr' ) ); ?>">&times;</button>';

						list.appendChild(row);
					}

					function escapeHtml(str) {
						return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
					}

					function openDeleteFieldModal(id, label, key) {
						document.getElementById('delete-cf-id').value = id;
						document.getElementById('delete-cf-name').textContent = label;
						document.getElementById('delete-cf-key').textContent = key;
						document.getElementById('nds-hr-cf-delete-modal').style.display = 'flex';
					}

					function closeDeleteFieldModal() {
						document.getElementById('nds-hr-cf-delete-modal').style.display = 'none';
					}
					</script>
				<?php endif; ?>

			<!-- 3. LOCALIZATION SETTINGS -->
			<?php elseif ( 'localization' === $active_section ) : ?>
				<div class="nds-hr-settings-section">
					<div style="border-bottom: 1px solid #E2E8F0; padding-bottom: 16px; margin-bottom: 24px;">
						<h2 style="margin: 0 0 6px 0; font-size: 18px; font-weight: 700; color: #0F172A;">
							<?php esc_html_e( 'Localization Settings', 'nds-hr' ); ?>
						</h2>
						<p style="margin: 0; color: #64748B; font-size: 13px;">
							<?php esc_html_e( 'Configure default application language and regional presentation preferences.', 'nds-hr' ); ?>
						</p>
					</div>

					<form method="POST" action="<?php echo esc_url( NDS_HR_Router::url( 'settings', array( 'section' => 'localization' ) ) ); ?>">
						<?php wp_nonce_field( NDS_HR_Admin_Settings::NONCE_ACTION, '_nds_hr_settings_nonce' ); ?>
						<input type="hidden" name="nds_hr_settings_action" value="save_localization">

						<div style="display: flex; flex-direction: column; gap: 20px; max-width: 640px;">
							<!-- Default Language -->
							<div class="nds-hr-form-group">
								<label for="default_language" style="display: block; font-size: 13px; font-weight: 600; color: #1E293B; margin-bottom: 6px;">
									<?php esc_html_e( 'Default Language', 'nds-hr' ); ?>
								</label>
								<select id="default_language" 
								        name="default_language" 
								        class="nds-hr-input"
								        style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 14px; background-color: #FFF; box-sizing: border-box;"
								        <?php disabled( ! $can_manage ); ?>>
									<option value="en" <?php selected( $localization_settings['default_language'], 'en' ); ?>>
										<?php esc_html_e( 'English (LTR)', 'nds-hr' ); ?>
									</option>
									<option value="ar" <?php selected( $localization_settings['default_language'], 'ar' ); ?>>
										<?php esc_html_e( 'العربية — Arabic (RTL)', 'nds-hr' ); ?>
									</option>
								</select>
								<p style="margin: 6px 0 0 0; font-size: 12px; color: #64748B;">
									<?php esc_html_e( 'This setting represents the application default language for new user sessions and initial system setup.', 'nds-hr' ); ?>
								</p>
							</div>

							<!-- Direction Info Box -->
							<div class="nds-hr-info-card" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 16px;">
								<div style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
									<span class="dashicons dashicons-info" style="color: #0D9488; font-size: 18px; width: 18px; height: 18px;"></span>
									<?php esc_html_e( 'Text Direction', 'nds-hr' ); ?>
								</div>
								<div style="font-size: 13px; color: #64748B;">
									<?php esc_html_e( 'Direction is automatic based on the selected language (LTR for English, RTL for Arabic).', 'nds-hr' ); ?>
								</div>
							</div>

							<!-- Submit Button -->
							<?php if ( $can_manage ) : ?>
								<div style="margin-top: 12px; padding-top: 16px; border-top: 1px solid #F1F5F9; display: flex; justify-content: flex-end;">
									<button type="submit" class="nds-hr-btn nds-hr-btn-primary" style="background: #0D9488; color: #FFF; border: none; padding: 10px 22px; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
										<span class="dashicons dashicons-saved" style="font-size: 16px; width: 16px; height: 16px;"></span>
										<?php esc_html_e( 'Save Changes', 'nds-hr' ); ?>
									</button>
								</div>
							<?php endif; ?>
						</div>
					</form>
				</div>

			<!-- 3. SECURITY SETTINGS (Clean Placeholder) -->
			<?php elseif ( 'security' === $active_section ) : ?>
				<div class="nds-hr-settings-placeholder" style="text-align: center; padding: 48px 24px;">
					<div style="width: 64px; height: 64px; border-radius: 50%; background: #F1F5F9; color: #64748B; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
						<span class="dashicons dashicons-shield" style="font-size: 32px; width: 32px; height: 32px;"></span>
					</div>
					<h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 700; color: #1E293B;">
						<?php esc_html_e( 'Security Settings', 'nds-hr' ); ?>
					</h3>
					<p style="margin: 0 auto; max-width: 480px; color: #64748B; font-size: 14px; line-height: 1.6;">
						<?php esc_html_e( 'Security settings will be available in a future release.', 'nds-hr' ); ?>
					</p>
				</div>

			<!-- 4. FUTURE MODULE PLACEHOLDERS -->
			<?php else : ?>
				<?php
				$module_label = isset( $nav_items[ $active_section ]['label'] ) ? $nav_items[ $active_section ]['label'] : ucfirst( $active_section );
				$module_icon  = isset( $nav_items[ $active_section ]['icon'] ) ? $nav_items[ $active_section ]['icon'] : 'dashicons-admin-generic';
				?>
				<div class="nds-hr-settings-placeholder" style="text-align: center; padding: 48px 24px;">
					<div style="width: 64px; height: 64px; border-radius: 50%; background: #F0FDFA; color: #0D9488; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
						<span class="dashicons <?php echo esc_attr( $module_icon ); ?>" style="font-size: 32px; width: 32px; height: 32px;"></span>
					</div>
					<h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 700; color: #1E293B;">
						<?php printf( esc_html__( '%s Settings', 'nds-hr' ), esc_html( $module_label ) ); ?>
					</h3>
					<div style="display: inline-block; background: #FEF3C7; color: #92400E; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 12px; margin-bottom: 12px;">
						<?php esc_html_e( 'Not Configured Yet', 'nds-hr' ); ?>
					</div>
					<p style="margin: 0 auto; max-width: 480px; color: #64748B; font-size: 14px; line-height: 1.6;">
						<?php printf( esc_html__( 'Configuration parameters and business policies for the %s module will be available in an upcoming release.', 'nds-hr' ), esc_html( $module_label ) ); ?>
					</p>
				</div>
			<?php endif; ?>

		</section>
	</div>
</div>

<style>
@media (max-width: 768px) {
	.nds-hr-settings-grid {
		grid-template-columns: 1fr !important;
	}
	.nds-hr-settings-nav-menu {
		display: grid !important;
		grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)) !important;
	}
}
</style>
