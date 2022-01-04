/**
 * @module changeListener
 */

/**
 * @typedef {Function} ext.popups.ChangeListener
 * @param {Object} oldState The previous state
 * @param {Object} newState The current state
 */

/**
 * Registers a change listener, which is bound to the
 * [store](http://redux.js.org/docs/api/Store.html).
 *
 * A change listener is a function that is only invoked when the state in the
 * [store](http://redux.js.org/docs/api/Store.html) changes. N.B. that there
 * may not be a 1:1 correspondence with actions being dispatched to the store
 * and the state in the store changing.
 *
 * See [Store#subscribe](http://redux.js.org/docs/api/Store.html#subscribe)
 * for more information about what change listeners may and may not do.
 *
 * @param {Redux.Store} store
 * @param {ext.popups.ChangeListener} callback
 * @return {void}
 */
export default function registerChangeListener( store, callback ) {
	// This function is based on the example in [the documentation for
	// Store#subscribe](http://redux.js.org/docs/api/Store.html#subscribe),
	// which was written by Dan Abramov.

	let previousState;

	store.subscribe( () => {
		const state = store.getState();

		if ( previousState !== state ) {
			callback( previousState, state );
			previousState = state;
		}
	} );
}
