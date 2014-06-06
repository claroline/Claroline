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

    Claroline.ResourceManager.Views.Form = Backbone.View.extend({
        events: {
            'submit form': 'submit'
        },
        knownActions: {
            'rename': {
                route: 'claro_resource_rename_form',
                onSuccess: 'renamed-node'
            },
            'edit-properties': {
                route: 'claro_resource_form_properties',
                onSuccess: 'edited-node'
            }
        },
        initialize: function (dispatcher) {
            this.dispatcher = dispatcher;
            this.targetNodeId = null;
            this.eventOnSuccess = null;
            _.each(_.keys(this.knownActions), function (eventName) {
                this.dispatcher.on(eventName, this.render, this);
            }, this);
            this.dispatcher.on('error-form', this.render, this);
            this.dispatcher.on('submit-success', function () {
                this.$el.modal('hide');
            }, this);
        },
        submit: function (event) {
            event.preventDefault();
            var form = this.$('form');
            this.dispatcher.trigger('submit-form', {
                formAction: form.attr('action'),
                formData: new FormData(form[0]),
                targetNodeId: this.targetNodeId,
                eventOnSuccess: this.eventOnSuccess
            });
        },
        replaceId: function (id) {
            var action = this.$('form').attr('action').replace('_nodeId', id);
            this.$('form').attr('action', action);
        },
        render: function (event) {
            this.targetNodeId = event.nodeId;
            this.eventOnSuccess = this.knownActions[event.action].onSuccess + '-' + event.view;

            if (!event.errorForm) {
                var route = this.knownActions[event.action].route;
                var parameters = {
                    node: this.targetNodeId
                };
                Claroline.Modal.fromRoute(route, parameters, _.bind(function (element) {
                    this.setElement(element);
                    this.replaceId(this.targetNodeId);
                }, this));
            } else {
                this.$el.html(event.errorForm);
                this.replaceId(this.targetNodeId);
            }
        }
    });
})();
