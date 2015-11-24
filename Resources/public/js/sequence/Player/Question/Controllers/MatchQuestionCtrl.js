(function () {
    'use strict';

    angular.module('Question').controller('MatchQuestionCtrl', [  
        '$ngBootbox',
        'CommonService',        
        'QuestionService',
        function ($ngBootbox, CommonService, QuestionService) {
            this.question = {};
            
            this.isCollapsed = false;
            
            this.init = function (question) {
                // get used hints infos (id + content) + checked answer(s) for the current step / question
                // those data are updated by view and sent to common service as soon as they change
                this.currentQuestionPaperData = CommonService.getCurrentQuestionPaperData(question);
                this.question = question;
                if (this.currentQuestionPaperData.hints.length > 0) {
                    // init used hints display
                    for (var i = 0; i < this.currentQuestionPaperData.hints.length; i++) {
                        this.getHintData(this.currentQuestionPaperData.hints[i]);
                    }
                }
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
                            // hide button
                            angular.element('#hint-' + id).hide();
                        }.bind(this));
            };

            this.getHintData = function (id) {
                var promise = QuestionService.getHint(id);
                promise.then(function (result) {
                    this.usedHints.push(result.data);

                }.bind(this));
            };

            this.setQuestion = function (question) {
                this.question = question;
            };

            this.getQuestion = function () {
                return this.question;
            };
        }
    ]);
})();