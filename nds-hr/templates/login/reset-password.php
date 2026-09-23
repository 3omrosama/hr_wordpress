<?php
/**
 * Password Reset Template for NDS HR.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir    = NDS_HR_I18n::get_direction();
$is_rtl = NDS_HR_I18n::is_rtl();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="<?php echo esc_attr( $dir ); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e( 'Password Reset — NDS HR', 'nds-hr' ); ?></title>
	
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
			color: #FFFFFF;
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
		.nds-hr-login-success {
			background: #F0FDF4;
			border: 1px solid #BBF7D0;
			color: #166534;
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

		<?php if ( 'new_password' === $step && ! empty( $raw_token ) ) : ?>

			<!-- Step 2: Set New Password -->
			<h1 class="nds-hr-login-title"><?php esc_html_e( 'Set New Password', 'nds-hr' ); ?></h1>
			<p class="nds-hr-login-subtitle"><?php esc_html_e( 'Enter and confirm your new secure password below.', 'nds-hr' ); ?></p>

			<?php if ( ! empty( $error_message ) ) : ?>
				<div class="nds-hr-login-alert">
					<?php echo esc_html( $error_message ); ?>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( NDS_HR_Router::url( 'reset-password' ) ); ?>" novalidate="novalidate">
				<input type="hidden" name="nds_hr_reset_action" value="set_new_password">
				<input type="hidden" name="reset_token" value="<?php echo esc_attr( $raw_token ); ?>">
				<?php wp_nonce_field( 'nds_hr_set_password', 'nds_hr_reset_nonce' ); ?>

				<div class="nds-hr-login-form-group">
					<label for="new_password"><?php esc_html_e( 'New Password *', 'nds-hr' ); ?></label>
					<input
						type="password"
						name="new_password"
						id="new_password"
						class="nds-hr-login-input"
						autocomplete="new-password"
						minlength="8"
						required
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
						minlength="8"
						required
					>
				</div>

				<button type="submit" class="nds-hr-login-btn">
					<?php esc_html_e( 'Update Password & Return to Login', 'nds-hr' ); ?>
				</button>
			</form>

		<?php else : ?>

			<!-- Step 1: Request Reset Link -->
			<h1 class="nds-hr-login-title"><?php esc_html_e( 'Reset Password', 'nds-hr' ); ?></h1>
			<p class="nds-hr-login-subtitle"><?php esc_html_e( 'Enter your NDS HR corporate username or email address. We will send a secure password reset link to your registered email.', 'nds-hr' ); ?></p>

			<?php if ( ! empty( $error_message ) ) : ?>
				<div class="nds-hr-login-alert">
					<?php echo esc_html( $error_message ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $success_msg ) ) : ?>
				<div class="nds-hr-login-success">
					<?php echo esc_html( $success_msg ); ?>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( NDS_HR_Router::url( 'reset-password' ) ); ?>" novalidate="novalidate">
				<input type="hidden" name="nds_hr_reset_action" value="request_reset">
				<?php wp_nonce_field( 'nds_hr_request_reset', 'nds_hr_reset_nonce' ); ?>

				<div class="nds-hr-login-form-group">
					<label for="identifier"><?php esc_html_e( 'Username or Corporate Email', 'nds-hr' ); ?></label>
					<input
						type="text"
						name="identifier"
						id="identifier"
						class="nds-hr-login-input"
						autocomplete="username"
						required
						autofocus
					>
				</div>

				<button type="submit" class="nds-hr-login-btn">
					<?php esc_html_e( 'Send Password Reset Link', 'nds-hr' ); ?>
				</button>
			</form>

		<?php endif; ?>

		<div class="nds-hr-login-footer">
			<div><a href="<?php echo esc_url( NDS_HR_Router::url( 'login' ) ); ?>">&larr; <?php esc_html_e( 'Back to Sign In', 'nds-hr' ); ?></a></div>
			<div><a href="<?php echo esc_url( NDS_HR_Router::url( 'home' ) ); ?>"><?php esc_html_e( 'Return to Website', 'nds-hr' ); ?></a></div>
		</div>
	</div>
</div>

</body>
</html>
