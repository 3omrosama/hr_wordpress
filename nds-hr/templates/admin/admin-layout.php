<?php
/**
 * Standalone HR Administration Portal Layout Shell.
 * Provides the top-level application navigation and styling for canonical /hr/ route.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir     = NDS_HR_I18n::get_direction();
$is_rtl  = NDS_HR_I18n::is_rtl();
$hr_user = NDS_HR_Session::get_authenticated_user();

$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ( get_query_var( 'nds_hr_tab' ) ? sanitize_key( get_query_var( 'nds_hr_tab' ) ) : 'dashboard' );
if ( empty( $current_tab ) || 'admin' === $current_tab ) {
	$current_tab = 'dashboard';
}

$plugin = nds_hr();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="<?php echo esc_attr( $dir ); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e( 'HR Management Portal — NDS HR', 'nds-hr' ); ?></title>
	<?php wp_head(); ?>
	<style>
		body.nds-hr-admin-standalone-body {
			margin: 0;
			padding: 0;
			background-color: #F8FAFC;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			color: #0F172A;
			min-height: 100vh;
			display: flex;
			flex-direction: column;
		}
		.nds-hr-portal-nav-bar {
			background: #0F172A;
			color: #FFF;
			padding: 0 24px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			height: 60px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
			position: sticky;
			top: 0;
			z-index: 1000;
		}
		.nds-hr-portal-nav-left {
			display: flex;
			align-items: center;
			gap: 28px;
		}
		.nds-hr-portal-nav-brand {
			display: flex;
			align-items: center;
			gap: 10px;
			text-decoration: none;
			color: #FFF;
		}
		.nds-hr-portal-nav-brand .nds-badge {
			background: #0D9488;
			color: #FFF;
			font-weight: 800;
			font-size: 13px;
			padding: 4px 8px;
			border-radius: 6px;
			letter-spacing: 0.5px;
		}
		.nds-hr-portal-nav-brand .nds-title {
			font-weight: 700;
			font-size: 16px;
			color: #F8FAFC;
		}
		.nds-hr-portal-nav-links {
			display: flex;
			align-items: center;
			gap: 4px;
		}
		.nds-hr-portal-nav-links a {
			color: #94A3B8;
			text-decoration: none;
			padding: 8px 14px;
			border-radius: 6px;
			font-size: 14px;
			font-weight: 500;
			display: flex;
			align-items: center;
			gap: 6px;
			transition: all 0.15s ease;
		}
		.nds-hr-portal-nav-links a:hover {
			color: #F8FAFC;
			background: rgba(255,255,255,0.06);
		}
		.nds-hr-portal-nav-links a.active {
			color: #FFF;
			background: #0D9488;
			font-weight: 600;
		}
		.nds-hr-portal-nav-right {
			display: flex;
			align-items: center;
			gap: 16px;
		}
		.nds-hr-portal-nav-right a {
			color: #94A3B8;
			text-decoration: none;
			font-size: 13px;
			display: flex;
			align-items: center;
			gap: 5px;
		}
		.nds-hr-portal-nav-right a:hover {
			color: #FFF;
		}
		.nds-hr-user-pill {
			display: flex;
			align-items: center;
			gap: 8px;
			background: rgba(255,255,255,0.08);
			padding: 4px 12px;
			border-radius: 20px;
			font-size: 13px;
			color: #E2E8F0;
		}
		.nds-hr-standalone-container {
			flex: 1;
			max-width: 1400px;
			width: 100%;
			margin: 0 auto;
			padding: 24px;
			box-sizing: border-box;
		}
		.nds-hr-wrap {
			margin: 0 !important;
			padding: 0 !important;
			max-width: 100% !important;
		}
	</style>
</head>
<body class="nds-hr-admin-standalone-body" dir="<?php echo esc_attr( $dir ); ?>">

	<!-- Standalone Portal Header -->
	<header class="nds-hr-portal-nav-bar">
		<div class="nds-hr-portal-nav-left">
			<a href="<?php echo esc_url( NDS_HR_Router::url( 'admin' ) ); ?>" class="nds-hr-portal-nav-brand">
				<span class="nds-badge">NDS</span>
				<span class="nds-title"><?php esc_html_e( 'HR Management', 'nds-hr' ); ?></span>
			</a>

			<nav class="nds-hr-portal-nav-links">
				<a href="<?php echo esc_url( NDS_HR_Router::url( 'admin' ) ); ?>" class="<?php echo 'dashboard' === $current_tab ? 'active' : ''; ?>">
					<span class="dashicons dashicons-dashboard"></span>
					<?php esc_html_e( 'Dashboard', 'nds-hr' ); ?>
				</a>

				<?php if ( NDS_HR_Permissions::can_view_employees() ) : ?>
					<a href="<?php echo esc_url( NDS_HR_Router::url( 'admin', array( 'tab' => 'employees' ) ) ); ?>" class="<?php echo 'employees' === $current_tab ? 'active' : ''; ?>">
						<span class="dashicons dashicons-groups"></span>
						<?php esc_html_e( 'Employees', 'nds-hr' ); ?>
					</a>
				<?php endif; ?>

				<?php if ( NDS_HR_Permissions::can_manage_roles() ) : ?>
					<a href="<?php echo esc_url( NDS_HR_Router::url( 'admin', array( 'tab' => 'roles' ) ) ); ?>" class="<?php echo 'roles' === $current_tab ? 'active' : ''; ?>">
						<span class="dashicons dashicons-shield"></span>
						<?php esc_html_e( 'Roles & Permissions', 'nds-hr' ); ?>
					</a>
				<?php endif; ?>

				<?php if ( NDS_HR_Permissions::can_view_audit_logs() ) : ?>
					<a href="<?php echo esc_url( NDS_HR_Router::url( 'admin', array( 'tab' => 'audit-logs' ) ) ); ?>" class="<?php echo 'audit-logs' === $current_tab ? 'active' : ''; ?>">
						<span class="dashicons dashicons-list-view"></span>
						<?php esc_html_e( 'Audit Logs', 'nds-hr' ); ?>
					</a>
				<?php endif; ?>
			</nav>
		</div>

		<div class="nds-hr-portal-nav-right">
			<!-- Language Switcher -->
			<div class="nds-hr-lang-switcher" style="display: flex; gap: 4px;">
				<a href="<?php echo esc_url( add_query_arg( 'nds_hr_lang', 'en' ) ); ?>" style="padding: 2px 8px; border-radius: 4px; <?php echo ! $is_rtl ? 'color: #FFF; font-weight: bold; background: rgba(255,255,255,0.15);' : 'color: #94A3B8;'; ?>">EN</a>
				<span style="color: #64748B;">|</span>
				<a href="<?php echo esc_url( add_query_arg( 'nds_hr_lang', 'ar' ) ); ?>" style="padding: 2px 8px; border-radius: 4px; <?php echo $is_rtl ? 'color: #FFF; font-weight: bold; background: rgba(255,255,255,0.15);' : 'color: #94A3B8;'; ?>">العربية</a>
			</div>

			<!-- Switch to Employee Portal -->
			<a href="<?php echo esc_url( NDS_HR_Router::url( 'employee' ) ); ?>" target="_blank" style="background: rgba(255,255,255,0.06); padding: 5px 10px; border-radius: 6px;">
				<span class="dashicons dashicons-external"></span>
				<?php esc_html_e( 'Employee Portal', 'nds-hr' ); ?>
			</a>

			<!-- User Info -->
			<?php if ( $hr_user ) : ?>
				<div class="nds-hr-user-pill">
					<span class="dashicons dashicons-admin-users" style="font-size: 15px; width: 15px; height: 15px;"></span>
					<span><?php echo esc_html( $hr_user->display_name ?: $hr_user->username ); ?></span>
					<span style="font-size: 11px; opacity: 0.7; font-weight: 600;">(<?php echo esc_html( $hr_user->role_name ?: __( 'HR Admin', 'nds-hr' ) ); ?>)</span>
				</div>
			<?php endif; ?>

			<!-- Logout Link -->
			<a href="<?php echo esc_url( NDS_HR_Router::url( 'logout' ) ); ?>" style="color: #F87171;">
				<span class="dashicons dashicons-exit"></span>
				<?php esc_html_e( 'Sign Out', 'nds-hr' ); ?>
			</a>
		</div>
	</header>

	<!-- Main Standalone Content Workspace -->
	<main class="nds-hr-standalone-container">
		<?php
		switch ( $current_tab ) {
			case 'employees':
				if ( $plugin->admin && $plugin->admin->employees ) {
					$plugin->admin->employees->render_employees_page();
				}
				break;

			case 'roles':
				if ( $plugin->admin && $plugin->admin->roles_controller ) {
					$plugin->admin->roles_controller->render_roles_page();
				}
				break;

			case 'audit-logs':
				if ( $plugin->admin ) {
					$plugin->admin->render_audit_logs_page();
				}
				break;

			case 'dashboard':
			default:
				if ( $plugin->admin && $plugin->admin->dashboard ) {
					$plugin->admin->dashboard->render_dashboard_page();
				}
				break;
		}
		?>
	</main>

	<?php wp_footer(); ?>
</body>
</html>
