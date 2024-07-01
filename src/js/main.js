import '../css/styles.css';

const environment = inspectEnvironment();

let pageForm;
let saveButton;

function launch() {
	pageForm    = document.querySelector( '#wpbody-content > .wrap > form' );
	saveButton  = pageForm !== null ? pageForm.querySelector( '.save-button' ) : null;

	if ( environment.isActionSchedulerHome ) {
		addPowerToolsLink();
	}
	
	if ( environment.isPowerToolsPage ) {
		formSubmitHint();
	}
}

function inspectEnvironment() {
	const query          = new URLSearchParams( window.location.search );
	const page           = query.get( 'page' );
	let   powerToolsPage = query.get( 'powertools' );

	switch ( powerToolsPage ) {
		case 'home': break;
		default:     powerToolsPage = null;
	}

	return {
		'page':                  page,
		'isActionScheduler':     page === 'action-scheduler',
		'isActionSchedulerHome': page === 'action-scheduler' && powerToolsPage === null,
		'isPowerToolsPage':      page === 'action-scheduler' && powerToolsPage !== null,
		'asPowerToolsHome':      page === 'action-scheduler' && powerToolsPage === 'home',
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
	if ( pageForm === null ) {
		return;
	}

	const fieldsChecksum = getHashForFieldValues();
	let   promptTimeout  = 0;

	pageForm.addEventListener( 'input', () => { 
		const newCheckSum = getHashForFieldValues();


		if ( newCheckSum === fieldsChecksum ) {
			saveButton.classList.remove( 'button-primary' )
		} else if ( ! saveButton.classList.contains( 'button-primary' ) ) {
			saveButton.classList.add( 'button-primary' );
			saveButton.classList.add( 'prompt' );
			clearTimeout(promptTimeout);
			setTimeout( () => saveButton.classList.remove( 'prompt' ), 250 );
		}
	} );
}

function getHashForFieldValues() {
	let checksum    = 0;
	let accumulator = '';


	pageForm.querySelectorAll( 'input, select' ).forEach( element => {
		accumulator += element.value;
	} );

	for ( let i = 0; i < accumulator.length; i++ ) {
		checksum += checksum + accumulator.charCodeAt( i );
	}
	
	console.log ( checksum.toString( 16 ) );
	return checksum.toString( 16 );
}

document.readyState === 'complete' 
	? launch()
	: document.addEventListener( 'DOMContentLoaded', launch );

