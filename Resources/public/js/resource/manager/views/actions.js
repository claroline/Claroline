/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global Twig */
/* global ResourceManagerActions */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.ResourceManager = window.Claroline.ResourceManager || {};
    window.Claroline.ResourceManager.Views = window.Claroline.ResourceManager.Views || {};

    Claroline.ResourceManager.Views.Actions = Backbone.View.extend({
        className: 'navbar navbar-default navbar-static-top',
        events: {
            'keypress input.name': function (event) {
                if (event.keyCode !== 13) {
                    return;
                }

                event.preventDefault();

                this.filter();
            },
            'click button.filter': 'filter',
            'click ul.create li a': function (event) {
                event.preventDefault();
                this.dispatcher.trigger('display-form', {
                    type: 'create',
                    node: {
                        type: event.currentTarget.getAttribute('id'),
                        id: this.currentDirectory.id
                    }
                });
            },
            'click ul.zoom li a': function (event) {
                event.preventDefault();

                if ($('.node-thumbnail').get(0) !== undefined) {
                    var zoom = event.currentTarget.getAttribute('id');
                    var currentZoom = $('.node-thumbnail').get(0).className.match(/\bzoom\d+/g);
                    this.parameters.zoom = zoom;

                    $.ajax({
                        url: routing.generate('claro_resource_change_zoom', {'zoom': zoom}),
                        type: 'GET',
                        success: function () {
                            $('.dropdown-menu.zoom li').removeClass('active');
                            $(event.currentTarget).parent().addClass('active');
                            $('.node-thumbnail').each(function () {
                                for (var i in currentZoom) {
                                    if (currentZoom.hasOwnProperty(i)) {
                                        $(this).removeClass(currentZoom[i]);
                                    }
                                }
                                $(this).addClass(zoom);
                            });
                        }
                    });
                }
            },
            'click a.delete': function () {
                if (!(this.$('a.delete').hasClass('disabled'))) {
                    this.dispatcher.trigger('delete', {ids: _.keys(this.checkedNodes.nodes)});
                }
            },
            'click a.download': function () {
                if (!(this.$('a.download').hasClass('disabled'))) {
                    this.dispatcher.trigger('download', {ids: _.keys(this.checkedNodes.nodes)});
                }
            },
            'click a.copy': function () {

                if (!(this.$('a.copy').hasClass('disabled')) && _.size(this.checkedNodes.nodes) > 0) {
                    this.setPasteBinState(true, false);
                }
            },
            'click a.cut': function () {

                if (!(this.$('a.cut').hasClass('disabled')) && _.size(this.checkedNodes.nodes) > 0) {
                    this.setPasteBinState(true, true);
                }
            },
            'click a.paste': function () {
                if (!(this.$('a.paste').hasClass('disabled'))) {
                    this.dispatcher.trigger('paste', {
                        ids:  _.keys(this.checkedNodes.nodes),
                        isCutMode: this.isCutMode,
                        directoryId: this.currentDirectory.id,
                        sourceDirectoryId: this.checkedNodes.directoryId
                    });
                }
            },
            'click a.open-picker': function () {
                this.dispatcher.trigger('picker-action', {
                    name: 'defaultPicker',
                    action: 'open'
                });
            },
            'click button.config-search-panel': function () {
                if (!this.filters) {
                    this.filters = new manager.Views.Filters(
                        this.parameters,
                        this.dispatcher,
                        this.currentDirectory
                    );
                    this.filters.render(this.resourceTypes);
                    $(this.el).after(this.filters.el);
                }

                this.filters.toggle();
            },
            'click a.add': function (event) {
                if (/disabled/.test(event.currentTarget.className)) {
                    return;
                }

                console.log(this.parameters)

                if (this.parameters.pickerCallback) {

                    this.parameters.pickerCallback(this.checkedNodes.nodes, this.currentDirectory.id);
                } else {

                    console.log('NO picker cb')

                    this.dispatcher.trigger('paste', {
                        ids: _.keys(this.checkedNodes.nodes),
                        directoryId: this.targetDirectoryId,
                        isCutMode: false
                    });
                }

//                    if (this.parameters.isPickerOnly) {
//                        this.parameters.pickerCallback(this.checkedNodes.nodes, this.currentDirectory.id);
//                    } else {
//                        if (this.callback) {
//                            this.callback(_.keys(this.checkedNodes.nodes), this.targetDirectoryId);
//                        } else {
//                            this.dispatcher.trigger('paste', {
//                                ids: _.keys(this.checkedNodes.nodes),
//                                directoryId: this.targetDirectoryId,
//                                isCutMode: false
//                            });
//                        }
//                    }

                this.dispatcher.trigger('picker', {action: 'close'});
            }
        },
        filter: function () {
            var searchParameters = {};
            var name = this.$('.name').val().trim();
            var dateFrom = $('input.date-from').first().val();
            var dateTo = $('input.date-to').first().val();
            var types = $('select.node-types').val();

            if (name) {
                searchParameters.name = name;
            }

            if (dateFrom) {
                searchParameters.dateFrom = dateFrom + ' 00:00:00';
            }

            if (dateTo) {
                searchParameters.dateTo = dateTo + ' 23:59:59';
            }

            if (types) {
                searchParameters.types = types;
            }

            if (this.currentDirectory.id !== '0') {
                searchParameters.roots = [this.currentDirectory.path];
            }

            this.dispatcher.trigger('filter', {
                isPickerMode: this.parameters.isPickerMode,
                directoryId: this.currentDirectory.id,
                parameters: searchParameters
            });
        },
        initialize: function (parameters, dispatcher) {
            this.parameters = parameters;
            this.dispatcher = dispatcher;
            this.isSearchMode = false;
            this.currentDirectory = {id: parameters.directoryId};
            // destination directory for picker "add" action
            this.targetDirectoryId = this.currentDirectory.id;
            // selection of nodes checked by the user
            this.checkedNodes = {
                nodes: {},
                directoryId: parameters.directoryId,
                isSearchMode: false
            };
            this.setPasteBinState(false, false);
            // if a node has been (un-)checked
            this.dispatcher.on('node-check-status', function (event) {
                // if the node belongs to this view instance
                if (event.isPickerMode === this.parameters.isPickerMode) {
                    // cancel any previous paste bin state
                    if (this.isReadyToPaste) {
                        this.setPasteBinState(false, false);
                    }
                    // cancel any previous selection made in another directory
                    // or in a previous search results list
                    // or in this directory if we're in picker 'mono-select' mode
                    if (this.checkedNodes.directoryId !== this.currentDirectory.id ||
                        (this.checkedNodes.isSearchMode && !this.isSearchMode) ||
                        (this.parameters.isPickerMode &&
                            !this.parameters.isPickerMultiSelectAllowed &&
                            event.isChecked)) {
                        this.checkedNodes.directoryId = this.currentDirectory.id;
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

                    this.checkedNodes.directoryId = this.currentDirectory.id;
                    this.checkedNodes.isSearchMode = this.isSearchMode;
                    this.setActionsEnabledState(event.isPickerMode);
                }
            }, this);
        },
        setButtonEnabledState: function (jqButton, isEnabled) {
            return isEnabled ? jqButton.removeClass('disabled') : jqButton.addClass('disabled');
        },
        setActionsEnabledState: function (isPickerMode) {
            var isSelectionNotEmpty = _.size(this.checkedNodes.nodes) > 0;
            // enable picker "add" button on non-root directories if selection is not empty
            if (isPickerMode && (this.currentDirectory.id !== '0' || this.isSearchMode)) {
                this.setButtonEnabledState(this.$('a.add'), isSelectionNotEmpty);
            } else {
                // enable download if selection is not empty
                this.setButtonEnabledState(this.$('a.download'), isSelectionNotEmpty);
                // other actions are only available on non-root directories
                // (so they are available in search mode too, as roots are not displayed in that mode)
                if (this.currentDirectory.id !== '0' || this.isSearchMode) {
                    this.setButtonEnabledState(this.$('a.cut'), isSelectionNotEmpty);
                    this.setButtonEnabledState(this.$('a.copy'), isSelectionNotEmpty);
                    this.setButtonEnabledState(this.$('a.delete'), isSelectionNotEmpty);
                }

            }
        },
        setPasteBinState: function (isReadyToPaste, isCutMode) {
            this.isReadyToPaste = isReadyToPaste;
            this.isCutMode = isCutMode;
            this.setButtonEnabledState(this.$('a.paste'), isReadyToPaste && !this.isSearchMode);
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
        render: function (directory, creatableTypes, isSearchMode, searchParameters, zoom) {
            this.currentDirectory = directory;

            if (isSearchMode && !this.isSearchMode) {
                this.checkedNodes.nodes = {};
                this.checkedNodes.isSearchMode = true;
            }

            this.isSearchMode = isSearchMode;

            if (this.filters) {
                this.filters.currentDirectory = directory;
            }


            var parameters = _.extend({}, this.parameters);
            parameters.searchedName = searchParameters ? searchParameters.name : null;
            parameters.creatableTypes = creatableTypes;
            parameters.isPasteAllowed = this.isReadyToPaste && !this.isSearchMode && directory.id !== '0';
            parameters.isSearchMode = this.isSearchMode;
            parameters.zoom = this.parameters.zoom;
            parameters.isCreateAllowed = parameters.isAddAllowed = directory.id !== 0
                && _.size(creatableTypes) > 0
                && (this.parameters.isPickerMode || !this.isSearchMode);
            $(this.el).html(Twig.render(ResourceManagerActions, parameters));
        }
    });
})();
