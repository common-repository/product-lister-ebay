<?php
if ( ! class_exists( 'EbayUpdate' ) ) {
	class EbayUpdate {


		private static $_instance;

		public $siteID;
		public $token;

		/**
		 * Get_instance Instance.
		 *
		 * Ensures only one instance of EbayUpdate is loaded or can be loaded.
		 *
		userId
		 *
		 * @since 1.0.0
		 * @static
		 * @return get_instance instance.
		 */
		public static function get_instance( $siteID, $token ) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $siteID, $token );
			}
			return self::$_instance;
		}
		/**
		 * Constructs
		 */
		public function __construct( $siteID, $token ) {
			$this->loadDepenedency();
			$this->token  = $token;
			$this->siteID = $siteID;
		}

		public function revise( $productId ) {

		}
	}
}
