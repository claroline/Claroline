'use strict';

angular.module('portfolioApp').run([
    '$templateCache',
    function($templateCache) {
        $templateCache.put('templates/collection-form_directive.tpl.html',
        '<p>' +
            '<label for="formation_name">{{ \'formation_title\'|trans }}</label>' +
            '<input type="text" class="form-control" id="formation_name" name="icap_portfolio_widget_form_formations[formations][name]" data-ng-model="widget.name" required="required" />' +
        '</p>' +
        '<p class="input-daterange input-group" data-b-datepicker="{{ \'datepicker_date_format\'|trans }}" id="datepicker">' +
            '<span class="input-group-addon">{{ \'date_range_from\'|trans }}</span>' +
            '<input type="text" data-date-time-input="{{ \'date_field_format\'|trans }}" class="input-sm form-control" name="startDate" data-ng-model="widget.startDate" required="required" />' +
            '<span class="input-group-addon">{{ \'date_range_to\'|trans }}</span>' +
            '<input type="text" data-date-time-input="{{ \'date_field_format\'|trans }}" class="input-sm form-control" name="endDate" data-ng-model="widget.endDate" required="required" />' +
        '</p>' +
        '<p>' +
            '<button type="button" class="btn btn-sm btn-success" data-ui-resource-picker="resourcePickerConfig" data-ui-resources="collection">' +
                '{{ \'formation_add_resource\'|trans }}' +
            '</button>' +
        '</p>' +
        '<ul class="list-unstyled">' +
            '<li data-ng-repeat="resource in collection">' +
                '<div data-ng-class="{\'input-group\': isAdded(resource)}">' +
                    '<input type="text" readonly="readonly" data-ng-disabled="resource.toDelete" class="input-sm form-control" name="resource" value="{{ resource.name }}" />' +
                    '<input type="hidden" class="form-control" id="formation_{{ $index }}_resource" name="icap_portfolio_widget_form_formations[resources][{{ $index }}]" data-ng-model="resource.resource" />' +
                    '<span class="input-group-btn" data-ng-if="isAdded(resource)">' +
                        '<button aria-label="{{ \'formation_delete_resource\'|trans }}" class="btn btn-sm btn-danger" type="button" data-ng-if="!isEmpty(resource) && !resource.toDelete" data-ng-click="deleteChild(resource)">' +
                            '<span class="fa fa-trash-o"></span>' +
                        '</button>' +
                        '<button aria-label="{{ \'formation_cancel_deletion_resource\'|trans }}" class="btn btn-sm btn-default" type="button" data-ng-if="resource.toDelete" data-ng-click="cancelDeletionOfChild(resource)">' +
                            '<span class="fa fa-undo"></span>' +
                        '</button>' +
                    '</span>' +
                '</div>' +
            '</li>' +
        '</ul>');
    }
]);