/**
 * Manages Primary Resources
 */
(function () {
    'use strict';

    angular.module('ResourcePrimaryModule').directive('resourcesPrimaryShow', [
        function ResourcesPrimaryEditDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: ResourcesPrimaryShowCtrl,
                controllerAs: 'resourcesPrimaryShowCtrl',
                template: '<iframe id="embeddedActivity" style="width: 100%; min-height: {{ resourcesPrimaryShowCtrl.height }}px;" data-ng-src="{{ resourcesPrimaryShowCtrl.resourceUrl.url }}" allowfullscreen></iframe>',
                scope: {
                    resources : '=', // Resources of the Step
                    height    : '='  // Min height for Resource display
                },
                bindToController: true,
                link: function (scope, element, attr) {
                    $(window).on('message',function(e) {

                        if (  (typeof e.originalEvent.data === 'string') && (e.originalEvent.data.indexOf('documentHeight:') > -1) ) {

                            // Split string from identifier
                            var height = e.originalEvent.data.split('documentHeight:')[1];

                            // do stuff with the height
                            $(element).css('height', parseInt(height) + 15);
                        }
                    });
                }
            };
        }
    ]);
})();
