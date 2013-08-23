/* global ModalWindow */
/* global ResourceManagerBreadcrumbs */
/* global ResourceManagerActions */
/* global ResourceManagerFilters */
/* global ResourceManagerThumbnail */
/* global ResourceManagerResults */
/* global resourceRightsRoles */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    var manager = window.Claroline.ResourceManager = {};

    manager.Views = {
        Master: Backbone.View.extend({
            initialize: function (parameters, dispatcher) {
                this.parameters = parameters;
                this.dispatcher = dispatcher;
                this.currentDirectory = {id: parameters.directoryId};

                if (parameters.isPickerMode) {
                    this.el.className = 'picker resource-manager';

                    $(this.el).html(Twig.render(ModalWindow, {
                        'header' : 'Resource Picker',
                        'body': ''
                    }));
                    this.wrapper = $('.modal-body', this.el);
                } else {
                    this.el.className = 'main resource-manager';
                    this.wrapper = $(this.el);
                }

                this.subViews = {
                    breadcrumbs: new manager.Views.Breadcrumbs(parameters, dispatcher),
                    actions: new manager.Views.Actions(parameters, dispatcher),
                    nodes: new manager.Views.Nodes(parameters, dispatcher)
                };
            },
            render: function (nodes, path, creatableTypes, isSearchMode, searchParameters) {
                this.currentDirectory = _.last(path);

                // if directoryHistory is empty
                if (this.parameters.directoryHistory.length === 0) {
                    this.parameters.directoryHistory = path;
                } else {
                    var index = -1;

                    for (var i = 0; i < this.parameters.directoryHistory.length; i++) {
                        if (this.parameters.directoryHistory[i].id === this.currentDirectory.id) {
                            index = i;
                        }
                    }

                    var directoriesToAdd = path.length - this.parameters.directoryHistory.length;
                    // compare path & directoryHistory
                    if (directoriesToAdd > 1) {
                        // if path > directoryHistory, it mush come from the search
                        //add the missing directories to the breadcrumbs
                        var pathLength = path.length;
                        var missingDirectories = directoriesToAdd;

                        while (missingDirectories > 0) {
                            this.parameters.directoryHistory.push(path[pathLength - missingDirectories]);
                            missingDirectories--;
                        }

                    } else {
                        if (index === -1) {
                        //if the directory isn't in the breadcrumbs yet'
                            this.parameters.directoryHistory.push(this.currentDirectory);
                        } else {
                            this.parameters.directoryHistory.splice(index + 1);
                        }
                    }
                }

                this.subViews.breadcrumbs.render(this.parameters.directoryHistory);
                this.subViews.actions.render(this.currentDirectory, creatableTypes, isSearchMode, searchParameters);
                this.subViews.nodes.render(
                    nodes,
                    isSearchMode,
                    this.currentDirectory.id,
                    this.directoryHistory
                );

                if (!this.subViews.areAppended) {
                    this.wrapper.append(
                        this.subViews.breadcrumbs.el,
                        this.subViews.actions.el,
                        this.subViews.nodes.el
                    );
                    this.subViews.areAppended = true;
                }
            }
        }),
        Breadcrumbs: Backbone.View.extend({
            tagName: 'ul',
            className: 'breadcrumb',
            events: {
                'click a': function (event) {
                    event.preventDefault();
                    this.dispatcher.trigger('breadcrumb-click', {
                        nodeId: event.currentTarget.getAttribute('data-node-id'),
                        isPickerMode: this.parameters.isPickerMode
                    });
                }
            },
            initialize: function (parameters, dispatcher) {
                this.parameters = parameters;
                this.dispatcher = dispatcher;
            },
            render: function (nodes) {
                $(this.el).html(Twig.render(ResourceManagerBreadcrumbs, {
                    'nodes': nodes
                }));
            }
        }),
        Actions: Backbone.View.extend({
            className: 'navbar navbar-default navbar-static-top',
            events: {
                'keypress input.name': function (event) {
                    if (event.keyCode !== 13) {
                        return;
                    }

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

                    var zoom = event.currentTarget.getAttribute('id');
                    var tmp = $('.node-thumbnail')[0].className.substring(
                        $('.node-thumbnail')[0].className.indexOf('zoom')
                    );

                    $('.dropdown-menu.zoom li').removeClass('active');
                    $(event.currentTarget).parent().addClass('active');

                    var thumbnail = $('.node-thumbnail');

                    thumbnail.removeClass(tmp);
                    thumbnail.addClass(zoom);

                },
                'click a.delete': function () {
                    if (!(this.$('a.delete').hasClass('disabled'))) {
                        this.dispatcher.trigger('delete', {ids: _.keys(this.checkedNodes.nodes)});
                        this.checkedNodes.nodes = {};
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
                    this.dispatcher.trigger('picker', {action: 'open'});
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

                    if (this.parameters.isPickerOnly) {
                        this.parameters.pickerCallback(this.checkedNodes.nodes, this.currentDirectory.id);
                    } else {
                        if (this.callback) {
                            this.callback(_.keys(this.checkedNodes.nodes), this.targetDirectoryId);
                        } else {
                            this.dispatcher.trigger('paste', {
                                ids: _.keys(this.checkedNodes.nodes),
                                directoryId: this.targetDirectoryId,
                                isCutMode: false
                            });
                        }
                    }

                    this.dispatcher.trigger('picker', {action: 'close'});
                },
                'click a.filter-result': function (event) {
                    this.dispatcher.trigger('filter-result', {action: $(event.currentTarget).attr('data-type')});
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
                        if (this.checkedNodes.nodes.hasOwnProperty(event.node.id)) {
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
                this.isReadyToPaste = false;
                this.isCutMode = false;
                this.setButtonEnabledState(this.$('a.cut'), false);
                this.setButtonEnabledState(this.$('a.copy'), false);
                this.setButtonEnabledState(this.$('a.paste'), false);
                this.setButtonEnabledState(this.$('a.delete'), false);
                this.setButtonEnabledState(this.$('a.download'), false);
            },
            render: function (directory, creatableTypes, isSearchMode, searchParameters) {
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
                parameters.isCreateAllowed = parameters.isAddAllowed = directory.id !== 0 &&
                    _.size(creatableTypes) > 0 &&
                    (this.parameters.isPickerMode || !this.isSearchMode);
                $(this.el).html(Twig.render(ResourceManagerActions, parameters));
            }
        }),
        Filters: Backbone.View.extend({
            className: 'filters container-fluid',
            events: {
                'click button.close-panel': function () {
                    this.toggle();
                },
                'click input.datepicker': function (event) {
                    this.$(event.currentTarget).datepicker('show');
                },
                'changeDate input.datepicker': function (event) {
                    this.$(event.currentTarget).datepicker('hide');
                },
                'keydown input.datepicker': function (event) {
                    event.preventDefault();
                    this.$(event.currentTarget).datepicker('hide');
                }
            },
            initialize: function (parameters, dispatcher, currentDirectory) {
                this.parameters = parameters;
                this.dispatcher = dispatcher;
                this.currentDirectory = currentDirectory;
                this.currentDirectoryId = parameters.directoryId;
            },
            toggle: function () {
                $(this.el).css('display', !this.isVisible ? 'block' : 'none');
                this.isVisible = !this.isVisible;
            },
            render: function () {
                $(this.el).html(Twig.render(ResourceManagerFilters, this.parameters));
            }
        }),
        Thumbnail: Backbone.View.extend({
            className: 'node-thumbnail node zoom100 ui-state-default',
            tagName: 'li',
            events: {
                'click .node-menu-action': function (event) {
                    event.preventDefault();
                    var action = event.currentTarget.getAttribute('data-action');
                    var actionType = event.currentTarget.getAttribute('data-action-type');
                    var nodeId = event.currentTarget.getAttribute('data-id');

                    if (actionType === 'display-form') {
                        this.dispatcher.trigger('display-form', {type: action, node : {id: nodeId}});
                    } else {
                        if (event.currentTarget.getAttribute('data-is-custom') === 'no') {
                            this.dispatcher.trigger(action, {ids: [nodeId]});
                        } else {
                            var async = event.currentTarget.getAttribute('data-async');
                            var redirect = (async === '1') ? false : true;
                            this.dispatcher.trigger('custom', {'action': action, id: [nodeId], 'redirect': redirect});
                        }
                    }
                }
            },
            initialize: function (parameters, dispatcher) {
                this.parameters = parameters;
                this.dispatcher = dispatcher;
            },
            render: function (node, isSelectionAllowed, hasMenu) {
                this.el.id = node.id;
                node.displayableName = Claroline.Utilities.formatText(node.name, 20, 2);
                $(this.el).html(Twig.render(ResourceManagerThumbnail, {
                    'node': node,
                    'isSelectionAllowed': isSelectionAllowed,
                    'hasMenu': hasMenu,
                    'customActions': this.parameters.resourceTypes[node.type].customActions || {},
                    'webRoot': this.parameters.webPath
                }));
            }
        }),
        Nodes: Backbone.View.extend({
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
                    thumbnail.render(node, this.directoryId !== 0 && !this.parameters.isPickerMode, true);
                    this.$el.append(thumbnail.$el);
                }, this);

                if (successHandler) {
                    successHandler();
                }
            },
            renameThumbnail: function (nodeId, newName, successHandler) {
                var displayableName = Claroline.Utilities.formatText(newName, 20, 2);
                this.$('#' + nodeId + ' .node-name').html(displayableName);
                this.$('#' + nodeId + ' .dropdown[rel=tooltip]').attr('title', newName);

                if (successHandler) {
                    successHandler();
                }
            },
            changeThumbnailIcon: function (nodeId, newIconPath, successHandler) {
                this.$('#' + nodeId + ' img').attr('src', this.parameters.webPath + newIconPath);

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
                this.dispatcher.trigger('node-click', {
                    nodeId: event.currentTarget.getAttribute('data-id'),
                    resourceType: event.currentTarget.getAttribute('data-type'),
                    isPickerMode: this.parameters.isPickerMode,
                    directoryHistory: this.parameters.directoryHistory
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
                        var thumbnail = new manager.Views.Thumbnail(this.parameters, this.dispatcher);
                        thumbnail.render(
                            node,
                            directoryId !== 0 || !this.parameters.isPickerMode,
                            directoryId !== 0 && !this.parameters.isPickerMode
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
        }),
        Form: Backbone.View.extend({
            className: 'node-form modal hide',
            events: {
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

                    if (event.currentTarget.getAttribute('data-toggle') !== 'tab') {
                        $.ajax({
                            url: event.currentTarget.getAttribute('href'),
                            type: 'POST',
                            processData: false,
                            contentType: false,
                            success: function (form) {
                                this.views.form.render(
                                    form,
                                    event.currentTarget.getAttribute('data-node-id'),
                                    'edit-rights-creation'
                                );
                            }
                        });
                    }
                },
                'click .search-role-btn': function (event) {
                    event.preventDefault();
                    var search = $('#role-search-text').val();
                    $.ajax({
                        url: Routing.generate('claro_resource_find_role_by_code', {'code': search}),
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
                            $('#modal-check-role').empty();
                            $('#modal-check-role').append(form);
                            $('#rights-form-resource-tab-content').css('display', 'none');
                            $('#rights-form-resource-nav-tabs').css('display', 'none');
                            $('#modal-check-resource-right-box .modal').modal('show');
                        }
                    });
                },
                'click .modal-close': function (event) {
                    event.preventDefault();
                    $('#modal-check-role').empty();
                    $('#modal-check-resource-right-box .modal').modal('hide');
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
                            $('#modal-check-resource-right-box .modal').modal('hide');
                            $('#rights-form-resource-tab-content').css('display', 'block');
                            $('#rights-form-resource-nav-tabs').css('display', 'block');
                        }
                    });
                },
                'submit form': function (event) {
                    event.preventDefault();
                    var form = $(this.el).find('form')[0];
                    this.dispatcher.trigger(this.eventOnSubmit, {
                        action: form.getAttribute('action'),
                        data: new FormData(form),
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
                $('.modal', this.el).modal('hide');
            },
            render: function (form, targetNodeId, eventOnSubmit) {
                this.targetNodeId = targetNodeId;
                this.eventOnSubmit = eventOnSubmit;
                form = form.replace('_nodeId', targetNodeId);
                $(this.el).html(Twig.render(ModalWindow, {
                    'body': form
                }));
                $('.modal', this.el).modal('show');
            }
        })
    };

    manager.Router = Backbone.Router.extend({
        initialize: function (defaultDirectoryId, displayResourcesCallback) {
            this.route(/^$/, 'default', function () {
                displayResourcesCallback(defaultDirectoryId, 'main');
            });
            this.route(/^resources\/(\d+)(\?.*)?$/, 'display', function (directoryId, queryString) {
                var searchParameters = null;

                if (queryString) {
                    //searchParameters = {};
                    var parameters = decodeURIComponent(queryString.substr(1)).split('&');
                    _.each(parameters, function (parameter) {
                        parameter = parameter.split('=');

                        if (['name', 'dateFrom', 'dateTo', 'roots[]', 'types[]'].indexOf(parameter[0]) > -1) {
                            searchParameters[parameter[0].replace('[]', '')] = parameter[1];
                        }
                    });
                }

                displayResourcesCallback(directoryId, 'main', searchParameters);
            });
        }
    });

    manager.Controller = {
        events: {
            'picker': function (event) {
                this.picker(event.action, event.callback);
            },
            'display-form': function (event) {
                this.displayForm(event.type, event.node);
            },
            'create': function (event) {
                this.create(event.action, event.data, event.nodeId);
            },
            'delete': function (event) {
                this.remove(event.ids);
            },
            'download': function (event) {
                this.download(event.ids);
            },
            'rename': function (event) {
                this.rename(event.action, event.data, event.nodeId);
            },
            'edit-properties': function (event) {
                this.editProperties(event.action, event.data, event.nodeId);
            },
            'custom': function (event) {
                this.custom(event.action, event.id, event.redirect);
            },
            'paste': function (event) {
                this[event.isCutMode ? 'move' : 'copy'](event.ids, event.directoryId, event.sourceDirectoryId);
            },
            'breadcrumb-click': function (event) {
                if (event.isPickerMode) {
                    this.displayResources(event.nodeId, 'picker');
                } else {
                    this.router.navigate('resources/' + event.nodeId, {trigger: true});
                }
            },
            'node-click': function (event) {
                if (this.isOpenEnabled) {
                    if (event.isPickerMode) {
                        if (event.resourceType === 'directory') {
                            this.displayResources(event.nodeId, 'picker');
                        }
                    } else {
                        if (event.resourceType === 'directory') {
                            this.router.navigate('resources/' + event.nodeId, {trigger: true});
                        } else {
                            this.open(event.resourceType, event.nodeId, event.directoryHistory);
                        }
                    }
                }
                this.isOpenEnabled = true;
            },
            'filter': function (event) {
                if (!event.isPickerMode) {
                    var fragment = 'resources/' + event.directoryId + '?';
                    _.each(event.parameters, function (value, key) {
                        if (typeof value === 'string') {
                            fragment += key + '=' + encodeURIComponent(value) + '&';
                        } else {
                            _.each(value, function (arrayValue) {
                                fragment += key + '[]=' + encodeURIComponent(arrayValue) + '&';
                            });
                        }
                    });
                    this.router.navigate(fragment);
                }

                this.displayResources(event.directoryId, event.isPickerMode ? 'picker' : 'main', event.parameters);
            },
            'edit-rights': function (event) {
                this.editRights(event.action, event.data);
            },
            'edit-rights-creation': function (event) {
                this.editCreationRights(event.action, event.data);
            },
            'filter-result': function (event) {
                this.setFilterState(event.action);
                this.parameters.filterState = event.action;
            }
        },
        initialize: function (parameters) {
            this.isOpenEnabled = true;
            this.views = {};
            this.parameters = parameters;
            this.dispatcher = _.extend({}, Backbone.Events);
            _.each(parameters.isPickerOnly ? ['picker'] : ['main', 'picker'], function (view) {
                var viewParameters = _.extend({}, parameters);
                viewParameters.isPickerMode = view === 'picker';
                this.views[view] = new manager.Views.Master(viewParameters, this.dispatcher);
            }, this);
            _.each(this.events, function (callback, event) {
                callback = _.bind(callback, this);
                this.dispatcher.on(event, callback);
            }, this);
            $.ajaxSetup({
                headers: {'X_Requested_With': 'XMLHttpRequest'},
                context: this
            });

            if (!parameters.isPickerOnly) {
                this.displayResources = _.bind(this.displayResources, this);
                this.router = new manager.Router(this.parameters.directoryId, this.displayResources);
                var hasMatchedRoute = Backbone.history.start();

                if (!hasMatchedRoute) {
                    this.displayResources(parameters.directoryId, 'main');
                }
            }
        },
        setFilterState: function (type) {
            $('.node-thumbnail').show();
            if (type !== 'none') {
                $.each($('.node-element'), function (key, element) {
                    if ($(element).attr('data-type') !== type && $(element).attr('data-type') !== 'directory') {
                        $(element.parentElement).hide();
                    }
                });
            }
        },
        displayResources: function (directoryId, view, searchParameters) {
            directoryId = directoryId || 0;
            view = view && view === 'picker' ? view : 'main';
            var isSearchMode = searchParameters ? true : false;

            $.ajax({
                context: this,
                url: this.parameters.appPath + '/resource/' +
                    (isSearchMode ? 'filter' : 'directory') +
                    '/' + directoryId,
                data: searchParameters || {},
                success: function (data) {

                    if (isSearchMode) {
                        data.creatableTypes = {};
                    }

                    if (this.parameters.directoryId === 0 || view === 'picker') {
                        data.path.unshift({id: 0});
                    }

                    this.views[view].render(
                        data.nodes,
                        data.path,
                        data.creatableTypes,
                        isSearchMode,
                        searchParameters,
                        data.is_root,
                        data.workspace_id
                    );

                    if (!this.views[view].isAppended) {
                        this.parameters.parentElement.append(this.views[view].el);
                        this.views[view].isAppended = true;
                    }

                    var that = this;

                    $('#sortable').sortable({
                        update: function (event, ui) {
                            var ids = $('#sortable').sortable('toArray');
                            var moved = ui.item.attr('id');
                            var indexMoved = 0;

                            for (var i = 0; i < ids.length; i++) {
                                if (ids[i] === moved) {
                                    indexMoved = i;
                                }
                            }

                            var nextId = 0;

                            if (indexMoved + 1 !== ids.length) {
                                nextId = ids[indexMoved + 1];
                            }

                            if (ui.position !== ui.originalPosition) {
                                $.ajax({
                                    url: Routing.generate(
                                        'claro_resource_insert_before',
                                        {'node': moved, 'nextId': nextId}
                                    )
                                });
                            }
                        },
                        start: function () {
                            that.isOpenEnabled = false;
                        }
                    });

                    if (!data.canChangePosition) {
                        $('#sortable').sortable('disable');
                    } else {
                        $('#sortable').sortable('enable');
                    }

                    this.setFilterState(this.parameters.filterState);
                }
            });
        },
        displayForm: function (type, node) {
            if (node.type === 'resource_shortcut') {
                var createShortcut = _.bind(function (nodes, parentId) {
                    this.createShortcut(nodes, parentId);
                }, this);
                this.dispatcher.trigger('picker', {
                    action: 'open',
                    callback: createShortcut
                });
            } else {
                var urlMap = {
                    'create': '/resource/form/' + node.type,
                    'rename': '/resource/rename/form/' + node.id,
                    'edit-properties': '/resource/properties/form/' + node.id,
                    'edit-rights': '/resource/' + node.id + '/rights/form/role'
                };

                if (!urlMap[type]) {
                    throw new Error('Form source unknown for action "' + type + '"');
                }

                if (!this.views.form) {
                    this.views.form = new manager.Views.Form(this.dispatcher);
                }

                $.ajax({
                    context: this,
                    url: this.parameters.appPath + urlMap[type],
                    success: function (form) {
                        this.views.form.render(form, node.id, type);

                        if (!this.views.form.isAppended) {
                            this.parameters.parentElement.append(this.views.form.el);
                            this.views.form.isAppended = true;
                        }
                    }
                });
            }
        },
        create: function (formAction, formData, parentDirectoryId) {
            $.ajax({
                context: this,
                url: formAction,
                data: formData,
                type: 'POST',
                processData: false,
                contentType: false,
                success: function (data, textStatus, jqXHR) {
                    if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                        this.views.main.subViews.nodes.addThumbnails(data, this.views.form.close());
                    } else {
                        this.views.form.render(data, parentDirectoryId, 'create');
                    }
                }
            });
        },
        createShortcut: function (nodeIds, parentId) {
            $.ajax({
                context: this,
                url: this.parameters.appPath + '/resource/shortcut/' +  parentId + '/create',
                data: {ids: nodeIds},
                success: function (data) {
                    this.views.main.subViews.nodes.addThumbnails(data);
                }
            });
        },
        remove: function (nodeIds) {
            $.ajax({
                context: this,
                url: this.parameters.appPath + '/resource/delete',
                data: {ids: nodeIds},
                success: function () {
                    this.views.main.subViews.nodes.removeResources(nodeIds);
                    this.views.main.subViews.actions.setInitialState();
                }
            });
        },
        copy: function (nodeIds, directoryId) {
            $.ajax({
                context: this,
                url: this.parameters.appPath + '/resource/copy/' + directoryId,
                data: {ids: nodeIds},
                success: function (data, textStatus, jqXHR) {
                    if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                        this.views.main.subViews.nodes.addThumbnails(data);
                    }
                }
            });
        },
        move: function (nodeIds, newParentDirectoryId, oldParentDirectoryId) {
            if (newParentDirectoryId === oldParentDirectoryId) {
                this.views.main.subViews.nodes.uncheckAll();
                this.views.main.subViews.actions.checkedNodes.nodes = {};
                this.views.main.subViews.actions.setInitialState();
            }
            else {
                $.ajax({
                    context: this,
                    url: this.parameters.appPath + '/resource/move/' + newParentDirectoryId,
                    data: {ids: nodeIds},
                    success: function (data) {
                        this.views.main.subViews.nodes.addThumbnails(data);
                        this.views.main.subViews.actions.setInitialState();
                    }
                });
            }
        },
        rename: function (formAction, formData, nodeId) {
            $.ajax({
                context: this,
                url: formAction,
                data: formData,
                type: 'POST',
                processData: false,
                contentType: false,
                success: function (data, textStatus, jqXHR) {
                    if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                        this.views.main.subViews.nodes.renameThumbnail(
                            nodeId,
                            data[0],
                            this.views.form.close()
                        );
                    } else {
                        this.views.form.render(data, nodeId);
                    }
                }
            });
        },
        editProperties: function (formAction, formData, nodeId) {
            $.ajax({
                context: this,
                url: formAction,
                data: formData,
                type: 'POST',
                processData: false,
                contentType: false,
                success: function (data, textStatus, jqXHR) {
                    if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                        if (data.name) {
                            this.views.main.subViews.nodes.renameThumbnail(
                                nodeId,
                                data.name,
                                this.views.form.close()
                            );
                        }

                        if (data.icon) {
                            this.views.main.subViews.nodes.changeThumbnailIcon(
                                nodeId,
                                data.icon,
                                this.views.form.close()
                            );
                        }
                    } else {
                        this.views.form.render(data, nodeId);
                    }
                }
            });
        },
        download: function (nodeIds) {
            window.location = this.parameters.appPath + '/resource/download?' + $.param({ids: nodeIds});
        },
        open: function (resourceType, nodeId, directoryHistory) {
            var _path = '';
            for (var i = 0; i < directoryHistory.length; i++) {
                if (directoryHistory[i].id !== 0) {
                    _path += i === 0 ? '?' : '&';
                    _path += '_breadcrumbs[]=' + directoryHistory[i].id;
                }
            }

            window.location = this.parameters.appPath + '/resource/open/' + resourceType + '/' +
                nodeId + _path;
        },
        editRights: function (formAction, formData) {
            $.ajax({
                context: this,
                url: formAction,
                data: formData,
                type: 'POST',
                processData: false,
                contentType: false,
                success: function () {
                    this.views.form.close();
                }
            });
        },
        editCreationRights: function (action, formData) {
            $.ajax({
                context: this,
                url: action,
                data: formData,
                type: 'POST',
                processData: false,
                contentType: false
            });
        },
        custom: function (action, nodeId, redirect) {
            if (redirect) {
                window.location = this.parameters.appPath + '/resource/custom/' + action + '/' + nodeId;
            } else {
                alert("ajax call: no implementation yet");
            }
        },
        picker: function (action, callback) {
            if (action === 'open' && !this.views.picker.isAppended) {
                this.displayResources(0, 'picker');
            }

            if (!this.parameters.isPickerOnly) {
                this.views.picker.subViews.actions.targetDirectoryId = this.views.main.currentDirectory.id;
            }

            if (callback) {
                this.views.picker.subViews.actions.callback = callback;
            }

            $('.modal', this.views.picker.$el).modal(action === 'open' ? 'show' : 'hide');
        }
    };

    /**
     * Initializes the resource manager with a set of options :
     * - appPath: the base url of the application
     *      (default to empty string)
     * - webPath: the base url of the web directory
     *      (default to empty string)
     * - directoryId : the id of the directory to open in main (vs picker) mode
     *      (default to "0", i.e. pseudo-root of all directories)
     * - parentElement: the jquery element in which the views will be rendered
     *      (default to "body" element)
     * - resourceTypes: an object whose properties describe the available resource types
     *      (default to empty object)
     * - isPickerOnly: whether the manager must initialize a main view and a picker view, or just the picker one
     *      (default to false)
     * - isMultiSelectAllowed: whether the selection of multiple nodes in picker mode should be allowed or not
     *      (default to false)
     * - pickerCallback: the function to be called when nodes are selected in picker mode
     *      (default to  empty function)
     *
     * @param object parameters The parameters of the manager
     */
    manager.initialize = function (parameters) {
        parameters = parameters || {};
        parameters.directoryId = parameters.directoryId || 0;
        parameters.directoryHistory = parameters.directoryHistory || [];
        parameters.parentElement = parameters.parentElement || $('body');
        parameters.resourceTypes = parameters.resourceTypes || {};
        parameters.isPickerOnly = parameters.isPickerOnly || false;
        parameters.isPickerMultiSelectAllowed = parameters.isPickerMultiSelectAllowed || false;
        parameters.pickerCallback = parameters.pickerCallback || function () {};
        parameters.appPath = parameters.appPath || '';
        parameters.webPath = parameters.webPath || '';
        parameters.filterState = parameters.filterState || 'none';
        manager.Controller.initialize(parameters);
    };
    /**
     * Opens or closes the resource picker, depending on the "action" parameter.
     *
     * @param string action The action to be taken, i.e. "open" or "close" (default to "open")
     */
    manager.picker = function (action) {
        manager.Controller.picker(action === 'open' ? action : 'close');
    };
})();
