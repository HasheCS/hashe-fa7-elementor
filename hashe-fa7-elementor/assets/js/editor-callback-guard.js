/**
 * Scope Font Awesome 7 picker callbacks and keep Hashe catalogs in memory.
 *
 * Elementor stores every icon library under one "elementor" localStorage
 * object. Caching 16 Pro catalogs there can exceed the browser quota and throw
 * QuotaExceededError, leaving the Icon Library blank or partially loaded.
 *
 * We only intercept our own libraries. Elementor's other caches are unchanged.
 */
(function () {
    'use strict';

    const prefix = 'hashe-fa7-';
    const storagePrefix = 'elementor_' + prefix;
    const storageSuffix = '_icons';

    function ownsLibrary(name) {
        return typeof name === 'string' && name.indexOf(prefix) === 0;
    }

    function ownsStorageKey(key) {
        return typeof key === 'string' &&
            key.indexOf(storagePrefix) === 0 &&
            key.slice(-storageSuffix.length) === storageSuffix;
    }

    function removeOldHasheCaches() {
        try {
            const storage = window.localStorage;
            const raw = storage.getItem('elementor');
            if (!raw) {
                return;
            }
            const values = JSON.parse(raw);
            if (!values || typeof values !== 'object' || Array.isArray(values)) {
                return;
            }

            let count = 0;
            Object.keys(values).forEach(function (key) {
                if (ownsStorageKey(key)) {
                    delete values[key];
                    count += 1;
                }
            });

            if (values.__expiration && typeof values.__expiration === 'object') {
                Object.keys(values.__expiration).forEach(function (key) {
                    if (ownsStorageKey(key)) {
                        delete values.__expiration[key];
                        count += 1;
                    }
                });
            }

            if (count > 0) {
                storage.setItem('elementor', JSON.stringify(values));
            }
        } catch (error) {
            // Browser storage can be disabled; our in-memory cache still works.
        }
    }

    function install() {
        const editor = window.elementor;
        const manager = editor && editor.iconManager;
        const library = manager && manager.library;
        const store = manager && manager.store;
        if (!library || !store ||
            typeof library.initIconType !== 'function' ||
            typeof library.runCallback !== 'function' ||
            typeof store.save !== 'function' ||
            typeof store.get !== 'function') {
            return false;
        }
        if (library.__hasheFa7CallbackGuard) {
            return true;
        }

        removeOldHasheCaches();

        const originals = {
            save: store.save,
            get: store.get,
            init: library.initIconType,
            run: library.runCallback
        };
        const memory = Object.create(null);
        const callbacks = Object.create(null);

        store.save = function (settings) {
            if (settings && ownsLibrary(settings.name)) {
                memory[storagePrefix + settings.name.slice(prefix.length) + storageSuffix] = settings;
                return;
            }
            return originals.save.apply(this, arguments);
        };

        store.get = function (key) {
            if (ownsStorageKey(key)) {
                return memory[key];
            }
            return originals.get.apply(this, arguments);
        };

        library.initIconType = function (settings, callback) {
            if (settings && ownsLibrary(settings.name) && typeof callback === 'function') {
                callbacks[settings.name] = callback;
            }
            return originals.init.apply(this, arguments);
        };

        library.runCallback = function (settings) {
            if (settings && ownsLibrary(settings.name) &&
                Object.prototype.hasOwnProperty.call(callbacks, settings.name)) {
                const callback = callbacks[settings.name];
                delete callbacks[settings.name];
                return callback(settings);
            }
            return originals.run.apply(this, arguments);
        };

        library.__hasheFa7CallbackGuard = true;
        return true;
    }

    if (!install()) {
        let attempts = 0;
        const timer = window.setInterval(function () {
            attempts += 1;
            if (install() || attempts >= 120) {
                window.clearInterval(timer);
            }
        }, 250);
    }
}());
