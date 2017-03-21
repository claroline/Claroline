/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global Twig */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.ResourceManager = window.Claroline.ResourceManager || {};
    window.Claroline.ResourceManager.Views = window.Claroline.ResourceManager.Views || {};

    Claroline.ResourceManager.Views.Breadcrumbs = Backbone.View.extend({
        tagName: 'ul',
        className: 'breadcrumb',
        events: {
            'click a.node': 'open'
        },
        initialize: function (parameters, dispatcher) {
            this.parameters = parameters;
            this.dispatcher = dispatcher;
            this.rootBreadcrumbs = [];
            this.dispatcher.on(
                'directory-data-' + this.parameters.viewName, this.render, this
            );

            if (this.parameters.breadcrumbElement) {
                this.reuseElement();
            }
        },
        reuseElement: function () {
            this.setElement(this.parameters.breadcrumbElement);
            this.rootBreadcrumbs = this.$('li').toArray(); // extract existing breadcrumbs
            this.rootBreadcrumbs.pop(); // without 'Resources' segment
            this.rootBreadcrumbs = _.map(this.rootBreadcrumbs, function (element) {
                return element.outerHTML;
            });
        },
        open: function (event) {
            event.preventDefault();
            this.dispatcher.trigger('open-directory', {
                nodeId: event.currentTarget.getAttribute('data-node-id'),
                view: this.parameters.viewName,
                fromPicker: this.parameters.isPickerMode
            });
        },
        render: function (event) {
            if (this.parameters.isWorkspace && !this.parameters.isPickerMode) {
                // remove workspace part (already in root breadcrumbs) but keep it
                // for the root id reference (i.e. 'Resources' segment target)
                var root = event.path.shift();
            }

            this.$el.html(Twig.render(ResourceManagerBreadcrumbs, {
                'rootBreadcrumbs': this.rootBreadcrumbs,
                'rootNodeId': root ? root.id : '0',
                'nodes': event.path
            }));
        }
    });
})();
