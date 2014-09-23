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
    var simpleRights = window.Claroline.SimpleRights;

    Claroline.ResourceManager.Views.Rights = Backbone.View.extend({
        events: {
            'change #simple input': 'checkSimple',
            'change #general input': 'checkAdvanced',
            'click #submit-default-rights-form-button': 'submitSimpleAndAdvanced',
            'click .res-creation-options': 'openCreationOptions',
            'click #form-node-creation-rights button[type=submit]': 'submitCreationOptions',
            'click .workspace-role-item': 'displayWorkspaceRolePermissions',
            'click #submit-right-form-button': 'submitWorkspaceRolePermissions'
        },
        initialize: function (dispatcher) {
            this.dispatcher = dispatcher;
            this.mainElement = null;
            this.defaultRoute = 'claro_resource_right_form';
            this.dispatcher.on('edit-rights', this.render, this);
            this.dispatcher.on('edited-rights', function () {
                this.$el.modal('hide');
            }, this);
            this.dispatcher.on('edited-workspace-rights', function () {
                this.$('#form-rights-tag-wrapper').empty();
            }, this);
            this.dispatcher.on('workspace-role-rights', function (form) {
                this.$('#form-rights-tag-wrapper').empty();
                this.$('#form-rights-tag-wrapper').append(form);
            }, this);
        },
        checkSimple: function (event) {
            var element = event.target;

            switch (this.$(element).attr('id')) {
                case 'everyone':
                    simpleRights.everyone(element);
                    break;
                case 'anonymous':
                    simpleRights.anonymous(element);
                    break;
                case 'workspace':
                    simpleRights.workspace(element);
                    break;
                case 'platform':
                    simpleRights.platform(element);
                    break;
                case 'recursive-option':
                    simpleRights.recursive(element)
                    break;
            }
        },
        checkAdvanced: function (event) {
            simpleRights.checkAll(event.target);
        },
        submitSimpleAndAdvanced: function (event) {
            event.preventDefault();
            this.dispatchSubmit(this.$('form')[0], 'edited-rights');
        },
        openCreationOptions: function (event) {
            event.preventDefault();
            this.render({
                isCreationOptions: true,
                nodeId: event.currentTarget.getAttribute('data-node-id'),
                url: event.currentTarget.getAttribute('href')
            });
        },
        submitCreationOptions: function (event) {
            event.preventDefault();
            this.dispatchSubmit(this.$('form')[0], 'edited-creation-rights');
        },
        displayWorkspaceRolePermissions: function (event) {
            event.preventDefault();
            this.dispatcher.trigger('get-url', {
                url: event.currentTarget.getAttribute('href'),
                onSuccess: 'workspace-role-rights'
            });
        },
        submitWorkspaceRolePermissions: function (event) {
            event.preventDefault();
            this.dispatchSubmit(this.$('form')[1], 'edited-workspace-rights');
        },
        dispatchSubmit: function (form, eventOnSuccess) {
            this.dispatcher.trigger('submit-form', {
                formAction: form.getAttribute('action'),
                formData: new FormData(form),
                eventOnSuccess: eventOnSuccess
            });
        },
        render: function (event) {
            if (!event.isCreationOptions) {
                Claroline.Modal.fromRoute(this.defaultRoute, { node: event.nodeId }, _.bind(function (element) {
                    this.setElement(element);
                    this.mainElement = element;
                    var action = this.$('form').attr('action').replace('_nodeId', event.nodeId);
                    this.$('form').attr('action', action);
                    simpleRights.checkAll(this.$('#general input').first());
                }, this));
            } else {
                Claroline.Modal.fromUrl(event.url, _.bind(function (element) {
                    this.setElement(element);
                    this.$el.on('hidden.bs.modal', _.bind(function () {
                        this.setElement(this.mainElement);
                    }, this));
                }, this));
            }
        }
    });
})();
