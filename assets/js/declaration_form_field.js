const onKeySumUp = event => {
        event.target.value = event.target.value.replace(/[^,0-9+]/g, '');
};

const onKeyKmUp = event => {
   		event.target.value = event.target.value.replace(/[^,0-9+]/g, '');
};


// If date is set then "Omachrijving is required"
function setDate(evt) {
    const id = evt.currentTarget.dataset.omschrijvingid;
    console.log("Found id: ", id);
    const om = document.getElementById(id);
    console.log("Omschrijving: ", om);
    if (evt.currentTarget.value)  {
        om.required = true;
    } else {
        om.required = false;
    }
}

const dutchFormatter = new Intl.NumberFormat('nl-NL', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
});

function calcKm(evt) {
    const ratio = evt.currentTarget.dataset.mileageratio;
    const kmInputs = document.querySelectorAll('.es-input-km-declaration-field');
	const amountOutputs = document.querySelectorAll('.es-input-amount-declaration-field');

    kmInputs.forEach((element, index ) => {
        calAndMap(element, ratio, amountOutputs[index]);
    });
}

function calAndMap(item, ratio, amountOutput ) {
    if ( item.value != '' &&  item.value > 0) {
        //console.log("Assigning calculation");
        amountOutput.value = dutchFormatter.format(item.value * ratio);
        amountOutput.readOnly = true;
    } else if ( item.value == '') {
        //console.log("Skipping calculation, value is empty", item.value);
        //amountOutput.value = dutchFormatter.format(0);
        amountOutput.readOnly = false;
    } else if ( item.value == 0 ){
        //console.log("Skipping calculation, value is zero?", item.value);
        amountOutput.value = dutchFormatter.format(0);
        amountOutput.readOnly = false;
    } else {
        //console.log("Skipping calculation, value is other?", item.value);
        amountOutput.value = dutchFormatter.format(0);
        amountOutput.readOnly = false;
    }
    amountOutput.dispatchEvent(new Event('input'));	
}

/*
 * The Total field is referenced by it Label value because ID and Name
 * have different values while deployed and in the Form Builder.
 */
function findTotalFieldId(labelName) {
    // console.log("Looking for field with name", labelName);
    const someLabels = document.querySelectorAll('label');

    // console.log("Found several labels, count:", someLabels.length);
    var totalId = '';

    someLabels.forEach( elem => {
        // console.log("Checking label,", elem.htmlFor);
        if (elem.innerText == labelName) {
            // alert(elem.htmlFor);
            totalId = elem.htmlFor;
        }
    });
    if (totalId == '') {
        console.log("findTotalFieldId Not Found", labelName);
    };
    return totalId;
}


function calculateTotal(evt) {	
    const amountInputs = document.querySelectorAll('.es-input-amount-declaration-field');	
    // console.log("Event: ", evt);
    // console.log("Event currentTarget: ", evt.currentTarget);
    // console.log("Event dataset: ", evt.currentTarget.dataset.total);

    const totalFieldId  = findTotalFieldId(evt.currentTarget.dataset.total);
    const totalField  = document.getElementById(totalFieldId);
    // console.log("TotalField: ", totalField);
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
    
    totalField.value = dutchFormatter.format(sum);
}
console.log("Declaration form Field Javascript file loaded.");