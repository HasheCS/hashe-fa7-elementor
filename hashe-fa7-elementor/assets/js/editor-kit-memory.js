/* global elementor */
/**
 * Hashe FA7 editor memory cache, applicable only to our add-on Kit libraries.
 * Elementor Core and Pro native library storage, callbacks, and picker are untouched.
 * This is a narrowly approved adapter for Elementor 4.3.5 browser quota limits.
 */
(function () {
    'use strict';

    var libraryPrefix = 'hashe-fa7-kit-';
    var storagePrefix = 'elementor_' + libraryPrefix;
    var storageSuffix = '_icons';

    function ownsName(name) {
        return typeof name === 'string' &&
            name.indexOf(libraryPrefix) === 0 &&
            /^[a-z0-9-]+$/.test(name.slice(libraryPrefix.length));
    }

    function ownsKey(key) {
        return typeof key === 'string' &&
            key.indexOf(storagePrefix) === 0 &&
            key.slice(-storageSuffix.length) === storageSuffix &&
            ownsName(key.slice('elementor_'.length, -storageSuffix.length));
    }

    // Delete *only* our old persisted icon catalog records, preserving every
    // native Elementor cache entry, editor settings, and expiration record.
    function pruneOwnOldPersistentCatalogs() {
        try {
            var raw = window.localStorage.getItem('elementor');
            if (!raw) {
                return;
            }
            var data = JSON.parse(raw);
            if (!data || typeof data !== 'object' || Array.isArray(data)) {
                return;
            }
            var removed = false;
            Object.keys(data).forEach(function (key) {
                if (ownsKey(key)) {
                    delete data[key];
                    removed = true;
                }
            });
            if (data.__expiration && typeof data.__expiration === 'object') {
                Object.keys(data.__expiration).forEach(function (key) {
                    if (ownsKey(key)) {
                        delete data.__expiration[key];
                        removed = true;
                    }
                });
            }
            if (removed) {
                window.localStorage.setItem('elementor', JSON.stringify(data));
            }
        } catch (error) {
            // Storage can be full, blocked, or corrupt; in-memory icons still work.
        }
    }

    function install() {
        var manager = window.elementor && window.elementor.iconManager;
        var store = manager && manager.store;
        if (!store ||
            typeof store.save !== 'function' ||
            typeof store.get !== 'function') {
            return false;
        }
        if (store.__hasheFa7KitMemoryCache) {
            return true;
        }

        var originals = { save: store.save, get: store.get };
        var memory = Object.create(null);

        pruneOwnOldPersistentCatalogs();

        store.save = function (library) {
            if (library && ownsName(library.name)) {
                memory[storagePrefix + library.name.slice(libraryPrefix.length) + storageSuffix] = library;
                return;
            }
            return originals.save.apply(this, arguments);
        };

        store.get = function (key) {
            if (ownsKey(key)) {
                return memory[key];
            }
            return originals.get.apply(this, arguments);
        };

        store.__hasheFa7KitMemoryCache = true;
        return true;
    }

    if (install()) {
        return;
    }

    // Elementor creates iconManager after its editor bundle initializes.
    // We do not intercept initIconType, callbacks, native icon tabs, or picker UI.
    var attempts = 0;
    var timer = window.setInterval(function () {
        attempts += 1;
        if (install() || attempts >= 300) {
            window.clearInterval(timer);
        }
    }, 100);
}());
