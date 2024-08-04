import { __ } from '@wordpress/i18n';

/**
 * Takes a numeric input, intended to manage a time interval expressed as a number of 
 * seconds, and 'replaces' it with an easier-to-understand combination of inputs that
 * allow the interval to be expressed in additional units like minutes, hours and days.
 */
class IntervalPicker {
	static manage( inputElement ) {
		const picker = new IntervalPicker( inputElement );
		picker.setup();
	}

	constructor( inputElement ) {
		this.baseInput    = inputElement;
		this.periodInput  = document.createElement( 'input' );
		this.unitSelector = document.createElement( 'select' );
	}

	setup() {
		this.baseInput.style.display = 'none';

		this.periodInput.setAttribute( 'type', 'number' );
		this.periodInput.setAttribute( 'min',  '0' );
		this.periodInput.setAttribute( 'max',  '100000000' );
		this.periodInput.setAttribute( 'step', '1' );

		this.unitSelector.innerHTML = `
			<option value="seconds"> ${ __( 'Seconds', 'as-powertools' ) } </option>
			<option value="minutes"> ${ __( 'Minutes', 'as-powertools' ) } </option>
			<option value="hours">   ${ __( 'Hours', 'as-powertools' ) }   </option>
			<option value="days">    ${ __( 'Days', 'as-powertools' ) }    </option>
			<option value="weeks">   ${ __( 'Weeks', 'as-powertools' ) }   </option>
			<option value="months">  ${ __( 'Months', 'as-powertools' ) }  </option>
		`;

		this.baseInput.after( this.unitSelector );
		this.baseInput.after( this.periodInput );

		this.#baseElementToUI();
		this.periodInput.addEventListener( 'change', () => this.#uiToBaseElement() );
		this.unitSelector.addEventListener( 'change', () => this.#uiToBaseElement() );
	}

	#baseElementToUI() {
		const matches = this.baseInput.value.match( /^([0-9]+)\W*(seconds|minutes|hours|days|weeks|months)$/ );

		if ( matches === null ) {
			console.error( 'Unexpected time interval data.' );
			return;
		}

		const integral = parseInt( matches[1], 10 );
		const period   = matches[2];
		
		this.periodInput.value = integral;
		this.unitSelector.value = period;
	}

	#uiToBaseElement() {
		const integral = parseInt( this.periodInput.value, 10 );
		const period   = this.unitSelector.value;
		this.baseInput.setAttribute( 'value', `${integral} ${period}` );
	}
}

export function setupIntervalPicker( inputElement ) {
	IntervalPicker.manage( inputElement );
}
