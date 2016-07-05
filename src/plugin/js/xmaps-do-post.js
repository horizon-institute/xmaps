/**
 * Utility for do POST request in a web worker.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

self.addEventListener(
	"message",
	function ( e ) {
		var req = new XMLHttpRequest();
		req.open( "POST", e.data.url, false );
		req.setRequestHeader( "Accept", "application/json" );
		req.setRequestHeader( "Content-Type", e.data.contenttype );
		req.send( e.data.requestdata );
		self.postMessage( JSON.parse( req.responseText ) );
	},
false );
