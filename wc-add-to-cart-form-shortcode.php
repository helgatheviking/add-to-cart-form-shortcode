<?php
/**
 * Plugin Name: Add to Cart Form Shortcode for WooCommerce
 * Plugin URI: https://github.com/helgatheviking/add-to-cart-form-shortcode
 * Description: Add [add_to_cart_form] shortcode that display a single product add to cart form.
 * Version: 2.1.0
 * Author: helgatheviking
 * Author URI: https://kathyisawesome.com
 * Requires at least: 4.8
 * Tested up to: 5.2.0
 * WC requires at least: 3.2.0
 * WC tested up to: 3.7.0
 *
 * @package Add to Cart Form Shortcode
 * @category Core
 * @author helgatheviking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Use new class system for creating a shortcode via WC3.2+
 */
if( ! function_exists( 'kia_add_to_cart_form_shortcode_init' ) ) {
	function kia_add_to_cart_form_shortcode_init() { 

		if( did_action( 'woocommerce_loaded' ) ) {
			if( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.2.0', '>=' ) ) {
				add_shortcode( 'add_to_cart_form', 'kia_add_to_cart_form_shortcode' );
			} else {
				add_shortcode( 'add_to_cart_form', 'kia_add_to_cart_form_shortcode_legacy' ); 
			}
		}
	}
}
add_action( 'after_setup_theme', 'kia_add_to_cart_form_shortcode_init' );

if( ! function_exists( 'kia_add_to_cart_form_shortcode_legacy' ) ) {
	/**
	 * Add [add_to_cart_form] shortcode that display a single product add to cart form
	 * Supports id and sku attributes [add_to_cart_form id=99] or [add_to_cart_form sku=123ABC]
	 * Essentially a duplicate of the [product_page]
	 * but replacing wc_get_template_part( 'content', 'single-product' ); with woocommerce_template_single_add_to_cart()
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	function kia_add_to_cart_form_shortcode_legacy( $atts ) {
			if ( empty( $atts ) ) {
				return '';
			}

			if ( ! isset( $atts['id'] ) && ! isset( $atts['sku'] ) ) {
				return '';
			}

			$args = array(
				'posts_per_page'      => 1,
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'no_found_rows'       => 1,
			);

			if ( isset( $atts['sku'] ) ) {
				$args['meta_query'][] = array(
					'key'     => '_sku',
					'value'   => sanitize_text_field( $atts['sku'] ),
					'compare' => '=',
				);

				$args['post_type'] = array( 'product', 'product_variation' );
			}

			if ( isset( $atts['id'] ) ) {
				$args['p'] = absint( $atts['id'] );
			}

			$single_product = new WP_Query( $args );

			$preselected_id = '0';

			// Check if sku is a variation.
			if ( isset( $atts['sku'] ) && $single_product->have_posts() && 'product_variation' === $single_product->post->post_type ) {

				$variation = new WC_Product_Variation( $single_product->post->ID );
				$attributes = $variation->get_attributes();

				// Set preselected id to be used by JS to provide context.
				$preselected_id = $single_product->post->ID;

				// Get the parent product object.
				$args = array(
					'posts_per_page'      => 1,
					'post_type'           => 'product',
					'post_status'         => 'publish',
					'ignore_sticky_posts' => 1,
					'no_found_rows'       => 1,
					'p'                   => $single_product->post->post_parent,
				);

				$single_product = new WP_Query( $args );
			?>
				<script type="text/javascript">
					jQuery( document ).ready( function( $ ) {
						var $variations_form = $( '[data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>"]' ).find( 'form.variations_form' );

						<?php foreach ( $attributes as $attr => $value ) { ?>
							$variations_form.find( 'select[name="<?php echo esc_attr( $attr ); ?>"]' ).val( '<?php echo esc_js( $value ); ?>' );
						<?php } ?>
					});
				</script>
			<?php
			}

			// For "is_single" to always make load comments_template() for reviews.
			$single_product->is_single = true;

			ob_start();

			global $wp_query;

			// Backup query object so following loops think this is a product page.
			$previous_wp_query = $wp_query;
			// @codingStandardsIgnoreStart
			$wp_query          = $single_product;
			// @codingStandardsIgnoreEnd

			wp_enqueue_script( 'wc-single-product' );

			while ( $single_product->have_posts() ) {
				$single_product->the_post()
				?>
				<div class="single-product" data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>">
					<?php woocommerce_template_single_add_to_cart(); ?>
				</div>
				<?php
			}

			// Restore $previous_wp_query and reset post data.
			// @codingStandardsIgnoreStart
			$wp_query = $previous_wp_query;
			// @codingStandardsIgnoreEnd
			wp_reset_postdata();

			return '<div class="woocommerce">' . ob_get_clean() . '</div>';
	}

}

if( ! function_exists( 'kia_add_to_cart_form_shortcode' ) ) {
	/**
	 * Display a single product with content-single-product.php template.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	function kia_add_to_cart_form_shortcode( $atts ) {
		global $post;

		if ( empty( $atts ) ) {
			return '';
		}

		$atts = shortcode_atts( array(
			'id'         => '',
			'class'      => '',
			'quantity'   => '1',
			'sku'        => '',
			'style'      => '',
			'show_price' => 'true',
		), $atts, 'product_add_to_cart' );

		if ( ! empty( $atts['id'] ) ) {
			$product_data = get_post( $atts['id'] );
		} elseif ( ! empty( $atts['sku'] ) ) {
			$product_id   = wc_get_product_id_by_sku( $atts['sku'] );
			$product_data = get_post( $product_id );
		} else {
			return '';
		}

		$product = is_object( $product_data ) && in_array( $product_data->post_type, array( 'product', 'product_variation' ), true ) ? wc_setup_product_data( $product_data ) : false;

		if ( ! $product ) {
			return '';
		}

		ob_start();

		echo '<div class="single-product woocommerce add_to_cart_form_shortcode ' . esc_attr( $atts['class'] ) . '" style="' . ( empty( $atts['style'] ) ? '' : esc_attr( $atts['style'] ) ) . '">';

		if ( wc_string_to_bool( $atts['show_price'] ) ) {
			// @codingStandardsIgnoreStart
			echo $product->get_price_html();
			// @codingStandardsIgnoreEnd
		}

		woocommerce_template_single_add_to_cart();

		echo '</div>';

		// Restore Product global in case this is shown inside a product post.
		wc_setup_product_data( $post );

		return ob_get_clean();
	}
}