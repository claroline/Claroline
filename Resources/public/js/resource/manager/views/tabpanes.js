/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global Twig */
/* global Translator */
/* global ModalWindow */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.ResourceManager = window.Claroline.ResourceManager || {};
    window.Claroline.ResourceManager.Views = window.Claroline.ResourceManager.Views || {};
    var views = Claroline.ResourceManager.Views;

    views.Tabpanes = Backbone.View.extend({
        tagName: "div",
        className: "tab-content",
        outerEvents: {
            'directory-data': 'render'
        },
        initialize: function (parameters, dispatcher) {
            this.parameters = parameters;
            this.dispatcher = dispatcher;
            this.wrapper = null;
            this.isAppended = false;
            this.buildElement();
            _.each(this.outerEvents, function (method, event) {
                this.dispatcher.on(event + '-' + this.parameters.viewName, this[method], this);
            }, this);
        },
        buildElement: function () {
            this.wrapper = this.$el;

            this.tabPanes = {
                resources: new views.Resources(this.parameters, this.dispatcher),
                widgets: new views.Widgets(this.parameters, this.dispatcher)
            };
        },
        render: function () {
            if (!this.isAppended) {
                //this.parameters.parentElement.append(this.$el);
                this.wrapper.append(this.tabPanes.resources.el);

                if (this.parameters.isTinyMce) {
                    this.wrapper.append(this.tabPanes.widgets.el);
                }
                this.isAppended = true;
            }
        }
    });
})();
