/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global Twig */
/* global ResourceManagerFilters */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.ResourceManager = window.Claroline.ResourceManager || {};
    window.Claroline.ResourceManager.Views = window.Claroline.ResourceManager.Views || {};

    Claroline.ResourceManager.Views.Filters = Backbone.View.extend({
        className: 'filters container-fluid',
        events: {
            'click button.close-panel': function () {
                this.toggle();
            },
            'click input.datepicker': function (event) {
                this.$(event.currentTarget).datepicker('show');
            },
            'changeDate input.datepicker': function (event) {
                this.$(event.currentTarget).datepicker('hide');
            },
            'keydown input.datepicker': function (event) {
                event.preventDefault();
                this.$(event.currentTarget).datepicker('hide');
            }
        },
        initialize: function (parameters, dispatcher, currentDirectory) {
            this.parameters = parameters;
            this.dispatcher = dispatcher;
            this.currentDirectory = currentDirectory;
            this.currentDirectoryId = parameters.directoryId;
        },
        toggle: function () {
            $(this.el).css('display', !this.isVisible ? 'block' : 'none');
            this.isVisible = !this.isVisible;
        },
        render: function () {
            $(this.el).html(Twig.render(ResourceManagerFilters, this.parameters));
        }
    });
})();
