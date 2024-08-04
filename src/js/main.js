import '../css/styles.css';
import { setupDiagnostics, runDiagnosticTests } from './diagnostics';
import { setupIntervalPicker } from './intervalpicker';

const environment = inspectEnvironment();

function launch() {
	environment.pageForm    = document.querySelector( '#wpbody-content > .wrap > form' );
	environment.saveButton  = environment.pageForm !== null ? environment.pageForm.querySelector( '.save-button' ) : null;

	if ( environment.isActionSchedulerHome ) {
		addPowerToolsLink();
	}
	
	if ( environment.isPowerToolsPage ) {
		formSubmitHint();
	}

	if ( environment.asPowerToolsHome ) {
		setupIntervalPicker( document.getElementById( 'retention-period' ) );
	}

	if ( environment.asPowerToolsDiagnostics ) {
		runDiagnostics();
	}
}

function inspectEnvironment() {
	const query          = new URLSearchParams( window.location.search );
	const page           = query.get( 'page' );
	let   powerToolsPage = query.get( 'powertools' );

	if ( [ 'home', 'diagnostics' ].indexOf( powerToolsPage ) === -1 ) {
		powerToolsPage = null;
	}

	return {
		'page':                    page,
		'isActionScheduler':       page === 'action-scheduler',
		'isActionSchedulerHome':   page === 'action-scheduler' && powerToolsPage === null,
		'isPowerToolsPage':        page === 'action-scheduler' && powerToolsPage !== null,
		'asPowerToolsHome':        page === 'action-scheduler' && powerToolsPage === 'home',
		'asPowerToolsDiagnostics': page === 'action-scheduler' && powerToolsPage === 'diagnostics',
		'pageForm':                null,
		'saveButton':              null
	}
}

/**
 * @todo make conditional on being on right page!
 */
function addPowerToolsLink() {
	const screenHeading = document.querySelector( 'h1.wp-heading-inline');

	if ( screenHeading === null ) {
		return;
	}

	const link = document.createElement('a');
	link.className = 'page-title-action';
	link.innerHTML = 'Open Power Tools ⤏';
	link.href = '?page=action-scheduler&powertools=home';
	screenHeading.insertAdjacentElement( 'afterend', link );
}

function formSubmitHint() {
	if ( environment.pageForm === null ) {
		return;
	}

	const fieldsChecksum = getHashForFieldValues();
	let   promptTimeout  = 0;

	environment.pageForm.addEventListener( 'input', () => { 
		const newCheckSum = getHashForFieldValues();


		if ( newCheckSum === fieldsChecksum ) {
			environment.saveButton.classList.remove( 'button-primary' )
		} else if ( ! environment.saveButton.classList.contains( 'button-primary' ) ) {
			environment.saveButton.classList.add( 'button-primary' );
			environment.saveButton.classList.add( 'prompt' );
			clearTimeout(promptTimeout);
			setTimeout( () => environment.saveButton.classList.remove( 'prompt' ), 250 );
		}
	} );
}

function getHashForFieldValues() {
	let checksum    = 0;
	let accumulator = '';


	environment.pageForm.querySelectorAll( 'input, select' ).forEach( element => {
		accumulator += element.value;
	} );

	for ( let i = 0; i < accumulator.length; i++ ) {
		checksum += checksum + accumulator.charCodeAt( i );
	}
	
	return checksum.toString( 16 );
}

function runDiagnostics() {
	setupDiagnostics( environment );
	runDiagnosticTests();
}

document.readyState === 'complete' 
	? launch()
	: document.addEventListener( 'DOMContentLoaded', launch );

