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

			<!-- 2. LOCALIZATION SETTINGS -->
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
