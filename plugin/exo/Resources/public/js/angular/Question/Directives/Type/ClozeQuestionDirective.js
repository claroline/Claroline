/**
 * Cloze Question Directive
 * Manages Question of types Cloze
 *
 * @returns {object}
 * @constructor
 */
var ClozeQuestionDirective = function ClozeQuestionDirective($compile) {
    return {
        restrict: 'E',
        replace: true,
        controller: 'ClozeQuestionCtrl',
        controllerAs: 'clozeQuestionCtrl',
        bindToController: true,
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/Partials/Type/cloze.html',
        scope: {
            question     : '=',
            questionPaper: '='
        },
        compile: function compile() {
            return {
                pre: function preLink(scope, element, attrs, controller) {
                    // Generate DOM for cloze
                    var cloze = angular.element(controller.question.text);

                    // Bind each hole input to Angular JS
                    controller.holes = {};
                    for (var i = 0; i < controller.question.holes.length; i++) {
                        var hole            = controller.question.holes[i];
                        var holeAnswer      = controller.getHoleAnswer(hole);
                        var holeAnswerIndex = controller.questionPaper.answer.indexOf(holeAnswer); // Will be used for Angular data-binding

                        // Find hole input
                        var holeHtml = cloze.find('#' + hole.position);

                        // Add bootstrap class
                        holeHtml.addClass('form-control input-sm');

                        // Bind Angular JS
                        holeHtml.attr('data-ng-model', 'clozeQuestionCtrl.questionPaper.answer[' + holeAnswerIndex + '].answerText');

                        // Add validation class
                        holeHtml.attr('data-ng-class', '{ "has-success": clozeQuestionCtrl.holes["' + hole.id + '"].valid, "has-error": clozeQuestionCtrl.feedback.visible && !clozeQuestionCtrl.holes[' + hole.id + '].valid }');

                        // Disabled condition
                        holeHtml.attr('data-ng-disabled', 'clozeQuestionCtrl.feedback.visible || clozeQuestionCtrl.holes["' + hole.id + '"].valid');

                        // Add feedback
                        var feedbackHtml = angular.element('<span class="fa fa-fw feedback-info"></span>');

                        // Show/Hide condition
                        feedbackHtml.attr('data-ng-show', 'clozeQuestionCtrl.feedback.visible || clozeQuestionCtrl.holes["' + hole.id + '"].valid');

                        // Icon to display
                        feedbackHtml.attr('data-ng-class', '{ "fa-check text-success": clozeQuestionCtrl.holes["' + hole.id + '"].valid, "fa-times text-danger": !clozeQuestionCtrl.holes["' + hole.id + '"].valid }');

                        // Add tooltip
                        feedbackHtml.attr('data-toggle', 'tooltip');
                        feedbackHtml.attr('title', '{{ clozeQuestionCtrl.getHoleFeedback(clozeQuestionCtrl.holes["' + hole.id + '"]) }}');

                        // Append feedback
                        holeHtml.after(feedbackHtml);

                        // Store hole in controller for validation purpose
                        controller.holes[hole.id] = hole;
                    }

                    // Append cloze to page
                    element.find('.cloze').append(cloze);

                    // Compile the generated text to bind Angular to it
                    $compile(cloze)(scope);
                }
            }
        }
    };
};

// Set up dependency injection
ClozeQuestionDirective.$inject = [ '$compile' ];

// Register directive into AngularJS
angular
    .module('Question')
    .directive('clozeQuestion', ClozeQuestionDirective);
