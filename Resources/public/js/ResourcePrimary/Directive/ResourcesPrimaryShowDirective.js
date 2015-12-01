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
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/ResourcePrimary/Partial/show.html',
                scope: {
                    resources : '=' // Resources of the Step
                },
                bindToController: true,
                link: function (scope, element, attrs) {
                    var iframeChangeTimeout = null;

                    var activityFrame = element.find('iframe');

                    var resizeIframe = function (activityFrame) {
                        var height = $(activityFrame).contents().find('body').first().height();

                        if (height) {
                            $(activityFrame).css('height', height + 15);
                        }
                    };

                    // Manage the height of the iFrame
                    $(activityFrame).load(function () {
                        var iframe = this;
                        setTimeout(function () {
                            resizeIframe(iframe);
                        }, 50);
                    });

                    $(window).on('resize', function () {
                        clearTimeout(iframeChangeTimeout);
                        iframeChangeTimeout = setTimeout(function () {
                            $(activityFrame).each(function () {
                                resizeIframe(this);
                            });
                        }, 300);
                    });

                    clearTimeout(iframeChangeTimeout);
                    iframeChangeTimeout = setTimeout(function () {
                        $(activityFrame).each(function () {
                            resizeIframe(activityFrame);
                        });
                    }, 300);
                }
            };
        }
    ]);
})();
