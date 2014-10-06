'use strict';
angular.module('portfolioApp').run([
    '$templateCache',
    function($templateCache) {
        $templateCache.put('templates/evaluation_form.tpl.html',
            '<div class="popover evaluation_popover widget-panel" data-ng-controller="evaluateWidgetsController">' +
                '<div class="arrow"></div>' +
                '<h3 class="popover-title">Evaluation</h3>' +
                '<div class="popover-content">' +
                    '<form name="popoverForm" data-loading-form="save(widget)">' +
                        '<textarea ui-tinymce class="form-control" rows="3" name="widgetEvaluationComment" data-ng-model="widget.comment"></textarea>' +
                        '<div class="form-actions">' +
                            '<button type="submit" class="btn btn-primary">Save changes</button>' +
                            '<button type="button" class="btn btn-default cancel_button" data-ng-click="$hide()">Close</button>' +
                        '</div>' +
                    '</form>' +
                '</div>' +
            '</div>');
    }
]);