(function () {
    this.Claroline = this.Claroline || {};
    var manager = this.Claroline.ResourceManager = {};

    manager.Views = {
        Master: Backbone.View.extend({
            initialize: function (parameters, dispatcher) {
                this.parameters = parameters;
                this.dispatcher = dispatcher;
                this.currentDirectory = {id: parameters.directoryId};

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
            render: function (resources, path, isSearchMode) {
                this.currentDirectory = _.last(path);
                this.subViews.breadcrumbs.render(path);
                this.subViews.actions.render(this.currentDirectory, isSearchMode);
                this.subViews.resources.render(resources, isSearchMode, this.currentDirectory.id);
                this.subViews.areAppended || this.wrapper.append(
                    this.subViews.breadcrumbs.el,
                    this.subViews.actions.el,
                    this.subViews.resources.el
                ) && (this.subViews.areAppended = true);
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
                $(this.el).html(Twig.render(resource_breadcrumbs_template, {
                    'resources': resources
                }));
            }
        }),
        Actions: Backbone.View.extend({
            className: 'actions clearfix',
            events: {
                'click button.create': function () {
                    this.dispatcher.trigger('display-form', {
                        type: 'create',
                        resource: {
                            type: this.$('.create-resource select').val(),
                            id: this.currentDirectory.id
                        }
                    });
                },
                'click button.delete': function () {
                    this.dispatcher.trigger('delete', {ids: _.keys(this.checkedResources.resources)});
                    this.checkedResources.resources = {};
                },
                'click button.download': function () {
                    this.dispatcher.trigger('download', {ids: _.keys(this.checkedResources.resources)});
                },
                'click button.copy': function () {
                    _.size(this.checkedResources.resources) > 0 && this.setPasteBinState(true, false);
                },
                'click button.cut': function () {
                    _.size(this.checkedResources.resources) > 0 && this.setPasteBinState(true, true);
                },
                'click button.paste': function () {
                    this.dispatcher.trigger('paste', {
                        ids:  _.keys(this.checkedResources.resources),
                        isCutMode: this.isCutMode,
                        directoryId: this.currentDirectory.id
                    });
                },
                'click button.open-picker': function () {
                    this.dispatcher.trigger('picker', {action: 'open'});
                },
                'click button.search-panel': function () {
                    if (!this.filters) {
                        this.filters = new manager.Views.Filters(this.parameters, this.dispatcher, this.currentDirectory);
                        this.filters.render(this.resourceTypes);
                        $(this.el).after(this.filters.el);
                    }

                    this.filters.toggle();
                },
                'click button.add': function () {
                    this.parameters.isPickerOnly ?
                        this.parameters.pickerCallback(this.checkedResources.resources) :
                        this.dispatcher.trigger('paste', {
                            ids: _.keys(this.checkedResources.resources),
                            directoryId: this.targetDirectoryId,
                            isCutMode: false
                        });
                    this.dispatcher.trigger('picker', {action: 'close'});
                }
            },
            initialize: function (parameters, dispatcher) {
                this.parameters = parameters;
                this.dispatcher = dispatcher;
                this.isSearchMode = false;
                this.currentDirectory = {id: parameters.directoryId};
                this.targetDirectoryId = this.currentDirectory.id; // destination directory for picker "add" action
                this.checkedResources = { // selection of resources checked by the user
                    resources: {},
                    directoryId: parameters.directoryId,
                    isSearchMode: false
                };
                this.setPasteBinState(false, false);
                this.dispatcher.on('resource-check-status', function (event) { // if a resource has been (un-)checked
                    if (event.isPickerMode == this.parameters.isPickerMode) { // if the resource belongs to this view instance
                        this.isReadyToPaste && this.setPasteBinState(false, false); // cancel any previous paste bin state

                        if (this.checkedResources.directoryId != this.currentDirectory.id // cancel any previous selection made in another directory
                            || (this.checkedResources.isSearchMode && !this.isSearchMode) // or in a previous search results list
                            || (this.parameters.isPickerMode && !this.parameters.isPickerMultiSelectAllowed && event.isChecked)) { // or in this directory if we're in picker 'mono-select' mode
                            this.checkedResources.directoryId = this.currentDirectory.id;
                            this.checkedResources.resources = {};
                            this.setPasteBinState(false, false);
                        }

                        this.checkedResources.resources.hasOwnProperty(event.resourceId) ? // add the resource to the selection or remove it if already present
                            delete this.checkedResources.resources[event.resourceId] :
                            (this.checkedResources.resources[event.resource.id] = event.resource.name);
                        this.checkedResources.directoryId = this.currentDirectory.id;
                        this.checkedResources.isSearchMode = this.isSearchMode;
                        this.setActionsEnabledState(event.isPickerMode);
                    }
                }, this);
            },
            setButtonEnabledState: function (jqButton, isEnabled) {
                return isEnabled ? jqButton.removeAttr('disabled') : jqButton.attr('disabled', 'disabled');
            },
            setActionsEnabledState: function (isPickerMode) {
                var isSelectionNotEmpty = _.size(this.checkedResources.resources) > 0;
                isPickerMode // enable picker "add" button on non-root directories if selection is not empty
                    && (this.currentDirectory.id != 0 || this.isSearchMode)
                    && this.setButtonEnabledState(this.$('button.add'), isSelectionNotEmpty);
                !isPickerMode // enable main actions if selection is not empty
                    && this.setButtonEnabledState(this.$('button.download'), isSelectionNotEmpty)
                    && (this.currentDirectory.id != 0 // following actions are only available on non-root directories
                        || this.isSearchMode) // and roots are not displayed in search mode
                    && this.setButtonEnabledState(this.$('button.cut'), isSelectionNotEmpty)
                    && this.setButtonEnabledState(this.$('button.copy'), isSelectionNotEmpty)
                    && this.setButtonEnabledState(this.$('button.delete'), isSelectionNotEmpty);
            },
            setPasteBinState: function (isReadyToPaste, isCutMode) {
                this.isReadyToPaste = isReadyToPaste;
                this.isCutMode = isCutMode;
                this.setButtonEnabledState(this.$('button.paste'), isReadyToPaste && !this.isSearchMode);
            },
            render: function (directory, isSearchMode) {
                this.currentDirectory = directory;
                isSearchMode && !this.isSearchMode
                    && (this.checkedResources.resources = {})
                    && (this.checkedResources.isSearchMode = true);
                this.isSearchMode = isSearchMode;
                this.filters && (this.filters.currentDirectory = directory);
                var parameters = _.extend({}, this.parameters);
                parameters.isPasteAllowed = this.isReadyToPaste && !this.isSearchMode&& directory.id != 0;
                parameters.isCreateAllowed = parameters.isAddAllowed =
                    !(directory.id == 0 || (!this.parameters.isPickerMode && this.isSearchMode));
                $(this.el).html(Twig.render(resource_actions_template, parameters));
            }
        }),
        Filters: Backbone.View.extend({
            className: 'filters form-horizontal',
            events: {
                'click button.filter': function () {
                    var searchParameters = {};
                    var name = this.$('.name').val().trim();
                    var dateFrom = this.$('.date-from').first().val();
                    var dateTo = this.$('.date-to').first().val();
                    var types = this.$('.resource-types').val();
                    name != '' && (searchParameters.name = name);
                    dateFrom != '' && (searchParameters.dateFrom = dateFrom + ' 00:00:00');
                    dateTo != '' && (searchParameters.dateTo = dateTo + ' 23:59:59');
                    types != null && (searchParameters.types = types);
                    this.currentDirectory.id != 0 && (searchParameters.roots = [this.currentDirectory.path]);
                    this.dispatcher.trigger('filter', {
                        isPickerMode: this.parameters.isPickerMode,
                        directoryId: this.currentDirectory.id,
                        parameters: searchParameters
                    });
                },
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
                !this.parameters.isPickerMode && this.dispatcher.trigger('filter-view-change', {
                    isVisible: this.isVisible
                });
            },
            render: function () {
                !this.parameters.isPickerMode && this.$el.addClass('span3');
                $(this.el).html(Twig.render(resource_filters_template, this.parameters));
            }
        }),
        Thumbnail: Backbone.View.extend({
            className: 'resource-thumbnail resource',
            events: {
                'click .resource-menu-action': function (event) {
                    event.preventDefault();
                    var action = event.currentTarget.getAttribute('data-action');
                    var actionType = event.currentTarget.getAttribute('data-action-type');
                    var resourceId = event.currentTarget.getAttribute('data-id');
                    actionType == 'display-form' ?
                        this.dispatcher.trigger('display-form', {type: action, resource : {id: resourceId}}) :
                        event.currentTarget.getAttribute('data-is-custom') == 'no' ?
                            this.dispatcher.trigger(action, {ids: [resourceId]}) :
                            this.dispatcher.trigger('custom', {'action': action, id: [resourceId]});
                }
            },
            initialize: function (parameters, dispatcher) {
                 this.parameters = parameters;
                 this.dispatcher = dispatcher;
            },
            render: function (resource, isSelectionAllowed, hasMenu) {
                this.el.id = resource.id;
                resource.displayableName = Claroline.Utilities.formatText(resource.name, 20, 2);
                $(this.el).html(Twig.render(resource_thumbnail_template, {
                    'resource': resource,
                    'isSelectionAllowed': isSelectionAllowed,
                    'hasMenu': hasMenu,
                    'customActions': this.parameters.resourceTypes[resource.type].customActions || {},
                    'webRoot': this.parameters.appPath + '/..'
                }));
            }
        }),
        Resources: Backbone.View.extend({
            className: 'resources',
            events: {
                'click .resource-thumbnail img': 'dispatchClick',
                'click .resource-thumbnail input[type=checkbox]': 'dispatchCheck',
                'click .results table a.resource-link': 'dispatchClick',
                'click .results table input[type=checkbox]': 'dispatchCheck'
            },
            initialize: function (parameters, dispatcher) {
                this.parameters = parameters;
                this.dispatcher = dispatcher;
                this.directoryId = parameters.directoryId;
                !this.parameters.isPickerMode && this.$el.addClass('span12');
                this.dispatcher.on('filter-view-change', function (event) {
                    if (!this.parameters.isPickerMode) {
                        event.isVisible ?
                            this.$el.removeClass('span12') && this.$el.addClass('span9') :
                            this.$el.removeClass('span9') && this.$el.addClass('span12');
                    }
                }, this);
            },
            addThumbnails: function (resources, successHandler) {
                _.each(resources, function (resource) {
                    var thumbnail = new manager.Views.Thumbnail(this.parameters, this.dispatcher);
                    thumbnail.render(resource, this.directoryId != 0 && !this.parameters.isPickerMode, true);
                    this.$el.append(thumbnail.$el);
                }, this);
                successHandler && successHandler();
            },
            removeResources: function (resourceIds) {
                // same logic for both thumbnails and search results
                for (var i = 0; i < resourceIds.length; ++i) {
                    this.$('#' + resourceIds[i]).remove();
                }
            },
            renameThumbnail: function (resourceId, newName, successHandler) {
                newName = Claroline.Utilities.formatText(newName, 20, 2);
                this.$('#' + resourceId + ' .resource-name').html(newName);
                successHandler && successHandler();
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
                this.parameters.isPickerMode
                    && !this.parameters.isPickerMultiSelectAllowed
                    && event.currentTarget.checked
                    && _.each(this.$('input[type=checkbox]'), function (checkbox) {
                        checkbox !== event.currentTarget && (checkbox.checked = false);
                    });
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
                isSearchMode ?
                    $(this.el).html(Twig.render(resource_results_template, {
                        'resources': resources,
                        'resourceTypes': this.parameters.resourceTypes
                    })) :
                    _.each(resources, function (resource) {
                        var thumbnail = new manager.Views.Thumbnail(this.parameters, this.dispatcher);
                        thumbnail.render(
                            resource,
                            directoryId != 0 || !this.parameters.isPickerMode,
                            directoryId != 0 && !this.parameters.isPickerMode
                        );
                        $(this.el).append(thumbnail.$el);
                    }, this);
            }
        }),
        Form: Backbone.View.extend({
            className: 'resource-form modal hide',
            events: {
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
                form = form.replace('_instanceId', targetResourceId);
                $(this.el).html(Twig.render(modal_template, {
                    'body': form
                })).modal();
            }
        })
    };

    manager.Router = Backbone.Router.extend({
        initialize: function (defaultDirectoryId, displayResourcesCallback) {
            this.route(/^$/, 'default', function() {
                displayResourcesCallback(defaultDirectoryId, 'main');
            });
            this.route(/^resources\/(\d+)(\?.*)?$/, 'display', function (directoryId, queryString) {
                if (queryString) {
                    var searchParameters = {};
                    var parameters = decodeURIComponent(queryString.substr(1)).split('&');
                    _.each(parameters, function (parameter) {
                        parameter = parameter.split('=');
                        parameter[0] == 'name' && (searchParameters.name = parameter[1]);
                        parameter[0] == 'dateFrom' && (searchParameters.dateFrom = parameter[1]);
                        parameter[0] == 'dateTo' && (searchParameters.dateTo = parameter[1]);
                        parameter[0] == 'roots[]' && (searchParameters.roots = [parameter[1]]);
                        parameter[0] == 'types[]' && (searchParameters.types = [parameter[1]]);
                    });
                }

                displayResourcesCallback(directoryId, 'main', searchParameters || null);
            });
        }
    });

    manager.Controller = {
        events: {
            'picker': function (event) {
                this.picker(event.action);
            },
            'create': function (event) {
                this.create(event.action, event.data, event.resourceId);
            },
            'display-form': function (event) {
                this.displayForm(event.type, event.resource);
            },
            'creation-form': function (event) {
                this.displayCreationForm(event.resourceType, event.directoryId);
            },
            'delete': function (event) {
                this.delete_(event.ids);
            },
            'download': function (event) {
                this.download(event.ids);
            },
            'rename': function (event) {
                this.rename(event.action, event.data, event.resourceId);
            },
            'rename-form': function (event) {
                this.displayRenameForm(event.ids[0]);
            },
            'custom': function (event) {
                this.custom(event.action, event.id);
            },
            'paste': function (event) {
                this[event.isCutMode ? 'move' : 'add'](event.ids, event.directoryId);
            },
            'breadcrumb-click': function (event) {
                event.isPickerMode ?
                    this.displayResources(event.resourceId, 'picker') :
                    this.router.navigate('resources/' + event.resourceId, {trigger: true});
            },
            'resource-click': function (event) {
                if (event.resourceType == 'directory') {
                    event.isPickerMode ?
                        this.displayResources(event.resourceId, 'picker') :
                        this.router.navigate('resources/' + event.resourceId, {trigger: true});
                } else {
                    !event.isPickerMode && this.open(event.resourceType, event.resourceId);
                }
            },
            'filter': function (event) {
                if (!event.isPickerMode) {
                    var fragment = 'resources/' + event.directoryId + '?';
                    _.each(event.parameters, function (value, key) {
                        typeof value == 'string' ?
                            (fragment += key + '=' + encodeURIComponent(value) + '&') :
                            _.each(value, function (arrayValue) {
                                fragment += key + '[]=' + encodeURIComponent(arrayValue) + '&';
                            });
                    });
                    this.router.navigate(fragment);
                }

                this.displayResources(event.directoryId, event.isPickerMode ? 'picker' : 'main', event.parameters);
            }
        },
        initialize: function (parameters) {
            this.views = {};
            this.parameters = parameters;
            this.dispatcher = _.extend({}, Backbone.Events);
            _.each(parameters.isPickerOnly ? ['picker'] : ['main', 'picker'], function (view) {
                var viewParameters = _.extend({}, parameters);
                viewParameters.isPickerMode = view == 'picker';
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
                Backbone.history.start() || this.displayResources(parameters.directoryId, 'main');
            }
        },
        displayResources: function (directoryId, view, searchParameters) {
            directoryId = directoryId || 0;
            view = view && view == 'picker' ? view : 'main';
            var isSearchMode = searchParameters ? true : false;
            $.ajax({
                url: this.parameters.appPath + '/resource/' + (isSearchMode ? 'filter' : 'children/' + directoryId),
                data: searchParameters || {},
                success: function (resources) {
                    $.ajax({
                        url: this.parameters.appPath + '/resource/parents/' + directoryId,
                        success: function (path) {
                            (this.parameters.directoryId == 0 || view == 'picker') && path.unshift({id: 0});
                            this.views[view].render(resources, path, isSearchMode);
                            this.views[view].isAppended ||
                                this.parameters.parentElement.append(this.views[view].el)
                                && (this.views[view].isAppended = true);
                        }
                    });
                }
            });
        },

        displayForm: function (type, resource) {
            var formSource = (
                (type == 'create' && '/resource/form/' + resource.type) ||
                (type == 'rename' && '/resource/rename/form/' + resource.id));
            formSource || function () {throw new Error('Form source unknown for action "' + type + '"')}();
            this.views['form'] || (this.views['form'] = new manager.Views.Form(this.dispatcher));
            $.ajax({
                url: this.parameters.appPath + formSource,
                success: function (form) {
                    this.views['form'].render(form, resource.id, type);
                    this.views['form'].isAppended ||
                        this.parameters.parentElement.append(this.views['form'].el)
                        && (this.views['form'].isAppended = true);
                }
            });
        },
        create: function (formAction, formData, parentDirectoryId) {
            $.ajax({
                url: formAction,
                data: formData,
                type: 'POST',
                processData: false,
                contentType: false,
                success: function (data, textStatus, jqXHR) {
                    jqXHR.getResponseHeader('Content-Type') === 'application/json' ?
                        this.views['main'].subViews.resources.addThumbnails(data, this.views['form'].close()) :
                        this.views['form'].render(data, parentDirectoryId);
                }
            });
        },
        rename: function (formAction, formData, resourceId) {
            $.ajax({
                url: formAction,
                data: formData,
                type: 'POST',
                processData: false,
                contentType: false,
                success: function (data, textStatus, jqXHR) {
                    jqXHR.getResponseHeader('Content-Type') === 'application/json' ?
                        this.views['main'].subViews.resources.renameThumbnail(resourceId, data[0], this.views['form'].close()) :
                        this.views['renameForm'].render(data, resourceId);
                }
            });
        },
        delete_: function (resourceIds) {
            $.ajax({
                url: this.parameters.appPath + '/resource/multidelete',
                data: {ids: resourceIds},
                success: function () {
                    this.views.main.subViews.resources.removeResources(resourceIds);
                }
            });
        },
        add: function (resourceIds, directoryId) {
            $.ajax({
                url: this.parameters.appPath + '/resource/workspace/multi/add/' + directoryId,
                data: {ids: resourceIds},
                success: function (data) {
                    this.views.main.subViews.resources.addThumbnails(data);
                }
            });
        },
        move: function (resourceIds, newParentDirectoryId) {
            $.ajax({
                url: this.parameters.appPath + '/resource/multimove/' + newParentDirectoryId,
                data: {ids: resourceIds},
                success: function (data) {
                    this.views.main.subViews.resources.addThumbnails(data);
                }
            });
        },
        download: function (resourceIds) {
            window.location = this.parameters.appPath + '/resource/multiexport?' + $.param({ids: resourceIds});
        },
        open: function (resourceType, resourceId) {
            window.location = this.parameters.appPath + '/resource/open/' + resourceType + '/' + resourceId;
        },
        manageRights: function (resourceId) {
            alert('Rights of ' + resourceId + ' (not implemented yet)')
        },
        custom: function (action, resourceId) {
            alert('Custom action "' + action + '" on resource ' + resourceId + ' (not implemented yet)');
        },
        picker: function (action) {
            action == 'open' && (this.views.picker.isAppended || this.displayResources(0, 'picker'));
            !this.parameters.isPickerOnly && (this.views.picker.subViews.actions.targetDirectoryId = this.views.main.currentDirectory.id);
            this.views.picker.$el.modal(action == 'open' ? 'show' : 'hide');
        }
    };

    /**
     * Initializes the resource manager with a set of options :
     * - appPath: the base url of the application (default to empty string).
     * - directoryId : the id of the directory to open in main (vs picker) mode (default to "0", i.e. pseudo-root of all directories).
     * - parentElement: the jquery element in which the views will be rendered (default to "body" element).
     * - resourceTypes: an object whose properties describe the available resource types (default to empty object).
     * - isPickerOnly: wheither the manager must initialize a main view and a picker view, or just the picker one (default to false).
     * - isMultiSelectAllowed: wheither the selection of multiple resources in picker mode should be allowed or not (default to false).
     * - pickerCallback: the function to be called when resources are selected in picker mode (default to  empty function).
     *
     * @param object parameters The parameters of the manager
     */
    manager.initialize = function (parameters) {
        parameters = parameters || {};
        parameters.directoryId = parameters.directoryId || '0';
        parameters.parentElement = parameters.parentElement || $('body');
        parameters.resourceTypes = parameters.resourceTypes || {};
        parameters.isPickerOnly = parameters.isPickerOnly || false;
        parameters.isPickerMultiSelectAllowed = parameters.isPickerMultiSelectAllowed || false;
        parameters.pickerCallback = parameters.pickerCallback || function () {};
        parameters.appPath = parameters.appPath || '';
        manager.Controller.initialize(parameters);
    };

    /**
     * Opens or closes the resource picker, depending on the "action" parameter.
     *
     * @param string action The action to be taken, i.e. "open" or "close" (default to "open")
     */
    manager.picker = function (action) {
        manager.Controller.picker(action == 'open' ? action : 'close');
    }
})();