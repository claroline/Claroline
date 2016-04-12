/**
 * Paper details directive controller
 *
 */
angular.module('Correction').controller('CorrectionClozeCtrl', [
    'CommonService',
    'CorrectionService',
    function (CommonService, CorrectionService) {

        this.question = {};
        this.paper = {};
        this.answer = "";

        this.init = function (question, paper) {
            this.question = question;
            this.paper = paper;

            this.setAnswer(this.question.text);

            this.fillInFields();
            this.colourUsersAnswers();
        };

        this.fillInFields = function () {
            var id_question;
            var id_answer;
            var currElem;
            var currElemGParent;
            var select_options;
            var wordResponse;
            var users_answer;
            var new_select;
            var higher_score;
            var higher_scores_word;
            var style;

            for (var i=0; i<this.paper.questions.length; i++) {
                if (this.question.id.toString() === this.paper.questions[i].id) {
                    var answers = this.paper.questions[i].answer;
                    var elements = document.getElementsByClassName('blank');

                    for (var j=0; j<elements.length; j++) {
                        currElem = elements[j];
                        // we have to check the grand parent element, as it is
                        // the only element that can inform us if the current
                        // element is an answer field or a solution field
                        currElemGParent = elements[j].parentNode.parentNode;

                        /**
                         * Here, we fill the answers fields with the user's answers
                         */
                        if (currElemGParent.getAttribute('id') === "answer_" + this.question.id) {
                            id_answer = elements[j].getAttribute("id");
                            id_question = this.question.id;
                            Object.keys(answers).map(function(key){
                                if (key === id_answer) {
                                    $('#answer_' + id_question).find('#'+id_answer).val(answers[key]);
                                    $('#answer_' + id_question).find('#'+id_answer).prop('disabled', true);
                                }
                            });
                        }

                        /**
                         * Here, we fill the solutions fields with the right answers
                         */
                        if (currElemGParent.getAttribute('id') === "solution_" + this.question.id) {
                            id_answer = elements[j].getAttribute('id');
                            id_question = this.question.id;
                            users_answer = $('#answer_' + id_question).find('#'+id_answer).val();
                            for (var k=0; k<this.question.holes.length; k++) {
                                if (this.question.holes[k].position === id_answer) {
                                    // If it was a text field, we replace it with a select field
                                    if (currElem.tagName === "INPUT") {
                                        new_select = "<select id='" + id_answer + "' class='blank' name='blank_" + id_answer + "'></select>";
                                        $('#solution_' + id_question).find('#'+id_answer).replaceWith(new_select);
                                    }

                                    select_options = "";
                                    style = "";
                                    higher_score = 0;
                                    higher_scores_word = null;
                                    // this loop checks which answer is the best
                                    for (var l=0; l<this.question.holes[k].wordResponses.length; l++) {
                                        wordResponse = this.question.holes[k].wordResponses[l];
                                        if (wordResponse.score > higher_score) {
                                            higher_score = wordResponse.score;
                                            higher_scores_word = wordResponse;
                                        }
                                    }

                                    // this loop adds only the right answers in the fields
                                    // and colours them depending on it's the best answer or not
                                    for (var l=0; l<this.question.holes[k].wordResponses.length; l++) {
                                        wordResponse = this.question.holes[k].wordResponses[l];
                                        if (wordResponse.score > 0) {
                                            if (wordResponse.id === higher_scores_word.id) {
                                                style = "style='color:#2289b5; text-weight: bold;' selected";
                                            }
                                            else {
                                                style = "style='color:#30C1FF;'";
                                            }
                                            select_options += "<option " + style + " value='" + wordResponse.id + "'>" + wordResponse.response + "</option>";
                                        }
                                    }
                                    $('#solution_' + id_question).find('#'+id_answer).html(select_options);
                                    $('#solution_' + id_question).find('#'+id_answer).css("color", "#2289b5");
                                }
                            }
                        }
                    }
                }
            }
        };

        this.colourUsersAnswers = function () {
            var good_answer;
            var value_to_compare;
            for (var i=0; i<this.paper.questions.length; i++) {
                if (this.question.id.toString() === this.paper.questions[i].id) {
                    var answers = this.paper.questions[i].answer;
                    var holes = this.question.holes;

                    for (var j=0; j<holes.length; j++) {
                        good_answer = false;
                        Object.keys(answers).map(function(key){
                            if (holes[j].position === key) {
                                for (var k=0; k<holes[j].wordResponses.length; k++) {
                                    if (holes[j].selector) {
                                        value_to_compare = holes[j].wordResponses[k].id;
                                    }
                                    else {
                                        value_to_compare = holes[j].wordResponses[k].response;
                                    }
                                    // for removed spaces at the beginnig and at the end
                                    var answer = answers[key].trim();
                                    if (value_to_compare === answer && holes[j].wordResponses[k].score > 0) {
                                        good_answer = true;
                                    }
                                }
                            }
                        });
                        if (good_answer) {
                            $('#answer_' + this.question.id).find('#'+holes[j].position).addClass("color-success");
                        }
                        else {
                            $('#answer_' + this.question.id).find('#'+holes[j].position).addClass("color-danger");
                        }
                    }
                }
            }
        };

        this.getAnswer = function () {
            return this.answer;
        };

        this.setAnswer = function (answer) {
            this.answer = answer;
        };

    }
]);