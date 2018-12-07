<?php
// phpcs:ignoreFile

namespace AutomateWoo\Fields;

use AutomateWoo\Clean;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Product
 */
class Product extends Field {

	protected $name = 'product';

	protected $type = 'product';

	/**
	 * Allow multiple product selections.
	 *
	 * @var bool
	 */
	public $multiple = false;

	/**
	 * Allow product variations to be possible selections.
	 *
	 * @var bool
	 */
	public $allow_variations = false;

	/**
	 * Flag to define whether variable products should be included in search results for the select box.
	 *
	 * @var bool
	 */
	public $allow_variable = true;


	function __construct() {
		parent::__construct();
		$this->set_title( __( 'Product', 'automatewoo' ) );
		$this->classes[] = 'wc-product-search';
	}


	/**
	 * @param int $value
	 */
	function render( $value ) {

		if ( $this->allow_variable && $this->allow_variations ) {
			$ajax_action = 'woocommerce_json_search_products_and_variations';
		} elseif ( false === $this->allow_variable && true === $this->allow_variations ) {
			$ajax_action = 'aw_json_search_products_and_variations_not_variable';
		} elseif ( false === $this->allow_variable && false === $this->allow_variations ) {
			$ajax_action = 'aw_json_search_products_not_variations_not_variable';
		} else {
			// allows variable but not variations
			$ajax_action = 'woocommerce_json_search_products';
		}

		$selected_products = [];

		if ( $value ) {
			$selected_products = array_filter( array_map( 'wc_get_product', (array) $value ) );
		}

		?>

		<select class="<?php echo esc_attr( $this->get_classes() ) ?>"
				<?php echo $this->multiple ? 'multiple="multiple"' : '' ?>
			    name="<?php echo esc_attr( $this->get_full_name() ); ?><?php echo $this->multiple ? '[]' : '' ?>"
			    data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'automatewoo' ); ?>"
			    data-action="<?php echo esc_attr( $ajax_action ); ?>">
			<?php
			foreach ( $selected_products as $product ) {
				echo '<option value="' . esc_attr( $product->get_id() ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
			}
			?>
		</select>

		<script type="text/javascript">
			jQuery( 'body' ).trigger( 'wc-enhanced-select-init' );
		</script>

	<?php
	}


	/**
	 * Sanitizes the value of the field.
	 *
	 * @since 4.4.0
	 *
	 * @param array|string $value
	 *
	 * @return array|string
	 */
	function sanitize_value( $value ) {
		if ( $this->multiple ) {
			return Clean::ids( $value );
		}
		else {
			return Clean::id( $value );
		}
	}

}
