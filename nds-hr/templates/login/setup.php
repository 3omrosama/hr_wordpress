<?php
/**
 * First HR Administrator Setup Wizard Template.
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
	<title><?php esc_html_e( 'First Administrator Setup — NDS HR', 'nds-hr' ); ?></title>
	
	<link rel="stylesheet" href="<?php echo esc_url( NDS_HR_URL . 'assets/css/employee-portal.css?ver=' . NDS_HR_VERSION ); ?>">
	<?php if ( $is_rtl ) : ?>
		<link rel="stylesheet" href="<?php echo esc_url( NDS_HR_URL . 'assets/css/portal-rtl.css?ver=' . NDS_HR_VERSION ); ?>">
	<?php endif; ?>

	<style>
		.nds-hr-setup-wrap {
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			background: #F8FAFC;
			padding: 32px 16px;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
		}
		.nds-hr-setup-card {
			width: 100%;
			max-width: 520px;
			background: #FFFFFF;
			border-radius: 16px;
			box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
			border: 1px solid #E2E8F0;
			padding: 40px 36px;
		}
		.nds-hr-setup-brand {
			display: flex;
			align-items: center;
			gap: 12px;
			margin-bottom: 24px;
		}
		.nds-hr-setup-badge {
			width: 44px;
			height: 44px;
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
		.nds-hr-setup-brand-text {
			font-size: 22px;
			font-weight: 800;
			color: #0F172A;
			letter-spacing: -0.5px;
		}
		.nds-hr-setup-title {
			font-size: 22px;
			font-weight: 700;
			color: #0F172A;
			margin: 0 0 8px 0;
		}
		.nds-hr-setup-subtitle {
			font-size: 14px;
			color: #64748B;
			margin: 0 0 24px 0;
			line-height: 1.5;
		}
		.nds-hr-setup-notice {
			background: #F0FDFA;
			border: 1px solid #CCFBF1;
			color: #0F766E;
			padding: 14px 16px;
			border-radius: 10px;
			font-size: 13px;
			line-height: 1.5;
			margin-bottom: 24px;
		}
		.nds-hr-setup-alert {
			background: #FEF2F2;
			border: 1px solid #FECACA;
			color: #991B1B;
			padding: 12px 14px;
			border-radius: 8px;
			font-size: 13px;
			margin-bottom: 20px;
		}
		.nds-hr-setup-row {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 16px;
			margin-bottom: 16px;
		}
		.nds-hr-form-group {
			margin-bottom: 18px;
		}
		.nds-hr-form-group label {
			display: block;
			font-size: 13px;
			font-weight: 600;
			color: #334155;
			margin-bottom: 6px;
		}
		.nds-hr-input {
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
		.nds-hr-input:focus {
			border-color: #0D9488;
			box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
		}
		.nds-hr-setup-btn {
			width: 100%;
			padding: 12px 18px;
			background: #0D9488;
			color: #FFFFFF;
			border: none;
			border-radius: 8px;
			font-size: 15px;
			font-weight: 600;
			cursor: pointer;
			transition: background-color 0.15s ease;
			margin-top: 8px;
		}
		.nds-hr-setup-btn:hover {
			background: #0F766E;
		}
		.nds-hr-setup-footer {
			margin-top: 24px;
			padding-top: 20px;
			border-top: 1px solid #E2E8F0;
			text-align: center;
			font-size: 12px;
			color: #94A3B8;
		}
	</style>
</head>
<body>

<div class="nds-hr-setup-wrap">
	<div class="nds-hr-setup-card">
		<!-- Brand Header -->
		<div class="nds-hr-setup-brand">
			<div class="nds-hr-setup-badge">NDS</div>
			<div class="nds-hr-setup-brand-text">NDS HR</div>
		</div>

		<h1 class="nds-hr-setup-title"><?php esc_html_e( 'First Administrator Setup', 'nds-hr' ); ?></h1>
		<p class="nds-hr-setup-subtitle"><?php esc_html_e( 'Welcome to NDS HR. Set up your dedicated primary HR Administrator account. This account is entirely independent of WordPress users.', 'nds-hr' ); ?></p>

		<div class="nds-hr-setup-notice">
			<strong><?php esc_html_e( 'Independent Authentication Engine', 'nds-hr' ); ?>:</strong>
			<?php esc_html_e( 'NDS HR maintains its own isolated database of users, roles, permissions, and sessions. This administrator will manage the enterprise workspace.', 'nds-hr' ); ?>
		</div>

		<?php if ( ! empty( $error_message ) ) : ?>
			<div class="nds-hr-setup-alert">
				<?php echo esc_html( $error_message ); ?>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( NDS_HR_Router::url( 'setup' ) ); ?>" novalidate="novalidate">
			<input type="hidden" name="nds_hr_setup_action" value="create_first_admin">
			<?php wp_nonce_field( 'nds_hr_setup_admin', 'nds_hr_setup_nonce' ); ?>

			<div class="nds-hr-setup-row">
				<div>
					<label for="first_name"><?php esc_html_e( 'First Name', 'nds-hr' ); ?></label>
					<input
						type="text"
						name="first_name"
						id="first_name"
						class="nds-hr-input"
						value="<?php echo esc_attr( $prefill_data['first_name'] ?? '' ); ?>"
						required
					>
				</div>
				<div>
					<label for="last_name"><?php esc_html_e( 'Last Name', 'nds-hr' ); ?></label>
					<input
						type="text"
						name="last_name"
						id="last_name"
						class="nds-hr-input"
						value="<?php echo esc_attr( $prefill_data['last_name'] ?? '' ); ?>"
						required
					>
				</div>
			</div>

			<div class="nds-hr-form-group">
				<label for="username"><?php esc_html_e( 'HR Administrator Username *', 'nds-hr' ); ?></label>
				<input
					type="text"
					name="username"
					id="username"
					class="nds-hr-input"
					value="<?php echo esc_attr( $prefill_data['username'] ?? 'hradmin' ); ?>"
					autocomplete="username"
					required
				>
			</div>

			<div class="nds-hr-form-group">
				<label for="email"><?php esc_html_e( 'Corporate Email Address *', 'nds-hr' ); ?></label>
				<input
					type="email"
					name="email"
					id="email"
					class="nds-hr-input"
					value="<?php echo esc_attr( $prefill_data['email'] ?? '' ); ?>"
					autocomplete="email"
					required
				>
			</div>

			<div class="nds-hr-setup-row">
				<div>
					<label for="password"><?php esc_html_e( 'Secure Password *', 'nds-hr' ); ?></label>
					<input
						type="password"
						name="password"
						id="password"
						class="nds-hr-input"
						autocomplete="new-password"
						minlength="8"
						required
					>
				</div>
				<div>
					<label for="confirm_password"><?php esc_html_e( 'Confirm Password *', 'nds-hr' ); ?></label>
					<input
						type="password"
						name="confirm_password"
						id="confirm_password"
						class="nds-hr-input"
						autocomplete="new-password"
						minlength="8"
						required
					>
				</div>
			</div>

			<button type="submit" class="nds-hr-setup-btn">
				<?php esc_html_e( 'Create HR Administrator & Initialize System', 'nds-hr' ); ?>
			</button>
		</form>

		<div class="nds-hr-setup-footer">
			<div><?php esc_html_e( 'Secure NDS HR Core &bull; Version 2.0.0 Architecture', 'nds-hr' ); ?></div>
		</div>
	</div>
</div>

</body>
</html>
