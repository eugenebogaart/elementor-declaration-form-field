<?php
/**
 * Plugin Name: Elementor Forms - Declaration / Reimbursment 
 * Description: Custom Form Field with Date, Text, and 2 Numbers inputs in one row with adjustable widths.
 * Version: 2.0
 * Author: Custom Generator on request
 * Text Domain: elementor-declaration-field
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Elementor_Declaration_Form_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base {

	public function get_type() {
		return 'declaration_row';
	}

	public function get_name() {
		return esc_html__( 'Declaratie', 'elementor-declaration-field' );
	}

	/**
	 * 1. FRONTEND RENDER (PHP)
	 */
	public function render( $item, $item_index, $form ) {
		$id_base = $item['custom_id'];

		//error_log("Rendering 'Row (Date, Text, Number)" . print_r($form, true));

		// Retrieve widths from controls
		$w1 = isset($item['col1_width']['size']) ? $item['col1_width']['size'] . $item['col1_width']['unit'] : '15%';
		$w2 = isset($item['col2_width']['size']) ? $item['col2_width']['size'] . $item['col2_width']['unit'] : '53%';
		$w3 = isset($item['col3_width']['size']) ? $item['col3_width']['size'] . $item['col3_width']['unit'] : '10%';
		$w4 = isset($item['col3_width']['size']) ? $item['col3_width']['size'] . $item['col3_width']['unit'] : '22%';

		$rows = isset($item['rows_number']) ? $item['rows_number'] : 3; 

		?>		
		<div class="e-form-declaration-wrapper">
			<table class="es-table-declaration-field">   <!-- <div class="e-form-declaration-wrapper"> -->
				<thead>
					<tr>
						<!-- Applied width classes here -->
						<th class="es-header-declaration-field es-w-5-declaration-field">nr.</th>
						<th class="es-header-declaration-field es-w-22-declaration-field"
							style="width: <?php echo esc_attr($w1); ?>;">Datum</th>
						<th class="es-header-declaration-field es-w-53-declaration-field"
							style="width: <?php echo esc_attr($w2); ?>;">Omschrijving</th>
						<th class="es-header-declaration-field es-w-20-declaration-field"
							style="width: <?php echo esc_attr($w3); ?>;">Aantal km&ast;</th>
						<th class="es-header-declaration-field es-w-20-declaration-field"
							style="width: <?php echo esc_attr($w4); ?>;">Bedrag (€)</th>
						</tr>
				</thead>
				<!-- Input 1: Date -->
				
				<?php for($i = 1; $i <= $rows; $i += 1):
					?>
					<tr>
						<td class="es-cell-declaration-field es-line-number-declaration-field"><?php echo "$i" ?></td>
						<td class="es-cell-declaration-field">
							<input 
								type="date" 
								name="form_fields[<?php echo $id_base; ?>][]" 
								class="es-input-declaration-field"
								placeholder="<?php echo esc_attr( $item['col1_placeholder'] ); ?>" 
								<?php echo $item['required'] ? 'required' : ''; ?>
							>
						</td>
						<!-- Input 2: Text -->
						<td class="es-cell-declaration-field">
							<input 
								type="text" 
								name="form_fields[<?php echo $id_base; ?>][]" 
								class="es-input-declaration-field"
								placeholder="<?php echo esc_attr( $item['col2_placeholder'] ); ?>" 
								<?php echo $item['required'] ? 'required' : ''; ?>
							>
						</td>
						<!-- Input 3: Number -->
						<td class="es-cell-declaration-field">
							<input 
								type="number" 
								name="form_fields[<?php echo $id_base; ?>][]" 
								class="es-input-declaration-field es-input-km-declaration-field"
								onkeyup="onKeyKmUp(event)" 
								placeholder="<?php echo esc_attr( $item['col3_placeholder'] ); ?>" 
								<?php echo $item['required'] ? 'required' : ''; ?>
							>
						</td>
						<!-- Input 3: Number -->
						<td class="es-cell-declaration-field">
							<input 
								type="text" 
								name="form_fields[<?php echo $id_base; ?>][]" 
								class="es-input-declaration-field es-input-amount-declaration-field"
								onkeyup="onKeySumUp(event)" 
								placeholder="<?php echo esc_attr( $item['col4_placeholder'] ); ?>" 
								<?php echo $item['required'] ? 'required' : ''; ?>
							>
						</td>
					</tr>
				<?php endfor; ?>
			</table>
		</div>
		<?php /** $this->totalField($id_base);*/ ?>
		<?php $this->sumTotaal($item['field_id_total']); ?>
		<?php $this->calcKm($item['km_ratio']); ?>
		<?php
	}

	function totalField($id_base) { 
		?>
			<div class="e-form-declaration-wrapper-right">	
				<div class="es-total-container-declaration-field">
					<input type="hidden" id="total-hidden-declaration-field" name="form_fields[<?php echo $id_base; ?>][]"> Totaal: € <span id="total-display-declaration-field">0,00</span>
				</div>
			</div>
		<?php
	}

	/**
	 * Calculate the sum of the last column with amount. Deny all input in the last column which not a number.
	 * Also accept only the nl_NL decimal seperator.
	 * 
	 * The sum value will be displayed in field that is configured in Form Control:  'field_id_total'. 
	 * The sum field becomes readonly to prevent manual override.
	 */
	function sumTotaal($id_totaal) {
		$total_field = 'form-field-' . $id_totaal;
		?>
		<script>
				const onKeySumUp = event => {
   					 event.target.value = event.target.value.replace(/[^,0-9+]/g, '')
				}

				const amountInputs = document.querySelectorAll('.es-input-amount-declaration-field');
				// const totalDisplay = document.getElementById('total-display-declaration-field');
				// const totalHidden  = document.getElementById('total-hidden-declaration-field');

				const dutchFormatter = new Intl.NumberFormat('nl-NL', {
					minimumFractionDigits: 2,
					maximumFractionDigits: 2,
				});

				function calculateTotal() {
					const totalField  = document.getElementById('<?php echo $total_field; ?>');
					totalField.readOnly = true;

					let sum = 0;
					
					amountInputs.forEach(input => {
						let val = input.value;
						// Dutch format: remove thousands separator (.), replace decimal comma (,) with dot (.)
						val = val.replace(/\./g, '').replace(',', '.');
						
						const numberValue = parseFloat(val);

						if (!isNaN(numberValue)) {
							sum += numberValue;
						}
					});
					// totalDisplay.textContent = dutchFormatter.format(sum);
					//console.log("Hidden value before: ", totalHidden.value);
					// totalHidden.value = dutchFormatter.format(sum);
					//console.log("Hidden value after: ", totalHidden.value);
					totalField.value = dutchFormatter.format(sum);
				}
				amountInputs.forEach(input => {
					input.addEventListener('input', calculateTotal);
				});
		</script>
		<?php
	}

	/** 
	 *  NUmbers in the 3rd column are mileage. The mileage is multiplied by Mileage_Ratio
	 *  and injected in the same row 4th column.
	 *  At that time the field in column 4 is made readonly to prevent manaual override of 
	 *  Mileage_Ratio. The Mileage_Ratio can be set in the FormField controls.
	 *  If the mileage in column 3 is set to zero or removed, then value in column 4 is
	 *  removed and the field becomes writeable again. 
	 * 
	 *  After every change of value in column 4, the 'calculateTotal()' is triggered. 
	 */
	function calcKm($mileage_rate) {
		$ratio_value = isset($mileage_rate) ? $mileage_rate : -1; 
		?>
		<script>
			const onKeyKmUp = event => {
   					 event.target.value = event.target.value.replace(/[^,0-9+]/g, '')
				}

			const kmInputs = document.querySelectorAll('.es-input-km-declaration-field');
			const amountOutputs = document.querySelectorAll('.es-input-amount-declaration-field');

			function calAndMap(item, index, arr) {
				console.log("Running calAndMap per field for:", typeof item.value, 'value:', item.value );
				if ( item.value != '' &&  item.value > 0) {
					// console.log("Assigning calculation");
  					amountOutputs[index].value = dutchFormatter.format(item.value * <?php echo $ratio_value; ?>);
					amountOutputs[index].readOnly = true;
					calculateTotal();
				} else if ( item.value == 0) {
					// console.log("Skipping calculation, value is", item.value);
					amountOutputs[index].value = dutchFormatter.format(0);
					amountOutputs[index].readOnly = false;
					calculateTotal();
				} else {
					// console.log("Skipping calculation, no value", item.value);
					amountOutputs[index].value = dutchFormatter.format(0);
					amountOutputs[index].readOnly = false;
					calculateTotal();
					
				}
			}

			function calcKm() {
				kmInputs.forEach(calAndMap);
				calculateTotal();
			}

			kmInputs.forEach(input => {
					input.addEventListener('input', calcKm);
			});
		</script>
		<?php
	}

	/**
	 * Validation Logic
	 */
	public function validation( $field, $record, $ajax_handler ) {
		if ( empty( $field['required'] ) ) {
			return;
		}
		$values = $field['value']; 
		if ( is_array( $values ) ) {
			foreach ( $values as $v ) {
				if ( empty( $v ) ) {
					$ajax_handler->add_error( $field['id'], esc_html__( 'All 3 fields are required.', 'elementor-declaration-field' ) );
					return;
				}
			}
		}
	}

	/**
	 * Define settings (Widths and Placeholders)
	 */
	public function update_controls( $widget ) {
		$elementor = \Elementor\Plugin::instance();

		$control_data = $elementor->controls_manager->get_control_from_stack( $widget->get_unique_name(), 'form_fields' );

		if ( is_wp_error( $control_data ) ) {
			return;
		}

		// --- Column 1 (Date) ---
		$field_controls = [
			'rows_number' =>
			[	'name' => 'rows_number',
				'label' => esc_html__( 'Number of rows', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'condition' => [ 'field_type' => $this->get_type() ],
				'min' => 1,
				'max' => 7,
				'placeholder' => 3
			],
			'col1_heading' =>
			[	'name' => 'col1_heading',
				'label' => esc_html__( 'Field 1 (Date)', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'condition' => [ 'field_type' => $this->get_type() ],
				'separator' => 'before', 
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'col1_width' =>
			[	'name' => 'col1_width',
				'label' => esc_html__( 'Width', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range' => [
					'%' => [ 'min' => 5, 'max' => 100 ],
				],
				'default' => [
					'unit' => '%',
					'size' => 15,
				],
				'condition' => [ 'field_type' => $this->get_type() ],
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'col1_placeholder' =>
			[	'name' => 'col1_placeholder',
				'label' => esc_html__( 'Placeholder', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'Date',
				'condition' => [ 'field_type' => $this->get_type() ],
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'col2_heading' =>
			[	'name' => 'col2_heading',
				'label' => esc_html__( 'Field 2 (Text)', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'condition' => [ 'field_type' => $this->get_type() ],
				'separator' => 'before', 
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'col2_width' =>
			[	'name' => 'col2_width',
				'label' => esc_html__( 'Width', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range' => [
					'%' => [ 'min' => 5, 'max' => 100 ],
				],
				'default' => [
					'unit' => '%',
					'size' => 53,
				],
				'condition' => [ 'field_type' => $this->get_type() ],
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'col2_placeholder' =>
			[	'name' => 'col2_placeholder',
				'label' => esc_html__( 'Placeholder', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'Description',
				'condition' => [ 'field_type' => $this->get_type() ],
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			], 
			'col3_heading' =>
			[	'name' => 'col3_heading',
				'label' => esc_html__( 'Field 3 (Number)', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'condition' => [ 'field_type' => $this->get_type() ],
				'separator' => 'before', 
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			], 
			'col3_width' =>
			[	'name' => 'col3_width',
				'label' => esc_html__( 'Width', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range' => [
					'%' => [ 'min' => 5, 'max' => 100 ],
				],
				'default' => [
					'unit' => '%',
					'size' => 10,
				],
				'condition' => [ 'field_type' => $this->get_type() ],
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'col3_placeholder' =>
			[	'name' => 'col3_placeholder',
				'label' => esc_html__( 'Placeholder', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '0',
				'condition' => [ 'field_type' => $this->get_type() ],
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'km_ratio' =>
			[	'name' => 'km_ratio',
				'label' => esc_html__( 'Euro per KM', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'condition' => [ 'field_type' => $this->get_type() ],
				'default' => 0.23,
			],
			'col4_heading' =>
			[	'name' => 'col4_heading',
				'label' => esc_html__( 'Field 4 (Number)', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'condition' => [ 'field_type' => $this->get_type() ],
				'separator' => 'before', 
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			], 
			'col4_width' =>
			[	'name' => 'col4_width',
				'label' => esc_html__( 'Width', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range' => [
					'%' => [ 'min' => 5, 'max' => 100 ],
				],
				'default' => [
					'unit' => '%',
					'size' => 22,
				],
				'condition' => [ 'field_type' => $this->get_type() ],
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'col4_placeholder' =>
			[	'name' => 'col4_placeholder',
				'label' => esc_html__( 'Placeholder', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '0',
				'condition' => [ 'field_type' => $this->get_type() ],
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'field_id_total' =>
			[	'name' => 'field_id_total',
				'label' => esc_html__( 'ID Total field', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'condition' => [ 'field_type' => $this->get_type() ],
			],
			'subfields' =>  // We will fill this programmatically
        	[
				'name' => 'subfields',
				'label' =>  esc_html__( 'Subfields', 'elementor-declaration-field' ),
				'type' => \Elementor\Controls_Manager::HIDDEN, // Hide from user, not vsible in UI.	
				'default' => '', 
				'condition' => [ 'field_type' => $this->get_type() ],
        	]
		];

		$control_data['fields'] = $this->inject_field_controls( $control_data['fields'], $field_controls );

		$widget->update_control( 'form_fields', $control_data );
	}

	/**
	 * Field constructor.
	 *
	 * Used to add a script to the Elementor editor preview.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'elementor/preview/init', [ $this, 'editor_preview_footer' ] );
	}

	/**
	 * Elementor editor preview.
	 *
	 * Add a script to the footer of the editor preview screen.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function editor_preview_footer(): void {
		add_action( 'wp_footer', [ $this, 'content_template_script' ] );
	}

	/**
	 * Elementor editor content template.
	 *
	 * Used for live preview inside the Elementor editor.
	 *
	 * @return void
	 */
	public function content_template_script() {
		?>
		<script type="text/javascript">
			
			jQuery( document) .ready( () => {
				console.log("Adding hook for 3 col template");

				elementor.hooks.addFilter(
					'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
					function ( inputField, item, i ) {
						const field1Type = 'date';
						const field1Class = "elementor-field elementor-field-textual e-col-1";
						const field1Width = item['col1_width']['size'] + item['col1_width']['unit'];
						const field1Name = item['declaration_row'];
						const field1Placeholder = item['col1_placeholder'];
						const field2Type = 'text';
						const field2Class = "elementor-field elementor-field-textual e-col-2";
						const field2Width = item['col2_width']['size'] + item['col2_width']['unit'];
						const field2Name = item['declaration_row'];
						const field2Placeholder = item['col2_placeholder'];
						const field3Type = 'number';
						const field3Class = "elementor-field elementor-field-textual e-col-3";
						const field3Width = item['col3_width']['size'] + item['col3_width']['unit'];
						const field3Name = item['declaration_row'];
						const field3Placeholder = item['col3_placeholder'];
						const field4Type = 'number';
						const field4Class = "elementor-field elementor-field-textual e-col-3";
						const field4Width = item['col4_width']['size'] + item['col4_width']['unit'];
						const field4Name = item['declaration_row'];
						const field4Placeholder = item['col4_placeholder'];
						const rows = item['rows_number']; 
						const total = 100;

						var tablerows = ``
						for (let i = 1; i <= rows; i++) {
								tablerows= tablerows + `
									<tr>
										<td class="es-cell-declaration-field es-line-number-declaration-field">${i}</td>
										<td class="es-cell-declaration-field">
											<input type="${field1Type}" class="${field1Class}" placeholder="${field1Placeholder}" required>
										</td>
										<td class="es-cell-declaration-field">
											<input type="${field2Type}" class="${field2Class}" placeholder="${field2Placeholder}" required>
										</td>
										<td class="es-cell-declaration-field">
											<input type="${field3Type}" class="${field3Class}" placeholder="${field3Placeholder}" required>
										</td>
										<td class="es-cell-declaration-field">
											<input type="${field4Type}" class="${field4Class}" placeholder="${field4Placeholder}" required>
										</td>
									</tr>`;
						}
						return `
						<div class="e-form-declaration-wrapper">
							<table class="es-table-declaration-field"> 
								<tr>
									<th class="es-header-declaration-field es-w-5-declaration-field">nr.</th>
									<th class="es-header-declaration-field es-w-22-declaration-field"
											style="width: ${field1Width};">Datum</th>
									<th class="es-header-declaration-field es-w-53-declaration-field"
											style="width: ${field2Width};">Omschrijving</th>
									<th class="es-header-declaration-field es-w-20-declaration-field"
											style="width: ${field3Width};">Aantal km&ast;</th>
									<th class="es-header-declaration-field es-w-20-declaration-field"
											style="width: ${field4Width};">Bedrag (€)</th>
								</tr>
								` 
								+ tablerows +
							`</table>
						</div>
						<div class="e-form-declaration-wrapper-right">	
							<div class="es-total-container-declaration-field">
								Totaal: € <span id="total-display-declaration-field">${total}</span>
							</div>
						</div>`;
					}, 10,3
				);
			});

		</script>
		<?php
	}


	// /**
	//  * Data Processing (Flatten array to string)
	//  */
	// public function process_field( $field, \ElementorPro\Modules\Forms\Classes\Form_Record $record, \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler ) {

	// 	$raw_value = $field['value'];
	// 	if ( is_array( $raw_value ) ) {
	// 		$sanitized_values = array_map( 'sanitize_text_field', $raw_value );
	// 		$field['value'] = implode( ' | ', $sanitized_values );
	// 	}
	// 	$record->update_field( $field['id'], 'value', $field['value'] );
	// }
}

/**
 * Load Styles for Frontend and Editor
 */
function elementor_declaration_field_styles() {
	?>
	<style>
		.e-form-declaration-wrapper {
			display: flex;
			flex-wrap: nowrap; /* Keep on one line */
			gap: 5px; /* Small gap between fields */
			width: 100%;
			box-sizing: border-box;
		}

		.es-table-declaration-field  {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 20px;
			table-layout: fixed; /* Ensures percentages are respected strictly */
		}	

		/* Column Width Classes */
    	.es-w-5-declaration-field  { width: 5%; }
    	.es-w-22-declaration-field { width: 22%; }
    	.es-w-53-declaration-field { width: 53%; }
    	.es-w-20-declaration-field { width: 20%; }

		.es-table-declaration-field {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 20px;
			table-layout: fixed; /* Ensures percentages are respected strictly */
    	}

		.es-header-declaration-field, 
    	.es-cell-declaration-field {
			border: 1px solid #999;
			padding: 0;
			margin: 0;
			vertical-align: middle;
   	 	}


		.es-input-declaration-field  {
			width: 100%;
			
			/* 
			* FIX FOR HEIGHTS: 
			* Setting a specific pixel height ensures the Date input 
			* is exactly the same height as the Text input.
			*/
			height: 36px; 
			line-height: 36px;

			display: block;
			margin: 0;
			padding: 0 6px; /* Horizontal padding only */
			
			border: none;
			border-radius: 0;
			outline: none;
			box-sizing: border-box;

			background-color: #e6f7ff;
			color: #000;
			font-size: 14px;
			font-family: inherit;
			padding: .25rem 0.25rem;
		}

		/* Remove default webkit appearance for date inputs to ensure height matches text */
		input[type="date"].es-input-declaration-field  {
			-webkit-appearance: none;
			appearance: none;
			/* Re-apply flex centering for date internals if needed by browser */
			display: flex; 
			align-items: center; 
			padding: .25rem 0.25rem;
		}

		.es-input-declaration-field :focus {
			background-color: #fff;
			box-shadow: inset 0 0 0 2px #007BFF;
			/* padding: 8px 4px; */
			padding: .25rem 0.25rem;
		}

		/* Specific Cell Styles */
		.es-line-number-declaration-field {
			text-align: center;
			background-color: #f9f9f9;
			font-size: 14px;
		}

		.es-input-amount-declaration-field {
			text-align: right;
			padding: .25rem 0.25rem;
		}

		.e-form-declaration-wrapper-right {
			display: flex;
			flex-direction: row;
			justify-content: right;
			flex-wrap: nowrap; /* Keep on one line */
			gap: 5px; /* Small gap between fields */
			width: 100%;
			box-sizing: border-box;
			padding: .25rem 0.25rem;
		}

		/* Total Section */
		.es-total-container-declaration-field {
			text-align: right;
			font-size: 1.2em;
			font-weight: bold;
			padding: 10px;
			background-color: #e9ecef;
			border-radius: 4px;
			border: 1px solid #ddd;
			color: #333;
			display: flex;
		}
		
		/* Default stacking for mobile */
		@media (max-width: 767px) {
			.e-form-declaration-wrapper {
				flex-direction: column;
			}
			.e-form-declaration-wrapper input {
				width: 100% !important;
				margin-bottom: 10px;
			}
		}
	</style>
	<?php
}

add_action( 'wp_enqueue_scripts', 'elementor_declaration_field_styles' );
add_action( 'elementor/editor/after_enqueue_styles', 'elementor_declaration_field_styles' );