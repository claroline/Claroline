/**
 * PlayerCommonService
 */
angular.module('Common').factory('DataSharing', [
    '$http',
    '$filter',
    '$q',
    function DataSharing($http, $filter, $q) {
        this.paper = {};

        this.currentQuestion = {};
        this.currentQuestionPaperData = {};

        return {
            setPaper: function (paper) {
                this.paper = paper;

                return this.paper;
            },

            getPaper: function () {
                return this.paper;
            },

            /**
             * Set the current paper data and return paper anwser(s) and used hints for the current question
             * @param {object} question
             * @returns {object}
             */
            setCurrentQuestionPaperData: function (question) {
                // search for an existing answer to the question or used hints in existing paper
                for (var i = 0; i < this.paper.questions.length; i++) {
                    if (this.paper.questions[i].id === question.id.toString()) {
                        this.currentQuestionPaperData = {
                            id:this.paper.questions[i].id,
                            answer:this.paper.questions[i].answer && this.paper.questions[i].answer !== '' ? this.paper.questions[i].answer : [],
                            hints:this.paper.questions[i].hints ? this.paper.questions[i].hints : [],
                        };
                        return this.currentQuestionPaperData;
                    }
                }

                // if no info found, initiate object
                this.currentQuestionPaperData = {
                    id: question.id.toString(),
                    answer: [],
                    hints: []
                };

                this.paper.questions.push(this.currentQuestionPaperData);

                return this.currentQuestionPaperData;
            },

            /**
             * Set / Update student data
             * @param question
             * @param currentQuestionPaperData
             */
            setStudentData: function (question, currentQuestionPaperData) {
                this.currentQuestion = question;
                // this will automatically update the paper object... Or not was working with choices question
                // but not with match questions...
                if (currentQuestionPaperData) {
                    this.currentQuestionPaperData = currentQuestionPaperData;
                    // specificly refresh question answers...
                    for (var i = 0; i < this.paper.questions.length; i++){
                        if(this.paper.questions[i].id === question.id.toString()){
                            this.paper.questions[i].answer = currentQuestionPaperData.answer;
                        }
                    }
                    // see if we'll need to do the same with hints...
                }
            },

            getStudentData: function () {
                return {
                    question: this.currentQuestion,
                    paper: this.paper,
                    answers: this.currentQuestionPaperData.answer
                };
            },

            setQuestionScore: function (score, questionId) {
                for (var i=0; i<this.paper.questions.length; i++) {
                    if (this.paper.questions[i].id === questionId.toString()) {
                        this.paper.questions[i].score = score;
                    }
                }
            }
        };
    }
]);


