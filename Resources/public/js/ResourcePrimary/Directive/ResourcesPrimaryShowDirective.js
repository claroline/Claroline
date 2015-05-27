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
                bindToController: true
            };
        }
    ]);
})();


/*$(document).ready(function() {
 // Enable tooltip
 $('*').tooltip({ placement: 'top' });

 var $frame = $('iframe#{{ currentStep.activity.primaryResource.id }}');

 // Resize IFrame on load
 $frame.load(function () {
 resizeIframe($(this));

 // Observe DOM modifications to resize IFrame to fit content
 var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;
 var observer = new MutationObserver(function (mutations, observer) {
 resizeIframe($frame);
 });

 observer.observe($frame.get(0).contentDocument.body, {
 subtree: true,
 childList: true
 });

 $frame.on('resize', function () {
 resizeIframe($frame);
 });
 }).attr('src', '{{ path('claro_resource_open', {'node': currentStep.activity.primaryResource.id, 'resourceType': currentStep.activity.primaryResource.resourceType.name }) }}');

 // Resize IFrame on window resize
 $(window).on('resize', function () {
 resizeIframe($frame);
 });
 });

 function resizeIframe(frame) {
 var height = frame.contents().height();
 frame.animate({ height: height }, 100, function() {});
 }*/