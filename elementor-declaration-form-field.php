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

// Step 1: Capture Elementor Form submission data globally
// This hook ensures our shortcode can access the form data during email processing.
add_action('elementor_pro/forms/record/actions_before', function($record, $handler) {
    // Store formatted data (often comma-separated for multi-selects)
    $GLOBALS['elementor_form_submission_data'] = $record->get('fields');

	return $record;
}, 10, 2);


// Step 2: Custom Shortcode to handle array fields by index or loop
function elementor_form_indexed_array_field_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'id' => '',          // The Field ID from Elementor (e.g., "my_checkbox_field")
            'index' => null,     // Specific index (0-based) to retrieve. If null, loops through all.
            'tag' => 'li',       // HTML tag for each item when looping (e.g., 'li', 'p', 'span')
            'wrapper_tag' => 'ul', // HTML wrapper tag when looping (e.g., 'ul', 'ol', 'div')
            'separator' => '',   // Separator for items when looping and no tags are used (e.g., ', ' or '<br>')
            'display_empty' => 'false', // 'true' to show wrapper even if no values when looping
            'fallback' => '',    // Text to display if the index is not found or no values
        ),
        $atts,
        'array_index'
    );

    $field_id = $atts['id'];
	
    $requested_index = (isset($atts['index']) && is_numeric($atts['index'])) ? (int)$atts['index'] : null;
    $output = '';

    // Ensure we have submission data
    if (!isset($GLOBALS['elementor_form_submission_data'])) {
        return $atts['fallback']; // No submission data available
    }

    $submission_data = $GLOBALS['elementor_form_submission_data'];
	
    // Check if the specific field exists in the submission
    if (!isset($submission_data[$field_id])) {
        return $atts['fallback']; // Field not found in submission
    }

    $field_value_array = $submission_data[$field_id];
	
	$values = $field_value_array['raw_value'];
	// Check if the field value is an Array
	if (!is_array($field_value_array)) {
		 return $atts['fallback']; // Field not found in submission
	}

    // --- Handle retrieval by index ---
    if ($requested_index !== null) {
        if (isset($values[$requested_index])) {
            $output = esc_html($values[$requested_index]);
        } else {
            $output = $atts['fallback']; // Index not found
        }
    }
    // --- Handle looping through all values ---
    else {
        if (!empty($values) || $atts['display_empty'] === 'true') {
            if (!empty($atts['wrapper_tag'])) {
                $output .= '<' . esc_attr($atts['wrapper_tag']) . '>';
            }

            foreach ($values as $value) {
                if (!empty($atts['tag'])) {
                    $output .= '<' . esc_attr($atts['tag']) . '>' . esc_html($value) . '</' . esc_attr($atts['tag']) . '>';
                } else {
                    $output .= esc_html($value) . $atts['separator'];
                }
            }

            if (!empty($atts['wrapper_tag'])) {
                $output .= '</' . esc_attr($atts['wrapper_tag']) . '>';
            } else if (!empty($atts['separator'])) {
                // Remove trailing separator if no wrapper tag
                $output = rtrim($output, $atts['separator']);
            }
        } else {
             $output = $atts['fallback']; // No values to loop and not displaying empty
        }
    }

    return $output;
}
add_shortcode('array_index', 'elementor_form_indexed_array_field_shortcode');

// Step 3: Clean up the global variable after mail is sent
//         Or after the last Action executed in your workflow
//         In our case we use E2PDF which is fired from mail via [e2pdf-attachment id="9"]
add_action('elementor_pro/forms/send_mail', function() {
    if (isset($GLOBALS['elementor_form_submission_data'])) {
        unset($GLOBALS['elementor_form_submission_data']);
    }
});