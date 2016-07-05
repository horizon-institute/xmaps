/**
 * XMaps utility functions.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

var XMaps = XMaps || (function(){
	return new Object();
}());

XMaps.WorkerRunOnce = function( script ) {
	var workers = new Array( new Worker( script ) );
	var queuedTask = false;

	var runTask = function( worker, data, pre, callback ) {
		pre();
		var l = null;
		l = function( e ) {
			worker.removeEventListener( "message", l, false );
			callback( e.data );
			if ( queuedTask ) {
				runTask( worker, queuedTask.data, queuedTask.pre, queuedTask.callback );
			} else {
				workers.push( worker );
			}
			queuedTask = false;
		};
		worker.addEventListener( "message", l, false );
		worker.postMessage( data );
	};

	this.queueTask = function( data, pre, callback ) {
		var w = workers.pop();
		if ( w ) {
			runTask( w, data, pre, callback );
		} else {
			queuedTask = {
				"data": data,
				"pre": pre,
				"callback": callback
			};
		}
	};
};

XMaps.AltRunOnce = function( func ) {
	var workers = new Array( func );
	var queuedTask = false;

	var runTask = function( worker, data, pre, callback ) {
		pre();
		worker( data, function( msg ) {
			callback( msg );
			if ( queuedTask ) {
				runTask( worker, queuedTask.data, queuedTask.pre, queuedTask.callback );
			} else {
				workers.push( worker );
			}
			queuedTask = false;
		});
	};

	this.queueTask = function( data, pre, callback ) {
		var w = workers.pop();
		if ( w ) {
			runTask( w, data, pre, callback );
		} else {
			queuedTask = {
				"data": data,
				"pre": pre,
				"callback": callback
			};
		}
	};
};

XMaps.RunOnce = function( workerScript, altFunc ) {
	var inner = null;
	if ( typeof( Worker ) === 'undefined' ) {
		inner = new XMaps.AltRunOnce( altFunc );
	} else {
		inner = new XMaps.WorkerRunOnce( workerScript );
	}

	this.queueTask = function( data, pre, callback ) {
		inner.queueTask( data, pre, callback );
	};
};

XMaps.WorkerWorkerPool = function( size, script ) {

	var availableWorkers = [];
	var queuedTasks = [];
	for ( var i = 0; i < size; i++ ) {
		availableWorkers.push( new Worker( script ) );
	}

	var runTask = function( worker, data, callback ) {
		var l = null;
		l = function( e ) {
			worker.removeEventListener( "message", l, false );
			callback( e.data );
			var next = queuedTasks.pop();
			if ( next ) {
				runTask( worker, next.data, next.callback );
			} else {
				availableWorkers.push( worker );
			}
		};
		worker.addEventListener( "message", l, false );
		worker.postMessage( data );
	};

	this.queueTask = function( data, callback ) {
		var w = availableWorkers.pop();
		if ( w ) {
			runTask( w, data, callback );
		} else {
			queuedTasks.push({
				"data": data,
				"callback": callback
			});
		}
	};
};

XMaps.AltWorkerPool = function( size, func ) {

	var availableWorkers = [];
	var queuedTasks = [];
	for ( var i = 0; i < size; i++ ) {
		availableWorkers.push( { "func": func } );
	}

	var runTask = function( worker, data, callback ) {
		worker.func( data, function( msg ) {
			callback( msg );
			var next = queuedTasks.pop();
			if ( next ) {
				runTask( worker, next.data, next.callback );
			} else {
				availableWorkers.push( worker );
			}
		});
	};

	this.queueTask = function( data, callback ) {
		var w = availableWorkers.pop();
		if ( w ) {
			runTask( w, data, callback );
		} else {
			queuedTasks.push({
				"data": data,
				"callback": callback
			});
		}
	};

};

XMaps.WorkerPool = function( size, workerScript, altFunc ) {
	var inner = null;
	if ( typeof( Worker ) === 'undefined' ) {
		inner = new XMaps.AltWorkerPool( size, altFunc );
	} else {
		inner = new XMaps.WorkerWorkerPool( size, workerScript );
	}

	this.queueTask = function( data, pre, callback ) {
		inner.queueTask( data, pre, callback );
	};
};
