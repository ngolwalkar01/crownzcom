<?php
/**
 * Registration - Order Totals.
 *
 * @version 4.16.0
 * @since 4.16.0
 *
 * @var Learndash_Coupon_DTO|null       $attached_coupon_dto Coupon data.
 * @var array<string, int|float|string> $course_pricing           Course or group pricing data.
 * @var string                          $price               Original price.
 * @var Product                         $product             Product data.
 * @var int                             $register_id         ID of the course or group.
 * @var string                          $trial_price         Trial price.
 * @var Template                        $this                The Template object.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Models\Product;
use LearnDash\Core\Template\Template;

?>
<div class="ld-registration-order__total">
	<div class="ld-registration-order__total-title">
		<?php esc_html_e( 'Total', 'learndash' ); ?>
	</div>
	<div class="ld-registration-order__total-price" aria-label="<?php esc_html_e( 'Total Price', 'learndash' ); ?>">
		<?php if ( $product->has_trial() ) : ?>
			<?php echo esc_html( $trial_price ); ?>
		<?php else : ?>
			<?php echo esc_html( $price ); ?>
		<?php endif; ?>
	</div>
</div>
