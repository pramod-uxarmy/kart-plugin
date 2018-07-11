<?php

include "./../../../wp-load.php";

$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email_address = $_POST['email'];
$mobile = $_POST['mobile'];
$city = $_POST['city'];
$state = $_POST['state'];

$user = get_user_by( 'login', $email_address );

if( !$user ) {

	// Generate the password and create the user
	$password = wp_generate_password( 12, false );
	$user_id = wp_create_user( $email_address, $password, $email_address );
	
	update_user_meta( $user_id, 'mobile', $mobile);
	update_user_meta( $user_id, 'city', $city);
	update_user_meta( $user_id, 'state', $state);
	update_user_meta( $user_id, 'first_name', $first_name);
	update_user_meta( $user_id, 'last_name', $last_name);
	
	// Set the nickname
	wp_update_user(
		array(
		  'ID'          =>    $user_id,
		  'display_name'	=>	  $first_name
		)
	);

	// Set the role
	$user = new WP_User( $user_id );
	$user->set_role( 'contributor' );

	// Email to the user
	$subject = "Product uploaded successfully and awaiting confirmation.";
	$message = "Dear User,\r\n\r\nYour product has been submitted and awaiting for admin approval.\r\nYour account also has been created on the site.\r\nUsername: $email_address \r\nPassword: $password \r\nThanks for choosing us.";
	wp_mail( $email_address, $subject, $message );
	add_product_kart($user_id);

} else {
	$user_id = $user->ID;
	update_user_meta( $user_id, 'mobile', $mobile);
	update_user_meta( $user_id, 'city', $city);
	update_user_meta( $user_id, 'state', $state);
	update_user_meta( $user_id, 'first_name', $first_name);
	update_user_meta( $user_id, 'last_name', $last_name);
	// Email the user
	$subject = "Product uploaded successfully and awaiting confirmation.";
	$message = "Dear User,\r\n\r\nYour item is in queue for approval. Product will be live on site once approved.\r\n\r\nThanks for choosing us";
	wp_mail( $email_address, $subject, $message );
	add_product_kart($user_id);
}

function add_product_kart($user_id){

	$item_name = $_POST['item_name'];
	$item_cat = $_POST['item_cat'];
	$item_description = $_POST['item_description'];
	$item_size = $_POST['item_size'];
	$item_colour = $_POST['item_colour'];
	$item_brand = $_POST['item_brand'];
	$item_price = $_POST['item_price'];

	$post = array(
		'post_author' => $user_id,
		'post_content' => $item_description,
		'post_status' => "draft",
		'post_title' => $item_name,
		'post_parent' => '',
		'post_type' => "product",
		);
	//Create post
	$post_id = wp_insert_post( $post, $wp_error );
	if($post_id){
		//$attach_id = get_post_meta($product->parent_id, "_thumbnail_id", true);
		//add_post_meta($post_id, '_thumbnail_id', $attach_id);
	}
    //wp_set_object_terms( $post_id, 'Races', 'product_cat' );
	//wp_set_object_terms ($post_id, 'variable', 'product_type');
	wp_set_object_terms( $post_id, 'simple', 'product_type');
	//wp_set_object_terms( $post_id, 'second-hand-items', 'product_cat');
	wp_set_object_terms( $post_id, $item_cat, 'product_cat');

	update_post_meta( $post_id, '_visibility', 'visible' );
	update_post_meta( $post_id, '_stock_status', 'instock');
	update_post_meta( $post_id, 'total_sales', '0');
	update_post_meta( $post_id, '_downloadable', 'yes');
	update_post_meta( $post_id, '_virtual', 'yes');
	update_post_meta( $post_id, '_regular_price', $item_price );
	update_post_meta( $post_id, '_sale_price', "" );
	update_post_meta( $post_id, '_purchase_note', "" );
	update_post_meta( $post_id, '_featured', "no" );
	update_post_meta( $post_id, '_weight', "" );
	update_post_meta( $post_id, '_length', "" );
	update_post_meta( $post_id, '_width', "" );
	update_post_meta( $post_id, '_height', "" );
	update_post_meta( $post_id, '_sku', "");
	
	$thedata = array(
		'colour'=> array(
			'name'=>'colour',
			'value'=> $item_colour,
			'is_visible' => '1',
			),
		'brand'=> array(
			'name'=>'brand',
			'value'=> $item_brand,
			'is_visible' => '1',
			),	
		'size'=> array(
			'name'=>'size',
			'value'=> $item_size,
			'is_visible' => '1',
			),
		);
	
	update_post_meta( $post_id, '_product_attributes', $thedata );
	update_post_meta( $post_id, '_sale_price_dates_from', "" );
	update_post_meta( $post_id, '_sale_price_dates_to', "" );
	update_post_meta( $post_id, '_price', "1" );
	update_post_meta( $post_id, '_sold_individually', "" );
	update_post_meta( $post_id, '_manage_stock', "no" );
	update_post_meta( $post_id, '_backorders', "no" );
	update_post_meta( $post_id, '_stock', "" );

	// file paths will be stored in an array keyed off md5(file path)
	//$downdloadArray =array('name'=>"Test", 'file' => $uploadDIR['baseurl']."/video/".$video);

	//$file_path =md5($uploadDIR['baseurl']."/video/".$video);

	//$_file_paths[  $file_path  ] = $downdloadArray;
	// grant permission to any newly added files on any existing orders for this product
	//do_action( 'woocommerce_process_product_file_download_paths', $post_id, 0, $downdloadArray );
	//update_post_meta( $post_id, '_downloadable_files ', $_file_paths);
	update_post_meta( $post_id, '_download_limit', '');
	update_post_meta( $post_id, '_download_expiry', '');
	update_post_meta( $post_id, '_download_type', '');
	update_post_meta( $post_id, '_product_image_gallery', '');

	$to = get_option('admin_email');
	$subject = "A Product awaiting confirmation";
	$message = "Dear Admin,\r\n\r\nNew product has been uploaded on site and awaiting confirmation to be live on site.\r\n\r\nThanks";
	wp_mail($to, $subject, $message);
	
	echo $post_id.'-'.$user_id;
	
}