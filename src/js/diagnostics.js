const tests       = [];
let   environment = {};

export function setupDiagnostics( envObject ) {
	environment = envObject;
	determineTests();
}

function determineTests() {
	document.querySelectorAll( '#as-power-tools-diagnostics .diagnostic-assessment' ).forEach( control => { 
		const test = control.dataset.test;
		if ( typeof test === "string" ) {
			tests.push( { testID: test, control: control } );
		}
	} );

	console.log( tests );
}

export function runDiagnosticTests() {
	const nextTest = tests.shift();

	if ( "object" !== typeof nextTest ) {
		return;
	}

	const data = new FormData();
	data.append( 'action', 'as_powertools_diagnostics' );
	data.append( 'nonce',  asPowerTools.nonce );
	data.append( 'test', nextTest.testID );

	const handler = ( response ) => { handleTestResponse( response, nextTest.control ) };
	controlIndicatorStatus( nextTest.control, 'working' );

	fetch( asPowerTools.serverUrl, { method: 'post', body: data } )
		.then( handler, handler )
		.finally( () => setTimeout( runDiagnosticTests, 400 ) );
}

function handleTestResponse( response, control ) {
	let body = false;
	response.json().then( data => body = data ).finally( () => { 
		const success = ( 'object' === typeof body && body.hasOwnProperty( 'success' ) && body.success === true );
		const message = ( 'object' === typeof body && body.hasOwnProperty( 'data' ) && body.data.hasOwnProperty( 'message' ) && typeof body.data.message === 'string' ) ? body.data.message : '';

		if ( success ) {
			testSucceeded( control, message );
		} else {
			testFailed( control, message );
		}
	} );
}

function testSucceeded( control, message ) {
	controlIndicatorStatus( control, 'good' );

	if ( message.length > 0 ) {
		control.querySelector( '.description' ).innerHTML = message;
	}
}

function testFailed( control, message ) {
	controlIndicatorStatus( control, 'problematic' );

	if ( message.length > 0 ) {
		control.querySelector( '.description' ).innerHTML = message;
	}
}

function controlIndicatorStatus( control, status ) {
	const indicator = control.querySelector( '.indicator' );
	indicator.classList.remove( 'undetermined' );
	indicator.classList.remove( 'working' );
	indicator.classList.add( status );
}