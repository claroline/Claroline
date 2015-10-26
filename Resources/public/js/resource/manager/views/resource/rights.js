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
            'click #submit-right-form-button': 'submitWorkspaceRolePermissions',
            'click #search-user-with-rights-btn': 'searchUsersWithRights',
            'click #search-user-without-rights-btn': 'searchUsersWithoutRights',
            'click .pagination > ul > li > a': 'pagination',
            'click th > a': 'reorder',
            'click #add-new-user-rights-btn': 'addUserClick',
            'click #search-workspaces-btn': 'searchWorkspaces'
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
        },
        searchUsersWithRights: function () {
            var search = $('#search-user-with-rights-input').val();
            var nodeId = $('#users-with-rights-datas').attr('data-node-id');

            $.ajax({
                url: Routing.generate(
                    'claro_resources_rights_users_with_rights_form',
                    {'node': nodeId, 'search': search}
                ),
                type: 'GET',
                success: function (datas) {
                    $('#users-with-rights-tab').empty();
                    $('#users-with-rights-tab').append(datas);
                }
            });
        },
        searchWorkspaces: function () {
            var search = $('#search-workspaces-input').val();
            var nodeId = $('#workspaces-datas').data('node-id');
            var max = $('#workspaces-datas').data('max');

            $.ajax({
                url: Routing.generate(
                    'claro_all_workspaces_list_pager_for_resource_rights',
                    {'resource': nodeId, 'wsSearch': search, 'page': 1, 'wsMax': max}
                ),
                type: 'GET',
                success: function (datas) {
                    $('#all-workspaces-panel').empty();
                    $('#all-workspaces-panel').append(datas);
                }
            });
        },
        pagination: function (event) {
            event.preventDefault();
            event.stopPropagation();

            var element = event.currentTarget;
            var url = $(element).attr('href');
            var urlTab = url.split('/');
            var type = this.getUsersListType(urlTab);

            if (url !== '#') {
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (datas) {

                        if (type === 'with') {
                            $('#users-with-rights-tab').empty();
                            $('#users-with-rights-tab').append(datas);
                        } else if (type === 'without') {
                            $('#users-without-rights-tab').empty();
                            $('#users-without-rights-tab').append(datas);
                        } else {
                            $('#all-workspaces-panel').empty();
                            $('#all-workspaces-panel').append(datas);
                        }
                    }
                });
            }
        },
        reorder: function (event) {
            event.preventDefault();
            event.stopPropagation();

            var element = event.currentTarget;
            var url = $(element).attr('href');
            var urlTab = url.split('/');
            var type = this.getUsersListType(urlTab);

            if (url !== '#') {
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (datas) {

                        if (type === 'with') {
                            $('#users-with-rights-tab').empty();
                            $('#users-with-rights-tab').append(datas);
                        } else if (type === 'without') {
                            $('#users-without-rights-tab').empty();
                            $('#users-without-rights-tab').append(datas);
                        }
                    }
                });
            }
        },
        getUsersListType: function (tab) {
            var type;

            for (var i = 0; i < tab.length; i++) {
                if (tab[i] === 'users') {
                    if (typeof(tab[i + 1]) !== 'undefined') {
                        type = tab[i + 1];
                    }
                    break;
                }
            }

            return type;
        },
        addUserClick: function (event) {
            var rights = $('#rights-list').attr('data-rights');
            var isDir = $('#rights-list').attr('data-is-dir');
            var nodeId = $('#rights-list').attr('data-node-id');
            rights = rights.split(',');
            var trimmed = [];

            for (var i = 0; i < rights.length; i++) {
                trimmed.push(rights[i].trim());
            }

            var picker = new UserPicker();
            var settings = {
                'multiple': true,
                'picker_name': 'user_res_picker',
                'return_datas': true
            };
            picker.configure(
                settings,
                function (users) {
                    $.each(users, function(index, val) {
                        console.debug(val);
                        //add the row to the tab
                        var twigParams = {
                            'user': val,
                            'isDir': true,
                            'rights': trimmed,
                            'nodeId': nodeId
                        };

                        var el = Twig.render(ResourceRightsRow, twigParams);
                        $('.rights-single-user').append(el);
                    });
                }
            );
            picker.open();
        }
    });
})();
