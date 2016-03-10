angular.module('Question').controller('ClozeQuestionCtrl', [
    '$ngBootbox',
    '$scope',
    'CommonService',
    'DataSharing',
    'QuestionService',
    '$timeout',
    function ($ngBootbox, $scope, CommonService, DataSharing, QuestionService, $timeout) {

        this.question = {};
        this.formatedClozeText = '';
        this.isCollapsed = false;
        this.currentQuestionPaperData = {};
        this.usedHints = [];// contains hints texts

        this.init = function (question) {
            // those data are updated by view and sent to common service as soon as they change
            this.currentQuestionPaperData = DataSharing.setCurrentQuestionPaperData(question);
            // init student data question object
            DataSharing.setStudentData(question);

            if (this.currentQuestionPaperData.hints && this.currentQuestionPaperData.hints.length > 0) {
                // init used hints display
                for (var i = 0; i < this.currentQuestionPaperData.hints.length; i++) {
                    this.getHintData(this.currentQuestionPaperData.hints[i]);
                }
            }
            if (this.currentQuestionPaperData.answer) {
                // init previously given answer
                this.setPreviousAnswers();
                this.setOnChange();
                this.setFirstCurrentQuestionPaperData();
            }
        };

        /**
         * set answers already given if any
         * @param {type} id
         * @returns {Boolean}
         */
        this.setPreviousAnswers = function () {
            var answers = this.currentQuestionPaperData.answer;
            var array_answers = new Array();
            Object.keys(answers).map(function(key){
                $("#"+key).val(answers[key]);
                array_answers[key] = answers[key];
            });
            this.currentQuestionPaperData.answer = array_answers;
            DataSharing.setStudentData(this.question, this.currentQuestionPaperData);
        };

        /**
         * set event on change of inputs values
         * @param {type} id
         * @returns {Boolean}
         */
        this.setOnChange = function () {
            var elements = document.getElementsByClassName("blank");
            for (var i=0; i<elements.length; i++) {
                var cqpd = this.currentQuestionPaperData;
                var question = this.question;
                document.getElementById(elements[i].id).onchange = function () {
                    var id = this.id;
                    var value = this.value;
                    cqpd.answer[id] = value;
                    DataSharing.setStudentData(question, cqpd);
                };
            }
        };

        /**
         * set first currentquestionpaperdata
         * @param {type} id
         * @returns {Boolean}
         */
        this.setFirstCurrentQuestionPaperData = function () {
            var elements = document.getElementsByClassName("blank");
            for (var i=0; i<elements.length; i++) {
                var cqpd = this.currentQuestionPaperData;
                var question = this.question;
                var id = elements[i].id;
                var value = elements[i].value;
                cqpd.answer[id] = value;
                DataSharing.setStudentData(question, cqpd);
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
         * Listen to show-feedback event (broadcasted by ExercisePlayerCtrl)
         */
        $scope.$on('show-feedback', function (event, data) {
            this.showFeedback();
        }.bind(this));

        $scope.$on('hide-feedback', function (event, data) {
            this.hideFeedback();
        }.bind(this));

        this.showFeedback = function () {
            // get question answers and feedback ONLY IF NEEDED
            var promise = QuestionService.getQuestionSolutions(this.question.id);
            promise.then(function (result) {
                this.feedbackIsVisible = true;
                this.solutions = result.solutions;
                this.setScore();
                this.questionFeedback = result.feedback;
                this.setFeedbacks();
            }.bind(this));

            $('.blank').each(function () {
                $(this).prop('disabled', true);
            });
        };

        this.setScore = function () {
            var score = 0;
            for (var i=0; i<this.solutions.length; i++) {
                // Calculer le score ici
                // et l'updater dans le current truc
                for (var j=0; j<this.solutions[i].wordResponses.length; j++) {
                    var currentAnswer = this.currentQuestionPaperData.answer[parseInt(this.solutions[i].position)];
                    if (this.solutions[i].selector && this.solutions[i].wordResponses[j].id === currentAnswer) {
                        score = score + this.solutions[i].wordResponses[j].score;
                    }
                    if (!this.solutions[i].selector && this.solutions[i].wordResponses[j].response === currentAnswer) {
                        score = score + this.solutions[i].wordResponses[j].score;
                    }
                }
            }
            DataSharing.setQuestionScore(score, this.question.id);
        };

        this.setFeedbacks = function () {
            var fields = $(".blank");
            for (var i=0; i<fields.length; i++) {
                var element = $("<i></i>");
                element.attr("data-toggle", "tooltip");
                for (var j=0; j<this.solutions.length; j++) {
                    if (this.solutions[j].position === fields[i].id) {
                        var betterScore = 0;
                        for (var k=0; k<this.solutions[j].wordResponses.length; k++) {
                            if (this.solutions[j].wordResponses[k].score > betterScore) {
                                betterScore = this.solutions[j].wordResponses[k].score;
                            }
                        }
                        var found = false;
                        for (var k=0; k<this.solutions[j].wordResponses.length; k++) {
                            if (fields[i].tagName === "INPUT" && fields[i].value === this.solutions[j].wordResponses[k].response) {
                                element.prop("title", this.solutions[j].wordResponses[k].feedback);
                                found = true;
                                if (this.solutions[j].wordResponses[k].score === betterScore) {
                                    element.addClass("feedback-icon fa fa-check color-success");
                                }
                                else if (this.solutions[j].wordResponses[k].score === 0) {
                                    element.addClass("feedback-icon fa fa-close color-danger");
                                }
                                else {
                                    element.addClass("feedback-icon fa fa-check color-info");
                                }
                            }
                            else if (fields[i].tagName === "SELECT" && fields[i].value === this.solutions[j].wordResponses[k].id) {
                                element.prop("title", this.solutions[j].wordResponses[k].feedback);
                                if (this.solutions[j].wordResponses[k].score === betterScore) {
                                    element.addClass("feedback-icon fa fa-check color-success");
                                }
                                else if (this.solutions[j].wordResponses[k].score === 0) {
                                    element.addClass("feedback-icon fa fa-close color-danger");
                                }
                                else {
                                    element.addClass("feedback-icon fa fa-check color-info");
                                }
                            }
                            else if (fields[i].value === "" && fields[i].value !== this.solutions[j].wordResponses[k].response) {
                                element.addClass("feedback-icon fa fa-close color-danger");
                            }
                        }
                        if (!found && fields[i].tagName === "INPUT") {
                            element.addClass("feedback-icon fa fa-close color-danger");
                        }
                    }
                }
                element.insertAfter(document.getElementById(fields[i].id));
            }
        };

        this.hideFeedback = function () {
            this.feedbackIsVisible = false;

            $(".feedback-icon").remove();

            $('.blank').each(function () {
                $(this).prop('disabled', false);
            });
        };

        /**
         * Check if the question has meta like created / licence, description...
         * @returns {boolean}
         */
        this.questionHasOtherMeta = function () {
            return this.question.meta.licence || this.question.meta.created || this.question.meta.modified || this.question.meta.description;
        };

        this.updateStudentData = function () {
            CommonService.setStudentData(this.question, this.currentQuestionPaperData);
        };
    }
]);