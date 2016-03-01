(function () {
    'use strict';

    angular.module('Question').controller('OpenQuestionCtrl', [
        '$ngBootbox',
        '$scope',
        'CommonService',
        'QuestionService',
        'PlayerDataSharing',
        'ExerciseService',
        function ($ngBootbox, $scope, CommonService, QuestionService, PlayerDataSharing, ExerciseService) {
            this.question = {};
            this.currentQuestionPaperData = {};
            this.usedHints = [];
            this.answer = "";

            // instant feedback data
            this.canSeeFeedback = false;
            this.feedbackIsVisible = false;
            this.solutions = {};
            this.questionFeedback = '';

            this.init = function (question, canSeeFeedback) {
                // those data are updated by view and sent to common service as soon as they change
                this.currentQuestionPaperData = PlayerDataSharing.setCurrentQuestionPaperData(question);
                this.question = question;
                this.canSeeFeedback = canSeeFeedback;
                // init student data question object
                PlayerDataSharing.setStudentData(question);

                if (this.currentQuestionPaperData.hints && this.currentQuestionPaperData.hints.length > 0) {
                    // init used hints display
                    for (var i = 0; i < this.currentQuestionPaperData.hints.length; i++) {
                        this.getHintData(this.currentQuestionPaperData.hints[i]);
                    }
                }
                
                if (typeof this.currentQuestionPaperData.answer !== "string" && this.currentQuestionPaperData.answer.length === 0) {
                    this.answer = "";
                    this.currentQuestionPaperData.answer = this.answer;
                    PlayerDataSharing.setStudentData(question, this.currentQuestionPaperData);
                }
                
                this.answer = this.currentQuestionPaperData.answer;
            };

            /**
             * check if a Hint has already been used (in paper)
             * @param {type} id
             * @returns {Boolean}
             */
            this.hintIsUsed = function (id) {
                if (this.currentQuestionPaperData && this.currentQuestionPaperData.hints) {
                    for (var i = 0; i < this.currentQuestionPaperData.hints.length; i++) {
                        if (this.currentQuestionPaperData.hints[i] === id) {
                            return true;
                        }
                    }
                }
                return false;
            };

            /**
             * Get hint data and update student data in common service
             * @param {type} hintId
             * @returns {undefined}
             */
            this.showHint = function (id) {
                var penalty = QuestionService.getHintPenalty(this.question.hints, id);
                $ngBootbox.confirm(Translator.trans('question_show_hint_confirm', {1: penalty}, 'ujm_sequence'))
                        .then(function () {
                            this.getHintData(id);
                            this.currentQuestionPaperData.hints.push(id);
                            this.updateStudentData();
                            // hide hint button
                            angular.element('#hint-' + id).hide();
                        }.bind(this));
            };

            this.getHintData = function (id) {
                var promise = QuestionService.getHint(id);
                promise.then(function (result) {
                    this.usedHints.push(result);

                }.bind(this));
            };

            /**
             * Called on each checkbox / radiobutton click
             * We need to share those informations with parent controllers
             * For that purpose we use a shared service
             */
            this.updateStudentData = function () {                
                // save the answer in currentQuestionPaperData, tu be able to reuse it during the sequence
                this.currentQuestionPaperData.answer = this.answer;
                PlayerDataSharing.setStudentData(this.question, this.currentQuestionPaperData);
            };

            this.showFeedback = function () {
                // get question answers and feedback ONLY IF NEEDED
                var promise = QuestionService.getQuestionSolutions(this.question.id);
                promise.then(function (result) {
                    this.feedbackIsVisible = true;
                    this.solutions = result.solutions;
                    this.questionFeedback = result.feedback;
                }.bind(this));
            };
            
            this.hideFeedback = function () {
                this.feedbackIsVisible = false;
            };

            /**
             * Listen to show-feedback event (broadcasted by ExercisePlayerCtrl)
             */
            $scope.$on('show-feedback', function (event, data) {
                this.showFeedback();
            }.bind(this));
            
            $scope.$on('hide-feedback', function () {
                this.hideFeedback();
            }.bind(this));

            /**
             * Hide / show a specific panel content and handle hide / show button icon 
             * @param {string} id (part of the panel id)
             */
            this.toggleDetails = function (id) {

                // custom toggle function to avoid the use of jquery
                if (angular.element('#question-body-' + id).attr('style') === undefined) {
                    angular.element('#question-body-' + id).attr('style', 'display: none;');
                } else {
                    // hide / show panel body
                    if (angular.element('#question-body-' + id).attr('style') === 'display: none;') {
                        angular.element('#question-body-' + id).attr('style', 'display: block;');
                    } else if (angular.element('#question-body-' + id).attr('style') === 'display: block;') {
                        angular.element('#question-body-' + id).attr('style', 'display: none;');
                    }
                }
                
                // handle hide / show button icon 
                if (angular.element('#question-toggle-' + id).hasClass('fa-chevron-down')) {
                    angular.element('#question-toggle-' + id).removeClass('fa-chevron-down').addClass('fa-chevron-right');
                }
                else if (angular.element('#question-toggle-' + id).hasClass('fa-chevron-right')) {
                    angular.element('#question-toggle-' + id).removeClass('fa-chevron-right').addClass('fa-chevron-down');
                }
            };
        }
    ]);
})();