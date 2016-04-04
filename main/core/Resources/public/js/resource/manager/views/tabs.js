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
/* global ResourceManagerTabs */

(function(){
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.ResourceManager = window.Claroline.ResourceManager || {};
    window.Claroline.ResourceManager.Views = window.Claroline.ResourceManager.Views || {};
    var views = Claroline.ResourceManager.Views;

    views.Tabs = Backbone.View.extend({
        tagName: "ul",
        className: "nav nav-tabs",
        outerEvents: {
            'directory-data': 'render'
        },
        events: {
            "shown.bs.tab a.tab-btn": "tabChanged"
        },
        initialize: function(parameters, dispatcher) {
            this.parameters = parameters;
            this.dispatcher = dispatcher;
            this.isAppended = false;
            this.tabs = ['resources', 'widgets'];
            this.buildElement();
            _.each(this.outerEvents, function (method, event) {
                this.dispatcher.on(event + '-' + this.parameters.viewName, this[method], this);
            }, this);
        },
        buildElement: function () {
            this.$el.attr("role", "tablist");
        },
        tabChanged: function(event) {
            this.dispatcher.trigger($(event.target).attr("aria-controls")+"-visible");
        },
        render: function() {
            if (!this.isAppended) {
                this.$el.html(Twig.render(ResourceManagerTabs, {
                    tabs: this.tabs
                }));

                this.isAppended = true;
            }
        }
    });
})();