<?php
/**
 * Plugin Name: Elementor Declaration Form field
 * Description: Custom addon developed on request
 * Version:     1.0.0
 * Author:      EB
 * Author URI:  https://developers.elementor.com/
 * Text Domain: elementor-declaration-form-field
 *
 * Requires Plugins: elementor
 * Elementor tested up to: 3.25.0
 * Elementor Pro tested up to: 3.25.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register `credit-card-number` field-type to Elementor form widget.
 *
 * @since 1.0.0
 * @param \ElementorPro\Modules\Forms\Registrars\Form_Fields_Registrar $form_fields_registrar
 * @return void
 */
function add_new_declaration_form_field( $form_fields_registrar ) {

	require_once( __DIR__ . '/form-fields/elementor-declaration-form-field.php' );

	$form_fields_registrar->register( new \Elementor_Declaration_Form_Field() );

}
add_action( 'elementor_pro/forms/fields/register', 'add_new_declaration_form_field' );


// function add_new_declaration_form_subfield( $form_fields_registrar ) {

// 	require_once( __DIR__ . '/form-fields/elementor-declaration-form-field.php' );

// 	$form_fields_registrar->register( new \Elementor_Declaration_Form_SubField() );

// }
// add_action( 'elementor_pro/forms/fields/register', 'add_new_declaration_form_subfield' );

/**
 * EB: Post processing submit of a specific Form 
 */

function declaration_form_processing( $record, $ajax_handler ) {
    // 1. Get all submitted fields from the record
    $raw_fields = $record->get( 'fields' );
    // $send_data = $record->get('sent_data');

    $update_required = false;

	$nrcols = 4;

    //     error_log("New Record" . print_r($raw_fields, true));

    // 2. Loop through fields to find type 'MyField'
    foreach ( $raw_fields as $id => $field ) {
        if ( 'declaration_row' === $field['type'] ) {
                error_log( 'Field Processing:' .  $field['type']);

                // Get the raw array of strings
                $raw_data = $field['raw_value'];

                $size = count($raw_data);

                if ($size  % $nrcols != 0 ) {
                        error_log('Field Processing error, number of fields not a multitude of '. $nrcols. ':' .  $size); 
                } else {

                        for ($row = 0; $row < intdiv($size, $nrcols); $row++) {
                                for ($col =  0; $col < $nrcols; $col++ ) {

                                        $id = $field['id'] . '_r' . $row . '_c' . $col;

                                        $newf = Array('id' => $id , 'type' => 'text', 'value' => $raw_data[$row* $nrcols + $col], 'raw_value' => $raw_data[$row* $nrcols + $col]);
                                        $raw_fields[$id] = $newf;
                                        // $send_data[$id] = $raw_data[$row* $nrcols + $col];
                                };
                                $update_required = true;
                        };
                }
        }
    }
    
    // 5. Save the modified fields back to the record if changes were made
    if ( $update_required ) {
        $record->set( 'fields', $raw_fields );
        // $record->set( 'sent_data', $send_data);
    }

//     error_log("New Record" . print_r($record->get('fields'), true));
    return $record;
}


add_action( 'elementor_pro/forms/record/actions_before', 'declaration_form_processing', 10, 2 ); 