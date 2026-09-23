<?php
/**
 * Roles & Permissions Management Matrix Template.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir    = NDS_HR_I18n::get_direction();
$is_rtl = NDS_HR_I18n::is_rtl();
?>

<div class="nds-hr-wrap" dir="<?php echo esc_attr( $dir ); ?>">

	<!-- Top Header Bar -->
	<header class="nds-hr-header">
		<div class="nds-hr-header-main">
			<div class="nds-hr-brand">
				<div class="nds-hr-logo-badge">NDS</div>
				<div>
					<h1 class="nds-hr-title"><?php esc_html_e( 'Roles & Permissions Management', 'nds-hr' ); ?></h1>
					<p class="nds-hr-subtitle"><?php esc_html_e( 'Configure granular module capabilities for WordPress Administrator, HR Administrator, HR Manager, HR Officer, and Employees.', 'nds-hr' ); ?></p>
				</div>
			</div>
			<div class="nds-hr-header-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr' ) ); ?>" class="nds-hr-btn nds-hr-btn-outline">
					&larr; <?php esc_html_e( 'Back to Dashboard', 'nds-hr' ); ?>
				</a>
			</div>
		</div>
	</header>

	<!-- Notices -->
	<?php if ( 'saved_success' === $message ) : ?>
		<div class="nds-hr-alert nds-hr-alert-success">
			<span class="dashicons dashicons-yes-alt"></span>
			<span><?php esc_html_e( 'Role capabilities matrix saved and synchronized successfully.', 'nds-hr' ); ?></span>
		</div>
	<?php elseif ( 'reset_success' === $message ) : ?>
		<div class="nds-hr-alert nds-hr-alert-warning">
			<span class="dashicons dashicons-warning"></span>
			<span><?php esc_html_e( 'Roles and permissions have been reset to factory defaults.', 'nds-hr' ); ?></span>
		</div>
	<?php endif; ?>

	<!-- Role Summary Cards -->
	<div class="nds-hr-roles-summary-grid">
		<?php foreach ( $manageable_roles as $role_key => $role_data ) : ?>
			<div class="nds-hr-card nds-hr-role-card">
				<div class="nds-hr-role-card-header">
					<div class="nds-hr-role-badge-icon">
						<span class="dashicons dashicons-shield"></span>
					</div>
					<div>
						<h3 class="nds-hr-role-card-title"><?php echo esc_html( $role_data['name'] ); ?></h3>
						<span class="nds-hr-role-slug"><code><?php echo esc_html( $role_key ); ?></code></span>
					</div>
				</div>
				<p class="nds-hr-role-card-desc"><?php echo esc_html( $role_data['description'] ); ?></p>
				<div class="nds-hr-role-card-footer">
					<span class="nds-hr-role-user-count">
						<strong><?php echo esc_html( $role_counts[ $role_key ] ?? 0 ); ?></strong>
						<?php esc_html_e( 'Assigned Users', 'nds-hr' ); ?>
					</span>
					<?php if ( ! empty( $role_data['is_system'] ) ) : ?>
						<span class="nds-hr-badge nds-hr-badge-active"><?php esc_html_e( 'System Role', 'nds-hr' ); ?></span>
					<?php else : ?>
						<span class="nds-hr-badge nds-hr-badge-pending"><?php esc_html_e( 'HR Managed', 'nds-hr' ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Permission Matrix Form Card -->
	<div class="nds-hr-card">
		<div class="nds-hr-card-header-flex">
			<div>
				<h2 class="nds-hr-card-title"><?php esc_html_e( 'Capability Matrix by Module', 'nds-hr' ); ?></h2>
				<p class="nds-hr-card-subtitle"><?php esc_html_e( 'Toggle checkmarks to grant or revoke specific privileges. Changes take effect immediately upon saving.', 'nds-hr' ); ?></p>
			</div>
			<div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-roles' ) ); ?>" onsubmit="return confirm('<?php esc_attr_e( 'Are you sure you want to reset all roles to default permissions?', 'nds-hr' ); ?>');">
					<?php wp_nonce_field( 'nds_hr_save_roles_permissions', '_nds_hr_nonce' ); ?>
					<input type="hidden" name="nds_hr_roles_action" value="reset_roles">
					<button type="submit" class="nds-hr-btn nds-hr-btn-outline" style="color: #DC2626; border-color: #FECACA;">
						<span class="dashicons dashicons-undo"></span>
						<?php esc_html_e( 'Reset to Defaults', 'nds-hr' ); ?>
					</button>
				</form>
			</div>
		</div>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-roles' ) ); ?>" class="nds-hr-roles-form">
			<?php wp_nonce_field( 'nds_hr_save_roles_permissions', '_nds_hr_nonce' ); ?>
			<input type="hidden" name="nds_hr_roles_action" value="save_roles">

			<div class="nds-hr-table-responsive" style="overflow-x: auto;">
				<table class="nds-hr-table nds-hr-matrix-table">
					<thead>
						<tr>
							<th style="width: 38%; min-width: 280px;"><?php esc_html_e( 'Capability / Privilege', 'nds-hr' ); ?></th>
							<?php foreach ( $manageable_roles as $role_key => $role_data ) : ?>
								<th class="nds-hr-text-center" style="min-width: 140px;">
									<div class="nds-hr-matrix-role-th">
										<span><?php echo esc_html( $role_data['name'] ); ?></span>
										<small><code><?php echo esc_html( $role_key ); ?></code></small>
									</div>
								</th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $groups as $group_key => $group ) : ?>
							<!-- Module Category Header Row -->
							<tr class="nds-hr-matrix-group-header">
								<td colspan="<?php echo esc_attr( count( $manageable_roles ) + 1 ); ?>">
									<div class="nds-hr-matrix-group-title">
										<span class="dashicons dashicons-category"></span>
										<span><?php echo esc_html( $group['label'] ); ?></span>
									</div>
								</td>
							</tr>

							<!-- Capability Rows -->
							<?php foreach ( $group['capabilities'] as $cap_slug => $cap_title ) : ?>
								<tr class="nds-hr-matrix-row">
									<td class="nds-hr-matrix-cap-cell">
										<div class="nds-hr-matrix-cap-name"><?php echo esc_html( $cap_title ); ?></div>
										<code class="nds-hr-matrix-cap-slug"><?php echo esc_html( $cap_slug ); ?></code>
									</td>

									<?php foreach ( $manageable_roles as $role_key => $role_data ) :
										$is_granted = ! empty( $matrix[ $role_key ][ $cap_slug ] );
										$is_locked  = ( 'administrator' === $role_key && in_array( $cap_slug, array( 'nds_hr_access_admin', 'nds_hr_manage_roles', 'nds_hr_manage_settings' ), true ) );
									?>
										<td class="nds-hr-text-center nds-hr-matrix-checkbox-cell">
											<label class="nds-hr-matrix-checkbox-label">
												<input
													type="checkbox"
													name="matrix[<?php echo esc_attr( $role_key ); ?>][<?php echo esc_attr( $cap_slug ); ?>]"
													value="1"
													<?php checked( $is_granted ); ?>
													<?php disabled( $is_locked ); ?>
												>
												<?php if ( $is_locked ) : ?>
													<input type="hidden" name="matrix[<?php echo esc_attr( $role_key ); ?>][<?php echo esc_attr( $cap_slug ); ?>]" value="1">
												<?php endif; ?>
												<span class="nds-hr-checkbox-custom"></span>
											</label>
										</td>
									<?php endforeach; ?>
								</tr>
							<?php endforeach; ?>

						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="nds-hr-form-actions" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end; gap: 12px;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr' ) ); ?>" class="nds-hr-btn nds-hr-btn-outline">
					<?php esc_html_e( 'Cancel', 'nds-hr' ); ?>
				</a>
				<button type="submit" class="nds-hr-btn nds-hr-btn-primary">
					<span class="dashicons dashicons-saved"></span>
					<?php esc_html_e( 'Save Role Capabilities', 'nds-hr' ); ?>
				</button>
			</div>
		</form>
	</div>

</div>
