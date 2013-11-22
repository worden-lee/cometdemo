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

class CometDemoApi extends CometApiBase {
	public function execute() {
		global $wgDebugLogFile;
		$params = $this->extractRequestParams();
		$from = $params['from'];

		$this->setupOutput();
		// where is the Last-Event-ID header?
		// error_log( 'Last-Event-ID: ' . $_SERVER['Last-Event-ID'] );
		//error_log( 'Last_Event_ID: ' . $_SERVER['HTTP_LAST_EVENT_ID'] );
		//error_log( 'keys: ' . json_encode( array_keys($_SERVER) ) );
		if ( $wgDebugLogFile == '' ) {
			$this->sendEvent( 
				wfMessage( 'cometdemo-no-logfile' )->escaped(), 
				'error' 
			);
		} else {
			try {
				$this->spoolFile( $wgDebugLogFile, $from );
			} catch ( CometFileNotFoundException $ex ) {
				$this->sendEvent(
					wfMessage( 'cometdemo-logfile-not-found',
						$wgDebugLogFile )->escaped(),
					'error'
				);
			}
		}
		$this->finishOutput();
	}

	public function getAllowedParams() {
		return array(
			'from' => array(
					ApiBase::PARAM_TYPE => 'integer',
					#ApiBase::PARAM_REQUIRED => false
			),
		);
	}

	public function getParamDescription() {
		return array(
			'from' => 'Starting byte index',
		);
	}

	public function getDescription() {
		return 'Use Server-Sent Events to spool the contents of the wiki\'s debug logfile';
	}

	public function getVersion() {
		return __CLASS__ . ': (version unknown.	By Lee Worden.)';
	}
}

?>
