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
		autoscrollTimerId = null,
		$status,
		$container,
       		notification,
		reconnectInterval = mw.config.get( 'cometRetryInterval' ),
		timerId = null;

	// Create a notification bubble

	// $spool is the div where the file contents go
	$spool = $( '<div id="cometdemo-spool"/>' );
	// initialize the div
	$spool.css( { 
		'min-height' : '3em',
		'max-height' : '300px',
		'overflow' : 'auto',
		'margin-top' : '5px',
		'font-size' : '67%',
		'font-family' : 'monospace'
	} );
	// we'll keep scrolling to the bottom of the text
	autoscroll = true;
	// but if someone scrolls it by hand, we'll yield control
	$spool.bind('scroll mousedown wheel DOMMouseScroll mousewheel keyup', function ( event ) {
		if ( event.which > 0 || event.type == 'mousedown' || event.type == 'mousewheel' ) {
			$spool.stop();
			autoscroll = false;
			$status.css( { 'font-style' : 'normal' } );
			// if they scroll to the bottom and stop there,
			// reeactivate autoscrolling
			clearTimeout( autoscrollTimerId );
			autoscrollTimerId = setTimeout( function () {
				if ( $spool.scrollTop() + $spool.height()
					>= $spool[0].scrollHeight ) {
					autoscroll = true;
					$status.css( { 'font-style' : 'italic' } );
				}
			}, 1000 );
		}
	} );
	// status is up at the top, saying what's happening
	$status = $( '<div id="cometdemo-status"/>' );
	$status.css( { 
		'font-size' : '67%'
	} );
	// starting message is "connecting to the wiki"
	$status.text( mw.message( 'cometdemo-opening' ).plain() );
	// roll those together
	$container = $( '<div id="cometdemo-container"/>' );
	$container.append( mw.message( 'cometdemo-notification-top' ).plain() )
		.append( $status ) // uncomment to get some debug output
		.append( $spool );
	// and display it in a notification bubble
	mw.notify( $container, { 
		tag: 'cometdemo-spool',
		autoHide: false       
	} );
	// provide access to the notification bubble
	// we have to do it indirectly, because we don't have access to
	// the data until sometime later, after the animation adds it to
	// the DOM
	function callOnNotification( callback ) {
		// can't do it right away because it gets added to the DOM
		// after a delay.
		if ( ! notification ) {
			notification = $( '.mw-notification-tag-cometdemo-spool' ).data( 'mw.notification' );
		}
		if ( notification ) {
			callback( notification );
		} else {
			window.setTimeout( function () { 
				callOnNotification( callback );
			}, 2 );
		}
	}
	// make the call to the wiki API
	function connectToWiki( lastPosition ) {
		var url = mw.util.wikiScript( 'api' ) + '?action=comet-demo'
			+ '&from=' + lastPosition;
		var esource = new EventSource( url );
		// When the connection is open, remove the "connecting" message
		esource.onopen = function ( event ) {
			$status.text( '(open)' );
			// clicking on the notification will close it, so it should also
			// stop the EventSource.
			callOnNotification( function( n ) {
				n.$notification.click( function () {
					closeConnection();
				} );
			} );
		};
		// keep a timer to force a reconnect if nothing happens for a while
		function keepAlive() {
			if ( timerId ) {
				window.clearTimeout( timerId );
			}
			timerId = window.setTimeout( function () {
				$status.text( '(timed out: reconnecting)' );
				closeConnection();
				connectToWiki( lastPosition );
			}, reconnectInterval );
		};
		// shut everything down when needed
		// note this sometimes doesn't communicate an abort to the server.
		// this is why the server times itself out pretty frequently.
		function closeConnection() {
			if ( timerId ) {
				window.clearTimeout( timerId );
			}
			esource.close();
		}

		// When text arrives, append it to the existing text
		esource.addEventListener( 'update', function ( event ) {
			var data = event.data.split( '; ' );
			var from = data.shift();
			if ( from != lastPosition ) {
				if ( lastPosition > 0 ) {
					$status.text( '(lost data: retrying)' );
					closeConnection();
					connectToWiki( lastPosition );
					return;
				}
				$spool.html( '...<br/>' );
			}
			var to = data.shift();
			$status.text( '(' + from + ' -- ' + to + ')' );
			var text = data.join();
			// html-escape the text and put html line breaks
			text = $( '<span/>' ).text( text )
				.html().replace( /\n/g, '<br/>\n' );
			$spool.append( text );
			var lengthLimit = 1000000; // enough is enough
			var st = $spool.text();
			if ( st.length > lengthLimit ) {
				$spool.text( '...<br/>' + st.substr( st.length -lengthLimit ) );
			}
			lastPosition = to;
			if ( autoscroll ) {
				$spool.stop().animate( { 'scrollTop': $spool[0].scrollHeight }, 1000 );
			}
			keepAlive();
		} );
		// A keep-alive event just tells us we can postpone reconnecting
		esource.addEventListener( 'keep-alive', function ( event ) {
			//$status.text( '(keep alive)' );
			keepAlive();
		} );
		// If the source says it's done, stop it from trying to reload
		esource.addEventListener( 'done', function ( event ) {
			$status.text( '(done)' );
			closeConnection();
		} );
		// If the source reports an error, display it in place of the text
		esource.addEventListener( 'error', function ( event ) {
			if ( lastPosition > 0 ) {
				// if we've been connected, this may be a routine
				// timeout.  Just wait for the timer to reconnect.
				$status.text( '(lost connection)' );
			} else {
				$status.text( '(error)' );
				var errortext;
				if ( 'data' in event ) {
					errortext = event.data;
				} else {
					errortext = mw.message( 'cometdemo-could-not-connect' ).plain();
				}
				closeConnection();
				callOnNotification( function ( n ) {
					n.close();
				} );
				mw.notify( mw.message( 'cometdemo-error', errortext ).plain() );
			}
		} );
		keepAlive();
	}
	connectToWiki( 0 );
}

// Make the link in the tab do the spooling action.
$( '.cometdemo-tab' ).click( function ( event ) {
	event.preventDefault();
	startSpool();
} ); 

} )( jQuery, mw );
