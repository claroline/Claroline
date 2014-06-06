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
        this.dispatcher.on('create-node',           _.bind(this.create, this));
        this.dispatcher.on('order-node',            _.bind(this.order, this));
        this.dispatcher.on('rename-node',           _.bind(this.rename, this));
        this.dispatcher.on('open-node',             _.bind(this.open, this));
        this.dispatcher.on('open-node-tracking',    _.bind(this.openTracking, this));
        this.dispatcher.on('open-directory',        _.bind(this.openDirectory, this));
        this.dispatcher.on('edit-properties',       _.bind(this.editProperties, this));
        this.dispatcher.on('edit-rights',           _.bind(this.editRights, this));
        this.dispatcher.on('edit-creation-rights',  _.bind(this.editCreationRights, this));
        this.dispatcher.on('custom-node-action',    _.bind(this.customAction, this));
        this.dispatcher.on('remove-nodes',          _.bind(this.remove, this));
        this.dispatcher.on('copy-nodes',            _.bind(this.copy, this));
        this.dispatcher.on('move-nodes',            _.bind(this.move, this));
        this.dispatcher.on('download-nodes',        _.bind(this.download, this));
        this.dispatcher.on('create-shortcuts',      _.bind(this.createShortcuts, this));

        $.ajaxSetup({
            headers: {'X_Requested_With': 'XMLHttpRequest'},
            context: this
        });
    };

    server.prototype.create = function (event) {
        $.ajax({
            url: event.formAction,
            data: event.formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function (data, textStatus, jqXHR) {
                if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                    this.dispatcher.trigger('created-nodes', {
                        nodes: data
                    });
                } else {
                    this.dispatcher.trigger('node-creation-error', {
                        errorForm: data,
                        parentDirectoryId: event.parentDirectoryId
                    });
                }
            }
        });
    };

    server.prototype.order = function (event) {
        $.ajax({
            url: Routing.generate('claro_resource_insert_before', {
                'node': event.movedNode,
                'nextId': event.nextId
            })
        });
    };

    server.prototype.rename = function (event) {
        $.ajax({
            url: event.formAction,
            data: event.formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function (data, textStatus, jqXHR) {
                if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                    this.dispatcher.trigger('renamed-node', {
                        nodeId: event.nodeId,
                        nodeName: data[0]
                    });
                } else {
                    this.dispatcher.trigger('node-renaming-error', {
                        errorForm: data,
                        nodeId: event.nodeId
                    });
                }
            }
        });
    };

    server.prototype.open = function (event) {
        var route = Routing.generate('claro_resource_open', {
            resourceType: event.resourceType,
            node: event.nodeId
        });
        window.location = route + '?' + event.breadcrumbQuery;
    };

    server.prototype.openTracking = function (event) {
        window.location = Routing.generate('claro_resource_logs', {
            node: event.nodeId
        });
    };

    server.prototype.openDirectory = function (event) {
        $.ajax({
            url: Routing.generate('claro_resource_directory', {
                nodeId: event.nodeId
            }),
            success: function (response) {
                this.dispatcher.trigger('directory-data-' + event.view, {
                    data: response
                });
            }
        });
    };

    server.prototype.editProperties = function (event) {
        $.ajax({
            url: event.formAction,
            data: event.formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function (data, textStatus, jqXHR) {
                if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                    if (data[0].name) {
                        this.dispatcher.trigger('renamed-node', {
                            nodeId: event.nodeId,
                            nodeName: data[0].name
                        });
                    }

                    if (data[0].large_icon) {
                        this.dispatcher.trigger('changed-node-icon', {
                            nodeId: event.nodeId,
                            nodeIcon: data[0].large_icon
                        });
                    }
                } else {
                    this.dispatcher.trigger('node-properties-error', {
                        errorForm: data,
                        nodeId: event.nodeId
                    });
                }
            }
        });
    };

    server.prototype.editRights = function (event) {
        $.ajax({
            url: event.formAction,
            data: event.formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function () {
                this.dispatcher.trigger('edited-rights', {});
            }
        });
    };

    server.prototype.editCreationRights = function (event) {
        $.ajax({
            url: event.formAction,
            data: event.formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function () {
                this.dispatcher.trigger('edited-creation-rights', {});
            }
        });
    };

    server.prototype.customAction = function (event) {
        window.location = Routing.generate('claro_resource_action', {
            action: event.action,
            node: event.nodeId
        });
    };

    server.prototype.remove = function (event) {
        $.ajax({
            url: Routing.generate('claro_resource_delete', {}),
            data: {
                ids: event.nodeIds
            },
            success: function () {
                this.dispatcher.trigger('deleted-nodes', {
                   nodes: event.nodeIds
                });
            }
        });
    };

    server.prototype.copy = function (event) {
        $.ajax({
            url: Routing.generate('claro_resource_copy', {
               parent: event.directoryId
            }),
            data: {
                ids: event.nodeIds
            },
            success: function (data, textStatus, jqXHR) {
                if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                    this.dispatcher.trigger('created-nodes', {
                        nodes: data
                    });
                }
            }
        });
    };

    server.prototype.move = function (event) {
        $.ajax({
            url: Routing.generate('claro_resource_move', {
                newParent: event.newParentDirectoryId
            }),
            data: {
                ids: event.nodeIds
            },
            success: function (data) {
                this.dispatcher.trigger('created-nodes', {
                    nodes: data
                });
            }
        });
    };

    server.prototype.download = function (event) {
        var route = Routing.generate('claro_resource_download', {});
        window.location = route + '?' + $.param({ ids: event.nodeIds });
    };

    server.prototype.createShortcuts = function (event) {
        $.ajax({
            url: Routing.generate('claro_resource_create_shortcut', {
                parent: event.parentId
            }),
            data: {
                ids: event.nodeIds
            },
            success: function (data) {
                this.dispatcher.trigger('created-nodes', {
                    nodes: data
                });
            }
        });
    };
})();
