/**
 * Scope Font Awesome 7 picker callbacks and keep Hashe catalogs in memory.
 *
 * Elementor stores every icon library under one "elementor" localStorage
 * object. Caching 16 Pro catalogs there can exceed the browser quota and throw
 * QuotaExceededError, leaving the Icon Library blank or partially loaded.
 *
 * Storage interception remains Hashe-only. Callback routing must include all\n * libraries: Elementor has one mutable notifyCallback shared by concurrent\n * fetches, so a late native response can otherwise invoke a Hashe callback.
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

    /**
     * The server is authoritative for tab identity. Elementor's React picker
     * mutates config.icons.libraries in place while async catalogs arrive,
     * and a stale callback can replace several *different* tabs with Brands.
     * Restore pristine tab descriptors before every picker opening, retaining
     * native and third-party libraries exactly as returned by Elementor.
     */
    function restoreExpectedTabs(editor) {
        const expected = window.hasheFa7ExpectedTabs;
        const config = editor && editor.config && editor.config.icons;
        const existing = config && config.libraries;
        if (!Array.isArray(expected) || !expected.length || !Array.isArray(existing)) {
            return false;
        }

        const names = new Set();
        const canonical = [];
        expected.forEach(function (tab) {
            if (!tab || typeof tab.name !== 'string' || names.has(tab.name)) {
                return;
            }
            names.add(tab.name);
            canonical.push(Object.assign({}, tab));
        });
        if (!canonical.length) {
            return false;
        }

        // Elementor itself may provide virtual "all" and "recommended" tabs,
        // and other editor extensions can create additional libraries via JS.
        // Preserve those existing tabs; only Hashe stale/invalid tab identities
        // are excluded. Third-party entries registered via PHP are already in
        // the authoritative array.
        const extras = [];
        existing.forEach(function (tab) {
            if (!tab || typeof tab.name !== 'string' ||
                names.has(tab.name) || tab.name.indexOf(prefix) === 0) {
                return;
            }
            names.add(tab.name);
            extras.push(tab);
        });
        const virtual = extras.filter(tab =>
            tab.name === 'all' || tab.name === 'recommended');
        const other = extras.filter(tab =>
            tab.name !== 'all' && tab.name !== 'recommended');
        const target = virtual.concat(canonical, other);

        // Do not discard successfully loaded native or Kit icon data merely
        // because a picker has been reopened. We only rebuild on an actual
        // identity/label collision, not on every modal appearance.
        const clean = target.length === existing.length &&
            target.every(function (tab, index) {
                return existing[index] && tab.name === existing[index].name &&
                    tab.label === existing[index].label;
            });
        if (clean) {
            return true;
        }
        existing.splice.apply(existing, [0, existing.length].concat(target));
        return true;
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
        restoreExpectedTabs(editor);

        const originals = {
            save: store.save,
            get: store.get,
            init: library.initIconType,
            run: library.runCallback,
            fetchIcons: library.fetchIcons
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

        // Elementor 4.x's fetchIcons() blindly assumes every JSON reply has
        // an "icons" property and has no catch handler. Any timeout, expired
        // nonce or unsupported Kit subset would otherwise raise an uncaught
        // promise rejection in the entire editor. Keep this fault boundary
        // Hashe-only: all native and third-party fetches use Elementor as-is.
        if (typeof originals.fetchIcons === 'function') {
            library.fetchIcons = function (settings) {
                if (!settings || !ownsLibrary(settings.name) ||
                    typeof window.fetch !== 'function') {
                    return originals.fetchIcons.apply(this, arguments);
                }
                const instance = this;
                return window.fetch(settings.fetchJson, {
                    mode: 'same-origin',
                    credentials: 'same-origin'
                }).then(function (response) {
                    if (!response.ok) {
                        return { icons: [] };
                    }
                    return response.json();
                }).catch(function () {
                    return { icons: [] };
                }).then(function (json) {
                    if (!json || !Array.isArray(json.icons)) {
                        json = { icons: [] };
                    }
                    settings.icons = json.icons;
                    if (settings.icons.length) {
                        return instance.normalizeIconList(settings);
                    }
                    // A deterministic, empty catalog keeps the category
                    // identity visible, with no false icons and no JS crash.
                    settings.icons = {};
                    return instance.runCallback(settings);
                });
            };
        }

        library.initIconType = function (settings, callback) {
            // Elementor 4.x stores only one global notifyCallback, even when
            // different libraries are fetched concurrently. Register the
            // callback by library name for *native and Hashe* tabs alike so
            // neither can consume the other's response.
            if (settings && typeof settings.name === 'string' &&
                typeof callback === 'function') {
                callbacks[settings.name] = callback;
            }
            return originals.init.apply(this, arguments);
        };

        library.runCallback = function (settings) {
            if (settings && typeof settings.name === 'string' &&
                Object.prototype.hasOwnProperty.call(callbacks, settings.name)) {
                const callback = callbacks[settings.name];
                delete callbacks[settings.name];
                return callback(settings);
            }
            // A named async response with no registered owner must never be
            // sent to Elementor's global *last callback*. That is how one
            // Brands result can overwrite unrelated tab entries.
            if (settings && typeof settings.name === 'string') {
                return settings;
            }
            return originals.run.apply(this, arguments);
        };

        // Hook the true Elementor picker lifecycle, not just a mocked
        // library call. A previously corrupted array must not persist between
        // openings. The original onPickerShow may mutate this exact array.
        if (typeof manager.onPickerShow === 'function') {
            const open = manager.onPickerShow;
            manager.onPickerShow = function () {
                restoreExpectedTabs(editor);
                return open.apply(this, arguments);
            };
        }
        library.__hasheFa7CallbackGuard = true;
        return true;
    }

    if (!install()) {
        let attempts = 0;
        const timer = window.setInterval(function () {
            attempts += 1;
            if (install() || attempts >= 1200) {
                window.clearInterval(timer);
            }
        }, 250);
    }
}());
