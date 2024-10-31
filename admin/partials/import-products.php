<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$file = CED_EBAY_DIRPATH . 'admin/partials/header.php';
if ( file_exists( $file ) ) {
	require_once $file;
}

if ( ! class_exists( 'Ced_Ebay_Import_Products_View' ) ) {
	class Ced_Ebay_Import_Products_View {


		public function ced_ebay_renderImportProductsViewHTML() {
			$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
			?>
 <div class="upgrade-bg d-flex justify-content-center align-items-center">
	  <div class="premium-card col-11 col-sm-9 col-md-7 col-lg-5 d-flex flex-column justify-content-center align-items-around">
		<div class="d-flex flex-nowrap justify-content-around align-items-center">
		  <div class="d-flex flex-column justify-content-start align-items-start">
			<h3 class="upgrade-heading">Import your eBay Products automatically</h3>
			<p class="upgrade-text">
			  Upgrade to full version to automatically import your eBay Products to your WooCommerce store. We automatically import all the product data including variations and create them in WooCommerce.
			</p>
			<div class="upgrade-btn-container">
			<button class="upgrade-btn" ><a href="https://woocommerce.com/products/ebay-integration-for-woocommerce/?quid=eb215c39718f271235a31fd70ac12fbb" target="_blank" style="color:white;">Upgrade to Full Version</a></button>

			</div>
		  </div>
		</div>
	  </div>
	</div>


<style>
	.upgrade-btn-container{
		display: flex-start;
	justify-content: space-around;
	width: 100%;
	gap: 5px;
	}
#wpfooter {
	position: relative;
}
.col-11,.col-lg-5,.col-md-7,.col-sm-9{position:relative;width:100%;padding-right:15px;padding-left:15px;}
.col-11{-ms-flex:0 0 91.666667%;flex:0 0 91.666667%;max-width:91.666667%;}
@media (min-width:576px){
.col-sm-9{-ms-flex:0 0 75%;flex:0 0 75%;max-width:75%;}
}
@media (min-width:768px){
.col-md-7{-ms-flex:0 0 58.333333%;flex:0 0 58.333333%;max-width:58.333333%;}
}
@media (min-width:992px){
.col-lg-5{-ms-flex:0 0 41.666667%;flex:0 0 41.666667%;max-width:41.666667%;}
}
.d-flex{display:-ms-flexbox!important;display:flex!important;}
.flex-column{-ms-flex-direction:column!important;flex-direction:column!important;}
.flex-nowrap{-ms-flex-wrap:nowrap!important;flex-wrap:nowrap!important;}
.justify-content-start{-ms-flex-pack:start!important;justify-content:flex-start!important;}
.justify-content-center{-ms-flex-pack:center!important;justify-content:center!important;}
.justify-content-around{-ms-flex-pack:distribute!important;justify-content:space-around!important;}
.align-items-start{-ms-flex-align:start!important;align-items:flex-start!important;}
.align-items-center{-ms-flex-align:center!important;align-items:center!important;}
.align-self-end{-ms-flex-item-align:end!important;align-self:flex-end!important;}

.fas{-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;display:inline-block;font-style:normal;font-variant:normal;text-rendering:auto;line-height:1;}
.fa-times:before{content:"\f00d";}
.fas{font-family:"Font Awesome 5 Free";}
.fas{font-weight:900;}
@media only screen and (min-width: 320px) and (max-width: 991.98px){
.upgrade-img{width:30%;margin-right:5%;}
}
@media only screen and (min-width: 920px){
.upgrade-img{width:20%;margin-right:5%;margin-top:5%;}
}
.upgrade-bg{background-color:#e2e7f7;min-height:100vh;width:100%;margin:10px 20px 0 2px;}
.premium-card{background-color:#ffffff;padding-top:1rem;padding-bottom:2rem;border-radius:5px;}
.cross-img{margin-bottom:1rem;color:rgba(0, 0, 0, 0.5);}
.upgrade-heading{font-size:1.2rem!important;}
.upgrade-text{font-size:0.9rem;color:rgba(0, 0, 0, 0.8);}
.upgrade-btn{font-size:0.9rem;background:linear-gradient(135deg, #9089fc, #3b86e2);color:#ffffff;border:none;border-radius:2px;padding:5px 3%;margin-top:5%;}
	</style>
			<?php

		}
	}
}
$obj = new Ced_Ebay_Import_Products_View();
$obj->ced_ebay_renderImportProductsViewHTML();
?>
