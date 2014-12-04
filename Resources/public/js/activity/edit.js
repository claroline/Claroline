(function () {
    'use strict';

    var routing = window.Routing;
    var translator = window.Translator;
    var modal = window.Claroline.Modal;
    var common = window.Claroline.Common;
    var activity = window.Claroline.Activity;
    var manager = window.Claroline.ResourceManager;

    /**
     * Add a dropdown menu for a new tab resource
     */
    function activityTabMenu(activity, resource)
    {
        return common.createElement('li', 'activity-tab-' + resource.id).html(
            common.createElement('a', 'pointer-hand')
            .attr('data-id', 'resource-' + resource.id)
            .append(resource.name)
            .append(
                common.createElement('div', 'dropdown')
                .append(
                    common.createElement('i', 'fa fa-chevron-down pointer-hand')
                    .attr('data-toggle', 'dropdown')
                )
                .append(
                    common.createElement('ul', 'dropdown-menu').html(
                        common.createElement('li').html(
                            common.createElement('i', 'activity-remove-resource fa fa-trash-o')
                            .data('resource', resource.id)
                            .data('activity', activity)
                            .html(
                                ' ' + translator.trans('remove', {}, 'platform')
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * get resource content
     */
    function activityContent(resource)
    {
        return common.createElement('div', 'hide').attr('id', 'resource-' + resource.id).html(
            common.createElement('iframe', 'activity-iframe')
            .attr(
                'src', routing.generate('claro_resource_open', {'resourceType': resource.type, 'node': resource.id})
            )
            .load(function () {
                var iframe = this;
                setTimeout(function () {
                    activity.height(iframe);
                }, 50);
            })
        );
    }

    /**
     * Add a new resource to an activity
     */
    function addResource(activity, resource)
    {
        $.ajax(routing.generate('claro_activity_add', {'activity': activity, 'resource': resource}))
        .done(function (data) {
            if (data !== 'false') {
                var resource = $.parseJSON(data);

                $('.activities').append(
                    activityContent(resource)
                );

                $('.activity-tabs .activity-primary').after(
                    activityTabMenu(activity, resource)
                );
            } else {
                modal.simpleContainer(
                    translator.trans('add_resource', {}, 'platform'),
                    translator.trans('activity_already', {}, 'platform')
                );
            }
        })
        .error(function () {
            modal.error();
        });
    }

    /**
     * Remove a resource from an activity
     */
    function removeResource(activity, resource)
    {
        $.ajax(routing.generate('claro_activity_remove_resource', {'activity': activity, 'resource': resource}))
        .done(function (data) {
            if (data === 'true') {
                $('.activity-tab-' + resource).hide('slow', function () {
                    $('.activities #resource-' + resource).remove();
                    $('.activity-tabs > li').removeClass('active');
                    $('.activity-tabs > li.activity-primary').addClass('active');
                    $('.activities > div').addClass('hide');
                    $('#activity-primary').removeClass('hide');
                });
            } else {
                modal.error();
            }
        })
        .error(function () {
            modal.error();
        });
    }

    /**
     *
     */
    function removePrimaryResource(activity)
    {
        if (activity) {
            $.ajax(routing.generate('claro_activity_remove_primary_resource', {'activity': activity}))
            .done(function () {
                $('#activity-primary iframe, #activity-primary hr, #activity-primary .remove-primary-resource')
                .hide('slow', function () {
                    $(this).remove();
                });
            })
            .error(function () {
                modal.error();
            });
        } else {
            modal.error();
        }
    }

    /** Events **/

    $('body').on('click', '.add-resource', function () {
        var activity = $(this).data('id');

        if (!manager.hasPicker('activityResourcePicker')) {
            manager.createPicker('activityResourcePicker', {
                callback: function (nodes) {
                    var nodeId = _.keys(nodes)[0];
                    addResource(activity, nodeId);
                },
                typeBlackList: ['activity']
            }, true);
        } else {
            manager.picker('activityResourcePicker', 'open');
        }
    })
    .on('click', '.activity-tabs .activity-remove-resource', function () {
        var activity = $(this).data('activity');
        var resource = $(this).data('resource');

        modal.fromRoute('claro_activity_remove_resource_confirm', {}, function (element) {
            element.on('click', '.btn-primary', function () {
                modal.hide();
                removeResource(activity, resource);
            });
        });
    }).on('click', '.remove-primary-resource', function () {
        var activity = $(this).data('id');

        modal.fromRoute('claro_activity_remove_resource_confirm', {}, function (element) {
            element.on('click', '.btn-primary', function () {
                modal.hide();
                removePrimaryResource(activity);
            });
        });
    });

}());
