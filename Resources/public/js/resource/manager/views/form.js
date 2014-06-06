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

    Claroline.ResourceManager.Views.Form = Backbone.View.extend({
        events: {
            'change #simple input': function (event) {
                var element = event.target;

                switch ($(element).attr('id')) {
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
            'change #general input': function (event) {
                simpleRights.checkAll(event.target);
            },
            'click #submit-default-rights-form-button': function (event) {
                event.preventDefault();
                var form = $(this.el).find('form')[0];
                this.dispatcher.trigger(this.eventOnSubmit, {
                    action: form.getAttribute('action'),
                    data: new FormData(form),
                    nodeId: this.targetNodeId
                });
            },
            'click .res-creation-options': function (event) {
                event.preventDefault();
                this.render(
                    event.currentTarget.getAttribute('href'),
                    event.currentTarget.getAttribute('data-node-id'),
                    'edit-rights-creation'
                );
            },
            'click .search-role-btn': function (event) {
                event.preventDefault();
                var search = $('#role-search-text').val();
                $.ajax({
                    url: routing.generate('claro_resource_find_role_by_code', {'code': search}),
                    type: 'GET',
                    context: this,
                    processData: false,
                    contentType: false,
                    success: function (workspaces) {
                        $('#form-right-wrapper').empty();
                        $('#role-list').empty();
                        $('#role-list').append(Twig.render(resourceRightsRoles,
                            {'workspaces': workspaces, 'nodeId': this.targetNodeId})
                        );
                        $('#role-search-text').val(search);
                    }
                });
            },
            'click .role-item': function (event) {
                event.preventDefault();
                $.ajax({
                    context: this,
                    url: event.currentTarget.getAttribute('href'),
                    type: 'POST',
                    processData: false,
                    contentType: false,
                    success: function (form) {
                        $('#role-list').empty();
                        $('#form-right-wrapper').append(form);
                    }
                });
            },
            'click .workspace-role-item': function (event) {
                event.preventDefault();
                $.ajax({
                    context: this,
                    url: event.currentTarget.getAttribute('href'),
                    type: 'POST',
                    processData: false,
                    contentType: false,
                    success: function (form) {
                        $('#form-rights-tag-wrapper').empty();
                        $('#form-rights-tag-wrapper').append(form);
                    }
                });
            },
            'click .modal-close': function () {
                $('#modal-check-role').empty();
                $('#rights-form-resource-tab-content').css('display', 'block');
                $('#rights-form-resource-nav-tabs').css('display', 'block');
            },
            'click #submit-right-form-button': function (event) {
                event.preventDefault();
                var form = $(this.el).find('form')[1];
                var data = new FormData(form);
                $.ajax({
                    url: form.getAttribute('action'),
                    context: this,
                    data: data,
                    type: 'POST',
                    processData: false,
                    contentType: false,
                    success: function (newrow) {
                        $('#form-right-wrapper').empty();
                        $('#perms-table').append(newrow);
                        $('#modal-check-role').empty();
                        $('#modal-check-node-right-box .modal').modal('hide');
                        $('#rights-form-node-tab-content').css('display', 'block');
                        $('#rights-form-node-nav-tabs').css('display', 'block');
                    }
                });
            },
            'submit form': function (event) {
                event.preventDefault();
                var form = this.$el.find('form');
                this.dispatcher.trigger(this.eventOnSubmit, {
                    action: form.attr('action'),
                    data: new FormData(form[0]),
                    nodeId: this.targetNodeId
                });
            }
        },
        initialize: function (dispatcher) {
            this.dispatcher = dispatcher;
            this.targetNodeId = null;
            this.eventOnSubmit = '';
            this.on('close', this.close, this);
        },
        close: function () {
            this.$el.modal('hide');
        },
        replace: function (content) {
            this.$el.html(content);
        },
        replaceId: function (id) {
            $('form', this.$el).attr('action', $('form', this.$el).attr('action').replace('_nodeId', id));
        },
        render: function (url, targetNodeId, eventOnSubmit) {
            this.targetNodeId = targetNodeId;
            this.eventOnSubmit = eventOnSubmit;
            var that = this;
            modal.fromUrl(url, function (element) {
                that.$el = element;
                that.el = element.get();
                that.replaceId(targetNodeId);
                that.delegateEvents(that.events);
                simpleRights.checkAll($('#general input', that.el).first());
            });
        }
    });
})();
