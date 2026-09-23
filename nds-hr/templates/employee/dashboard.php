<?php
/**
 * Employee Self-Service Dashboard Tab.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="nds-hr-portal-grid">

	<!-- Hero Welcome Card -->
	<div class="nds-hr-portal-card nds-hr-portal-welcome-card">
		<div class="nds-hr-welcome-text">
			<h2>
				<?php
				/* translators: %s: employee first name */
				printf( esc_html__( 'Welcome back, %s!', 'nds-hr' ), esc_html( $employee->first_name ) );
				?>
			</h2>
			<p>
				<?php
				/* translators: 1: employee ID, 2: department name */
				printf( esc_html__( 'Employee ID: %1$s &bull; %2$s Department', 'nds-hr' ), esc_html( $employee->employee_id ), esc_html( $employee->department_name ?: __( 'Unassigned', 'nds-hr' ) ) );
				?>
			</p>
		</div>
		<div class="nds-hr-welcome-action">
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'profile' ) ); ?>" class="nds-hr-btn nds-hr-btn-primary">
				<span class="dashicons dashicons-id"></span>
				<?php esc_html_e( 'View Full Profile', 'nds-hr' ); ?>
			</a>
		</div>
	</div>

	<!-- Quick KPI Cards -->
	<div class="nds-hr-portal-kpis">
		<!-- Status -->
		<div class="nds-hr-portal-card nds-hr-kpi-card">
			<span class="nds-hr-kpi-label"><?php esc_html_e( 'Employment Status', 'nds-hr' ); ?></span>
			<div class="nds-hr-kpi-val">
				<?php if ( 'active' === $employee->employment_status ) : ?>
					<span class="nds-hr-status-badge status-active"><?php esc_html_e( 'Active', 'nds-hr' ); ?></span>
				<?php else : ?>
					<span class="nds-hr-status-badge status-inactive"><?php echo esc_html( ucfirst( $employee->employment_status ) ); ?></span>
				<?php endif; ?>
			</div>
		</div>

		<!-- Department -->
		<div class="nds-hr-portal-card nds-hr-kpi-card">
			<span class="nds-hr-kpi-label"><?php esc_html_e( 'Department', 'nds-hr' ); ?></span>
			<div class="nds-hr-kpi-val">
				<strong><?php echo esc_html( $employee->department_name ?: __( 'Unassigned', 'nds-hr' ) ); ?></strong>
			</div>
		</div>

		<!-- Position -->
		<div class="nds-hr-portal-card nds-hr-kpi-card">
			<span class="nds-hr-kpi-label"><?php esc_html_e( 'Position', 'nds-hr' ); ?></span>
			<div class="nds-hr-kpi-val">
				<strong><?php echo esc_html( $employee->position_title ?: __( 'Employee', 'nds-hr' ) ); ?></strong>
			</div>
		</div>

		<!-- Tenure -->
		<div class="nds-hr-portal-card nds-hr-kpi-card">
			<span class="nds-hr-kpi-label"><?php esc_html_e( 'Service Tenure', 'nds-hr' ); ?></span>
			<div class="nds-hr-kpi-val">
				<strong>
					<?php
					if ( $tenure_interval->y > 0 ) {
						/* translators: 1: years, 2: months */
						printf( esc_html__( '%1$d yr, %2$d mo', 'nds-hr' ), esc_html( $tenure_interval->y ), esc_html( $tenure_interval->m ) );
					} elseif ( $tenure_interval->m > 0 ) {
						/* translators: %d: months */
						printf( esc_html__( '%d months', 'nds-hr' ), esc_html( $tenure_interval->m ) );
					} else {
						/* translators: %d: days */
						printf( esc_html__( '%d days', 'nds-hr' ), esc_html( $tenure_interval->d ) );
					}
					?>
				</strong>
			</div>
		</div>
	</div>

	<!-- System Roadmap Preview for Employees -->
	<div class="nds-hr-portal-card nds-hr-portal-features-card">
		<div class="nds-hr-card-header">
			<h3 class="nds-hr-card-title"><?php esc_html_e( 'Self-Service Modules', 'nds-hr' ); ?></h3>
			<span class="nds-hr-badge nds-hr-badge-secondary"><?php esc_html_e( 'Phase 1 Active', 'nds-hr' ); ?></span>
		</div>

		<div class="nds-hr-portal-feature-grid">
			<!-- Profile Active Card -->
			<div class="nds-hr-portal-feature-box feature-active">
				<div class="nds-hr-feature-icon-box">
					<span class="dashicons dashicons-id-alt"></span>
				</div>
				<h4><?php esc_html_e( 'Profile & Information', 'nds-hr' ); ?></h4>
				<p><?php esc_html_e( 'View personal data, contact details, national ID, and emergency contacts.', 'nds-hr' ); ?></p>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'profile' ) ); ?>" class="nds-hr-btn nds-hr-btn-sm nds-hr-btn-primary">
					<?php esc_html_e( 'Access Profile', 'nds-hr' ); ?>
				</a>
			</div>

			<!-- Future modules -->
			<?php foreach ( $future_features as $key => $feat ) : ?>
				<div class="nds-hr-portal-feature-box feature-pending">
					<div class="nds-hr-feature-icon-box">
						<span class="dashicons <?php echo esc_attr( $feat['icon'] ); ?>"></span>
					</div>
					<h4><?php echo esc_html( $feat['title'] ); ?></h4>
					<p><?php echo esc_html( $feat['description'] ); ?></p>
					<span class="nds-hr-pill nds-hr-pill-neutral"><?php echo esc_html( $feat['status'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

</div>
