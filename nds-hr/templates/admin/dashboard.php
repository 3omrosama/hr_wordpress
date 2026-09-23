<?php
/**
 * Admin Dashboard View Template.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_rtl = NDS_HR_I18n::is_rtl();
$dir    = NDS_HR_I18n::get_direction();
?>

<div class="nds-hr-wrap" dir="<?php echo esc_attr( $dir ); ?>">

	<!-- Top Header Bar -->
	<header class="nds-hr-header">
		<div class="nds-hr-header-main">
			<div class="nds-hr-brand">
				<div class="nds-hr-logo-badge">NDS</div>
				<div>
					<h1 class="nds-hr-title"><?php esc_html_e( 'NDS HR Management System', 'nds-hr' ); ?></h1>
					<p class="nds-hr-subtitle"><?php esc_html_e( 'Enterprise Foundation & Employee Operations', 'nds-hr' ); ?></p>
				</div>
			</div>
			<div class="nds-hr-header-actions">
				<!-- Language & Direction Toggle -->
				<div class="nds-hr-lang-switcher">
					<a href="<?php echo esc_url( add_query_arg( 'nds_hr_lang', 'en' ) ); ?>" class="nds-hr-lang-btn <?php echo ! $is_rtl ? 'active' : ''; ?>">EN</a>
					<a href="<?php echo esc_url( add_query_arg( 'nds_hr_lang', 'ar' ) ); ?>" class="nds-hr-lang-btn <?php echo $is_rtl ? 'active' : ''; ?>">العربية (AR)</a>
				</div>

				<?php if ( NDS_HR_Permissions::can_manage_employees() ) : ?>
					<a href="<?php echo esc_url( NDS_HR_Router::url( 'admin', array( 'tab' => 'employees', 'action' => 'add' ) ) ); ?>" class="nds-hr-btn nds-hr-btn-primary">
						<span class="dashicons dashicons-plus"></span>
						<?php esc_html_e( 'Add Employee', 'nds-hr' ); ?>
					</a>
				<?php endif; ?>

				<a href="<?php echo esc_url( NDS_HR_Router::url( 'employee' ) ); ?>" target="_blank" class="nds-hr-btn nds-hr-btn-outline">
					<span class="dashicons dashicons-external"></span>
					<?php esc_html_e( 'Employee Portal', 'nds-hr' ); ?>
				</a>
			</div>
		</div>
	</header>

	<!-- Metric Cards Grid -->
	<div class="nds-hr-metrics-grid">
		<!-- Total Employees -->
		<div class="nds-hr-card nds-hr-metric-card">
			<div class="nds-hr-metric-icon nds-hr-icon-primary">
				<span class="dashicons dashicons-groups"></span>
			</div>
			<div class="nds-hr-metric-content">
				<span class="nds-hr-metric-label"><?php esc_html_e( 'Total Workforce', 'nds-hr' ); ?></span>
				<div class="nds-hr-metric-number"><?php echo esc_html( number_format_i18n( $counts['total'] ) ); ?></div>
			</div>
		</div>

		<!-- Active Employees -->
		<div class="nds-hr-card nds-hr-metric-card">
			<div class="nds-hr-metric-icon nds-hr-icon-success">
				<span class="dashicons dashicons-yes-alt"></span>
			</div>
			<div class="nds-hr-metric-content">
				<span class="nds-hr-metric-label"><?php esc_html_e( 'Active Employees', 'nds-hr' ); ?></span>
				<div class="nds-hr-metric-number"><?php echo esc_html( number_format_i18n( $counts['active'] ) ); ?></div>
			</div>
		</div>

		<!-- Inactive / Suspended -->
		<div class="nds-hr-card nds-hr-metric-card">
			<div class="nds-hr-metric-icon nds-hr-icon-warning">
				<span class="dashicons dashicons-dismiss"></span>
			</div>
			<div class="nds-hr-metric-content">
				<span class="nds-hr-metric-label"><?php esc_html_e( 'Inactive / On Leave', 'nds-hr' ); ?></span>
				<div class="nds-hr-metric-number"><?php echo esc_html( number_format_i18n( $counts['inactive'] + $counts['terminated'] ) ); ?></div>
			</div>
		</div>

		<!-- Departments -->
		<div class="nds-hr-card nds-hr-metric-card">
			<div class="nds-hr-metric-icon nds-hr-icon-info">
				<span class="dashicons dashicons-networking"></span>
			</div>
			<div class="nds-hr-metric-content">
				<span class="nds-hr-metric-label"><?php esc_html_e( 'Departments', 'nds-hr' ); ?></span>
				<div class="nds-hr-metric-number"><?php echo esc_html( number_format_i18n( $counts['departments'] ) ); ?></div>
			</div>
		</div>
	</div>

	<!-- Main Grid: Recent Employees & Audit Logs -->
	<div class="nds-hr-dashboard-columns">

		<!-- Left: Recent Employees Directory -->
		<div class="nds-hr-card nds-hr-recent-employees">
			<div class="nds-hr-card-header">
				<div class="nds-hr-card-title-group">
					<h2 class="nds-hr-card-title"><?php esc_html_e( 'Recent Employees', 'nds-hr' ); ?></h2>
					<span class="nds-hr-badge nds-hr-badge-secondary"><?php esc_html_e( 'Phase 1 Active', 'nds-hr' ); ?></span>
				</div>
				<a href="<?php echo esc_url( NDS_HR_Router::url( 'admin', array( 'tab' => 'employees' ) ) ); ?>" class="nds-hr-link">
					<?php esc_html_e( 'View All', 'nds-hr' ); ?> &rarr;
				</a>
			</div>

			<div class="nds-hr-table-responsive">
				<table class="nds-hr-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Employee', 'nds-hr' ); ?></th>
							<th><?php esc_html_e( 'Employee ID', 'nds-hr' ); ?></th>
							<th><?php esc_html_e( 'Department', 'nds-hr' ); ?></th>
							<th><?php esc_html_e( 'Status', 'nds-hr' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'nds-hr' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $recent_employees ) ) : ?>
							<?php foreach ( $recent_employees as $emp ) : ?>
								<tr>
									<td>
										<div class="nds-hr-user-cell">
											<div class="nds-hr-avatar">
												<?php if ( ! empty( $emp->profile_photo_url ) ) : ?>
													<img src="<?php echo esc_url( $emp->profile_photo_url ); ?>" alt="<?php echo esc_attr( $emp->full_name ); ?>">
												<?php else : ?>
													<span><?php echo esc_html( strtoupper( substr( $emp->first_name, 0, 1 ) . substr( $emp->last_name, 0, 1 ) ) ); ?></span>
												<?php endif; ?>
											</div>
											<div class="nds-hr-user-info">
												<strong><?php echo esc_html( $emp->full_name ); ?></strong>
												<small><?php echo esc_html( $emp->email ); ?></small>
											</div>
										</div>
									</td>
									<td>
										<span class="nds-hr-code"><?php echo esc_html( $emp->employee_id ); ?></span>
									</td>
									<td>
										<span><?php echo esc_html( $emp->department_name ?: __( 'Unassigned', 'nds-hr' ) ); ?></span>
									</td>
									<td>
										<?php if ( 'active' === $emp->employment_status ) : ?>
											<span class="nds-hr-status-badge status-active"><?php esc_html_e( 'Active', 'nds-hr' ); ?></span>
										<?php else : ?>
											<span class="nds-hr-status-badge status-inactive"><?php echo esc_html( ucfirst( $emp->employment_status ) ); ?></span>
										<?php endif; ?>
									</td>
									<td>
										<a href="<?php echo esc_url( NDS_HR_Router::url( 'admin', array( 'tab' => 'employees', 'action' => 'view', 'id' => $emp->id ) ) ); ?>" class="nds-hr-action-icon" title="<?php esc_attr_e( 'View Details', 'nds-hr' ); ?>">
											<span class="dashicons dashicons-visibility"></span>
										</a>
										<?php if ( NDS_HR_Permissions::can_manage_employees() ) : ?>
											<a href="<?php echo esc_url( NDS_HR_Router::url( 'admin', array( 'tab' => 'employees', 'action' => 'edit', 'id' => $emp->id ) ) ); ?>" class="nds-hr-action-icon" title="<?php esc_attr_e( 'Edit', 'nds-hr' ); ?>">
												<span class="dashicons dashicons-edit"></span>
											</a>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="5" class="nds-hr-empty-state">
									<p><?php esc_html_e( 'No employees found in the system yet.', 'nds-hr' ); ?></p>
									<a href="<?php echo esc_url( NDS_HR_Router::url( 'admin', array( 'tab' => 'employees', 'action' => 'add' ) ) ); ?>" class="nds-hr-btn nds-hr-btn-primary nds-hr-btn-sm">
										<?php esc_html_e( 'Add Your First Employee', 'nds-hr' ); ?>
									</a>
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Right: Recent Activity & Audit Logs -->
		<div class="nds-hr-card nds-hr-activity-card">
			<div class="nds-hr-card-header">
				<h2 class="nds-hr-card-title"><?php esc_html_e( 'Recent HR Audit Logs', 'nds-hr' ); ?></h2>
				<a href="<?php echo esc_url( NDS_HR_Router::url( 'admin', array( 'tab' => 'audit-logs' ) ) ); ?>" class="nds-hr-link">
					<?php esc_html_e( 'View Full Log', 'nds-hr' ); ?> &rarr;
				</a>
			</div>

			<div class="nds-hr-timeline">
				<?php if ( ! empty( $recent_logs ) ) : ?>
					<?php foreach ( $recent_logs as $log ) : 
						$actor = $log->user_id ? get_userdata( $log->user_id ) : null;
						$actor_name = $actor ? $actor->display_name : __( 'System', 'nds-hr' );
					?>
						<div class="nds-hr-timeline-item">
							<div class="nds-hr-timeline-dot"></div>
							<div class="nds-hr-timeline-content">
								<div class="nds-hr-timeline-action">
									<strong><?php echo esc_html( ucwords( str_replace( '_', ' ', $log->action ) ) ); ?></strong>
									<span class="nds-hr-timeline-time"><?php echo esc_html( human_time_diff( strtotime( $log->created_at ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'nds-hr' ) ); ?></span>
								</div>
								<div class="nds-hr-timeline-meta">
									<span><?php esc_html_e( 'By:', 'nds-hr' ); ?> <?php echo esc_html( $actor_name ); ?></span>
									<span class="nds-hr-separator">&bull;</span>
									<span><?php echo esc_html( ucfirst( $log->entity_type ) . ' #' . $log->entity_id ); ?></span>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="nds-hr-empty-state">
						<p><?php esc_html_e( 'No audit entries recorded yet.', 'nds-hr' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>

	</div>

	<!-- Future Modules Architectural Blueprint (Ready for Phase 2-6) -->
	<div class="nds-hr-card nds-hr-blueprint-card">
		<div class="nds-hr-card-header">
			<div>
				<h2 class="nds-hr-card-title"><?php esc_html_e( 'Extensible Architecture & Next Phases', 'nds-hr' ); ?></h2>
				<p class="nds-hr-card-desc"><?php esc_html_e( 'The foundation database layer and role permissions are pre-architected for seamless integration without schema rewrites.', 'nds-hr' ); ?></p>
			</div>
			<span class="nds-hr-badge nds-hr-badge-info"><?php esc_html_e( 'Schema Version: ', 'nds-hr' ); ?><?php echo esc_html( NDS_HR_DB_VERSION ); ?></span>
		</div>

		<div class="nds-hr-phases-grid">
			<div class="nds-hr-phase-box phase-completed">
				<div class="nds-hr-phase-badge">Phase 1</div>
				<h3><?php esc_html_e( 'Foundation & Employees', 'nds-hr' ); ?></h3>
				<p><?php esc_html_e( 'Custom database tables, RBAC, WP user synchronization, Employee ID generator, and self-service portal.', 'nds-hr' ); ?></p>
				<span class="nds-hr-pill nds-hr-pill-success"><?php esc_html_e( 'Active & Operational', 'nds-hr' ); ?></span>
			</div>

			<div class="nds-hr-phase-box phase-upcoming">
				<div class="nds-hr-phase-badge">Phase 2</div>
				<h3><?php esc_html_e( 'Attendance & Time', 'nds-hr' ); ?></h3>
				<p><?php esc_html_e( 'Daily check-in / check-out, shifts, work hours, overtime tracking, and attendance reports.', 'nds-hr' ); ?></p>
				<span class="nds-hr-pill nds-hr-pill-neutral"><?php esc_html_e( 'Scheduled Next', 'nds-hr' ); ?></span>
			</div>

			<div class="nds-hr-phase-box phase-upcoming">
				<div class="nds-hr-phase-badge">Phase 3</div>
				<h3><?php esc_html_e( 'Leave Management', 'nds-hr' ); ?></h3>
				<p><?php esc_html_e( 'Annual, sick, and maternity leave types, yearly balances, request approval workflow.', 'nds-hr' ); ?></p>
				<span class="nds-hr-pill nds-hr-pill-neutral"><?php esc_html_e( 'Scheduled', 'nds-hr' ); ?></span>
			</div>

			<div class="nds-hr-phase-box phase-upcoming">
				<div class="nds-hr-phase-badge">Phase 4 & 5</div>
				<h3><?php esc_html_e( 'Payroll & Payslips', 'nds-hr' ); ?></h3>
				<p><?php esc_html_e( 'Salary structures, allowances, deductions, payroll cycles, and automated printable payslips.', 'nds-hr' ); ?></p>
				<span class="nds-hr-pill nds-hr-pill-neutral"><?php esc_html_e( 'Scheduled', 'nds-hr' ); ?></span>
			</div>
		</div>
	</div>

</div>
