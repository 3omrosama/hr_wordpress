<?php
/**
 * Canonical Unified Login Template.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir    = NDS_HR_I18n::get_direction();
$is_rtl = NDS_HR_I18n::is_rtl();

$login_error        = isset( $_GET['login_error'] ) ? sanitize_key( $_GET['login_error'] ) : '';
$prefill_log        = isset( $_GET['log'] ) ? sanitize_user( wp_unslash( $_GET['log'] ) ) : '';
$redirect_to        = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';
$require_pw_change  = isset( $_GET['require_pw_change'] ) && '1' === (string) $_GET['require_pw_change'];
$pw_error           = isset( $_GET['pw_error'] ) ? sanitize_key( $_GET['pw_error'] ) : '';

// Friendly error messages
$error_message = '';
switch ( $login_error ) {
	case 'empty_username':
		$error_message = __( 'Please enter your corporate username or email address.', 'nds-hr' );
		break;
	case 'empty_password':
		$error_message = __( 'Please enter your password.', 'nds-hr' );
		break;
	case 'invalid_username':
	case 'invalid_email':
	case 'incorrect_password':
		$error_message = __( 'Invalid username, email, or password. Please verify and try again.', 'nds-hr' );
		break;
	case 'nonce_failed':
		$error_message = __( 'Session token expired. Please refresh the page and try logging in again.', 'nds-hr' );
		break;
	default:
		if ( ! empty( $login_error ) ) {
			$error_message = __( 'Authentication failed. Please check your credentials.', 'nds-hr' );
		}
		break;
}

switch ( $pw_error ) {
	case 'empty_fields':
		$error_message = __( 'Please enter and confirm your new private password.', 'nds-hr' );
		break;
	case 'password_mismatch':
		$error_message = __( 'New passwords do not match. Please re-enter them.', 'nds-hr' );
		break;
	case 'password_too_short':
		$error_message = __( 'New password must be at least 8 characters long.', 'nds-hr' );
		break;
	case 'nonce_failed':
		$error_message = __( 'Security token expired. Please retry.', 'nds-hr' );
		break;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="<?php echo esc_attr( $dir ); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html( $require_pw_change ? __( 'First Login Password Update — NDS HR', 'nds-hr' ) : __( 'Sign In — NDS HR Management System', 'nds-hr' ) ); ?></title>
	
	<link rel="stylesheet" href="<?php echo esc_url( NDS_HR_URL . 'assets/css/employee-portal.css?ver=' . NDS_HR_VERSION ); ?>">
	<?php if ( $is_rtl ) : ?>
		<link rel="stylesheet" href="<?php echo esc_url( NDS_HR_URL . 'assets/css/portal-rtl.css?ver=' . NDS_HR_VERSION ); ?>">
	<?php endif; ?>

	<style>
		.nds-hr-login-wrap {
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			background: #F8FAFC;
			padding: 24px;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
		}
		.nds-hr-login-card {
			width: 100%;
			max-width: 440px;
			background: #FFFFFF;
			border-radius: 16px;
			box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
			border: 1px solid #E2E8F0;
			padding: 36px 32px;
		}
		.nds-hr-login-brand {
			display: flex;
			align-items: center;
			gap: 12px;
			margin-bottom: 24px;
		}
		.nds-hr-login-badge {
			width: 42px;
			height: 42px;
			border-radius: 10px;
			background: #0D9488;
			color: #0F172A;
			font-weight: 900;
			font-size: 16px;
			display: flex;
			align-items: center;
			justify-content: center;
			letter-spacing: -0.5px;
		}
		.nds-hr-login-brand-text {
			font-size: 20px;
			font-weight: 800;
			color: #0F172A;
			letter-spacing: -0.5px;
		}
		.nds-hr-login-title {
			font-size: 20px;
			font-weight: 700;
			color: #0F172A;
			margin: 0 0 6px 0;
		}
		.nds-hr-login-subtitle {
			font-size: 13px;
			color: #64748B;
			margin: 0 0 24px 0;
			line-height: 1.5;
		}
		.nds-hr-login-alert {
			background: #FEF2F2;
			border: 1px solid #FECACA;
			color: #991B1B;
			padding: 12px 14px;
			border-radius: 8px;
			font-size: 13px;
			margin-bottom: 20px;
			line-height: 1.4;
		}
		.nds-hr-login-info {
			background: #F0FDFA;
			border: 1px solid #CCFBF1;
			color: #0F766E;
			padding: 12px 14px;
			border-radius: 8px;
			font-size: 13px;
			margin-bottom: 20px;
			line-height: 1.4;
		}
		.nds-hr-login-form-group {
			margin-bottom: 18px;
		}
		.nds-hr-login-form-group label {
			display: block;
			font-size: 13px;
			font-weight: 600;
			color: #334155;
			margin-bottom: 6px;
		}
		.nds-hr-login-input {
			width: 100%;
			box-sizing: border-box;
			padding: 10px 14px;
			border: 1px solid #CBD5E1;
			border-radius: 8px;
			font-size: 14px;
			color: #0F172A;
			outline: none;
			transition: all 0.15s ease;
		}
		.nds-hr-login-input:focus {
			border-color: #0D9488;
			box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
		}
		.nds-hr-login-meta {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 22px;
			font-size: 13px;
		}
		.nds-hr-login-remember {
			display: flex;
			align-items: center;
			gap: 6px;
			color: #475569;
			cursor: pointer;
		}
		.nds-hr-login-forgot {
			color: #0D9488;
			text-decoration: none;
			font-weight: 500;
		}
		.nds-hr-login-forgot:hover {
			text-decoration: underline;
		}
		.nds-hr-login-btn {
			width: 100%;
			padding: 12px 16px;
			background: #0D9488;
			color: #FFFFFF;
			border: none;
			border-radius: 8px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			transition: background-color 0.15s ease;
		}
		.nds-hr-login-btn:hover {
			background: #0F766E;
		}
		.nds-hr-login-footer {
			margin-top: 24px;
			padding-top: 20px;
			border-top: 1px solid #E2E8F0;
			text-align: center;
			font-size: 12px;
			color: #94A3B8;
			display: flex;
			flex-direction: column;
			gap: 8px;
		}
		.nds-hr-login-footer a {
			color: #64748B;
			text-decoration: none;
		}
		.nds-hr-login-footer a:hover {
			color: #0D9488;
		}
	</style>
</head>
<body>

<div class="nds-hr-login-wrap">
	<div class="nds-hr-login-card">
		<!-- Brand Header -->
		<div class="nds-hr-login-brand">
			<div class="nds-hr-login-badge">NDS</div>
			<div class="nds-hr-login-brand-text">NDS HR</div>
		</div>

		<?php if ( $require_pw_change ) : ?>

			<!-- First Login Password Change View -->
			<h1 class="nds-hr-login-title"><?php esc_html_e( 'First-Time Password Setup', 'nds-hr' ); ?></h1>
			<p class="nds-hr-login-subtitle"><?php esc_html_e( 'Your organization requires you to replace your temporary password with a personal, secure password before accessing your workspace.', 'nds-hr' ); ?></p>

			<div class="nds-hr-login-info">
				<?php esc_html_e( 'Please choose a strong password with at least 8 characters. Once updated, you will be automatically routed to your assigned workspace.', 'nds-hr' ); ?>
			</div>

			<?php if ( ! empty( $error_message ) ) : ?>
				<div class="nds-hr-login-alert">
					<?php echo esc_html( $error_message ); ?>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( NDS_HR_Router::url( 'login' ) ); ?>" novalidate="novalidate">
				<input type="hidden" name="nds_hr_login_action" value="change_required_password">
				<?php wp_nonce_field( 'nds_hr_password_change', 'nds_hr_pw_change_nonce' ); ?>

				<div class="nds-hr-login-form-group">
					<label for="new_password"><?php esc_html_e( 'New Password *', 'nds-hr' ); ?></label>
					<input
						type="password"
						name="new_password"
						id="new_password"
						class="nds-hr-login-input"
						autocomplete="new-password"
						required
						minlength="8"
						autofocus
					>
				</div>

				<div class="nds-hr-login-form-group">
					<label for="confirm_password"><?php esc_html_e( 'Confirm New Password *', 'nds-hr' ); ?></label>
					<input
						type="password"
						name="confirm_password"
						id="confirm_password"
						class="nds-hr-login-input"
						autocomplete="new-password"
						required
						minlength="8"
					>
				</div>

				<button type="submit" class="nds-hr-login-btn">
					<?php esc_html_e( 'Save Password & Continue', 'nds-hr' ); ?>
				</button>
			</form>

		<?php else : ?>

			<!-- Unified Login Form -->
			<h1 class="nds-hr-login-title"><?php esc_html_e( 'Enterprise Authentication', 'nds-hr' ); ?></h1>
			<p class="nds-hr-login-subtitle"><?php esc_html_e( 'Sign in to access your HR workspace, employee self-service portal, or administration center.', 'nds-hr' ); ?></p>

			<?php if ( ! empty( $error_message ) ) : ?>
				<div class="nds-hr-login-alert">
					<?php echo esc_html( $error_message ); ?>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( NDS_HR_Router::url( 'login' ) ); ?>" novalidate="novalidate">
				<input type="hidden" name="nds_hr_login_action" value="submit_login">
				<?php wp_nonce_field( 'nds_hr_unified_login', 'nds_hr_login_nonce' ); ?>
				<?php if ( ! empty( $redirect_to ) ) : ?>
					<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">
				<?php endif; ?>

				<div class="nds-hr-login-form-group">
					<label for="user_login"><?php esc_html_e( 'Username or Email Address', 'nds-hr' ); ?></label>
					<input
						type="text"
						name="log"
						id="user_login"
						class="nds-hr-login-input"
						value="<?php echo esc_attr( $prefill_log ); ?>"
						autocomplete="username"
						required
						autofocus
					>
				</div>

				<div class="nds-hr-login-form-group">
					<label for="user_pass"><?php esc_html_e( 'Password', 'nds-hr' ); ?></label>
					<input
						type="password"
						name="pwd"
						id="user_pass"
						class="nds-hr-login-input"
						autocomplete="current-password"
						required
					>
				</div>

				<div class="nds-hr-login-meta">
					<label class="nds-hr-login-remember">
						<input type="checkbox" name="rememberme" value="forever">
						<span><?php esc_html_e( 'Remember Me', 'nds-hr' ); ?></span>
					</label>

					<a href="<?php echo esc_url( NDS_HR_Router::url( 'reset-password' ) ); ?>" class="nds-hr-login-forgot">
						<?php esc_html_e( 'Forgot Password?', 'nds-hr' ); ?>
					</a>
				</div>

				<button type="submit" class="nds-hr-login-btn">
					<?php esc_html_e( 'Sign In to NDS HR', 'nds-hr' ); ?>
				</button>
			</form>

		<?php endif; ?>

		<div class="nds-hr-login-footer">
			<div><?php esc_html_e( 'Protected Corporate Area &bull; Role-based capability routing active.', 'nds-hr' ); ?></div>
			<div><a href="<?php echo esc_url( NDS_HR_Router::url( 'home' ) ); ?>">&larr; <?php esc_html_e( 'Return to Website', 'nds-hr' ); ?></a></div>
		</div>
	</div>
</div>

</body>
</html>
