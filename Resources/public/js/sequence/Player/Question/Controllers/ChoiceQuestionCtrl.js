(function () {
    'use strict';

    angular.module('Question').controller('ChoiceQuestionCtrl', [
        '$ngBootbox',
        '$scope',
        'CommonService',
        'QuestionService',
        'PlayerDataSharing',
        'ExerciseService',
        function ($ngBootbox, $scope, CommonService, QuestionService, PlayerDataSharing, ExerciseService) {
            this.question = {};
            // keep choice(s)
            this.multipleChoice = {};
            this.uniqueChoice = [];
            this.currentQuestionPaperData = {};
            this.usedHints = [];

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
                //this.checkChoices(this.question.multiple);
                if (this.currentQuestionPaperData.answer && this.currentQuestionPaperData.answer.length > 0) {
                    // init previously given answer
                    this.checkChoices(this.question.multiple);
                }
            };

            /**
             * Check if choice is valid or not
             * @TODO expected answers not checked by user should not be shown as bad result except after the last step try
             * @param {Object} choice
             * @returns {Number} 
             *  0 = nothing, (unexpected answer not checked by user) 
             *  1 = valid, (expected answer checked by user) 
             *  2 = false (unexpected answer checked by user OR valid answer not checked by user) 
             */
            this.choiceIsValid = function (choice) {
                var isValid = 0;
                var sdata = PlayerDataSharing.getStudentData();
                // if there is any answer in student data
                if (sdata.answers.length > 0) {
                    for (var i = 0; i < this.solutions.length; i++) {
                        // search for valid solutions (score > 0)
                        if (this.solutions[i].id === choice.id && this.solutions[i].score > 0) {
                            var found = false;
                            // search for expected answer checked by student
                            for (var j = 0; j < sdata.answers.length; j++) {
                                if (sdata.answers[j] === choice.id) {
                                    isValid = 1;
                                    found = true;
                                }
                            }
                            // expected answer not checked by student
                            if(!found){
                                isValid = 2;
                            }
                        } else if (this.solutions[i].id === choice.id && this.solutions[i].score <= 0) {
                            // search for unexpected answer checked by student
                            for (var j = 0; j < sdata.answers.length; j++) {
                                if (sdata.answers[j] === choice.id) {
                                    isValid = 2;
                                }
                            }
                        }
                    }
                } else {
                    for (var i = 0; i < this.solutions.length; i++) {
                        if (this.solutions[i].id === choice.id && this.solutions[i].score > 0) {
                            // expected answer not checked by student
                            isValid = 2;
                        }
                    }
                }
                return isValid;
            };

            this.getChoiceFeedback = function (choice) {

                for (var i = 0; i < this.solutions.length; i++) {
                    if (this.solutions[i].id === choice.id) {
                        return this.solutions[i].feedback;
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
                            // hide hint button
                            angular.element('#hint-' + id).hide();
                        }.bind(this));
            };

            this.getHintData = function (id) {
                var promise = QuestionService.getHint(id);
                promise.then(function (result) {
                    //console.log(result);
                    this.usedHints.push(result);

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
                        // unset from this.currentQuestionPaperData.answer
                        for (var i = 0; i < this.currentQuestionPaperData.answer.length; i++) {
                            if (this.currentQuestionPaperData.answer[i] === choiceId) {
                                this.currentQuestionPaperData.answer.splice(i, 1);
                            }
                        }
                    }
                }
                else {
                    if (this.uniqueChoice.length > 0) {
                        this.currentQuestionPaperData.answer[0] = this.uniqueChoice;
                    }
                }
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

            /**
             * Listen to show-feedback event (broadcasted by ExercisePlayerCtrl)
             */
            $scope.$on('show-feedback', function (event, data) {
                this.showFeedback();
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