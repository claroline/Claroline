(function () {
    'use strict';

    angular.module('Question').controller('ChoiceQuestionCtrl', [
        '$ngBootbox',
        '$window',
        'CommonService',
        'QuestionService',
        function ($ngBootbox, $window, CommonService, QuestionService) {
            this.question = {};
            // keep choice(s)
            this.multipleChoice = {};
            this.uniqueChoice = [];
            this.currentQuestionPaperData = {};
            this.usedHints = [];// contains full hints object(s) for display

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
                //this.checkChoices(this.question.multiple);
                if (this.currentQuestionPaperData.answer.length > 0) {
                    // init previously given answer
                    this.checkChoices(this.question.multiple);
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

            /**
             * check already given answers
             * @param {boolean} isMultiple
             */
            this.checkChoices = function (isMultiple) {
                var prevAnswer = this.currentQuestionPaperData.answer; // only one question per step for now
                if (prevAnswer && prevAnswer.length > 0) {
                    for (var i = 0; i < this.question.choices.length; i++) {
                        // if an anwser exist with the choice id set checkbox answer model to true
                        if (this.answerExists(prevAnswer, this.question.choices[i].id, isMultiple)) {
                            if (isMultiple) {
                                this.multipleChoice[this.question.choices[i].id] = true;
                            }
                            else {
                                this.uniqueChoice = this.question.choices[i].id;
                            }
                        } else {
                            if (isMultiple) {
                                this.multipleChoice[this.question.choices[i].id] = false;
                            }
                        }
                    }
                }
                // send the data to commen service so that other directives can get them
                this.updateStudentData();
            };

            /**
             * method used by check choices function
             * for each question we check if the answer has been given
             * search an anwser in array
             * @param {array} prevAnswer collection of questions id
             * @param {type} searched question id
             * @param {bool} is mutliple choice ?
             * @returns {Boolean}
             */
            this.answerExists = function (prevAnswer, searched, isMultiple) {
                for (var j = 0; j < prevAnswer.length; j++) {
                    if (prevAnswer[j] === searched) {
                        return true;
                    }
                }
                return false;
            };

            /**
             * Checks if the question has meta
             * @returns {boolean}
             */
            this.questionHasOtherMeta = function () {
                return CommonService.objectHasOtherMeta(this.question);
            };

            /**
             * 
             * @param {object} object a javascript object with type property
             * @returns {string}
             */
            this.getChoiceSimpleType = function (object) {
                return CommonService.getObjectSimpleType(object);
            };

            /**
             * Called on each checkbox / radiobutton click
             * We need to share those informations with parent controllers
             * For that purpose we use a shared service
             */
            this.updateStudentData = function (choiceId) {
                if (this.question.multiple) {
                    if (this.multipleChoice[choiceId]) {
                        this.currentQuestionPaperData.answer.push(choiceId);
                    }
                    else {
                        //usnset from this.currentQuestionPaperData.answer
                        for (var i = 0; i < this.currentQuestionPaperData.answer.length; i++) {
                            if (this.currentQuestionPaperData.answer[i] === choiceId) {
                                this.currentQuestionPaperData.answer.splice(i, 1);
                            }
                        }
                    }
                }
                else {
                    this.currentQuestionPaperData.answer[0] = this.uniqueChoice;
                }
                CommonService.setStudentData(this.question, this.currentQuestionPaperData);
            };
        }
    ]);
})();