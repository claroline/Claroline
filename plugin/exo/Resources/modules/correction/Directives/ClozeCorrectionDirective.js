import cloze from './../Partials/cloze.html'

export default function ClozeCorrectionDirective($compile) {
    return {
        restrict: 'E',
        replace: true,
        controller: 'ClozeCorrectionCtrl',
        controllerAs: 'clozeCorrectionCtrl',
        bindToController: true,
        template: cloze,
        scope: {
            question: '='
        },
        compile: function compile() {
            return {
                pre: function preLink(scope, element, attrs, controller) {
                    // Generate DOM for cloze
                    var cloze = angular.element(controller.question.text);

                    // Bind each hole input to Angular JS
                    for (var i = 0; i < controller.question.holes.length; i++) {
                        var hole            = controller.question.holes[i];
                        var holeAnswer      = controller.getHoleAnswer(hole);
                        var holeAnswerIndex = controller.answer.indexOf(holeAnswer); // Will be used for Angular data-binding

                        // Find hole input
                        var holeHtml = cloze.find('#' + hole.position);

                        // Add bootstrap class
                        holeHtml.addClass('form-control input-sm');

                        // Bind Angular JS
                        holeHtml.attr('data-ng-model', 'clozeCorrectionCtrl.answer[' + holeAnswerIndex + '].answerText');

                        // Disabled condition
                        holeHtml.attr('data-ng-disabled', 'true');

                        var feedback = controller.getHoleFeedback(hole);
                        if (feedback) {
                            // Add feedback
                            var feedbackHtml = angular.element('<span class="fa fa-comment fa-fw feedback-info"></span>');

                            // Add tooltip
                            feedbackHtml.attr('data-toggle', 'tooltip');
                            feedbackHtml.attr('title', feedback);

                            // Append feedback
                            holeHtml.after(feedbackHtml);
                        }
                    }

                    // Append cloze to page
                    element.find('.cloze').append(cloze);

                    // Compile the generated text to bind Angular to it
                    $compile(cloze)(scope);
                }
            }
        }
    };
}
