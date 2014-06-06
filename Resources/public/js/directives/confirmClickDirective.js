'use strict';

portfolioApp
    .directive('confirmClick', ["$parse", "translationService", function ($parse, translationService) {
        function link(scope, element, attributes) {
            var clickAction = attributes.confirmClick;
            element.confirmModal({
                'confirmTitle'   : translationService.trans('widget_delete_confirm_title'),
                'confirmMessage'   : translationService.trans('widget_delete_confirm_message'),
                'confirmOk'   : translationService.trans('widget_delete_confirm_ok'),
                'confirmCancel'   : translationService.trans('widget_delete_confirm_cancel'),
                'confirmCallback': function() {scope.$eval(clickAction)}
            });
        }

        var directive = {
            link: link,
            restrict: 'A'
        };

        return directive;
    }]);