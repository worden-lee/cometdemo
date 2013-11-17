<?php
/* Comet extension for MediaWiki 1.21 and later
 *
 * Copyright (C) 2010 Lee Worden <worden.lee@gmail.com>
 * http://lalashan.mcmaster.ca/theobio/projects/index.php/WorkingWiki
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

abstract class CometApiBase extends ApiBase {

	# call this at the beginning of execute()
	public function setupOutput() {
		# seize control of the HTTP response
		# this means no returning to the ApiBase machinery.
		wfResetOutputBuffers();
		header( 'Content-type: text/event-stream' );
		header( 'Cache-Control: no-cache, no-store, max-age=0, must-revalidate' );
		header( 'Pragma: no-cache' );
	}

	# call this to send an error to client, if output hasn't started yet
	# this will cause the EventSource to retry its request
	public function quitEarly( $text ) {
		header( 'HTTP/1.0 500 Internal Error: '. $text );
		$this->finishOutput();
	}

	# call this at the end of execute()
	public function finishOutput() { 
		exit( 0 );
	}

	# send an event
	public function sendEvent( $data, $event=null, $id=null, $retry=null, $comment=null ) {
		if ( $comment !== null ) {
			echo ': ' . str_replace( "\n", "\n: ", $comment ) . "\n";
		}
		if ( $retry !== null ) {
			echo "retry: $retry\n";
		}
		if ( $id !== null ) {
			echo "id: $id\n";
		}
		if ( $event !== null ) {
			echo "event: $event\n";
		}
		if ( $data !== null ) {
			echo 'data: ' . str_replace( "\n", "\ndata: ", $data ) . "\n";
		}
		echo "\n";
		@ob_flush();
		flush();
	}

	# spool a file from a given position
	public function spoolFile( $path, $from=0 ) {
		global $wgCometPeriod;
		while ( 1 ) {
			if ( ! file_exists( $path ) ) {
				throw new CometFileNotFoundException;
			}
			@$data = file_get_contents( $path, false, null, $from, 8192 );
			if ( strlen( $data ) > 0 ) {
				$to = $from + strlen($data);
				$this->sendEvent( "$from; $to; $data", 'update' );
				$from = $to;
				error_log( getmypid() . " scroll" );
			}
			# connection_aborted() will either return false or not return
			if ( ! connection_aborted() and strlen($data) < 8192 ) {
				usleep( $wgCometPeriod );
			}
		}
	}
}

class CometFileNotFoundException extends FatalError {
}

?>
