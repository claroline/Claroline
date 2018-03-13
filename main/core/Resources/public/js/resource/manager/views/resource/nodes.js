/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global Twig */
/* global ModalWindow */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.ResourceManager = window.Claroline.ResourceManager || {};
    window.Claroline.ResourceManager.Views = window.Claroline.ResourceManager.Views || {};
    var views = window.Claroline.ResourceManager.Views;

    Claroline.ResourceManager.Views.Nodes = Backbone.View.extend({
        className: 'nodes',
        tagName: 'ul',
        attributes: {'id': 'sortable'},
        events: {
            'click .node .clickable-node': 'openNode',
            'click .results table a.result-path': 'openNode',
            'click .node input[type=checkbox]': 'checkNode',
            'click .results table input[type=checkbox]': 'checkNode'
        },
        outerEvents: {
            'directory-data': 'render',
            'created-nodes': 'addNodes',
            'deleted-nodes': 'removeNodes',
            'renamed-node': 'renameNode',
            'edited-node': 'editNode',
            'reload-page': 'reloadPage',
            'published-change-nodes': 'changePublishedNodes'
        },
        initialize: function (parameters, dispatcher) {
            this.parameters = parameters;
            this.dispatcher = dispatcher;
            this.directoryId = '0';
            this.nodes = [];
            this.listMode = parameters.displayMode;
            this.zoomValue = this.parameters.zoom;
            this.dispatcher.on('change-zoom', this.zoom, this);
            _.each(this.outerEvents, function (method, event) {
                this.dispatcher.on(
                    event + '-' + this.parameters.viewName, this[method], this
                );
            }, this);
            this.dispatcher.on('list-mode-' + parameters.viewName, this.setListMode, this)
        },
        addNodes: function (event) {
            _.each(event, function (node) {
                var isWhiteListed = this.parameters.resourceTypes[node.type] !== undefined;

                if (isWhiteListed || node.type === 'directory') {
                    this.nodes[node.id] = node;
                    this.renderNode(node);
                }
            }, this);
        },
        renameNode: function (event) {
            var displayableName = Claroline.Utilities.formatText(event.name, 20, 2);
            this.$('#' + event.id + ' .node-name')
                .html(displayableName + ' ')
                .append($(document.createElement('i'))
                .addClass('fa fa-caret-down'));
            this.$('#' + event.id + ' .dropdown[rel=tooltip]').attr('title', event.name);
        },
        publishNode: function (event) {
            var nodeId = event.id;
            var published = event.published;

            if (published) {
                $('#node-element-' + nodeId).removeClass('unpublished');
            } else {
                $('#node-element-' + nodeId).addClass('unpublished');
            }
        },
        changePublishedNodes: function (event) {
            event.ids.forEach(function (nodeId) {
                if (event.published) {
                    $('#node-element-' + nodeId).removeClass('unpublished');
                } else {
                    $('#node-element-' + nodeId).addClass('unpublished');
                }
            })
        },
        editNode: function (event) {
            this.renameNode(event);
            this.publishNode(event);
            this.$('#node-element-' + event.id).attr(
                'style',
                'background-image:url("' + this.parameters.webPath + '/' + event.large_icon + '");'
            );
            this.$('#node-element-' + event.id).prev('.checkbox-left').children('.node-chk-main').attr(
                'data-deletable',
                event.deletable ? '1' : '0'
            );
        },
        removeNodes: function (event) {
            var ids = event.ids || [event.nodeId];

            for (var i = 0; i < ids.length; ++i) {
                this.$('#' + ids[i]).remove();
                delete this.nodes[ids[i]];
            }
        },
        zoom: function (event) {
            this.zoomValue = event.value;
            _.each(this.$('.node-thumbnail'), function (node) {
                node.className = node.className.replace(/\bzoom\d+/g, event.value);
            });
        },
        openNode: function (event) {
            event.preventDefault();
            var type = event.currentTarget.getAttribute('data-type');
            var eventName = 'open-' + (type === 'directory' ? 'directory' : 'node');

            if (!this.parameters.isPickerMode || type === 'directory') {
                this.nodes = [];
                this.dispatcher.trigger(eventName , {
                    nodeId: event.currentTarget.getAttribute('data-id'),
                    resourceType: type,
                    view: this.parameters.viewName,
                    fromPicker: this.parameters.isPickerMode
                });
            }
        },
        //almost the same as select all in action.js
        checkNode: function (event) {
            if (this.parameters.isPickerMode
                && !this.parameters.isPickerMultiSelectAllowed
                && event.currentTarget.checked) {
                _.each(this.$('input[type=checkbox]'), function (checkbox) {
                    if (checkbox !== event.currentTarget) {
                        checkbox.checked = false;
                    }
                });
            }

            this.dispatcher.trigger('node-check-status-' + this.parameters.viewName, {
                node: {
                    id: event.currentTarget.getAttribute('value'),
                    name: event.currentTarget.getAttribute('data-node-name'),
                    type: event.currentTarget.getAttribute('data-type'),
                    mimeType: event.currentTarget.getAttribute('data-mime-type'),
                    path: event.currentTarget.getAttribute('data-path'),
                    mask: event.currentTarget.getAttribute('data-mask'),
                    deletable: event.currentTarget.getAttribute('data-deletable') !== '0'
                },
                isChecked: event.currentTarget.checked,
                isPickerMode: this.parameters.isPickerMode
            });
        },
        orderNodes: function (event, ui) {
            var ids = this.$el.sortable('toArray');
            var movedNodeId = ui.item.attr('id');
            var index = ids.indexOf(movedNodeId) + 1;
            this.dispatcher.trigger('order-nodes', {
                'nodeId': movedNodeId,
                'index': index
            });
        },
        prepareResults: function (nodes) {
            // exclude blacklisted types
            var displayableNodes = _.reject(nodes, function (node) {
                return this.parameters.resourceTypes[node.type] === undefined;
            }, this);

            // extract nodes id and name from materialized path data
            return _.map(displayableNodes, function (node) {
                node.pathParts = node.path.split('`');
                node.pathParts.pop();
                node.pathParts.pop();
                node.pathParts = _.map(node.pathParts, function (part) {
                    var matches = part.match(/(.+)\-([0-9]+)$/);

                    return {
                        name: matches[1],
                        id: matches[2]
                    }
                });

                return node;
            });
        },
        render: function (event) {
            this.directoryId = event.id;

            if (!event.isSearchMode) {
                this.$el.empty();
                this.addNodes(event.nodes);

                this.$el.sortable({
                    update: _.bind(this.orderNodes, this)
                });

                (!event.canChangePosition || this.parameters.isPickerMode) ?
                    this.$el.sortable('disable'):
                    this.$el.sortable({helper: 'clone'});
            } else {
                this.$el.html(Twig.render(ResourceManagerResults, {
                    'nodes': this.prepareResults(event.nodes),
                    'resourceTypes': this.parameters.resourceTypes
                }));
            }
        },
        setListMode: function (event) {
            this.listMode = event.mode;
            //remove everything from the View
            this.$el.empty();
            var orderedNodesWithoutIndex = [];
            var orderedNodesWithIndex = [];

            var j = 0;
            //first we need to order the nodes !
            for (var i in this.nodes) {

                if (this.nodes[i].index_dir === null) {
                    orderedNodesWithoutIndex[j] = this.nodes[i];
                    j++;
                } else {
                    orderedNodesWithIndex[this.nodes[i].index_dir] = this.nodes[i];
                }
            }
            var orderedNodes = orderedNodesWithoutIndex.concat(orderedNodesWithIndex);
            orderedNodes.forEach(function (node) {
                this.renderNode(node);
            }.bind(this));
        },
        renderNode: function(node) {
            //1023 is the "I can do everything" mask.
            if (this.parameters.restrictForOwner == 1 && node.creator_id != this.parameters.currentUserId && node.type !== 'directory') {
                return;
            }

            var view = (this.listMode === 'list') ?
                new views.ListViewElement(this.parameters, this.dispatcher, this.zoomValue):
                new views.Thumbnail(this.parameters, this.dispatcher, this.zoomValue);

            view.render(node, true && this.directoryId !== '0');
            this.$el.append(view.$el);
        },
        reloadPage: function () {
            window.location.reload();
        }
    });
})();
