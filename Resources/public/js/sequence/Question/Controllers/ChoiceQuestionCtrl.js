(function () {
    'use strict';

    angular.module('Question').controller('ChoiceQuestionCtrl', [
        'CommonService',
        'QuestionService',
        function (CommonService, QuestionService) {
            this.question = {};
            // keep answer
            this.multipleChoice = {};
            this.uniqueChoice = 0;
            this.currentPaperStep = {};

            this.init = function (question) {
                this.currentPaperStep = CommonService.getCurrentPaperStep();
                this.question = question;
                var meta = CommonService.getSequenceMeta();
                if (meta.random) {
                    CommonService.shuffleArray(this.question.choices);
                }
                this.checkChoices(this.question.multiple);
                // init hints object if needed
                if (this.currentPaperStep.hints === '') {
                    this.currentPaperStep.hints = [];
                }
                // init anwsers object if needed
                if (this.currentPaperStep.answers === '') {
                    this.currentPaperStep.answers = [];
                    this.currentPaperStep.answers[0] = {
                        answer_id: '',
                        question_id: this.question.id,
                        choices: []
                    };
                }
            };

            /**
             * check if a Hint has already been used (in paper)
             * @param {type} id
             * @returns {Boolean}
             */
            this.hintIsUsed = function (id) {
                for (var i = 0; i < this.currentPaperStep.hints.length; i++) {
                    if (this.currentPaperStep.hints[i].id === id) {
                        return true;
                    }
                }
                return false;
            };

            /**
             * Get hint data and update student data in common service
             * @param {type} hintId
             * @returns {undefined}
             */
            this.showHint = function (hintId) {
                //var content = this.getHintContent(hintId);
                var promise = QuestionService.getHint(hintId);
                promise.then(function (result) {
                    // hide button
                    angular.element('#hint-' + hintId).hide();
                    this.currentPaperStep.hints.push(result.data);
                    this.updateStudentData();

                }.bind(this), function (error) {
                    console.log('error');
                });
            };

            /**
             * check already given answers
             * @param {boolean} isMultiple
             */
            this.checkChoices = function (isMultiple) {
                var prevAnswer = this.currentPaperStep.answers[0]; // only one question per step for now
                if (prevAnswer && prevAnswer.choices && prevAnswer.choices.length > 0) {
                    for (var i = 0; i < this.question.choices.length; i++) {
                        // if an anwser exist with the choice id set checkbox answer model to true
                        if (this.answerExists(prevAnswer, this.question.choices[i].id)) {
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
                else {
                    if (isMultiple) {
                        for (var j = 0; j < this.question.choices.length; j++) {
                            this.multipleChoice[this.question.choices[j].id] = false;
                        }
                    }
                }
                // send the data to commen service so that other directives can get them
                this.updateStudentData();
            };

            /**
             * method used by check choices function
             * search an anwser in array
             * @param {type} prevAnswer
             * @param {type} search_id
             * @returns {Boolean}
             */
            this.answerExists = function (prevAnswer, search_id) {
                for (var i = 0; i < prevAnswer.choices.length; i++) {
                    if (prevAnswer.choices[i] === search_id) {
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
            this.updateStudentData = function () {
                var answer = this.question.multiple ? this.multipleChoice : this.uniqueChoice;
                this.currentPaperStep.answers[0].choices = answer;
                CommonService.setStudentData(this.question, this.currentPaperStep);
            };
        }
    ]);
})();