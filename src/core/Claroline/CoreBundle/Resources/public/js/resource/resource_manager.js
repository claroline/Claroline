(function () {
    this.Claroline = this.Claroline || {};
    this.Claroline.ResourceManager = manager = {};

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

    manager.Views = {
        Master: Backbone.View.extend({
            tagName: 'div',
            initialize: function (parameters) {
                this.parameters = parameters;

                if (parameters.isPickerMode) {
                    this.el.className = 'picker resource-manager modal hide';
                    this.wrapper = $('<div class="modal-body"/>');
                    $(this.el).append(this.wrapper);
                } else {
                    this.el.className = 'main resource-manager';
                    this.wrapper = $(this.el);
                }

                this.subViews = {
                    breadcrumbs: new manager.Views.Breadcrumbs(parameters),
                    actions: new manager.Views.Actions(parameters),
                    resources: new manager.Views.Resources(parameters)
                };
            },
            addThumbnails: function (resources, successHandler) {
                for (var i = 0; i < resources.length; ++i) {
                    var thumbnail = new manager.Views.Thumbnail(this.parameters);
                    thumbnail.render(resources[i]);
                    this.subViews.resources.$el.append(thumbnail.$el);
                }

                successHandler && successHandler();
            },
            removeThumbnails: function (resourceIds) {
                for (var i = 0; i < resourceIds.length; ++i) {
                    this.subViews.resources.$('#' + resourceIds[i]).remove();
                }

                this.subViews.actions.checkedResourceIds = [];
            },
            render: function (resources, path) {
                this.subViews.breadcrumbs.render(path);
                this.subViews.actions.render(_.last(path).id);
                this.subViews.resources.render(resources);
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
            initialize: function (parameters) {
                 this.delegateEvents({
                    'click a' : function (event) {
                        event.preventDefault();
                        parameters.isPickerMode ?
                            manager.Controller.displayDirectory(
                                event.currentTarget.getAttribute('data-resource-id'),
                                'picker'
                            ) :
                            manager.Controller.router.navigate(
                                event.currentTarget.getAttribute('data-route'),
                                {trigger: true}
                            );
                    }
                })
            },
            render: function (resources) {
                $(this.el).html(Twig.render(resource_breadcrumbs_template, {
                    'resources': resources
                }));
            }
        }),
        Actions: Backbone.View.extend({
            tagName: 'div',
            className: 'actions clearfix',
            events: {
                'click button.create': function () {
                    var resourceType = this.$('.create-resource select').val();
                    manager.Controller.displayCreationForm(resourceType);
                },
                'click button.delete': function () {
                    manager.Controller.delete_(this.checkedResourceIds);
                },
                'click button.download': function () {
                    manager.Controller.download(this.checkedResourceIds);
                },
                'click button.copy': function () {
                    this.isReadyToPaste = true;
                    this.$('button.paste').removeAttr('disabled');
                },
                'click button.paste': function () {
                    manager.Controller.add(this.checkedResourceIds);
                },
                'click button.open-picker': function () {
                    manager.Controller.openPicker();
                },
                'click button.filter': function () {
                    if (!this.filters) {
                        this.filters = new manager.Views.Filters(this.parameters);
                        this.filters.render(this.resourceTypes);
                        $(this.el).after(this.filters.el);
                    }

                    this.filters.toggle();
                }
            },
            initialize: function (parameters) {
                this.parameters = parameters;
                this.isReadyToPaste = false;
                this.checkedResourceIds = [];
                this.checkedResourcesDirectory = this.currentDirectory = parameters.directoryId;
                manager.Dispatcher.on('thumbnail-check-status', function (event) {
                    if (event.isPickerMode == this.parameters.isPickerMode) {
                        if (this.checkedResourcesDirectory != this.currentDirectory) {
                            this.checkedResourceIds = [];
                            this.checkedResourcesDirectory = this.currentDirectory;
                            this.$('button.paste').attr('disabled', 'disabled');
                        }

                        if (this.isReadyToPaste) {
                            this.isReadyToPaste = false;
                            this.$('button.paste').attr('disabled', 'disabled');
                        }

                        var idIndex = this.checkedResourceIds.indexOf(event.resourceId);
                        idIndex == -1 ?
                            this.checkedResourceIds.push(event.resourceId) :
                            this.checkedResourceIds.splice(idIndex, 1);
                        var checkedResourcesCount = this.checkedResourceIds.length;


                        if (event.isPickerMode) {
                            if (manager.Controller.directories['picker'].id != 0) {
                                checkedResourcesCount > 0 ?
                                    this.$('button.add').removeAttr('disabled'):
                                    this.$('button.add').attr('disabled', 'disabled');
                            }
                        } else {
                            if (manager.Controller.directories['main'].id != 0) {
                                this.$('button.on-checked-thumbnails').each(function () {
                                    checkedResourcesCount > 0 ?
                                        $(this).removeAttr('disabled') :
                                        $(this).attr('disabled', 'disabled');
                                });
                            } else {
                                checkedResourcesCount > 0 ?
                                    this.$('button.download').removeAttr('disabled'):
                                    this.$('button.download').attr('disabled', 'disabled');
                            }
                        }
                    }
                }, this);
            },
            render: function (directoryId) {
                this.currentDirectory = directoryId;
                $(this.el).html(Twig.render(resource_actions_template, this.parameters));
            }
        }),
        Filters: Backbone.View.extend({
            tagName: 'div',
            className: 'filters',
            events: {
                'click input.datepicker': function (event) {
                    this.$(event.currentTarget).datepicker('show');
                },
                'changeDate input.datepicker': function (event) {
                    this.$(event.currentTarget).datepicker('hide');
                },
                'keydown input.datepicker': function (event) {
                    event.preventDefault();
                    this.$(event.currentTarget).datepicker('show');
                }
            },
            initialize: function (parameters) {
                this.parameters = parameters;
            },
            toggle: function () {
                this.isVisible ?
                    $(this.el).css('display', 'none'):
                    $(this.el).css('display', 'block');
                this.isVisible = !this.isVisible;
            },
            render: function () {
                $(this.el).html(Twig.render(resource_filters_template, this.parameters));
            }
        }),
        Thumbnail: Backbone.View.extend({
            tagName: 'span',
            className: 'resource-thumbnail',
            initialize: function (parameters) {
                 this.parameters = parameters;
                 this.delegateEvents({
                    'click img': function (event) {
                        if (event.currentTarget.getAttribute('data-type') == 'directory') {
                            var directoryId = event.currentTarget.getAttribute('data-id');
                            parameters.isPickerMode ?
                                manager.Controller.displayDirectory(directoryId, 'picker') :
                                manager.Controller.router.navigate('resources/' + directoryId, {trigger: true});
                        }
                    },
                    'click input[type=checkbox]': function (event) {
                        manager.Dispatcher.trigger('thumbnail-check-status', {
                            resourceId: event.currentTarget.getAttribute('value'),
                            isChecked: event.currentTarget.checked,
                            isPickerMode: this.parameters.isPickerMode
                        });
                    }
                });
            },
            render: function (resource) {
                this.el.id = resource.id;
                $(this.el).html(Twig.render(resource_thumbnail_template, {
                    'resource': resource,
                    'webRoot': this.parameters.appPath + '/..'
                }));
            }
        }),
        Resources: Backbone.View.extend({
            tagName: 'div',
            className: 'resources',
            initialize: function (parameters) {
                 this.parameters = parameters;
            },
            render: function (resources) {
                this.$el.empty();

                for (var i = 0; i < resources.length; ++i) {
                    var thumbnail = new manager.Views.Thumbnail(this.parameters);
                    thumbnail.render(resources[i]);
                    $(this.el).append(thumbnail.$el);
                }
            }
        }),
        Form: Backbone.View.extend({
            tagName: 'div',
            className: 'resource-form modal hide',
            events: {
                'submit form': 'submit'
            },
            initialize: function () {
                this.on('close', this.close, this);
            },
            close: function () {
                $(this.el).modal('hide');
            },
            submit: function (event) {
                event.preventDefault();
                var form = $(this.el).find('form')[0];
                var action = form.getAttribute('action');
                var data = new FormData(form);
                manager.Controller.create(action, data);
            },
            render: function (form) {
                $(this.el).html(Twig.render(modal_template, {
                    'body': form
                })).modal();
            }
        }),
        Modal: Backbone.View.extend({
            tagName: 'div',
            className: 'modal hide',
            close: function () {
                $(this.el).modal('hide');
            },
            render: function (content) {
                $(this.el).html(Twig.render(modal_template, content)).modal();
            }
        })
    };

    manager.Router = Backbone.Router.extend({
        initialize: function () {
            this.route(/^resources\/(\d+)$/, 'thumbnails', manager.Controller.displayDirectory);
            this.route(/^$/, 'default', manager.Controller.displayDirectory);
            //this.route(/^resources\/creation-form\/([a-zA-Z]+)$/, 'creation-form', controller.creationFormAction);
        }
    });

    manager.Dispatcher = _.extend({}, Backbone.Events);

    manager.Controller = {
        parameters: {},
        views: {},
        directories: {},
        router: null,
        initialize: function (parameters) {
            this.parameters = parameters;
            var resourcesPath = parameters.appPath + '/resource/children';
            var modes = parameters.isPickerOnly ? ['picker'] : ['main', 'picker'];

            for (var i = 0; i < modes.length; ++i) {
                this.directories[modes[i]] = new manager.Collections.Directory([], parameters.directoryId, resourcesPath);
                var viewParameters = _.extend({}, parameters);
                viewParameters.isPickerMode = modes[i] == 'picker';
                this.views[modes[i]] = new manager.Views.Master(viewParameters);
            }

            if (!parameters.isPickerOnly) {
                this.router = new manager.Router();
                Backbone.history.start() || this.displayDirectory(parameters.directoryId, 'main');
            }
        },
        displayDirectory: function (directoryId, mode) {
            var _this = manager.Controller;
            mode = mode && mode == 'picker' ? mode : 'main';
            _this.directories[mode].id = directoryId || 0;
            _this.directories[mode].fetch({
                success: function (resourceData) {
                    $.ajax({
                        url: _this.parameters.appPath + '/resource/parents/' + _this.directories[mode].id,
                        success: function (path) {
                            var resources = [];
                            resourceData.forEach(function (resource, key) {
                                resources[key] = resource.attributes;
                            });
                            _this.parameters.directoryId == 0 && path.unshift({id: 0});
                            _this.views[mode].render(resources, path);
                            _this.views[mode].isAppended ||
                                _this.parameters.parentElement.append(_this.views[mode].el)
                                && (_this.views[mode].isAppended = true);
                        }
                    });
                }
            });
        },
        displayCreationForm: function (resourceType) {
            var _this = manager.Controller;
            _this.views['form'] || (_this.views['form'] = new manager.Views.Form());
            $.ajax({
                url: _this.parameters.appPath + '/resource/form/' + resourceType,
                success: function (form) {
                    form = form.replace('_instanceId', _this.directories['main'].id);
                    _this.views['form'].render(form);
                    _this.views['form'].isAppended ||
                        _this.parameters.parentElement.append(_this.views['form'].el)
                        && (_this.views['form'].isAppended = true);
                }
            });
        },
        create: function (formAction, formData) {
            var _this = manager.Controller;
            $.ajax({
                url: formAction,
                data: formData,
                type: 'POST',
                processData: false,
                contentType: false,
                headers: {'X_Requested_With': 'XMLHttpRequest'},
                success: function (data, textStatus, jqXHR) {
                    if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                        _this.views['main'].addThumbnails(data, _this.views['form'].close());
                    } else {
                        data = data.replace('_instanceId', _this.directories['main'].id);
                        _this.views['form'].render(data);
                    }
                }
            });
        },
        delete_: function (resourceIds) {
            var _this = manager.Controller;
            var ids = {};
            _.map(resourceIds, function (el, i) {
                ids[i] = el;
            });
            $.ajax({
                url: _this.parameters.appPath + '/resource/multidelete',
                data: ids,
                contentType: false,
                headers: {'X_Requested_With': 'XMLHttpRequest'},
                success: function () {
                    _this.views.main.removeThumbnails(resourceIds);
                }
            });
        },
        add: function (resourceIds) {
            var _this = manager.Controller;
            var ids = {};
            _.map(resourceIds, function (el, i) {
                ids[i] = el;
            });
            $.ajax({
                url: _this.parameters.appPath + '/resource/workspace/multi/add/' + _this.directories.main.id,
                data: ids,
                contentType: false,
                headers: {'X_Requested_With': 'XMLHttpRequest'},
                success: function (data) {
                    _this.views.main.addThumbnails(data);
                }
            });
        },
        download: function (resourceIds) {
            var _this = manager.Controller;
            var ids = {};
            _.map(resourceIds, function (el, i) {
                ids[i] = el;
            });
            window.location = _this.parameters.appPath + '/resource/multiexport?' + $.param(ids);
        },
        openPicker: function () {
            var _this = manager.Controller;
            _this.views.picker.isAppended || _this.displayDirectory(0, 'picker');
            _this.views.picker.$el.modal('show');
        }
    };

    manager.initialize = function (parameters) {
        parameters = parameters || {};
        parameters.directoryId = parameters.directoryId || '0';
        parameters.parentElement = parameters.parentElement || $('body');
        parameters.resourceTypes = parameters.resourceTypes || [];
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
        'resourceTypes': ['file', 'directory', 'text', 'Forum', 'HTMLElement'],
        'workspaceDirectories': [{id: '1', name: 'abc - Workspace 1'}, {id: '7', name: 'w2 - Workspace 2'}],
        'isPickerOnly': false,
        'appPath': Routing.generate('claro_home_index') + '..'
    });
});