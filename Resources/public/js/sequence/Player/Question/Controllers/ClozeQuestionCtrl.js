(function () {
    'use strict';

    angular.module('Question').controller('ClozeQuestionCtrl', [
        'CommonService',
        function (CommonService) {

            this.question = {};
            this.formatedClozeText = '';
            this.isCollapsed = false;
            this.currentQuestionPaperData = {};
            
            this.init = function (question) {
                // those data are updated by view and sent to common service as soon as they change
                this.currentQuestionPaperData = CommonService.setCurrentQuestionPaperData(question);
                this.question = question;
                // init student data question object
                CommonService.setStudentData(question);

                if (this.currentQuestionPaperData.hints && this.currentQuestionPaperData.hints.length > 0) {
                    // init used hints display
                    for (var i = 0; i < this.currentQuestionPaperData.hints.length; i++) {
                        this.getHintData(this.currentQuestionPaperData.hints[i]);
                    }
                }
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
        }
    ]);
})();
