/* global ModalWindow */
/* global ResourceManagerBreadcrumbs */
/* global ResourceManagerActions */
/* global ResourceManagerFilters */
/* global ResourceManagerThumbnail */
/* global ResourceManagerResults */

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
                this.directoryHistory = [];

                if (parameters.isPickerMode) {
                    this.el.className = 'picker resource-manager modal hide';
                    this.wrapper = $('<div class="modal-body"/>');
                    $(this.el).append(this.wrapper);
                } else {
                    this.el.className = 'main resource-manager';
                    this.wrapper = $(this.el);
                }

                this.subViews = {
                    breadcrumbs: new manager.Views.Breadcrumbs(parameters, dispatcher),
                    actions: new manager.Views.Actions(parameters, dispatcher),
                    resources: new manager.Views.Resources(parameters, dispatcher)
                };
            },
            render: function (resources, path, creatableTypes, isSearchMode, searchParameters) {
                this.currentDirectory = _.last(path);

                if (this.directoryHistory.length === 0) {
                    this.directoryHistory = path;
                } else {
                    var index = -1;

                    for (var i = 0; i < this.directoryHistory.length; i++) {
                        if (this.directoryHistory[i].id == this.currentDirectory.id) {
                            index = i;
                        }
                    }

                    if (index == -1) {
                        this.directoryHistory.push(this.currentDirectory);
                    } else {
                        this.directoryHistory.splice(index+1);
                    }
                }

                this.subViews.breadcrumbs.render(this.directoryHistory);
                this.subViews.actions.render(this.currentDirectory, creatableTypes, isSearchMode, searchParameters);
                this.subViews.resources.render(resources, isSearchMode, this.currentDirectory.id);

                if (!this.subViews.areAppended) {
                    this.wrapper.append(
                        this.subViews.breadcrumbs.el,
                        this.subViews.actions.el,
                        this.subViews.resources.el
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
                        resourceId: event.currentTarget.getAttribute('data-resource-id'),
                        isPickerMode: this.parameters.isPickerMode
                    });
                }
            },
            initialize: function (parameters, dispatcher) {
                this.parameters = parameters;
                this.dispatcher = dispatcher;
            },
            render: function (resources) {
                $(this.el).html(Twig.render(ResourceManagerBreadcrumbs, {
                    'resources': resources
                }));
            }
        }),
        Actions: Backbone.View.extend({
            className: 'navbar navbar-static-top',
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
                        resource: {
                            type: event.currentTarget.getAttribute('id'),
                            id: this.currentDirectory.id
                        }
                    });
                },
                'click ul.zoom li a': function (event) {
                    event.preventDefault();

                    var zoom = event.currentTarget.getAttribute('id');
                    var tmp = $('.resource-thumbnail')[0].className.substring(
                        $('.resource-thumbnail')[0].className.indexOf('zoom')
                    );

                    $('.dropdown-menu.zoom li').removeClass('active');
                    $(event.currentTarget).parent().addClass('active');

                    var thumbnail = $('.resource-thumbnail');

                    thumbnail.removeClass(tmp);
                    thumbnail.addClass(zoom);

                },
                'click a.delete': function () {
                    this.dispatcher.trigger('delete', {ids: _.keys(this.checkedResources.resources)});
                    this.checkedResources.resources = {};
                },
                'click a.download': function () {
                    this.dispatcher.trigger('download', {ids: _.keys(this.checkedResources.resources)});
                },
                'click a.copy': function () {
                    if (_.size(this.checkedResources.resources) > 0) {
                        this.setPasteBinState(true, false);
                    }
                },
                'click a.cut': function () {
                    if (_.size(this.checkedResources.resources) > 0) {
                        this.setPasteBinState(true, true);
                    }
                },
                'click a.paste': function () {
                    this.dispatcher.trigger('paste', {
                        ids:  _.keys(this.checkedResources.resources),
                        isCutMode: this.isCutMode,
                        directoryId: this.currentDirectory.id
                    });
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
                        this.parameters.pickerCallback(this.checkedResources.resources, this.currentDirectory.id);
                    } else {
                        if (this.callback) {
                            this.callback(_.keys(this.checkedResources.resources), this.targetDirectoryId);
                        } else {
                            this.dispatcher.trigger('paste', {
                                ids: _.keys(this.checkedResources.resources),
                                directoryId: this.targetDirectoryId,
                                isCutMode: false
                            });
                        }
                    }

                    this.dispatcher.trigger('picker', {action: 'close'});
                }
            },
            filter: function () {
                var searchParameters = {};
                var name = this.$('.name').val().trim();
                var dateFrom = $('input.date-from').first().val();
                var dateTo = $('input.date-to').first().val();
                var types = $('select.resource-types').val();

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
                // selection of resources checked by the user
                this.checkedResources = {
                    resources: {},
                    directoryId: parameters.directoryId,
                    isSearchMode: false
                };
                this.setPasteBinState(false, false);
                // if a resource has been (un-)checked
                this.dispatcher.on('resource-check-status', function (event) {
                    // if the resource belongs to this view instance
                    if (event.isPickerMode === this.parameters.isPickerMode) {
                        // cancel any previous paste bin state
                        if (this.isReadyToPaste) {
                            this.setPasteBinState(false, false);
                        }
                        // cancel any previous selection made in another directory
                        // or in a previous search results list
                        // or in this directory if we're in picker 'mono-select' mode
                        if (this.checkedResources.directoryId !== this.currentDirectory.id ||
                            (this.checkedResources.isSearchMode && !this.isSearchMode) ||
                            (this.parameters.isPickerMode &&
                                !this.parameters.isPickerMultiSelectAllowed &&
                                event.isChecked)) {
                            this.checkedResources.directoryId = this.currentDirectory.id;
                            this.checkedResources.resources = {};
                            this.setPasteBinState(false, false);
                        }
                        // add the resource to the selection or remove it if already present
                        if (this.checkedResources.resources.hasOwnProperty(event.resource.id)) {
                            delete this.checkedResources.resources[event.resource.id];
                        } else {
                            this.checkedResources.resources[event.resource.id] = event.resource.name;
                        }

                        this.checkedResources.directoryId = this.currentDirectory.id;
                        this.checkedResources.isSearchMode = this.isSearchMode;
                        this.setActionsEnabledState(event.isPickerMode);
                    }
                }, this);
            },
            setButtonEnabledState: function (jqButton, isEnabled) {
                return isEnabled ? jqButton.removeClass('disabled') : jqButton.addClass('disabled');
            },
            setActionsEnabledState: function (isPickerMode) {
                var isSelectionNotEmpty = _.size(this.checkedResources.resources) > 0;
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
            render: function (directory, creatableTypes, isSearchMode, searchParameters) {
                this.currentDirectory = directory;

                if (isSearchMode && !this.isSearchMode) {
                    this.checkedResources.resources = {};
                    this.checkedResources.isSearchMode = true;
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
            className: 'resource-thumbnail resource zoom100',
            events: {
                'click .resource-menu-action': function (event) {
                    event.preventDefault();
                    var action = event.currentTarget.getAttribute('data-action');
                    var actionType = event.currentTarget.getAttribute('data-action-type');
                    var resourceId = event.currentTarget.getAttribute('data-id');

                    if (actionType === 'display-form') {
                        this.dispatcher.trigger('display-form', {type: action, resource : {id: resourceId}});
                    } else {
                        if (event.currentTarget.getAttribute('data-is-custom') === 'no') {
                            this.dispatcher.trigger(action, {ids: [resourceId]});
                        } else {
                            this.dispatcher.trigger('custom', {'action': action, id: [resourceId]});
                        }
                    }
                }
            },
            initialize: function (parameters, dispatcher) {
                this.parameters = parameters;
                this.dispatcher = dispatcher;
            },
            render: function (resource, isSelectionAllowed, hasMenu) {
                this.el.id = resource.id;
                resource.displayableName = Claroline.Utilities.formatText(resource.name, 20, 2);
                $(this.el).html(Twig.render(ResourceManagerThumbnail, {
                    'resource': resource,
                    'isSelectionAllowed': isSelectionAllowed,
                    'hasMenu': hasMenu,
                    'customActions': this.parameters.resourceTypes[resource.type].customActions || {},
                    'webRoot': this.parameters.webPath
                }));
            }
        }),
        Resources: Backbone.View.extend({
            className: 'resources',
            events: {
                'click .resource-thumbnail .resource-element': 'dispatchClick',
                'click .resource-thumbnail input[type=checkbox]': 'dispatchCheck',
                'click .results table a.resource-link': 'dispatchClick',
                'click .results table input[type=checkbox]': 'dispatchCheck'
            },
            initialize: function (parameters, dispatcher) {
                this.parameters = parameters;
                this.dispatcher = dispatcher;
                this.directoryId = parameters.directoryId;
            },
            addThumbnails: function (resources, successHandler) {
                _.each(resources, function (resource) {
                    var thumbnail = new manager.Views.Thumbnail(this.parameters, this.dispatcher);
                    thumbnail.render(resource, this.directoryId !== 0 && !this.parameters.isPickerMode, true);
                    this.$el.append(thumbnail.$el);
                }, this);

                if (successHandler) {
                    successHandler();
                }
            },
            renameThumbnail: function (resourceId, newName, successHandler) {
                var displayableName = Claroline.Utilities.formatText(newName, 20, 2);
                this.$('#' + resourceId + ' .resource-name').html(displayableName);
                this.$('#' + resourceId + ' .dropdown[rel=tooltip]').attr('title', newName);

                if (successHandler) {
                    successHandler();
                }
            },
            changeThumbnailIcon: function (resourceId, newIconPath, successHandler) {
                this.$('#' + resourceId + ' img').attr('src', this.parameters.webPath + newIconPath);

                if (successHandler) {
                    successHandler();
                }
            },
            removeResources: function (resourceIds) {
                // same logic for both thumbnails and search results
                for (var i = 0; i < resourceIds.length; ++i) {
                    this.$('#' + resourceIds[i]).remove();
                }
            },
            dispatchClick: function (event) {
                event.preventDefault();
                this.dispatcher.trigger('resource-click', {
                    resourceId: event.currentTarget.getAttribute('data-id'),
                    resourceType: event.currentTarget.getAttribute('data-type'),
                    isPickerMode: this.parameters.isPickerMode
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

                this.dispatcher.trigger('resource-check-status', {
                    resource: {
                        id: event.currentTarget.getAttribute('value'),
                        name: event.currentTarget.getAttribute('data-resource-name')
                    },
                    isChecked: event.currentTarget.checked,
                    isPickerMode: this.parameters.isPickerMode
                });
            },
            render: function (resources, isSearchMode, directoryId) {
                this.directoryId = directoryId;
                this.$el.empty();

                if (isSearchMode) {
                    $(this.el).html(Twig.render(ResourceManagerResults, {
                        'resources': resources,
                        'resourceTypes': this.parameters.resourceTypes
                    }));
                } else {
                    _.each(resources, function (resource) {
                        var thumbnail = new manager.Views.Thumbnail(this.parameters, this.dispatcher);
                        thumbnail.render(
                            resource,
                            directoryId !== 0 || !this.parameters.isPickerMode,
                            directoryId !== 0 && !this.parameters.isPickerMode
                        );
                        $(this.el).append(thumbnail.$el);
                    }, this);
                }
            }
        }),
        Form: Backbone.View.extend({
            className: 'resource-form modal hide',
            events: {
                'click #submit-default-rights-form-button': function (event) {
                    event.preventDefault();
                    var form = $(this.el).find('form')[0];
                    this.dispatcher.trigger(this.eventOnSubmit, {
                        action: form.getAttribute('action'),
                        data: new FormData(form),
                        resourceId: this.targetResourceId
                    });
                },
                'click .res-creation-options': function (event) {
                    event.preventDefault();

                    if (event.currentTarget.getAttribute('data-toggle') !== 'tab') {
                        $.ajax({
//                            context: this,
                            url: event.currentTarget.getAttribute('href'),
                            type: 'POST',
                            processData: false,
                            contentType: false,
                            success: function (form) {
                                this.views.form.render(
                                    form,
                                    event.currentTarget.getAttribute('data-resource-id'),
                                    'edit-rights-creation'
                                );
                            }
                        });
                    }
                },
                'click .search-role-btn':function (event) {
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
                            $('#role-list').append(Twig.render(resourceRightsRoles, {'workspaces': workspaces, 'resourceId': this.targetResourceId}));
                        }
                    })
                },
                'click .role-item':function (event) {
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
                    })
                },
                'click #submit-right-form-button': function(event) {
                    event.preventDefault();
                    var form = $(this.el).find('form')[1]
                    var data = new FormData(form);
                    $.ajax({
                        url: form.getAttribute('action'),
                        context: this,
                        data: data,
                        type: 'POST',
                        processData: false,
                        contentType: false,
                        success: function () {
                            $('#form-right-wrapper').empty();
                        }
                    });
                },
                'submit form': function (event) {
                    event.preventDefault();
                    var form = $(this.el).find('form')[0];
                    this.dispatcher.trigger(this.eventOnSubmit, {
                        action: form.getAttribute('action'),
                        data: new FormData(form),
                        resourceId: this.targetResourceId
                    });
                }
            },
            initialize: function (dispatcher) {
                this.dispatcher = dispatcher;
                this.targetResourceId = null;
                this.eventOnSubmit = '';
                this.on('close', this.close, this);
            },
            close: function () {
                $(this.el).modal('hide');
            },
            render: function (form, targetResourceId, eventOnSubmit) {
                this.targetResourceId = targetResourceId;
                this.eventOnSubmit = eventOnSubmit;
                form = form.replace('_resourceId', targetResourceId);
                $(this.el).html(Twig.render(ModalWindow, {
                    'body': form
                })).modal();
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
                    searchParameters = {};
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
                this.displayForm(event.type, event.resource);
            },
            'create': function (event) {
                this.create(event.action, event.data, event.resourceId);
            },
            'delete': function (event) {
                this.remove(event.ids);
            },
            'download': function (event) {
                this.download(event.ids);
            },
            'rename': function (event) {
                this.rename(event.action, event.data, event.resourceId);
            },
            'edit-properties': function (event) {
                this.editProperties(event.action, event.data, event.resourceId);
            },
            'custom': function (event) {
                this.custom(event.action, event.id);
            },
            'paste': function (event) {
                this[event.isCutMode ? 'move' : 'copy'](event.ids, event.directoryId);
            },
            'breadcrumb-click': function (event) {
                if (event.isPickerMode) {
                    this.displayResources(event.resourceId, 'picker');
                } else {
                    this.router.navigate('resources/' + event.resourceId, {trigger: true});
                }
            },
            'resource-click': function (event) {
                if (event.isPickerMode) {
                    if (event.resourceType === 'directory') {
                        this.displayResources(event.resourceId, 'picker');
                    }
                } else {
                    if (event.resourceType === 'directory') {
                        this.router.navigate('resources/' + event.resourceId, {trigger: true});
                    } else {
                        this.open(event.resourceType, event.resourceId);
                    }
                }
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
            }
        },
        initialize: function (parameters) {
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
            this.stackedRequests = 0;
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
                        data.resources,
                        data.path,
                        data.creatableTypes,
                        isSearchMode,
                        searchParameters
                    );

                    if (!this.views[view].isAppended) {
                        this.parameters.parentElement.append(this.views[view].el);
                        this.views[view].isAppended = true;
                    }
                }
            });
        },
        displayForm: function (type, resource) {
            if (resource.type === 'resource_shortcut') {
                var createShortcut = _.bind(function (resources, parentId) {
                    this.createShortcut(resources, parentId);
                }, this);
                this.dispatcher.trigger('picker', {
                    action: 'open',
                    callback: createShortcut
                });
            } else {
                var urlMap = {
                    'create': '/resource/form/' + resource.type,
                    'rename': '/resource/rename/form/' + resource.id,
                    'edit-properties': '/resource/properties/form/' + resource.id,
                    'edit-rights': '/resource/' + resource.id + '/rights/form/role'
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
                        this.views.form.render(form, resource.id, type);

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
                        this.views.main.subViews.resources.addThumbnails(data, this.views.form.close());
                    } else {
                        this.views.form.render(data, parentDirectoryId, 'create');
                    }
                }
            });
        },
        createShortcut: function (resourceIds, parentId) {
            $.ajax({
                context: this,
                url: this.parameters.appPath + '/resource/shortcut/' +  parentId + '/create',
                data: {ids: resourceIds},
                success: function (data) {
                    this.views.main.subViews.resources.addThumbnails(data);
                }
            });
        },
        remove: function (resourceIds) {
            $.ajax({
                context: this,
                url: this.parameters.appPath + '/resource/delete',
                data: {ids: resourceIds},
                success: function () {
                    this.views.main.subViews.resources.removeResources(resourceIds);
                }
            });
        },
        copy: function (resourceIds, directoryId) {
            $.ajax({
                context: this,
                url: this.parameters.appPath + '/resource/copy/' + directoryId,
                data: {ids: resourceIds},
                success: function (data, textStatus, jqXHR) {
                    if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                        this.views.main.subViews.resources.addThumbnails(data);
                    }
                }
            });
        },
        move: function (resourceIds, newParentDirectoryId) {
            $.ajax({
                context: this,
                url: this.parameters.appPath + '/resource/move/' + newParentDirectoryId,
                data: {ids: resourceIds},
                success: function (data) {
                    this.views.main.subViews.resources.addThumbnails(data);
                }
            });
        },
        rename: function (formAction, formData, resourceId) {
            $.ajax({
                context: this,
                url: formAction,
                data: formData,
                type: 'POST',
                processData: false,
                contentType: false,
                success: function (data, textStatus, jqXHR) {
                    if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                        this.views.main.subViews.resources.renameThumbnail(
                            resourceId,
                            data[0],
                            this.views.form.close()
                        );
                    } else {
                        this.views.form.render(data, resourceId);
                    }
                }
            });
        },
        editProperties: function (formAction, formData, resourceId) {
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
                            this.views.main.subViews.resources.renameThumbnail(
                                resourceId,
                                data.name,
                                this.views.form.close()
                            );
                        }

                        if (data.icon) {
                            this.views.main.subViews.resources.changeThumbnailIcon(
                                resourceId,
                                data.icon,
                                this.views.form.close()
                            );
                        }
                    } else {
                        this.views.form.render(data, resourceId);
                    }
                }
            });
        },
        download: function (resourceIds) {
            window.location = this.parameters.appPath + '/resource/export?' + $.param({ids: resourceIds});
        },
        open: function (resourceType, resourceId) {
            window.location = this.parameters.appPath + '/resource/open/' + resourceType + '/' + resourceId;
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
                contentType: false,
                success: function () {
                    this.views.form.close();
                }
            });
        },
        custom: function (action, resourceId) {
            alert('Custom action "' + action + '" on resource ' + resourceId + ' (not implemented yet)');
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

            this.views.picker.$el.modal(action === 'open' ? 'show' : 'hide');
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
     * - isMultiSelectAllowed: whether the selection of multiple resources in picker mode should be allowed or not
     *      (default to false)
     * - pickerCallback: the function to be called when resources are selected in picker mode
     *      (default to  empty function)
     *
     * @param object parameters The parameters of the manager
     */
    manager.initialize = function (parameters) {
        parameters = parameters || {};
        parameters.directoryId = parameters.directoryId || 0;
        parameters.parentElement = parameters.parentElement || $('body');
        parameters.resourceTypes = parameters.resourceTypes || {};
        parameters.isPickerOnly = parameters.isPickerOnly || false;
        parameters.isPickerMultiSelectAllowed = parameters.isPickerMultiSelectAllowed || false;
        parameters.pickerCallback = parameters.pickerCallback || function () {};
        parameters.appPath = parameters.appPath || '';
        parameters.webPath = parameters.webPath || '';
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
