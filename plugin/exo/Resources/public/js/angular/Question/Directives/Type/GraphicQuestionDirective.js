/**
 * Graphic Question Directive
 * Manages Question of type Graphic
 *
 * @param {Object} timeout
 * @returns {object}
 * @constructor
 */
var GraphicQuestionDirective = function GraphicQuestionDirective($timeout) {
    return {
        restrict: 'E',
        replace: true,
        controller: 'GraphicQuestionCtrl',
        controllerAs: 'graphicQuestionCtrl',
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/Partials/Type/graphic.html',
        scope: {
            question: '=',
            questionPaper: '='
        },
        bindToController: true,
        link: function (scope, element, attr, graphicQuestionCtrl) {
            // in case of coming from a js plumb question
            $timeout(function(){
                graphicQuestionCtrl.initPreviousAnswers();
                graphicQuestionCtrl.initDragAndDrop();
            });
        }
    };
};

// Set up dependency injection
GraphicQuestionDirective.$inject = [ '$timeout' ];

// Register directive into AngularJS
angular
    .module('Question')
    .directive('graphicQuestion', GraphicQuestionDirective);
