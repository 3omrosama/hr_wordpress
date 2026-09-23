<?php
/**
 * Admin Audit Logs Template.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir = NDS_HR_I18n::get_direction();
?>

<div class="nds-hr-wrap" dir="<?php echo esc_attr( $dir ); ?>">

	<header class="nds-hr-header">
		<div class="nds-hr-header-main">
			<div>
				<h1 class="nds-hr-title"><?php esc_html_e( 'HR Audit & Compliance Logs', 'nds-hr' ); ?></h1>
				<p class="nds-hr-subtitle"><?php esc_html_e( 'Immutable audit trail of employee changes, account linkages, and administrative operations.', 'nds-hr' ); ?></p>
			</div>
			<div class="nds-hr-header-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr' ) ); ?>" class="nds-hr-btn nds-hr-btn-outline">
					&larr; <?php esc_html_e( 'Back to Dashboard', 'nds-hr' ); ?>
				</a>
			</div>
		</div>
	</header>

	<!-- Filter Bar -->
	<div class="nds-hr-card nds-hr-filter-toolbar">
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="nds-hr-filter-form">
			<input type="hidden" name="page" value="nds-hr-audit-logs">

			<div class="nds-hr-form-group">
				<select name="log_action" class="nds-hr-select">
					<option value=""><?php esc_html_e( 'All Logged Actions', 'nds-hr' ); ?></option>
					<option value="employee_created" <?php selected( $action_filter, 'employee_created' ); ?>><?php esc_html_e( 'Employee Created', 'nds-hr' ); ?></option>
					<option value="employee_updated" <?php selected( $action_filter, 'employee_updated' ); ?>><?php esc_html_e( 'Employee Updated', 'nds-hr' ); ?></option>
					<option value="employee_deactivated" <?php selected( $action_filter, 'employee_deactivated' ); ?>><?php esc_html_e( 'Employee Deactivated', 'nds-hr' ); ?></option>
					<option value="employee_reactivated" <?php selected( $action_filter, 'employee_reactivated' ); ?>><?php esc_html_e( 'Employee Reactivated', 'nds-hr' ); ?></option>
					<option value="user_account_created" <?php selected( $action_filter, 'user_account_created' ); ?>><?php esc_html_e( 'User Account Created', 'nds-hr' ); ?></option>
					<option value="user_account_linked" <?php selected( $action_filter, 'user_account_linked' ); ?>><?php esc_html_e( 'User Account Linked', 'nds-hr' ); ?></option>
				</select>
			</div>

			<button type="submit" class="nds-hr-btn nds-hr-btn-secondary">
				<?php esc_html_e( 'Filter Logs', 'nds-hr' ); ?>
			</button>

			<?php if ( ! empty( $action_filter ) ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-audit-logs' ) ); ?>" class="nds-hr-btn nds-hr-btn-outline">
					<?php esc_html_e( 'Reset Filter', 'nds-hr' ); ?>
				</a>
			<?php endif; ?>
		</form>
	</div>

	<!-- Logs Table Card -->
	<div class="nds-hr-card">
		<div class="nds-hr-card-header nds-hr-table-meta-header">
			<span class="nds-hr-results-count">
				<?php
				/* translators: %d: total logs count */
				printf( esc_html__( 'Total Records: %d', 'nds-hr' ), esc_html( $total_logs ) );
				?>
			</span>
		</div>

		<div class="nds-hr-table-responsive">
			<table class="nds-hr-table">
				<thead>
					<tr>
						<th>#</th>
						<th><?php esc_html_e( 'Timestamp', 'nds-hr' ); ?></th>
						<th><?php esc_html_e( 'Action', 'nds-hr' ); ?></th>
						<th><?php esc_html_e( 'Entity', 'nds-hr' ); ?></th>
						<th><?php esc_html_e( 'Actor (User)', 'nds-hr' ); ?></th>
						<th><?php esc_html_e( 'IP Address', 'nds-hr' ); ?></th>
						<th><?php esc_html_e( 'Details', 'nds-hr' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $logs ) ) : ?>
						<?php foreach ( $logs as $log ) : 
							$actor = $log->user_id ? get_userdata( $log->user_id ) : null;
							$actor_name = $actor ? $actor->display_name : __( 'System / Automated', 'nds-hr' );
						?>
							<tr>
								<td><?php echo esc_html( $log->id ); ?></td>
								<td>
									<span><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log->created_at ) ) ); ?></span>
								</td>
								<td>
									<span class="nds-hr-tag nds-hr-tag-action">
										<?php echo esc_html( ucwords( str_replace( '_', ' ', $log->action ) ) ); ?>
									</span>
								</td>
								<td>
									<strong><?php echo esc_html( ucfirst( $log->entity_type ) ); ?></strong>
									<span>#<?php echo esc_html( $log->entity_id ); ?></span>
								</td>
								<td>
									<span><?php echo esc_html( $actor_name ); ?></span>
								</td>
								<td>
									<code class="nds-hr-code"><?php echo esc_html( $log->ip_address ?: '127.0.0.1' ); ?></code>
								</td>
								<td>
									<?php if ( ! empty( $log->new_values ) ) : ?>
										<details class="nds-hr-details">
											<summary class="nds-hr-details-toggle"><?php esc_html_e( 'View Payload', 'nds-hr' ); ?></summary>
											<pre class="nds-hr-json"><?php echo esc_html( $log->new_values ); ?></pre>
										</details>
									<?php else : ?>
										<span class="nds-hr-muted-text">—</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="7" class="nds-hr-empty-state">
								<p><?php esc_html_e( 'No audit records found matching criteria.', 'nds-hr' ); ?></p>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<!-- Pagination -->
		<?php if ( $total_pages > 1 ) : ?>
			<div class="nds-hr-pagination-bar">
				<div class="nds-hr-pagination-links">
					<?php for ( $p = 1; $p <= $total_pages; $p++ ) : ?>
						<a
							href="<?php echo esc_url( add_query_arg( array( 'paged' => $p, 'log_action' => $action_filter ), admin_url( 'admin.php?page=nds-hr-audit-logs' ) ) ); ?>"
							class="nds-hr-page-link <?php echo $p === $page ? 'active' : ''; ?>"
						>
							<?php echo esc_html( $p ); ?>
						</a>
					<?php endfor; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>

</div>
