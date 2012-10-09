var isGlobalMode = true;

var Resource = Backbone.Model.extend();

var ResourceCollection = Backbone.Collection.extend({
    model: Resource,
    parentResourceId: 0,
    url: function () {
        return '../resource/children/' + this.parentResourceId
    }
});

var ResourceFormView = Backbone.View.extend({
    el: $('.modal'),
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
        var url = form.getAttribute('action');
        var data = new FormData(form);
        controller.submitFormAction(url, data);
    },
    render: function (form) {
        form = form.replace('_instanceId', controller.currentDirectoryId);
        $(this.el).html(Twig.render(modal_template, {
            'body': form
        })).modal();
    }
})

var ResourceBreadcrumbsView = Backbone.View.extend({
    el: $('.breadcrumb'),
    render: function (resources) {
        $(this.el).html(Twig.render(resource_breadcrumbs_template, {
            'resources': resources
        }));
    }
})

var ResourceActionsView = Backbone.View.extend({
    el: $('.actions'),
    events: {
        'click button.create': 'createResource',
        'click button.filter-btn': 'toggleFilters'
    },
    initialize: function () {
        this.resourceTypes = [];
        _.each($('.actions .resource-type'), function (element, index) {
            this.resourceTypes[index] = element.getAttribute('data-resource-type');
        }, this);
        this.filters = null;
    },
    createResource: function () {
        var resourceType = $('.create-resource select').val();
        controller.creationFormAction(resourceType);
    },
    toggleFilters: function () {
        if (this.filters == null) {
            this.filters = new ResourceFiltersView();
            this.filters.render(this.resourceTypes);
        }

        this.filters.toggle();
    },
    render: function () {
        $(this.el).html(Twig.render(resource_actions_template, {
            'resourceTypes': this.resourceTypes
        }));
    }
});

var ResourceFiltersView = Backbone.View.extend({
    el: $('.filters'),
    events: {
        'click input.datepicker': 'showDatepicker',
        'changeDate input.datepicker': 'closeDatepicker',
        'keydown input.datepicker': 'preventDateManualInput'
    },
    initialize: function () {
        this.isVisible = false;
    },
    showDatepicker: function (event) {
        $(event.currentTarget).datepicker('show');
    },
    closeDatepicker: function (event) {
        $(event.currentTarget).datepicker('hide');
    },
    preventDateManualInput: function (event) {
        event.preventDefault();
        $(event.currentTarget).datepicker('show');
    },
    toggle: function () {
        this.isVisible ?
            $(this.el).css('display', 'none'):
            $(this.el).css('display', 'block');
        this.isVisible = !this.isVisible;
    },
    render: function (resourceTypes) {
        var workspaceRoots = [];

        if (isGlobalMode) {
            _.each($('.filters .workspace-root'), function (element, index) {
                workspaceRoots[index] = {
                    'id': element.getAttribute('data-root-id'),
                    'name': element.getAttribute('data-root-name')
                }
            });
        }

        $(this.el).html(Twig.render(resource_filters_template, {
            'workspaceRoots': workspaceRoots,
            'resourceTypes': resourceTypes
        }));
    }
});



var resourceActions = new ResourceActionsView();
resourceActions.render();

var ResourceThumbnailsView = Backbone.View.extend({
    el: $('.resources'),
    events: {
        'click input.chk-instance' : 'test'
    },

    test: function(){
        router.navigate('test', {trigger: true});
    },

    render: function (resources) {
        var resourcesAttributes = [];
        resources.forEach(function (resource, key) {
            resourcesAttributes[key] = resource.attributes;
        });
        $(this.el).html(Twig.render(resource_thumbnails_template, {
            'instances': resourcesAttributes,
            'webRoot': Routing.generate('claro_admin_index') + "/../../.."
        }));
    }
});

var Router = Backbone.Router.extend({
    initialize: function () {
        this.route(/^resources\/(\d+)$/, 'thumbnails', controller.resourcesAction);
        this.route(/^resources\/creation-form\/([a-zA-Z]+)$/, 'creation-form', controller.creationFormAction);
    }
});

$('.resource-manager').ajaxError(function(event, jqXHR, settings, exception){
    if (jqXHR.status == 403) {
        window.location.reload();
    } else {
        alert('Error ' + jqXHR.status);
    }
});

var controller = {
    currentDirectoryId: 0,
    views: {},

    resourcesAction: function (parentId) {
        var resources = new ResourceCollection();
        resources.parentResourceId = parentId;
        resources.fetch({
            success: function () {
                controller.views['thumbnails'] = controller.views['thumbnails'] || new ResourceThumbnailsView();
                controller.views['thumbnails'].render(resources.models);
                $.ajax({
                    url: Routing.generate('claro_resource_parents', {'instanceId': parentId}),
                    success: function (resources) {
                        controller.views['breadcrumbs'] = controller.views['breadcrumbs'] || new ResourceBreadcrumbsView();
                        isGlobalMode && resources.unshift({id: 0});
                        controller.views['breadcrumbs'].render(resources);
                        controller.currentDirectoryId = parentId;
                    }
                });
            }
        });
    },

    creationFormAction: function (resourceType) {
        $.ajax({
            url: Routing.generate('claro_resource_creation_form', {'resourceType': resourceType}),
            success: function (form) {
                controller.views['form'] = controller.views['form'] || new ResourceFormView();
                controller.views['form'].render(form);
            }
        });
    },
    createAction: function (url, data) {

    },
    submitFormAction: function (formAction, formData, successHandler) {
        $.ajax({
            url: formAction,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            beforeSend: function (request) {
                request.setRequestHeader('X_Requested_With', 'XMLHttpRequest');
            },
            success: function (data, textStatus, jqXHR) {
                if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                    controller.resourcesAction(controller.currentDirectoryId);
                    controller.views['form'].close();
                } else {
                    alert('invalid response (expected json content)');
                }
            }
        });
    }
};

var router = new Router();
Backbone.history.start();

// force loading first level on page load... (ws ?)
window.location.hash !== '' || controller.resourcesAction(0);
