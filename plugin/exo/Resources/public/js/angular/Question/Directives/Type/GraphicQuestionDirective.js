/**
 * Graphic Question Directive
 * Manages Question of type Graphic
 *
 * @param   {FeedbackService} FeedbackService
 * @param   {Object}          $window
 * @returns {Object}
 * @constructor
 */
var GraphicQuestionDirective = function GraphicQuestionDirective(FeedbackService, $window) {
    return angular.merge({}, AbstractQuestionDirective.apply(this, arguments), {
        controller: 'GraphicQuestionCtrl',
        controllerAs: 'graphicQuestionCtrl',
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/Partials/Type/graphic.html',
        scope: {
            includeCorrection: '='
        },
        link: {
            post: function postLink(scope, element, attr, controller) {
                // Get a reference of the img object for pointers
                controller.$image = element.find('.img-question');

                var addPointer = function addPointer(event) {
                    if (controller.answer.length < controller.question.pointers && !controller.feedback.visible) {
                        var rect = controller.$image.get(0).getBoundingClientRect(); // Get position of the img based on the viewport

                        var x = event.pageX - rect.left - $window.pageXOffset;
                        var y = event.pageY - rect.top - $window.pageYOffset;

                        var originalHeight = controller.question.image.height;
                        var originalWidth = controller.question.image.width;

                        // Notify angular of the changes
                        scope.$apply(function () {
                            // Add new pointer at the click position
                            // As [x, y] represents the coords INSIDE the image (so there are some calculations to get it)
                            controller.answer.push({
                                x: x * (originalWidth / rect.width),
                                y: y * (originalHeight / rect.height)
                            });
                        });
                    }

                    event.preventDefault();
                };

                // Add a pointer to image (we use jQuery to have access to the normalized .pageX, .pageY for mouse position)
                element.on('click', '.img-question', addPointer);

                // Manually show feedback (as we override the default postLink method)
                if (FeedbackService.isVisible()) {
                    controller.onFeedbackShow();
                }

                scope.$on('$destroy', function () {
                    element.off('click', '.img-question', addPointer);
                });
            }
        }
    });
};

// Extends AbstractQuestionDirective
GraphicQuestionDirective.prototype = Object.create(AbstractQuestionDirective.prototype);

// Set up dependency injection (get DI from parent too)
GraphicQuestionDirective.$inject = AbstractQuestionDirective.$inject.concat([ '$window' ]);

// Register directive into AngularJS
angular
    .module('Question')
    .directive('graphicQuestion', GraphicQuestionDirective);
