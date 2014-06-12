(function () {
    'use strict';

    var routing = window.Routing;
    var translator = window.Translator;
    var modal = window.Claroline.Modal;
    var common = window.Claroline.Common;
    var picker = window.Claroline.ResourcePicker;


    /**
     * Add a dropdown menu for a new tab resource
     */
    function activityMenu(activity, resource)
    {
        return common.createElement('li', 'activity-tab-' + resource.id).html(
            common.createElement('a', 'pointer-hand')
            .attr('data-id', 'resource-' + resource.id)
            .append(resource.name)
            .append(
                common.createElement('div', 'dropdown')
                .append(
                    common.createElement('i', 'icon-chevron-down pointer-hand')
                    .attr('data-toggle', 'dropdown')
                )
                .append(
                    common.createElement('ul', 'dropdown-menu').html(
                        common.createElement('li').html(
                            common.createElement('i', 'activity-delete icon-trash')
                            .data('resource', resource.id)
                            .data('activity', activity)
                            .html(
                                ' ' + translator.get('platform:delete')
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * Add a new resource to an activity
     */
    function addResource(activity, resource)
    {
        $.ajax(routing.generate('activity_add', {'activity': activity, 'resource': resource}))
        .done(function (data) {
            if (data !== 'false') {
                var resource = $.parseJSON(data);

                $('.activities').append(
                    common.createElement('div', 'hide').attr('id', 'resource-' + resource.id).html(resource.name)
                );

                $('.activity-tabs .activity-primary').after(
                    activityMenu(activity, resource)
                );
            } else {
                modal.simpleContainer(
                    translator.get('platform:add_resource'),
                    translator.get('platform:activity_already')
                );
            }
        })
        .error(function () {
            modal.error();
        });
    }

    /**
     * Delete a resource from an activity
     */
    function deleteResource(data, activity, resource)
    {
        var picker = modal.create(data);

        picker.on('click', 'button.btn-primary', function () {
            modal.hide();

            $.ajax(routing.generate('activity_delete', {'activity': activity, 'resource': resource}))
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
        });
    }

    /** Events **/

    $('body').on('click', '.add-resource', function () {
        var activity = $(this).data('id');

        picker.open(function (nodes) {
            var nodeId = _.keys(nodes)[0];

            if (nodeId) {
                addResource(activity, nodeId);
            } else {
                modal.error();
            }
        });
    })
    .on('click', '.activity-tabs .activity-delete', function () {
        var activity = $(this).data('activity');
        var resource = $(this).data('resource');

        $.ajax(
            routing.generate(
                'claro_activity_delete_resource',
                {'activity': activity, 'resource': resource}
            )
        )
        .done(function (data) {
            deleteResource(data, activity, resource);
        })
        .error(function () {
            modal.error();
        });
    });

}());
