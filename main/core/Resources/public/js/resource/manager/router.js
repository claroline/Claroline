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

    Claroline.ResourceManager.Router = Backbone.Router.extend({
        initialize: function (dispatcher, defaultDirectoryId) {
            this.dispatcher = dispatcher;
            this.defaultDirectoryId = defaultDirectoryId;
            this.dispatcher.on('open-directory', this.recordRoute, this);
            this.dispatcher.on('filter', this.recordRoute, this);
            this.route(/^$/, 'default', this.openDefault, this);
            //we authorize guid usage
            this.route(/^resources\/(.*)?$/, 'handle', this.handleRequest, this);
        },
        recordRoute: function (event) {
            if (event.view === 'main' && !event.fromRouter) { // avoid event loop
                var route = 'resources/' + event.nodeId;

                if (event.parameters) {
                    route += '?' + $.param(event.parameters);
                }

                this.navigate(route); // just recording, not triggering
            }
        },
        openDefault: function () {
            this.dispatchMainEvent('open-directory', this.defaultDirectoryId);
        },
        handleRequest: function (directoryId, queryString) {
            if (!queryString) {
                this.dispatchMainEvent('open-directory', directoryId);
            } else {
                var parameters = decodeURIComponent(queryString.substr(1)).split('&');
                var searchParameters = {};
                var knownParameters = ['name', 'dateFrom', 'dateTo', 'types[]'];
                _.each(parameters, function (parameter) {
                    parameter = parameter.split('=');

                    if (knownParameters.indexOf(parameter[0]) !== -1) {
                        searchParameters[parameter[0].replace('[]', '')] = parameter[1];
                    }
                });
                this.dispatchMainEvent('filter', directoryId, searchParameters);
            }
        },
        dispatchMainEvent: function (eventName, directoryId, parameters) {

            var event = {
                nodeId: directoryId,
                view: 'main',
                fromRouter: true
            };

            if (parameters) {
                event.parameters = parameters;
            }

            this.dispatcher.trigger(eventName, event);
        }
    });
})();
