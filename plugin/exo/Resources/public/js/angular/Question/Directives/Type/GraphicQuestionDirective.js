/**
 * Graphic Question Directive
 * Manages Question of type Graphic
 *
 * @param   {FeedbackService} FeedbackService
 * @param   {Function}        $timeout
 * @returns {Object}
 * @constructor
 */
var GraphicQuestionDirective = function GraphicQuestionDirective(FeedbackService, $timeout) {
    return angular.merge({}, AbstractQuestionDirective.apply(this, arguments), {
        controller: 'GraphicQuestionCtrl',
        controllerAs: 'graphicQuestionCtrl',
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/Partials/Type/graphic.html',
        link: {
            post: function postLink(scope, element, attr, controller) {
                // in case of coming from a js plumb question
                $timeout(function(){
                    controller.initPreviousAnswers();
                    controller.initDragAndDrop();

                    // Manually show feedback (as we override the default postLink method)
                    if (FeedbackService.isVisible()) {
                        controller.onFeedbackShow();
                    }
                });
            }
        }
    });
};

// Extends AbstractQuestionDirective
GraphicQuestionDirective.prototype = Object.create(AbstractQuestionDirective.prototype);

// Set up dependency injection (get DI from parent too)
GraphicQuestionDirective.$inject = AbstractQuestionDirective.$inject.concat([ '$timeout' ]);

// Register directive into AngularJS
angular
    .module('Question')
    .directive('graphicQuestion', GraphicQuestionDirective);
