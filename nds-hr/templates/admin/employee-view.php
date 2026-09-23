<?php
/**
 * Admin Employee Profile View Template.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir     = NDS_HR_I18n::get_direction();
$wp_user = ! empty( $employee->user_id ) ? get_userdata( $employee->user_id ) : null;
?>

<div class="nds-hr-wrap" dir="<?php echo esc_attr( $dir ); ?>">

	<!-- Back Navigation -->
	<div class="nds-hr-breadcrumb">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-employees' ) ); ?>">&larr; <?php esc_html_e( 'Back to Employee Directory', 'nds-hr' ); ?></a>
	</div>

	<!-- Top Hero Card -->
	<div class="nds-hr-card nds-hr-profile-hero">
		<div class="nds-hr-profile-hero-content">
			<div class="nds-hr-profile-avatar">
				<?php if ( ! empty( $employee->profile_photo_url ) ) : ?>
					<img src="<?php echo esc_url( $employee->profile_photo_url ); ?>" alt="<?php echo esc_attr( $employee->full_name ); ?>">
				<?php else : ?>
					<span><?php echo esc_html( strtoupper( substr( $employee->first_name, 0, 1 ) . substr( $employee->last_name, 0, 1 ) ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="nds-hr-profile-meta">
				<div class="nds-hr-profile-title-row">
					<h1 class="nds-hr-profile-name"><?php echo esc_html( $employee->full_name ); ?></h1>
					<span class="nds-hr-code"><?php echo esc_html( $employee->employee_id ); ?></span>
					<?php if ( 'active' === $employee->employment_status ) : ?>
						<span class="nds-hr-status-badge status-active"><?php esc_html_e( 'Active', 'nds-hr' ); ?></span>
					<?php else : ?>
						<span class="nds-hr-status-badge status-inactive"><?php echo esc_html( ucfirst( $employee->employment_status ) ); ?></span>
					<?php endif; ?>
				</div>
				<div class="nds-hr-profile-subtitles">
					<span><strong><?php echo esc_html( $employee->position_title ?: __( 'No Position', 'nds-hr' ) ); ?></strong></span>
					<span class="nds-hr-separator">&bull;</span>
					<span><?php echo esc_html( $employee->department_name ?: __( 'Unassigned Department', 'nds-hr' ) ); ?></span>
					<span class="nds-hr-separator">&bull;</span>
					<span><?php esc_html_e( 'Hired:', 'nds-hr' ); ?> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $employee->hire_date ) ) ); ?></span>
				</div>
			</div>
		</div>

		<div class="nds-hr-profile-hero-actions">
			<?php if ( NDS_HR_Permissions::can_manage_employees() ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-employees&action=edit&id=' . $employee->id ) ); ?>" class="nds-hr-btn nds-hr-btn-primary">
					<span class="dashicons dashicons-edit"></span>
					<?php esc_html_e( 'Edit Profile', 'nds-hr' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<!-- Information Grid -->
	<div class="nds-hr-profile-grid">

		<!-- Left Column: Details -->
		<div class="nds-hr-profile-main">

			<!-- Personal Info -->
			<div class="nds-hr-card nds-hr-info-card">
				<div class="nds-hr-card-header">
					<h2 class="nds-hr-card-title"><?php esc_html_e( 'Personal Information', 'nds-hr' ); ?></h2>
				</div>
				<dl class="nds-hr-dl">
					<div>
						<dt><?php esc_html_e( 'Full Name', 'nds-hr' ); ?></dt>
						<dd><?php echo esc_html( $employee->full_name ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Work Email', 'nds-hr' ); ?></dt>
						<dd><a href="mailto:<?php echo esc_attr( $employee->email ); ?>"><?php echo esc_html( $employee->email ); ?></a></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Phone', 'nds-hr' ); ?></dt>
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
						<dt><?php esc_html_e( 'Address', 'nds-hr' ); ?></dt>
						<dd><?php echo esc_html( $employee->address ?: '—' ); ?></dd>
					</div>
				</dl>
			</div>

			<!-- Employment & Compensation -->
			<div class="nds-hr-card nds-hr-info-card">
				<div class="nds-hr-card-header">
					<h2 class="nds-hr-card-title"><?php esc_html_e( 'Employment Details', 'nds-hr' ); ?></h2>
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
						<dt><?php esc_html_e( 'Position', 'nds-hr' ); ?></dt>
						<dd><?php echo esc_html( $employee->position_title ?: __( 'None', 'nds-hr' ) ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Hire Date', 'nds-hr' ); ?></dt>
						<dd><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $employee->hire_date ) ) ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Employment Status', 'nds-hr' ); ?></dt>
						<dd><?php echo esc_html( ucfirst( $employee->employment_status ) ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Basic Salary Placeholder', 'nds-hr' ); ?></dt>
						<dd><?php echo esc_html( number_format_i18n( (float) $employee->basic_salary, 2 ) ); ?></dd>
					</div>
				</dl>
			</div>

			<!-- Emergency Contact -->
			<div class="nds-hr-card nds-hr-info-card">
				<div class="nds-hr-card-header">
					<h2 class="nds-hr-card-title"><?php esc_html_e( 'Emergency Contact', 'nds-hr' ); ?></h2>
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

		<!-- Right Column: Account & Audit -->
		<div class="nds-hr-profile-sidebar">

			<!-- WordPress Account Card -->
			<div class="nds-hr-card nds-hr-info-card">
				<div class="nds-hr-card-header">
					<h2 class="nds-hr-card-title"><?php esc_html_e( 'WordPress Account', 'nds-hr' ); ?></h2>
				</div>
				<?php if ( $wp_user ) : ?>
					<div class="nds-hr-account-status linked">
						<span class="dashicons dashicons-yes-alt"></span>
						<div>
							<strong><?php esc_html_e( 'Linked Account', 'nds-hr' ); ?></strong>
							<p><?php echo esc_html( $wp_user->user_login ); ?></p>
							<small class="nds-hr-muted-text"><?php echo esc_html( $wp_user->user_email ); ?></small>
						</div>
					</div>
				<?php else : ?>
					<div class="nds-hr-account-status unlinked">
						<span class="dashicons dashicons-info"></span>
						<div>
							<strong><?php esc_html_e( 'No Account Linked', 'nds-hr' ); ?></strong>
							<p class="nds-hr-muted-text"><?php esc_html_e( 'This employee currently does not have a WordPress login.', 'nds-hr' ); ?></p>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<!-- Employee Activity Audit Trail -->
			<div class="nds-hr-card nds-hr-info-card">
				<div class="nds-hr-card-header">
					<h2 class="nds-hr-card-title"><?php esc_html_e( 'Audit History', 'nds-hr' ); ?></h2>
				</div>
				<div class="nds-hr-timeline">
					<?php if ( ! empty( $employee_logs ) ) : ?>
						<?php foreach ( $employee_logs as $log ) : 
							$actor = $log->user_id ? get_userdata( $log->user_id ) : null;
							$actor_name = $actor ? $actor->display_name : __( 'System', 'nds-hr' );
						?>
							<div class="nds-hr-timeline-item">
								<div class="nds-hr-timeline-dot"></div>
								<div class="nds-hr-timeline-content">
									<div class="nds-hr-timeline-action">
										<strong><?php echo esc_html( ucwords( str_replace( '_', ' ', $log->action ) ) ); ?></strong>
									</div>
									<small class="nds-hr-muted-text">
										<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log->created_at ) ) ); ?>
										(<?php echo esc_html( $actor_name ); ?>)
									</small>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<p class="nds-hr-muted-text"><?php esc_html_e( 'No activity logs found for this record.', 'nds-hr' ); ?></p>
					<?php endif; ?>
				</div>
			</div>

		</div>

	</div>

</div>
