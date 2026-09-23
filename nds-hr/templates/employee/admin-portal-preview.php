<?php
/**
 * Administrator Employee Portal Preview Selector.
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
	<title><?php esc_html_e( 'NDS HR — Employee Portal (Admin View)', 'nds-hr' ); ?></title>
	<?php wp_head(); ?>
</head>
<body class="nds-hr-portal-body">

<div class="nds-hr-portal-admin-preview-wrap">
	<div class="nds-hr-card nds-hr-admin-preview-card">
		<div class="nds-hr-portal-logo">NDS HR</div>
		<h2><?php esc_html_e( 'Administrator Portal Preview', 'nds-hr' ); ?></h2>
		<p class="nds-hr-muted-text">
			<?php esc_html_e( 'You are logged in as an HR Administrator. Your user account does not currently have a linked employee record, but you can preview the portal as any employee below.', 'nds-hr' ); ?>
		</p>

		<?php if ( ! empty( $all_employees ) ) : ?>
			<div class="nds-hr-preview-employees-list">
				<h3><?php esc_html_e( 'Select an Employee to Preview Portal:', 'nds-hr' ); ?></h3>
				<ul class="nds-hr-preview-list">
					<?php foreach ( $all_employees as $emp ) : ?>
						<li>
							<a href="<?php echo esc_url( add_query_arg( 'preview_emp_id', $emp->id ) ); ?>" class="nds-hr-preview-item">
								<strong><?php echo esc_html( $emp->full_name ); ?></strong>
								<span class="nds-hr-code"><?php echo esc_html( $emp->employee_id ); ?></span>
								<span class="nds-hr-muted-text"><?php echo esc_html( $emp->department_name ?: __( 'Unassigned', 'nds-hr' ) ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'No employee records exist yet.', 'nds-hr' ); ?></p>
		<?php endif; ?>

		<div class="nds-hr-admin-preview-actions">
			<a href="<?php echo esc_url( NDS_HR_Router::url( 'admin' ) ); ?>" class="nds-hr-btn nds-hr-btn-primary">
				<?php esc_html_e( 'Go to HR Admin Dashboard', 'nds-hr' ); ?>
			</a>
			<a href="<?php echo esc_url( NDS_HR_Router::url( 'admin', array( 'tab' => 'employees', 'action' => 'add' ) ) ); ?>" class="nds-hr-btn nds-hr-btn-outline">
				<?php esc_html_e( 'Create an Employee Record', 'nds-hr' ); ?>
			</a>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
