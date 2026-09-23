<?php
/**
 * Admin Employee Profile View Template.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir = NDS_HR_I18n::get_direction();

$formatted_dob            = ! empty( $employee->date_of_birth ) ? NDS_HR_Security::format_date_for_display( $employee->date_of_birth ) : '—';
$formatted_hire_date      = ! empty( $employee->hire_date ) ? NDS_HR_Security::format_date_for_display( $employee->hire_date ) : '—';
$formatted_contract_start = ! empty( $employee->contract_start_date ) ? NDS_HR_Security::format_date_for_display( $employee->contract_start_date ) : $formatted_hire_date;
$formatted_contract_end   = ! empty( $employee->contract_end_date ) ? NDS_HR_Security::format_date_for_display( $employee->contract_end_date ) : __( 'Open-ended', 'nds-hr' );
$contract_duration        = ! empty( $employee->contract_start_date ) ? NDS_HR_Security::calculate_contract_duration( $employee->contract_start_date, $employee->contract_end_date ) : __( 'Indefinite', 'nds-hr' );
?>

<div class="nds-hr-wrap" dir="<?php echo esc_attr( $dir ); ?>">

	<!-- Back Navigation -->
	<div class="nds-hr-breadcrumb" style="margin-bottom: 16px;">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=nds-hr-employees' ) ); ?>" class="nds-hr-btn nds-hr-btn-outline" style="display: inline-flex; align-items: center; gap: 6px;">
			<span class="dashicons dashicons-arrow-left-alt"></span>
			<?php esc_html_e( 'Back to Employee Directory', 'nds-hr' ); ?>
		</a>
	</div>

	<!-- Top Hero Card -->
	<div class="nds-hr-card nds-hr-profile-hero" style="margin-bottom: 20px;">
		<div class="nds-hr-profile-hero-content" style="display: flex; align-items: center; gap: 20px;">
			<div class="nds-hr-profile-avatar" style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; background: #0D9488; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; flex-shrink: 0;">
				<?php if ( ! empty( $employee->profile_photo_url ) ) : ?>
					<img src="<?php echo esc_url( $employee->profile_photo_url ); ?>" alt="<?php echo esc_attr( $employee->full_name ); ?>" style="width: 100%; height: 100%; object-fit: cover;">
				<?php else : ?>
					<span><?php echo esc_html( strtoupper( substr( $employee->first_name, 0, 1 ) . substr( $employee->last_name, 0, 1 ) ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="nds-hr-profile-meta" style="flex: 1;">
				<div class="nds-hr-profile-title-row" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
					<h1 class="nds-hr-profile-name" style="margin: 0; font-size: 24px; font-weight: 700; color: #0F172A;"><?php echo esc_html( $employee->full_name ); ?></h1>
					<span class="nds-hr-code" style="background: #E2E8F0; padding: 2px 8px; border-radius: 4px; font-family: monospace; font-size: 13px;"><?php echo esc_html( $employee->employee_id ); ?></span>
					<?php if ( 'active' === $employee->employment_status ) : ?>
						<span class="nds-hr-status-badge status-active" style="background: #DCFCE7; color: #166534; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;"><?php esc_html_e( 'Active', 'nds-hr' ); ?></span>
					<?php else : ?>
						<span class="nds-hr-status-badge status-inactive" style="background: #F1F5F9; color: #475569; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;"><?php echo esc_html( ucfirst( $employee->employment_status ) ); ?></span>
					<?php endif; ?>
				</div>
				<div class="nds-hr-profile-subtitles" style="margin-top: 6px; font-size: 14px; color: #64748B;">
					<span><strong><?php echo esc_html( $employee->position_title ?: __( 'No Position', 'nds-hr' ) ); ?></strong></span>
					<span class="nds-hr-separator">&bull;</span>
					<span><?php echo esc_html( $employee->department_name ?: __( 'Unassigned Department', 'nds-hr' ) ); ?></span>
					<span class="nds-hr-separator">&bull;</span>
					<span><?php esc_html_e( 'Hired:', 'nds-hr' ); ?> <?php echo esc_html( $formatted_hire_date ); ?></span>
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
	</div>

	<!-- Information Grid -->
	<div class="nds-hr-profile-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">

		<!-- Left Column: Details -->
		<div class="nds-hr-profile-main">

			<!-- 1. Personal Info -->
			<div class="nds-hr-card nds-hr-info-card" style="margin-bottom: 20px;">
				<div class="nds-hr-card-header">
					<h2 class="nds-hr-card-title"><?php esc_html_e( 'Personal Information', 'nds-hr' ); ?></h2>
				</div>
				<dl class="nds-hr-dl">
					<div>
						<dt><?php esc_html_e( 'Full Name', 'nds-hr' ); ?></dt>
						<dd><?php echo esc_html( $employee->full_name ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Corporate Email', 'nds-hr' ); ?></dt>
						<dd><a href="mailto:<?php echo esc_attr( $employee->email ); ?>"><?php echo esc_html( $employee->email ); ?></a></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Mobile Number', 'nds-hr' ); ?></dt>
						<dd>
							<?php if ( ! empty( $employee->mobile ) ) : ?>
								<span style="font-family: monospace; font-weight: 600;"><?php echo esc_html( $employee->mobile ); ?></span>
							<?php else : ?>
								—
							<?php endif; ?>
						</dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Date of Birth', 'nds-hr' ); ?></dt>
						<dd><?php echo esc_html( $formatted_dob ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Gender', 'nds-hr' ); ?></dt>
						<dd><?php echo esc_html( ucfirst( $employee->gender ) ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'National ID / Iqama', 'nds-hr' ); ?></dt>
						<dd><?php echo esc_html( $employee->national_id ?: '—' ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Residential Address', 'nds-hr' ); ?></dt>
						<dd><?php echo esc_html( $employee->address ?: '—' ); ?></dd>
					</div>
				</dl>
			</div>

			<!-- 2. Employment Contract & Duration -->
			<div class="nds-hr-card nds-hr-info-card" style="margin-bottom: 20px;">
				<div class="nds-hr-card-header">
					<h2 class="nds-hr-card-title"><?php esc_html_e( 'Employment Contract', 'nds-hr' ); ?></h2>
				</div>
				<dl class="nds-hr-dl">
					<div>
						<dt><?php esc_html_e( 'Contract Type', 'nds-hr' ); ?></dt>
						<dd>
							<span style="background: #E0F2FE; color: #0369A1; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 12px; text-transform: capitalize;">
								<?php echo esc_html( str_replace( '_', ' ', ! empty( $employee->contract_type ) ? $employee->contract_type : 'permanent' ) ); ?>
							</span>
						</dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Calculated Duration', 'nds-hr' ); ?></dt>
						<dd><strong><?php echo esc_html( $contract_duration ); ?></strong></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Contract Start Date', 'nds-hr' ); ?></dt>
						<dd><?php echo esc_html( $formatted_contract_start ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Contract End Date', 'nds-hr' ); ?></dt>
						<dd><?php echo esc_html( $formatted_contract_end ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Attached Contract Document', 'nds-hr' ); ?></dt>
						<dd>
							<?php if ( ! empty( $employee->contract_document_url ) ) : ?>
								<a href="<?php echo esc_url( $employee->contract_document_url ); ?>" target="_blank" class="nds-hr-btn nds-hr-btn-outline" style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; padding: 4px 10px;">
									<span class="dashicons dashicons-media-document" style="color: #0D9488;"></span>
									<?php echo esc_html( ! empty( $employee->contract_document_name ) ? $employee->contract_document_name : __( 'Download Contract', 'nds-hr' ) ); ?>
								</a>
							<?php else : ?>
								<span class="nds-hr-muted-text"><?php esc_html_e( 'No document attached', 'nds-hr' ); ?></span>
							<?php endif; ?>
						</dd>
					</div>
				</dl>
			</div>

			<!-- 3. Employment & Compensation -->
			<div class="nds-hr-card nds-hr-info-card" style="margin-bottom: 20px;">
				<div class="nds-hr-card-header">
					<h2 class="nds-hr-card-title"><?php esc_html_e( 'Organization & Position', 'nds-hr' ); ?></h2>
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
						<dd><?php echo esc_html( $formatted_hire_date ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Basic Salary', 'nds-hr' ); ?></dt>
						<dd>
							<?php if ( isset( $employee->basic_salary ) && null !== $employee->basic_salary && '' !== $employee->basic_salary && (float) $employee->basic_salary > 0 ) : ?>
								<strong><?php echo esc_html( number_format_i18n( (float) $employee->basic_salary, 2 ) . ' ' . ( ! empty( $employee->salary_currency ) ? $employee->salary_currency : 'EGP' ) ); ?></strong>
							<?php else : ?>
								<span class="nds-hr-muted-text"><?php esc_html_e( 'Not specified', 'nds-hr' ); ?></span>
							<?php endif; ?>
						</dd>
					</div>
				</dl>
			</div>

			<!-- 4. Emergency Contact -->
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

			<!-- NDS HR Account Card -->
			<div class="nds-hr-card nds-hr-info-card" style="margin-bottom: 20px;">
				<div class="nds-hr-card-header">
					<h2 class="nds-hr-card-title"><?php esc_html_e( 'NDS HR Account & Access', 'nds-hr' ); ?></h2>
				</div>
				<?php if ( ! empty( $hr_user ) ) : ?>
					<div class="nds-hr-account-status linked" style="background: #F0FDF4; border: 1px solid #BBF7D0; padding: 12px; border-radius: 6px;">
						<div style="display: flex; align-items: center; gap: 8px;">
							<span class="dashicons dashicons-yes-alt" style="color: #16A34A; font-size: 22px;"></span>
							<div>
								<strong><?php esc_html_e( 'NDS HR Account Active', 'nds-hr' ); ?></strong>
								<p style="margin: 2px 0; font-family: monospace; font-size: 13px; color: #166534;"><?php echo esc_html( $hr_user->username ); ?></p>
								<span style="background: #DCFCE7; color: #166534; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
									<?php echo esc_html( ! empty( $hr_user->role_name ) ? $hr_user->role_name : ( 'hr_admin' === $hr_user->role_slug ? 'Admin' : 'Employee' ) ); ?>
								</span>
							</div>
						</div>
					</div>
				<?php else : ?>
					<div class="nds-hr-account-status unlinked" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 12px; border-radius: 6px;">
						<div style="display: flex; align-items: center; gap: 8px;">
							<span class="dashicons dashicons-info" style="color: #94A3B8; font-size: 22px;"></span>
							<div>
								<strong><?php esc_html_e( 'No Login Account', 'nds-hr' ); ?></strong>
								<p class="nds-hr-muted-text" style="font-size: 12px; margin: 4px 0 0;"><?php esc_html_e( 'This employee currently does not have an active login account.', 'nds-hr' ); ?></p>
							</div>
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
						<ul class="nds-hr-timeline-list" style="list-style: none; padding: 0; margin: 0;">
							<?php foreach ( $employee_logs as $log ) : ?>
								<li class="nds-hr-timeline-item" style="padding: 8px 0; border-bottom: 1px solid #F1F5F9; font-size: 12px;">
									<div class="nds-hr-timeline-action" style="font-weight: 600; color: #334155;"><?php echo esc_html( ucwords( str_replace( '_', ' ', $log->action ) ) ); ?></div>
									<div class="nds-hr-timeline-date" style="color: #94A3B8; font-size: 11px;"><?php echo esc_html( human_time_diff( strtotime( $log->created_at ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'nds-hr' ) ); ?></div>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p class="nds-hr-muted-text" style="font-size: 12px;"><?php esc_html_e( 'No audit entries recorded yet.', 'nds-hr' ); ?></p>
					<?php endif; ?>
				</div>
			</div>

		</div>

	</div>

</div>
