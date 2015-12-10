(function () {
    'use strict';

    angular.module('Question').controller('ClozeQuestionCtrl', [
        '$ngBootbox',
        'CommonService',
        'PlayerDataSharing',
        'QuestionService',
        function ($ngBootbox, CommonService, PlayerDataSharing, QuestionService) {

            this.question = {};
            this.formatedClozeText = '';
            this.isCollapsed = false;
            this.currentQuestionPaperData = {};
            this.usedHints = [];// contains hints texts
            
            this.init = function (question) {
                // those data are updated by view and sent to common service as soon as they change
                this.currentQuestionPaperData = PlayerDataSharing.setCurrentQuestionPaperData(question);
                this.question = question;
                // init student data question object
                PlayerDataSharing.setStudentData(question);

                if (this.currentQuestionPaperData.hints && this.currentQuestionPaperData.hints.length > 0) {
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
                    //console.log(result);
                    this.usedHints.push(result);

                }.bind(this));
            };

            this.setQuestion = function (question) {
                this.question = question;
            };

            this.getQuestion = function () {
                return this.question;
            };

            /**
             * build the cloze string to show
             * the original text is given with n [[hole_id]] tags that we need to replace with appropriate holes or choice lists
             * @param {string} text the original text
             * 
             */
            this.setQuestionText = function (text) {
                var regex = /\[\[[0-9]*\]\]/g;
                var toReplace = [];
                // find every [[hole_id]] occurences in the original text and push them into array
                text.replace(regex, function (found) {
                    toReplace.push(found);
                }.bind(this));
                var result = text;
                // foreach item found, find the corresponding hole data and replace the [[hole_id]] with either a select input or text input 
                for (var i = 0; i < toReplace.length; i++) {
                    var temp = toReplace[i];// [[hole_id]]
                    var holeId = temp.substring(2, 3);
                    // find the corresponding object in question.holes attribute
                    var holeObject = this.findHoleObject(holeId);
                    // build data to put in the original text depending on hole object type
                    var replacement = this.getHoleContent(holeObject);
                    result = result.replace(toReplace[i], replacement);
                }
                this.formatedClozeText = result;
            };

            /**
             * Find a hole object in the collection or return a default one
             * @param {string} id
             * @returns a hole object (default or found)
             */
            this.findHoleObject = function (id) {
                if (this.question.holes) {
                    for (var j = 0; j < this.question.holes.length; j++) {
                        if (this.question.holes[j].id === parseInt(id)) {
                            return this.question.holes[j];
                        }
                    }
                }
                return {"type": "simple", "size": 50};
            };

            this.getHoleContent = function (hole) {
                if (typeof hole.choices !== "undefined") {
                    var html = '';
                    html += '<select>';
                    for (var i = 0; i < hole.choices.length; i++) {
                        html += '<option>' + hole.choices[i] + '</option>';
                    }
                    html += '</select>';
                    return html;
                }
                else {
                    var size = hole.size ? hole.size.toString() : '50';
                    var input = '<input type="text" style="width:' + size + 'px;" value=""';
                    if (hole.placeholder) {
                        input += ' placeholder="' + hole.placeholder + '"';
                    }
                    input += ' >';
                    return input;
                }
            };

            /**
             * Check if the question has meta like created / licence, description...
             * @returns {boolean}
             */
            this.questionHasOtherMeta = function () {
                return this.question.meta.licence || this.question.meta.created || this.question.meta.modified || this.question.meta.description;
            };
            
            this.updateStudentData = function (choiceId) {
                CommonService.setStudentData(this.question, this.currentQuestionPaperData);
            };
        }
    ]);
})();
