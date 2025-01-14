<?php
/**
 * Banner 1 template.
 *
 * @since 4.18.0
 * @version 4.18.0
 *
 * @package LearnDash\Core
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="ld-banner" id="<?php echo esc_attr( $slug ); ?>">
	<div>
		<a href="<?php echo esc_url_raw( $cta_link ); ?>">
			<img class="ld-banner-image-only" src="<?php echo esc_url_raw( $image_url ); ?>"/>
		</a>
	</div>
	<?php if ( 'yes' === $dismissible ) : ?>
		<a href="#" class="ld-banner-dismiss" data-target="<?php echo esc_attr( $slug ); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
				<path fill-rule="evenodd"
						d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z"
						clip-rule="evenodd"/>
			</svg>
		</a>
	<?php endif ?>
</div>
