/* global elementor */
/**
 * Elementor's IconLibrary stores a single mutable notifyCallback, even when
 * several catalog fetches are in flight. Keep callbacks by Hashe library name.
 * Unlike v1.0.1, this does not inline large icon lists into editor config.
 */
(function () {
    'use strict';

    function install() {
        const editor = window.elementor;
        const library = editor && editor.iconManager && editor.iconManager.library;
        if ( ! library || 'function' !== typeof library.initIconType || 'function' !== typeof library.runCallback ) {
            return false;
        }
        if ( library.__hasheFa7CallbackGuard ) {
            return true;
        }

        const originalInit = library.initIconType;
        const originalRun = library.runCallback;
        const callbacks = Object.create(null);

        library.initIconType = function ( settings, callback ) {
            if ( settings && 'string' === typeof settings.name && settings.name.indexOf( 'hashe-fa7-' ) === 0 && 'function' === typeof callback ) {
                callbacks[ settings.name ] = callback;
            }
            return originalInit.apply( this, arguments );
        };

        library.runCallback = function ( settings ) {
            if ( settings && Object.prototype.hasOwnProperty.call( callbacks, settings.name ) ) {
                const callback = callbacks[ settings.name ];
                delete callbacks[ settings.name ];
                return callback( settings );
            }
            return originalRun.apply( this, arguments );
        };

        library.__hasheFa7CallbackGuard = true;
        return true;
    }

    if ( ! install() ) {
        let attempts = 0;
        const timer = window.setInterval( function () {
            attempts += 1;
            if ( install() || attempts >= 100 ) {
                window.clearInterval( timer );
            }
        }, 250 );
    }
}());