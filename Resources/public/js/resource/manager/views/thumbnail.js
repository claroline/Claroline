/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global Twig */
/* global ResourceManagerThumbnail */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.ResourceManager = window.Claroline.ResourceManager || {};
    window.Claroline.ResourceManager.Views = window.Claroline.ResourceManager.Views || {};

    Claroline.ResourceManager.Views.Thumbnail = Backbone.View.extend({
        className: 'node-thumbnail node ui-state-default',
        tagName: 'li',
        events: {
            'click .node-menu-action': function (event) {
                event.preventDefault();
                var action = event.currentTarget.getAttribute('data-action');
                var actionType = event.currentTarget.getAttribute('data-action-type');
                var nodeId = event.currentTarget.getAttribute('data-id');

                if (actionType === 'display-form') {
                    this.dispatcher.trigger('display-form', {type: action, node : {id: nodeId}});
                } else {
                    if (event.currentTarget.getAttribute('data-is-custom') === 'no') {
                        this.dispatcher.trigger(action, {ids: [nodeId]});
                    } else {
                        var async = event.currentTarget.getAttribute('data-async');
                        var redirect = (async === '1') ? false : true;
                        this.dispatcher.trigger('custom', {'action': action, id: [nodeId], 'redirect': redirect});
                    }
                }
            }
        },
        initialize: function (parameters, dispatcher) {
            this.parameters = parameters;
            this.dispatcher = dispatcher;
        },
        render: function (node, isSelectionAllowed, hasMenu) {
            this.el.id = node.id;
            $(this.el).addClass(this.parameters.zoom);
            node.displayableName = Claroline.Utilities.formatText(node.name, 20, 2);
            this.el.innerHTML = Twig.render(ResourceManagerThumbnail, {
                'node': node,
                'isSelectionAllowed': isSelectionAllowed,
                'hasMenu': hasMenu,
                'actions': this.parameters.resourceTypes[node.type].actions || {},
                'webRoot': this.parameters.webPath
            });
        }
    });
})();
