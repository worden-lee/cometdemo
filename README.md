This is a small MediaWiki extension demonstrating the use of Server-Sent
Events (SSE, also known as a form of Comet interaction) to stream
information to the browser in near realtime, within a wiki.

Included is a general-purpose Comet ApiBase subclass, a second subclass
using that code to implement a specific api action, and a JavaScript
client interface to display the streaming information.

When this extension is installed, wiki pages will gain a "View debug log"
action tab, which opens a notification bubble where the contents of the 
wiki's debug logfile are displayed in near realtime.

Working in MW 1.21+ as of 11/2013.
