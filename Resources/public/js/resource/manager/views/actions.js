/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global Twig */
/* global Translator */
/* global ResourceManagerActions */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.ResourceManager = window.Claroline.ResourceManager || {};
    window.Claroline.ResourceManager.Views = window.Claroline.ResourceManager.Views || {};
    var views = Claroline.ResourceManager.Views;

    views.Actions = Backbone.View.extend({
        className: 'navbar navbar-default navbar-static-top',
        events: {
            'click ul.create li a': 'create',
            'click a.delete': 'delete',
            'click a.download': 'download',
            'click a.copy': 'copy',
            'click a.cut': 'cut',
            'click a.paste': 'paste',
            'click button.config-search-panel': 'toggleFilters',
            'click button.filter': 'filter',
            'keypress input.name': 'filter',
            'click ul.zoom li a': 'zoom',
            'click a.open-picker': 'openPicker',
            'click a.add': 'add'
        },
        initialize: function (parameters, dispatcher) {
            this.parameters = parameters;
            this.dispatcher = dispatcher;
            this.filters = null;
            this.isReadyToPaste = false;
            this.isCutMode = false;
            this.isSearchMode = false;
            this.lastSearchedName = null;
            this.zoomValue = parameters.zoom;
            this.currentDirectoryId = parameters.directoryId;
            // destination directory for picker "add" action
            this.targetDirectoryId = this.currentDirectoryId;
            // selection of nodes checked by the user
            this.checkedNodes = {
                nodes: {},
                directoryId: this.currentDirectoryId,
                isSearchMode: this.isSearchMode
            };
            this.setPasteBinState(false, false);
            this.dispatcher.on('open-directory', this.setTargetDirectory, this);
            this.dispatcher.on('directory-data-' + this.parameters.viewName, this.render, this);
            this.dispatcher.on('node-check-status-' + this.parameters.viewName, this.handleSelection, this);
            this.dispatcher.on('deleted-nodes-' + this.parameters.viewName, this.setInitialState, this);
        },
        create: function (event) {
            var type = event.currentTarget.getAttribute('id');

            if (type === 'resource_shortcut') {
                this.dispatcher.trigger('open-picker-shortcutPicker');
            } else {
                this.dispatcher.trigger('create-form', {
                    action: 'create-form',
                    nodeId: this.currentDirectoryId,
                    resourceType: event.currentTarget.getAttribute('id'),
                    view: this.parameters.viewName
                });
            }
        },
        'delete': function (event) {
            if (!this.$(event.currentTarget).hasClass('disabled')) {
                var trans = _.keys(this.checkedNodes.nodes).length > 1 ?
                    'resources_delete' :
                    'resource_delete';
                this.dispatcher.trigger('confirm', {
                    header: Translator.get('platform:delete'),
                    body: Translator.get('platform:' + trans),
                    callback: _.bind(function () {
                        this.dispatcher.trigger('delete', {
                            ids: _.keys(this.checkedNodes.nodes),
                            view: this.parameters.viewName
                        });
                    }, this)
                });
            }
        },
        'download': function (event) {
            if (!this.$(event.currentTarget).hasClass('disabled')) {
                this.dispatcher.trigger('download', {
                    ids: _.keys(this.checkedNodes.nodes)
                });
            }
        },
        'copy': function (event) {
            if (!this.$(event.currentTarget).hasClass('disabled') && _.size(this.checkedNodes.nodes) > 0) {
                this.setPasteBinState(true, false);
            }
        },
        'cut': function (event) {
            if (!this.$(event.currentTarget).hasClass('disabled') && _.size(this.checkedNodes.nodes) > 0) {
                this.setPasteBinState(true, true);
            }
        },
        'paste': function (event) {
            if (!this.$(event.currentTarget).hasClass('disabled')) {
                var event = this.isCutMode ? 'move-nodes' : 'copy-nodes';
                this.dispatcher.trigger(event, {
                    ids:  _.keys(this.checkedNodes.nodes),
                    directoryId: this.currentDirectoryId,
                    sourceDirectoryId: this.checkedNodes.directoryId,
                    view: this.parameters.viewName
                });
                
                if(this.isCutMode) {
                    // disable cut/copy/paste/delete/download buttons && empty this.checkedNodes.nodes after paste action
                    this.setInitialState();
                }
            }
        },
        'toggleFilters': function () {
            this.initFilters();
            this.filters.toggle();
        },
        filter: function (event) {
            if (event.type === 'keypress' && event.keyCode !== 13) {
                return;
            }

            event.preventDefault();
            this.initFilters();
            var parameters = this.filters.getParameters();
            var name = this.$('.name').val().trim();

            if (name) {
                parameters.name = name;
                this.lastSearchedName = name;
            }

            this.dispatcher.trigger('filter', {
                nodeId: this.currentDirectoryId,
                parameters: parameters,
                view: this.parameters.viewName
            });
        },
        zoom: function (event) {
            this.zoomValue = event.currentTarget.getAttribute('id');
            this.dispatcher.trigger('change-zoom', {
                value: this.zoomValue
            });
            this.$('.dropdown-menu.zoom li').removeClass('active');
            this.$(event.currentTarget).parent().addClass('active');
        },
        openPicker: function () {
            this.dispatcher.trigger('open-picker-defaultPicker');
        },
        add: function (event) {
            if (this.$(event.currentTarget).hasClass('disabled')) {
                return;
            }

            if (this.parameters.viewName === 'defaultPicker') {
                this.dispatcher.trigger('copy-nodes', {
                    ids: _.keys(this.checkedNodes.nodes),
                    directoryId: this.targetDirectoryId,
                    view: 'main'
                });
            } else if (this.parameters.viewName === 'shortcutPicker') {
                this.dispatcher.trigger('create-shortcuts', {
                    ids: _.keys(this.checkedNodes.nodes),
                    directoryId: this.targetDirectoryId,
                    view: 'main'
                });
            } else {
                this.parameters.pickerCallback(this.checkedNodes.nodes, this.currentDirectoryId);
                this.checkedNodes.nodes = {};
            }

            this.dispatcher.trigger('close-picker-' + this.parameters.viewName);
        },
        'initFilters': function () {
            if (!this.filters) {
                this.filters = new views.Filters(this.parameters);
                this.filters.render(this.resourceTypes);
                this.$el.after(this.filters.el);
            }
        },
        setTargetDirectory: function (event) {
            if (event.view === 'main' && this.parameters.isPickerMode) {
                this.targetDirectoryId = event.nodeId;
            }
        },
        handleSelection: function (event) {
            // cancel any previous paste bin state
            if (this.isReadyToPaste) {
                this.setPasteBinState(false, false);
            }
            // cancel any previous selection made in another directory
            // or in a previous search results list
            // or in this directory if we're in picker 'mono-select' mode
            if (this.checkedNodes.directoryId !== this.currentDirectoryId ||
                (this.checkedNodes.isSearchMode && !this.isSearchMode) ||
                (this.parameters.isPickerMode &&
                    !this.parameters.isPickerMultiSelectAllowed &&
                    event.isChecked)) {
                this.checkedNodes.directoryId = this.currentDirectoryId;
                this.checkedNodes.nodes = {};
                this.setPasteBinState(false, false);
            }
            // add the node to the selection or remove it if already present
            if (this.checkedNodes.nodes.hasOwnProperty(event.node.id) && !event.isChecked) {
                delete this.checkedNodes.nodes[event.node.id];
            } else {
                this.checkedNodes.nodes[event.node.id] = [
                    event.node.name,
                    event.node.type,
                    event.node.mimeType
                ];
            }

            this.checkedNodes.directoryId = this.currentDirectoryId;
            this.checkedNodes.isSearchMode = this.isSearchMode;
            this.setActionsEnabledState(event.isPickerMode);
        },
        setPasteBinState: function (isReadyToPaste, isCutMode) {
            this.isReadyToPaste = isReadyToPaste;
            this.isCutMode = isCutMode;
            this.setButtonEnabledState(
                this.$('a.paste'),
                isReadyToPaste && (!this.isCutMode || this.checkedNodes.directoryId !== this.currentDirectoryId)
            );
        },
        setInitialState: function () {
            this.checkedNodes.nodes = {};
            this.isReadyToPaste = false;
            this.isCutMode = false;
            this.setButtonEnabledState(this.$('a.cut'), false);
            this.setButtonEnabledState(this.$('a.copy'), false);
            this.setButtonEnabledState(this.$('a.paste'), false);
            this.setButtonEnabledState(this.$('a.delete'), false);
            this.setButtonEnabledState(this.$('a.download'), false);
        },
        setButtonEnabledState: function (jqButton, isEnabled) {
            return isEnabled ? jqButton.removeClass('disabled') : jqButton.addClass('disabled');
        },
        setActionsEnabledState: function (isPickerMode) {
            var isSelectionNotEmpty = _.size(this.checkedNodes.nodes) > 0;
            // enable picker "add" button on non-root directories if selection is not empty
            if (isPickerMode && (this.currentDirectoryId !== '0' || this.isSearchMode)) {
                this.setButtonEnabledState(this.$('a.add'), isSelectionNotEmpty);
            } else {
                // enable download if selection is not empty
                this.setButtonEnabledState(this.$('a.download'), isSelectionNotEmpty);
                // other actions are only available on non-root directories
                // (so they are available in search mode too, as roots are not displayed in that mode)
                if (this.currentDirectoryId !== '0' || this.isSearchMode) {
                    this.setButtonEnabledState(this.$('a.cut'), isSelectionNotEmpty);
                    this.setButtonEnabledState(this.$('a.copy'), isSelectionNotEmpty);
                    this.setButtonEnabledState(this.$('a.delete'), isSelectionNotEmpty);
                }
            }
        },
        render: function (event) {
            if (event.isSearchMode && !this.isSearchMode) {
                this.checkedNodes.nodes = {};
                this.checkedNodes.isSearchMode = true;
            } else if (!event.isSearchMode && this.isSearchMode) {
                this.lastSearchedName = null;
                this.filters && this.filters.close();
            }

            this.currentDirectoryId = event.id;
            this.isSearchMode = event.isSearchMode;

            var creatableTypes = event.creatableTypes || [];
            var isCreationAllowed = this.currentDirectoryId !== '0'
                && !this.parameters.isPickerMode
                && !this.isSearchMode;
            var isCreateAllowed = isCreationAllowed && _.size(creatableTypes) > 0;
            var isPasteAllowed = isCreationAllowed
                && this.isReadyToPaste
                && (!this.isCutMode || this.checkedNodes.directoryId !== event.id);

            $(this.el).html(Twig.render(ResourceManagerActions, {
                resourceTypes: this.parameters.resourceTypes,
                searchedName: this.lastSearchedName,
                isPickerMode: this.parameters.isPickerMode,
                isSearchMode: this.isSearchMode,
                isAddAllowed: isCreateAllowed,
                isPasteAllowed: isPasteAllowed,
                isCreateAllowed: isCreateAllowed,
                creatableTypes: creatableTypes,
                zoom: this.zoomValue
            }));
        }
    });
})();
