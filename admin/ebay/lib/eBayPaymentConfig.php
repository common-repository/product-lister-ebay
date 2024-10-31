<?php
if ( ! class_exists( 'Ced_EBay_Config' ) ) {
	class Ced_EBay_Config {


		// public $userId;
		public $endpointUrl;
		public $partnerId;
		public $secretKey;

		public static $_instance;

		/**
		 * Ced_EBay_Config Instance.
		 *
		 * Ensures only one instance of Ced_EBay_Config is loaded or can be loaded.
		 *
		userId
		 *
		 * @since 1.0.0
		 * @static
		 * @return CED_UMB_EBAY_ebay_Manager instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		/**
		 * Function to get All available sites for ebay
		 *
		 * @name getEbaysites
		 */
		public function getEbaysites() {
			$ebaySites = array(
				array(
					'siteID'       => '15',
					'name'         => 'Australia',
					'countrycode'  => 'AU',
					'abbreviation' => 'AU',
					'currency'     => array( 'AUD' ),
				),
				array(
					'siteID'       => '2',
					'name'         => 'Canada',
					'countrycode'  => 'CA',
					'abbreviation' => 'CA',
					'currency'     => array( 'CAD', 'USD' ),
				),
				array(
					'siteID'       => '210',
					'name'         => 'CanadaFrench',
					'countrycode'  => 'CA',
					'abbreviation' => 'CAFR',
					'currency'     => array( 'CAD', 'USD' ),
				),
				array(
					'siteID'       => '201',
					'name'         => 'HongKong',
					'countrycode'  => 'HK',
					'abbreviation' => 'HK',
					'currency'     => array( 'HKD' ),
				),
				array(
					'siteID'       => '207',
					'name'         => 'Malaysia',
					'countrycode'  => 'MY',
					'abbreviation' => 'MY',
					'currency'     => array( 'MYR' ),
				),
				array(
					'siteID'       => '211',
					'name'         => 'Philippines',
					'countrycode'  => 'PH',
					'abbreviation' => 'PH',
					'currency'     => array( 'PHP' ),
				),
				array(
					'siteID'       => '216',
					'name'         => 'Singapore',
					'countrycode'  => 'SG',
					'abbreviation' => 'SG',
					'currency'     => array( 'SGD' ),
				),
				array(
					'siteID'       => '3',
					'name'         => 'UK',
					'countrycode'  => 'GB',
					'abbreviation' => 'UK',
					'currency'     => array( 'GBP' ),
				),
				array(
					'siteID'       => '0',
					'name'         => 'US',
					'countrycode'  => 'US',
					'abbreviation' => 'US',
					'currency'     => array( 'USD' ),
				),
				array(
					'siteID'       => '71',
					'name'         => 'France',
					'abbreviation' => 'FR',
					'currency'     => array( 'EUR' ),
					'countrycode'  => 'FR',
				),
				array(
					'siteID'       => '101',
					'name'         => 'Italy',
					'countrycode'  => 'IT',
					'abbreviation' => 'IT',
					'currency'     => array( 'EUR' ),
				),
			);
			return $ebaySites;
		}

		public function getEbayPaymentMethods() {
			$payArray = array(
				array(
					'name'        => 'American Express',
					'value'       => 'AmEx',
					'description' => 'Not applicable to US/CA eBay Motors listings',
				),
				array(
					'name'        => 'Cash-in-person option',
					'value'       => 'CashInPerson',
					'description' => 'Applicable only to US and Canada eBay Motors vehicles',
				),
				array(
					'name'        => 'Payment on delivery',
					'value'       => 'CashOnPickup',
					'description' => 'Not applicable to Real Estate or US/CA eBay Motors listings',
				),
				array(
					'name'        => 'Credit card',
					'value'       => 'CCAccepted',
					'description' => 'Not applicable to Real Estate or US/CA eBay Motors listings',
				),
				array(
					'name'        => 'Cash on delivery',
					'value'       => 'COD',
					'description' => ' This payment method is obsolete (ignored) for the US, US eBay Motors, UK, and Canada sites. See "Field Differences for eBay Sites" in the eBay Web Services Guide for a list of sites that accept COD as a payment method. Not applicable to Real Estate listings',
				),
				array(
					'name'        => 'Credit Card',
					'value'       => 'CreditCard',
					'description' => 'This value indicates that a credit card can be used/was used to pay for the order',
				),
				array(
					'name'        => 'Diners',
					'value'       => 'Diners',
					'description' => 'This payment method can be added only if a seller has a IMCC payment gateway account and Diners Club card is selected in credit card preference',
				),
				array(
					'name'        => 'Direct Debit',
					'value'       => 'DirectDebit',
					'description' => 'This value indicates that a debit card can be used/was used to pay for the order',
				),
				array(
					'name'        => 'Discover card',
					'value'       => 'Discover',
					'description' => 'Not applicable to US/CA eBay Motors listings',
				),
				array(
					'name'        => 'Elektronisches Lastschriftverfahren (direct debit)',
					'value'       => 'ELV',
					'description' => 'Only applicable to the Express Germany site, which has been shut down',
				),
				array(
					'name'        => 'Integrated Merchant CreditCard',
					'value'       => 'IntegratedMerchantCreditCard',
					'description' => 'This payment method can be added only if a seller has a payment gateway account',
				),
				array(
					'name'        => 'Loan Check',
					'value'       => 'LoanCheck',
					'description' => 'Loan check option (applicable only to the US eBay Motors site, except in the Parts and Accessories category, and the eBay Canada site for motors)',
				),
				array(
					'name'        => 'Money order/cashiers check',
					'value'       => 'MOCC',
					'description' => 'Not applicable to US/CA eBay Motors listings',
				),
				array(
					'name'        => 'Direct transfer of money',
					'value'       => 'MoneyXferAccepted',
					'description' => 'Not applicable to US/CA eBay Motors listings',
				),
				array(
					'name'        => 'MoneyXferAcceptedInCheckout',
					'value'       => 'MoneyXferAcceptedInCheckout',
					'description' => 'If the seller has bank account information on file, and MoneyXferAcceptedInCheckout = true, then the bank account information will be displayed in Checkout. Applicable only to certain global eBay sites. See the "International Differences Overview" in the eBay Web Services Guide',
				),
				array(
					'name'        => 'None',
					'value'       => 'No payment method specified',
					'description' => ' For example, no payment methods would be specified for Ad format listings',
				),
				array(
					'name'        => 'Other',
					'value'       => 'Other forms of payment',
					'description' => 'Some custom methods are accepted by seller as the payment method in the transaction. Not applicable to US/CA eBay Motors listings (see PaymentSeeDescription instead)',
				),
				array(
					'name'        => 'All other online payments',
					'value'       => 'OtherOnlinePayments',
					'description' => 'Not applicable to US/CA eBay Motors listings',
				),
				array(
					'name'        => 'PaisaPay (for India site only)',
					'value'       => 'PaisaPayAccepted',
					'description' => 'This qualifies as a safe payment method and is required for all categories on the IN site',
				),
				array(
					'name'        => 'PaisaPayEscrow payment option',
					'value'       => 'PaisaPayEscrow',
					'description' => 'Applicable on selected categories on the India site only',
				),
				array(
					'name'        => 'PaisaPayEscrowEMI (Equal Monthly Installments) Payment option',
					'value'       => 'PaisaPayEscrowEMI',
					'description' => 'Must be specified with PaisaPayEscrow. Applicable only to India site',
				),
				array(
					'name'        => 'Payment See Description',
					'value'       => 'PaymentSeeDescription',
					'description' => 'Payment instructions are contained in the items description',
				),
				array(
					'name'        => 'PayOnPickup payment method',
					'value'       => 'PayOnPickup',
					'description' => 'PayOnPickup is the same as CashOnPickup. For listings on the eBay US site, the user interface refers to this feature as Pay on pickup',
				),
				array(
					'name'        => 'PayPal',
					'value'       => 'PayPal',
					'description' => 'This qualifies as a safe payment method. If true in listing requests, Item.PayPalEmailAddress must also be specified',
				),
				array(
					'name'        => 'PayPal Credit',
					'value'       => 'PayPalCredit',
					'description' => 'This value indicates that a PayPal credit card can be used/was used to pay for the order',
				),
				array(
					'name'        => 'PayUpon Invoice',
					'value'       => 'PayUponInvoice',
					'description' => 'This buyer payment method is only applicable for the Germany site and is associated with the rollout of Progressive Checkout and the Pay Upon Invoice feature',
				),
				array(
					'name'        => 'Personal Check',
					'value'       => 'PersonalCheck',
					'description' => '',
				),
				array(
					'name'        => 'Visa/Mastercard',
					'value'       => 'VisaMC',
					'description' => 'These qualify as safe payment methods. Not applicable to US/CA eBay Motors listings',
				),
			);

			return $payArray;
		}
		/**
		 * Constructor
		 */
		public function __construct() {
		}
	}
}
