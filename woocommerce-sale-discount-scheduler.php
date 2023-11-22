<?php
/*
Plugin Name: Woocommerce Sale Discount Scheduler
Description: This Plugin provide you options to manage the discount throughout seasonally and occasionally of all your woocommerce products, scheduling discount throughout Any Date and Time.
Author: Geek Code Lab
Version: 1.7
WC tested up to: 8.3.0
Author URI: https://geekcodelab.com/
*/

if(!defined('ABSPATH')) exit;

define("WSDS_BUILD",1.7);

if(!defined("WSDS_PLUGIN_DIR_PATH"))
	
	define("WSDS_PLUGIN_DIR_PATH",plugin_dir_path(__FILE__));
	
if(!defined("WSDS_PLUGIN_URL"))
	
	define("WSDS_PLUGIN_URL",plugins_url().'/'.basename(dirname(__FILE__)));
	
require_once( WSDS_PLUGIN_DIR_PATH .'functions.php');

require_once( WSDS_PLUGIN_DIR_PATH .'shortcodes.php');

require_once( WSDS_PLUGIN_DIR_PATH .'widgets.php');

add_action('admin_enqueue_scripts','wsds_enqueue_styles');

function wsds_enqueue_styles(){
	
	wp_enqueue_style("wsds-admin-style.css",WSDS_PLUGIN_URL."/assets/css/wsds-style.css",array(),WSDS_BUILD);
}
function wsds_front_style_include() {
	
   wp_enqueue_style("wsds-front-style.css",WSDS_PLUGIN_URL."/assets/css/wsds-front-style.css",array(),WSDS_BUILD);
}

add_action( 'wp_enqueue_scripts', 'wsds_front_style_include' );

/** Trigger an admin notice if WooCommerce is not installed.*/
if ( ! function_exists( 'wsds_install_woocommerce_admin_notice' ) ) {
	function wsds_install_woocommerce_admin_notice() { ?>
		<div class="error">
			<p>
				<?php
				// translators: %s is the plugin name.
				echo esc_html( sprintf( __( '%s is enabled but not effective. It requires WooCommerce in order to work.' ), 'Woocommerce Sale Discount Scheduler' ) );
				?>
			</p>
		</div>
		<?php
	}
}
function wsds_woocommerce_constructor() {
    // Check WooCommerce installation
	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'wsds_install_woocommerce_admin_notice' );
		return;
	}

}
add_action( 'plugins_loaded', 'wsds_woocommerce_constructor' );

add_action('admin_init', 'wsds_manage_scheduler');
function wsds_manage_scheduler() {
	
    if ( is_admin() ) {
		
		$current_roles=wp_get_current_user()->roles;
		
		if(in_array("administrator",$current_roles))
		{
			
			include( WSDS_PLUGIN_DIR_PATH .'options.php');
			
		}       
    }
}

add_action('wsds_start_shedule_sale_discount','wsds_start_schedule_sale_discount_event');

add_action('wsds_end_shedule_sale_discount','wsds_end_schedule_sale_discount_event');

