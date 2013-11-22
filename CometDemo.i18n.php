<?php
/* CometDemo extension for MediaWiki 1.21 and later
 *
 * Copyright (C) 2013 Lee Worden <worden.lee@gmail.com>
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

/* internationalization for CometDemo */
$messages = array();

$messages['en'] = array(
	'cometdemo-tab-name' => 'View Debug Log',
	'cometdemo-opening' => 'Connecting to wiki...',
	'cometdemo-could-not-connect' => 'Could not connect to CometDemo API service',
	'cometdemo-no-logfile' => "CometDemo cannot display the contents of "
		. "the wikiʼs debug log, because the wiki does not have its "
		. "debug log enabled.  If you are the website administrator, "
		. "consider setting \$wgDebugLogFile, to enable this feature "
		. "for long enough to try this demo.",
	'cometdemo-notification-top' => "Click to dismiss.",
	'cometdemo-logfile-not-found' => "File $1 not found",
	'cometdemo-error' => 'Error: $1',
);
