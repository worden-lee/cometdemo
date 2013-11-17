/*
 * ext.cometdemo.js : the client side of the comet API demo
 */
( function ( $, mw ) {

/*
 * startSpool : start up a Comet connection to spool the contents
 *   of the debug log, and create a notification bubble in the browser
 *   to display the text.
 */
function startSpool() {
	var $spool,
		autoscroll,
       		$notification,
		esource;
	// $spool is the div where the file contents go
	$spool = $( '<div id="cometdemo-spool"/>' );
	// initialize the div
	$spool.css( { 
		'min-height' : '3em',
		'max-height' : '300px',
		'overflow' : 'auto',
		'font-size' : '67%'
	} );
	// starting message is "connecting to the wiki"
	$spool.text( mw.message( 'cometdemo-opening' ).plain() );
	// we'll keep scrolling to the bottom of the text
	autoscroll = true;
	// but if someone scrolls it by hand, we'll yield control
	$spool.bind('scroll mousedown wheel DOMMouseScroll mousewheel keyup', function ( event ) {
		if ( event.which > 0 || event.type == 'mousedown' || event.type == 'mousewheel' ) {
			$spool.stop();
			autoscroll = false;
		}
	} );
	// and display it in a notification bubble
	// TODO add a line at the top; a scroll-to-bottom button; a close button, maybe
	mw.notify( $spool, { 
		tag: 'cometdemo-spool',
		autoHide: false       
	} );
	// get the notification bubble itself
	$notification = $( '.mw-notification-tag-cometdemo-spool' );
	// make the call to the wiki API
	esource = new EventSource( mw.util.wikiScript( 'api' ) + '?action=comet-demo' );
	// clicking on the notification will close it, so it should also
	// stop the EventSource.  this will send an abort to the API process.
	$notification.click( function () {
		// TODO this is getting called, but it's not causing an abort.
		esource.close();
	} );
	// When the connection is open, remove the "connecting" message
	esource.onopen = function ( event ) {
		$spool.text( '' );
	};
	// When text arrives, append it to the existing text
	esource.addEventListener( 'update', function ( event ) {
		var data = event.data.split( '; ' );
		var from = data.shift();
		// TODO: check from against previous value of to
		var to = data.shift();
		var text = data.join();
		// html-escape the text and put html line breaks
		text = $( '<span/>' ).text( text )
			.html().replace( /\n/g, '<br/>\n' );
		$spool.append( text );
		if ( autoscroll ) {
			$spool.stop().animate( { 'scrollTop': $spool[0].scrollHeight }, 1000 );
		}
	} );
	// If the source says it's done, stop it from trying to reload
	esource.addEventListener( 'done', function ( event ) {
		esource.close();
	} );
	// If the source reports an error, display it in place of the text
	esource.addEventListener( 'error', function ( event ) {
		var errortext;
		if ( 'data' in event ) {
			errortext = event.data;
		} else {
			errortext = mw.message( 'cometdemo-could-not-connect' ).plain();
		}
		esource.close();
		$notification.data( 'mw.notification' ).close();
		mw.notify( mw.message( 'cometdemo-error', errortext ).plain() );
	} );
	// TODO: reconnect on transmission error, with position
	// (use the position as the "id", and explicitly, when reloading from my code)
}

// Make the link in the tab do the spooling action.
$( '.cometdemo-tab' ).click( function ( event ) {
	event.preventDefault();
	startSpool();
} ); 

} )( jQuery, mw );
