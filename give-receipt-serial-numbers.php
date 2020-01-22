<?php
/*
 * Plugin Name: Give - Sequential Serial Numbers
 * Plugin URI: https://givewp.com
 * Description: Additional functionality for GIVE.  Adds a hidden field to the front end of the form that counts each donation.
 * Author: WordImpress / Munter Westermann
 * Author URI: https://wordimpress.com
 * Version: 0.1
 * Text Domain: give
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/WordImpress/Give
 *
 * Adds a hidden field to the front end of the form that counts each donation.
 *
 */

add_action( 'give_after_donation_levels', 'give_myprefix_custom_form_fields', 10, 1 );

function give_myprefix_custom_form_fields( $form_id ) {

    //if no other Give Posts with sequential ID, it will start with 1
    $sequential_id = 1;

	//gets the most recent donation
    $args          = array(
		'numberposts' => 1,
		'post_type'   => 'give_payment'
	);
    $last_payment = get_posts( $args );

    //gets that donation's Post ID
    $last_payment_id = $last_payment[0]->ID;

    //if there is no last donation, save the database hit
	if ( $last_payment ) {

	    //get the payment meta of the last donation
		$last_payment_meta = give_get_payment_meta( $last_payment_id );
		
		//get the sequential id of that donation, if it has one.
		$last_sequential_id = $last_payment_meta['custom_transaction_id'];
		//var_dump( $last_sequential_id );
        //increment the sequential id number
		$sequential_id = $last_sequential_id + 1;
	}
	//uncomment the next line to verify on the front end of the form that the sequential ID is correct
	//var_dump( $sequential_id );

    //markup for the hidden field
	?>
    <div id="my-give-hidden-counter" style="display:none !important">
        <input type="hidden" name="custom_transaction_id" value="<?php echo $sequential_id ?>">
    </div>
	<?php
}

/*
 * Stores the value of that hidden field in the payment meta.
 */

//add_filter( 'give_payment_meta', 'give_myprefix_store_custom_fields' );



/*function give_myprefix_store_custom_fields( $payment_meta ) {
	$payment_meta['custom_transaction_id'] = isset( $_POST['custom_transaction_id'] ) ?  $_POST['custom_transaction_id'] : '';

	return $payment_meta;
}*/
add_action( 'give_insert_payment', 'give_myprefix_store_custom_fields', 10, 2 );
function give_myprefix_store_custom_fields( $payment_id, $payment_data ) {
	if ( isset( $_POST['custom_transaction_id'] ) ) {
		$message = wp_strip_all_tags( $_POST['custom_transaction_id'], true );
		add_post_meta( $payment_id, 'custom_transaction_id', $message );
	}
}


/*
 * Show Data in Donation Details
 *
 * @description show the custom field(s) on the Donation details page
 *
 *
 */

add_action( 'give_view_order_details_payment_meta_before', 'give_myprefix_purchase_details', 10, 2 );

function give_myprefix_purchase_details( $payment_id ) {
	$payment_meta = give_get_payment_meta( $payment_id );
	//Bounce out if no data for this transaction
	if ( ! isset( $payment_meta['custom_transaction_id'] ) ) {
		return;
	}

	?>
    <div class="give-order-custom-transaction-id give-admin-box-inside">

        <p class="my-custom-transaction-id">
            <strong><?php echo __( 'Sequential transaction ID:', 'give' ); ?></strong>
			<?php echo $payment_meta['custom_transaction_id'] ?>
        </p>
    </div>
<?php }

/*
 * Adds a custom email tag of {seq_id} for use on the donation confirmation email/PDF receipt
 */

function my_give_add_custom_transaction_id_tag( $payment_id ) {

	give_add_email_tag( 'seq_id', 'output the sequential ID', 'my_give_get_custom_transaction_tag_data' );
}

add_action( 'give_add_email_tags', 'my_give_add_custom_transaction_id_tag' );

/*function my_give_get_custom_transaction_tag_data( $payment_id ) {

	$postmeta = give_get_payment_meta( $payment_id );
	$output   = __( 'n/a', 'give' );

	if ( isset( $postmeta['custom_transaction_id'] ) && ! empty( $postmeta['custom_transaction_id'] ) ) {
		$output = $postmeta['custom_transaction_id'];
	}

	return $output;

}*/
function my_give_get_custom_transaction_tag_data( $tag_args ) {
	$wbhi_seq_id = get_post_meta( $tag_args['payment_id'], 'custom_transaction_id', true );
	$output = __( 'n/a', 'give' );
		if ( ! empty( $wbhi_seq_id ) ) {
			$output = wp_kses_post( $wbhi_seq_id );
		}
	return $output;
}
?>
