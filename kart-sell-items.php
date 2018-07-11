<?php
/*
Plugin Name: Kart Sell Items
Description: This plugin enables user to upload their second hand products in order to sell them online. Please use short code to display form i.e. <strong>[kart_sell_items_form]</strong> in content area. Use php function do_shortcode in order to call form in template. <strong> Note: Woocommerce plugin should be pre-installed and activated. If not please first download woocommerce plugin and activate it.</strong>
*/

function theme_name_scripts() {
	wp_enqueue_style( 'kart-form-style', plugins_url().'/kart-sell-items/css/style.css' );
	wp_enqueue_script( 'kart-form-script', plugins_url().'/kart-sell-items/js/jquery-1.11.0.min.js' );
	wp_localize_script( 'kart-form-script', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'theme_name_scripts' );
	
function kart_insert_term() {
	/*wp_insert_term(
      'Second hand items',
      'product_cat', // the taxonomy
      array(
        'description'=> 'Second hand items category.',
        'slug' => 'second-hand-items',
      )
    );*/
	$my_post = array(
	  'post_title'    => 'Add Product',
	  'post_content'  => '[kart_sell_items_form]',
	  'post_status'   => 'publish',
	  'comment_status'   => 'closed',
	  'post_type'	  => 'page',
	  'post_author'   => 1
	);
	wp_insert_post( $my_post );
}
register_activation_hook( __FILE__, 'kart_insert_term' );
	
function main() { 

	function upload_user_file( $file = array(), $post_id, $user_id ) {
		
		require_once( ABSPATH . 'wp-admin/includes/admin.php' );
		
		$file_return = wp_handle_upload( $file, array('test_form' => false ) );
		
		if( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
			return false;
		} else {
			
			$filename = $file_return['file'];
			
			$attachment = array(
				'post_author'	=>	$user_id,
				'post_mime_type' => $file_return['type'],
				'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content' => '',
				'post_status' => 'inherit',
				'post_parent' => $post_id,
				'guid' => $file_return['url']
			);
			
			$attachment_id = wp_insert_attachment( $attachment, $file_return['url'] );
			
			require_once (ABSPATH . 'wp-admin/includes/image.php' );
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
			wp_update_attachment_metadata( $attachment_id, $attachment_data );
			
			if( 0 < intval( $attachment_id ) ) {
				return $attachment_id;
			}
		}
		
		return false;
	}
	
	if(isset($_POST['postid'])){
		$postid = explode('-', $_POST['postid']);
		$post_id = $postid[0];
		$user_id = $postid[1];
		if( ! empty( $_FILES ) ) {
			$index = 1;
			foreach( $_FILES as $file ) {
				if( is_array( $file ) ) {
					$attachment_id = upload_user_file( $file, $post_id, $user_id );
					$ids[] = $attachment_id;
				}
				if($index == 1){
					update_post_meta($post_id, '_thumbnail_id', $attachment_id);
				}
				$index++;
			}
			$ids = implode(',', $ids);
			update_post_meta($post_id, '_product_image_gallery', $ids);
			echo '<div class="kart-field kart-success">Product submitted successfully and awaiting admin approval. Once approved it will be published on site.</div>';
		}
	}
	///// Get all product categories
	$args = array(
		'number'     => $number,
		'orderby'    => $orderby,
		'order'      => $order,
		'hide_empty' => $hide_empty,
		'include'    => $ids
	);

	$product_categories = get_terms( 'product_cat', $args );
?>
	<div class="kart-form-div">
		<form method="POST" action="" id="kart-form" name="kart_form" enctype="multipart/form-data">
			<div class="kart-head">
				Upload Product Form:
			</div>
			User Information:
			<fieldset class="kart-fieldset">
				<div class="kart-field">
					<input type="text" id="first_name" name="first_name" placeholder="First Name" required="" maxlength="20">
				</div>
				<div class="kart-field">
					<input type="text" id="last_name" name="last_name" placeholder="Last Name" required="" maxlength="20">
				</div>
				<div class="kart-field">
					<input type="email" id="email" name="email" placeholder="Email" required="" maxlength="30">
				</div>
				<div class="kart-field">
					<input type="text" id="mobile" name="mobile" placeholder="Mobile" required="" maxlength="15">
				</div>
			
				<div class="kart-field">
					<input type="text" id="city" name="city" placeholder="City" required="" maxlength="30">
				</div>
				<div class="kart-field">
					<input type="text" id="state" name="state" placeholder="State" required="" maxlength="40">
				</div>
			</fieldset>
			Product Information:
			<fieldset class="kart-fieldset">
				<div class="kart-field">
					<input type="text" id="item_name" name="item_name" placeholder="Item Name" required="" maxlength="50">
				</div>
				
				<div class="kart-field">
					<select id="item_cat" name="item_cat" required="">
					<option value="">Item Category</option>
					<?php foreach( $product_categories as $cat ) { // if($cat->slug != 'second-hand-items')
							echo '<option value="'.$cat->slug.'">'.$cat->name.'</option>'; 
					} 
					?>
					</select>
				</div>
				<div class="kart-field">
					<textarea name="item_description" id="item_description" placeholder="Item Description (250  characters max.)" required="" maxlength="250" rows="4"></textarea>
				</div>
				<div class="kart-field">
					<input type="text" id="item_size" name="item_size" placeholder="Item Size" required="" maxlength="15">
				</div>
				<div class="kart-field">
					<input type="text" id="item_brand" name="item_brand" placeholder="Item Brand" required="" maxlength="30">
				</div>
				<div class="kart-field">
					<select id="item_colour" name="item_colour" required="">
						<option value="">Item Colour</option>
						<option value="Red" style="background:red">Red</option>
						<option value="Yellow" style="background:yellow">Yellow</option>
						<option value="Purple" style="background:purple">Purple</option>
						<option value="White" style="background:#fff;">White</option>
						<option value="Black" style="background:#000; color:#fff;">Black</option>
						<option value="Green" style="background:green">Green</option>
						<option value="Pink" style="background:pink">Pink</option>
						<option value="Brown" style="background:brown; color:#fff;">Brown</option>
						<option value="Grey" style="background:grey">Grey</option>
					</select>
				</div>
				<div class="kart-field">
					<input type="text" id="item_price" name="item_price" placeholder="Item Price" required="" maxlength="15">
				</div>
			</fieldset>
			<div class="kart-field kart-loading" style="display:none;">
				<img src="<?php echo plugins_url().'/kart-sell-items/images/loading.gif'; ?>"><br/>(Uploading)
			</div>
			<div class="kart-field">
				<button type="submit" id="kart-form-submit" class="button-primary">Add Product</button>
			</div>
		</form>
	</div>
	
	<div class="kart-form-div2" id="kart-form-div2" style="display:none;">
		<form id="kart-form2" name="kart_form2" action="" method="post" enctype="multipart/form-data">
			<div class="kart-head">
				Upload Images: <span>(Upload any 5 images of the product)</span>
			</div>
			<fieldset class="kart-fieldset">
				<div class="kart-field">
					<input type="file" name="file1" id="file1">
				</div>
				<div class="kart-field">
					<input type="file" name="file2">
				</div>
				<div class="kart-field">
					<input type="file" name="file3">
				</div>
				<div class="kart-field">
					<input type="file" name="file4">
				</div>
				<div class="kart-field">
					<input type="file" name="file5">
				</div>
			</fieldset>	
			<input type="hidden" id="postid" name="postid" value="">
			<div class="kart-field">
				<button type="submit" id="kart-upload-submit" class="button-primary">Start Upload</button>
			</div>
		</form>	
	</div>

<script type="text/javascript">
	$("#kart-form").submit(function(e){
	//$("#kart-form-submit").click(function(e){
		e.preventDefault();
		$("#kart-form-submit").attr("disabled","disabled");
		$(".kart-loading").show();
		$.post( "<?php echo plugins_url().'/kart-sell-items/ajax.php'; ?>", { 
			first_name: $("#first_name").val(), 
			last_name: $("#last_name").val(), 
			email: $("#email").val(), 
			mobile: $("#mobile").val(), 
			city: $("#city").val(), 
			state: $("#state").val(), 
			item_name: $("#item_name").val(), 
			item_cat: $("#item_cat").val(), 
			item_description: $("#item_description").val(), 
			item_size: $("#item_size").val(), 
			item_colour: $("#item_colour").val(), 
			item_brand: $("#item_brand").val(), 
			item_price: $("#item_price").val()
		})
		.done(function( data ) {
			if( data ){
				$("#kart-form-submit").removeAttr("disabled");
				$("#postid").val(data);
				$(".kart-form-div").hide();
				$(".kart-form-div2").show();
				$("html, body").animate({ scrollTop: 0 }, "slow");
				//alert("Please now select images for the product.");
			}
		});
	});
</script>

<?php
}

add_shortcode('kart_sell_items_form','main');