<?php
if(!defined('ABSPATH')) exit;

add_filter( 'woocommerce_product_data_tabs', 'wsds_product_schedule_sale_data_tab' ); 
function wsds_product_schedule_sale_data_tab( $product_data_tabs ) 
{ 
	$product_data_tabs['wsds-schedule-sale-tab'] = array( 
				'label' => __( 'Schedule Sale Discount', 'woocommerce-sale-discount-scheduler' ), 
				'target' => 'wsds_product_schedule_sale_data_panel', 
				'class' => array( 'show_if_simple' ), );
				return $product_data_tabs; 
} 
add_action('woocommerce_product_data_panels', 'wsds_product_schedule_sale_data_fields');

function wsds_product_schedule_sale_data_fields() {
	
    global $post;
	$now=time();
	$post_id=$post->ID;
	
	$status=get_post_meta($post_id,'wsds_schedule_sale_status',true);   
	$start_time=get_post_meta($post_id,'wsds_schedule_sale_st_time',true);   
	$end_time=get_post_meta($post_id,'wsds_schedule_sale_end_time',true);   
	$mode=get_post_meta($post_id,'wsds_schedule_sale_mode',true);   
	$discount_type=get_post_meta($post_id,'wsds_schedule_sale_discount_type',true);   
	$sale_price=get_post_meta($post_id,'wsds_schedule_sale_sale_price',true);   
	$start_countdown=get_post_meta($post_id,'wsds_schedule_sale_start_countdown',true);   
	$end_countdown=get_post_meta($post_id,'wsds_schedule_sale_end_countdown',true);

	$wsds_shop_loop_countdown = '';
	if(metadata_exists('post',$post_id,'wsds_enable_countdown_on_shop_loop')) {
		$wsds_shop_loop_countdown = get_post_meta($post_id,'wsds_enable_countdown_on_shop_loop',true);
	}else{
		if($start_countdown==1 || $end_countdown==1) {
			$wsds_shop_loop_countdown = 'on';
		}
	}

	$wsds_single_product_countdown = '';
	if(metadata_exists('post',$post_id,'wsds_enable_countdown_on_single_product')) {
		$wsds_single_product_countdown = get_post_meta($post_id,'wsds_enable_countdown_on_single_product',true);
	}else{
		if($start_countdown==1 || $end_countdown==1) {
			$wsds_single_product_countdown = 'on';
		}
	}

	if(!empty($start_time))
	{
		$start_date=date('Y-m-d', $start_time);
		$st_mm=date('m', $start_time);
		$st_dd=date('d', $start_time);
		$st_hh=date('H', $start_time);
		$st_mn=date('i', $start_time);
	}
	if(isset($end_time) &!empty($end_time))
	{
		$end_date=date('Y-m-d', $end_time);
		$end_mm=date('m', $end_time);
		$end_dd=date('d', $end_time);
		$end_hh=date('H', $end_time);
		$end_mn=date('i', $end_time);
	}
	?>
   <div id ='wsds_product_schedule_sale_data_panel' class ='panel woocommerce_options_panel wsds_options_panel' > 
    <div class = 'wsds_options_group' >
	<p class='form-field wsds_select_status'>
		<label for='wsds_select_status'><?php _e( 'Status', 'woocommerce-sale-discount-scheduler' ); ?></label>
		<select name='wsds_select_status' class='wsds_enhanced_select' id="wsds_select_status">
			<option  <?php if($status==0){ echo "selected"; } ?> value='0'>Disable</option>
			<option  <?php if($status==1){ echo "selected"; } ?> value='1'>Enable</option>
		</select>	
    </p>
	<p class="form-field wsds_select_start_time">
		<label for='wsds_select_start_time'><?php _e( 'Start Time', 'woocommerce-sale-discount-scheduler' ); ?></label> 
		<span class="screen-reader-text wsds_start_yy">Year</span>
		<input type="text" id="wsds_st_date" class="wsds_st_date" name="wsds_st_date" value="<?php if(!empty($start_time)) { echo $start_date; } ?>" placeholder="From… YYYY-MM-DD" maxlength="10" autocomplete="off">
			<span>@</span>
			<span class="screen-reader-text wsds_hh">Hour</span>
			<input type="text" id="wsds_st_hh" class="wsds_st_hh" name="wsds_st_hh" placeholder="HH" value="<?php if(!empty($start_time)) { echo $st_hh; } ?>" size="2" maxlength="2" autocomplete="off"><span>:</span>
			<span class="screen-reader-text wsds_mn">Minute</span>
			<input type="text" id="wsds_st_mn" class="wsds_st_mn" name="wsds_st_mn" placeholder="MM" value="<?php if(!empty($start_time)) { echo $st_mn; } ?>" size="2" maxlength="2" autocomplete="off">
			<span>GMT</span>
			
	</p>
	<p class="form-field wsds_select_end_time">
		<label for='wsds_select_end_time'><?php _e( 'End Time', 'woocommerce-sale-discount-scheduler' ); ?></label>
		<span class="screen-reader-text wsds_end_yy">Year</span>
				<input type="text" id="wsds_end_date" class="wsds_end_date" name="wsds_end_date" value="<?php if(!empty($end_time)) { echo $end_date; } ?>" placeholder="From… YYYY-MM-DD" maxlength="10" autocomplete="off">
				<span>@</span>
				<span class="screen-reader-text">Hour</span>
				<input type="text" id="wsds_end_hh" class="wsds_end_hh" name="wsds_end_hh" placeholder="HH" value="<?php if(!empty($end_time)) { echo $end_hh; } ?>" size="2" maxlength="2" autocomplete="off">
				<span>:</span>
				<span class="screen-reader-text">Minute</span>
				<input type="text" id="wsds_end_mn"  class="wsds_end_mn" name="wsds_end_mn" placeholder="MM" value="<?php if(!empty($end_time)) { echo $end_mn; } ?>" size="2" maxlength="2" autocomplete="off">
				<span>GMT</span>
			
	</p>
	<p class="form-field wsds_discounttype">
		<label for='wsds_select_discounttype'><?php _e( 'Discount Type', 'woocommerce-sale-discount-scheduler' ); ?></label>
		<select name='wsds_discounttype' class='wsds_enhanced_select' id="wsds_discounttype">
			<option  <?php if($discount_type=="Fixed Price Discount"){ echo "selected"; } ?> value='Fixed Price'>Fixed Price Discount</option>
			<option  <?php if($discount_type=="Percentage"){ echo "selected"; } ?> value='Percentage'>%(Percentage)</option>
		</select>
			
	</p>
	<p class="form-field wsds_saleprice">
		<label for='wsds_saleprice'><?php _e( 'Sale Price Discount', 'woocommerce-sale-discount-scheduler' ); ?></label>
		<input type="text" id="wsds_saleprice" class="wsds_saleprice" name="wsds_saleprice" value="<?php echo $sale_price; ?>" placeholder="Sale Price Discount" autocomplete="off">
		<span>Off regular price</span>
	</p>
	<p class="form-field wsds_sale_begin_countdown">
		<label for='wsds_select_start_time'><?php _e( 'Future Sale CountDown', 'woocommerce-sale-discount-scheduler' ); ?></label>
		<select name='wsds_sale_start_countdown' class='wsds_enhanced_select' id="wsds_sale_start_countdown">
			<option  <?php if($start_countdown==0){ echo "selected"; } ?> value='0'>Disable</option>
			<option  <?php if($start_countdown==1){ echo "selected"; } ?> value='1'>Enable</option>
		</select><span>CountDown is showing until start sale</span>	
			
	</p>
	<p class="form-field wsds_sale_ongoing_countdown">
		<label for='wsds_select_end_time'><?php _e( 'On Sale CountDown', 'woocommerce-sale-discount-scheduler' ); ?></label>
		<select name='wsds_sale_end_countdown' class='wsds_enhanced_select' id="wsds_sale_end_countdown">
			<option  <?php if($end_countdown==0){ echo "selected"; } ?> value='0'>Disable</option>
			<option  <?php if($end_countdown==1){ echo "selected"; } ?> value='1'>Enable</option>
		</select><span>CountDown is showing during sale</span>	
			
	</p>
	<p class="form-field wsds_show_countdown">
		<label for='wsds_enable_countdown_on_shop_loop'><?php _e( 'Shop loop Countdown', 'woocommerce-sale-discount-scheduler' ); ?></label>
		<input type="checkbox" name="wsds_enable_countdown_on_shop_loop" id="wsds_enable_countdown_on_shop_loop" value="on" <?php checked( $wsds_shop_loop_countdown, 'on' ); ?> />
		<span for="wsds_enable_countdown_on_shop_loop">Enable</span><br>
		<span class="wsds_note"><?php _e('Enable this to display countdown in shop loop.','woocommerce-sale-discount-scheduler'); ?></span>
	</p>
	<p class="form-field wsds_show_countdown">
		<label for='wsds_enable_countdown_on_single_product'><?php _e( 'Single product Countdown', 'woocommerce-sale-discount-scheduler' ); ?></label>
		<input type="checkbox" name="wsds_enable_countdown_on_single_product" id="wsds_enable_countdown_on_single_product" value="on" <?php checked( $wsds_single_product_countdown, 'on' ); ?> />
		<span for="wsds_enable_countdown_on_single_product">Enable</span><br>
		<span class="wsds_note"><?php _e('Enable this to display countdown in single product page.','woocommerce-sale-discount-scheduler'); ?></span>
	</p>
	<p class="form-field wsds_note"><?php echo esc_html__('Note: Start time and End time will be on GMT, Current GMT time is:','woocommerce-sale-discount-scheduler'); ?> <?php echo date("Y-m-d @ H:i",$now); ?><br> Note: Sale price discount will be consider on regular price.  </p>
	
  </div>

    </div><?php
}

