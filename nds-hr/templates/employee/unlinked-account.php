<?php
/**
 * Employee Portal Unlinked Account Notice.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hr_user = NDS_HR_Session::get_authenticated_user();
$display_name = $hr_user ? ( $hr_user->display_name ?: $hr_user->username ) : '';
$dir          = NDS_HR_I18n::get_direction();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="<?php echo esc_attr( $dir ); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e( 'NDS HR — No Employee Profile Linked', 'nds-hr' ); ?></title>
	<?php wp_head(); ?>
</head>
<body class="nds-hr-portal-body">

<div class="nds-hr-auth-screen">
	<div class="nds-hr-auth-card">
		<div class="nds-hr-portal-logo">NDS HR</div>
		<div class="nds-hr-notice-icon nds-hr-icon-warning">
			<span class="dashicons dashicons-warning"></span>
		</div>
		<h1 class="nds-hr-auth-title"><?php esc_html_e( 'No Employee Record Linked', 'nds-hr' ); ?></h1>
		<p class="nds-hr-auth-desc">
			<?php
			/* translators: %s: user display name */
			printf( esc_html__( 'Hello %s, your NDS HR account is authenticated, but it has not been linked to an active employee profile yet.', 'nds-hr' ), esc_html( $display_name ) );
			?>
		</p>
		<p class="nds-hr-muted-text">
			<?php esc_html_e( 'Please contact your HR Department or System Administrator to link your account to your Employee record.', 'nds-hr' ); ?>
		</p>

		<div class="nds-hr-auth-actions">
			<a href="<?php echo esc_url( NDS_HR_Router::url( 'logout' ) ); ?>" class="nds-hr-btn nds-hr-btn-outline nds-hr-btn-block text-center">
				<?php esc_html_e( 'Sign Out', 'nds-hr' ); ?>
			</a>
			<a href="<?php echo esc_url( NDS_HR_Router::url( 'home' ) ); ?>" class="nds-hr-btn nds-hr-btn-outline nds-hr-btn-block text-center">
				<?php esc_html_e( 'Return to Home', 'nds-hr' ); ?>
			</a>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
