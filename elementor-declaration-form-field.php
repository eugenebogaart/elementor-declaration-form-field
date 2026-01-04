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


