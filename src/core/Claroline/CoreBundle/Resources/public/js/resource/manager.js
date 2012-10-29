(function () {
    this.Claroline = this.Claroline || {};
    var manager = this.Claroline.ResourceManager = {};

// TO BE REMOVED ///////////////////////////////////////////////////////////////
    manager.Models = {
        Resource: Backbone.Model.extend({})
    };

    manager.Collections = {
        Directory: Backbone.Collection.extend({
            model: manager.Models.Resource,
            initialize: function (resources, id, baseUrl) {
                if (!id) {
                    throw new Error('Directory must have an id');
                }

                if (!baseUrl) {
                    throw new Error('Directory must have a base url');
                }

                this.id = id;
                this.baseUrl = baseUrl;
            },
            url: function () {
                return this.baseUrl + '/' + this.id;
            }
        })
    };
////////////////////////////////////////////////////////////////////////////////

    manager.Views = {
        Master: Backbone.View.extend({
            initialize: function (parameters, dispatcher) {
                this.parameters = parameters;
                this.dispatcher = dispatcher;

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
                this.subViews.breadcrumbs.render(path);
                this.subViews.actions.render(_.last(path), isSearchMode);
                this.subViews.resources.render(resources, isSearchMode);
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
                        route: event.currentTarget.getAttribute('data-route'),
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
                    this.dispatcher.trigger('creation-form', {
                        resourceType: this.$('.create-resource select').val(),
                        directoryId: this.currentDirectory.id
                    });
                },
                'click button.delete': function () {
                    this.dispatcher.trigger('delete', {ids: this.checkedResources.ids});
                    this.checkedResources.ids = [];
                },
                'click button.download': function () {
                    this.dispatcher.trigger('download', {ids: this.checkedResources.ids});
                },
                'click button.copy': function () {
                    this.checkedResources.ids.length > 0 && this.setPasteBinState(true, false);
                },
                'click button.cut': function () {
                    this.checkedResources.ids.length > 0 && this.setPasteBinState(true, true);
                },
                'click button.paste': function () {
                    this.dispatcher.trigger('paste', {
                        ids: this.checkedResources.ids,
                        isCutMode: this.isCutMode,
                        directoryId: this.currentDirectory.id
                    });
                },
                'click button.open-picker': function () {
                    this.dispatcher.trigger('open-picker');
                },
                'click button.search-panel': function () {
                    if (!this.filters) {
                        this.filters = new manager.Views.Filters(this.parameters, this.dispatcher, this.currentDirectory);
                        this.filters.render(this.resourceTypes);
                        $(this.el).after(this.filters.el);
                    }

                    this.filters.toggle();
                }
            },
            initialize: function (parameters, dispatcher) {
                this.parameters = parameters;
                this.dispatcher = dispatcher;
                this.isSearchMode = false;
                this.currentDirectory = {id: parameters.directoryId};
                this.checkedResources = { // selection of resources checked by the user
                    ids: [],
                    directoryId: parameters.directoryId,
                    isSearchMode: false
                };
                this.setPasteBinState(false, false);
                this.dispatcher.on('resource-check-status', function (event) { // if a resource has been (un-)checked
                    if (event.isPickerMode == this.parameters.isPickerMode) { // if the resource belongs to this view instance
                        this.isReadyToPaste && this.setPasteBinState(false, false); // cancel any previous paste bin state

                        if (this.checkedResources.directoryId != this.currentDirectory.id // cancel any previous selection made in another directory
                            || (this.checkedResources.isSearchMode && !this.isSearchMode)) { // or in a previous search results list
                            this.checkedResources.directoryId = this.currentDirectory.id;
                            this.checkedResources.ids = [];
                            this.setPasteBinState(false, false);
                        }

                        var idIndex = this.checkedResources.ids.indexOf(event.resourceId);
                        idIndex == -1 ? // add the resource to the selection or remove it if already present
                            this.checkedResources.ids.push(event.resourceId) :
                            this.checkedResources.ids.splice(idIndex, 1);
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
                isPickerMode // enable picker "add" button on non-root directories if selection is not empty
                    && this.currentDirectory.id != 0
                    && this.setButtonEnabledState(this.$('button.add'), this.checkedResources.ids.length > 0);
                !isPickerMode // enable main actions if selection is not empty
                    && this.setButtonEnabledState(this.$('button.download'), this.checkedResources.ids.length > 0)
                    && (this.currentDirectory.id != 0 // following actions are only available on non-root directories
                        || this.isSearchMode) // and roots are not displayed in search mode
                    && this.setButtonEnabledState(this.$('button.cut'), this.checkedResources.ids.length > 0)
                    && this.setButtonEnabledState(this.$('button.copy'), this.checkedResources.ids.length > 0)
                    && this.setButtonEnabledState(this.$('button.delete'), this.checkedResources.ids.length > 0);
            },
            setPasteBinState: function (isReadyToPaste, isCutMode) {
                this.isReadyToPaste = isReadyToPaste;
                this.isCutMode = isCutMode;
                this.setButtonEnabledState(this.$('button.paste'), isReadyToPaste && !this.isSearchMode);
            },
            render: function (directory, isSearchMode) {
                this.currentDirectory = directory;
                isSearchMode && !this.isSearchMode
                    && (this.checkedResources.ids = [])
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
                !this.parameters.isPickerMode && this.$el.addClass('span4');
                $(this.el).html(Twig.render(resource_filters_template, this.parameters));
            }
        }),
        Thumbnail: Backbone.View.extend({
            className: 'resource-thumbnail',
            events: {
                'click .resource-menu-action': function (event) {
                    event.preventDefault();
                    var actionType = event.currentTarget.getAttribute('data-action');
                    var resourceId = event.currentTarget.getAttribute('data-id');
                    event.currentTarget.getAttribute('data-is-custom') == 'no' ?
                        this.dispatcher.trigger(actionType, {ids: [resourceId]}) :
                        this.dispatcher.trigger('custom', {action: actionType, id: [resourceId]});
                }
            },
            initialize: function (parameters, dispatcher) {
                 this.parameters = parameters;
                 this.dispatcher = dispatcher;
            },
            render: function (resource) {
                this.el.id = resource.id;
                resource.displayableName = Claroline.Utilities.formatText(resource.name, 20, 2);
                $(this.el).html(Twig.render(resource_thumbnail_template, {
                    'resource': resource,
                    'isDownloadable': this.parameters.resourceTypes[resource.type].isDownloadable,
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
                !this.parameters.isPickerMode && this.$el.addClass('span12');
                this.dispatcher.on('filter-view-change', function (event) {
                    if (!this.parameters.isPickerMode) {
                        event.isVisible ?
                            this.$el.removeClass('span12') && this.$el.addClass('span8') :
                            this.$el.removeClass('span8') && this.$el.addClass('span12');
                    }
                }, this);
            },
            addThumbnails: function (resources, successHandler) {
                for (var i = 0; i < resources.length; ++i) {
                    var thumbnail = new manager.Views.Thumbnail(this.parameters, this.dispatcher);
                    thumbnail.render(resources[i]);
                    this.$el.append(thumbnail.$el);
                }

                successHandler && successHandler();
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
                this.dispatcher.trigger('resource-check-status', {
                    resourceId: event.currentTarget.getAttribute('value'),
                    isChecked: event.currentTarget.checked,
                    isPickerMode: this.parameters.isPickerMode
                });
            },
            render: function (resources, isSearchMode) {
                this.$el.empty();
                isSearchMode ?
                    $(this.el).html(Twig.render(resource_results_template, {
                        'resources': resources,
                        'resourceTypes': this.parameters.resourceTypes
                    })) :
                    _.each(resources, function (resource) {
                        var thumbnail = new manager.Views.Thumbnail(this.parameters, this.dispatcher);
                        thumbnail.render(resource);
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
                    this.dispatcher.trigger('create', {
                       action: form.getAttribute('action'),
                       data: new FormData(form),
                       parentId: this.targetResourceId
                    });
                }
            },
            initialize: function (dispatcher) {
                this.dispatcher = dispatcher;
                this.on('close', this.close, this);
            },
            close: function () {
                $(this.el).modal('hide');
            },
            render: function (form, targetResourceId) {
                this.targetResourceId = targetResourceId;
                form = form.replace('_instanceId', targetResourceId);
                $(this.el).html(Twig.render(modal_template, {
                    'body': form
                })).modal();
            }
        }),



        RenameForm: Backbone.View.extend({
            className: 'resource-form modal hide',
            events: {
                'submit form': function (event) {
                    event.preventDefault();
                    var form = $(this.el).find('form')[0];
                    this.dispatcher.trigger('rename', {
                       action: form.getAttribute('action'),
                       data: new FormData(form),
                       resourceId: this.targetResourceId
                    });
                }
            },
            initialize: function (dispatcher) {
                this.dispatcher = dispatcher;
                this.on('close', this.close, this);
            },
            close: function () {
                $(this.el).modal('hide');
            },
            render: function (form, targetResourceId) {
                this.targetResourceId = targetResourceId;
                form = form.replace('_instanceId', targetResourceId);
                $(this.el).html(Twig.render(modal_template, {
                    'body': form
                })).modal();
            }
        })



    };

    manager.Router = Backbone.Router.extend({
        initialize: function (displayResourcesCallback) {
            this.route(/^$/, 'default', displayResourcesCallback);
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
        views: {},
        events: {
            'open-picker': function () {
                this.openPicker();
            },
            'create': function (event) {
                this.create(event.action, event.data, event.parentId);
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


            'manage-rights': function (event) {
                this.manageRights(event.ids[0]);
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
                    this.router.navigate(event.route, {trigger: true});
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
                this.router = new manager.Router(this.displayResources);
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
                            this.parameters.directoryId == 0 && path.unshift({id: 0});
                            this.views[view].render(resources, path, isSearchMode);
                            this.views[view].isAppended ||
                                this.parameters.parentElement.append(this.views[view].el)
                                && (this.views[view].isAppended = true);
                        }
                    });
                }
            });
        },
        displayCreationForm: function (resourceType, directoryId) {
            this.views['form'] || (this.views['form'] = new manager.Views.Form(this.dispatcher));
            $.ajax({
                url: this.parameters.appPath + '/resource/form/' + resourceType,
                success: function (form) {
//                    form = form.replace('_instanceId', directoryId);
                    this.views['form'].render(form, directoryId);
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
                    if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                        this.views['main'].subViews.resources.addThumbnails(data, this.views['form'].close());
                    } else {
                        this.views['form'].render(data, parentDirectoryId);
                    }
                }
            });
        },


        displayRenameForm: function (resourceId) {
            this.views['renameForm'] || (this.views['renameForm'] = new manager.Views.RenameForm(this.dispatcher));
            $.ajax({
                url: this.parameters.appPath + '/resource/rename/form/' + resourceId,
                success: function (form) {
//                    form = form.replace('_instanceId', resourceId);
                    this.views['renameForm'].render(form, resourceId);
                    this.views['renameForm'].isAppended ||
                        this.parameters.parentElement.append(this.views['renameForm'].el)
                        && (this.views['renameForm'].isAppended = true);
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
                    if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                        console.debug(data)
                        //this.views['main'].subViews.resources.addThumbnails(data, this.views['form'].close());
                    } else {
                        this.views['renameForm'].render(data, resourceId);
                    }
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
        /*
        rename: function (resourceId) {
            alert('rename ' + resourceId)
        },*/
        manageRights: function (resourceId) {
            alert('rights of ' + resourceId)
        },
        open: function (resourceType, resourceId) {
            window.location = this.parameters.appPath + '/resource/custom/' + resourceType + '/open/' + resourceId;
        },
        custom: function (action, resourceId) {
            alert('custom action "' + action + '" on resource ' + resourceId);
        },
        openPicker: function () {
            this.views.picker.isAppended || this.displayResources(0, 'picker');
            this.views.picker.$el.modal('show');
        }
    };

    manager.initialize = function (parameters) {
        parameters = parameters || {};
        parameters.directoryId = parameters.directoryId || '0';
        parameters.parentElement = parameters.parentElement || $('body');
        parameters.resourceTypes = parameters.resourceTypes || {};

        // needed ?
        parameters.workspaceDirectories = parameters.workspaceDirectories || [];
        parameters.isPickerOnly = parameters.isPickerOnly || false;
        parameters.appPath = parameters.appPath || '';
        manager.Controller.initialize(parameters);
    };

    manager.openPicker = function () {
        manager.Controller.openPicker();
    }
})();

$(function() {
    var container = $(document.createElement('div'))
        .attr('class', 'container')
        .prependTo('body');

    Claroline.ResourceManager.initialize({
        'directoryId': '0',
        'parentElement': container,
        'resourceTypes': {
            'file': {
                name: 'Fichier',
                isDownloadable: true,
                customActions: {
                    'foo': {
                        'name': 'Custom...'
                    }
                }
            },
            'directory': {
                name: 'Répertoire',
                isDownloadable: true
            },
            'text': {
                name: 'Texte'
            },
            'Forum': {
                name: 'Forum'
            },
            'HTMLElement': {
                name: 'Page html'
            },
            'Subject': {}
        },
        // needed ?
        'workspaceDirectories': [{id: '85', name: 'abc - Workspace 1'}, {id: '1', name: 'PERSO - admin'}],
        'isPickerOnly': false,
        'appPath': Routing.generate('claro_home_index') + '..'
    });
});