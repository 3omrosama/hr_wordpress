<?php
/**
 * Frontend Employee Portal Layout Shell.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir    = NDS_HR_I18n::get_direction();
$is_rtl = NDS_HR_I18n::is_rtl();

// Handle preview mode for HR admins
$hr_auth_user = NDS_HR_Session::get_authenticated_user();
if ( $hr_auth_user && NDS_HR_Permissions::can_access_admin( (int) $hr_auth_user->id ) && isset( $_GET['preview_emp_id'] ) ) {
	$previewed = $repo->find_by_id( absint( $_GET['preview_emp_id'] ) );
	if ( $previewed ) {
		$employee = $previewed;
	}
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="<?php echo esc_attr( $dir ); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e( 'Employee Self-Service Portal — NDS HR', 'nds-hr' ); ?></title>
	<?php wp_head(); ?>
</head>
<body class="nds-hr-portal-body" dir="<?php echo esc_attr( $dir ); ?>">

<div class="nds-hr-portal-wrapper">

	<!-- Portal Sidebar -->
	<aside class="nds-hr-portal-sidebar">
		<!-- Brand -->
		<div class="nds-hr-portal-brand">
			<div class="nds-hr-portal-logo-badge">NDS</div>
			<div>
				<div class="nds-hr-portal-brand-title"><?php esc_html_e( 'NDS HR', 'nds-hr' ); ?></div>
				<div class="nds-hr-portal-brand-sub"><?php esc_html_e( 'Employee Portal', 'nds-hr' ); ?></div>
			</div>
		</div>

		<!-- Current Employee Mini Card -->
		<div class="nds-hr-portal-user-card">
			<div class="nds-hr-portal-avatar">
				<?php if ( ! empty( $employee->profile_photo_url ) ) : ?>
					<img src="<?php echo esc_url( $employee->profile_photo_url ); ?>" alt="<?php echo esc_attr( $employee->full_name ); ?>">
				<?php else : ?>
					<span><?php echo esc_html( strtoupper( substr( $employee->first_name, 0, 1 ) . substr( $employee->last_name, 0, 1 ) ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="nds-hr-portal-user-meta">
				<div class="nds-hr-portal-user-name"><?php echo esc_html( $employee->full_name ); ?></div>
				<div class="nds-hr-portal-user-code"><?php echo esc_html( $employee->employee_id ); ?></div>
				<div class="nds-hr-portal-user-role"><?php echo esc_html( $employee->position_title ?: __( 'Employee', 'nds-hr' ) ); ?></div>
			</div>
		</div>

		<!-- Navigation Menu -->
		<nav class="nds-hr-portal-nav">
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'dashboard' ) ); ?>" class="nds-hr-nav-item <?php echo 'dashboard' === $current_tab ? 'active' : ''; ?>">
				<span class="dashicons dashicons-dashboard"></span>
				<span><?php esc_html_e( 'Dashboard', 'nds-hr' ); ?></span>
			</a>

			<a href="<?php echo esc_url( add_query_arg( 'tab', 'profile' ) ); ?>" class="nds-hr-nav-item <?php echo 'profile' === $current_tab ? 'active' : ''; ?>">
				<span class="dashicons dashicons-id"></span>
				<span><?php esc_html_e( 'My Profile', 'nds-hr' ); ?></span>
			</a>

			<!-- Future phase nav items (clearly tagged) -->
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'attendance' ) ); ?>" class="nds-hr-nav-item <?php echo 'attendance' === $current_tab ? 'active' : ''; ?>">
				<span class="dashicons dashicons-clock"></span>
				<span><?php esc_html_e( 'Attendance', 'nds-hr' ); ?></span>
				<span class="nds-hr-badge-phase">P2</span>
			</a>

			<a href="<?php echo esc_url( add_query_arg( 'tab', 'leaves' ) ); ?>" class="nds-hr-nav-item <?php echo 'leaves' === $current_tab ? 'active' : ''; ?>">
				<span class="dashicons dashicons-calendar-alt"></span>
				<span><?php esc_html_e( 'Leaves & Time Off', 'nds-hr' ); ?></span>
				<span class="nds-hr-badge-phase">P3</span>
			</a>

			<a href="<?php echo esc_url( add_query_arg( 'tab', 'payroll' ) ); ?>" class="nds-hr-nav-item <?php echo 'payroll' === $current_tab ? 'active' : ''; ?>">
				<span class="dashicons dashicons-media-document"></span>
				<span><?php esc_html_e( 'Payslips', 'nds-hr' ); ?></span>
				<span class="nds-hr-badge-phase">P4</span>
			</a>
		</nav>

		<!-- Sidebar Footer -->
		<div class="nds-hr-portal-sidebar-footer">
			<!-- Language Switcher -->
			<div class="nds-hr-portal-lang">
				<a href="<?php echo esc_url( add_query_arg( 'nds_hr_lang', 'en' ) ); ?>" class="<?php echo ! $is_rtl ? 'active' : ''; ?>">EN</a>
				<span>|</span>
				<a href="<?php echo esc_url( add_query_arg( 'nds_hr_lang', 'ar' ) ); ?>" class="<?php echo $is_rtl ? 'active' : ''; ?>">العربية</a>
			</div>

			<?php if ( NDS_HR_Permissions::can_access_admin() ) : ?>
				<a href="<?php echo esc_url( NDS_HR_Router::url( 'admin' ) ); ?>" class="nds-hr-portal-admin-link">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'HR Admin View', 'nds-hr' ); ?>
				</a>
			<?php endif; ?>

			<a href="<?php echo esc_url( NDS_HR_Router::url( 'logout' ) ); ?>" class="nds-hr-portal-logout-link">
				<span class="dashicons dashicons-exit"></span>
				<?php esc_html_e( 'Sign Out', 'nds-hr' ); ?>
			</a>
		</div>
	</aside>

	<!-- Main Content Area -->
	<main class="nds-hr-portal-main">

		<!-- Top Header -->
		<header class="nds-hr-portal-topbar">
			<div class="nds-hr-portal-topbar-title">
				<?php if ( 'profile' === $current_tab ) : ?>
					<h1><?php esc_html_e( 'My Profile', 'nds-hr' ); ?></h1>
				<?php elseif ( 'attendance' === $current_tab ) : ?>
					<h1><?php esc_html_e( 'Attendance Management', 'nds-hr' ); ?></h1>
				<?php elseif ( 'leaves' === $current_tab ) : ?>
					<h1><?php esc_html_e( 'Leave Balances & Requests', 'nds-hr' ); ?></h1>
				<?php elseif ( 'payroll' === $current_tab ) : ?>
					<h1><?php esc_html_e( 'My Payslips & Compensation', 'nds-hr' ); ?></h1>
				<?php else : ?>
					<h1><?php esc_html_e( 'Workforce Dashboard', 'nds-hr' ); ?></h1>
				<?php endif; ?>
			</div>

			<div class="nds-hr-portal-topbar-info">
				<div class="nds-hr-today-badge">
					<span class="dashicons dashicons-calendar"></span>
					<span><?php echo esc_html( date_i18n( get_option( 'date_format' ) ) ); ?></span>
				</div>
			</div>
		</header>

		<!-- Content View Container -->
		<div class="nds-hr-portal-content">
			<?php
			if ( 'profile' === $current_tab ) {
				$this->profile->render( $employee );
			} elseif ( in_array( $current_tab, array( 'attendance', 'leaves', 'payroll' ), true ) ) {
				// Clean roadmap notice for future phases
				?>
				<div class="nds-hr-portal-card nds-hr-future-module-card">
					<div class="nds-hr-future-icon">
						<span class="dashicons dashicons-clock"></span>
					</div>
					<h2><?php esc_html_e( 'Module Scheduled for Next Phase', 'nds-hr' ); ?></h2>
					<p>
						<?php
						/* translators: %s: tab name */
						printf( esc_html__( 'The %s module is part of the planned roadmap and will become active in its respective phase. Phase 1 foundation tables and permissions are already provisioned.', 'nds-hr' ), esc_html( ucfirst( $current_tab ) ) );
						?>
					</p>
					<a href="<?php echo esc_url( add_query_arg( 'tab', 'dashboard' ) ); ?>" class="nds-hr-btn nds-hr-btn-primary">
						&larr; <?php esc_html_e( 'Return to Dashboard', 'nds-hr' ); ?>
					</a>
				</div>
				<?php
			} else {
				$this->dashboard->render( $employee );
			}
			?>
		</div>

	</main>

</div>

<?php wp_footer(); ?>
</body>
</html>
