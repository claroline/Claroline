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
            this.route(/^$/, 'default', function () {
                dispatcher.trigger('open-directory', {
                    directoryId: defaultDirectoryId,
                    view: 'main'
                });
            });
            this.route(/^resources\/(\d+)(\?.*)?$/, 'display', function (directoryId, queryString) {
                var searchParameters = null;

                if (queryString) {
                    //searchParameters = {};
                    var parameters = decodeURIComponent(queryString.substr(1)).split('&');
                    _.each(parameters, function (parameter) {
                        parameter = parameter.split('=');

                        if (['name', 'dateFrom', 'dateTo', 'roots[]', 'types[]'].indexOf(parameter[0]) > -1 &&
                            searchParameters) {
                            searchParameters[parameter[0].replace('[]', '')] = parameter[1];
                        }
                    });
                }

                dispatcher.trigger('open-directory-main', {
                    directoryId: directoryId,
                    parameters: searchParameters
                });
            });
        }
    });
})();