function wsds_start_schedule_sale_discount_event($post_id)
{	
	$status=get_post_meta($post_id,'wsds_schedule_sale_status',true);
	if($status){
		update_post_meta($post_id,'wsds_schedule_sale_mode',1);
	}
}
function wsds_end_schedule_sale_discount_event($post_id)
{	
	$status=get_post_meta($post_id,'wsds_schedule_sale_status',true);
	if($status){
		update_post_meta($post_id,'wsds_schedule_sale_mode',0);	
		update_post_meta($post_id,'wsds_schedule_sale_status',0);	
	}
}
function wsds_return_price($price, $product) {
    global $post, $blog_id;
    $product_id = $product->get_id();
	if(!is_object($post))
		return;
	
	$product_ids= wsds_get_schedule_product_list(1);
	$discount_type=get_post_meta($product_id,'wsds_schedule_sale_discount_type',true);
	$sale_price=get_post_meta($product_id,'wsds_schedule_sale_sale_price',true);
	if (in_array($product->get_id(), $product_ids))
	{
		$price = get_post_meta($product_id, '_regular_price');

		if($discount_type=="Percentage") {
			$price = $price[0]-($price[0]*$sale_price)/100;			
		}else{
			// $price = $price[0]-$sale_price;
			$price = ($price[0] - $sale_price);
		}
	}
	return $price;
}
add_filter('woocommerce_product_get_price', 'wsds_return_price', 10, 2);
function wsds_get_discount_price() {
	$price = "";
    global $post, $blog_id;
	global $product;
	$product_ids= wsds_get_schedule_product_list(0);
	$discount_type=get_post_meta($post->ID,'wsds_schedule_sale_discount_type',true);
	$sale_price=get_post_meta($post->ID,'wsds_schedule_sale_sale_price',true);
	if (in_array($product->get_id(), $product_ids))
	{
			$post_id = $post->ID;
			$price = get_post_meta($post->ID, '_regular_price');

				if($discount_type=="Percentage")
				{
					$price = $price[0]-($price[0]*$sale_price)/100;
					
				}
				else
				{
					$price = $price[0]-$sale_price;
					
				}
	}
	return $price;
}
add_action( 'woocommerce_after_shop_loop_item', 'wsds_shop_sale_start_countdown', 5 );
function wsds_shop_sale_start_countdown() {
	global $product;	
	global  $woocommerce;
	$product_ids= wsds_get_schedule_product_list(0);
	$product_id=$product->get_id();
	$sale_price=wsds_get_discount_price();
	$currency_symbol=get_woocommerce_currency_symbol();	
	if (in_array($product_id, $product_ids))
	{ 
		$start_time=get_post_meta($product_id,'wsds_schedule_sale_st_time',true);   
		$countdown=get_post_meta($product_id,'wsds_schedule_sale_start_countdown',true);   
		$time_diffrent=$start_time-time();
		$s = $time_diffrent;
		$m = floor($s / 60);
		$s = $s % 60;
		$h = floor($m / 60);
		$m = $m % 60;
		$d = floor($h / 24);
		$h = $h % 24;
		$display_msg='';
		if($sale_price<0)
		{
			$display_msg='<b>Discount Not Applied: Set Regular Price greater than discount price </b>';
			$last_msg='';
		}
		else
		{
			$display_msg='This product will be sale in ';
			$last_msg='after';
		}
		if ($time_diffrent > 0 && !empty($countdown))
		{
			echo '<div id="wsds_countdown_start_'.$product_id.'" data-product="'.$product_id.'" data-start="'.$start_time.'" class="wsds_countdown_start wsds_coundown_shop">
			
			<span>'.$display_msg.''.$currency_symbol.''.$sale_price.' '.$last_msg.'</span>
			<ul><li><div><span class="wsds_count_digit">'.$d.'</span><span class="wsds_count_lable">Days</span></div></li><li><div><span class="wsds_count_digit">'.$h.'</span><span class="wsds_count_lable">Hours</span></div></li><li><div><span class="wsds_count_digit">'.$m.'</span><span class="wsds_count_lable">Min</span></div></li><li><div><span class="wsds_count_digit">'.$s.'</span><span class="wsds_count_lable">Sec</span></div></li></ul></div>';
		}
	}
}
add_action( 'woocommerce_after_shop_loop_item', 'wsds_shop_sale_ongoing_countdown', 5 );
function wsds_shop_sale_ongoing_countdown() {
	global $product;	
	global  $woocommerce;
	$product_ids= wsds_get_schedule_product_list(1);
	$product_id=$product->get_id();
	$sale_price=wsds_get_discount_price();
	$currency_symbol=get_woocommerce_currency_symbol();	
	if (in_array($product_id, $product_ids))
	{ 
		$end_time=get_post_meta($product_id,'wsds_schedule_sale_end_time',true);  
		$countdown=get_post_meta($product_id,'wsds_schedule_sale_end_countdown',true);   
		$time_diffrent=$end_time-time();
		$s = $time_diffrent;
		$m = floor($s / 60);
		$s = $s % 60;
		$h = floor($m / 60);
		$m = $m % 60;
		$d = floor($h / 24);
		$h = $h % 24;
		
		if ($time_diffrent > 0 && !empty($countdown))
		{
			echo '<div id="wsds_countdown_end_'.$product_id.'" data-product="'.$product_id.'" data-end="'.$end_time.'" class="wsds_countdown_end wsds_coundown_shop">
			<span>Sale ends in</span>
			<ul><li><div><span class="wsds_count_digit">'.$d.'</span><span class="wsds_count_lable">Days</span></div></li><li><div><span class="wsds_count_digit">'.$h.'</span><span class="wsds_count_lable">Hours</span></div></li><li><div><span class="wsds_count_digit">'.$m.'</span><span class="wsds_count_lable">Min</span></div></li><li><div><span class="wsds_count_digit">'.$s.'</span><span class="wsds_count_lable">Sec</span></div></li></ul></div>';
		}
	}
}
add_action( 'woocommerce_single_product_summary', 'wsds_sale_start_countdown', 30 );
 
