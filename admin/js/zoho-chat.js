const wind = window;

var store_url = ced_ebay_admin_obj.store_url;
//console.log(store_url);

var $zoho=$zoho || {};$zoho.salesiq = $zoho.salesiq || {widgetcode: "siqa8c5761519650fe047076203095c17a98d7328e7afd77313fcda6c78dfb88931", values:{},ready:function(){}};
var d=document;s=d.createElement("script");s.type="text/javascript";s.id="zsiqscript";s.defer=true;s.src="https://salesiq.zohopublic.in/widget";
var t=d.getElementsByTagName("script")[0];t.parentNode.insertBefore(s,t);

$zoho.salesiq.ready = function() {
    //console.log(sourceData?.[0].domain ?? '');
    $zoho.salesiq.visitor.info({
        'Store URL': store_url,
        'Payment Status': 'Free',
        'App Name': 'Product Lister for eBay',
        'FrameWork': 'WooCommerce'
    });
};
