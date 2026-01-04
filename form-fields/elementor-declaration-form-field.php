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

		// Retrieve widths from controls
		$w1 = isset($item['col1_width']['size']) ? $item['col1_width']['size'] . $item['col1_width']['unit'] : '15%';
		$w2 = isset($item['col2_width']['size']) ? $item['col2_width']['size'] . $item['col2_width']['unit'] : '53%';
		$w3 = isset($item['col3_width']['size']) ? $item['col3_width']['size'] . $item['col3_width']['unit'] : '10%';
		$w4 = isset($item['col4_width']['size']) ? $item['col4_width']['size'] . $item['col4_width']['unit'] : '22%';

		$rows = isset($item['rows_number']) ? $item['rows_number'] : 3; 

		?>		
		<div class="e-form-declaration-wrapper">
			<table class="es-table-declaration-field" name="form_fields[<?php echo $id_base; ?>]">  
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
								oninput="setDate(event)"
								data-omschrijvingid="form-field-<?php echo $id_base ."-date-r" . $i ?>" 
								placeholder="<?php echo esc_attr( $item['col1_placeholder'] ); ?>" 
								<?php echo $item['required'] ? 'required' : ''; ?>
							>
						</td>
						<!-- Input 2: Text -->
						<td class="es-cell-declaration-field">
							<input 
								type="text" 
								name="form_fields[<?php echo $id_base; ?>][]" 
								id="form-field-<?php echo $id_base ."-date-r" . $i ?>" 
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
								oninput="calcKm(event)"
								data-mileageratio="<?php echo $item['km_ratio'];?>"
								placeholder="<?php echo esc_attr( $item['col3_placeholder'] ); ?>" 
								<?php echo $item['required'] ? 'required' : ''; ?>
							>
						</td>
						<!-- Input 4: Text -->
						<td class="es-cell-declaration-field">
							<input 
								type="text" 
								name="form_fields[<?php echo $id_base; ?>][]" 
								class="es-input-declaration-field es-input-amount-declaration-field"
								onkeyup="onKeySumUp(event)" 
								oninput="calculateTotal(event)"
								data-total="<?php  echo $item['label_name_total_field'];?>"
								placeholder="<?php echo esc_attr( $item['col4_placeholder'] ); ?>" 
								<?php echo $item['required'] ? 'required' : ''; ?>
							>
						</td>
					</tr>
				<?php endfor; ?>
			</table>
		</div>
		<?php
	}

	/**
	 * Validation Logic & Declaration Field should not use Required! 
	 * 
	 */
	public function validation( $field, $record, $ajax_handler ) {
		error_log("Declaration Form validation!" . $field['id']);
		error_log("Field: ". print_r($field, true));
		// if ( empty( $field['required'] ) ) {
		// 	return;
		// }

		$raw_values = $field['raw_value'];
		$rows = intdiv(count($raw_values),4);
		for($i = 0; $i < $rows; $i++) {
			error_log("Validating row:" . $i);
			if ( !empty($raw_values[ $i * $rows]) ){
				error_log("First cell is not empty:" . $raw_values[ $i * $rows]);
				if (empty($raw_values[ $i * $rows + 1])) {
					error_log("Second cell is empty:" . $raw_values[ $i * $rows + 1]);
					$ajax_handler->add_error( $field['id'] . "-date-r1", esc_html__( 'Omschrijving mag niet leeg zijn', 'elementor-declaration-field' ) );

					// $ajax_handler->add_error(
					// 	$field['id'],
					// 	esc_html__( 'IBAN nummer ongeldig.', 'elementor-form-IBAN-NL-field' )
					// );
				}
			}
		};
		// $values = $field['value']; 
		// if ( is_array( $values ) ) {
		// 	foreach ( $values as $v ) {
		// 		if ( empty( $v ) ) {
		// 			$ajax_handler->add_error( $field['id'], esc_html__( 'All 3 fields are required.', 'elementor-declaration-field' ) );
		// 			return;
		// 		}
		// 	}
		// }
		return;
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
				//'default' => 0.23,
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
				'default' => '0,00',
				'condition' => [ 'field_type' => $this->get_type() ],
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'label_name_total_field' =>
			[	'name' => 'label_name_total_field',
				'label' => esc_html__( 'Label name Total field', 'elementor-declaration-field' ),
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
				// console.log("Adding hook for 3 col template");

				elementor.hooks.addFilter(
					'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
					function ( inputField, item, i ) {
						const field1Type = 'date';
						const field1Class = "es-input-declaration-field";
						const field1Width = item['col1_width']['size'] + item['col1_width']['unit'];
						const field1Name = "form_fields[" + item['custom_id'] + "][]";
						const field1Placeholder = item['col1_placeholder'];
						const field2Type = 'text';
						const field2Class = "es-input-declaration-field";
						const field2Width = item['col2_width']['size'] + item['col2_width']['unit'];
						const field2Name = "form_fields[" + item['custom_id'] + "][]";
						const field2Placeholder = item['col2_placeholder'];
						const field3Type = 'number';
						const field3Class = "es-input-declaration-field es-input-km-declaration-field";
						const field3Width = item['col3_width']['size'] + item['col3_width']['unit'];
						const field3Name = "form_fields[" + item['custom_id'] + "][]";
						const field3MileageRatio =  item['km_ratio'];
						const field3Placeholder = item['col3_placeholder'];
						const field4Type = 'text';
						const field4Class = "es-input-declaration-field es-input-amount-declaration-field";
						const field4Width = item['col4_width']['size'] + item['col4_width']['unit'];
						const field4Name = "form_fields[" + item['custom_id'] + "][]";
						const field4Total = item['label_name_total_field'];
						const field4Placeholder = item['col4_placeholder'];
						const rows = item['rows_number'] == null ? 3 : item['rows_number']; 
						const total = 100;
						const ratio_value = item['km_ratio'] == null ? 0 :item['km_ratio'];
						
						var lines = [...Array(rows).keys()];

						return  `
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
								</tr>` +
								lines.map(function (row) {
									return `<tr>
												<td class="es-cell-declaration-field es-line-number-declaration-field">${row}</td>
												<td class="es-cell-declaration-field">
													<input name="${field1Name}" type="${field1Type}" class="${field1Class}" placeholder="${field1Placeholder}" required>
												</td>
												<td class="es-cell-declaration-field">
													<input name="${field2Name}" type="${field2Type}" class="${field2Class}" placeholder="${field2Placeholder}" required>
												</td>
												<td class="es-cell-declaration-field">
													<input name="${field3Name}" type="${field3Type}" class="${field3Class}" onkeyup="onKeyKmUp(event)" oninput="calcKm(event)" data-mileageratio="${field3MileageRatio}" placeholder="${field3Placeholder}" required>
												</td>
												<td class="es-cell-declaration-field">
													<input name="${field4Name}" type="${field4Type}" class="${field4Class}" onkeyup="onKeySumUp(event)" oninput="calculateTotal(event)" data-total="${field4Total}" placeholder="${field4Placeholder}" required>
												</td>
											</tr>`
								}).reduce(function(total, line) {
									return total + line;
								})
								+
							`</table>
						</div>` ;
					}, 10,3
				);
			});	
		</script>
		<?php
	}
}

function declaration_form_field_styles_and_scripts() {
	error_log("Register Styles & Scripts");
	wp_register_style('style-1', 
		plugins_url( 'assets/css/declaration_form_field.css', __DIR__ ), Array(), '1.15', false
	);
	wp_enqueue_style('style-1');
	wp_register_script( 'editor-script-1', 
		plugins_url( 'assets/js/declaration_form_field.js', __DIR__ ), Array(), '1.9', false
	);
	wp_enqueue_script( 'editor-script-1' );
}
add_action('wp_enqueue_scripts', 'declaration_form_field_styles_and_scripts');