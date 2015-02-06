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

    views.Widgets = Backbone.View.extend({
        tagName: "div",
        className: "tab-pane widget-tree",
        initialize: function (parameters, dispatcher) {
            this.parameters = parameters;
            this.dispatcher = dispatcher;
            this.tabName = 'widgets';
            this.wrapper = null;
            this.isAppended = false;
            this.buildElement();
            this.dispatcher.on('widgets-tab-pane-visible', this.getWorkspaces, this);
            this.dispatcher.on('workspace-list-returned', this.render, this)
        },
        buildElement: function () {
            this.wrapper = this.$el;
            this.el.id = this.tabName+'-tab-pane';
            this.$el.attr("role", "tabpanel");
        },
        getWorkspaces: function (event) {
            if (!this.isAppended) {
                this.dispatcher.trigger('get-workspace-list');
            }
        },
        render: function (data) {
            if (!this.isAppended) {
                this.subViews = {
                    workspaces: new views.Treelist(this.parameters, this.dispatcher, data)
                };

                this.wrapper.append(this.subViews.workspaces.el);

                this.isAppended = true;
            }
        }
    });
})();
