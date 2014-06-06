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
    var isInitialized = false;
    var isDebug = false;
    var dispatcher = null;
    var server = null;
    var router = null;
    var views = {};
    var pickers = [];

    function initialize() {
        if (!isInitialized) {
            dispatcher = _.extend({}, Backbone.Events);
            server = new manager.Server(dispatcher);
            isInitialized = true;
        }
    }

    function mergeParameters(viewName, parameters, isPicker) {
        var mergedParameters = _.extend({}, parameters);
        mergedParameters.viewName = viewName;
        mergedParameters.directoryId = parameters.directoryId || '0';
        mergedParameters.directoryHistory = parameters.directoryHistory || [];
        mergedParameters.parentElement = parameters.parentElement || $('body');
        mergedParameters.resourceTypes = parameters.resourceTypes || {};
        mergedParameters.appPath = parameters.appPath || '';
        mergedParameters.webPath = parameters.webPath || '';
        mergedParameters.filterState = parameters.filterState || 'none';
        mergedParameters.zoom = parameters.zoom || 'zoom100';
        mergedParameters.isPickerMode = isPicker;

        return mergedParameters;
    }

    function createPicker(name, parameters) {
        var pickerParameters = mergeParameters(name, parameters, true);
        views[name] = new manager.Views.Master(pickerParameters, dispatcher);
        pickers.push(name);
        dispatcher.on('picker-action', function (event) {
            manager.picker(event.name, event.action);
        });
    };

    manager.createFullManager = function (parameters) {
        initialize();
        createPicker('defaultPicker', parameters);
        var mainParameters = mergeParameters('main', parameters, false);
        views['main'] = new manager.Views.Master(mainParameters, dispatcher);
        router = new manager.Router(dispatcher, parameters.directoryId);
        Backbone.history.start();
    };

    manager.createPicker = function (name, parameters) {
        if (name === 'main' || name === 'defaultPicker') {
            throw new Error('Cannot use "' + name + '" for picker (internal use)');
        }

        initialize();
        createPicker(name, parameters);
    };

    manager.picker = function (name, action) {
        if (pickers.indexOf(name) === -1) {
            throw new Error('Unknown picker "' + name + '"');
        }

        action = ['open', 'close'].indexOf(action) !== -1 ? action : 'open';


        dispatcher.trigger(action + '-picker-' + name);
    };

    manager.setDebugMode = function () {
        if (!isDebug) {
            initialize();
            dispatcher.on('all', function (eventName) {
                var listenerCount = (_.keys(dispatcher._events)).indexOf(eventName) !== -1 ?
                    _.keys(dispatcher._events[eventName]).length :
                    0;
                console.debug(
                    'Event "' + eventName + '" was triggered ['
                     + listenerCount + ' listener(s) attached]'
                );
            });
        }
    };



    manager.setDebugMode();


    /**
     * Initializes the resource manager with a set of options :
     * - appPath: the base url of the application
     *      (default to empty string)
     * - webPath: the base url of the web directory
     *      (default to empty string)
     * - directoryId : the id of the directory to open in main (vs picker) mode
     *      (default to "0", i.e. pseudo-root of all directories)
     * - parentElement: the jquery element in which the views will be rendered
     *      (default to "body" element)
     * - resourceTypes: an object whose properties describe the available resource types
     *      (default to empty object)
     * - isPickerOnly: whether the manager must initialize a main view and a picker view, or just the picker one
     *      (default to false)
     * - isMultiSelectAllowed: whether the selection of multiple nodes in picker mode should be allowed or not
     *      (default to false)
     * - pickerCallback: the function to be called when nodes are selected in picker mode
     *      (default to  empty function)
     *
     * @param object parameters The parameters of the manager
     */
    manager.initialize = function (parameters) {
        parameters = parameters || {};
        parameters.language = parameters.language || 'en';
        parameters.directoryId = parameters.directoryId || '0';
        parameters.directoryHistory = parameters.directoryHistory || [];
        parameters.parentElement = parameters.parentElement || $('body');
        parameters.resourceTypes = parameters.resourceTypes || {};
        parameters.isPickerOnly = parameters.isPickerOnly || false;
        parameters.isPickerMultiSelectAllowed = parameters.isPickerMultiSelectAllowed || false;
        parameters.pickerCallback = parameters.pickerCallback || function () {};
        parameters.appPath = parameters.appPath || '';
        parameters.webPath = parameters.webPath || '';
        parameters.filterState = parameters.filterState || 'none';
        parameters.zoom = parameters.zoom || 'zoom100';
        manager.Controller.initialize(parameters);
    };


    manager.create = function (parameters) {
        parameters = parameters || {};
        parameters.type = parameters.type || 'full';
        parameters.directoryId = parameters.directoryId || '0';
        parameters.appPath = parameters.appPath || '';
        parameters.webPath = parameters.webPath || '';
        parameters.parentElement = parameters.parentElement || $('body');
        parameters.resourceTypes = parameters.resourceTypes || {};
        parameters.zoom = parameters.zoom || 'zoom100';

        // ???
        parameters.directoryHistory = parameters.directoryHistory || [];

        if (parameters.type === 'picker') {
            if (!parameters.pickerName) {
                throw new Error('Missing "name" parameter for picker');
            }

            parameters.pickerName = parameters.pickerName;
            parameters.pickerCallback = parameters.pickerCallback || function () {};
            parameters.isPickerMultiSelectAllowed = parameters.isPickerMultiSelectAllowed || false;
        }

        manager.Controller.createView(parameters);
    };


//
//    /**
//     * Opens or closes the resource picker, depending on the "action" parameter.
//     *
//     * @param string action The action to be taken, i.e. "open" or "close" (default to "open")
//     */
//    manager.picker = function (action, pickerName) {
//        manager.Controller.picker(action === 'open' ? action : 'close', null, pickerName);
//    };
})();
