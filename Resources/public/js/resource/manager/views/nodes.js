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

    Claroline.ResourceManager.Views.Nodes = Backbone.View.extend({
        className: 'nodes',
        tagName: 'ul',
        attributes: {'id': 'sortable'},
        events: {
            'click .node-thumbnail .node-element': 'dispatchOpen',
            'click .node-thumbnail input[type=checkbox]': 'dispatchCheck',
            'click .results table a.node-link': 'dispatchOpen',
            'click .results table input[type=checkbox]': 'dispatchCheck'
        },
        initialize: function (parameters, dispatcher) {
            this.parameters = parameters;
            this.dispatcher = dispatcher;
            this.directoryId = parameters.directoryId;
        },
        addThumbnails: function (nodes, successHandler) {
            _.each(nodes, function (node) {
                var thumbnail = new manager.Views.Thumbnail(this.parameters, this.dispatcher);
                thumbnail.render(node, this.directoryId !== '0' && !this.parameters.isPickerMode, true);
                this.$el.append(thumbnail.$el);
            }, this);

            if (successHandler) {
                successHandler();
            }
        },
        renameThumbnail: function (nodeId, newName, successHandler) {
            var displayableName = Claroline.Utilities.formatText(newName, 20, 2);
            this.$('#' + nodeId + ' .node-name')
                .html(displayableName + ' ').append($(document.createElement('i')).addClass('icon-caret-down'));
            this.$('#' + nodeId + ' .dropdown[rel=tooltip]').attr('title', newName);

            if (successHandler) {
                successHandler();
            }
        },
        changeThumbnailIcon: function (nodeId, newIconPath, successHandler) {
            this.$('#node-element-' + nodeId).attr(
                'style', 'background-image:url("' + this.parameters.webPath + newIconPath + '");'
            );
            //console.debug(this.parameters.webPath + newIconPath);
            if (successHandler) {
                successHandler();
            }
        },
        removeResources: function (nodeIds) {
            // same logic for both thumbnails and search results
            for (var i = 0; i < nodeIds.length; ++i) {
                this.$('#' + nodeIds[i]).remove();
            }
        },
        dispatchOpen: function (event) {
            event.preventDefault();
            var type = event.currentTarget.getAttribute('data-type');
            var eventName = 'open-' + (type === 'directory' ? 'directory' : 'node');
            this.dispatcher.trigger(eventName , {
                nodeId: event.currentTarget.getAttribute('data-id'),
                resourceType: type,
                isPickerMode: this.parameters.isPickerMode,
                directoryHistory: this.parameters.directoryHistory,
                view: this.parameters.viewName
            });
        },
        dispatchCheck: function (event) {
            if (this.parameters.isPickerMode &&
                !this.parameters.isPickerMultiSelectAllowed &&
                event.currentTarget.checked) {
                _.each(this.$('input[type=checkbox]'), function (checkbox) {
                    if (checkbox !== event.currentTarget) {
                        checkbox.checked = false;
                    }
                });
            }

            this.dispatcher.trigger('node-check-status', {
                node: {
                    id: event.currentTarget.getAttribute('value'),
                    name: event.currentTarget.getAttribute('data-node-name'),
                    type: event.currentTarget.getAttribute('data-type'),
                    mimeType: event.currentTarget.getAttribute('data-mime-type')

                },
                isChecked: event.currentTarget.checked,
                isPickerMode: this.parameters.isPickerMode
            });
        },
        render: function (nodes, isSearchMode, directoryId) {
            this.directoryId = directoryId;
            this.$el.empty();

            if (isSearchMode) {
                $(this.el).html(Twig.render(ResourceManagerResults, {
                    'nodes': nodes,
                    'resourceTypes': this.parameters.resourceTypes
                }));
            } else {
                _.each(nodes, function (node) {
                    var thumbnail = new Claroline.ResourceManager.Views.Thumbnail(this.parameters, this.dispatcher);
                    thumbnail.render(
                        node,
                        directoryId !== '0' || !this.parameters.isPickerMode,
                        directoryId !== '0' && !this.parameters.isPickerMode
                    );
                    $(this.el).append(thumbnail.$el);
                }, this);
            }
        },
        uncheckAll: function () {
            _.each(this.$('input[type=checkbox]'), function (checkbox) {
                checkbox.checked = false;
            });
        }
    });
})();
