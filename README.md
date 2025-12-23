# Costs reimbursement table with 4 columns for Wordpress Elementor Forms

This is custom build Table with 4 column with entry fields. The number of rows is controlled by a variable in Form control. 
At the moment limited is set to 7.

The first column is a date field
The second column is description
The third is mileage 
The fourth is amount

# Column 3, Mileage

The mileage in column 3 is multiplied by a Mileage_Ratio, abd injected in to the fourth column.
The  Mileage_Ratio is editable n the From controls.   

When colun 3 has a value greater then zero, the field in column 4 is made readonly to prevent 
manaual override. If the mileage in column 3 is set to zero or removed, then value in column 4 is
removed and the field becomes writeable again. 

After every change of value in column 4, the 'calculateTotal()' is triggered. 


# Column 4, Total

Any change in the column 4 fields will trigger calculateTotal(). In the Form control can be specified 
in which field the sum needs to be injected. The sum field becomes readonly to prevent manual override.

The fields in column 4 have hard coded nl_NL decimal seperator. 
Also deny all input in the last column which not a number.
	

# Mapping Rows and Colmns a clean submission.

Many actions after submission require all fields be one dimensional (can not handle Arrays) and the fields need to be defined at design time.  Fields created on the fly are often not recoqnized. So e.g E2PDF needs to connect the designed form to a PDF template and ignores any on the fly created fields. 

In order to overcome this, one need to create hidden fields at design time for every row and column. The naming of these fields should be something like:    Your-form-field-name_r0_c0   (times $row * $col)


If you want to name you hidden fields differently then you need to edit the function below.  This function hooks into the $ajax_handler 
and copies the values form this Form Field into the hidden fields


```php
function declaration_form_processing( $record, $ajax_handler ) {
    // 1. Get all submitted fields from the record
    $raw_fields = $record->get( 'fields' );

    $update_required = false;

	$nrcols = 4;

    // 2. Loop through fields to find type 'MyField'
    foreach ( $raw_fields as $id => $field ) {
        if ( 'declaration_row' === $field['type'] ) {
                // 3. Get the raw array of strings of everything in the table.
                $raw_data = $field['raw_value'];

                $size = count($raw_data);

                if ($size  % $nrcols != 0 ) {
                        error_log('Field Processing error, number of fields not a multitude of '. $nrcols. ':' .  $size); 
                } else {
                         // 4. Iterate over all rows and cols
                        for ($row = 0; $row < intdiv($size, $nrcols); $row++) {
                                for ($col =  0; $col < $nrcols; $col++ ) {

                                        $id = $field['id'] . '_r' . $row . '_c' . $col;

                                        $newf = Array('id' => $id , 'type' => 'text', 'value' => $raw_data[$row* $nrcols + $col], 'raw_value' => $raw_data[$row* $nrcols + $col]);
                                        $raw_fields[$id] = $newf;
                                };
                                $update_required = true;
                        };
                }
        }
    }
    
    // 5. Save the modified fields back to the record if changes were made
    if ( $update_required ) {
        $record->set( 'fields', $raw_fields );
    }
    return $record;
}
```