function wsds_sale_start_countdown() {
	global $product;
	global  $woocommerce;
	$product_ids= wsds_get_schedule_product_list(0);
	$sale_price=wsds_get_discount_price();
	$currency_symbol=get_woocommerce_currency_symbol();
	$product_id=$product->get_id();	
	if (in_array($product_id, $product_ids))
	{ 
		  $start_time=get_post_meta($product_id,'wsds_schedule_sale_st_time',true);  
		  $countdown=get_post_meta($product_id,'wsds_schedule_sale_start_countdown',true);   
		  $time_diffrent=$start_time-time();
		  $s = $time_diffrent;
		  $m = floor($s / 60);
		  $s = $s % 60;
		  $h = floor($m / 60);
		  $m = $m % 60;
		  $d = floor($h / 24);
		  $h = $h % 24;
		if($sale_price<0)
		{
			$display_msg='<b>Discount Not Applied: Set Regular Price greater than discount price </b>';
			$last_msg='';
		}
		else
		{
			$display_msg='This product will be sale in ';
			$last_msg='after';
		}
	if ($time_diffrent > 0 && !empty($countdown))
		{
			
			 echo '
			<div id="wsds_countdown_start_'.$product_id.'" data-product="'.$product_id.'" data-start="'.$start_time.'" class="wsds_countdown_start wsds_coundown_single">
				
				<span>'.$display_msg.''.$currency_symbol.''.$sale_price.' '.$last_msg.'</span>
				<ul>
					<li>
						<div>
							<span class="wsds_count_digit">'.$d.'</span>
							<span class="wsds_count_lable">Days</span>
							<div class="border-over"></div>
							<div class="slice">
								<div class="bar"></div>
							</div>
						</div>
					</li>
					<li>
						<div>
							<span class="wsds_count_digit">'.$h.'</span>
							<span class="wsds_count_lable">Hours</span>
							<div class="border-over"></div>
						</div>
					</li>
					<li>
						<div>
							<span class="wsds_count_digit">'.$m.'</span>
							<span class="wsds_count_lable">Min</span>
							<div class="border-over"></div>
						</div>
					</li>
					<li>
						<div>
							<span class="wsds_count_digit">'.$s.'</span>
							<span class="wsds_count_lable">Sec</span>
							<div class="border-over"></div>
						</div>
					</li>
				</ul>
			</div>';
		}
	}
	
}
add_action( 'woocommerce_single_product_summary', 'wsds_schedule_sale_ongoing_countdown', 30 );
 
 
function wsds_schedule_sale_ongoing_countdown() {
	global $product;
	global  $woocommerce;
	$product_ids= wsds_get_schedule_product_list(1);
	$sale_price=wsds_get_discount_price();
	$currency_symbol=get_woocommerce_currency_symbol();
	$product_id=$product->get_id();	
	if (in_array($product_id, $product_ids))
	{ 
		  $end_time=get_post_meta($product_id,'wsds_schedule_sale_end_time',true);  
		  $countdown=get_post_meta($product_id,'wsds_schedule_sale_end_countdown',true);   
		  $time_diffrent=$end_time-time();
		  $s = $time_diffrent;
		  $m = floor($s / 60);
		  $s = $s % 60;
		  $h = floor($m / 60);
		  $m = $m % 60;
		  $d = floor($h / 24);
		  $h = $h % 24;
	if ($time_diffrent > 0 && !empty($countdown))
		{
			
			 echo '
			<div id="wsds_countdown_end_'.$product_id.'" data-product="'.$product_id.'" data-end="'.$end_time.'" class="wsds_countdown_end wsds_coundown_single">
				
				<span>Sale ends in</span>
				<ul>
					<li>
						<div>
							<span class="wsds_count_digit">'.$d.'</span>
							<span class="wsds_count_lable">Days</span>
							<div class="border-over"></div>
							<div class="slice">
								<div class="bar"></div>
							</div>
						</div>
					</li>
					<li>
						<div>
							<span class="wsds_count_digit">'.$h.'</span>
							<span class="wsds_count_lable">Hours</span>
							<div class="border-over"></div>
						</div>
					</li>
					<li>
						<div>
							<span class="wsds_count_digit">'.$m.'</span>
							<span class="wsds_count_lable">Min</span>
							<div class="border-over"></div>
						</div>
					</li>
					<li>
						<div>
							<span class="wsds_count_digit">'.$s.'</span>
							<span class="wsds_count_lable">Sec</span>
							<div class="border-over"></div>
						</div>
					</li>
				</ul>
			</div>';
		}
	}
}

