<?php
/**
 * Employee Portal Personal Profile Tab.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="nds-hr-portal-profile-container">

	<!-- Hero Header Profile Card -->
	<div class="nds-hr-portal-card nds-hr-portal-profile-header">
		<div class="nds-hr-portal-avatar-large">
			<?php if ( ! empty( $employee->profile_photo_url ) ) : ?>
				<img src="<?php echo esc_url( $employee->profile_photo_url ); ?>" alt="<?php echo esc_attr( $employee->full_name ); ?>">
			<?php else : ?>
				<span><?php echo esc_html( strtoupper( substr( $employee->first_name, 0, 1 ) . substr( $employee->last_name, 0, 1 ) ) ); ?></span>
			<?php endif; ?>
		</div>
		<div class="nds-hr-portal-profile-intro">
			<h2><?php echo esc_html( $employee->full_name ); ?></h2>
			<div class="nds-hr-portal-tags">
				<span class="nds-hr-code"><?php echo esc_html( $employee->employee_id ); ?></span>
				<?php if ( 'active' === $employee->employment_status ) : ?>
					<span class="nds-hr-status-badge status-active"><?php esc_html_e( 'Active Employee', 'nds-hr' ); ?></span>
				<?php else : ?>
					<span class="nds-hr-status-badge status-inactive"><?php echo esc_html( ucfirst( $employee->employment_status ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="nds-hr-portal-role-line">
				<span><strong><?php echo esc_html( $employee->position_title ?: __( 'Employee', 'nds-hr' ) ); ?></strong></span>
				<span>&bull;</span>
				<span><?php echo esc_html( $employee->department_name ?: __( 'Unassigned', 'nds-hr' ) ); ?></span>
			</div>
		</div>
	</div>

	<!-- Information Cards Grid -->
	<div class="nds-hr-portal-grid-two">

		<!-- Card 1: Personal Details -->
		<div class="nds-hr-portal-card">
			<div class="nds-hr-card-header">
				<h3 class="nds-hr-card-title"><?php esc_html_e( 'Personal & Contact Information', 'nds-hr' ); ?></h3>
			</div>
			<dl class="nds-hr-dl">
				<div>
					<dt><?php esc_html_e( 'Full Name', 'nds-hr' ); ?></dt>
					<dd><?php echo esc_html( $employee->full_name ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Corporate Email', 'nds-hr' ); ?></dt>
					<dd><?php echo esc_html( $employee->email ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Phone Number', 'nds-hr' ); ?></dt>
					<dd><?php echo esc_html( $employee->phone ?: '—' ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Mobile', 'nds-hr' ); ?></dt>
					<dd><?php echo esc_html( $employee->mobile ?: '—' ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'National ID / Passport', 'nds-hr' ); ?></dt>
					<dd><?php echo esc_html( $employee->national_id ?: '—' ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Date of Birth', 'nds-hr' ); ?></dt>
					<dd><?php echo esc_html( $employee->date_of_birth ? date_i18n( get_option( 'date_format' ), strtotime( $employee->date_of_birth ) ) : '—' ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Gender', 'nds-hr' ); ?></dt>
					<dd><?php echo esc_html( ucfirst( $employee->gender ) ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Residential Address', 'nds-hr' ); ?></dt>
					<dd><?php echo esc_html( $employee->address ?: '—' ); ?></dd>
				</div>
			</dl>
		</div>

		<!-- Card 2: Employment & Emergency Details -->
		<div class="nds-hr-portal-card">
			<div class="nds-hr-card-header">
				<h3 class="nds-hr-card-title"><?php esc_html_e( 'Job & Organization', 'nds-hr' ); ?></h3>
			</div>
			<dl class="nds-hr-dl">
				<div>
					<dt><?php esc_html_e( 'Employee ID', 'nds-hr' ); ?></dt>
					<dd><span class="nds-hr-code"><?php echo esc_html( $employee->employee_id ); ?></span></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Department', 'nds-hr' ); ?></dt>
					<dd><?php echo esc_html( $employee->department_name ?: __( 'Unassigned', 'nds-hr' ) ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Designation / Position', 'nds-hr' ); ?></dt>
					<dd><?php echo esc_html( $employee->position_title ?: __( 'Employee', 'nds-hr' ) ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Hire / Joining Date', 'nds-hr' ); ?></dt>
					<dd><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $employee->hire_date ) ) ); ?></dd>
				</div>
			</dl>

			<div class="nds-hr-card-header nds-hr-mt-4">
				<h3 class="nds-hr-card-title"><?php esc_html_e( 'Emergency Contact', 'nds-hr' ); ?></h3>
			</div>
			<dl class="nds-hr-dl">
				<div>
					<dt><?php esc_html_e( 'Contact Person', 'nds-hr' ); ?></dt>
					<dd><?php echo esc_html( $employee->emergency_contact_name ?: '—' ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Phone Number', 'nds-hr' ); ?></dt>
					<dd><?php echo esc_html( $employee->emergency_contact_phone ?: '—' ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Relationship', 'nds-hr' ); ?></dt>
					<dd><?php echo esc_html( $employee->emergency_contact_relationship ?: '—' ); ?></dd>
				</div>
			</dl>
		</div>

	</div>

</div>
