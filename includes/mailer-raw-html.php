<?php
// phpcs:ignoreFile

namespace AutomateWoo;

defined( 'ABSPATH' ) or exit;

/**
 * @class Mailer_Raw_HTML
 * @since 3.6.0
 */
class Mailer_Raw_HTML extends Mailer {


	/**
	 * Inline styles already contained in the HTML
	 *
	 * @param string|null $content
	 * @return string
	 */
	function style_inline( $content ) {
		if ( ! class_exists( 'DOMDocument' ) ) {
			return $content;
		}

		$css = '';

		if ( $this->include_automatewoo_styles ) {
			ob_start();
			aw_get_template( 'email/styles.php' );
			$css = ob_get_clean();
		}

		$css = apply_filters( 'automatewoo/mailer_raw/styles', $css , $this );

		try {
			$emogrifier = new \AW_Emogrifier( $content, $css );
			$emogrifier->disableInvisibleNodeRemoval();
			$content = $emogrifier->emogrify();
		}
		catch ( \Exception $e ) {
			Logger::error( 'emogrifier', $e->getMessage() );
		}

		return $content;
	}


	/**
	 * @return string
	 */
	function get_email_body() {
		$html = $this->content;
		return $this->prepare_html( $html );
	}

}
