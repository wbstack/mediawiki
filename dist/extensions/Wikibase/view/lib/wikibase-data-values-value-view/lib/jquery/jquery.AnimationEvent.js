( function( $ ) {
	'use strict';

	var PurposedCallbacks = require( './jquery.PurposedCallbacks.js' );
	/**
	 * Event dedicated to providing information about an animation which will be added to an
	 * animations queue. This is just a standard jQuery event object providing additional
	 * information and services related to an animation.
	 * It is up to the instantiating code to propagate the event and to start the actual animation
	 * related to the event. An animation related to the event should be started by using the
	 * event's `animationOptions` member. This ensures that the animation option's callback
	 * parameters consider changes to the event's `animationCallbacks` property.
	 *
	 *     @example
	 *     var animationEvent = jQuery.AnimationEvent( 'explode' ),
	 *         $node = $( '.some-node' );
	 *
	 *     $node.animate( animationEvent.animationOptions( {
	 *         duration: 'slow',
	 *         queue: 'explosions',
	 *         start: function() {
	 *             // Right before the animation actually starts, the 'explode' event will be
	 *             // triggered and the animationEvent will be provided as event object.
	 *             $node.trigger( animationEvent );
	 *         }
	 *     } ) );
	 *
	 *     // Using event triggered in first example. Alert something once the animation is done:
	 *     $( 'body' ).on( 'explode', function( animationEvent ) {
	 *         animationEvent.animationCallbacks.add( 'complete', function() {
	 *             alert( 'The thing exploded!' );
	 *         } );
	 *     } );
	 *
	 * @class jQuery.AnimationEvent
	 * @extends jQuery.Event
	 * @uses jQuery.PurposedCallbacks
	 * @license GNU GPL v2+
	 * @author Daniel Werner < daniel.a.r.werner@gmail.com >
	 *
	 * @constructor
	 *
	 * @param {string} animationPurpose Will be available as `animationPurpose` field. This will not
	 *        end up as the event's `type`. The `type` will always be set to `animation`.
	 * @param {Object} props Additional event properties which will be copied into the object.
	 *
	 * @throws {Error} when the animation purpose is not specified.
	 */
	var SELF = function AnimationEvent( animationPurpose, props ) {
		if ( !( this instanceof SELF ) ) {
			return new SELF( animationPurpose, props );
		}
		if ( typeof animationPurpose !== 'string' || animationPurpose.trim() === '' ) {
			throw new Error( 'An animation purpose has to be stated' );
		}

		// Apply "parent" constructor:
		$.Event.call( this, 'animation', props );

		var self = this;
		var callbacksList = new PurposedCallbacks( SELF.ANIMATION_STEPS );

		/**
		 * The purpose stated for the animation which is about to be started.
		 *
		 * @property {string}
		 */
		this.animationPurpose = animationPurpose;

		/**
		 * A `jQuery.PurposedCallbacks.Facade` which allows for registering callbacks for different
		 * stages of the animation which is about to be started. The possible stages are those
		 * defined in `jQuery.AnimationEvent.ANIMATION_STEPS.`
		 * (see http://api.jquery.com/animate For a detailed description for each animation stage.)
		 *
		 *     @example
		 *     event.animationCallbacks.add( 'step', function() { ... } );
		 *
		 * @see jQuery.AnimationEvent.ANIMATION_STEPS
		 * @property {jQuery.PurposedCallbacks.Facade}
		 */
		this.animationCallbacks = callbacksList.facade();

		/**
		 * The `jQuery.Animation` object associated with the event. This will only be set if the
		 * `animationOptions()` object generated by this instance is used as options for an
		 * animation and after the animation has been started (not just queued).
		 *
		 * @property {Object}
		 */
		this.animation = null;

		/**
		 * Returns an object which can be used as options for `jQuery.animate` or any shortcut
		 * version of it (e.g. `jQuery.fadeIn`). Defines all animation callback fields with
		 * functions which will trigger all registered callbacks and all callbacks still registered
		 * to the `animationCallbacks` field's `jQuery.PurposedCallbacks.Facade` object in the
		 * future. Optionally, an object of options to be mixed in can be given. If this object has
		 * callbacks defined already, then these callbacks will be mixed in and called first.
		 *
		 * IMPORTANT: The options generated by one `AnimationEvent` instance should only be used for
		 * one animation.
		 *
		 * @param {Object} [baseOptions={}]
		 * @return {Object}
		 *
		 * @throws {Error} if `animationOptions()` has been called already and the returned options
		 *         have been passed to some animation whose execution has started already.
		 * @throws {Error} when trying to use the an `AnimationEvent` instance's
		 *         `animationOptions()` for two different animations.
		 */
		this.animationOptions = function( baseOptions ) {
			if ( this.animation ) {
				throw new Error( 'The AnimationEvent instance\'s generated animation options are ' +
					'used within some animation already, they can not be used in two animations.' );
			}
			baseOptions = baseOptions || {};
			var options = $.extend( {}, baseOptions );

			$.each( SELF.ANIMATION_STEPS, function( i, purpose ) {
				// Consider callbacks defined in the given options, they should be called first.
				var baseCallback = baseOptions[ purpose ];
				var finalCallback = function() {
					if ( baseCallback ) {
						baseCallback.apply( this, arguments );
					}
					callbacksList.fireWith( this, [ purpose ], arguments );
				};
				if ( purpose === 'start' ) {
					options.start = function() {
						// If "start" gets called, this means the generated options are used within
						// an jQuery.Animation. Tell the AnimationEvent instance which has created
						// these options which jQuery.Animation object it is related to.
						var animation = arguments[0];
						if ( self.animation && self.animation !== animation ) {
							throw new Error( 'Can not use the same AnimationEvent instance\'s '
								+ 'animationOptions() for two different animations.' );
						}
						self.animation = animation;
						finalCallback.apply( this, arguments );
					};
				} else {
					options[ purpose ] = finalCallback;
				}
			} );
			return options;
		};
	};

	// Inherit from $.Event but remove certain fields this will create. This should not actually
	// matter since they will be overwritten when creating an instance, but do it the "clean" way
	// anyhow:
	SELF.prototype = new $.Event();
	delete SELF.prototype.timeStamp;
	delete SELF.prototype[ jQuery.expando ];

	/**
	 * All animation step callback option names usable in `jQuery.Animation`'s options.
	 *
	 * @property {string[]}
	 * @static
	 */
	SELF.ANIMATION_STEPS = [ 'start', 'step', 'progress', 'complete', 'done', 'fail', 'always' ];

	module.exports = SELF;

}( jQuery ) );
