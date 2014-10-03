'use strict';
angular.module('portfolioApp').run([
    '$templateCache',
    function($templateCache) {
        $templateCache.put('templates/evaluation_form.tpl.html',
            '<div class="popover evaluation_popover">' +
                '<div class="arrow"></div>' +
                '<h3 class="popover-title">Evaluation</h3>' +
                '<div class="popover-content">' +
                    '<form name="popoverForm">' +
                        '<textarea ui-tinymce class="form-control" rows="3" name="userInformation[presentation]" data-ng-model="evaluatedWidget.comment"></textarea>' +
                        '<div class="form-actions">' +
                            '<button type="button" class="btn btn-danger cancel_button" data-ng-click="$hide()">Close</button>' +
                            '<button type="button" class="btn btn-primary" data-ng-click="$hide()">Save changes</button>' +
                        '</div>' +
                    '</form>' +
                '</div>' +
            '</div>');
    }
]);