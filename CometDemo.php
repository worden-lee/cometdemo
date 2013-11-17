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

// extension info for the Special:Version page
$wgExtensionCredits['cometdemo'][] = array(
	'path' => __FILE__,
	'name' => 'Comet/Server-Sent Events Demo Extension',
	'description' => 'Code to spool data continuously from the MediaWiki API to the client, and a demo operation',
	'version' => 0.1,
	'author' => 'Lee Worden',
	'url' => '', # fill in github url?
);

// Set period in microseconds between updates
// I'm setting it short for a smooth demo - for serious applications
// it should be longer, to give the server a break
$wgCometPeriod = 250000;

// Associate api.php?action=comet-demo with a class name to call
$wgAPIModules['comet-demo'] = 'CometDemoApi';

// Associate class name with the file where it's defined
$wgAutoloadClasses['CometApiBase'] // A reusable class
	= dirname(__FILE__).'/CometApi.php';
$wgAutoloadClasses['CometDemoApi'] // And a demo of what it can do
	= dirname(__FILE__).'/CometDemoApi.php';

// Declare file where the demo's messages are defined in the user's language
$wgExtensionMessagesFiles['cometdemo'] 
	= dirname(__FILE__).'/CometDemo.i18n.php';

// Declare resources (JavaScript, CSS) to be included in the output page
$wgResourceModules['ext.cometdemo'] = array(
	'localBasePath' => dirname( __FILE__ ) . '/resources',
	'scripts' => 'ext.cometdemo.js',
	'messages' => array(
		'cometdemo-opening',
		'cometdemo-error',
		'cometdemo-could-not-connect',
	),
	'dependencies' => array(
		'mediawiki.notify',
	),
);

// Make sure the resources get added to the output page
function wfCometDemoBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
	$out->addModules( array( 'ext.cometdemo' ) );
	return true;
}
$wgHooks['BeforePageDisplay'][] = 'wfCometDemoBeforePageDisplay';

// And add a 'View Debug Log' action to the page-top tabs, to run the demo
// In Vector, it'll be in the pull-down
function wfCometDemoSkinTemplateNavigation( SkinTemplate &$skt, array &$links ) {
	$links['actions']['view-debug-log'] = array(
		'class' => 'cometdemo-tab',
		'text' => wfMessage( 'cometdemo-tab-name' )->escaped(),
		'href' => '#'
	);
}
$wgHooks['SkinTemplateNavigation'][] = 'wfCometDemoSkinTemplateNavigation';

// Reassure the upstream code that nothing's wrong
return true;
?>
