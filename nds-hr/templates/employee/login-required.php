<?php
/**
 * Employee Portal Login Required Screen.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir = NDS_HR_I18n::get_direction();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="<?php echo esc_attr( $dir ); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e( 'NDS HR — Employee Portal Login Required', 'nds-hr' ); ?></title>
	<?php wp_head(); ?>
</head>
<body class="nds-hr-portal-body">

<div class="nds-hr-auth-screen">
	<div class="nds-hr-auth-card">
		<div class="nds-hr-portal-logo">NDS HR</div>
		<h1 class="nds-hr-auth-title"><?php esc_html_e( 'Employee Portal Access', 'nds-hr' ); ?></h1>
		<p class="nds-hr-auth-desc"><?php esc_html_e( 'Please sign in with your corporate credentials to view your profile and workforce dashboard.', 'nds-hr' ); ?></p>

		<div class="nds-hr-auth-actions">
			<a href="<?php echo esc_url( NDS_HR_Router::url( 'login', array( 'redirect_to' => NDS_HR_Router::url( 'employee' ) ) ) ); ?>" class="nds-hr-btn nds-hr-btn-primary nds-hr-btn-block">
				<span class="dashicons dashicons-lock"></span>
				<?php esc_html_e( 'Sign In to NDS HR', 'nds-hr' ); ?>
			</a>
			<a href="<?php echo esc_url( NDS_HR_Router::url( 'home' ) ); ?>" class="nds-hr-btn nds-hr-btn-outline nds-hr-btn-block text-center">
				&larr; <?php esc_html_e( 'Return to Website', 'nds-hr' ); ?>
			</a>
		</div>

		<div class="nds-hr-auth-footer">
			<small><?php esc_html_e( 'Protected HR Area &bull; Unauthorized access is audited.', 'nds-hr' ); ?></small>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