add_action('admin_footer', 'wsds_schedule_sale_discount_admin_footer_function');
function wsds_schedule_sale_discount_admin_footer_function() {
	$screen = get_current_screen();
	if(isset($screen) && !empty($screen)){
		if(isset($screen->post_type)){
			if($screen->post_type == 'product'){ ?>
			<script>
				jQuery("#wsds_st_date").datepicker({
					dateFormat: 'yy-m-d'
				});
				jQuery("#wsds_end_date").datepicker({
					dateFormat: 'yy-m-d'
				});
			</script>
		<?php }
		}
	}
}
add_action('wp_footer', 'wsds_schedule_sale_discount_front_footer_function');
function wsds_schedule_sale_discount_front_footer_function() {
	?>
	<script>
		jQuery(".wsds_countdown_start").each(function() {
			var start_time = jQuery(this).attr('data-start');
			var product_id = jQuery(this).attr('data-product');
			var interval1 = setInterval(function() {
				var today = new Date();
				var str = today.toGMTString();
				var now_timestamp = Date.parse(str) / 1000;
				var remain_start_time = start_time - now_timestamp;
				if (remain_start_time > 0) {
					jQuery('#wsds_countdown_start_' + product_id + ' ul').html(wsds_convertMS(remain_start_time + '000'));
					
				} else {
					clearInterval(interval1);
					document.location.reload(true);
					}
			}, 1000);
			
		});
		
		jQuery(".wsds_countdown_end").each(function() {
			var end_time = jQuery(this).attr('data-end');
			var product_id = jQuery(this).attr('data-product');
			var interval2 = setInterval(function() {
				var today = new Date();
				var str = today.toGMTString();
				var now_timestamp = Date.parse(str) / 1000;
				var remain_end_time = end_time - now_timestamp;
				if (remain_end_time > 0) {
					jQuery('#wsds_countdown_end_' + product_id + ' ul').html(wsds_convertMS(remain_end_time + '000'));
					
				} else {
					clearInterval(interval2);
					document.location.reload();
					
				}
			}, 1000);
			
		});
		function wsds_convertMS(ms) {
			var d, h, m, s;
			s = Math.floor(ms / 1000);
			m = Math.floor(s / 60);
			s = s % 60;
			h = Math.floor(m / 60);
			m = m % 60;
			d = Math.floor(h / 24);
			h = h % 24;
			// return { d: d, h: h, m: m, s: s };
			var html = '<li><div><span class="wsds_count_digit">' + d + '</span><span class="wsds_count_lable">Days</span></div></li><li><div><span class="wsds_count_digit">' + h + '</span><span class="wsds_count_lable">Hours</span></div></li><li><div><span class="wsds_count_digit">' + m + '</span><span class="wsds_count_lable">Min</span></div></li><li><div><span class="wsds_count_digit">' + s + '</span><span class="wsds_count_lable">Sec</span></div></li>'
			return html;
		};
	</script>
<?php }
/**
 * Added HPOS support for woocommerce
 */
add_action( 'before_woocommerce_init', 'wsds_before_woocommerce_init' );
function wsds_before_woocommerce_init() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}