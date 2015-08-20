(function() {
    $('.add-new-resource-type').click(function() {
        var $resourceTypeInput = $('input[name="resource_type"]'),
            resourceTypeName = $resourceTypeInput.val(),
            routing = window.Routing.generate('formalibre_add_new_resource_type', {'name': resourceTypeName});

        $.ajax({
            url: routing,
            type: 'post',
            success: function(data) {
                if (data.error == 'empty_string') {
                    window.Claroline.Modal.confirmContainer(t('error_'), t('error.empty_input'));
                } else if (data.error == 'resource_type_exists') {
                    window.Claroline.Modal.confirmContainer(t('error_'), t('error.resource_type_already_exists'));
                } else {
                    var $newResourceType = $('<div class="col-sm-6">' +
                        '<div class="list-group">' +
                        '<div class="list-group-item active" data-resource-type-id="'+ data.id +'"><div class="pull-right" style="margin: -5px -5px 0 0;"><button class="btn btn-sm btn-success add-new-resource" title="Ajouter une ressource" data-toggle="tooltip"><span class="fa fa-plus"></span></button> ' +
                        '<button class="btn btn-sm btn-warning modify-resource-type" title="'+ t("modify_resource_type_name") +'" data-toggle="tooltip"><span class="fa fa-pencil"></span></button> ' +
                        '<button class="btn btn-sm btn-danger delete-resource-type" title="Supprimer ce type de ressources" data-toggle="tooltip"><span class="fa fa-trash"></span></button></div>' +
                        '<div class="list-group-item-title">'+ resourceTypeName +'</div>' +
                        '</div>' +
                        '<div class="list-group-item no-resource-yet">'+ t("no_resources_in_resource_type") +'</div>' +
                        '</div>' +
                        '</div>').hide();

                    $newResourceType.appendTo('.list-group-resource-type').slideDown('slow');
                    $resourceTypeInput.val('');
                    $('[data-toggle="tooltip"]').tooltip();
                }
            }
        });
    });

    $('body')
        .on('click', '.delete-resource-type', function() {
            var $div = $(this).parents('div.active'),
                resourceTypeId = $div.data('resource-type-id'),
                routing = window.Routing.generate('formalibre_delete_resource_type', {id: resourceTypeId});

            window.Claroline.Modal.confirmRequest(
                routing, onResourceTypeDeleted,
                resourceTypeId,
                t('confirm_resource_type_deletion_content'),
                t('confirm_resource_type_deletion_title')
            );
        })
        .on('click', '.modify-resource-type', function() {
            var $div = $(this).parents('div.active'),
                resourceTypeId = $div.data('resource-type-id'),
                routing = window.Routing.generate('formalibre_modify_resource_type_name', {id: resourceTypeId});

            window.Claroline.Modal.displayForm(routing, displayResourceTypeChanges, function(){}, 'form-resource');
        })
        .on('click', '.add-new-resource', function() {
            var $div = $(this).parents('div.active'),
                resourceTypeId = $div.data('resource-type-id'),
                routing = window.Routing.generate('formalibre_add_new_resource', {id: resourceTypeId});

            window.Claroline.Modal.displayForm(routing, displayNewResource, function() {}, 'form-resource');
        })
        // Show resource form when click on the name of the resource in the list-group
        .on('click', 'a[data-resource-id]', function(e) {
            e.preventDefault();
            var $a = $(this),
                resourceId = $a.data('resource-id'),
                routing = window.Routing.generate('formalibre_modification_resource', {id: resourceId});

            window.Claroline.Modal.displayForm(routing, displayModificationResource, function(){}, 'form-resource');
        })
        .on('click', '.delete-resource', function() {
            var resourceId = $(this).data('resource-id'),
                routing = window.Routing.generate('formalibre_delete_resource', {id: resourceId});

            window.Claroline.Modal.confirmRequest(
                routing, onResourceDeleted,
                resourceId,
                t('confirm_resource_deletion_content'),
                t('confirm_resource_deletion_title')
            );
        })
    ;

    function onResourceDeleted(event, successParameter)
    {
        var $resourceDeleted = $('.list-group-item[data-resource-id="'+ successParameter +'"]'),
            $resourceTypeList = $resourceDeleted.parents('.list-group');

        if ($resourceTypeList.children().length === 2) {
            $resourceTypeList.append('<div class="list-group-item no-resource-yet">'+ t("no_resources_in_resource_type") +'</div>');
        }
        $resourceDeleted.slideUp('slow', function() {
            $(this).remove();
        });
    }

    function onResourceTypeDeleted(event, successParameter)
    {
        $('div[data-resource-type-id="'+ successParameter +'"]').parents('.col-sm-6').slideUp('slow', function () {
            $(this).remove();
        });
    }

    function displayResourceTypeChanges(data)
    {
        $('div[data-resource-type-id="'+ data.id +'"]').find('.list-group-item-title').text(data.name);
    }

    var displayNewResource = function(data) {
        var $newResource = $('<a class="list-group-item" href="#" data-resource-id="'+ data.resource.id +'">'+ data.resource.name +'</a>'),
            $listGroup = $('div.list-group > div.list-group-item[data-resource-type-id="'+ data.resourceTypeId +'"]').parent();

        $listGroup.find('.no-resource-yet').slideUp(
            'slow',
            function() {
                $(this).remove();
            });

        $listGroup.append($newResource);
    };

    var displayModificationResource = function(data) {
        $('a[data-resource-id="'+ data.id +'"]').text(data.name);
    };

    function t(key)
    {
        return Translator.trans(key, {}, 'reservation');
    }
}) ();
