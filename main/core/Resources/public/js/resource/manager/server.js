/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global Routing */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.ResourceManager = window.Claroline.ResourceManager || {};

    var server = Claroline.ResourceManager.Server = function (dispatcher) {
        this.dispatcher = dispatcher;
        this.preFetchedDirectory = null;
        this.outerEvents = {
            'open-directory': 'openDirectory',
            'submit-form': 'submit',
            'download': 'download',
            'open-tracking': 'openTracking',
            'delete': 'delete',
            'open-node': 'open',
            'copy-nodes': 'copy',
            'move-nodes': 'move',
            'order-nodes': 'order',
            'list-mode': 'listMode',
            'filter': 'filter',
            'change-zoom': 'zoom',
            'custom-action': 'customAction',
            'create-shortcuts': 'createShortcuts',
            'get-url': 'get',
            'get-workspace-list': 'getWorkspaces',
            'get-tab-list': 'getVisibleTabsForWorkspace',
            'get-widget-list': 'getVisibleWidgetsForTabAndWorkspace',
            'export': 'export',
            'publish': 'publish',
            'unpublish': 'unpublish'
        };
        _.each(this.outerEvents, function (method, event) {
            this.dispatcher.on(event, this[method], this);
        }, this);
        $.ajaxSetup({
            headers: {'X_Requested_With': 'XMLHttpRequest'},
            context: this
        });
    };

    server.prototype.setPreFetchedDirectory = function (directory) {
        this.preFetchedDirectory = directory;
    };

    server.prototype.openDirectory = function (event) {
        var eventName = 'directory-data-' + event.view;

        if (this.preFetchedDirectory && this.preFetchedDirectory.id == event.nodeId) {
            this.preFetchedDirectory.isSearchMode = false;
            this.dispatcher.trigger(eventName, this.preFetchedDirectory);
        } else {
            var url = Routing.generate('claro_resource_directory', {
                nodeId: event.nodeId
            });

            if (event.fromPicker) {
                this.dispatcher.trigger('save-picker-directory', {
                    directoryId: event.nodeId
                });
                url += '?keep-id';
            }

            $.ajax({
                url: url,
                success: function (data) {
                    data.isSearchMode = false;
                    this.dispatcher.trigger(eventName, data);
                }
            });
        }

        this.preFetchedDirectory = null;
    };

    server.prototype.getWorkspaces = function () {
        var url = Routing.generate('claro_workspace_by_user_picker');

        $.ajax({
            url: url,
            success: function (data) {
                data.type = "workspace";
                data.acceptsType = "tab";
                data.parents = {};
                this.dispatcher.trigger('workspace-list-returned', data);
            }
        });
    };

    server.prototype.getVisibleTabsForWorkspace = function (event) {
        var url = Routing.generate('claro_list_visible_workspace_home_tabs_picker', {
            'workspaceId': event.workspace
        });

        $.ajax({
            url: url,
            success: function (data) {
                data.type = "tab";
                data.acceptsType = "widget";
                data.parents = {"workspace": event.workspace};
                this.dispatcher.trigger('tab-list-returned-'+event.workspace, data);
            }
        });
    };

    server.prototype.getVisibleWidgetsForTabAndWorkspace = function (event) {
        var url = Routing.generate('claro_workspace_home_tab_widget_list_picker', {
            'workspaceId': event.workspace,
            'homeTabId': event.tab
        });

        $.ajax({
            url: url,
            success: function (data) {
                data.type = "widget";
                data.acceptsType = null;
                data.parents = {'workspace': event.workspace, "tab": event.tab};
                this.dispatcher.trigger('widget-list-returned-'+event.workspace+'-'+event.tab, data);
            }
        });
    };

    server.prototype.submit = function (event) {
        $.ajax({
            url: event.formAction,
            data: event.formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function (data, textStatus, jqXHR) {
                if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                    this.dispatcher.trigger(event.eventOnSuccess, data);
                    this.dispatcher.trigger('submit-success');
                } else {
                    event.errorForm = data;
                    this.dispatcher.trigger('error-form', event);
                }
            }
        });
    };

    server.prototype.delete = function (event) {
        $.ajax({
            url: Routing.generate('claro_resource_delete', {}),
            data: {
                ids: event.ids || [event.nodeId]
            },
            success: function () {
                this.dispatcher.trigger('deleted-nodes-' + event.view, {
                    ids: event.ids || [event.nodeId]
                });
                this.dispatcher.trigger('close-confirm');
            }
        });
    };

    server.prototype.copy = function (event) {
        $.ajax({
            url: Routing.generate('claro_resource_copy', {
                parent: event.directoryId
            }),
            data: {
                ids: event.ids
            },
            success: function (data, textStatus, jqXHR) {
                if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                    this.dispatcher.trigger('created-nodes-' + event.view, data);
                }
            }
        });
    };

    server.prototype.move = function (event) {
        $.ajax({
            url: Routing.generate('claro_resource_move', {
                newParent: event.directoryId
            }),
            data: {
                ids: event.ids
            },
            success: function (data) {
                this.dispatcher.trigger('created-nodes-' + event.view, data);
            }
        });
    };

    server.prototype.filter = function (event) {
        $.ajax({
            url: Routing.generate('claro_resource_filter', {
                nodeId: event.nodeId
            }),
            data: event.parameters,
            success: function (data) {
                data.isSearchMode = true;
                this.dispatcher.trigger('directory-data-' + event.view, data);
            }
        });
    };

    server.prototype.order = function (event) {
        $.ajax({
            url: Routing.generate('claro_resource_insert_at', {
                'node': event.nodeId,
                'index': event.index
            })
        });
    };

    server.prototype.zoom = function (event) {
        $.ajax({
            url: Routing.generate('claro_resource_change_zoom', {
                'zoom': event.value
            })
        });
    };

    server.prototype.fetchManagerParameters = function (callback) {
        $.ajax({
            url: Routing.generate('claro_resource_manager_parameters'),
            success: callback
        });
    };

    server.prototype.createShortcuts = function (event) {
        $.ajax({
            url: Routing.generate('claro_resource_create_shortcut', {
                parent: event.directoryId
            }),
            data: {
                ids: event.ids
            },
            success: function (data) {
                this.dispatcher.trigger('created-nodes-' + event.view, data);
            }
        });
    };

    server.prototype.get = function (event) {
        $.ajax({
            url: event.url,
            success: function (data) {
                this.dispatcher.trigger(event.onSuccess, data);
            }
        });
    };

    server.prototype.open = function (event) {
        window.location = Routing.generate('claro_resource_open', {
            resourceType: event.resourceType,
            node: event.nodeId
        });
    };

    server.prototype.download = function (event) {
        var route = Routing.generate('claro_resource_download', {});
        var ids = event.ids || [event.nodeId];
        window.location = route + '?' + $.param({ ids: ids });
    };

    server.prototype.openTracking = function (event) {
        window.location = Routing.generate('claro_resource_logs', {
            node: event.nodeId
        });
    };

    server.prototype.customAction = function (event) {
        window.location = Routing.generate('claro_resource_action', {
            action: event.action,
            node: event.nodeId
        });
    };

    server.prototype.listMode = function (event) {
        this.dispatcher.trigger('list-mode-' + event.viewName, {mode: event.mode});
    };

    server.prototype.export = function (event) {
        var route = Routing.generate('claro_resource_export', {});
        var ids = event.ids || [event.nodeId];
        window.location = route + '?' + $.param({ ids: ids });
    };

    server.prototype.publish = function (event) {
        $.ajax({
            url: Routing.generate('claro_resource_publish', {}),
            data: {
                ids: event.ids || [event.nodeId]
            },
            success: function () {
                this.dispatcher.trigger('published-change-nodes-' + event.view, {
                    ids: event.ids || [event.nodeId],
                    published: true
                });
                this.dispatcher.trigger('close-confirm');
            }
        });
    };

    server.prototype.unpublish = function (event) {
        $.ajax({
            url: Routing.generate('claro_resource_unpublish', {}),
            data: {
                ids: event.ids || [event.nodeId]
            },
            success: function () {
                this.dispatcher.trigger('published-change-nodes-' + event.view, {
                    ids: event.ids || [event.nodeId],
                    published: false
                });
                this.dispatcher.trigger('close-confirm');
            }
        });
    };
})();
