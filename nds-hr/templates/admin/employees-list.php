<?php
/**
 * Admin Employee Directory List Template.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir    = NDS_HR_I18n::get_direction();
$is_rtl = NDS_HR_I18n::is_rtl();

$message = isset( $_GET['message'] ) ? sanitize_key( $_GET['message'] ) : '';
$error   = isset( $_GET['error'] ) ? sanitize_text_field( wp_unslash( $_GET['error'] ) ) : '';
?>

<div class="nds-hr-wrap" dir="<?php echo esc_attr( $dir ); ?>">

	<!-- Top Header -->
	<header class="nds-hr-header">
		<div class="nds-hr-header-main">
			<div>
				<h1 class="nds-hr-title"><?php esc_html_e( 'Employee Directory', 'nds-hr' ); ?></h1>
				<p class="nds-hr-subtitle"><?php esc_html_e( 'Manage workforce profiles, department assignments, and system accounts.', 'nds-hr' ); ?></p>
			</div>
			<div class="nds-hr-header-actions">
				<?php if ( NDS_HR_Permissions::can_manage_employees() ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-employees&action=add' ) ); ?>" class="nds-hr-btn nds-hr-btn-primary">
						<span class="dashicons dashicons-plus"></span>
						<?php esc_html_e( 'Add Employee', 'nds-hr' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<!-- Credentials Display Box on Successful Employee Account Onboarding -->
	<?php if ( ! empty( $created_credentials ) ) : ?>
		<div class="nds-hr-card" style="border: 2px solid #0D9488; background: #F0FDFA; margin-bottom: 24px; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
			<div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
				<div>
					<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
						<span class="dashicons dashicons-yes-alt" style="color: #0D9488; font-size: 24px; width: 24px; height: 24px;"></span>
						<h2 style="font-size: 18px; font-weight: 700; color: #134E4A; margin: 0;">
							<?php esc_html_e( 'Employee Created Successfully', 'nds-hr' ); ?>
						</h2>
					</div>
					<p style="font-size: 13px; color: #475569; margin: 0 0 16px 0;">
						<?php esc_html_e( 'The corporate login account has been initialized. Temporary credentials are shown below for this session only and are not stored in any HR database table.', 'nds-hr' ); ?>
					</p>

					<!-- Credential Box -->
					<div style="background: #FFFFFF; border: 1px solid #CCFBF1; border-radius: 8px; padding: 14px 18px; display: inline-block; min-width: 320px; font-size: 13px; line-height: 1.8;">
						<div>
							<strong style="color: #64748B;"><?php esc_html_e( 'Employee ID:', 'nds-hr' ); ?></strong>
							<span style="font-family: monospace; font-weight: 600; color: #0F172A; margin-left: 8px;" id="nds-hr-cred-empid"><?php echo esc_html( $created_credentials['employee_code'] ); ?></span>
						</div>
						<div>
							<strong style="color: #64748B;"><?php esc_html_e( 'Username:', 'nds-hr' ); ?></strong>
							<span style="font-family: monospace; font-weight: 600; color: #0F172A; margin-left: 8px;" id="nds-hr-cred-username"><?php echo esc_html( $created_credentials['username'] ); ?></span>
						</div>
						<div>
							<strong style="color: #64748B;"><?php esc_html_e( 'Temporary Password:', 'nds-hr' ); ?></strong>
							<span style="font-family: monospace; font-weight: 700; color: #0D9488; background: #F8FAFC; padding: 2px 8px; border-radius: 4px; border: 1px dashed #CBD5E1; margin-left: 8px;" id="nds-hr-cred-password"><?php echo esc_html( $created_credentials['temporary_password'] ); ?></span>
						</div>
						<?php if ( ! empty( $created_credentials['require_password_change'] ) ) : ?>
							<div style="margin-top: 6px; font-size: 12px; color: #B45309; display: flex; align-items: center; gap: 4px;">
								<span class="dashicons dashicons-lock" style="font-size: 14px; width: 14px; height: 14px;"></span>
								<span><?php esc_html_e( 'Password change required on first login at /login/', 'nds-hr' ); ?></span>
							</div>
						<?php endif; ?>
					</div>

					<!-- Copy Button -->
					<div style="margin-top: 14px;">
						<button type="button" class="nds-hr-btn nds-hr-btn-primary js-copy-new-credentials-btn" style="background: #0D9488; color: #FFF; font-weight: 600; font-size: 13px;">
							<span class="dashicons dashicons-clipboard"></span>
							<span class="js-copy-btn-text"><?php esc_html_e( 'Copy Credentials', 'nds-hr' ); ?></span>
						</button>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<!-- Success / Error Notices -->
	<?php if ( 'created' === $message && empty( $created_credentials ) ) : ?>
		<div class="nds-hr-alert nds-hr-alert-success">
			<span class="dashicons dashicons-yes-alt"></span>
			<span><?php esc_html_e( 'Employee successfully added to the system.', 'nds-hr' ); ?></span>
		</div>
	<?php elseif ( 'updated' === $message ) : ?>
		<div class="nds-hr-alert nds-hr-alert-success">
			<span class="dashicons dashicons-yes-alt"></span>
			<span><?php esc_html_e( 'Employee record updated successfully.', 'nds-hr' ); ?></span>
		</div>
	<?php elseif ( 'deactivated' === $message ) : ?>
		<div class="nds-hr-alert nds-hr-alert-warning">
			<span class="dashicons dashicons-info"></span>
			<span><?php esc_html_e( 'Employee has been deactivated.', 'nds-hr' ); ?></span>
		</div>
	<?php elseif ( 'reactivated' === $message ) : ?>
		<div class="nds-hr-alert nds-hr-alert-success">
			<span class="dashicons dashicons-yes-alt"></span>
			<span><?php esc_html_e( 'Employee has been reactivated.', 'nds-hr' ); ?></span>
		</div>
	<?php elseif ( 'deleted' === $message ) : ?>
		<div class="nds-hr-alert nds-hr-alert-info">
			<span class="dashicons dashicons-trash"></span>
			<span><?php esc_html_e( 'Employee record removed.', 'nds-hr' ); ?></span>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $error ) ) : ?>
		<div class="nds-hr-alert nds-hr-alert-danger">
			<span class="dashicons dashicons-warning"></span>
			<span><?php echo esc_html( $error ); ?></span>
		</div>
	<?php endif; ?>

	<!-- Filter & Search Toolbar -->
	<div class="nds-hr-card nds-hr-filter-toolbar">
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="nds-hr-toolbar-form">
			<input type="hidden" name="page" value="nds-hr-employees">

			<!-- Search input -->
			<div class="nds-hr-search-box">
				<span class="dashicons dashicons-search"></span>
				<input
					type="search"
					name="s"
					value="<?php echo esc_attr( $search ); ?>"
					placeholder="<?php esc_attr_e( 'Search by name, ID, email...', 'nds-hr' ); ?>"
					class="nds-hr-input"
				>
			</div>

			<!-- Department filter -->
			<div class="nds-hr-filter-item">
				<select name="department_id" class="nds-hr-select">
					<option value="0"><?php esc_html_e( 'All Departments', 'nds-hr' ); ?></option>
					<?php foreach ( $departments as $dept ) : ?>
						<option value="<?php echo esc_attr( $dept->id ); ?>" <?php selected( $department_id, $dept->id ); ?>>
							<?php echo esc_html( $dept->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- Status filter -->
			<div class="nds-hr-filter-item">
				<select name="status" class="nds-hr-select">
					<option value=""><?php esc_html_e( 'All Statuses', 'nds-hr' ); ?></option>
					<option value="active" <?php selected( $status_filter, 'active' ); ?>><?php esc_html_e( 'Active', 'nds-hr' ); ?></option>
					<option value="inactive" <?php selected( $status_filter, 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'nds-hr' ); ?></option>
					<option value="terminated" <?php selected( $status_filter, 'terminated' ); ?>><?php esc_html_e( 'Terminated', 'nds-hr' ); ?></option>
					<option value="suspended" <?php selected( $status_filter, 'suspended' ); ?>><?php esc_html_e( 'Suspended', 'nds-hr' ); ?></option>
				</select>
			</div>

			<button type="submit" class="nds-hr-btn nds-hr-btn-outline">
				<?php esc_html_e( 'Filter', 'nds-hr' ); ?>
			</button>

			<?php if ( ! empty( $search ) || ! empty( $department_id ) || ! empty( $status_filter ) ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-employees' ) ); ?>" class="nds-hr-btn nds-hr-btn-link">
					<?php esc_html_e( 'Reset Filters', 'nds-hr' ); ?>
				</a>
			<?php endif; ?>
		</form>
	</div>

	<!-- Directory Data Table -->
	<div class="nds-hr-card nds-hr-table-wrap">
		<?php if ( empty( $employees ) ) : ?>
			<div class="nds-hr-empty-state">
				<span class="dashicons dashicons-id-alt nds-hr-empty-icon"></span>
				<h3><?php esc_html_e( 'No employees found', 'nds-hr' ); ?></h3>
				<p class="nds-hr-muted-text"><?php esc_html_e( 'Try refining your search terms or add a new employee profile to start.', 'nds-hr' ); ?></p>
				<?php if ( NDS_HR_Permissions::can_manage_employees() ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-employees&action=add' ) ); ?>" class="nds-hr-btn nds-hr-btn-primary">
						<span class="dashicons dashicons-plus"></span>
						<?php esc_html_e( 'Add First Employee', 'nds-hr' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<table class="nds-hr-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Employee', 'nds-hr' ); ?></th>
						<th><?php esc_html_e( 'Employee ID', 'nds-hr' ); ?></th>
						<th><?php esc_html_e( 'Department', 'nds-hr' ); ?></th>
						<th><?php esc_html_e( 'Position', 'nds-hr' ); ?></th>
						<th><?php esc_html_e( 'Hire Date', 'nds-hr' ); ?></th>
						<th><?php esc_html_e( 'Status', 'nds-hr' ); ?></th>
						<th><?php esc_html_e( 'Account', 'nds-hr' ); ?></th>
						<th class="text-right"><?php esc_html_e( 'Actions', 'nds-hr' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $employees as $emp ) : ?>
						<tr>
							<!-- Employee Name & Email -->
							<td>
								<div class="nds-hr-user-cell">
									<div class="nds-hr-avatar-sm">
										<?php if ( ! empty( $emp->profile_photo_url ) ) : ?>
											<img src="<?php echo esc_url( $emp->profile_photo_url ); ?>" alt="<?php echo esc_attr( $emp->full_name ); ?>">
										<?php else : ?>
											<span><?php echo esc_html( strtoupper( substr( $emp->first_name, 0, 1 ) . substr( $emp->last_name, 0, 1 ) ) ); ?></span>
										<?php endif; ?>
									</div>
									<div>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-employees&action=view&id=' . $emp->id ) ); ?>" class="nds-hr-user-name">
											<?php echo esc_html( $emp->full_name ); ?>
										</a>
										<div class="nds-hr-user-email"><?php echo esc_html( $emp->email ); ?></div>
									</div>
								</div>
							</td>

							<!-- Employee ID -->
							<td>
								<code class="nds-hr-code"><?php echo esc_html( $emp->employee_id ); ?></code>
							</td>

							<!-- Department -->
							<td>
								<?php echo esc_html( $emp->department_name ? $emp->department_name : __( 'Unassigned', 'nds-hr' ) ); ?>
							</td>

							<!-- Position -->
							<td>
								<?php echo esc_html( $emp->position_title ? $emp->position_title : __( '—', 'nds-hr' ) ); ?>
							</td>

							<!-- Hire Date -->
							<td>
								<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $emp->hire_date ) ) ); ?>
							</td>

							<!-- Status Badge -->
							<td>
								<?php if ( 'active' === $emp->employment_status ) : ?>
									<span class="nds-hr-badge nds-hr-badge-success"><?php esc_html_e( 'Active', 'nds-hr' ); ?></span>
								<?php elseif ( 'inactive' === $emp->employment_status ) : ?>
									<span class="nds-hr-badge nds-hr-badge-neutral"><?php esc_html_e( 'Inactive', 'nds-hr' ); ?></span>
								<?php elseif ( 'terminated' === $emp->employment_status ) : ?>
									<span class="nds-hr-badge nds-hr-badge-danger"><?php esc_html_e( 'Terminated', 'nds-hr' ); ?></span>
								<?php else : ?>
									<span class="nds-hr-badge nds-hr-badge-warning"><?php echo esc_html( ucfirst( $emp->employment_status ) ); ?></span>
								<?php endif; ?>
							</td>

							<!-- Linked Account Status -->
							<td>
								<?php if ( ! empty( $emp->user_id ) ) : ?>
									<span class="dashicons dashicons-admin-users nds-hr-icon-active" title="<?php esc_attr_e( 'WordPress Account Connected', 'nds-hr' ); ?>"></span>
									<span class="nds-hr-text-sm"><?php esc_html_e( 'Connected', 'nds-hr' ); ?></span>
								<?php else : ?>
									<span class="dashicons dashicons-admin-users nds-hr-icon-inactive" title="<?php esc_attr_e( 'No WordPress User Account', 'nds-hr' ); ?>"></span>
									<span class="nds-hr-text-sm nds-hr-muted-text"><?php esc_html_e( 'No Account', 'nds-hr' ); ?></span>
								<?php endif; ?>
							</td>

							<!-- Actions Menu -->
							<td class="text-right">
								<div class="nds-hr-row-actions">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-employees&action=view&id=' . $emp->id ) ); ?>" class="nds-hr-btn-icon" title="<?php esc_attr_e( 'View Details', 'nds-hr' ); ?>">
										<span class="dashicons dashicons-visibility"></span>
									</a>

									<?php if ( NDS_HR_Permissions::can_manage_employees() ) : ?>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-employees&action=edit&id=' . $emp->id ) ); ?>" class="nds-hr-btn-icon" title="<?php esc_attr_e( 'Edit Profile', 'nds-hr' ); ?>">
											<span class="dashicons dashicons-edit"></span>
										</a>

										<?php if ( 'active' === $emp->employment_status ) : ?>
											<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=nds-hr-employees&nds_hr_action=deactivate&id=' . $emp->id ), 'nds_hr_status_' . $emp->id ) ); ?>" class="nds-hr-btn-icon text-warning" title="<?php esc_attr_e( 'Deactivate Employee', 'nds-hr' ); ?>">
												<span class="dashicons dashicons-lock"></span>
											</a>
										<?php else : ?>
											<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=nds-hr-employees&nds_hr_action=reactivate&id=' . $emp->id ), 'nds_hr_status_' . $emp->id ) ); ?>" class="nds-hr-btn-icon text-success" title="<?php esc_attr_e( 'Reactivate Employee', 'nds-hr' ); ?>">
												<span class="dashicons dashicons-unlock"></span>
											</a>
										<?php endif; ?>
									<?php endif; ?>

									<?php if ( NDS_HR_Permissions::can_delete_employees() ) : ?>
										<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=nds-hr-employees&nds_hr_action=delete&id=' . $emp->id ), 'nds_hr_delete_' . $emp->id ) ); ?>" class="nds-hr-btn-icon text-danger" title="<?php esc_attr_e( 'Delete Employee', 'nds-hr' ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to permanently delete this employee record?', 'nds-hr' ) ); ?>');">
											<span class="dashicons dashicons-trash"></span>
										</a>
									<?php endif; ?>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<!-- Pagination Footer -->
			<?php if ( $total_pages > 1 ) : ?>
				<div class="nds-hr-pagination">
					<span class="nds-hr-pagination-info">
						<?php echo esc_html( sprintf( __( 'Showing %1$d to %2$d of %3$d employees', 'nds-hr' ), ( ( $page - 1 ) * $per_page ) + 1, min( $page * $per_page, $total_count ), $total_count ) ); ?>
					</span>
					<div class="nds-hr-pagination-links">
						<?php
						echo paginate_links( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'base'      => add_query_arg( 'paged', '%#%' ),
								'format'    => '',
								'prev_text' => '&laquo; ' . __( 'Prev', 'nds-hr' ),
								'next_text' => __( 'Next', 'nds-hr' ) . ' &raquo;',
								'total'     => $total_pages,
								'current'   => $page,
							)
						);
						?>
					</div>
				</div>
			<?php endif; ?>

		<?php endif; ?>
	</div>

</div>
