/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.ResourceManager = window.Claroline.ResourceManager || {};

    var manager = window.Claroline.ResourceManager;
    var dispatcher = null;
    var server = null;
    var router = null;
    var views = {};
    var isInitialized = false;
    var areLogsActivated = false;
    var hasFetchedParameters = false;
    var fetchedParameters = {
        resourceTypes: null,
        webPath: null,
        language: null,
        zoom: null
    };

    /**
     * Creates a full manager, i.e. a plain view of desktop or workspace resources,
     * with all the utilities included (pickers, forms, etc.).
     *
     * Relevant parameters are:
     *
     * - directoryId : the id of the directory to open at initialization
     *      (defaults to "0", i.e. pseudo-root of all directories)
     * - preFetchedDirectory: a json representation of the first directory to open
     *      (defaults to null)
     * - isWorkspace: whether the manager is in a workspace (vs desktop) context
     *      (defaults to false)
     * - parentElement: the jquery element in which the views will be rendered
     *      (defaults to "body" element)
     * - breadcrumbElement: an existing jquery element to reuse for breadcrumbs
     *      (defaults to null)
     * - resourceTypes: an object whose properties describe the available resource types
     *      (defaults to empty object)
     * - webPath: the base url of the web directory
     *      (defaults to empty string)
     * - language: the locale to use when needed
     *      (defaults to "en")
     * - zoom: a zoom value for thumbnails
     *      (defaults to "zoom100")
     *
     * @param object parameters
     */
    manager.createFullManager = function (parameters) {
        initialize();
        var mainParameters = buildParameters('main', parameters, false, true);
        var pickerParameters = buildParameters('defaultPicker', parameters, true, true);
        var shortcutParameters = buildParameters('shortcutPicker', parameters, true, true);
        views['main'] = new manager.Views.Master(mainParameters, dispatcher);
        views['defaultPicker'] = new manager.Views.Master(pickerParameters, dispatcher);
        views['shortcutPicker'] = new manager.Views.Master(shortcutParameters, dispatcher);
        router = new manager.Router(dispatcher, mainParameters.directoryId);

        if (mainParameters.preFetchedDirectory) {
            server.setPreFetchedDirectory(mainParameters.preFetchedDirectory);
        }

        Backbone.history.start();
    };

    /**
     * Creates a named picker. Relevant parameters are:
     *
     * - pickerCallback: the function to be called when nodes are selected
     *      (default to empty function)
     * - isMultiSelectAllowed: whether the selection of multiple nodes should be allowed
     *      (default to false)
     * - parentElement: the jquery element in which the views will be rendered
     *      (defaults to "body" element)
     * - directoryId : the id of the directory to open at initialization
     *      (defaults to "0", i.e. pseudo-root of all directories)
     * - resourceTypes: an object whose properties describe the available resource types
     *      (defaults to empty object)
     * - webPath: the base url of the web directory
     *      (defaults to empty string)
     * - language: the locale to use when needed
     *      (defaults to "en")
     * - zoom: a zoom value for thumbnails
     *      (defaults to "zoom100")
     *
     * Note that if they're not provided, the manager will try to fetch the last five
     * parameters directly from the server before using their default values.
     *
     * @param string name
     * @param object parameters
     */
    manager.createPicker = function (name, parameters) {
        if (['main', 'defaultPicker', 'shortcutPicker'].indexOf(name) !== -1) {
            throw new Error('Invalid picker name "' + name + '" (reserved for internal use)');
        }

        initialize();
        fetchMissingParameters(parameters, function () {
            var pickerParameters = buildParameters(name, parameters, true);
            views[name] = new manager.Views.Master(pickerParameters, dispatcher);
        });
    };

    /**
     * Opens or closes a picker by its name.
     *
     * @param string name
     * @param string action (open|close)
     */
    manager.picker = function (name, action) {
        if (!views.hasOwnProperty(name) || !views[name].parameters.isPickerMode) {
            throw new Error('Unknown picker "' + name + '"');
        }

        action = action === 'close' ? 'close' : 'open';
        dispatcher.trigger(action + '-picker-' + name);
    };

    /**
     * Activates event dispatcher logging (debug utility).
     */
    manager.logEvents = function () {
        if (!areLogsActivated) {
            initialize();
            var originalTrigger = dispatcher.trigger;
            dispatcher.trigger = function (event, args) {
                var listenerCount = _.keys(dispatcher._events).indexOf(event) !== -1 ?
                    _.keys(dispatcher._events[event]).length :
                    0;
                console.debug(
                    'Event "' + event + '" was triggered ['
                    + listenerCount + ' listener(s) attached]'
                );
                originalTrigger.apply(dispatcher, [event, args]);
            }
            areLogsActivated = true;
        }
    };

    function initialize() {
        if (!isInitialized) {
            dispatcher = _.extend({}, Backbone.Events);
            server = new manager.Server(dispatcher);
            isInitialized = true;
        }
    }

    function buildParameters(viewName, parameters, isPicker, isDefault) {
        return {
            viewName: viewName,
            isPickerMode: isPicker,
            isWorkspace: parameters.isWorkspace || false,
            directoryId: isDefault ? '0' : parameters.directoryId || '0',
            preFetchedDirectory: parameters.preFetchedDirectory || null,
            parentElement: parameters.parentElement || $('body'),
            breadcrumbElement: isPicker ? null : parameters.breadcrumbElement || null,
            resourceTypes: parameters.resourceTypes || fetchedParameters.resourceTypes || {},
            language: parameters.language || fetchedParameters.language || 'en',
            webPath: parameters.webPath || fetchedParameters.webPath || '',
            zoom: parameters.zoom || fetchedParameters.zoom || 'zoom100',
            pickerCallback: parameters.pickerCallback || function () {},
            isPickerMultiSelectAllowed: isDefault || parameters.isPickerMultiSelectAllowed || false
        };
    }

    function fetchMissingParameters(givenParameters, callback) {
        var expectedParameters = ['resourceTypes', 'webPath', 'language', 'zoom'];
        var hasMissingParameter = _.some(expectedParameters, function (parameter) {
            return !givenParameters.hasOwnProperty(parameter);
        });

        if (!hasMissingParameter || hasFetchedParameters) {
            return callback();
        }

        server.fetchManagerParameters(function (data) {
            fetchedParameters.resourceTypes = data.resourceTypes;
            fetchedParameters.language = data.language;
            fetchedParameters.webPath = data.webPath;
            fetchedParameters.zoom = data.zoom;
            hasFetchedParameters = true;
            callback();
        });
    }
})();
