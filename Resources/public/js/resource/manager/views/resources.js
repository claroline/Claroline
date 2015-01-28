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

    views.Resources = Backbone.View.extend({
        tagName: "div",
        className: "tab-pane active",
        outerEvents: {
            'directory-data': 'render'
        },
        initialize: function (parameters, dispatcher) {
            this.parameters = parameters;
            this.dispatcher = dispatcher;
            this.tabName = 'resources';
            this.wrapper = null;
            this.isAppended = false;
            this.buildElement();
            _.each(this.outerEvents, function (method, event) {
                this.dispatcher.on(event + '-' + this.parameters.viewName, this[method], this);
            }, this);
        },
        buildElement: function () {
            this.wrapper = this.$el;
            this.el.id = this.tabName+'-tab-pane';
            this.$el.attr("role", "tabpanel");

            this.subViews = {
                breadcrumbs: new views.Breadcrumbs(this.parameters, this.dispatcher),
                actions: new views.Actions(this.parameters, this.dispatcher),
                nodes: new views.Nodes(this.parameters, this.dispatcher)
            };
        },
        render: function () {
            if (!this.isAppended) {
                //this.parameters.parentElement.append(this.$el);

                if (!this.parameters.breadcrumbElement) {
                    this.wrapper.append(this.subViews.breadcrumbs.el);
                }

                this.wrapper.append(this.subViews.actions.el);
                this.wrapper.append(this.subViews.nodes.el);

                this.isAppended = true;
            }
        }
    });
})();