/** Hook callback function to save custom fields information */
function wsds_save_discount_sale_schedule_data($post_id) {
		global $post;
		$wsds_error=false;
		$wsds_st_hh=00;
		$wsds_st_mn=00;
		$wsds_end_hh=00;
		$wsds_end_mn=00;
		
		$wsds_status =sanitize_text_field($_POST['wsds_select_status']);
		$wsds_discounttype =sanitize_text_field($_POST['wsds_discounttype']);
		$wsds_saleprice =sanitize_text_field($_POST['wsds_saleprice']);
		$start_countdown =sanitize_text_field($_POST['wsds_sale_start_countdown']);
		$end_countdown =sanitize_text_field($_POST['wsds_sale_end_countdown']);
		$wsds_st_date=sanitize_text_field($_POST['wsds_st_date']);
		$wsds_end_date=sanitize_text_field($_POST['wsds_end_date']);

		if(!empty($_POST['wsds_st_hh'])) $wsds_st_hh=sanitize_text_field($_POST['wsds_st_hh']);
		if(!empty($_POST['wsds_st_mn'])) $wsds_st_mn=sanitize_text_field($_POST['wsds_st_mn']);		
		if(!empty($_POST['wsds_end_hh'])) $wsds_end_hh=sanitize_text_field($_POST['wsds_end_hh']);
		if(!empty($_POST['wsds_end_mn'])) $wsds_end_mn=sanitize_text_field($_POST['wsds_end_mn']);
		if(isset($_POST['wsds_enable_countdown_on_shop_loop'])) $wsds_shop_loop_countdown=sanitize_text_field($_POST['wsds_enable_countdown_on_shop_loop']);
		if(isset($_POST['wsds_enable_countdown_on_single_product'])) $wsds_single_product_countdown=sanitize_text_field($_POST['wsds_enable_countdown_on_single_product']);

		$wsds_start_schedule_hook="wsds_start_shedule_sale_discount";
		$wsds_end_schedule_hook="wsds_end_shedule_sale_discount";	
		//Y-m-d H:i:s
		$wsds_st_time=strtotime($wsds_st_date." ".$wsds_st_hh.":".$wsds_st_mn.":00"); 
		
		$wsds_end_time=strtotime($wsds_end_date." ".$wsds_end_hh.":".$wsds_end_mn.":00");
		if($wsds_status==1)
		{			
				wp_clear_scheduled_hook( $wsds_start_schedule_hook, array($post->ID) );
				wp_clear_scheduled_hook( $wsds_end_schedule_hook, array($post->ID) );
				wp_schedule_single_event($wsds_st_time, $wsds_start_schedule_hook,array($post->ID));
				wp_schedule_single_event($wsds_end_time, $wsds_end_schedule_hook,array($post->ID));
		}
		// Save Data
		
		if (!empty($wsds_st_date) && !empty($wsds_end_date)) {
			
			if(isset($wsds_status)) 		update_post_meta($post_id,'wsds_schedule_sale_status',$wsds_status);   
			if(isset($wsds_st_time)) 		update_post_meta($post_id,'wsds_schedule_sale_st_time',$wsds_st_time);   
			if(isset($wsds_end_time)) 		update_post_meta($post_id,'wsds_schedule_sale_end_time',$wsds_end_time);   
			if(isset($wsds_discounttype)) 	update_post_meta($post_id,'wsds_schedule_sale_discount_type',$wsds_discounttype);   
			if(isset($wsds_saleprice)) 		update_post_meta($post_id,'wsds_schedule_sale_sale_price',$wsds_saleprice);   
			if(isset($start_countdown)) 	update_post_meta($post_id,'wsds_schedule_sale_start_countdown',$start_countdown);   
			if(isset($end_countdown)) 		update_post_meta($post_id,'wsds_schedule_sale_end_countdown',$end_countdown);
			if(isset($wsds_shop_loop_countdown)) 	update_post_meta($post_id,'wsds_enable_countdown_on_shop_loop',$wsds_shop_loop_countdown);
			if(isset($wsds_single_product_countdown)) 	update_post_meta($post_id,'wsds_enable_countdown_on_single_product',$wsds_single_product_countdown);
			
			if($wsds_st_time > time()) {
				update_post_meta($post_id,'wsds_schedule_sale_mode',0);   	
			}			
		}
} 

add_action( 'woocommerce_process_product_meta', 'wsds_save_discount_sale_schedule_data'  );